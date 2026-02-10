<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTemplateExpenseRequest;
use App\Models\Account;
use App\Models\TemplateExpense;
use App\Models\Vendor;

class TemplateExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:templates.manage');
    }

    public function index()
    {
        $templates = TemplateExpense::with(['vendor', 'account'])->get();

        return view('templates.expense.index', compact('templates'));
    }

    public function create()
    {
        $accounts = Account::where('type', 'expense')->where('is_active', true)->get();
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();

        return view('templates.expense.create', compact('accounts', 'vendors'));
    }

    public function store(StoreTemplateExpenseRequest $request)
    {
        TemplateExpense::create($request->validated());

        return redirect()->route('templates.expense.index')
            ->with('success', 'Gider şablonu oluşturuldu.');
    }

    public function edit(TemplateExpense $expense)
    {
        $accounts = Account::where('type', 'expense')->where('is_active', true)->get();
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $template = $expense;

        return view('templates.expense.edit', compact('template', 'accounts', 'vendors'));
    }

    public function update(StoreTemplateExpenseRequest $request, TemplateExpense $expense)
    {
        $expense->update($request->validated());

        return redirect()->route('templates.expense.index')
            ->with('success', 'Gider şablonu güncellendi.');
    }

    public function destroy(TemplateExpense $expense)
    {
        $expense->delete();

        return redirect()->route('templates.expense.index')
            ->with('success', 'Gider şablonu silindi.');
    }
}
