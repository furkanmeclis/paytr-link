<?php

namespace FurkanMeclis\PayTRLink\Support;

class PriceConverter
{
    /**
     * Convert price to kuruş (multiply by 100)
     * PayTR API expects prices in kuruş (cents)
     */
    public static function toKurus(float|int $price): int
    {
        return (int) round($price * 100);
    }

    /**
     * Convert kuruş to price (divide by 100)
     */
    public static function fromKurus(int $kurus): float
    {
        return round($kurus / 100, 2);
    }
}
