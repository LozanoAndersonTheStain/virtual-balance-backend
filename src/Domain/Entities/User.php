<?php

namespace VirtualBalance\Domain\Entities;

use VirtualBalance\Domain\ValueObjects\Email;
use DateTime;

class User
{
    private ?int $id;
    private string $document;
    private string $name;
    private Email $email;
    private string $phone;
    private DateTime $createdAt;
    private DateTime $updatedAt;

    public function __construct(
        string $document,
        string $name,
        Email $email,
        string $phone,
        ?int $id = null,
        ?DateTime $createdAt = null,
        ?DateTime $updatedAt = null
    ) {
        $this->id = $id;
        $this->document = $document;
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->createdAt = $createdAt ?? new DateTime();
        $this->updatedAt = $updatedAt ?? new DateTime();
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDocument(): string
    {
        return $this->document;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    // Métodos de dominio
    public function updateProfile(string $name, string $phone): void
    {
        $this->name = $name;
        $this->phone = $phone;
        $this->updatedAt = new DateTime();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'document' => $this->document,
            'name' => $this->name,
            'email' => $this->email->getValue(),
            'phone' => $this->phone,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s')
        ];
    }
}