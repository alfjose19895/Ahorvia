<?php

declare(strict_types=1);

namespace Tests\Unit\Finance;

use App\Support\DecimalMath;
use App\Support\MoneyFormatter;
use InvalidArgumentException;
use Tests\TestCase;

class DecimalMathAndMoneyFormatterTest extends TestCase
{
    public function test_decimal_math_operates_with_exact_scale_four_without_float_drift(): void
    {
        $sum = DecimalMath::add('0.1', '0.2');
        $this->assertSame('0.3000', $sum);
        $this->assertTrue(DecimalMath::equals($sum, '0.3000'));

        $sub = DecimalMath::sub('1500.5000', '499.7525');
        $this->assertSame('1000.7475', $sub);

        $mul = DecimalMath::mul('250.25', '6');
        $this->assertSame('1501.5000', $mul);

        $div = DecimalMath::div('3000.0000', '6');
        $this->assertSame('500.0000', $div);
    }

    public function test_decimal_math_rejects_division_by_zero_and_invalid_values(): void
    {
        $this->expectException(InvalidArgumentException::class);
        DecimalMath::div('100.0000', '0');
    }

    public function test_decimal_math_rejects_non_numeric_input(): void
    {
        $this->expectException(InvalidArgumentException::class);
        DecimalMath::normalize('abc');
    }

    public function test_money_formatter_formats_large_and_negative_amounts_without_float_cast(): void
    {
        $this->assertSame(
            '$ 999,999,999,999,999.12',
            MoneyFormatter::format('999999999999999.1289', 'USD')
        );

        $this->assertSame(
            '-S/ 1,250.50',
            MoneyFormatter::format('-1250.5000', 'PEN')
        );

        $this->assertSame(
            '€ 0.00',
            MoneyFormatter::format('0', 'EUR')
        );
    }

    public function test_decimal_math_format_decimal_formats_to_two_decimals(): void
    {
        $this->assertSame('15.60', DecimalMath::formatDecimal('15.6000'));
        $this->assertSame('64.01', DecimalMath::formatDecimal('64.0100'));
        $this->assertSame('1914.31', DecimalMath::formatDecimal('1914.3100'));
        $this->assertSame('5000.00', DecimalMath::formatDecimal('5000'));
        $this->assertSame('0.00', DecimalMath::formatDecimal('0'));
        $this->assertSame('-45.50', DecimalMath::formatDecimal('-45.5000'));
        $this->assertSame('', DecimalMath::formatDecimal(null));
        $this->assertSame('', DecimalMath::formatDecimal(''));
    }
}
