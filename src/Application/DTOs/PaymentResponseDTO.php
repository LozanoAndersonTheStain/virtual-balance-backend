<?php

namespace VirtualBalance\Application\DTOs;

/**
 * DTO para respuestas de recarga/pago
 */
class PaymentResponseDTO
{
    public function __construct(
        public readonly bool $success,
        public readonly string $sessionId,
        public readonly string $token,
        public readonly float $amount,
        public readonly string $status,
        public readonly ?string $message = null,
        public readonly ?int $transactionId = null
    ) {
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'session_id' => $this->sessionId,
            'token' => $this->token,
            'amount' => $this->amount,
            'status' => $this->status,
            'message' => $this->message,
            'transaction_id' => $this->transactionId
        ];
    }
}
