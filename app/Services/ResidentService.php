<?php

namespace App\Services;

use App\Enums\ChargeType;
use App\Models\Charge;
use App\Models\Receipt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ResidentService
{
    public function getDetail(User $user): array
    {
        $user->load([
            'apartments' => fn ($q) => $q->orderedForDisplay(),
            'roles',
        ]);

        $apartmentIds = $user->apartments->pluck('id')->toArray();

        $totalCharged = $apartmentIds
            ? Charge::whereIn('apartment_id', $apartmentIds)->sum('amount')
            : 0;

        $totalPaid = $apartmentIds
            ? Charge::whereIn('apartment_id', $apartmentIds)->sum('paid_amount')
            : 0;

        $balance = (float) $totalCharged - (float) $totalPaid;

        $openCharges = $apartmentIds
            ? Charge::whereIn('apartment_id', $apartmentIds)
                ->whereColumn('paid_amount', '<', 'amount')
                ->with('apartment:id,block,floor,number', 'account:id,code,name')
                ->orderBy('due_date')
                ->limit(50)
                ->get()
            : collect();

        return [
            'user' => $user,
            'balance' => $balance,
            'total_charged' => (float) $totalCharged,
            'total_paid' => (float) $totalPaid,
            'open_charges' => $openCharges,
        ];
    }

    public function getStatement(User $user, Carbon $from, Carbon $to): array
    {
        $apartmentIds = $user->apartments()->pluck('apartments.id')->toArray();

        if (empty($apartmentIds)) {
            return [
                'from' => $from,
                'to' => $to,
                'opening_balance' => 0,
                'transactions' => [],
                'closing_balance' => 0,
            ];
        }

        $openingCharged = Charge::whereIn('apartment_id', $apartmentIds)
            ->where('due_date', '<', $from)
            ->sum('amount');

        $openingPaid = Charge::whereIn('apartment_id', $apartmentIds)
            ->where('due_date', '<', $from)
            ->sum('paid_amount');

        $openingBalance = (float) $openingCharged - (float) $openingPaid;

        $charges = Charge::whereIn('apartment_id', $apartmentIds)
            ->whereBetween('due_date', [$from, $to])
            ->with('apartment:id,block,floor,number', 'account:id,code,name')
            ->orderBy('due_date')
            ->get();

        $receipts = Receipt::whereIn('apartment_id', $apartmentIds)
            ->whereBetween('paid_at', [$from, $to])
            ->with('apartment:id,block,floor,number')
            ->orderBy('paid_at')
            ->get();

        $transactions = collect();

        foreach ($charges as $charge) {
            $transactions->push([
                'date' => $charge->due_date->toDateString(),
                'description' => $charge->description ?: ($charge->charge_type?->label() . ' - ' . optional($charge->apartment)->full_label),
                'type' => 'charge',
                'direction' => 'debit',
                'amount' => (float) $charge->amount,
            ]);
        }

        foreach ($receipts as $receipt) {
            $transactions->push([
                'date' => $receipt->paid_at->toDateString(),
                'description' => $receipt->description ?: ('Tahsilat - ' . optional($receipt->apartment)->full_label),
                'type' => 'receipt',
                'direction' => 'credit',
                'receipt_no' => $receipt->receipt_no,
                'amount' => (float) $receipt->total_amount,
            ]);
        }

        $transactions = $transactions->sortBy('date')->values();

        $runningBalance = $openingBalance;
        $transactions = $transactions->map(function (array $tx) use (&$runningBalance) {
            if ($tx['direction'] === 'debit') {
                $runningBalance += $tx['amount'];
            } else {
                $runningBalance -= $tx['amount'];
            }
            $tx['balance'] = $runningBalance;

            return $tx;
        });

        return [
            'from' => $from,
            'to' => $to,
            'opening_balance' => $openingBalance,
            'transactions' => $transactions->values()->toArray(),
            'closing_balance' => $runningBalance,
        ];
    }

    public function addOpeningBalance(User $user, array $data): Charge
    {
        $siteId = $user->site_id;

        return Charge::create([
            'site_id' => $siteId,
            'apartment_id' => $data['apartment_id'],
            'account_id' => $data['account_id'] ?? null,
            'charge_type' => ChargeType::OpeningBalance,
            'period' => $data['period'] ?? now()->format('Y-m'),
            'due_date' => $data['due_date'] ?? now()->toDateString(),
            'amount' => $data['amount'],
            'paid_amount' => 0,
            'description' => $data['description'] ?? 'Acilis bakiyesi',
            'created_by' => auth()->id(),
        ]);
    }

    public function transferDebt(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $siteId = auth()->user()->site_id;
            $period = now()->format('Y-m');
            $today = now()->toDateString();

            $sourceCharge = Charge::create([
                'site_id' => $siteId,
                'apartment_id' => $data['source_apartment_id'],
                'account_id' => $data['account_id'] ?? null,
                'charge_type' => ChargeType::Transfer,
                'period' => $period,
                'due_date' => $today,
                'amount' => 0,
                'paid_amount' => $data['amount'],
                'description' => $data['description'] ?? 'Borc aktarma (cikis)',
                'created_by' => auth()->id(),
            ]);

            $targetCharge = Charge::create([
                'site_id' => $siteId,
                'apartment_id' => $data['target_apartment_id'],
                'account_id' => $data['account_id'] ?? null,
                'charge_type' => ChargeType::Transfer,
                'period' => $period,
                'due_date' => $today,
                'amount' => $data['amount'],
                'paid_amount' => 0,
                'description' => $data['description'] ?? 'Borc aktarma (giris)',
                'created_by' => auth()->id(),
            ]);

            return ['source' => $sourceCharge, 'target' => $targetCharge];
        });
    }

    public function archive(User $user): void
    {
        $user->update(['archived_at' => now()]);
    }

    public function unarchive(User $user): void
    {
        $user->update(['archived_at' => null]);
    }
}
