<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Customer extends User
{
    protected $table = 'users';

    protected static function booted(): void
    {
        static::addGlobalScope('customer_role', fn (Builder $query) => $query->where('role', 'customer'));
        static::creating(function (Customer $customer) {
            $customer->role = 'customer';
            $customer->email ??= 'customer-'.Str::lower(Str::random(12)).'@mmsgroup.pk';
            $customer->password ??= Str::random(40);
            if (! $customer->referral_agent_id) {
                $customer->referral_agent_id = User::query()
                    ->where('referral_code', 'DIRECT-SALES')
                    ->orWhereIn('email', ['direct-sales@abdullahtown.pk', 'direct-sales@mmsgroup.pk'])
                    ->value('id');
            }
            if (! $customer->referral_code) {
                do {
                    $code = 'MMSG-'.Str::upper(Str::random(8));
                } while (User::where('referral_code', $code)->exists());
                $customer->referral_code = $code;
            }
        });
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'customer_id');
    }

    public function latestBooking()
    {
        return $this->hasOne(Booking::class, 'customer_id')->latestOfMany('booking_date');
    }

    public function referral()
    {
        return $this->hasOne(Referral::class, 'user_id');
    }

    public function referralAgent()
    {
        return $this->belongsTo(User::class, 'referral_agent_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'customer_id');
    }
}
