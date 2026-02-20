# Changelog

Todos los cambios notables de este proyecto están documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

## [1.0.0] - 2026-02-20

### 🎉 Lanzamiento Inicial

Primer release del sistema Virtual Balance Backend - API REST para gestión de saldos virtuales.

### ✨ Características Implementadas

#### Arquitectura y Estructura

- ✅ Clean Architecture implementada (Domain → Application → Infrastructure → Shared)
- ✅ Principios SOLID aplicados en todo el código
- ✅ PHP 8.0+ con typed properties y constructor property promotion
- ✅ Composer para gestión de dependencias
- ✅ PSR-4 autoloading configurado

#### Domain Layer (Capa de Dominio)

**Entities:**
- ✅ User.php - Entidad Usuario con métodos de negocio
- ✅ Wallet.php - Entidad Billetera con recharge(), debit() y hasBalance()
- ✅ Transaction.php - Entidad Transacción con estados y tipos

**Value Objects:**
- ✅ Email.php - Validación de formato de email
- ✅ Balance.php - Operaciones inmutables con saldos (add, subtract)
- ✅ TransactionStatus.php - Estados válidos (PENDING, COMPLETED, FAILED)
- ✅ DocumentType.php - Tipos de documento válidos

**Repository Interfaces:**
- ✅ UserRepositoryInterface.php - Contrato para UserRepository
- ✅ WalletRepositoryInterface.php - Contrato para WalletRepository
- ✅ TransactionRepositoryInterface.php - Contrato para TransactionRepository

**Domain Exceptions:**
- ✅ UserNotFoundException.php - Usuario no encontrado
- ✅ WalletNotFoundException.php - Billetera no encontrada
- ✅ TransactionNotFoundException.php - Transacción no encontrada
- ✅ InsufficientBalanceException.php - Saldo insuficiente
- ✅ DuplicateUserException.php - Usuario duplicado

#### Application Layer (Capa de Aplicación)

**Use Cases Implementados:**

1. **RegisterUserUseCase**
   - Registro de usuario con validación de duplicados
   - Creación automática de billetera con saldo 0
   - Validación de email mediante ValueObject
   - Retorna UserDTO completo

2. **CheckBalanceUseCase**
   - Consulta de saldo por documento
   - Retorna BalanceResponseDTO con información completa
   - Manejo de usuario no encontrado

3. **RechargeWalletUseCase**
   - Inicio de proceso de recarga (paso 1)
   - Generación de token y sessionId únicos
   - Validación de usuario y teléfono
   - Creación de transacción PENDING
   - Retorna TransactionDTO con datos de confirmación

4. **ConfirmPaymentUseCase**
   - Confirmación de recarga (paso 2)
   - Validación de token y sessionId
   - Simulación de pasarela de pagos
   - Acreditación de saldo si es exitoso
   - Actualización de estado (COMPLETED o FAILED)

5. **MakePaymentUseCase**
   - Realización de pago inmediato
   - Validación de saldo suficiente
   - Débito automático de billetera
   - Creación de transacción tipo PAYMENT
   - Retorna PaymentResponseDTO con saldo anterior y nuevo

**DTOs (Data Transfer Objects):**
- ✅ UserDTO - Transferencia de datos de usuario
- ✅ WalletDTO - Transferencia de datos de billetera
- ✅ TransactionDTO - Transferencia de datos de transacción
- ✅ BalanceResponseDTO - Respuesta de consulta de saldo
- ✅ PaymentResponseDTO - Respuesta de pago

**Request Objects:**
- ✅ Cada UseCase tiene su Request object con validación
- ✅ Método validate() centralizado
- ✅ Readonly properties para inmutabilidad

#### Infrastructure Layer (Capa de Infraestructura)

**Persistence (MySQL):**
- ✅ Connection.php - Singleton PDO con manejo de errores
- ✅ MySQLUserRepository.php - Implementación completa con prepared statements
- ✅ MySQLWalletRepository.php - CRUD de billeteras
- ✅ MySQLTransactionRepository.php - Gestión de transacciones

**HTTP Controllers:**
- ✅ HealthController.php - Health check con verificación de BD
- ✅ UserController.php - Endpoints de usuario (register, getBalance)
- ✅ TransactionController.php - Endpoints de transacciones (recharge, confirm, payment)

**Middleware:**
- ✅ ApiKeyAuthMiddleware.php - Autenticación por API Key
- ✅ CorsMiddleware.php - Headers CORS configurados
- ✅ ErrorMiddleware.php - Manejo centralizado de errores

**Routing:**
- ✅ api.php - Definición de rutas con Slim
- ✅ Route groups para organización
- ✅ Middleware stack configurado

**Front Controller:**
- ✅ public/index.php - Punto de entrada HTTP
- ✅ Error handling middleware
- ✅ CORS middleware global
- ✅ Body parsing middleware

#### Base de Datos

**Schema MySQL:**
- ✅ users table - Documento y email únicos, índices
- ✅ wallets table - Relación 1:1 con users, balance con 2 decimales
- ✅ transactions table - ENUMs para type y status, índices múltiples
- ✅ Foreign keys con CASCADE DELETE
- ✅ CHECK constraints para validación

**Migrations:**
- ✅ init_database.sql - Script consolidado de creación
- ✅ Charset UTF8MB4 para soporte completo Unicode
- ✅ InnoDB engine para transacciones

#### Shared Layer (Código Compartido)

**Utilities:**
- ✅ Logger.php - Wrapper de Monolog con niveles configurables
- ✅ ResponseFormatter.php - Formateo consistente de respuestas JSON
  - success() - Respuestas exitosas
  - error() - Respuestas de error con data opcional
  - validationError() - Errores de validación
  - notFound() - Recursos no encontrados
  - unauthorized() - No autorizado
  - conflict() - Conflictos (duplicados)
  - serverError() - Errores del servidor

#### Configuración

**Dependency Injection:**
- ✅ config/dependencies.php - PHP-DI container configurado
- ✅ Binding de interfaces a implementaciones
- ✅ Inyección automática en controllers

**Environment:**
- ✅ .env.example - Plantilla de variables de entorno
- ✅ vlucas/phpdotenv integrado
- ✅ Variables para BD, API Key, logging

**Composer:**
- ✅ composer.json con todas las dependencias
- ✅ Scripts: start, test
- ✅ PSR-4 autoloading configurado

#### Seguridad

- ✅ API Key authentication
- ✅ PDO prepared statements (prevención SQL Injection)
- ✅ Validación de entrada en múltiples capas
- ✅ CORS configurado
- ✅ Headers de seguridad

#### Testing

**Interfaz Web:**
- ✅ public/test.html - Interfaz completa de testing
- ✅ Formularios para todos los endpoints
- ✅ Auto-copia de tokens y sessionIds
- ✅ Respuestas coloreadas (verde/rojo)
- ✅ Pre-llenado de datos de prueba

**Scripts:**
- ✅ test-api.ps1 - Script PowerShell para testing
- ✅ test-api.sh - Script Bash para testing
- ✅ Flujo completo de pruebas automatizado

#### Documentación

- ✅ README.md - Documentación principal con badges y ejemplos
- ✅ SETUP.md - Guía de instalación detallada
- ✅ FEATURES.md - Lista completa de características
- ✅ DOCUMENTATION.md - Documentación técnica completa
- ✅ VALIDACION_REQUERIMIENTOS.md - Validación contra requisitos (100%)
- ✅ CHANGELOG.md - Este archivo

### 🔧 Dependencias

**Producción:**
- slim/slim: ^4.0
- slim/psr7: ^1.8
- php-di/php-di: ^7.1
- vlucas/phpdotenv: ^5.6
- monolog/monolog: ^3.10

**Desarrollo:**
- phpunit/phpunit: ^10.0 (para futuro testing)

### 🐛 Correcciones de Bugs

#### Sesión de Bug Fixing 1 (2026-02-20 AM)

- ✅ **API Key Middleware:** Corregido fallback a query parameter
- ✅ **/api/health público:** Movido fuera del grupo autenticado
- ✅ **Composer dependencies:** Regenerado composer.lock
- ✅ **Database init:** Consolidado en init_database.sql
- ✅ **sessionId naming:** Cambiado de snake_case a camelCase en frontend

#### Sesión de Bug Fixing 2 (2026-02-20 PM)

- ✅ **UserController scope error:** Movido $document fuera de try-catch
- ✅ **ConfirmPaymentRequest:** Mensaje de validación corregido
- ✅ **TransactionController:** Acepta sessionId en camelCase
- ✅ **PAYMENT_SUCCESS_RATE:** Cambiado de 0.8 a 1.0 (100% éxito)
- ✅ **Confirm response:** Retorna error cuando status=FAILED
- ✅ **ResponseFormatter::error():** Agregado parámetro $data opcional
- ✅ **Server restart:** Instrucciones para detener y reiniciar

### 📊 Métricas del Proyecto

**Líneas de Código:**
- Domain Layer: ~800 líneas
- Application Layer: ~1,200 líneas
- Infrastructure Layer: ~1,500 líneas
- Total: ~3,500 líneas de código PHP

**Archivos:**
- 35 archivos PHP de producción
- 3 archivos de configuración
- 6 archivos de documentación
- 1 script SQL
- 1 interfaz de testing HTML

**Cobertura de Requisitos:**
- Requisitos funcionales: 5/5 (100%)
- Requisitos técnicos: 7/7 (100%)
- Características extras: 8

### 🎯 Estado del Proyecto

- ✅ Backend API completamente funcional
- ✅ Clean Architecture implementada
- ✅ Base de datos MySQL configurada
- ✅ Documentación completa
- ✅ Interfaz de testing disponible
- ⏳ Deploy en producción (próximamente)
- ⏳ Tests unitarios (próximamente)
- ⏳ Tests de integración (próximamente)

### 🚀 Próximos Pasos

Planeados para [1.1.0]:
- Tests unitarios con PHPUnit
- Tests de integración
- CI/CD con GitHub Actions (Railway ya tiene deploy automático)
- Documentación de API con OpenAPI/Swagger
- Frontend en Vue.js
- Logging avanzado con Monolog handlers
- Rate limiting mejorado

---

## [Unreleased]

### 🔮 En Desarrollo

Nada actualmente.

### 📝 Planeado

- Tests unitarios para todos los Use Cases
- Tests de integración para endpoints
- Deploy automatizado
- Frontend con Vue 3
- Panel de administración
- Historial de transacciones por usuario
- Filtros y paginación
- Notificaciones por email
- Webhook para pasarela real

---

## Tipos de Cambios

- **Added** - Nuevas características
- **Changed** - Cambios en funcionalidad existente
- **Deprecated** - Características que se eliminarán pronto
- **Removed** - Características eliminadas
- **Fixed** - Corrección de bugs
- **Security** - Correcciones de seguridad

---

## Versionado

Este proyecto usa [Semantic Versioning](https://semver.org/lang/es/):

- **MAJOR** version cuando hay cambios incompatibles en la API
- **MINOR** version cuando se agrega funcionalidad compatible con versiones anteriores
- **PATCH** version cuando se corrigen bugs compatibles con versiones anteriores

---

**Autor:** Anderson Lozano  
**Repositorio:** [github.com/LozanoAndersonTheStain/virtual-balance-backend](https://github.com/LozanoAndersonTheStain/virtual-balance-backend)
