<?php

declare(strict_types=1);

namespace App\Support;

final class MoneyFormatter
{
    public static function format(string|int $amount, string $currencyCode = 'USD', int $displayDecimals = 2): string
    {
        $normalized = DecimalMath::normalize($amount);
        $isNegative = str_starts_with($normalized, '-');
        $unsigned = ltrim($normalized, '-');

        [$integerPart, $fractionPart] = array_pad(explode('.', $unsigned, 2), 2, '0000');

        $cleanInteger = ltrim($integerPart, '0');
        $cleanInteger = $cleanInteger === '' ? '0' : $cleanInteger;
        $formattedInteger = (string) preg_replace('/\B(?=(\d{3})+(?!\d))/', ',', $cleanInteger);
        $trimmedFraction = substr(str_pad($fractionPart, $displayDecimals, '0'), 0, $displayDecimals);

        $symbol = self::symbolFor($currencyCode);
        $sign = $isNegative ? '-' : '';

        if ($displayDecimals <= 0) {
            return "{$sign}{$symbol} {$formattedInteger}";
        }

        return "{$sign}{$symbol} {$formattedInteger}.{$trimmedFraction}";
    }

    public static function symbolFor(string $currencyCode): string
    {
        /** @var array<string, array{name: string, symbol: string}> $currencies */
        $currencies = config('ahorvia.finance.supported_currencies', []);
        $upper = strtoupper($currencyCode);

        return $currencies[$upper]['symbol'] ?? $upper;
    }
}
