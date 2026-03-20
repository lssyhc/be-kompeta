<?php

namespace App\Support;

use Carbon\CarbonInterface;

class ExploreFormatter
{
    public static function salaryLabel(?int $min, ?int $max, bool $isHidden): string
    {
        if ($isHidden || (! $min && ! $max)) {
            return 'Gaji tidak ditampilkan';
        }

        if ($min && $max) {
            return 'Rp '.self::toJutaLabel($min).'-'.self::toJutaLabel($max);
        }

        if ($min) {
            return 'Mulai Rp '.self::toJutaLabel($min);
        }

        return 'Hingga Rp '.self::toJutaLabel((int) $max);
    }

    public static function relativeTime(?CarbonInterface $dateTime): ?string
    {
        if (! $dateTime) {
            return null;
        }

        return $dateTime->copy()->locale('id')->diffForHumans();
    }

    private static function toJutaLabel(int $amount): string
    {
        $valueInJuta = number_format($amount / 1_000_000, 1, ',', '.');

        if (str_ends_with($valueInJuta, ',0')) {
            $valueInJuta = substr($valueInJuta, 0, -2);
        }

        return $valueInJuta.' jt';
    }
}
