<?php

namespace App\Services;

use App\Models\Charge;
use App\Models\Receipt;
use App\Models\ReceiptItem;
use App\Models\Site;
use Illuminate\Support\Facades\DB;

class ReceiptService
{
    public function __construct(
        private ChargeService $chargeService
    ) {}

    /**
     * Collect payment for one or more charges.
     * $allocations = [['charge_id' => 1, 'amount' => 500.00], ...]
     */
    public function collectPayment(array $data, array $allocations): ?Receipt
    {
        return DB::transaction(function () use ($data, $allocations) {
            $siteId = $data['site_id'] ?? auth()->user()->site_id;
            $receiptNo = $this->generateReceiptNo($siteId);

            $receipt = Receipt::create([
                'site_id' => $siteId,
                'receipt_no' => $receiptNo,
                'apartment_id' => $data['apartment_id'],
                'cash_account_id' => $data['cash_account_id'],
                'paid_at' => $data['paid_at'],
                'method' => $data['method'],
                'total_amount' => 0,
                'description' => $data['description'] ?? null,
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);

            $actualTotal = 0;

            foreach ($allocations as $alloc) {
                $charge = Charge::whereKey($alloc['charge_id'])
                    ->where('site_id', $siteId)
                    ->where('apartment_id', $data['apartment_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $cappedAmount = min((float) $alloc['amount'], (float) $charge->remaining);
                if ($cappedAmount <= 0) {
                    continue;
                }

                ReceiptItem::create([
                    'receipt_id' => $receipt->id,
                    'charge_id' => $charge->id,
                    'amount' => $cappedAmount,
                ]);

                $actualTotal += $cappedAmount;
                $this->chargeService->recalculatePaidAmount($charge);
            }

            if ($actualTotal <= 0) {
                return null;
            }

            $receipt->update(['total_amount' => $actualTotal]);
            DashboardService::clearCache((int) $siteId);

            return $receipt;
        });
    }

    /**
     * Collect payment for a single charge (simplified).
     */
    public function collectSinglePayment(array $data, Charge $charge, float $amount): ?Receipt
    {
        return $this->collectPayment($data, [
            ['charge_id' => $charge->id, 'amount' => $amount],
        ]);
    }

    private function generateReceiptNo(int $siteId): string
    {
        $year = now()->format('Y');
        $prefix = "MKB-{$year}-";

        // Serialize number generation per site within the current transaction.
        Site::query()->whereKey($siteId)->lockForUpdate()->firstOrFail();

        $last = Receipt::withoutGlobalScope('site')
            ->where('site_id', $siteId)
            ->where('receipt_no', 'like', "{$prefix}%")
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('receipt_no');

        $seq = $last ? (int) substr($last, -6) + 1 : 1;

        return sprintf("%s%06d", $prefix, $seq);
    }
}
