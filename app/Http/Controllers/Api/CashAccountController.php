<?php

namespace App\Http\Controllers\Api;

use App\Enums\CashAccountType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCashAccountRequest;
use App\Models\CashAccount;
use App\Services\CashAccountService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CashAccountController extends Controller
{
    public function __construct(
        private CashAccountService $cashAccountService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CashAccount::class);

        $query = CashAccount::query()->withComputedBalance()->orderBy('name');

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->string('search'));
            $query->where('name', 'like', '%' . $search . '%');
        }

        $cashAccounts = $query->paginate(20)->withQueryString();

        return response()->json([
            'data' => $cashAccounts->through(fn (CashAccount $account) => $this->mapCashAccount($account))->items(),
            'meta' => [
                'current_page' => $cashAccounts->currentPage(),
                'last_page' => $cashAccounts->lastPage(),
                'per_page' => $cashAccounts->perPage(),
                'total' => $cashAccounts->total(),
            ],
        ]);
    }

    public function meta(): JsonResponse
    {
        $this->authorize('viewAny', CashAccount::class);

        return response()->json([
            'data' => [
                'types' => collect(CashAccountType::cases())->map(fn (CashAccountType $type) => [
                    'value' => $type->value,
                    'label' => $type->label(),
                ])->values(),
            ],
        ]);
    }

    public function store(StoreCashAccountRequest $request): JsonResponse
    {
        $this->authorize('create', CashAccount::class);

        $payload = $request->validated();
        $payload['is_active'] = (bool) ($payload['is_active'] ?? true);

        $cashAccount = CashAccount::create($payload);

        return response()->json([
            'message' => 'Kasa/Banka hesabi olusturuldu.',
            'data' => $this->mapCashAccount($cashAccount),
        ], 201);
    }

    public function update(StoreCashAccountRequest $request, CashAccount $cashAccount): JsonResponse
    {
        $this->authorize('update', $cashAccount);

        $payload = $request->validated();
        if (array_key_exists('is_active', $payload)) {
            $payload['is_active'] = (bool) $payload['is_active'];
        }

        $cashAccount->update($payload);
        $cashAccount->refresh();

        return response()->json([
            'message' => 'Kasa/Banka hesabi guncellendi.',
            'data' => $this->mapCashAccount($cashAccount),
        ]);
    }

    public function destroy(CashAccount $cashAccount): JsonResponse
    {
        $this->authorize('delete', $cashAccount);

        if ($cashAccount->receipts()->exists() || $cashAccount->payments()->exists()) {
            return response()->json([
                'message' => 'Bu hesaba bagli islemler var, silinemez.',
            ], 422);
        }

        $cashAccount->delete();

        return response()->json([
            'message' => 'Kasa/Banka hesabi silindi.',
        ]);
    }

    public function statement(Request $request, CashAccount $cashAccount): JsonResponse
    {
        $this->authorize('view', $cashAccount);

        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = isset($validated['from'])
            ? Carbon::parse($validated['from'])
            : Carbon::now()->startOfMonth();
        $to = isset($validated['to'])
            ? Carbon::parse($validated['to'])
            : Carbon::now();

        $statement = $this->cashAccountService->getStatement($cashAccount, $from, $to);

        return response()->json([
            'data' => [
                'account' => $this->mapCashAccount($cashAccount),
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'opening_balance' => (float) $statement['opening_balance'],
                'closing_balance' => (float) $statement['closing_balance'],
                'transactions' => collect($statement['transactions'])->map(function (array $transaction) {
                    return [
                        'date' => Carbon::parse((string) $transaction['date'])->toDateString(),
                        'description' => (string) $transaction['description'],
                        'type' => (string) $transaction['type'],
                        'direction' => (string) $transaction['direction'],
                        'receipt_no' => $transaction['receipt_no'],
                        'amount' => (float) $transaction['amount'],
                        'balance' => (float) $transaction['balance'],
                    ];
                })->values(),
            ],
        ]);
    }

    private function mapCashAccount(CashAccount $cashAccount): array
    {
        return [
            'id' => $cashAccount->id,
            'name' => $cashAccount->name,
            'type' => $cashAccount->type?->value ?? (string) $cashAccount->type,
            'type_label' => $cashAccount->type?->label() ?? (string) $cashAccount->type,
            'opening_balance' => (float) $cashAccount->opening_balance,
            'balance' => (float) $cashAccount->balance,
            'is_active' => (bool) $cashAccount->is_active,
        ];
    }
}
