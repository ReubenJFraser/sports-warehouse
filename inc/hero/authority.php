<?php
// /inc/hero/HeroAuthority.php

final class HeroAuthority
{
    const SOURCE_MANUAL      = 'manual';
    const SOURCE_ADMIN_AUTO  = 'admin_auto';
    const SOURCE_MAINTENANCE = 'maintenance';

    /**
     * Determines whether a hero write is permitted.
     *
     * This function enforces the Hero Image Authority Contract.
     * It must be consulted before any UPDATE to item.hero_* fields.
     */
    public static function canWrite(array $item, string $source): bool
    {
        $hasHero = !empty($item['hero_image']);

        // Manual authority is always permitted
        if ($source === self::SOURCE_MANUAL) {
            return true;
        }

        // If a hero already exists, only manual authority may replace it
        if ($hasHero) {
            return false;
        }

        // Admin automation may write only if no hero exists
        if ($source === self::SOURCE_ADMIN_AUTO) {
            return true;
        }

        // Maintenance scripts are strictly lowest authority
        if ($source === self::SOURCE_MAINTENANCE) {
            return true;
        }

        // Unknown authority source → forbidden
        return false;
    }
}

