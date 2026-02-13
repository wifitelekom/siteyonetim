<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\Receipt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Receipt::class);

        $query = Receipt::query()->with(['apartment', 'cashAccount']);

        if ($request->filled('apartment_id')) {
            $query->where('apartment_id', $request->integer('apartment_id'));
        }

        if ($request->filled('from')) {
            $query->whereDate('paid_at', '>=', $request->string('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('paid_at', '<=', $request->string('to'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->string('search'));
            $query->where(function ($nested) use ($search) {
                $nested->where('receipt_no', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhereHas('apartment', function ($apartmentQuery) use ($search) {
                        $apartmentQuery->where('block', 'like', '%' . $search . '%')
                            ->orWhere('number', 'like', '%' . $search . '%');
                    });
            });
        }

        $user = $request->user();
        if ($user->hasAnyRole(['owner', 'tenant'])) {
            $query->whereIn('apartment_id', $user->apartment_ids);
        }

        $receipts = $query
            ->orderByDesc('paid_at')
            ->paginate(15)
            ->withQueryString();

        return response()->json([
            'data' => $receipts->through(fn (Receipt $receipt) => $this->mapReceipt($receipt))->items(),
            'meta' => [
                'current_page' => $receipts->currentPage(),
                'last_page' => $receipts->lastPage(),
                'per_page' => $receipts->perPage(),
                'total' => $receipts->total(),
            ],
        ]);
    }

    public function meta(): JsonResponse
    {
        $this->authorize('viewAny', Receipt::class);

        $apartments = Apartment::query()
            ->where('is_active', true)
            ->orderBy('block')
            ->orderBy('number')
            ->get()
            ->map(fn (Apartment $apartment) => [
                'id' => $apartment->id,
                'label' => $apartment->full_label,
            ])->values();

        return response()->json([
            'data' => [
                'apartments' => $apartments,
            ],
        ]);
    }

    public function show(Receipt $receipt): JsonResponse
    {
        $this->authorize('view', $receipt);

        $receipt->loadMissing([
            'apartment',
            'cashAccount',
            'creator',
            'items.charge.apartment',
            'items.charge.account',
        ]);

        return response()->json([
            'data' => [
                ...$this->mapReceipt($receipt),
                'creator' => $receipt->creator ? [
                    'id' => $receipt->creator->id,
                    'name' => $receipt->creator->name,
                ] : null,
                'items' => $receipt->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'amount' => (float) $item->amount,
                        'charge' => $item->charge ? [
                            'id' => $item->charge->id,
                            'period' => $item->charge->period,
                            'description' => $item->charge->description,
                            'apartment' => $item->charge->apartment ? [
                                'id' => $item->charge->apartment->id,
                                'label' => $item->charge->apartment->full_label,
                            ] : null,
                            'account' => $item->charge->account ? [
                                'id' => $item->charge->account->id,
                                'name' => $item->charge->account->full_name,
                            ] : null,
                        ] : null,
                    ];
                })->values(),
            ],
        ]);
    }

    private function mapReceipt(Receipt $receipt): array
    {
        return [
            'id' => $receipt->id,
            'receipt_no' => $receipt->receipt_no,
            'paid_at' => optional($receipt->paid_at)->format('Y-m-d'),
            'method' => $receipt->method?->value ?? (string) $receipt->method,
            'total_amount' => (float) $receipt->total_amount,
            'description' => $receipt->description,
            'apartment' => $receipt->apartment ? [
                'id' => $receipt->apartment->id,
                'label' => $receipt->apartment->full_label,
            ] : null,
            'cash_account' => $receipt->cashAccount ? [
                'id' => $receipt->cashAccount->id,
                'name' => $receipt->cashAccount->name,
            ] : null,
        ];
    }
}
