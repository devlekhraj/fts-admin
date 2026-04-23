<?php

declare(strict_types=1);

namespace App\Domains\Admin\Controllers;

use App\Domains\Admin\DTOs\CreateAdminData;
use App\Domains\Admin\DTOs\UpdateAdminEmailData;
use App\Domains\Admin\DTOs\UpdateAdminPasswordData;
use App\Domains\Admin\DTOs\UpdateAdminProfileData;
use App\Domains\Admin\DTOs\UpdateAdminRoleData;
use App\Domains\Admin\Models\Admin;
use App\Domains\Admin\Requests\SendAdminEmailVerificationCodeRequest;
use App\Domains\Admin\Requests\StoreAdminRequest;
use App\Domains\Admin\Requests\UpdateAdminEmailRequest;
use App\Domains\Admin\Requests\UpdateAdminPasswordRequest;
use App\Domains\Admin\Requests\UpdateAdminProfileRequest;
use App\Domains\Admin\Requests\UpdateAdminRoleRequest;
use App\Domains\Admin\Resources\AdminResource;
use App\Domains\Admin\Services\AdminService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class AdminController extends Controller
{
    public function __construct(
        private readonly AdminService $adminService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Admin::query()->with('role')->orderByDesc('created_at');

        if ($search = $request->query('search')) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($roleId = $request->query('role_id')) {
            $query->where('role_id', $roleId);
        }

        $perPageParam = (int) $request->query('per_page', 15);
        if ($perPageParam === -1) {
            $items = $query->get();

            return response()->json([
                'data' => AdminResource::collection($items),
                'meta' => [
                    'current_page' => 1,
                    'per_page' => $items->count(),
                    'total' => $items->count(),
                    'last_page' => 1,
                    'from' => $items->count() > 0 ? 1 : null,
                    'to' => $items->count() > 0 ? $items->count() : null,
                ],
            ]);
        }

        $perPage = max(1, min($perPageParam, 100));
        $paginator = $query->paginate($perPage);

        return response()->json([
            'data' => AdminResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
    }

    public function store(StoreAdminRequest $request): JsonResponse
    {
        try {
            $dto = CreateAdminData::fromArray($request->validated());
            
            $admin = $this->adminService->create($dto);

            return response()->json([
                'success' => true,
                'message' => 'Admin created successfully.',
                'data' => new AdminResource($admin),
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $this->firstValidationMessage($e),
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function updateProfile(UpdateAdminProfileRequest $request, Admin $admin): JsonResponse
    {
        try {
            $dto = UpdateAdminProfileData::fromArray($request->validated());
            $admin = $this->adminService->updateProfile($admin, $dto);

            return response()->json([
                'success' => true,
                'message' => 'Admin profile updated successfully.',
                'data' => new AdminResource($admin),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $this->firstValidationMessage($e),
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function updateRole(UpdateAdminRoleRequest $request, Admin $admin): JsonResponse
    {
        try {
            $dto = UpdateAdminRoleData::fromArray($request->validated());
            $admin = $this->adminService->updateRole($admin, $dto);

            return response()->json([
                'success' => true,
                'message' => 'Admin role updated successfully.',
                'data' => new AdminResource($admin),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $this->firstValidationMessage($e),
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function sendEmailVerificationCode(SendAdminEmailVerificationCodeRequest $request, Admin $admin): JsonResponse
    {
        $data = $request->validated();
        $email = is_string($data['email'] ?? null) ? trim((string) $data['email']) : '';

        $this->adminService->sendEmailVerificationCode($admin, $email);

        return response()->json([
            'success' => true,
            'message' => 'Verification code sent.',
            'data' => null,
        ]);
    }

    public function updateEmail(UpdateAdminEmailRequest $request, Admin $admin): JsonResponse
    {
        try {
            $dto = UpdateAdminEmailData::fromArray($request->validated());
            $admin = $this->adminService->updateEmail($admin, $dto);

            return response()->json([
                'success' => true,
                'message' => 'Admin email updated successfully.',
                'data' => new AdminResource($admin),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $this->firstValidationMessage($e),
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function updatePassword(UpdateAdminPasswordRequest $request, Admin $admin): JsonResponse
    {
        try {
            $dto = UpdateAdminPasswordData::fromArray($request->validated());
            $this->adminService->updatePassword($admin, $dto);

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully.',
                'data' => null,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $this->firstValidationMessage($e),
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function destroy(Admin $admin): JsonResponse
    {
        $this->adminService->delete($admin);

        return response()->json([
            'success' => true,
            'message' => 'Admin deleted successfully.',
            'data' => null,
        ]);
    }

    private function firstValidationMessage(ValidationException $exception): string
    {
        $errors = $exception->errors();

        foreach ($errors as $fieldErrors) {
            if (is_array($fieldErrors) && isset($fieldErrors[0])) {
                return (string) $fieldErrors[0];
            }
        }

        return $exception->getMessage();
    }
}

