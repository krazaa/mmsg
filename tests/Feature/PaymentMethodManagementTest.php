<?php

namespace Tests\Feature;

use App\Models\PaymentGatewaySetting;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentMethodManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_customer_portal_payment_methods(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@abdullahtown.pk')->firstOrFail();

        $this->actingAs($admin)->get(route('payment-methods.index'))
            ->assertOk()
            ->assertSee('Payment method settings')
            ->assertSee('Online Transfer')
            ->assertDontSee('Online payment gateways');

        $this->actingAs($admin)->get(route('payment-gateways.index'))
            ->assertOk()
            ->assertSee('Payment Gateway')
            ->assertSee('JazzCash')
            ->assertSee('Easypaisa')
            ->assertSee('Binance Pay');

        $this->actingAs($admin)->post(route('payment-methods.store'), [
            'name' => 'JazzCash',
            'code' => 'jazzcash',
            'bank_name' => 'Mobilink Microfinance Bank',
            'account_title' => 'Abdullah Town',
            'account_number' => '03001234567',
            'instructions' => 'Use your booking number as the reference.',
            'sort_order' => 40,
            'customer_portal' => 1,
            'status' => 1,
        ])->assertRedirect();

        $method = PaymentMethod::where('code', 'jazzcash')->firstOrFail();
        $this->assertTrue($method->customer_portal);
        $this->assertTrue($method->status);
        $this->assertSame('Mobilink Microfinance Bank', $method->bank_name);
        $this->assertSame('03001234567', $method->account_number);

        $this->actingAs($admin)->put(route('payment-methods.update', $method), [
            'name' => 'JazzCash Wallet',
            'code' => 'jazzcash',
            'bank_name' => 'JazzCash',
            'account_title' => 'MMS Group',
            'account_number' => '03007654321',
            'sort_order' => 5,
            'customer_portal' => 0,
            'status' => 0,
        ])->assertRedirect();

        $this->assertSame('JazzCash Wallet', $method->refresh()->name);
        $this->assertFalse($method->customer_portal);
        $this->assertFalse($method->status);

        $this->actingAs($admin)->put(route('payment-gateways.update', 'jazzcash'), [
            'mode' => 'sandbox',
            'enabled' => 1,
            'merchant_id' => 'merchant-secure',
            'password' => 'password-secure',
            'integrity_salt' => 'salt-secure',
            'api_url' => 'https://sandbox.jazzcash.com.pk/checkout',
            'return_url' => 'https://example.test/payments/jazzcash/return',
        ])->assertRedirect()->assertSessionHas('success');

        $gateway = PaymentGatewaySetting::where('provider', 'jazzcash')->firstOrFail();
        $this->assertTrue($gateway->enabled);
        $this->assertSame('merchant-secure', $gateway->credentials['merchant_id']);
        $this->assertStringNotContainsString('merchant-secure', $gateway->getRawOriginal('credentials'));
    }
}
