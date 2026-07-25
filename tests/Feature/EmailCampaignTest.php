<?php

namespace Tests\Feature;

use App\Jobs\SendEmailCampaignRecipient;
use App\Mail\BulkCampaignMail;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailUnsubscribe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmailCampaignTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_filtered_campaign_with_daily_limit(): void
    {
        $this->seed();
        Queue::fake();
        config()->set('mail.bulk_daily_limit', 1);
        $admin = User::where('email', 'admin@abdullahtown.pk')->firstOrFail();
        $first = User::factory()->create(['role' => 'customer', 'status' => true, 'email' => 'first@example.com']);
        $second = User::factory()->create(['role' => 'customer', 'status' => true, 'email' => 'second@example.com']);
        $optedOut = User::factory()->create(['role' => 'customer', 'status' => true, 'email' => 'opted@example.com']);
        EmailUnsubscribe::create(['email' => $optedOut->email, 'unsubscribed_at' => now()]);

        $this->actingAs($admin)->post(route('email-campaigns.store'), [
            'name' => 'Customer Update',
            'subject' => 'Important project news',
            'body' => '<p>Campaign message</p>',
        ])->assertRedirect();

        $campaign = EmailCampaign::firstOrFail();
        $this->assertSame(2, $campaign->recipient_count);
        $this->assertDatabaseHas('email_campaign_recipients', ['email' => $first->email, 'status' => 'dispatched']);
        $this->assertDatabaseHas('email_campaign_recipients', ['email' => $second->email, 'status' => 'queued']);
        $this->assertDatabaseMissing('email_campaign_recipients', ['email' => $optedOut->email]);
        Queue::assertPushed(SendEmailCampaignRecipient::class, 1);
    }

    public function test_campaign_job_sends_and_records_delivery(): void
    {
        $this->seed();
        Mail::fake();
        $admin = User::where('email', 'admin@abdullahtown.pk')->firstOrFail();
        $customer = User::factory()->create(['role' => 'customer']);
        $campaign = EmailCampaign::create([
            'name' => 'Test', 'subject' => 'Campaign subject', 'body' => '<p>Hello</p>',
            'status' => 'sending', 'recipient_count' => 1, 'created_by' => $admin->id,
        ]);
        $recipient = EmailCampaignRecipient::create([
            'email_campaign_id' => $campaign->id, 'user_id' => $customer->id,
            'name' => $customer->name, 'email' => $customer->email,
            'unsubscribe_token' => fake()->uuid(), 'status' => 'dispatched', 'dispatched_at' => now(),
        ]);

        (new SendEmailCampaignRecipient($recipient->id))->handle();

        Mail::assertSent(BulkCampaignMail::class, fn (BulkCampaignMail $mail) => $mail->hasTo($customer->email));
        $this->assertSame('sent', $recipient->refresh()->status);
        $this->assertNotNull($recipient->sent_at);
        $this->assertSame('completed', $campaign->refresh()->status);
    }

    public function test_customer_can_unsubscribe_from_campaigns(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@abdullahtown.pk')->firstOrFail();
        $customer = User::factory()->create(['role' => 'customer']);
        $campaign = EmailCampaign::create([
            'name' => 'Test', 'subject' => 'Campaign subject', 'body' => 'Hello',
            'recipient_count' => 1, 'created_by' => $admin->id,
        ]);
        $recipient = EmailCampaignRecipient::create([
            'email_campaign_id' => $campaign->id, 'user_id' => $customer->id,
            'name' => $customer->name, 'email' => $customer->email,
            'unsubscribe_token' => fake()->uuid(),
        ]);

        $this->get(route('email-unsubscribe.show', $recipient->unsubscribe_token))->assertOk();
        $this->post(route('email-unsubscribe.store', $recipient->unsubscribe_token))
            ->assertSessionHas('success');
        $this->assertDatabaseHas('email_unsubscribes', ['email' => strtolower($customer->email)]);
    }

    public function test_staff_without_notification_permission_cannot_access_campaigns(): void
    {
        $this->seed();
        $staff = User::factory()->create(['role' => 'staff']);
        $staff->syncPermissions([]);

        $this->actingAs($staff)->get(route('email-campaigns.index'))->assertForbidden();
    }
}
