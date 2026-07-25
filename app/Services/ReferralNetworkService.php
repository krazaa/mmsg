<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Referral;
use Illuminate\Support\Collection;

class ReferralNetworkService
{
    public function downline(int $customerId, int $maximumLevels = 3): Collection
    {
        $result = collect();
        $sponsors = [$customerId];
        $seen = [$customerId];

        for ($level = 1; $level <= $maximumLevels && $sponsors; $level++) {
            $userIds = Referral::whereIn('sponsor_id', $sponsors)->whereNotIn('user_id', $seen)->pluck('user_id')->all();
            if (! $userIds) {
                break;
            }

            $users = $this->customers($userIds);
            foreach ($users as $user) {
                $result->push(['level' => $level, 'user' => $user]);
                $seen[] = $user->id;
            }
            $sponsors = $users->pluck('id')->all();
        }

        return $result;
    }

    public function tree(int $sponsorId, int $level = 1, array $seen = [], int $maximumLevels = 3): array
    {
        if ($level > $maximumLevels || in_array($sponsorId, $seen, true)) {
            return [];
        }

        $seen[] = $sponsorId;
        $ids = Referral::where('sponsor_id', $sponsorId)->whereNotIn('user_id', $seen)->pluck('user_id');

        return $this->customers($ids)->map(fn (Customer $user) => [
            'user' => $user,
            'level' => $level,
            'children' => $this->tree($user->id, $level + 1, $seen, $maximumLevels),
        ])->all();
    }

    private function customers(iterable $ids): Collection
    {
        return Customer::whereIn('id', $ids)
            ->with([
                'bookings' => fn ($query) => $query
                    ->with(['project:id,name', 'package:id,name', 'installments:id,booking_id,status'])
                    ->latest('booking_date'),
            ])
            ->withCount('bookings')
            ->withSum(['commissions as payable_commission' => fn ($query) => $query->where('status', 'earned')], 'amount')
            ->orderBy('name')->get();
    }
}
