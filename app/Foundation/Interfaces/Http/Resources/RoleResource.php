<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray($request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name ?? null,
            'permissions' => $this->permissions ?? [],
        ];

        // Only include permissions for role detail responses.
        $isDetail = (bool) $request->route('role')
            || (bool) $request->route('id')
            || ($request->route() && $request->route()->getName() === 'admin.rbac.roles.show');

        if ($isDetail) {
            $data['permissions'] = $this->permissions ?? [];
        }

        return $data;
    }
}
