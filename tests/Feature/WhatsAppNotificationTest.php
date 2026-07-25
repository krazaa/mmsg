<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\AccountActivityNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_activity_is_sent_to_the_customers_whatsapp_number(): void
    {
        config()->set('services.whatsapp', [
            'enabled' => true,
            'api_url' => 'https://graph.facebook.com/v23.0',
            'phone_number_id' => '123456789',
            'access_token' => 'test-token',
            'default_country_code' => '92',
        ]);
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.test']]])]);
        $customer = User::factory()->create(['role' => 'customer', 'phone' => '0300-1234567']);

        $customer->notify(new AccountActivityNotification(
            'Booking approved',
            'Your booking has been approved.',
            'booking',
            'https://example.test/dashboard',
            ['Booking' => 'REQ-001']
        ));

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://graph.facebook.com/v23.0/123456789/messages'
                && $request->hasHeader('Authorization', 'Bearer test-token')
                && $request['to'] === '923001234567'
                && $request['type'] === 'text'
                && str_contains($request['text']['body'], 'Booking approved')
                && str_contains($request['text']['body'], 'REQ-001');
        });
        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_account_activity_uses_the_approved_template_when_configured(): void
    {
        config()->set('services.whatsapp', [
            'enabled' => true,
            'api_url' => 'https://graph.facebook.com/v23.0',
            'phone_number_id' => '123456789',
            'access_token' => 'test-token',
            'default_country_code' => '92',
            'notification_template' => 'account_activity_update',
            'notification_template_language' => 'en',
        ]);
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.test']]])]);
        $customer = User::factory()->create(['name' => 'Ali Khan', 'role' => 'customer', 'phone' => '03219802672']);

        $customer->notify(new AccountActivityNotification('Payment verified', 'Your payment was credited.', 'payment', 'https://example.test/dashboard', ['Receipt' => 'RC-001']));

        Http::assertSent(fn (Request $request): bool => $request['type'] === 'template'
            && $request['template']['name'] === 'account_activity_update'
            && $request['template']['language']['code'] === 'en'
            && $request['template']['components'][0]['parameters'][0]['text'] === 'Ali Khan'
            && $request['template']['components'][0]['parameters'][1]['text'] === 'Payment verified'
            && $request['template']['components'][0]['parameters'][3]['text'] === 'Receipt: RC-001');
    }

    public function test_whatsapp_failure_does_not_block_the_database_notification(): void
    {
        config()->set('services.whatsapp', [
            'enabled' => true,
            'api_url' => 'https://graph.facebook.com/v23.0',
            'phone_number_id' => '123456789',
            'access_token' => 'test-token',
            'default_country_code' => '92',
        ]);
        Http::fake(['graph.facebook.com/*' => Http::response(['error' => ['message' => 'Unavailable']], 500)]);
        $customer = User::factory()->create(['role' => 'customer', 'phone' => '03001234567']);

        $customer->notify(new AccountActivityNotification('Payment verified', 'Your payment was verified.', 'payment', 'https://example.test/dashboard'));

        $this->assertDatabaseCount('notifications', 1);
    }
}
