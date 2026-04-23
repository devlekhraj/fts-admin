<?php

declare(strict_types=1);

namespace App\Domains\EmiUser\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class EmiUserController extends Controller
{
    public function index(): JsonResponse
    {
        // TODO: List EMI users.
        return response()->json([]);
    }
}

