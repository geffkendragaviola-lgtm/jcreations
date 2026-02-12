<?php

namespace App\Http\Controllers;

use App\Models\AttendanceImportBatch;
use Illuminate\Http\Request;

class AttendanceImportBatchController extends Controller
{
    public function index(Request $request)
    {
        $batches = AttendanceImportBatch::query()
            ->orderByDesc('date_end')
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return response()->json([
            'batches' => $batches->map(function ($b) {
                return [
                    'uuid' => $b->uuid,
                    'source_filename' => $b->source_filename,
                    'date_start' => optional($b->date_start)->format('Y-m-d'),
                    'date_end' => optional($b->date_end)->format('Y-m-d'),
                    'uploaded_at' => optional($b->created_at)->toISOString(),
                ];
            })->values(),
        ]);
    }
}
