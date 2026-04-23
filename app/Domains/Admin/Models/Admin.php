<?php

declare(strict_types=1);

namespace App\Domains\Admin\Models;

use App\Models\Concerns\HasActivityLogs;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

final class Admin extends Authenticatable implements JWTSubject
{
    use SoftDeletes, HasActivityLogs;

    private array $pendingActivityDiffs = [];

    protected $table = 'admins';

    protected $keyType = 'int';

    public $incrementing = true;

    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'avatar',
        'role_id',
    ];

    protected $hidden = ['password'];

    protected $appends = ['avatar_url'];

    private static function activityActor(): ?self
    {
        try {
            $actor = auth('admin_api')->user();
        } catch (\Throwable) {
            return null;
        }

        return $actor instanceof self ? $actor : null;
    }

    /**
     * Keep ordering stable for tests/UI and avoid sensitive data.
     */
    private static function activityCreateFieldCandidates(): array
    {
        return [
            'name',
            'email',
            'username',
            'avatar',
            'role_id',
        ];
    }

    /**
     * Filter out noise (timestamps/soft-delete flags) while keeping password as field-name only.
     *
     * @param  array<int, string>  $fields
     * @return array<int, string>
     */
    private static function filterActivityFields(array $fields): array
    {
        $excluded = [
            'created_at',
            'updated_at',
            'deleted_at',
        ];

        $filtered = [];
        foreach ($fields as $field) {
            if (in_array($field, $excluded, true)) {
                continue;
            }

            $filtered[] = $field;
        }

        return array_values(array_unique($filtered));
    }

    private static function activityDescription(string $label, mixed $id, array $changedFields): string
    {
        $idString = is_scalar($id) ? (string) $id : '';
        $changedFieldsString = implode(',', $changedFields);

        return "{$label} (id: {$idString}): changed_fields={$changedFieldsString}";
    }


    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function getAvatarUrlAttribute(): string
    {
        $avatar = $this->avatar;

        if (! is_string($avatar) || trim($avatar) === '') {
            return asset('images/avatar.png');
        }

        if (filter_var($avatar, FILTER_VALIDATE_URL)) {
            return $avatar;
        }

        return asset(ltrim($avatar, '/'));
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function getMorphClass(): string
    {
        return $this->getTable();
    }
}
