<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use App\Support\WhatsAppMessageTemplates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_whatsapp_status_and_send_a_test_message(): void
    {
        config()->set('services.whatsapp', [
            'enabled' => true,
            'api_url' => 'https://graph.facebook.com/v23.0',
            'phone_number_id' => '123456789',
            'access_token' => 'test-token',
            'default_country_code' => '92',
            'test_template' => 'account_activity_update',
            'test_template_language' => 'en_US',
        ]);
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.test']]])]);
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $this->actingAs($admin)->get(route('management.whatsapp.index'))
            ->assertOk()
            ->assertSee('WhatsApp notifications')
            ->assertSee('Ready')
            ->assertSee('Owner payment alerts')
            ->assertSee('Send a test message');

        $this->actingAs($admin)->put(route('management.whatsapp.owners.update'), [
            'owner_numbers' => "+923001112233\n03002223334",
        ])->assertRedirect()->assertSessionHas('success');
        $this->assertSame(['+923001112233', '03002223334'], SiteSetting::ownerWhatsAppNumbers());

        $templates = WhatsAppMessageTemplates::defaults();
        $templates['customer_booking_approved'] = 'Approved {customer} · {booking} · {url}';
        $this->actingAs($admin)->put(route('management.whatsapp.templates.update'), [
            'templates' => $templates,
        ])->assertRedirect()->assertSessionHas('success');
        $this->assertSame(
            'Approved Ali · BOOK-1 · https://example.test',
            WhatsAppMessageTemplates::render('customer_booking_approved', [
                'customer' => 'Ali',
                'booking' => 'BOOK-1',
                'url' => 'https://example.test',
            ])
        );

        $this->actingAs($admin)->post(route('management.whatsapp.test'), ['phone' => '03001234567'])
            ->assertRedirect()
            ->assertSessionHas('success');

        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request['type'] === 'template'
            && $request['template']['name'] === 'account_activity_update'
            && $request['template']['language']['code'] === 'en_US');
    }
}
