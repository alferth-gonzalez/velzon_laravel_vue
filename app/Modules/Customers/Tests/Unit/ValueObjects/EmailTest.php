<?php

declare(strict_types=1);

namespace App\Modules\Customers\Tests\Unit\ValueObjects;

use App\Modules\Customers\Domain\ValueObjects\Email;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class EmailTest extends TestCase
{
    public function test_can_create_valid_email(): void
    {
        $email = new Email('test@example.com');
        
        $this->assertEquals('test@example.com', $email->value);
        $this->assertEquals('test@example.com', $email->normalized());
    }

    public function test_normalizes_email_to_lowercase(): void
    {
        $email = new Email('TEST@EXAMPLE.COM');
        
        $this->assertEquals('test@example.com', $email->normalized());
    }

    public function test_throws_exception_for_invalid_email(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('El formato del email es inválido');
        
        new Email('invalid-email');
    }

    public function test_throws_exception_for_empty_email(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('El email no puede estar vacío');
        
        new Email('');
    }

    public function test_throws_exception_for_too_long_email(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('El email no puede tener más de 255 caracteres');
        
        $longEmail = str_repeat('a', 250) . '@example.com';
        new Email($longEmail);
    }

    public function test_can_compare_emails(): void
    {
        $email1 = new Email('test@example.com');
        $email2 = new Email('TEST@EXAMPLE.COM');
        $email3 = new Email('other@example.com');
        
        $this->assertTrue($email1->equals($email2));
        $this->assertFalse($email1->equals($email3));
    }

    public function test_can_convert_to_string(): void
    {
        $email = new Email('test@example.com');
        
        $this->assertEquals('test@example.com', (string) $email);
    }
}
