<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Requests\Admin;

use App\Foundation\Application\File\DTO\ImageDto;
use Illuminate\Foundation\Http\FormRequest;

final class ImageStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'usage_type' => ['required', 'string', 'max:120'],
            'usage_id' => ['required', 'integer', 'min:1'],
            'source' => ['required', 'string', 'in:existing,upload'],
            'image_id' => ['nullable', 'integer', 'exists:files,id', 'required_if:source,existing'],
            'image' => ['nullable', 'image', 'max:10240', 'required_if:source,upload'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'meta' => ['nullable', 'array'],
            'usage_meta' => ['nullable', 'string'],
            'directory' => ['nullable', 'string', 'max:120'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }

    public function toDto(): ImageDto
    {
        $validated = $this->validated();

        $meta = is_array($validated['meta'] ?? null) ? $validated['meta'] : [];
        $usageMeta = $validated['usage_meta'] ?? null;
        if (is_string($usageMeta) && $usageMeta !== '') {
            $decoded = json_decode($usageMeta, true);
            if (is_array($decoded)) {
                $meta = array_merge($meta, $decoded);
            }
        }

        $isDefault = (bool) ($validated['is_default'] ?? ($meta['is_default'] ?? false));

        return new ImageDto(
            usageType: trim((string) $validated['usage_type']),
            usageId: (int) $validated['usage_id'],
            
            imageId: isset($validated['image_id']) ? (int) $validated['image_id'] : null,
            
            source: (string) $validated['source'],
            file: $this->file('image'),
            
            altText: isset($validated['alt_text']) ? trim((string) $validated['alt_text']) : '',
            meta: $meta,

            directory: isset($validated['directory']) ? (string) $validated['directory'] : null,
            isDefault: $isDefault,
        );
    }
}
