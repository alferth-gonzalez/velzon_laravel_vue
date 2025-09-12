<?php

declare(strict_types=1);

namespace App\Modules\Customers\Tests\Unit\ValueObjects;

use App\Modules\Customers\Domain\ValueObjects\DocumentId;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class DocumentIdTest extends TestCase
{
    public function test_can_create_valid_document_id(): void
    {
        $documentId = new DocumentId('CC', '12345678');
        
        $this->assertEquals('CC', $documentId->type);
        $this->assertEquals('12345678', $documentId->number);
    }

    public function test_normalizes_document_number(): void
    {
        $documentId = new DocumentId('CC', '12.345.678');
        
        $this->assertEquals('12345678', $documentId->normalized());
    }

    public function test_formats_cc_document(): void
    {
        $documentId = new DocumentId('CC', '12345678');
        
        $this->assertEquals('12.345.678', $documentId->formatted());
    }

    public function test_formats_nit_document(): void
    {
        $documentId = new DocumentId('NIT', '9001234567');
        
        $this->assertEquals('900.123.456-7', $documentId->formatted());
    }

    public function test_throws_exception_for_invalid_document_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tipo de documento inválido');
        
        new DocumentId('INVALID', '12345678');
    }

    public function test_throws_exception_for_empty_document_number(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('El número de documento no puede estar vacío');
        
        new DocumentId('CC', '');
    }

    public function test_throws_exception_for_too_short_document(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('El número de documento debe tener entre 5 y 15 dígitos');
        
        new DocumentId('CC', '123');
    }

    public function test_throws_exception_for_too_long_document(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('El número de documento debe tener entre 5 y 15 dígitos');
        
        new DocumentId('CC', '1234567890123456');
    }

    public function test_validates_nit_check_digit(): void
    {
        // NIT válido: 900123456-7
        $validNit = new DocumentId('NIT', '9001234567');
        $this->assertEquals('9001234567', $validNit->normalized());

        // NIT inválido
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('El dígito de verificación del NIT es incorrecto');
        
        new DocumentId('NIT', '9001234568'); // Dígito incorrecto
    }

    public function test_can_compare_document_ids(): void
    {
        $doc1 = new DocumentId('CC', '12345678');
        $doc2 = new DocumentId('CC', '12.345.678');
        $doc3 = new DocumentId('CC', '87654321');
        $doc4 = new DocumentId('NIT', '12345678');
        
        $this->assertTrue($doc1->equals($doc2));
        $this->assertFalse($doc1->equals($doc3));
        $this->assertFalse($doc1->equals($doc4)); // Diferente tipo
    }

    public function test_can_convert_to_string(): void
    {
        $documentId = new DocumentId('CC', '12345678');
        
        $this->assertEquals('CC:12345678', (string) $documentId);
    }
}
