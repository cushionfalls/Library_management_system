<?php
require_once __DIR__ . '/../config/config.php';

class MembershipPlan {
    public const SLUG_MONTHLY = 'MONTHLY_1';
    public const SLUG_6MONTH = 'MONTHS_6';
    public const SLUG_YEARLY = 'MONTHS_12';

    /**
     * Canonical fallback plans (used if DB table is empty).
     * Prices are in INR.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function defaults() {
        return [
            [
                'slug' => self::SLUG_MONTHLY,
                'name' => 'Bibliophile (1 Month)',
                'duration_days' => 30,
                'price' => 399,
            ],
            [
                'slug' => self::SLUG_6MONTH,
                'name' => 'Bibliophile (6 Months)',
                'duration_days' => 180,
                'price' => 699,
            ],
            [
                'slug' => self::SLUG_YEARLY,
                'name' => 'Bibliophile (12 Months)',
                'duration_days' => 365,
                'price' => 1999,
            ],
        ];
    }
}

