<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Commission;
use App\Models\CommissionPayout;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\PlotPackage;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_sees_personal_sales_commissions_and_payouts_only(): void
    {
        $agent = User::factory()->create(['role' => 'agent', 'referral_code' => 'AGT-PORTAL', 'email_verified_at' => now()]);
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Project::create(['name' => 'Agent Project', 'slug' => 'agent-project', 'location' => 'Abbottabad', 'gross_area_marla' => 1000, 'saleable_area_marla' => 1000]);
        $package = PlotPackage::create(['project_id' => $project->id, 'name' => 'Agent Plan', 'size_marla' => 5, 'booking_amount' => 100000, 'months' => 12, 'monthly_amount' => 25000, 'month_12_balloon' => 0, 'month_24_balloon' => 0, 'month_36_balloon' => 0]);
        $customer = Customer::create(['name' => 'Agent Customer', 'cnic' => '33333-3333333-3', 'phone' => '0300']);
        $booking = Booking::create(['booking_number' => 'BOOK-AGENT', 'project_id' => $project->id, 'package_id' => $package->id, 'customer_id' => $customer->id, 'agent_id' => $agent->id, 'booking_date' => today(), 'total_price' => 400000, 'booking_amount' => 100000, 'financed_amount' => 300000, 'status' => 'active']);
        $payment = Payment::create(['receipt_number' => 'RC-AGENT', 'booking_id' => $booking->id, 'customer_id' => $customer->id, 'amount' => 100000, 'payment_method' => 'cash', 'payment_date' => now(), 'status' => 'verified']);
        $payout = CommissionPayout::create(['payout_number' => 'PAY-AGENT', 'agent_id' => $agent->id, 'amount' => 3000, 'payment_method' => 'bank_transfer', 'transaction_reference' => 'BANK-AGENT', 'paid_by' => $admin->id, 'paid_at' => now()]);
        Commission::create(['payment_id' => $payment->id, 'booking_id' => $booking->id, 'beneficiary_id' => $agent->id, 'level' => 1, 'percentage' => 5, 'amount' => 5000, 'status' => 'earned']);
        Commission::create(['payment_id' => $payment->id, 'booking_id' => $booking->id, 'beneficiary_id' => $agent->id, 'commission_payout_id' => $payout->id, 'level' => 2, 'percentage' => 3, 'amount' => 3000, 'status' => 'paid']);

        $this->actingAs($agent)->get(route('dashboard'))
            ->assertOk()->assertSee('Agent portal')->assertSee('AGT-PORTAL')
            ->assertSee('Payable commission')->assertSee('5,000.00')
            ->assertSee('Paid out')->assertSee('3,000.00')
            ->assertSee('Lifetime commission')->assertSee('8,000.00')
            ->assertSee('Payout history')->assertSee('PAY-AGENT')
            ->assertSee('Commission history')->assertSee('RC-AGENT')
            ->assertSee('My direct sales')->assertSee('BOOK-AGENT')
            ->assertDontSee('Manage projects');

        $this->actingAs($agent)->get(route('payments.index'))->assertForbidden();
        $this->actingAs($agent)->get(route('agents.index'))->assertForbidden();
    }
}
