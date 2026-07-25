<?php

namespace Tests\Feature\Auth;

use App\Mail\CustomerWelcomeMail;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Security check: What is');
        $this->assertNotNull(session('registration_captcha_answer'));
    }

    public function test_new_users_can_register(): void
    {
        Mail::fake();
        Notification::fake();

        $response = $this->withSession(['registration_captcha_answer' => 10])->post('/register', [
            'name' => 'Test User',
            'father_name' => 'Test Father',
            'cnic' => '12345-1234567-1',
            'email' => 'test@example.com',
            'phone' => '03001234567',
            'address' => 'House 1, Abbottabad',
            'password' => 'password',
            'password_confirmation' => 'password',
            'captcha' => 10,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('verification.notice'));
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com', 'father_name' => 'Test Father', 'cnic' => '12345-1234567-1',
            'phone' => '03001234567', 'address' => 'House 1, Abbottabad', 'role' => 'customer',
        ]);
        $registeredUser = User::findOrFail(auth()->id());
        $this->assertTrue($registeredUser->hasRole('customer'));
        $this->assertTrue($registeredUser->hasAllPermissions(\App\Support\Permissions::customer()));
        $this->assertDatabaseHas('referrals', ['user_id' => auth()->id()]);
        Mail::assertSent(CustomerWelcomeMail::class, function (CustomerWelcomeMail $mail) {
            return $mail->hasTo('test@example.com')
                && $mail->customer->referral_code === auth()->user()->referral_code;
        });
        Notification::assertSentTo(auth()->user(), VerifyEmail::class);
    }

    public function test_new_customer_can_verify_email_from_the_sent_link(): void
    {
        Mail::fake();
        Notification::fake();

        $this->withSession(['registration_captcha_answer' => 10])->post('/register', [
            'name' => 'Verify Customer',
            'father_name' => 'Verify Father',
            'cnic' => '12345-1234567-9',
            'email' => 'verify-customer@example.com',
            'phone' => '03001234567',
            'address' => 'House 9, Abbottabad',
            'password' => 'password',
            'password_confirmation' => 'password',
            'captcha' => 10,
        ]);

        $customer = auth()->user();
        $notification = Notification::sent($customer, VerifyEmail::class)->first();
        $verificationUrl = $notification->toMail($customer)->actionUrl;

        $this->get($verificationUrl)
            ->assertRedirect(route('dashboard', absolute: false).'?verified=1');

        $this->assertTrue(User::findOrFail($customer->id)->hasVerifiedEmail());
    }

    public function test_referral_code_is_validated_and_connects_the_customer_to_the_database_sponsor(): void
    {
        Mail::fake();
        Notification::fake();

        $sponsor = Customer::create([
            'name' => 'Sponsor', 'email' => 'sponsor@example.com', 'phone' => '0300',
            'password' => 'password', 'referral_code' => 'REF-SPONSOR', 'status' => true,
        ]);
        $registration = [
            'name' => 'Referred Customer', 'father_name' => 'Father', 'cnic' => '12345-1234567-2',
            'email' => 'referred@example.com', 'phone' => '03001234567', 'address' => 'Abbottabad',
            'password' => 'password', 'password_confirmation' => 'password',
        ];

        $this->withSession(['registration_captcha_answer' => 10])
            ->post('/register', $registration + ['referral_code' => 'NOT-FOUND', 'captcha' => 10])
            ->assertSessionHasErrors('referral_code');
        $this->assertDatabaseMissing('users', ['email' => 'referred@example.com']);

        $this->withSession(['registration_captcha_answer' => 10])
            ->post('/register', $registration + ['referral_code' => 'ref-sponsor', 'captcha' => 10])
            ->assertRedirect(route('verification.notice'));
        $customer = Customer::where('email', 'referred@example.com')->firstOrFail();
        $this->assertEquals($sponsor->id, $customer->referral_agent_id);
        $this->assertDatabaseHas('referrals', ['user_id' => $customer->id, 'sponsor_id' => $sponsor->id]);
        Mail::assertSent(CustomerWelcomeMail::class, fn (CustomerWelcomeMail $mail) => $mail->hasTo('referred@example.com'));
    }

    public function test_registration_requires_the_correct_captcha_answer_and_consumes_it(): void
    {
        Mail::fake();
        Notification::fake();

        $registration = [
            'name' => 'Captcha User', 'father_name' => 'Father', 'cnic' => '12345-1234567-3',
            'email' => 'captcha@example.com', 'phone' => '03001234567', 'address' => 'Abbottabad',
            'password' => 'password', 'password_confirmation' => 'password',
        ];

        $this->withSession(['registration_captcha_answer' => 10])
            ->post('/register', $registration + ['captcha' => 11])
            ->assertSessionHasErrors('captcha');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'captcha@example.com']);
        $this->assertNull(session('registration_captcha_answer'));

        $this->post('/register', $registration + ['captcha' => 10])
            ->assertSessionHasErrors('captcha');
        $this->assertDatabaseMissing('users', ['email' => 'captcha@example.com']);
    }
}
