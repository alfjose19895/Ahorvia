<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

final class DecimalMath
{
    public const int SCALE = 4;

    public const string ZERO = '0.0000';

    /**
     * @return numeric-string
     */
    public static function normalize(string|int $value): string
    {
        $raw = trim((string) $value);

        if (! is_numeric($raw)) {
            throw new InvalidArgumentException("El valor monetario [{$raw}] no es numérico válido.");
        }

        return bcadd($raw, '0', self::SCALE);
    }

    /**
     * @return numeric-string
     */
    public static function add(string|int $left, string|int $right): string
    {
        return bcadd(self::normalize($left), self::normalize($right), self::SCALE);
    }

    /**
     * @return numeric-string
     */
    public static function sub(string|int $left, string|int $right): string
    {
        return bcsub(self::normalize($left), self::normalize($right), self::SCALE);
    }

    /**
     * @return numeric-string
     */
    public static function mul(string|int $left, string|int $right): string
    {
        return bcmul(self::normalize($left), self::normalize($right), self::SCALE);
    }

    /**
     * @return numeric-string
     */
    public static function div(string|int $dividend, string|int $divisor): string
    {
        $normalizedDivisor = self::normalize($divisor);

        if (bccomp($normalizedDivisor, self::ZERO, self::SCALE) === 0) {
            throw new InvalidArgumentException('No es posible dividir un importe monetario entre cero.');
        }

        return bcdiv(self::normalize($dividend), $normalizedDivisor, self::SCALE);
    }

    public static function compare(string|int $left, string|int $right): int
    {
        return bccomp(self::normalize($left), self::normalize($right), self::SCALE);
    }

    public static function equals(string|int $left, string|int $right): bool
    {
        return self::compare($left, $right) === 0;
    }

    public static function isNegative(string|int $value): bool
    {
        return self::compare($value, self::ZERO) < 0;
    }

    public static function formatDecimal(string|int|float|null $value, int $decimals = 2): string
    {
        if ($value === null || trim((string) $value) === '') {
            return '';
        }

        $raw = trim((string) $value);
        if (! is_numeric($raw)) {
            return $raw;
        }

        $normalized = self::normalize($raw);
        $isNegative = str_starts_with($normalized, '-');
        $unsigned = ltrim($normalized, '-');

        [$integerPart, $fractionPart] = array_pad(explode('.', $unsigned, 2), 2, '00');
        $trimmedFraction = substr(str_pad($fractionPart, $decimals, '0'), 0, $decimals);
        $sign = $isNegative ? '-' : '';

        return $decimals > 0 ? "{$sign}{$integerPart}.{$trimmedFraction}" : "{$sign}{$integerPart}";
    }
}
