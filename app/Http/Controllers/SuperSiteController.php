<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSiteRequest;
use App\Http\Requests\UpdateSiteRequest;
use App\Models\Site;
use App\Models\User;
use App\Services\SitePurger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class SuperSiteController extends Controller
{
    public function index(): View
    {
        $sites = Site::with(['users' => function ($query) {
            $query->role('admin');
        }])
            ->latest()
            ->paginate(15);

        return view('super.sites.index', compact('sites'));
    }

    public function create(): View
    {
        $availableAdmins = $this->availableAdmins();

        return view('super.sites.create', compact('availableAdmins'));
    }

    public function store(StoreSiteRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $site = Site::create([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'tax_no' => $data['tax_no'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if (!empty($data['admin_user_id'])) {
            $admin = User::findOrFail($data['admin_user_id']);

            if ($admin->hasRole('super-admin')) {
                return back()->withInput()->with('error', 'Super admin site yoneticisi olarak atanamaz.');
            }

            $admin->update(['site_id' => $site->id]);
            $admin->syncRoles(['admin']);
        } else {
            $admin = User::create([
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
                'site_id' => $site->id,
                'email_verified_at' => now(),
            ]);
            $admin->assignRole('admin');
        }

        return redirect()->route('super.sites.index')
            ->with('success', 'Site olusturuldu ve yonetici atandi.');
    }

    public function edit(Site $site): View
    {
        $availableAdmins = $this->availableAdmins($site->id);
        $currentAdminId = $site->users()->role('admin')->value('id');

        return view('super.sites.edit', compact('site', 'availableAdmins', 'currentAdminId'));
    }

    public function update(UpdateSiteRequest $request, Site $site): RedirectResponse
    {
        $data = $request->validated();

        $site->update([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'tax_no' => $data['tax_no'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if (!empty($data['admin_user_id'])) {
            $admin = User::findOrFail($data['admin_user_id']);

            if ($admin->hasRole('super-admin')) {
                return back()->withInput()->with('error', 'Super admin site yoneticisi olarak atanamaz.');
            }

            if ($admin->site_id && $admin->site_id !== $site->id) {
                return back()->withInput()->with('error', 'Bu kullanici baska bir siteye ait.');
            }

            $admin->update(['site_id' => $site->id]);
            $admin->syncRoles(['admin']);
        } else {
            $wantsNewAdmin = !empty($data['admin_name']) || !empty($data['admin_email']) || !empty($data['admin_password']);

            if ($wantsNewAdmin) {
                $validator = Validator::make($data, [
                    'admin_name' => ['required', 'string', 'max:255'],
                    'admin_email' => ['required', 'email', 'max:255', 'unique:users,email'],
                    'admin_password' => ['required', 'string', 'min:8', 'confirmed'],
                ]);

                if ($validator->fails()) {
                    return back()->withErrors($validator)->withInput();
                }

                $admin = User::create([
                    'name' => $data['admin_name'],
                    'email' => $data['admin_email'],
                    'password' => Hash::make($data['admin_password']),
                    'site_id' => $site->id,
                    'email_verified_at' => now(),
                ]);
                $admin->assignRole('admin');
            }
        }

        return redirect()->route('super.sites.index')
            ->with('success', 'Site guncellendi.');
    }

    public function destroy(Site $site, SitePurger $purger): RedirectResponse
    {
        $purger->purge($site);

        return redirect()->route('super.sites.index')
            ->with('success', 'Site ve bagli kayitlar silindi.');
    }

    private function availableAdmins(?int $siteId = null)
    {
        return User::query()
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'super-admin');
            })
            ->where(function ($query) use ($siteId) {
                $query->whereNull('site_id');
                if ($siteId) {
                    $query->orWhere('site_id', $siteId);
                }
            })
            ->orderBy('name')
            ->get();
    }
}
