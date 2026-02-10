<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Models\Account;

class AccountController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Account::class);

        $accounts = Account::orderBy('code')->get();

        return view('accounts.index', compact('accounts'));
    }

    public function create()
    {
        $this->authorize('create', Account::class);

        return view('accounts.create');
    }

    public function store(StoreAccountRequest $request)
    {
        $this->authorize('create', Account::class);

        Account::create($request->validated());

        return redirect()->route('accounts.index')
            ->with('success', 'Hesap başarıyla oluşturuldu.');
    }

    public function edit(Account $account)
    {
        $this->authorize('update', $account);

        return view('accounts.edit', compact('account'));
    }

    public function update(StoreAccountRequest $request, Account $account)
    {
        $this->authorize('update', $account);

        $account->update($request->validated());

        return redirect()->route('accounts.index')
            ->with('success', 'Hesap güncellendi.');
    }

    public function destroy(Account $account)
    {
        $this->authorize('delete', $account);

        if ($account->charges()->exists() || $account->expenses()->exists()) {
            return back()->with('error', 'Bu hesaba bağlı işlemler var, silinemez.');
        }

        $account->delete();

        return redirect()->route('accounts.index')
            ->with('success', 'Hesap silindi.');
    }
}
