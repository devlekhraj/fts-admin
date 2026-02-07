<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class RbacController extends Controller
{
    public function roles(): JsonResponse
    {
        // TODO: List roles.
        return response()->json([]);
    }

    public function permissions(): JsonResponse
    {
        // TODO: List permissions.
        return response()->json([]);
    }
}
