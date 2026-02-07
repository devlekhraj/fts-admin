<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class EmiUsersController extends Controller
{
    public function index(): JsonResponse
    {
        // TODO: List EMI users.
        return response()->json([]);
    }
}
