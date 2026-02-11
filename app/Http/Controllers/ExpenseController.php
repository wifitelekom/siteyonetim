<?php

namespace App\Http\Controllers;

use App\Http\Requests\MakePaymentRequest;
use App\Http\Requests\StoreExpenseRequest;
use App\Models\Account;
use App\Models\CashAccount;
use App\Models\Expense;
use App\Models\Vendor;
use App\Services\ExpenseService;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function __construct(
        private ExpenseService $expenseService,
        private PaymentService $paymentService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Expense::class);

        $query = Expense::with(['vendor', 'account']);

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        if ($request->filled('from')) {
            $query->where('expense_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('expense_date', '<=', $request->to);
        }
        if ($request->filled('status')) {
            match ($request->status) {
                'unpaid' => $query->unpaid(),
                'partial' => $query->partial(),
                'paid' => $query->paid(),
                default => null,
            };
        }

        $user = auth()->user();
        if ($user->hasRole('vendor') && $user->vendor) {
            $query->where('vendor_id', $user->vendor->id);
        }

        $expenses = $query->orderByDesc('expense_date')->paginate(15);
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();

        return view('expenses.index', compact('expenses', 'vendors'));
    }

    public function create()
    {
        $this->authorize('create', Expense::class);

        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $accounts = Account::where('type', 'expense')->where('is_active', true)->get();

        return view('expenses.create', compact('vendors', 'accounts'));
    }

    public function store(StoreExpenseRequest $request)
    {
        $this->authorize('create', Expense::class);

        $expense = $this->expenseService->createExpense($request->validated());

        return redirect()->route('expenses.show', $expense)
            ->with('success', 'Gider başarıyla oluşturuldu.');
    }

    public function show(Expense $expense)
    {
        $this->authorize('view', $expense);

        $expense->load(['vendor', 'account', 'paymentItems.payment.cashAccount', 'creator']);
        $cashAccounts = CashAccount::where('is_active', true)->get();

        return view('expenses.show', compact('expense', 'cashAccounts'));
    }

    public function pay(MakePaymentRequest $request, Expense $expense)
    {
        $this->authorize('pay', $expense);

        $amount = min($request->validated('amount'), $expense->remaining);
        if ($amount <= 0) {
            return back()->with('error', 'Odenecek kalan tutar bulunmuyor.');
        }

        $this->paymentService->makeSinglePayment(
            $request->validated(),
            $expense,
            $amount
        );

        return redirect()->route('expenses.show', $expense)
            ->with('success', 'Ödeme başarıyla yapıldı.');
    }

    public function destroy(Expense $expense)
    {
        $this->authorize('delete', $expense);

        if (!$this->expenseService->deleteExpense($expense)) {
            return back()->with('error', 'Ödemesi olan gider silinemez.');
        }

        return redirect()->route('expenses.index')
            ->with('success', 'Gider silindi.');
    }
}
