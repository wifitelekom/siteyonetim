<?php

namespace App\Http\Controllers\Api;

use App\Enums\ExpenseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\MakePaymentRequest;
use App\Http\Requests\StoreExpenseRequest;
use App\Models\Account;
use App\Models\CashAccount;
use App\Models\Expense;
use App\Models\Vendor;
use App\Services\ExpenseService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function __construct(
        private ExpenseService $expenseService,
        private PaymentService $paymentService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Expense::class);

        $query = Expense::query()->with(['vendor', 'account']);

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->integer('vendor_id'));
        }

        if ($request->filled('from')) {
            $query->whereDate('expense_date', '>=', $request->string('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('expense_date', '<=', $request->string('to'));
        }

        if ($request->filled('status')) {
            match ($request->string('status')->value()) {
                'unpaid' => $query->unpaid(),
                'partial' => $query->partial(),
                'paid' => $query->paid(),
                default => null,
            };
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

        $expenses = $query
            ->orderByDesc('expense_date')
            ->paginate(15)
            ->withQueryString();

        return response()->json([
            'data' => $expenses->through(fn (Expense $expense) => $this->mapExpense($expense))->items(),
            'meta' => [
                'current_page' => $expenses->currentPage(),
                'last_page' => $expenses->lastPage(),
                'per_page' => $expenses->perPage(),
                'total' => $expenses->total(),
            ],
            'filters' => [
                'status' => [
                    ['value' => ExpenseStatus::Unpaid->value, 'label' => 'Odenmedi'],
                    ['value' => ExpenseStatus::Partial->value, 'label' => 'Kismi'],
                    ['value' => ExpenseStatus::Paid->value, 'label' => 'Odendi'],
                ],
            ],
        ]);
    }

    public function meta(): JsonResponse
    {
        $this->authorize('viewAny', Expense::class);

        $vendors = Vendor::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Vendor $vendor) => [
                'id' => $vendor->id,
                'label' => $vendor->name,
            ])->values();

        $accounts = Account::query()
            ->where('type', 'expense')
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(fn (Account $account) => [
                'id' => $account->id,
                'label' => $account->full_name,
            ])->values();

        return response()->json([
            'data' => [
                'vendors' => $vendors,
                'accounts' => $accounts,
            ],
        ]);
    }

    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $this->authorize('create', Expense::class);

        $expense = $this->expenseService->createExpense($request->validated());
        $expense->loadMissing(['vendor', 'account']);

        return response()->json([
            'message' => 'Gider olusturuldu.',
            'data' => $this->mapExpense($expense),
        ], 201);
    }

    public function show(Expense $expense): JsonResponse
    {
        $this->authorize('view', $expense);

        $expense->loadMissing([
            'vendor',
            'account',
            'creator',
            'paymentItems.payment.cashAccount',
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
                ...$this->mapExpense($expense),
                'creator' => $expense->creator ? [
                    'id' => $expense->creator->id,
                    'name' => $expense->creator->name,
                ] : null,
                'payment_items' => $expense->paymentItems->map(function ($item) {
                    $payment = $item->payment;

                    return [
                        'id' => $item->id,
                        'amount' => (float) $item->amount,
                        'payment' => $payment ? [
                            'id' => $payment->id,
                            'paid_at' => optional($payment->paid_at)->format('Y-m-d'),
                            'method' => $payment->method?->value ?? (string) $payment->method,
                            'description' => $payment->description,
                            'cash_account' => $payment->cashAccount ? [
                                'id' => $payment->cashAccount->id,
                                'name' => $payment->cashAccount->name,
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

    public function pay(MakePaymentRequest $request, Expense $expense): JsonResponse
    {
        $this->authorize('pay', $expense);

        $amount = (float) $request->validated('amount');

        $payment = $this->paymentService->makeSinglePayment(
            $request->validated(),
            $expense,
            $amount
        );

        if (!$payment) {
            return response()->json([
                'message' => 'Odenecek kalan tutar bulunmuyor.',
            ], 422);
        }

        $expense->refresh();
        $expense->loadMissing([
            'vendor',
            'account',
            'paymentItems.payment.cashAccount',
        ]);

        return response()->json([
            'message' => 'Odeme basariyla yapildi.',
            'data' => $this->mapExpense($expense),
            'payment' => [
                'id' => $payment->id,
                'total_amount' => (float) $payment->total_amount,
            ],
        ]);
    }

    public function destroy(Expense $expense): JsonResponse
    {
        $this->authorize('delete', $expense);

        if (!$this->expenseService->deleteExpense($expense)) {
            return response()->json([
                'message' => 'Odemesi olan gider silinemez.',
            ], 422);
        }

        return response()->json([
            'message' => 'Gider silindi.',
        ]);
    }

    private function mapExpense(Expense $expense): array
    {
        return [
            'id' => $expense->id,
            'expense_date' => optional($expense->expense_date)->format('Y-m-d'),
            'due_date' => optional($expense->due_date)->format('Y-m-d'),
            'amount' => (float) $expense->amount,
            'paid_amount' => (float) $expense->paid_amount,
            'remaining' => (float) $expense->remaining,
            'description' => $expense->description,
            'status' => $expense->status->value,
            'vendor' => $expense->vendor ? [
                'id' => $expense->vendor->id,
                'name' => $expense->vendor->name,
            ] : null,
            'account' => $expense->account ? [
                'id' => $expense->account->id,
                'name' => $expense->account->full_name,
            ] : null,
        ];
    }
}
