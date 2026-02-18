<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChargeResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\ResidentService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ResidentController extends Controller
{
    public function __construct(
        private ResidentService $residentService
    ) {}

    public function show(Request $request, User $user): JsonResponse
    {
        $this->authorize('view', $user);

        $detail = $this->residentService->getDetail($user);

        return response()->json([
            'data' => [
                ...(new UserResource($detail['user']))->resolve(),
                'balance' => $detail['balance'],
                'total_charged' => $detail['total_charged'],
                'total_paid' => $detail['total_paid'],
                'open_charges' => ChargeResource::collection($detail['open_charges'])->resolve(),
            ],
        ]);
    }

    public function statement(Request $request, User $user): JsonResponse
    {
        $this->authorize('view', $user);

        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $from = isset($validated['from']) ? Carbon::parse($validated['from']) : now()->startOfYear();
        $to = isset($validated['to']) ? Carbon::parse($validated['to']) : now();

        $data = $this->residentService->getStatement($user, $from, $to);

        return response()->json([
            'data' => [
                'from' => $data['from']->toDateString(),
                'to' => $data['to']->toDateString(),
                'opening_balance' => (float) $data['opening_balance'],
                'closing_balance' => (float) $data['closing_balance'],
                'transactions' => $data['transactions'],
            ],
        ]);
    }

    public function openingBalance(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $siteId = $request->user()->site_id;
        $validated = $request->validate([
            'apartment_id' => [
                'required',
                Rule::exists('apartments', 'id')
                    ->where(fn ($q) => $q->where('site_id', $siteId)->whereNull('deleted_at')),
            ],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'due_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $charge = $this->residentService->addOpeningBalance($user, $validated);

        return response()->json([
            'message' => 'Acilis bakiyesi eklendi.',
            'data' => new ChargeResource($charge),
        ], 201);
    }

    public function transferDebt(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('charges.create'), 403);

        $siteId = $request->user()->site_id;
        $validated = $request->validate([
            'source_apartment_id' => [
                'required',
                Rule::exists('apartments', 'id')
                    ->where(fn ($q) => $q->where('site_id', $siteId)->whereNull('deleted_at')),
            ],
            'target_apartment_id' => [
                'required',
                'different:source_apartment_id',
                Rule::exists('apartments', 'id')
                    ->where(fn ($q) => $q->where('site_id', $siteId)->whereNull('deleted_at')),
            ],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $this->residentService->transferDebt($validated);

        return response()->json([
            'message' => 'Borc aktarma islemi tamamlandi.',
        ]);
    }

    public function archive(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $this->residentService->archive($user);

        return response()->json([
            'message' => 'Kullanici arsivlendi.',
        ]);
    }

    public function unarchive(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $this->residentService->unarchive($user);

        return response()->json([
            'message' => 'Kullanici arsivden cikarildi.',
        ]);
    }
}
