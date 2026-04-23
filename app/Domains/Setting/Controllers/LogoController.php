<?php

declare(strict_types=1);

namespace App\Domains\Setting\Controllers;

use App\Domains\Setting\Services\LogoService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class LogoController extends Controller
{
    public function __construct(private readonly LogoService $logoService)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->logoService->list(),
        ]);
    }
}

