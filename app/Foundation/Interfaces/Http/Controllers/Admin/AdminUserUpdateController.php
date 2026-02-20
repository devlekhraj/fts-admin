<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin;

use App\Foundation\Application\AdminIdentity\Commands\UpdateAdminBasicCommand;
use App\Foundation\Application\AdminIdentity\Commands\UpdateAdminPasswordCommand;
use App\Foundation\Application\AdminIdentity\Handlers\UpdateAdminBasicHandler;
use App\Foundation\Application\AdminIdentity\Handlers\UpdateAdminPasswordHandler;
use App\Foundation\Infrastructure\Persistence\Eloquent\Models\AdminModel;
use App\Foundation\Interfaces\Http\Requests\Admin\UpdateAdminBasicRequest;
use App\Foundation\Interfaces\Http\Requests\Admin\UpdateAdminPasswordRequest;
use App\Foundation\Interfaces\Http\Resources\AdminResource;
use App\Foundation\Shared\Domain\Exceptions\FieldValidationException;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;

class AdminUserUpdateController extends Controller
{
    public function __construct(
        public readonly UpdateAdminBasicHandler $updateAdminBasicHandler,
        public readonly UpdateAdminPasswordHandler $updateAdminPasswordHandler,
    ) {}

    public function updateBasic(UpdateAdminBasicRequest $request, string $id): JsonResponse
    {
        try {
            $data = $request->validated();
            $result = $this->updateAdminBasicHandler->handle(new UpdateAdminBasicCommand(
                id: $id,
                username: $data['username'],
                name: $data['name'],
                roleId: (int) $data['role_id'],
            ));

            $admin = AdminModel::query()->with('role')->find($id);

            return response()->json([
                'message' => $result->message,
                'data' => $admin ? new AdminResource($admin) : null,
            ]);
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
                'error' => 'Failed to update admin.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updatePassword(UpdateAdminPasswordRequest $request, string $id): JsonResponse
    {
        try {
            $data = $request->validated();
            $result = $this->updateAdminPasswordHandler->handle(new UpdateAdminPasswordCommand(
                id: $id,
                password: $data['password'],
            ));

            return response()->json([
                'message' => $result->message,
                'data' => $result,
            ]);
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
                'error' => 'Failed to update password.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function sendEmailVerificationCode(Request $request, string $id): JsonResponse
    {
        // TODO: implement send verification code logic.
        return response()->json([
            'message' => 'Not implemented',
            'id' => $id,
        ], 501);
    }

    public function updateEmail(Request $request, string $id): JsonResponse
    {
        // TODO: implement update email logic.
        return response()->json([
            'message' => 'Not implemented',
            'id' => $id,
        ], 501);
    }
}
