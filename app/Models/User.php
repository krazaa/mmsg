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

#[Fillable(['name', 'father_name', 'email', 'password', 'role', 'referral_code', 'file_no', 'phone', 'cnic', 'address', 'referral_agent_id', 'status', 'theme'])]
#[Hidden(['password', 'remember_token'])]
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
}
