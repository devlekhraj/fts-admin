<?php

namespace App\Services;

use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public function apply(string $code, float $subtotal): array
    {
        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon) {
            throw ValidationException::withMessages([
                'coupon' => ['Invalid coupon code'],
            ]);
        }

        if (! $coupon->is_active) {
            throw ValidationException::withMessages([
                'coupon' => ['Coupon is inactive'],
            ]);
        }

        $now = Carbon::now();

        if ($coupon->starts_at && $now->lt($coupon->starts_at)) {
            throw ValidationException::withMessages([
                'coupon' => ['Coupon not started yet'],
            ]);
        }

        if ($coupon->expires_at && $now->gt($coupon->expires_at)) {
            throw ValidationException::withMessages([
                'coupon' => ['Coupon expired'],
            ]);
        }

        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            throw ValidationException::withMessages([
                'coupon' => ['Coupon usage limit reached'],
            ]);
        }

        if ($subtotal < $coupon->min_order_amount) {
            throw ValidationException::withMessages([
                'coupon' => ['Minimum order not met'],
            ]);
        }

        // calculate discount
        $discount = 0;

        if ($coupon->type === 'percentage') {
            $discount = ($subtotal * $coupon->value) / 100;
        }

        if ($coupon->type === 'fixed') {
            $discount = $coupon->value;
        }

        $discount = min($discount, $subtotal);
        $final = $subtotal - $discount;

        return [
            'coupon_id' => $coupon->id,
            'code' => $coupon->code,
            'discount' => round($discount, 2),
            'final_total' => round($final, 2),
        ];
    }
}

