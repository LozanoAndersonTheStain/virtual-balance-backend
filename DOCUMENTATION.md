# 📚 Documentación Técnica - Virtual Balance Backend

## Índice

- [Arquitectura](#arquitectura)
- [Domain Layer](#domain-layer)
- [Application Layer](#application-layer)
- [Infrastructure Layer](#infrastructure-layer)
- [Shared Layer](#shared-layer)
- [Base de Datos](#base-de-datos)
- [Flujo de Datos](#flujo-de-datos)
- [Patrones de Diseño](#patrones-de-diseño)

## Arquitectura

El proyecto sigue los principios de **Clean Architecture** propuesta por Robert C. Martin (Uncle Bob), organizando el código en capas concéntricas donde las dependencias apuntan hacia adentro.

```
┌─────────────────────────────────────────────────┐
│          Infrastructure Layer                   │
│  (HTTP Controllers, MySQL Repositories,         │
│   Middleware, External Services)                │
│                                                  │
│  ┌───────────────────────────────────────────┐  │
│  │      Application Layer                    │  │
│  │  (Use Cases, DTOs, Request/Response)      │  │
│  │                                           │  │
│  │  ┌─────────────────────────────────────┐ │  │
│  │  │     Domain Layer                    │ │  │
│  │  │  (Entities, Value Objects,          │ │  │
│  │  │   Repositories Interfaces,          │ │  │
│  │  │   Domain Exceptions)                │ │  │
│  │  └─────────────────────────────────────┘ │  │
│  └───────────────────────────────────────────┘  │
└─────────────────────────────────────────────────┘
```

### Principios Fundamentales

1. **Independencia de Frameworks:** El dominio no conoce Slim, MySQL ni ningún framework
2. **Testeable:** La lógica de negocio se puede testear sin UI, BD o servicios externos
3. **Independencia de UI:** Puede usarse con REST API, GraphQL, CLI, etc.
4. **Independencia de BD:** El dominio no conoce si usa MySQL, PostgreSQL o MongoDB
5. **Independencia de Servicios Externos:** El dominio no conoce APIs externas

## Domain Layer

### Entities

Las entidades son objetos con identidad que representan conceptos del negocio.

#### User.php

**Ubicación:** `src/Domain/Entities/User.php`

**Responsabilidad:** Representa un usuario/cliente del sistema.

```php
<?php
namespace VirtualBalance\Domain\Entities;

use VirtualBalance\Domain\ValueObjects\Email;

class User
{
private int $id;
private string $document;
private string $name;
private Email $email;
private string $phone;

public function __construct(
    int $id,
    string $document,
    string $name,
    Email $email,
    string $phone
) {
    $this->id = $id;
    $this->document = $document;
    $this->name = $name;
    $this->email = $email;
    $this->phone = $phone;
}

// Getters
public function getId(): int { return $this->id; }
public function getDocument(): string { return $this->document; }
public function getName(): string { return $this->name; }
public function getEmail(): Email { return $this->email; }
public function getPhone(): string { return $this->phone; }

// Business logic
public function updateProfile(string $name, string $phone): void
{
    $this->name = $name;
    $this->phone = $phone;
}
}
```

**Características:**
- Constructor property promotion (PHP 8.0+)
- Usa ValueObject Email para validación
- Método de negocio updateProfile()
- Inmutable (no hay setters públicos)

#### Wallet.php

**Ubicación:** `src/Domain/Entities/Wallet.php`

**Responsabilidad:** Representa una billetera virtual con su saldo.

```php
<?php
namespace VirtualBalance\Domain\Entities;

use VirtualBalance\Domain\ValueObjects\Balance;
use VirtualBalance\Domain\Exceptions\InsufficientBalanceException;

class Wallet
{
private int $id;
private int $userId;
private Balance $balance;

public function __construct(
    int $id,
    int $userId,
    Balance $balance
) {
    $this->id = $id;
    $this->userId = $userId;
    $this->balance = $balance;
}

// Getters
public function getId(): int { return $this->id; }
public function getUserId(): int { return $this->userId; }
public function getBalance(): Balance { return $this->balance; }

// Business logic
public function recharge(Balance $amount): void
{
    $this->balance = $this->balance->add($amount);
}

public function debit(Balance $amount): void
{
    if (!$this->hasBalance($amount)) {
        throw new InsufficientBalanceException(
            "Balance actual: {$this->balance->getAmount()} COP, " .
            "Requerido: {$amount->getAmount()} COP"
        );
    }
    
    $this->balance = $this->balance->subtract($amount);
}

public function hasBalance(Balance $amount): bool
{
    return $this->balance->getAmount() >= $amount->getAmount();
}
}
```

**Características:**
- Usa ValueObject Balance para operaciones
- Validación de negocio en hasBalance()
- Lanza InsufficientBalanceException si no hay fondos
- Métodos recharge() y debit() encapsulan lógica

#### Transaction.php

**Ubicación:** `src/Domain/Entities/Transaction.php`

**Responsabilidad:** Representa una transacción (recarga o pago).

```php
<?php
namespace VirtualBalance\Domain\Entities;

use VirtualBalance\Domain\ValueObjects\Balance;
use VirtualBalance\Domain\ValueObjects\TransactionStatus;

class Transaction
{
private int $id;
private int $walletId;
private string $type; // RECHARGE | PAYMENT
private Balance $amount;
private TransactionStatus $status;
private ?string $token;
private ?string $sessionId;

// Constructor y getters...

// Business logic
public function markAsCompleted(): void
{
    $this->status = TransactionStatus::completed();
}

public function markAsFailed(): void
{
    $this->status = TransactionStatus::failed();
}

public function isPending(): bool
{
    return $this->status->getValue() === 'PENDING';
}

public function isRecharge(): bool
{
    return $this->type === 'RECHARGE';
}
}
```

**Características:**
- Usa ValueObject para status
- Métodos de negocio para cambiar estado
- Métodos de consulta isPending(), isRecharge()

### Value Objects

Los Value Objects son objetos sin identidad que encapsulan lógica de validación.

#### Email.php

**Ubicación:** `src/Domain/ValueObjects/Email.php`

**Responsabilidad:** Validar y encapsular un email.

```php
<?php
namespace VirtualBalance\Domain\ValueObjects;

use InvalidArgumentException;

class Email
{
private string $value;

public function __construct(string $value)
{
    $this->validate($value);
    $this->value = $value;
}

private function validate(string $value): void
{
    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException(
            "El email '{$value}' no es válido"
        );
    }
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
```

**Características:**
- Inmutable (no se puede cambiar después de creado)
- Validación en constructor
- Lanza excepción si es inválido
- Implementa __toString() para conversión

#### Balance.php

**Ubicación:** `src/Domain/ValueObjects/Balance.php`

**Responsabilidad:** Operaciones aritméticas con saldos.

```php
<?php
namespace VirtualBalance\Domain\ValueObjects;

use InvalidArgumentException;

class Balance
{
private float $amount;

public function __construct(float $amount)
{
    if ($amount < 0) {
        throw new InvalidArgumentException(
            "El balance no puede ser negativo: {$amount}"
        );
    }
    
    $this->amount = round($amount, 2);
}

public function getAmount(): float
{
    return $this->amount;
}

// Operaciones inmutables (retornan nuevo objeto)
public function add(Balance $other): Balance
{
    return new Balance($this->amount + $other->amount);
}

public function subtract(Balance $other): Balance
{
    return new Balance($this->amount - $other->amount);
}

public function isGreaterThan(Balance $other): bool
{
    return $this->amount > $other->amount;
}
}
```

**Características:**
- Inmutable (cada operación retorna nuevo objeto)
- Validación de negativos
- Redondeo a 2 decimales
- Métodos de comparación

#### TransactionStatus.php

**Ubicación:** `src/Domain/ValueObjects/TransactionStatus.php`

**Responsabilidad:** Estados válidos de una transacción.

```php
<?php
namespace VirtualBalance\Domain\ValueObjects;

class TransactionStatus
{
private const PENDING = 'PENDING';
private const COMPLETED = 'COMPLETED';
private const FAILED = 'FAILED';

private string $value;

private function __construct(string $value)
{
    $this->value = $value;
}

// Factory methods (static)
public static function pending(): self
{
    return new self(self::PENDING);
}

public static function completed(): self
{
    return new self(self::COMPLETED);
}

public static function failed(): self
{
    return new self(self::FAILED);
}

public function getValue(): string
{
    return $this->value;
}
}
```

**Características:**
- Constructor privado (solo factory methods)
- Constantes para estados válidos
- Inmutable
- Type safety

### Repository Interfaces

Contratos que definen cómo acceder a los datos, sin especificar la implementación.

#### UserRepositoryInterface.php

**Ubicación:** `src/Domain/Repositories/UserRepositoryInterface.php`

```php
<?php
namespace VirtualBalance\Domain\Repositories;

use VirtualBalance\Domain\Entities\User;

interface UserRepositoryInterface
{
public function save(User $user): User;
public function findById(int $id): ?User;
public function findByDocument(string $document): ?User;
public function existsByEmail(string $email): bool;
public function existsByDocument(string $document): bool;
}
```

#### WalletRepositoryInterface.php

```php
<?php
namespace VirtualBalance\Domain\Repositories;

use VirtualBalance\Domain\Entities\Wallet;

interface WalletRepositoryInterface
{
public function save(Wallet $wallet): Wallet;
public function findById(int $id): ?Wallet;
public function findByUserId(int $userId): ?Wallet;
public function update(Wallet $wallet): bool;
}
```

#### TransactionRepositoryInterface.php

```php
<?php
namespace VirtualBalance\Domain\Repositories;

use VirtualBalance\Domain\Entities\Transaction;

interface TransactionRepositoryInterface
{
public function save(Transaction $transaction): Transaction;
public function findById(int $id): ?Transaction;
public function findByToken(string $token): ?Transaction;
public function update(Transaction $transaction): bool;
}
```

### Domain Exceptions

Excepciones específicas del dominio.

```php
// UserNotFoundException.php
class UserNotFoundException extends DomainException
{
public function __construct(string $identifier)
{
    parent::__construct("Usuario no encontrado: {$identifier}");
}
}

// InsufficientBalanceException.php
class InsufficientBalanceException extends DomainException
{
public function __construct(string $message)
{
    parent::__construct($message);
}
}

// DuplicateUserException.php
class DuplicateUserException extends DomainException
{
public function __construct(string $field, string $value)
{
    parent::__construct(
        "Ya existe un usuario con {$field}: {$value}"
    );
}
}
```

## Application Layer

### Use Cases

Los casos de uso orquestan el flujo de datos entre el dominio y la infraestructura.

#### RegisterUserUseCase.php

**Ubicación:** `src/Application/UseCases/RegisterUser/RegisterUserUseCase.php`

**Responsabilidad:** Registrar un nuevo usuario y crear su billetera.

```php
<?php
namespace VirtualBalance\Application\UseCases\RegisterUser;

use VirtualBalance\Domain\Entities\User;
use VirtualBalance\Domain\Entities\Wallet;
use VirtualBalance\Domain\ValueObjects\Email;
use VirtualBalance\Domain\ValueObjects\Balance;
use VirtualBalance\Domain\Repositories\UserRepositoryInterface;
use VirtualBalance\Domain\Repositories\WalletRepositoryInterface;
use VirtualBalance\Domain\Exceptions\DuplicateUserException;

class RegisterUserUseCase
{
public function __construct(
    private UserRepositoryInterface $userRepository,
    private WalletRepositoryInterface $walletRepository
) {
}

public function execute(RegisterUserRequest $request): UserDTO
{
    // 1. Validar request
    $request->validate();
    
    // 2. Verificar duplicados
    if ($this->userRepository->existsByEmail($request->email)) {
        throw new DuplicateUserException('email', $request->email);
    }
    
    if ($this->userRepository->existsByDocument($request->document)) {
        throw new DuplicateUserException('document', $request->document);
    }
    
    // 3. Crear entidades de dominio
    $user = new User(
        id: 0,
        document: $request->document,
        name: $request->name,
        email: new Email($request->email),
        phone: $request->phone
    );
    
    // 4. Guardar usuario
    $savedUser = $this->userRepository->save($user);
    
    // 5. Crear billetera con saldo 0
    $wallet = new Wallet(
        id: 0,
        userId: $savedUser->getId(),
        balance: new Balance(0)
    );
    
    // 6. Guardar billetera
    $savedWallet = $this->walletRepository->save($wallet);
    
    // 7. Retornar DTO
    return new UserDTO(
        id: $savedUser->getId(),
        document: $savedUser->getDocument(),
        name: $savedUser->getName(),
        email: $savedUser->getEmail()->getValue(),
        phone: $savedUser->getPhone(),
        walletId: $savedWallet->getId(),
        balance: $savedWallet->getBalance()->getAmount()
    );
}
}
```

**Flujo:**
1. Validar datos de entrada
2. Verificar duplicados (email y documento)
3. Crear entidad User con ValueObject Email
4. Guardar usuario en BD
5. Crear Wallet con Balance inicial de 0
6. Guardar billetera en BD
7. Retornar DTO con toda la información

#### MakePaymentUseCase.php

**Ubicación:** `src/Application/UseCases/MakePayment/MakePaymentUseCase.php`

**Responsabilidad:** Realizar un pago descontando del saldo.

**Flujo:**
1. Validar request
2. Buscar usuario por documento
3. Buscar billetera del usuario
4. Crear Balance con el monto del pago
5. Debitar de la billetera (lanza excepción si no hay saldo)
6. Actualizar billetera en BD
7. Crear Transaction tipo PAYMENT en estado COMPLETED
8. Guardar transacción en BD
9. Retornar DTO con saldo anterior y nuevo

### Request Objects

Encapsulan y validan parámetros de entrada.

```php
<?php
namespace VirtualBalance\Application\UseCases\RegisterUser;

class RegisterUserRequest
{
public function __construct(
    public readonly string $document,
    public readonly string $name,
    public readonly string $email,
    public readonly string $phone
) {
}

public function validate(): void
{
    if (empty($this->document)) {
        throw new \InvalidArgumentException('El documento es requerido');
    }
    
    if (empty($this->name)) {
        throw new \InvalidArgumentException('El nombre es requerido');
    }
    
    if (empty($this->email)) {
        throw new \InvalidArgumentException('El email es requerido');
    }
    
    if (empty($this->phone)) {
        throw new \InvalidArgumentException('El teléfono es requerido');
    }
}
}
```

**Características:**
- Readonly properties (PHP 8.1)
- Constructor property promotion
- Método validate() centralizado
- Tipo específico para cada UseCase

### DTOs (Data Transfer Objects)

Objetos para transferir datos entre capas.

```php
<?php
namespace VirtualBalance\Application\UseCases\RegisterUser;

class UserDTO
{
public function __construct(
    public readonly int $id,
    public readonly string $document,
    public readonly string $name,
    public readonly string $email,
    public readonly string $phone,
    public readonly int $walletId,
    public readonly float $balance
) {
}

public function toArray(): array
{
    return [
        'id' => $this->id,
        'document' => $this->document,
        'name' => $this->name,
        'email' => $this->email,
        'phone' => $this->phone,
        'wallet_id' => $this->walletId,
        'balance' => $this->balance
    ];
}
}
```

## Infrastructure Layer

### MySQL Repositories

Implementaciones concretas de los Repository Interfaces usando PDO.

#### MySQLUserRepository.php

**Ubicación:** `src/Infrastructure/Persistence/MySQLUserRepository.php`

```php
<?php
namespace VirtualBalance\Infrastructure\Persistence;

use VirtualBalance\Domain\Entities\User;
use VirtualBalance\Domain\ValueObjects\Email;
use VirtualBalance\Domain\Repositories\UserRepositoryInterface;

class MySQLUserRepository implements UserRepositoryInterface
{
private \PDO $connection;

public function __construct()
{
    $this->connection = Connection::getInstance();
}

public function save(User $user): User
{
    $stmt = $this->connection->prepare("
        INSERT INTO users (document, name, email, phone)
        VALUES (:document, :name, :email, :phone)
    ");
    
    $stmt->execute([
        ':document' => $user->getDocument(),
        ':name' => $user->getName(),
        ':email' => $user->getEmail()->getValue(),
        ':phone' => $user->getPhone()
    ]);
    
    $id = (int) $this->connection->lastInsertId();
    
    return new User(
        id: $id,
        document: $user->getDocument(),
        name: $user->getName(),
        email: $user->getEmail(),
        phone: $user->getPhone()
    );
}

public function findByDocument(string $document): ?User
{
    $stmt = $this->connection->prepare("
        SELECT * FROM users WHERE document = :document
    ");
    
    $stmt->execute([':document' => $document]);
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    if (!$row) {
        return null;
    }
    
    return new User(
        id: $row['id'],
        document: $row['document'],
        name: $row['name'],
        email: new Email($row['email']),
        phone: $row['phone']
    );
}

// Otros métodos...
}
```

**Características:**
- Implements UserRepositoryInterface
- Usa PDO con prepared statements
- Convierte rows de BD a Entities
- Singleton para conexión

### HTTP Controllers

Manejan las peticiones HTTP y delegan a los Use Cases.

#### UserController.php

**Ubicación:** `src/Infrastructure/Http/Controllers/UserController.php`

```php
<?php
namespace VirtualBalance\Infrastructure\Http\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use VirtualBalance\Application\UseCases\RegisterUser\RegisterUserRequest;
use VirtualBalance\Application\UseCases\RegisterUser\RegisterUserUseCase;
use VirtualBalance\Shared\Utils\ResponseFormatter;
use VirtualBalance\Domain\Exceptions\DuplicateUserException;

class UserController
{
public function __construct(
    private RegisterUserUseCase $registerUserUseCase
) {
}

public function register(
    ServerRequestInterface $request,
    ResponseInterface $response
): ResponseInterface {
    try {
        $data = $request->getParsedBody();
        
        $userRequest = new RegisterUserRequest(
            document: $data['document'] ?? '',
            name: $data['name'] ?? '',
            email: $data['email'] ?? '',
            phone: $data['phone'] ?? ''
        );
        
        $userDTO = $this->registerUserUseCase->execute($userRequest);
        
        return ResponseFormatter::success(
            data: $userDTO->toArray(),
            message: 'Usuario registrado exitosamente',
            statusCode: 201
        );
    } catch (DuplicateUserException $e) {
        return ResponseFormatter::conflict($e->getMessage());
    } catch (\InvalidArgumentException $e) {
        return ResponseFormatter::validationError([], $e->getMessage());
    } catch (\Exception $e) {
        return ResponseFormatter::serverError('Error al registrar usuario');
    }
}
}
```

**Responsabilidades:**
- Recibe ServerRequestInterface (PSR-7)
- Extrae datos del body
- Crea Request object
- Ejecuta Use Case
- Maneja excepciones
- Formatea respuesta con ResponseFormatter
- Retorna ResponseInterface (PSR-7)

### Middleware

#### ApiKeyAuthMiddleware.php

**Ubicación:** `src/Infrastructure/Http/Middleware/ApiKeyAuthMiddleware.php`

```php
<?php
namespace VirtualBalance\Infrastructure\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ApiKeyAuthMiddleware implements MiddlewareInterface
{
public function process(
    ServerRequestInterface $request,
    RequestHandlerInterface $handler
): ResponseInterface {
    $apiKey = $request->getHeaderLine('X-API-Key');
    
    // Fallback a query parameter
    if (empty($apiKey)) {
        $params = $request->getQueryParams();
        $apiKey = $params['api_key'] ?? '';
    }
    
    $validApiKey = $_ENV['API_KEY'] ?? '3d1ae36128db9f079fd5f0b4af05ba16ee7bfbea94343623ef2055cf2272ccfe';
    
    if ($apiKey !== $validApiKey) {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => 'API Key inválida o no proporcionada',
            'timestamp' => date('Y-m-d H:i:s')
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }
    
    return $handler->handle($request);
}
}
```

## Base de Datos

### Esquema

```sql
CREATE DATABASE IF NOT EXISTS virtual_balance 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE virtual_balance;

-- Tabla users
CREATE TABLE users (
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
document VARCHAR(50) NOT NULL UNIQUE,
name VARCHAR(255) NOT NULL,
email VARCHAR(255) NOT NULL UNIQUE,
phone VARCHAR(20) NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
INDEX idx_document (document),
INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla wallets
CREATE TABLE wallets (
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
user_id INT UNSIGNED NOT NULL UNIQUE,
balance DECIMAL(15,2) NOT NULL DEFAULT 0.00 CHECK (balance >= 0),
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla transactions
CREATE TABLE transactions (
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
wallet_id INT UNSIGNED NOT NULL,
type ENUM('RECHARGE', 'PAYMENT') NOT NULL,
amount DECIMAL(15,2) NOT NULL CHECK (amount > 0),
status ENUM('PENDING', 'COMPLETED', 'FAILED') NOT NULL,
token VARCHAR(255),
session_id VARCHAR(255),
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE CASCADE,
INDEX idx_wallet (wallet_id),
INDEX idx_status (status),
INDEX idx_token (token),
INDEX idx_session (session_id),
INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## Flujo de Datos

### Flujo de Registro de Usuario

```
1. Cliente HTTP → POST /api/users/register
2. ApiKeyAuthMiddleware → Valida API Key
3. UserController::register() → Extrae datos
4. RegisterUserRequest → Encapsula parámetros
5. RegisterUserUseCase::execute() → Lógica de aplicación
6. UserRepository::existsByEmail() → Consulta BD
7. UserRepository::save() → Inserta usuario
8. WalletRepository::save() → Inserta billetera
9. UserDTO → Prepara respuesta
10. ResponseFormatter::success() → Formatea JSON
11. Cliente HTTP ← 201 Created
```

### Flujo de Recarga (2 pasos)

**Paso 1: Iniciar Recarga**
```
Cliente → POST /api/transactions/recharge
Controller → RechargeWalletUseCase
UseCase → Valida usuario y teléfono
UseCase → Genera token y sessionId
UseCase → Crea Transaction PENDING
Repository → Guarda en BD
Cliente ← 201 Created con token/sessionId
```

**Paso 2: Confirmar Recarga**
```
Cliente → POST /api/transactions/confirm (con token/sessionId)
Controller → ConfirmPaymentUseCase
UseCase → Busca transacción por token
UseCase → Valida sessionId
UseCase → Simula pasarela de pagos
UseCase → Si exitoso: acredita saldo + marca COMPLETED
UseCase → Si falla: marca FAILED
Repository → Actualiza transacción y billetera
Cliente ← 200 OK o 400 Bad Request
```

## Patrones de Diseño

### 1. Repository Pattern
- **Propósito:** Abstracción del acceso a datos
- **Implementación:** Interfaces en Domain, implementaciones en Infrastructure
- **Beneficio:** Cambiar BD sin afectar lógica de negocio

### 2. Use Case Pattern
- **Propósito:** Encapsular lógica de aplicación
- **Implementación:** Una clase por caso de uso
- **Beneficio:** Código organizado y testeable

### 3. DTO Pattern
- **Propósito:** Transferir datos entre capas
- **Implementación:** Clases con readonly properties
- **Beneficio:** Tipo seguro, previene mutación

### 4. Value Object Pattern
- **Propósito:** Encapsular validación de primitivos
- **Implementación:** Email, Balance, TransactionStatus
- **Beneficio:** Validación centralizada, inmutabilidad

### 5. Dependency Injection
- **Propósito:** Inversión de control
- **Implementación:** PHP-DI container
- **Beneficio:** Código desacoplado, fácil testing

### 6. Singleton Pattern
- **Propósito:** Una sola instancia de conexión BD
- **Implementación:** Connection::getInstance()
- **Beneficio:** Eficiencia, control de recursos

---

Para más información, consulta los otros archivos de documentación:
- [README.md](README.md) - Introducción general
- [FEATURES.md](FEATURES.md) - Características implementadas
- [SETUP.md](SETUP.md) - Guía de instalación
- [CHANGELOG.md](CHANGELOG.md) - Historial de cambios
