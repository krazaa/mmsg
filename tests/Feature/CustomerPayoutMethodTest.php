<?php

namespace Tests\Feature;

use App\Models\CustomerPayoutMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPayoutMethodTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_add_multiple_methods_and_first_is_default(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)->post(route('customer.payout-methods.store'), [
            'label' => 'Main bank',
            'payment_method' => 'bank_transfer',
            'account_title' => 'Test Customer',
            'account_number' => 'PK00TEST123',
            'bank_name' => 'Test Bank',
            'is_default' => '0',
        ])->assertRedirect();

        $this->actingAs($customer)->post(route('customer.payout-methods.store'), [
            'label' => 'Mobile wallet',
            'payment_method' => 'easypaisa',
            'account_title' => 'Test Customer',
            'account_number' => '03001234567',
            'is_default' => '0',
        ])->assertRedirect();

        $this->assertCount(2, $customer->payoutMethods);
        $this->assertTrue($customer->payoutMethods()->where('label', 'Main bank')->firstOrFail()->is_default);
        $this->assertFalse($customer->payoutMethods()->where('label', 'Mobile wallet')->firstOrFail()->is_default);
    }

    public function test_customer_can_select_one_default_method(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $first = CustomerPayoutMethod::create($this->methodData($customer, ['label' => 'First', 'is_default' => true]));
        $second = CustomerPayoutMethod::create($this->methodData($customer, ['label' => 'Second']));

        $this->actingAs($customer)
            ->patch(route('customer.payout-methods.default', $second))
            ->assertRedirect();

        $this->assertFalse($first->refresh()->is_default);
        $this->assertTrue($second->refresh()->is_default);
    }

    public function test_customer_cannot_manage_another_customers_method(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $other = User::factory()->create(['role' => 'customer']);
        $method = CustomerPayoutMethod::create($this->methodData($other));

        $this->actingAs($customer)
            ->delete(route('customer.payout-methods.destroy', $method))
            ->assertNotFound();

        $this->assertNotNull($method->fresh());
    }

    public function test_removing_default_promotes_another_saved_method(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $default = CustomerPayoutMethod::create($this->methodData($customer, ['label' => 'Default', 'is_default' => true]));
        $remaining = CustomerPayoutMethod::create($this->methodData($customer, ['label' => 'Remaining']));

        $this->actingAs($customer)
            ->delete(route('customer.payout-methods.destroy', $default))
            ->assertRedirect();

        $this->assertTrue($remaining->refresh()->is_default);
    }

    private function methodData(User $customer, array $overrides = []): array
    {
        return array_merge([
            'customer_id' => $customer->id,
            'label' => 'Wallet',
            'payment_method' => 'jazzcash',
            'account_title' => $customer->name,
            'account_number' => '03001234567',
            'is_default' => false,
        ], $overrides);
    }
}
