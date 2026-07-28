<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use App\Models\WithdrawalSetting;
use App\Notifications\WithdrawalPinResetNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk()->assertSee('Add a passkey');
    }

    public function test_customer_profile_shows_referral_code_and_link_when_enabled(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'referral_code' => 'CUS-123456',
        ]);

        $this->actingAs($customer)
            ->get('/profile')
            ->assertOk()
            ->assertSee('CUS-123456')
            ->assertSee(route('register', ['ref' => 'CUS-123456']));
    }

    public function test_customer_profile_hides_referral_code_and_link_when_disabled(): void
    {
        SiteSetting::query()->create([
            'key' => 'customer_portal_show_referral_code',
            'value' => '0',
        ]);

        $customer = User::factory()->create([
            'role' => 'customer',
            'referral_code' => 'CUS-HIDDEN',
        ]);

        $this->actingAs($customer)
            ->get('/profile')
            ->assertOk()
            ->assertDontSee('CUS-HIDDEN')
            ->assertDontSee(route('register', ['ref' => 'CUS-HIDDEN']));
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_customer_cannot_change_registered_email_from_profile(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $originalEmail = $customer->email;

        $this->actingAs($customer)->patch('/profile', [
            'name' => $customer->name,
            'email' => 'changed@example.com',
        ])->assertSessionHasErrors('email');

        $this->assertSame($originalEmail, $customer->refresh()->email);
    }

    public function test_customer_cannot_change_registered_phone_or_cnic_from_profile(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'phone' => '03001234567',
            'cnic' => '12345-1234567-1',
        ]);

        $this->actingAs($customer)->patch('/profile', [
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => '03009999999',
            'cnic' => '99999-9999999-9',
        ])->assertSessionHasErrors(['phone', 'cnic']);

        $customer->refresh();
        $this->assertSame('03001234567', $customer->phone);
        $this->assertSame('12345-1234567-1', $customer->cnic);
    }

    public function test_customer_can_securely_set_and_change_withdrawal_pin(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)->patch(route('profile.withdrawal-pin.update'), [
            'current_password' => 'password',
            'withdrawal_pin' => '2468',
            'withdrawal_pin_confirmation' => '2468',
        ])->assertRedirect(route('profile.edit'))->assertSessionHas('success');

        $customer->refresh();
        $this->assertNotSame('2468', $customer->getRawOriginal('withdrawal_pin'));
        $this->assertTrue(Hash::check('2468', $customer->withdrawal_pin));
        $this->assertSame(0, $customer->withdrawal_pin_failed_attempts);
        $this->assertNull($customer->withdrawal_pin_locked_until);
        $this->assertArrayNotHasKey('withdrawal_pin', $customer->toArray());
    }

    public function test_withdrawal_pin_requires_current_password_and_matching_digits(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)->from(route('profile.edit'))->patch(route('profile.withdrawal-pin.update'), [
            'current_password' => 'wrong-password',
            'withdrawal_pin' => '12ab',
            'withdrawal_pin_confirmation' => '9999',
        ])->assertSessionHasErrors(['current_password', 'withdrawal_pin']);

        $this->assertNull($customer->refresh()->withdrawal_pin);
    }

    public function test_customer_can_receive_a_temporary_withdrawal_pin_and_clear_the_lock(): void
    {
        Notification::fake();
        $customer = User::factory()->create([
            'role' => 'customer',
            'withdrawal_pin' => '2468',
            'withdrawal_pin_failed_attempts' => 4,
            'withdrawal_pin_locked_until' => now()->addDay(),
        ]);

        $this->actingAs($customer)->post(route('customer.withdrawal-pin.recover'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $customer->refresh();
        Notification::assertSentTo($customer, WithdrawalPinResetNotification::class, function ($notification) use ($customer): bool {
            return preg_match('/^\d{6}$/', $notification->temporaryPin) === 1
                && Hash::check($notification->temporaryPin, $customer->withdrawal_pin);
        });
        $this->assertSame(0, $customer->withdrawal_pin_failed_attempts);
        $this->assertNull($customer->withdrawal_pin_locked_until);

        $this->actingAs($customer)->post(route('customer.withdrawal-pin.recover'))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_customer_cannot_recover_withdrawal_pin_when_admin_disables_recovery(): void
    {
        Notification::fake();
        WithdrawalSetting::query()->update(['pin_recovery_enabled' => false]);
        $customer = User::factory()->create(['role' => 'customer', 'withdrawal_pin' => '2468']);
        $originalPin = $customer->getRawOriginal('withdrawal_pin');

        $this->actingAs($customer)->post(route('customer.withdrawal-pin.recover'))
            ->assertRedirect()
            ->assertSessionHas('error', 'Temporary PIN recovery is disabled by the office. Please contact the office for assistance.');

        Notification::assertNothingSent();
        $this->assertSame($originalPin, $customer->refresh()->getRawOriginal('withdrawal_pin'));
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
