<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

/**
 * Health check endpoint — GET /api/v1/health
 *
 * Public, no authentication required (CA-01.3, T-01).
 * Returns {"status":"ok"} with HTTP 200.
 */
class HealthController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['status' => 'ok']);
    }
}
