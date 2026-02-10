<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSiteSettingsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SiteSettingsController extends Controller
{
    public function edit(): View
    {
        $site = auth()->user()->site;

        abort_if(!$site, 404);

        return view('management.site-settings.edit', compact('site'));
    }

    public function update(UpdateSiteSettingsRequest $request): RedirectResponse
    {
        $site = auth()->user()->site;

        abort_if(!$site, 404);

        $site->update($request->validated());

        return redirect()->route('management.site-settings.edit')
            ->with('success', 'Site ayarlari guncellendi.');
    }
}

