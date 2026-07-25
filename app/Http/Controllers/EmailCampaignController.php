<?php

namespace App\Http\Controllers;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailUnsubscribe;
use App\Models\PlotPackage;
use App\Models\Project;
use App\Models\User;
use App\Services\EmailCampaignDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmailCampaignController extends Controller
{
    public function index(): View
    {
        $campaigns = EmailCampaign::with('creator')
            ->withCount([
                'recipients',
                'recipients as sent_count' => fn ($query) => $query->where('status', 'sent'),
                'recipients as failed_count' => fn ($query) => $query->where('status', 'failed'),
                'recipients as pending_count' => fn ($query) => $query->whereIn('status', ['queued', 'dispatched', 'sending']),
            ])
            ->latest()
            ->paginate(15);
        $sentToday = EmailCampaignRecipient::whereDate('sent_at', today())->count();
        $dailyLimit = (int) config('mail.bulk_daily_limit', 300);

        return view('email-campaigns.index', compact('campaigns', 'sentToday', 'dailyLimit'));
    }

    public function create(Request $request): View
    {
        $projects = Project::orderBy('name')->get(['id', 'name']);
        $packages = PlotPackage::with('project:id,name')->orderBy('name')->get(['id', 'project_id', 'name']);
        $recipientCount = $this->recipientQuery($request->only(['project_id', 'package_id', 'booking_status']))->count();

        return view('email-campaigns.create', compact('projects', 'packages', 'recipientCount'));
    }

    public function store(Request $request, EmailCampaignDispatcher $dispatcher): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:50000'],
            'project_id' => ['nullable', 'integer', Rule::exists('projects', 'id')],
            'package_id' => ['nullable', 'integer', Rule::exists('plot_packages', 'id')],
            'booking_status' => ['nullable', Rule::in(['pending', 'approved', 'active', 'completed', 'cancelled', 'defaulted'])],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx'],
        ]);

        $filters = collect($data)->only(['project_id', 'package_id', 'booking_status'])->filter()->all();
        $recipients = $this->recipientQuery($filters)->get(['id', 'name', 'email']);
        if ($recipients->isEmpty()) {
            return back()->withInput()->withErrors(['recipients' => 'No subscribed customers match these filters.']);
        }

        $attachmentPath = $request->file('attachment')?->store('email-campaigns', 'local');
        $campaign = DB::transaction(function () use ($data, $filters, $recipients, $attachmentPath, $request) {
            $campaign = EmailCampaign::create([
                'name' => $data['name'],
                'subject' => $data['subject'],
                'body' => $data['body'],
                'attachment_path' => $attachmentPath,
                'attachment_name' => $request->file('attachment')?->getClientOriginalName(),
                'filters' => $filters,
                'recipient_count' => $recipients->count(),
                'created_by' => $request->user()->id,
            ]);

            $now = now();
            EmailCampaignRecipient::insert($recipients->map(fn (User $user) => [
                'email_campaign_id' => $campaign->id,
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => Str::lower($user->email),
                'unsubscribe_token' => (string) Str::uuid(),
                'status' => 'queued',
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all());

            return $campaign;
        });

        $dispatched = $dispatcher->dispatchAvailable();

        return redirect()->route('email-campaigns.show', $campaign)
            ->with('success', "Campaign created for {$campaign->recipient_count} recipients. {$dispatched} emails were added to the delivery queue.");
    }

    public function show(EmailCampaign $emailCampaign): View
    {
        $emailCampaign->load('creator')->loadCount([
            'recipients',
            'recipients as sent_count' => fn ($query) => $query->where('status', 'sent'),
            'recipients as failed_count' => fn ($query) => $query->where('status', 'failed'),
            'recipients as pending_count' => fn ($query) => $query->whereIn('status', ['queued', 'dispatched', 'sending']),
        ]);
        $recipients = $emailCampaign->recipients()->latest()->paginate(25);

        return view('email-campaigns.show', compact('emailCampaign', 'recipients'));
    }

    public function test(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'test_email' => ['required', 'email'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:50000'],
        ]);

        Mail::html($data['body'], fn ($message) => $message->to($data['test_email'])->subject('[TEST] '.$data['subject']));

        return back()->withInput()->with('success', 'Test email sent to '.$data['test_email'].'.');
    }

    public function retry(EmailCampaign $emailCampaign, EmailCampaignDispatcher $dispatcher): RedirectResponse
    {
        $emailCampaign->recipients()->where('status', 'failed')->update([
            'status' => 'queued',
            'dispatched_at' => null,
            'failed_at' => null,
            'failure_reason' => null,
        ]);
        $emailCampaign->update(['status' => 'queued', 'completed_at' => null]);
        $dispatched = $dispatcher->dispatchAvailable();

        return back()->with('success', $dispatched.' failed emails added back to the delivery queue.');
    }

    private function recipientQuery(array $filters)
    {
        $unsubscribed = EmailUnsubscribe::query()->select('email');

        return User::query()
            ->where('role', 'customer')
            ->where('status', true)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->whereNotIn(DB::raw('LOWER(email)'), $unsubscribed)
            ->when($filters['project_id'] ?? null, fn ($query, $id) => $query->whereHas('customer.bookings', fn ($bookings) => $bookings->where('project_id', $id)))
            ->when($filters['package_id'] ?? null, fn ($query, $id) => $query->whereHas('customer.bookings', fn ($bookings) => $bookings->where('package_id', $id)))
            ->when($filters['booking_status'] ?? null, fn ($query, $status) => $query->whereHas('customer.bookings', fn ($bookings) => $bookings->where('status', $status)));
    }
}
