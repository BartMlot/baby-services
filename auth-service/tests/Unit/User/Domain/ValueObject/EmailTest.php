<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Domain\ValueObject;

use App\User\Domain\ValueObject\Email;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    /** @dataProvider validEmails */
    public function testCreatesFromValidEmail(string $input, string $expected): void
    {
        $email = new Email($input);

        $this->assertSame($expected, $email->value());
    }

    public static function validEmails(): array
    {
        return [
            'lowercase'       => ['user@example.com', 'user@example.com'],
            'uppercase'       => ['USER@EXAMPLE.COM', 'user@example.com'],
            'with whitespace' => ['  user@example.com  ', 'user@example.com'],
        ];
    }

    /** @dataProvider invalidEmails */
    public function testThrowsOnInvalidEmail(string $input): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Email($input);
    }

    public static function invalidEmails(): array
    {
        return [
            'no at sign'  => ['notanemail'],
            'no domain'   => ['user@'],
            'empty string' => [''],
        ];
    }

    public function testEquality(): void
    {
        $a = new Email('user@example.com');
        $b = new Email('USER@EXAMPLE.COM');
        $c = new Email('other@example.com');

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }
}
