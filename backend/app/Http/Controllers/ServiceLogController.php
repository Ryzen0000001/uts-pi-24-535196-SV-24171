<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceLogRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $logs = $request->user()
            ->serviceLogs()
            ->orderByDesc('serviced_at')
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $logs]);
    }

    public function store(ServiceLogRequest $request): JsonResponse
    {
        $log = $request->user()->serviceLogs()->create($request->validated());

        return response()->json(['data' => $log], 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        return response()->json(['data' => $this->findOwned($request, $id)]);
    }

    public function update(ServiceLogRequest $request, string $id): JsonResponse
    {
        $log = $this->findOwned($request, $id);
        $log->update($request->validated());

        return response()->json(['data' => $log]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->findOwned($request, $id)->delete();

        return response()->json(['message' => 'Catatan dihapus.']);
    }

    /**
     * Cari hanya di catatan milik user yang login,
     * jadi catatan orang lain terbaca sebagai 404.
     */
    private function findOwned(Request $request, string $id)
    {
        return $request->user()->serviceLogs()->findOrFail($id);
    }
}
