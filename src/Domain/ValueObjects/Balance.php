<?php

namespace VirtualBalance\Domain\ValueObjects;

use InvalidArgumentException;

class Balance
{
    private float $amount;

    public function __construct(float $amount)
    {
        if ($amount < 0) {
            throw new InvalidArgumentException("El balance no puede ser negativo");
        }
        
        $this->amount = round($amount, 2);
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function add(Balance $balance): Balance
    {
        return new Balance($this->amount + $balance->amount);
    }

    public function subtract(Balance $balance): Balance
    {
        $newAmount = $this->amount - $balance->amount;
        
        if ($newAmount < 0) {
            throw new InvalidArgumentException("Saldo insuficiente");
        }
        
        return new Balance($newAmount);
    }

    public function isGreaterThan(Balance $balance): bool
    {
        return $this->amount > $balance->amount;
    }

    public function isGreaterOrEqualThan(Balance $balance): bool
    {
        return $this->amount >= $balance->amount;
    }

    public function __toString(): string
    {
        return number_format($this->amount, 2);
    }
}