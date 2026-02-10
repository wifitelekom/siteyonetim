<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTemplateAidatRequest;
use App\Models\Account;
use App\Models\Apartment;
use App\Models\TemplateAidat;

class TemplateAidatController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:templates.manage');
    }

    public function index()
    {
        $templates = TemplateAidat::with('account')->get();

        return view('templates.aidat.index', compact('templates'));
    }

    public function create()
    {
        $accounts = Account::where('type', 'income')->where('is_active', true)->get();
        $apartments = Apartment::where('is_active', true)->orderBy('block')->orderBy('number')->get();

        return view('templates.aidat.create', compact('accounts', 'apartments'));
    }

    public function store(StoreTemplateAidatRequest $request)
    {
        $template = TemplateAidat::create($request->safe()->except('apartment_ids'));

        if ($request->scope === 'selected' && $request->has('apartment_ids')) {
            $template->apartments()->sync($request->apartment_ids);
        }

        return redirect()->route('templates.aidat.index')
            ->with('success', 'Aidat şablonu oluşturuldu.');
    }

    public function edit(TemplateAidat $aidat)
    {
        $accounts = Account::where('type', 'income')->where('is_active', true)->get();
        $apartments = Apartment::where('is_active', true)->orderBy('block')->orderBy('number')->get();
        $selectedApartmentIds = $aidat->apartments()->pluck('apartment_id')->toArray();
        $template = $aidat;

        return view('templates.aidat.edit', compact('template', 'accounts', 'apartments', 'selectedApartmentIds'));
    }

    public function update(StoreTemplateAidatRequest $request, TemplateAidat $aidat)
    {
        $aidat->update($request->safe()->except('apartment_ids'));

        if ($request->scope === 'selected' && $request->has('apartment_ids')) {
            $aidat->apartments()->sync($request->apartment_ids);
        } else {
            $aidat->apartments()->detach();
        }

        return redirect()->route('templates.aidat.index')
            ->with('success', 'Aidat şablonu güncellendi.');
    }

    public function destroy(TemplateAidat $aidat)
    {
        $aidat->apartments()->detach();
        $aidat->delete();

        return redirect()->route('templates.aidat.index')
            ->with('success', 'Aidat şablonu silindi.');
    }
}
