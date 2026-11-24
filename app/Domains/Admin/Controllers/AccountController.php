<?php

declare(strict_types=1);

namespace App\Domains\Admin\Controllers;

use App\Domains\Admin\Actions\UpdateAdminPasswordAction;
use App\Domains\Admin\DTOs\UpdateAdminPasswordData;
use App\Domains\Admin\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * What every signed-in admin may do with their OWN account, whatever their role: change the name and the password.
 * Changing somebody else's account stays with the team screens (and their permissions).
 */
final class AccountController extends Controller
{
    public function __construct(private readonly UpdateAdminPasswordAction $passwordAction) {}

    public function updateProfile(Request $request): JsonResponse
    {
        $admin = $this->me();

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
        ]);

        $admin->name = trim($validated['name']);
        $admin->save();

        return response()->json([
            'success' => true,
            'message' => 'Your name was updated.',
            'data' => ['name' => $admin->name],
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $admin = $this->me();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'max:128'],
            'confirm_password' => ['required', 'same:password'],
        ], [
            'confirm_password.same' => 'The new password and its confirmation do not match.',
        ]);

        if (! Hash::check($validated['current_password'], (string) $admin->password)) {
            Log::notice('Own password change refused: wrong current password', ['admin_id' => $admin->id]);
            throw ValidationException::withMessages(['current_password' => ['Your current password is not correct.']]);
        }

        if (hash_equals($validated['current_password'], $validated['password'])) {
            throw ValidationException::withMessages(['password' => ['Choose a password that is different from the current one.']]);
        }

        $this->passwordAction->execute($admin, UpdateAdminPasswordData::fromArray(['password' => $validated['password']]));
        Log::notice('Own password changed', ['admin_id' => $admin->id]);

        return response()->json([
            'success' => true,
            'message' => 'Your password was changed.',
            'data' => null,
        ]);
    }

    private function me(): Admin
    {
        $admin = auth('admin_api')->user();
        abort_unless($admin instanceof Admin, 401, 'Unauthenticated');

        return $admin;
    }
}
