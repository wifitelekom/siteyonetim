<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\Charge;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $q = $request->input('q', '');

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $results = [];

        // Search apartments
        $apartments = Apartment::where('number', 'like', "%{$q}%")
            ->orWhere('block', 'like', "%{$q}%")
            ->limit(5)
            ->get();

        foreach ($apartments as $apt) {
            $results[] = [
                'group' => 'Daireler',
                'label' => $apt->full_label,
                'url' => route('management.apartments.show', $apt),
                'icon' => 'bi-door-open',
                'color' => 'bg-accent',
            ];
        }

        // Search users
        $users = User::where('name', 'like', "%{$q}%")
            ->orWhere('email', 'like', "%{$q}%")
            ->limit(5)
            ->get();

        foreach ($users as $user) {
            $results[] = [
                'group' => 'Kullanıcılar',
                'label' => $user->name,
                'url' => route('management.users.show', $user),
                'icon' => 'bi-person',
                'color' => 'bg-info-soft',
            ];
        }

        // Search vendors
        $vendors = Vendor::where('name', 'like', "%{$q}%")
            ->orWhere('tax_no', 'like', "%{$q}%")
            ->limit(5)
            ->get();

        foreach ($vendors as $vendor) {
            $results[] = [
                'group' => 'Tedarikçiler',
                'label' => $vendor->name,
                'url' => route('management.vendors.show', $vendor),
                'icon' => 'bi-truck',
                'color' => 'bg-warning-soft',
            ];
        }

        // Search charges by receipt_no or description
        $charges = Charge::where('description', 'like', "%{$q}%")
            ->orWhere('period', 'like', "%{$q}%")
            ->limit(5)
            ->get();

        foreach ($charges as $charge) {
            $results[] = [
                'group' => 'Tahakkuklar',
                'label' => ($charge->apartment?->full_label ?? '') . ' - ' . $charge->period,
                'url' => route('charges.show', $charge),
                'icon' => 'bi-cash-stack',
                'color' => 'bg-success-soft',
            ];
        }

        return response()->json($results);
    }
}
