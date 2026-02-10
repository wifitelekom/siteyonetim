<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCashAccountRequest;
use App\Models\CashAccount;
use App\Services\CashAccountService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CashAccountController extends Controller
{
    public function __construct(
        private CashAccountService $cashAccountService
    ) {}

    public function index()
    {
        $this->authorize('viewAny', CashAccount::class);

        $cashAccounts = CashAccount::all()->map(function ($account) {
            $account->current_balance = $account->balance;
            return $account;
        });

        return view('cash-accounts.index', compact('cashAccounts'));
    }

    public function create()
    {
        $this->authorize('create', CashAccount::class);

        return view('cash-accounts.create');
    }

    public function store(StoreCashAccountRequest $request)
    {
        $this->authorize('create', CashAccount::class);

        CashAccount::create($request->validated());

        return redirect()->route('cash-accounts.index')
            ->with('success', 'Kasa/Banka hesabı oluşturuldu.');
    }

    public function edit(CashAccount $cashAccount)
    {
        $this->authorize('update', $cashAccount);

        return view('cash-accounts.edit', compact('cashAccount'));
    }

    public function update(StoreCashAccountRequest $request, CashAccount $cashAccount)
    {
        $this->authorize('update', $cashAccount);

        $cashAccount->update($request->validated());

        return redirect()->route('cash-accounts.index')
            ->with('success', 'Kasa/Banka hesabı güncellendi.');
    }

    public function destroy(CashAccount $cashAccount)
    {
        $this->authorize('delete', $cashAccount);

        if ($cashAccount->receipts()->exists() || $cashAccount->payments()->exists()) {
            return back()->with('error', 'Bu hesaba bağlı işlemler var, silinemez.');
        }

        $cashAccount->delete();

        return redirect()->route('cash-accounts.index')
            ->with('success', 'Kasa/Banka hesabı silindi.');
    }

    public function statement(Request $request, CashAccount $cashAccount)
    {
        $this->authorize('view', $cashAccount);

        $from = $request->filled('from') ? Carbon::parse($request->from) : Carbon::now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->to) : Carbon::now();

        $statement = $this->cashAccountService->getStatement($cashAccount, $from, $to);

        return view('cash-accounts.statement', $statement);
    }
}
