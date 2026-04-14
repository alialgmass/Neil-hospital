<?php

namespace Modules\Surgery\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Surgery\Models\OrBed;
use Modules\Surgery\Models\OrRoom;

class OrRoomController extends Controller
{
    public function index(): JsonResponse
    {
        $rooms = OrRoom::with(['beds' => function ($q) {
            $q->orderBy('bed_number');
        }])->orderBy('name')->get();

        return response()->json($rooms);
    }

    public function availableBeds(): JsonResponse
    {
        $beds = OrBed::with('room')
            ->where('status', 'available')
            ->get()
            ->map(fn (OrBed $bed) => [
                'id'     => $bed->id,
                'label'  => "{$bed->room->name} — سرير {$bed->bed_number}",
                'room'   => $bed->room->name,
                'number' => $bed->bed_number,
            ]);

        return response()->json($beds);
    }
}
