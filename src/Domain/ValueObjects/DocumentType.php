<?php

namespace VirtualBalance\Domain\ValueObjects;

use InvalidArgumentException;

class DocumentType
{
    public const CC = 'CC'; // Cédula de Ciudadanía
    public const CE = 'CE'; // Cédula de Extranjería
    public const NIT = 'NIT'; // Número de Identificación Tributaria
    public const PP = 'PP'; // Pasaporte

    private const VALID_TYPES = [
        self::CC,
        self::CE,
        self::NIT,
        self::PP
    ];

    private string $value;

    public function __construct(string $type)
    {
        $type = strtoupper($type);
        
        if (!in_array($type, self::VALID_TYPES, true)) {
            throw new InvalidArgumentException("Tipo de documento inválido: {$type}");
        }
        
        $this->value = $type;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}