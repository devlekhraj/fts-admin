<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin;

use App\Foundation\Application\AdminIdentity\Commands\CreateAdminCommand;
use App\Foundation\Application\AdminIdentity\Handlers\CreateAdminHandler;
use App\Foundation\Interfaces\Http\Requests\Admin\StoreAdminRequest;
use App\Foundation\Shared\Domain\Exceptions\FieldValidationException;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AdminUserController extends Controller
{
    public function __construct(
        public readonly CreateAdminHandler $createAdminHandler
    ) {}

    public function saveAdmin(StoreAdminRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $result = $this->createAdminHandler->handle(new CreateAdminCommand(
                username: $data['username'],
                name: $data['name'],
                email: $data['email'],
                password: $data['password'],
                roleId: (int) $data['role_id'],
            ));

            return response()->json([
                'message' => $result->message,
                'data' => $result,
            ], 201);
        } catch (FieldValidationException $e) {
            
            $message = $e->getMessage();
            $field = $e->field();
            $errors = $field ? [$field => [$message]] : [];

            return response()->json([
                'message' => $message,
                'errors' => $errors,
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create admin.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
