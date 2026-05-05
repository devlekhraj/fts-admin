<?php

namespace App\Domains\Coupon\Services;

use App\Domains\Coupon\Models\CouponDiscount;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public function apply(string $code, float $subtotal): array
    {
        $coupon = CouponDiscount::where('code', $code)->first();

        if (! $coupon) {
            throw ValidationException::withMessages([
                'coupon' => ['Invalid coupon code'],
            ]);
        }

        if ($coupon->deleted_at) {
            throw ValidationException::withMessages([
                'coupon' => ['Coupon is inactive'],
            ]);
        }

        $now = Carbon::now();

        if ($coupon->start_date && $now->lt($coupon->start_date)) {
            throw ValidationException::withMessages([
                'coupon' => ['Coupon not started yet'],
            ]);
        }

        if ($coupon->end_date && $now->gt($coupon->end_date)) {
            throw ValidationException::withMessages([
                'coupon' => ['Coupon expired'],
            ]);
        }

        // Note: You'll need to implement usage tracking logic based on your requirements
        // if ($coupon->usage_per_user && $currentUsage >= $coupon->usage_per_user) {
        //     throw ValidationException::withMessages([
        //         'coupon' => ['Coupon usage limit reached'],
        //     ]);
        // }

        if ($subtotal < $coupon->minimum_value) {
            throw ValidationException::withMessages([
                'coupon' => ['Minimum order not met'],
            ]);
        }

        // calculate discount
        $discount = 0;

        if ($coupon->discount_type === 'percentage') {
            $discount = ($subtotal * $coupon->discount_value) / 100;
        }

        if ($coupon->discount_type === 'fixed') {
            $discount = $coupon->discount_value;
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
