<?php

namespace App\Models;

use App\Models\Concerns\AuditsModelChanges;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passkeys\Contracts\PasskeyUser;
use Laravel\Passkeys\PasskeyAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'father_name', 'email', 'password', 'role', 'referral_code', 'file_no', 'phone', 'cnic', 'address', 'referral_agent_id', 'status', 'theme', 'withdrawal_frequency', 'withdrawal_pin', 'withdrawal_pin_failed_attempts', 'withdrawal_pin_locked_until', 'email_notifications_enabled', 'whatsapp_notifications_enabled'])]
#[Hidden(['password', 'withdrawal_pin', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use AuditsModelChanges, HasFactory, HasRoles, Notifiable, PasskeyAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'withdrawal_pin' => 'hashed',
            'withdrawal_pin_failed_attempts' => 'integer',
            'withdrawal_pin_locked_until' => 'datetime',
            'email_notifications_enabled' => 'boolean',
            'whatsapp_notifications_enabled' => 'boolean',
            'status' => 'boolean',
        ];
    }

    public function referral()
    {
        return $this->hasOne(Referral::class);
    }

    public function customer()
    {
        return $this->hasOne(Customer::class, 'id', 'id');
    }

    public function agentBookings()
    {
        return $this->hasMany(Booking::class, 'agent_id');
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class, 'beneficiary_id');
    }

    public function withdrawalRequests()
    {
        return $this->hasMany(WithdrawalRequest::class, 'customer_id');
    }

    public function payoutMethods()
    {
        return $this->hasMany(CustomerPayoutMethod::class, 'customer_id');
    }
}
