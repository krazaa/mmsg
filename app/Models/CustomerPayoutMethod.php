<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['customer_id', 'label', 'payment_method', 'account_title', 'account_number', 'bank_name', 'network', 'is_default'])]
class CustomerPayoutMethod extends Model
{
    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
