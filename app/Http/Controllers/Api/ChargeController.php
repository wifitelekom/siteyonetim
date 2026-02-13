<?php

namespace App\Http\Controllers\Api;

use App\Enums\ChargeStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CollectPaymentRequest;
use App\Http\Requests\StoreBulkChargeRequest;
use App\Http\Requests\StoreChargeRequest;
use App\Models\Account;
use App\Models\Apartment;
use App\Models\CashAccount;
use App\Models\Charge;
use App\Services\ChargeService;
use App\Services\ReceiptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChargeController extends Controller
{
    public function __construct(
        private ChargeService $chargeService,
        private ReceiptService $receiptService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Charge::class);

        $query = Charge::query()->with(['apartment', 'account']);

        if ($request->filled('period')) {
            $query->where('period', $request->string('period'));
        }

        if ($request->filled('apartment_id')) {
            $query->where('apartment_id', $request->integer('apartment_id'));
        }

        if ($request->filled('status')) {
            match ($request->string('status')->value()) {
                'overdue' => $query->overdue(),
                'paid' => $query->paid(),
                'open' => $query->open(),
                default => null,
            };
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->string('search'));
            $query->where(function ($nested) use ($search) {
                $nested->where('description', 'like', '%' . $search . '%')
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

        $charges = $query
            ->orderByDesc('due_date')
            ->paginate(15)
            ->withQueryString();

        return response()->json([
            'data' => $charges->through(fn (Charge $charge) => $this->mapCharge($charge))->items(),
            'meta' => [
                'current_page' => $charges->currentPage(),
                'last_page' => $charges->lastPage(),
                'per_page' => $charges->perPage(),
                'total' => $charges->total(),
            ],
            'filters' => [
                'status' => [
                    ['value' => ChargeStatus::Open->value, 'label' => 'Acik'],
                    ['value' => ChargeStatus::Paid->value, 'label' => 'Odendi'],
                    ['value' => ChargeStatus::Overdue->value, 'label' => 'Gecikmis'],
                ],
            ],
        ]);
    }

    public function meta(): JsonResponse
    {
        $this->authorize('viewAny', Charge::class);

        $apartments = Apartment::query()
            ->where('is_active', true)
            ->orderBy('block')
            ->orderBy('number')
            ->get()
            ->map(fn (Apartment $apartment) => [
                'id' => $apartment->id,
                'label' => $apartment->full_label,
            ])->values();

        $accounts = Account::query()
            ->where('type', 'income')
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(fn (Account $account) => [
                'id' => $account->id,
                'label' => $account->full_name,
            ])->values();

        return response()->json([
            'data' => [
                'apartments' => $apartments,
                'accounts' => $accounts,
                'charge_types' => [
                    ['value' => 'aidat', 'label' => 'Aidat'],
                    ['value' => 'other', 'label' => 'Diger'],
                ],
            ],
        ]);
    }

    public function store(StoreChargeRequest $request): JsonResponse
    {
        $this->authorize('create', Charge::class);

        $charge = $this->chargeService->createCharge($request->validated());
        $charge->loadMissing(['apartment', 'account']);

        return response()->json([
            'message' => 'Tahakkuk olusturuldu.',
            'data' => $this->mapCharge($charge),
        ], 201);
    }

    public function storeBulk(StoreBulkChargeRequest $request): JsonResponse
    {
        $this->authorize('create', Charge::class);

        $count = $this->chargeService->createBulkCharges($request->validated());

        return response()->json([
            'message' => "{$count} adet tahakkuk olusturuldu.",
            'data' => [
                'count' => $count,
            ],
        ], 201);
    }

    public function show(Charge $charge): JsonResponse
    {
        $this->authorize('view', $charge);

        $charge->loadMissing([
            'apartment',
            'account',
            'creator',
            'receiptItems.receipt.cashAccount',
        ]);

        $cashAccounts = CashAccount::query()
            ->withComputedBalance()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (CashAccount $account) => [
                'id' => $account->id,
                'name' => $account->name,
                'type' => $account->type?->value ?? (string) $account->type,
                'balance' => (float) $account->balance,
            ])->values();

        return response()->json([
            'data' => [
                ...$this->mapCharge($charge),
                'creator' => $charge->creator ? [
                    'id' => $charge->creator->id,
                    'name' => $charge->creator->name,
                ] : null,
                'receipt_items' => $charge->receiptItems->map(function ($item) {
                    $receipt = $item->receipt;

                    return [
                        'id' => $item->id,
                        'amount' => (float) $item->amount,
                        'receipt' => $receipt ? [
                            'id' => $receipt->id,
                            'receipt_no' => $receipt->receipt_no,
                            'paid_at' => optional($receipt->paid_at)->format('Y-m-d'),
                            'method' => $receipt->method?->value ?? (string) $receipt->method,
                            'description' => $receipt->description,
                            'cash_account' => $receipt->cashAccount ? [
                                'id' => $receipt->cashAccount->id,
                                'name' => $receipt->cashAccount->name,
                            ] : null,
                        ] : null,
                    ];
                })->values(),
            ],
            'meta' => [
                'cash_accounts' => $cashAccounts,
                'payment_methods' => [
                    ['value' => 'cash', 'label' => 'Nakit'],
                    ['value' => 'bank', 'label' => 'Banka'],
                ],
            ],
        ]);
    }

    public function collect(CollectPaymentRequest $request, Charge $charge): JsonResponse
    {
        $this->authorize('collect', $charge);

        $amount = (float) $request->validated('amount');

        $receipt = $this->receiptService->collectSinglePayment(
            array_merge($request->validated(), ['apartment_id' => $charge->apartment_id]),
            $charge,
            $amount
        );

        if (!$receipt) {
            return response()->json([
                'message' => 'Tahsil edilecek kalan tutar bulunmuyor.',
            ], 422);
        }

        $charge->refresh();
        $charge->loadMissing([
            'apartment',
            'account',
            'receiptItems.receipt.cashAccount',
        ]);

        return response()->json([
            'message' => 'Tahsilat basariyla alindi.',
            'data' => $this->mapCharge($charge),
            'receipt' => [
                'id' => $receipt->id,
                'receipt_no' => $receipt->receipt_no,
                'total_amount' => (float) $receipt->total_amount,
            ],
        ]);
    }

    public function destroy(Charge $charge): JsonResponse
    {
        $this->authorize('delete', $charge);

        if (!$this->chargeService->deleteCharge($charge)) {
            return response()->json([
                'message' => 'Odemesi olan tahakkuk silinemez.',
            ], 422);
        }

        return response()->json([
            'message' => 'Tahakkuk silindi.',
        ]);
    }

    private function mapCharge(Charge $charge): array
    {
        return [
            'id' => $charge->id,
            'period' => $charge->period,
            'due_date' => optional($charge->due_date)->format('Y-m-d'),
            'amount' => (float) $charge->amount,
            'paid_amount' => (float) $charge->paid_amount,
            'remaining' => (float) $charge->remaining,
            'description' => $charge->description,
            'status' => $charge->status->value,
            'charge_type' => $charge->charge_type?->value ?? 'aidat',
            'apartment' => $charge->apartment ? [
                'id' => $charge->apartment->id,
                'label' => $charge->apartment->full_label,
            ] : null,
            'account' => $charge->account ? [
                'id' => $charge->account->id,
                'name' => $charge->account->full_name,
            ] : null,
        ];
    }
}
