<?php

namespace VirtualBalance\Application\UseCases\RegisterUser;

/**
 * Request DTO para el caso de uso RegisterUser
 */
class RegisterUserRequest
{
    public function __construct(
        public readonly string $document,
        public readonly string $name,
        public readonly string $email,
        public readonly string $phone
    ) {
    }

    public function validate(): array
    {
        $errors = [];

        if (empty($this->document)) {
            $errors['document'] = 'El documento es obligatorio';
        }

        if (empty($this->name)) {
            $errors['name'] = 'El nombre es obligatorio';
        }

        if (empty($this->email)) {
            $errors['email'] = 'El email es obligatorio';
        }

        if (empty($this->phone)) {
            $errors['phone'] = 'El teléfono es obligatorio';
        }

        return $errors;
    }
}
