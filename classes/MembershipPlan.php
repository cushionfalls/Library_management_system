<?php
require_once __DIR__ . '/../config/config.php';

class MembershipPlan
{
    public const SLUG_3MONTH = 'MONTHS_3';
    public const SLUG_6MONTH = 'MONTHS_6';
    public const SLUG_YEARLY = 'MONTHS_12';

    /**
     * Canonical fallback plans (used if DB table is empty).
     * Prices are in cents (USD).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function defaults()
    {
        return [
            [
                'slug' => self::SLUG_3MONTH,
                'name' => 'Bibliophile (3 Months)',
                'duration_days' => 90,
                'price' => 500,
            ],
            [
                'slug' => self::SLUG_6MONTH,
                'name' => 'Bibliophile (6 Months)',
                'duration_days' => 180,
                'price' => 1500,
            ],
            [
                'slug' => self::SLUG_YEARLY,
                'name' => 'Bibliophile (12 Months)',
                'duration_days' => 365,
                'price' => 3500,
            ],
        ];
    }
}

