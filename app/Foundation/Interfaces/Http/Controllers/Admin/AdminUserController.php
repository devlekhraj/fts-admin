<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin;

use App\Foundation\Application\AdminIdentity\Commands\CreateAdminCommand;
use App\Foundation\Application\AdminIdentity\Handlers\CreateAdminHandler;
use App\Foundation\Interfaces\Http\Requests\Admin\StoreAdminRequest;
use App\Foundation\Shared\Domain\Exceptions\FieldValidationException;
use App\Http\Controllers\Controller;
use App\Mail\AdminAccountCreatedMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AdminUserController extends Controller
{
    public function __construct(
        public readonly CreateAdminHandler $createAdminHandler
    ) {}

    public function saveAdmin(StoreAdminRequest $request): JsonResponse
    {
        $data = $request->validated();

        $generatedPassword = Str::random(10);

        try {
            $result = $this->createAdminHandler->handle(new CreateAdminCommand(
                username: $data['username'],
                name: $data['name'],
                email: $data['email'],
                password: $generatedPassword,
                roleId: (int) $data['role_id'],
            ));

            try {
                Mail::to($data['email'])->send(new AdminAccountCreatedMail(
                    name: $data['name'],
                    username: $data['username'],
                    password: $generatedPassword,
                ));
            } catch (\Throwable $mailException) {
                Log::error('Failed to send admin account created email.', [
                    'email' => $data['email'] ?? null,
                    'username' => $data['username'] ?? null,
                    'error' => $mailException->getMessage(),
                ]);
            }

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
