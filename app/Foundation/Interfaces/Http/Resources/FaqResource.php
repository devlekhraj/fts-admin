<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Resources;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use Illuminate\Http\Resources\Json\JsonResource;

class FaqResource extends JsonResource
{
    public function toArray($request): array
    {
        $type = $this->type;
        $typeName = null;
        if ($type === 'brand') {
            $typeName = $this->brand?->name;
        } elseif ($type === 'category') {
            $typeName = $this->category?->title;
        } elseif (in_array($type, ['product', 'products', ProductModel::class], true)) {
            $typeName = $this->product?->name;
        }

        return [
            'id' => $this->id,
            'type' => $this->type,
            'type_id' => $this->type_id,
            'type_name' => $typeName,
            'question' => $this->question,
            'answer' => $this->answer,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
