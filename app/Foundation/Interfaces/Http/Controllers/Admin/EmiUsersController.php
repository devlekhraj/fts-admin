<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class EmiUsersController extends Controller
{
    public function index(): JsonResponse
    {
        // TODO: List EMI users.
        return response()->json([]);
    }
}
