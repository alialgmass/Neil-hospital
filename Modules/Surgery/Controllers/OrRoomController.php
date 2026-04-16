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
        $dept = request('dept', 'surgery');

        $rooms = OrRoom::with(['beds' => function ($q) use ($dept) {
            $q->orderBy('bed_number')
                ->with(['surgery' => function ($sq) use ($dept) {
                    $sq->where('dept', $dept)->with(['booking:id,file_no,patient_name', 'surgeon:id,name']);
                }]);
        }])->orderBy('name')->get();

        return response()->json($rooms);
    }

    public function availableBeds(): JsonResponse
    {
        $dept = request('dept', 'surgery');

        $beds = OrBed::with(['room', 'surgery' => function ($q) use ($dept) {
            $q->where('dept', $dept)->select('id', 'booking_id', 'dept', 'status');
        }])
            ->where('status', 'available')
            ->orWhere(function ($q) use ($dept) {
                $q->whereHas('surgery', function ($sq) use ($dept) {
                    $sq->where('dept', $dept)->whereIn('status', ['completed', 'cancelled']);
                });
            })
            ->get()
            ->map(fn (OrBed $bed) => [
                'id' => $bed->id,
                'label' => "{$bed->room->name} — سرير {$bed->bed_number}",
                'room' => $bed->room->name,
                'number' => $bed->bed_number,
                'status' => $bed->status,
            ]);

        return response()->json($beds);
    }
}
