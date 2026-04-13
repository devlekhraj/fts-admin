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
use App\Foundation\Shared\Application\DTO\ActionResult;
use App\Foundation\Shared\Domain\Exceptions\FieldValidationException;
use App\Http\Controllers\Controller;
use App\Mail\AdminBasicUpdatedMail;
use App\Mail\AdminPasswordUpdatedMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

            $admin = $result->data instanceof AdminModel
                ? $result->data
                : AdminModel::with('role')->find($id);

            if ($admin && $admin->email) {
                $roleName = $admin->role->name ?? null;
                $timestamp = now()->timezone(config('app.timezone'))->format('Y-m-d H:i:s');
                try {
                    Mail::to($admin->email)->send(new AdminBasicUpdatedMail(
                        name: $admin->name ?? $admin->username ?? 'Admin',
                        username: $admin->username ?? '',
                        email: $admin->email,
                        role: $roleName,
                        updatedAt: $timestamp,
                        timezone: config('app.timezone'),
                    ));
                } catch (\Throwable $mailException) {
                    Log::error('Failed to send admin basic updated email.', [
                        'admin_id' => $admin->id,
                        'email' => $admin->email,
                        'error' => $mailException->getMessage(),
                    ]);
                }
            }

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

            $admin = $result->data instanceof AdminModel
                ? $result->data
                : AdminModel::query()->find($id);


            if ($admin && $admin->email) {
                $name = $admin->name ?? $admin->username ?? 'Admin';
                $username = $admin->username ?? '';
                $timestamp = now()->timezone(config('app.timezone'))->format('Y-m-d H:i:s');
                try {
                    Mail::to($admin->email)->send(new AdminPasswordUpdatedMail(
                        recipientName: $name,
                        recipientUsername: $username,
                        changedAt: $timestamp,
                        timezone: config('app.timezone'),
                    ));
                } catch (\Throwable $mailException) {
                    Log::error('Failed to send admin password updated email.', [
                        'admin_id' => $admin->id,
                        'email' => $admin->email,
                        'error' => $mailException->getMessage(),
                    ]);
                    // Do not fail the password update because of mail errors.
                }
            }

            $responseResult = new ActionResult(
                success: $result->success,
                message: $result->message,
            );

            return response()->json([
                'message' => $responseResult->message,
                'data' => $responseResult,
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

    public function delete(Request $request, string $id): JsonResponse
    {
        try {
            $admin = AdminModel::query()->find($id);
            if (! $admin) {
                return response()->json([
                    'message' => 'Admin not found.',
                ], 404);
            }

            $admin->delete();

            return response()->json([
                'message' => 'Admin deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete admin.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
