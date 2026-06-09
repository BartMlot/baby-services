<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Domain\ValueObject;

use App\User\Domain\ValueObject\PhoneNumber;
use PHPUnit\Framework\TestCase;

final class PhoneNumberTest extends TestCase
{
    /** @dataProvider validNumbers */
    public function testCreatesFromValidNumber(string $input, string $expected): void
    {
        $this->assertSame($expected, (new PhoneNumber($input))->value());
    }

    public static function validNumbers(): array
    {
        return [
            'PL mobile'       => ['+48123456789', '+48123456789'],
            'US number'       => ['+12125551234', '+12125551234'],
            'with spaces'     => ['+48 123 456 789', '+48123456789'],
        ];
    }

    /** @dataProvider invalidNumbers */
    public function testThrowsOnInvalidNumber(string $input): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new PhoneNumber($input);
    }

    public static function invalidNumbers(): array
    {
        return [
            'no plus sign'   => ['48123456789'],
            'too short'      => ['+4812345'],
            'letters'        => ['+48abc456789'],
            'empty'          => [''],
        ];
    }
}
