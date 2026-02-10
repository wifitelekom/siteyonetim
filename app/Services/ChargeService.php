<?php

namespace App\Services;

use App\Models\Charge;
use Illuminate\Support\Facades\DB;

class ChargeService
{
    public function createCharge(array $data): Charge
    {
        return DB::transaction(function () use ($data) {
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
    }

    public function createBulkCharges(array $data): int
    {
        return DB::transaction(function () use ($data) {
            $count = 0;
            $apartmentIds = $data['apartment_ids'];

            foreach ($apartmentIds as $apartmentId) {
                $exists = Charge::where('apartment_id', $apartmentId)
                    ->where('period', $data['period'])
                    ->where('account_id', $data['account_id'])
                    ->exists();

                if (!$exists) {
                    $this->createCharge(array_merge($data, [
                        'apartment_id' => $apartmentId,
                    ]));
                    $count++;
                }
            }

            return $count;
        });
    }

    public function recalculatePaidAmount(Charge $charge): void
    {
        $totalPaid = $charge->receiptItems()->sum('amount');
        $charge->update(['paid_amount' => $totalPaid]);
    }

    public function deleteCharge(Charge $charge): bool
    {
        if ((float) $charge->paid_amount > 0) {
            return false;
        }

        return (bool) $charge->delete();
    }
}
