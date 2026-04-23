<?php

declare(strict_types=1);

namespace App\Domains\Admin\DTOs;

final class UpdateAdminEmailData
{
    public function __construct(
        public readonly string $email,
        public readonly ?string $verificationCode = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $code = $data['verification_code'] ?? null;

        return new self(
            email: (string) ($data['email'] ?? ''),
            verificationCode: is_string($code) ? trim($code) : null,
        );
    }
}

