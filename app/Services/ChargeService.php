<?php

namespace App\Services;

use App\Models\Charge;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class ChargeService
{
    public function createCharge(array $data): Charge
    {
        $charge = DB::transaction(function () use ($data) {
            return Charge::create([
                'site_id' => $data['site_id'] ?? auth()->user()->site_id,
                'apartment_id' => $data['apartment_id'],
                'account_id' => $data['account_id'],
                'charge_type' => $data['charge_type'] ?? 'aidat',
                'period' => $data['period'],
                'due_date' => $data['due_date'],
                'amount' => $data['amount'],
                'description' => $data['description'] ?? null,
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);
        });

        DashboardService::clearCache((int) $charge->site_id);

        return $charge;
    }

    public function createBulkCharges(array $data): int
    {
        $count = DB::transaction(function () use ($data) {
            $count = 0;
            $apartmentIds = $data['apartment_ids'];

            foreach ($apartmentIds as $apartmentId) {
                $exists = Charge::where('apartment_id', $apartmentId)
                    ->where('period', $data['period'])
                    ->where('account_id', $data['account_id'])
                    ->lockForUpdate()
                    ->exists();

                if (!$exists) {
                    try {
                        Charge::create([
                            'site_id' => $data['site_id'] ?? auth()->user()->site_id,
                            'apartment_id' => $apartmentId,
                            'account_id' => $data['account_id'],
                            'charge_type' => $data['charge_type'] ?? 'aidat',
                            'period' => $data['period'],
                            'due_date' => $data['due_date'],
                            'amount' => $data['amount'],
                            'description' => $data['description'] ?? null,
                            'created_by' => $data['created_by'] ?? auth()->id(),
                        ]);
                        $count++;
                    } catch (UniqueConstraintViolationException) {
                        // Duplicate charge already exists, skip silently
                    }
                }
            }

            return $count;
        });

        if ($count > 0) {
            $siteId = (int) ($data['site_id'] ?? auth()->user()?->site_id ?? 0);
            DashboardService::clearCache($siteId);
        }

        return $count;
    }

    public function recalculatePaidAmount(Charge $charge): void
    {
        $totalPaid = $charge->receiptItems()->sum('amount');
        $charge->update(['paid_amount' => $totalPaid]);
    }

    public function updateCharge(Charge $charge, array $data): Charge
    {
        $updatedCharge = DB::transaction(function () use ($charge, $data) {
            $charge->update([
                'apartment_id' => $data['apartment_id'] ?? $charge->apartment_id,
                'account_id' => $data['account_id'] ?? $charge->account_id,
                'charge_type' => $data['charge_type'] ?? ($charge->charge_type?->value ?? 'aidat'),
                'period' => $data['period'] ?? $charge->period,
                'due_date' => $data['due_date'] ?? optional($charge->due_date)->toDateString(),
                'amount' => $data['amount'] ?? $charge->amount,
                'description' => array_key_exists('description', $data) ? $data['description'] : $charge->description,
            ]);

            return $charge->fresh();
        });

        DashboardService::clearCache((int) $updatedCharge->site_id);

        return $updatedCharge;
    }

    public function deleteCharge(Charge $charge): bool
    {
        if ((float) $charge->paid_amount > 0) {
            return false;
        }

        $deleted = (bool) $charge->delete();

        if ($deleted) {
            DashboardService::clearCache((int) $charge->site_id);
        }

        return $deleted;
    }
}
