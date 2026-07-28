<?php

namespace App\Models;

use App\Models\Concerns\AuditsModelChanges;
use App\Models\Concerns\TracksUserstamps;
use Illuminate\Database\Eloquent\Model;

class PlotPackage extends Model
{
    use AuditsModelChanges, TracksUserstamps;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['size_marla' => 'decimal:2', 'cash_price' => 'decimal:2', 'booking_amount' => 'decimal:2', 'monthly_amount' => 'decimal:2', 'month_12_balloon' => 'decimal:2', 'month_24_balloon' => 'decimal:2', 'month_36_balloon' => 'decimal:2', 'balloon_payments' => 'array', 'status' => 'boolean'];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'package_id');
    }

    public function commissionRules()
    {
        return $this->hasMany(CommissionRule::class, 'package_id');
    }

    public function getTotalPriceAttribute(): float
    {
        return (float) $this->booking_amount
            + ($this->months * (float) $this->monthly_amount)
            + collect($this->balloonPayments())->sum('amount');
    }

    public function getEffectiveCashPriceAttribute(): float
    {
        return $this->cash_price !== null ? (float) $this->cash_price : $this->total_price;
    }

    public function offersCash(): bool
    {
        return in_array($this->payment_plan_options, ['cash', 'both'], true) && $this->cash_price !== null;
    }

    public function offersInstallments(): bool
    {
        return in_array($this->payment_plan_options, ['installment', 'both'], true);
    }

    public function balloonPayments(): array
    {
        if ($this->balloon_payments !== null) {
            return collect($this->balloon_payments)
                ->map(fn (array $payment) => ['month' => (int) $payment['month'], 'amount' => (float) $payment['amount']])
                ->sortBy('month')->values()->all();
        }

        return collect([
            ['month' => 12, 'amount' => (float) $this->month_12_balloon],
            ['month' => 24, 'amount' => (float) $this->month_24_balloon],
            ['month' => 36, 'amount' => (float) $this->month_36_balloon],
        ])->filter(fn (array $payment) => $payment['month'] <= $this->months && $payment['amount'] > 0)->values()->all();
    }
}
