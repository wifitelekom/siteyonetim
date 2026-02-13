<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Payment::class);

        $query = Payment::query()->with(['vendor', 'cashAccount']);

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->integer('vendor_id'));
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
                $nested->where('description', 'like', '%' . $search . '%')
                    ->orWhereHas('vendor', fn ($vendorQuery) => $vendorQuery->where('name', 'like', '%' . $search . '%'));
            });
        }

        $user = $request->user();
        if ($user->hasRole('vendor') && $user->vendor) {
            $query->where('vendor_id', $user->vendor->id);
        }

        $payments = $query
            ->orderByDesc('paid_at')
            ->paginate(15)
            ->withQueryString();

        return response()->json([
            'data' => $payments->through(fn (Payment $payment) => $this->mapPayment($payment))->items(),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
        ]);
    }

    public function meta(): JsonResponse
    {
        $this->authorize('viewAny', Payment::class);

        $vendors = Vendor::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Vendor $vendor) => [
                'id' => $vendor->id,
                'label' => $vendor->name,
            ])->values();

        return response()->json([
            'data' => [
                'vendors' => $vendors,
            ],
        ]);
    }

    public function show(Payment $payment): JsonResponse
    {
        $this->authorize('view', $payment);

        $payment->loadMissing([
            'vendor',
            'cashAccount',
            'creator',
            'items.expense.account',
        ]);

        return response()->json([
            'data' => [
                ...$this->mapPayment($payment),
                'creator' => $payment->creator ? [
                    'id' => $payment->creator->id,
                    'name' => $payment->creator->name,
                ] : null,
                'items' => $payment->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'amount' => (float) $item->amount,
                        'expense' => $item->expense ? [
                            'id' => $item->expense->id,
                            'description' => $item->expense->description,
                            'account' => $item->expense->account ? [
                                'id' => $item->expense->account->id,
                                'name' => $item->expense->account->full_name,
                            ] : null,
                        ] : null,
                    ];
                })->values(),
            ],
        ]);
    }

    private function mapPayment(Payment $payment): array
    {
        return [
            'id' => $payment->id,
            'paid_at' => optional($payment->paid_at)->format('Y-m-d'),
            'method' => $payment->method?->value ?? (string) $payment->method,
            'total_amount' => (float) $payment->total_amount,
            'description' => $payment->description,
            'vendor' => $payment->vendor ? [
                'id' => $payment->vendor->id,
                'name' => $payment->vendor->name,
            ] : null,
            'cash_account' => $payment->cashAccount ? [
                'id' => $payment->cashAccount->id,
                'name' => $payment->cashAccount->name,
            ] : null,
        ];
    }
}
