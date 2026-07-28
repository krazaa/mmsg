<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_staff_accounts(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@abdullahtown.pk')->firstOrFail();

        $this->actingAs($admin)->get(route('staff.index'))
            ->assertOk()->assertSee('Staff management')->assertSee('Customers');

        $this->actingAs($admin)->post(route('staff.store'), [
            'name' => 'Office Staff', 'father_name' => 'Staff Father', 'cnic' => '12345-1234567-4',
            'email' => 'staff2@example.com', 'phone' => '03001234567', 'address' => 'Office Road, Abbottabad',
            'password' => 'password', 'password_confirmation' => 'password', 'role' => 'staff', 'status' => 1,
        ])->assertRedirect(route('staff.index'));

        $staff = User::where('email', 'staff2@example.com')->firstOrFail();
        $system = User::where('email', 'direct-sales@mmsgroup.pk')->firstOrFail();
        $this->assertSame('staff', $staff->role);
        $this->assertSame('STF-'.str_pad((string) $staff->id, 6, '0', STR_PAD_LEFT), $staff->referral_code);
        $this->assertSame($system->id, $staff->referral_agent_id);
        $this->assertDatabaseHas('referrals', ['user_id' => $staff->id, 'sponsor_id' => $system->id]);
        $this->assertTrue($staff->hasRole('staff'));

        $this->actingAs($admin)->put(route('staff.update', $staff), [
            'name' => 'Updated Staff', 'father_name' => 'Updated Father', 'cnic' => $staff->cnic,
            'email' => $staff->email, 'phone' => '03111234567', 'address' => 'Updated staff address', 'role' => 'staff', 'status' => 0,
        ])->assertRedirect(route('staff.index'));
        $this->assertDatabaseHas('users', ['id' => $staff->id, 'name' => 'Updated Staff', 'father_name' => 'Updated Father', 'address' => 'Updated staff address', 'status' => false]);

        $this->actingAs($admin)->delete(route('staff.destroy', $staff))->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $staff->id]);
    }

    public function test_regular_staff_cannot_manage_other_staff_accounts(): void
    {
        $this->seed();
        $staff = User::factory()->create(['role' => 'staff', 'email_verified_at' => now()]);

        $this->actingAs($staff)->get(route('staff.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('customers.index'))->assertOk();
    }

    public function test_staff_routes_cannot_modify_non_staff_accounts(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@abdullahtown.pk')->firstOrFail();
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($admin)->get(route('staff.edit', $customer))->assertNotFound();
        $this->actingAs($admin)->delete(route('staff.destroy', $customer))->assertNotFound();
    }

    public function test_admin_cannot_remove_their_own_administrator_role(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@abdullahtown.pk')->firstOrFail();

        $this->actingAs($admin)->put(route('staff.update', $admin), [
            'name' => $admin->name, 'father_name' => 'Admin Father', 'cnic' => '12345-1234567-5',
            'email' => $admin->email, 'phone' => '03001234567', 'address' => 'Admin address',
            'role' => 'staff', 'status' => 1,
        ])->assertSessionHasErrors('role');

        $this->assertSame('admin', $admin->refresh()->role);
    }

    public function test_admin_cannot_see_or_access_super_admin_account(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@abdullahtown.pk')->firstOrFail();
        $superAdmin = User::factory()->create([
            'name' => 'Protected Super Admin',
            'email' => 'sadmin@mmsg.com',
            'role' => 'super_admin',
        ]);
        $superAdmin->syncRoles('super_admin');

        $this->actingAs($admin)->get(route('staff.index'))
            ->assertOk()
            ->assertDontSee('sadmin@mmsg.com')
            ->assertDontSee('Protected Super Admin');

        $this->actingAs($admin)->get(route('staff.edit', $superAdmin))->assertForbidden();
        $this->actingAs($admin)->put(route('staff.update', $superAdmin), [])->assertForbidden();
        $this->actingAs($admin)->delete(route('staff.destroy', $superAdmin))->assertForbidden();

        $this->actingAs($superAdmin)->get(route('staff.edit', $superAdmin))->assertOk();
    }

    public function test_only_one_super_admin_account_is_allowed(): void
    {
        $this->seed();
        $superAdmin = User::factory()->create([
            'email' => 'only-super-admin@example.com',
            'role' => 'super_admin',
        ]);
        $superAdmin->syncRoles('super_admin');

        $this->actingAs($superAdmin)->post(route('staff.store'), [
            'name' => 'Second Super Admin',
            'father_name' => 'Admin Father',
            'cnic' => '12345-1234567-9',
            'email' => 'second-super-admin@example.com',
            'phone' => '03001234567',
            'address' => 'Admin address',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'super_admin',
            'status' => 1,
        ])->assertSessionHasErrors('role');

        $admin = User::where('email', 'admin@abdullahtown.pk')->firstOrFail();
        $this->actingAs($superAdmin)->put(route('staff.update', $admin), [
            'name' => $admin->name,
            'father_name' => 'Admin Father',
            'cnic' => '12345-1234567-8',
            'email' => $admin->email,
            'phone' => '03001234567',
            'address' => 'Admin address',
            'role' => 'super_admin',
            'status' => 1,
        ])->assertSessionHasErrors('role');

        $this->assertSame(1, User::where('role', 'super_admin')->count());
        $this->assertSame('admin', $admin->refresh()->role);
    }

    public function test_operational_roles_only_access_their_assigned_workflows(): void
    {
        $this->seed();

        $booking = User::factory()->create(['role' => 'booking', 'email_verified_at' => now()]);
        $booking->syncRoles('booking');
        $this->assertTrue($booking->can('access management'));
        $this->assertTrue($booking->can('manage bookings'));
        $this->actingAs($booking)->get(route('bookings.index'))->assertOk();
        $this->actingAs($booking)->get(route('payments.index'))->assertForbidden();
        $this->actingAs($booking)->get(route('withdrawal-requests.index'))->assertForbidden();

        $verification = User::factory()->create(['role' => 'verification', 'email_verified_at' => now()]);
        $verification->syncRoles('verification');
        $this->actingAs($verification)->get(route('payments.index'))->assertOk();
        $this->actingAs($verification)->get(route('bookings.index'))->assertForbidden();
        $this->actingAs($verification)->get(route('withdrawal-requests.index'))->assertForbidden();

        $withdrawal = User::factory()->create(['role' => 'withdrawal', 'email_verified_at' => now()]);
        $withdrawal->syncRoles('withdrawal');
        $this->actingAs($withdrawal)->get(route('withdrawal-requests.index'))->assertOk();
        $this->actingAs($withdrawal)->get(route('bookings.index'))->assertForbidden();
        $this->actingAs($withdrawal)->get(route('payments.index'))->assertForbidden();
    }
}
