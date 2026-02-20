# 💰 Virtual Balance Backend

![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Slim](https://img.shields.io/badge/Slim-4.0-719e40?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

Backend REST API para gestión integral de saldos virtuales desarrollado con **PHP 8.0+**, **Clean Architecture** y **SOLID Principles**. Sistema completo de billeteras virtuales con recargas, pagos y gestión de transacciones.

## 🌐 Demo en Vivo

🔗 **API Base URL:** `https://tu-proyecto.railway.app` *(Próximamente)*

📄 **Documentación Interactiva:** `/test.html`

## 📋 Tabla de Contenidos

- [Características](#-características)
- [Arquitectura](#️-arquitectura)
- [Tecnologías](#-tecnologías)
- [Instalación](#-instalación-rápida)
- [Endpoints API](#-endpoints-api)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Testing](#-testing)
- [Deploy](#-deploy)
- [Documentación Adicional](#-documentación-adicional)

## ✨ Características

### 🎯 Funcionalidades Principales

- ✅ **Gestión de Usuarios**
  - Registro de clientes con validación de datos
  - Verificación de documentos únicos
  - Validación de email con ValueObject
  - Creación automática de billetera virtual

- ✅ **Billetera Virtual**
  - Saldo inicial en 0 al crear usuario
  - Consulta de saldo por documento
  - Historial de transacciones
  - Validación de saldo insuficiente

- ✅ **Sistema de Recargas**
  - Proceso de dos pasos (iniciar + confirmar)
  - Generación de token y sessionId únicos
  - Simulación de pasarela de pagos (100% éxito)
  - Estados de transacción (PENDING, COMPLETED, FAILED)
  - Validación de teléfono

- ✅ **Gestión de Pagos**
  - Pago inmediato con descuento de saldo
  - Validación de saldo disponible
  - Registro de transacciones
  - Manejo de excepciones personalizadas

- ✅ **Arquitectura Robusta**
  - Clean Architecture (Domain → Application → Infrastructure)
  - Principios SOLID
  - Inyección de dependencias con PHP-DI
  - Repository Pattern
  - Value Objects para tipos primitivos

- ✅ **Seguridad**
  - Autenticación por API Key
  - Middleware de autorización
  - CORS configurado
  - Validación de entrada de datos
  - SQL Injection prevention (PDO prepared statements)

- ✅ **Logging y Monitoreo**
  - Sistema de logs con Monolog
  - Registro de operaciones críticas
  - Tracking de errores
  - Health check endpoint

## 🏗️ Estructura del Proyecto

```
virtual-balance-backend/
├── public/                          # Punto de entrada HTTP
│   ├── index.php                   # Front controller
│   └── test.html                   # Interfaz de testing
│
├── src/                            # Código fuente
│   ├── Domain/                     # Capa de Dominio (Lógica de negocio)
│   │   ├── Entities/
│   │   │   ├── User.php           # Entidad Usuario
│   │   │   ├── Wallet.php         # Entidad Billetera
│   │   │   └── Transaction.php    # Entidad Transacción
│   │   ├── ValueObjects/
│   │   │   ├── Email.php          # Value Object Email
│   │   │   ├── Balance.php        # Value Object Balance
│   │   │   ├── TransactionStatus.php
│   │   │   └── DocumentType.php
│   │   ├── Repositories/          # Interfaces de repositorios
│   │   │   ├── UserRepositoryInterface.php
│   │   │   ├── WalletRepositoryInterface.php
│   │   │   └── TransactionRepositoryInterface.php
│   │   └── Exceptions/            # Excepciones de dominio
│   │       ├── UserNotFoundException.php
│   │       ├── WalletNotFoundException.php
│   │       ├── InsufficientBalanceException.php
│   │       └── DuplicateUserException.php
│   │
│   ├── Application/               # Capa de Aplicación (Casos de uso)
│   │   └── UseCases/
│   │       ├── RegisterUser/
│   │       │   ├── RegisterUserUseCase.php
│   │       │   ├── RegisterUserRequest.php
│   │       │   └── UserDTO.php
│   │       ├── CheckBalance/
│   │       │   ├── CheckBalanceUseCase.php
│   │       │   ├── CheckBalanceRequest.php
│   │       │   └── BalanceResponseDTO.php
│   │       ├── RechargeWallet/
│   │       │   ├── RechargeWalletUseCase.php
│   │       │   ├── RechargeWalletRequest.php
│   │       │   └── TransactionDTO.php
│   │       ├── ConfirmPayment/
│   │       │   ├── ConfirmPaymentUseCase.php
│   │       │   └── ConfirmPaymentRequest.php
│   │       └── MakePayment/
│   │           ├── MakePaymentUseCase.php
│   │           ├── MakePaymentRequest.php
│   │           └── PaymentResponseDTO.php
│   │
│   ├── Infrastructure/            # Capa de Infraestructura
│   │   ├── Persistence/
│   │   │   ├── Connection.php                 # PDO Singleton
│   │   │   ├── MySQLUserRepository.php
│   │   │   ├── MySQLWalletRepository.php
│   │   │   └── MySQLTransactionRepository.php
│   │   └── Http/
│   │       ├── Controllers/
│   │       │   ├── HealthController.php
│   │       │   ├── UserController.php
│   │       │   └── TransactionController.php
│   │       ├── Middleware/
│   │       │   ├── ApiKeyAuthMiddleware.php
│   │       │   ├── CorsMiddleware.php
│   │       │   └── ErrorMiddleware.php
│   │       └── Routes/
│   │           └── api.php                    # Definición de rutas
│   │
│   └── Shared/                    # Código compartido
│       └── Utils/
│           ├── Logger.php         # Wrapper de Monolog
│           └── ResponseFormatter.php
│
├── database/                      # Migraciones y scripts SQL
│   └── migrations/
│       └── init_database.sql     # Script completo de BD
│
├── logs/                          # Archivos de log
│   └── app.log
│
├── config/                        # Configuración
│   └── dependencies.php          # PHP-DI container
│
├── .env                           # Variables de entorno (no versionado)
├── .env.example                   # Ejemplo de variables
├── composer.json                  # Dependencias PHP
├── README.md                      # Este archivo
├── SETUP.md                       # Guía de instalación detallada
├── FEATURES.md                    # Lista de características
├── DOCUMENTATION.md               # Documentación técnica
├── CHANGELOG.md                   # Historial de cambios
└── VALIDACION_REQUERIMIENTOS.md   # Validación vs requisitos
```

## 🚀 Tecnologías

### Core

- **PHP 8.0+** - Lenguaje de programación con typed properties
- **Slim Framework 4** - Micro-framework PSR-7/PSR-15
- **MySQL 5.7+** - Base de datos relacional
- **Composer 2.x** - Gestor de dependencias

### Dependencias Principales

- **slim/slim** `^4.0` - Framework HTTP
- **slim/psr7** `^1.8` - Implementación PSR-7 (HTTP messages)
- **php-di/php-di** `^7.1` - Contenedor de inyección de dependencias
- **vlucas/phpdotenv** `^5.6` - Gestión de variables de entorno
- **monolog/monolog** `^3.10` - Sistema de logging PSR-3

### Arquitectura y Patrones

- **Clean Architecture** - Separación de capas (Domain, Application, Infrastructure)
- **SOLID Principles** - Diseño orientado a objetos
- **Repository Pattern** - Abstracción de acceso a datos
- **Dependency Injection** - Inversión de control
- **Value Objects** - Encapsulación de lógica de validación
- **DTOs** - Data Transfer Objects para comunicación entre capas

### Base de Datos

- **InnoDB Engine** - Motor de almacenamiento con transacciones
- **UTF8MB4 Charset** - Soporte completo de caracteres Unicode
- **Foreign Keys** - Integridad referencial con CASCADE
- **Check Constraints** - Validaciones a nivel de BD
- **Indexes** - Optimización de consultas

## 📦 Instalación Rápida

### Requisitos Previos

- PHP >= 8.0 (con extensiones: pdo, pdo_mysql, mbstring, json)
- Composer >= 2.0
- MySQL >= 5.7 o MariaDB >= 10.2
- Git

### Instalación Paso a Paso

```bash
# 1. Clonar el repositorio
git clone https://github.com/LozanoAndersonTheStain/virtual-balance-backend.git
cd virtual-balance-backend

# 2. Instalar dependencias
composer install

# 3. Configurar variables de entorno
cp .env.example .env

# 4. Editar .env con tus credenciales (importante)
# DB_HOST=localhost
# DB_NAME=virtual_balance
# DB_USER=root
# DB_PASS=tu_password
# API_KEY=3d1ae36128db9f079fd5f0b4af05ba16ee7bfbea94343623ef2055cf2272ccfe

# 5. Crear base de datos
mysql -u root -p < database/migrations/init_database.sql

# 6. Iniciar servidor de desarrollo
php -d opcache.enable=0 -S localhost:8000 -t public

# 7. Verificar instalación
curl http://localhost:8000/api/health
```

**Resultado esperado del health check:**
```json
{
  "success": true,
  "message": "API funcionando correctamente",
  "data": {
    "status": "ok",
    "timestamp": "2026-02-20 12:00:00",
    "service": "Virtual Balance API",
    "version": "1.0.0",
    "database": "connected"
  }
}
```

### Interfaz de Testing

Abre en tu navegador: `http://localhost:8000/test.html`

Interfaz web con formularios para probar todos los endpoints.

## 📝 Endpoints API

### 🔐 Autenticación

Todas las rutas (excepto `/api/health`) requieren el header:

```http
X-API-Key: 3d1ae36128db9f079fd5f0b4af05ba16ee7bfbea94343623ef2055cf2272ccfe
```

### Endpoints Disponibles

| Método | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| GET | `/api/health` | Health check del servicio | ❌ No |
| POST | `/api/users/register` | Registrar nuevo usuario | ✅ Sí |
| GET | `/api/users/{document}/balance` | Consultar saldo | ✅ Sí |
| POST | `/api/transactions/recharge` | Iniciar recarga de saldo | ✅ Sí |
| POST | `/api/transactions/confirm` | Confirmar recarga pendiente | ✅ Sí |
| POST | `/api/transactions/payment` | Realizar pago | ✅ Sí |
| POST | `/api/notifications/payment` | **Webhook para notificaciones de pago** | ✅ Sí |

### 📘 Ejemplos de Uso

#### 1. Health Check

```bash
curl -X GET http://localhost:8000/api/health
```

**Respuesta 200 OK:**
```json
{
  "success": true,
  "message": "API funcionando correctamente",
  "data": {
    "status": "ok",
    "database": "connected"
  }
}
```

#### 2. Registrar Usuario

```bash
curl -X POST http://localhost:8000/api/users/register \
  -H "Content-Type: application/json" \
  -H "X-API-Key: 3d1ae36128db9f079fd5f0b4af05ba16ee7bfbea94343623ef2055cf2272ccfe" \
  -d '{
    "document": "1234567890",
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "phone": "3001234567"
  }'
```

**Respuesta 201 Created:**
```json
{
  "success": true,
  "message": "Usuario registrado exitosamente",
  "data": {
    "id": 1,
    "document": "1234567890",
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "phone": "3001234567",
    "wallet_id": 1,
    "balance": 0
  }
}
```

#### 3. Consultar Saldo

```bash
curl -X GET http://localhost:8000/api/users/1234567890/balance \
  -H "X-API-Key: 3d1ae36128db9f079fd5f0b4af05ba16ee7bfbea94343623ef2055cf2272ccfe"
```

**Respuesta 200 OK:**
```json
{
  "success": true,
  "message": "Saldo consultado exitosamente",
  "data": {
    "user_id": 1,
    "user_name": "Juan Pérez",
    "document": "1234567890",
    "wallet_id": 1,
    "balance": 50000,
    "currency": "COP"
  }
}
```

#### 4. Iniciar Recarga de Saldo

```bash
curl -X POST http://localhost:8000/api/transactions/recharge \
  -H "Content-Type: application/json" \
  -H "X-API-Key: 3d1ae36128db9f079fd5f0b4af05ba16ee7bfbea94343623ef2055cf2272ccfe" \
  -d '{
    "document": "1234567890",
    "phone": "3001234567",
    "amount": 50000
  }'
```

**Respuesta 201 Created:**
```json
{
  "success": true,
  "message": "Recarga iniciada. Proceda a confirmar con el token",
  "data": {
    "transaction_id": 1,
    "token": "tok_507f1f77bcf86cd799439011",
    "sessionId": "sess_507f191e810c19729de860ea",
    "amount": 50000,
    "status": "PENDING"
  }
}
```

#### 5. Confirmar Recarga

```bash
curl -X POST http://localhost:8000/api/transactions/confirm \
  -H "Content-Type: application/json" \
  -H "X-API-Key: 3d1ae36128db9f079fd5f0b4af05ba16ee7bfbea94343623ef2055cf2272ccfe" \
  -d '{
    "token": "tok_507f1f77bcf86cd799439011",
    "sessionId": "sess_507f191e810c19729de860ea"
  }'
```

**Respuesta 200 OK (Exitosa):**
```json
{
  "success": true,
  "message": "Transacción confirmada exitosamente. Saldo acreditado",
  "data": {
    "transaction_id": 1,
    "status": "COMPLETED",
    "amount": 50000,
    "new_balance": 50000
  }
}
```

**Respuesta 400 Bad Request (Fallida):**
```json
{
  "success": false,
  "message": "La transacción fue rechazada por la pasarela de pagos",
  "data": {
    "transaction_id": 1,
    "status": "FAILED",
    "amount": 50000
  }
}
```

#### 6. Webhook de Notificaciones (Pasarelas Externas)

> 📡 **Uso:** Este endpoint está diseñado para que pasarelas de pago externas (PSE, Nequi, Bancolombia, etc.) notifiquen confirmaciones de pago.

```bash
curl -X POST http://localhost:8000/api/notifications/payment \
  -H "Content-Type: application/json" \
  -H "X-API-Key: 3d1ae36128db9f079fd5f0b4af05ba16ee7bfbea94343623ef2055cf2272ccfe" \
  -d '{
    "token": "tok_507f1f77bcf86cd799439011",
    "sessionId": "sess_507f191e810c19729de860ea"
  }'
```

**Respuesta 200 OK (Pago Confirmado):**
```json
{
  "success": true,
  "message": "Notificación de pago recibida y procesada exitosamente. Saldo actualizado.",
  "data": {
    "transaction_id": 1,
    "status": "COMPLETED",
    "amount": 50000,
    "new_balance": 50000
  }
}
```

**Respuesta 400 Bad Request (Pago Fallido):**
```json
{
  "success": false,
  "message": "Notificación recibida. La transacción fue marcada como fallida.",
  "data": {
    "transaction_id": 1,
    "status": "FAILED",
    "amount": 50000
  }
}
```

**🔑 Diferencia entre `/confirm` y `/notifications/payment`:**
- **`/api/transactions/confirm`**: Endpoint genérico para confirmar transacciones (puede ser llamado por cliente)
- **`/api/notifications/payment`**: Webhook específico para pasarelas externas, con logging detallado y auditoría

#### 7. Realizar Pago

```bash
curl -X POST http://localhost:8000/api/transactions/payment \
  -H "Content-Type: application/json" \
  -H "X-API-Key: 3d1ae36128db9f079fd5f0b4af05ba16ee7bfbea94343623ef2055cf2272ccfe" \
  -d '{
    "document": "1234567890",
    "amount": 10000
  }'
```

**Respuesta 200 OK:**
```json
{
  "success": true,
  "message": "Pago realizado exitosamente",
  "data": {
    "transaction_id": 2,
    "previous_balance": 50000,
    "amount_paid": 10000,
    "new_balance": 40000,
    "status": "COMPLETED"
  }
}
```

### ⚠️ Respuestas de Error

#### Usuario no encontrado (404)
```json
{
  "success": false,
  "message": "Usuario no encontrado con el documento: 1234567890",
  "timestamp": "2026-02-20 12:00:00"
}
```

#### Saldo insuficiente (400)
```json
{
  "success": false,
  "message": "Saldo insuficiente",
  "errors": ["Balance actual: 5000 COP, Requerido: 10000 COP"],
  "timestamp": "2026-02-20 12:00:00"
}
```

#### API Key inválida (401)
```json
{
  "success": false,
  "message": "API Key inválida o no proporcionada",
  "timestamp": "2026-02-20 12:00:00"
}
```

#### Validación de datos (400)
```json
{
  "success": false,
  "message": "Datos de entrada inválidos",
  "errors": {
    "email": "El email no es válido",
    "amount": "El monto debe ser mayor a 0"
  },
  "timestamp": "2026-02-20 12:00:00"
}
```

## 🧪 Testing

### Ejecutar Tests

```bash
# Tests unitarios (próximamente)
composer test

# Coverage report (próximamente)
composer test:coverage
```

### Interfaz de Testing Manual

Abre `http://localhost:8000/test.html` para acceder a la interfaz web de testing que incluye:

- ✅ Formularios pre-llenados para cada endpoint
- ✅ Auto-copia de tokens y sessionIds
- ✅ Respuestas coloreadas (verde = éxito, rojo = error)
- ✅ Historial de operaciones
- ✅ Health check en tiempo real

### Scripts de Testing (Bash/PowerShell)

```bash
# Linux/Mac
./test-api.sh

# Windows PowerShell
.\test-api.ps1
```

## 🚀 Deploy

Este proyecto se puede desplegar usando:

🌐 **Backend PHP**: [Render.com](https://render.com)  
🗄️ **Base de Datos**: [Clever Cloud](https://www.clever-cloud.com) - MySQL  

> **⚠️ Nota:** Railway.app ahora requiere tarjeta de crédito para uso continuo

### 📖 Guía Completa de Deploy

**👉 Ver la guía paso a paso detallada:** **[DEPLOY.md](DEPLOY.md)**

### ⚡ Inicio Rápido (Resumen)

#### 1️⃣ Clever Cloud (MySQL)

```bash
# 1. Crear cuenta en https://www.clever-cloud.com
# 2. Crear addon MySQL: virtual-balance-db
# 3. Obtener credenciales de conexión
# 4. Ejecutar migraciones vía phpMyAdmin o MySQL
```

[**📚 Guía detallada de Clever Cloud**](DEPLOY.md#-parte-1-configurar-clever-cloud-mysql)

#### 2️⃣ Render.com (Backend PHP)

```bash
# 1. Crear cuenta en https://render.com
# 2. New + → Blueprint
# 3. Conectar repositorio GitHub
# 4. Configurar variables de entorno (credenciales de Clever Cloud)
# 5. Apply → Esperar deploy (~5 min)
```

[**📚 Guía detallada de Render.com**](DEPLOY.md#-parte-2-configurar-rendercom-backend-php)

#### 3️⃣ Verificar Deploy

```bash
# Probar health check
curl https://tu-servicio.onrender.com/api/health \
  -H "X-API-Key: tu_api_key"

# Respuesta esperada:
# {"status":"success","message":"API is running","database":"connected"}
```

### 🔧 Archivos de Configuración Incluidos

- ✅ **`Dockerfile`** - Imagen Docker con PHP 8.2 + Apache
- ✅ **`render.yaml`** - Blueprint de Render.com (auto-deploy)
- ✅ **`.dockerignore`** - Optimización de build
- ✅ **`DEPLOY.md`** - Guía completa paso a paso

### ⚠️ Consideraciones del Deployment

**Render.com:**
- ⚠️ El servicio se duerme después de 15 min sin actividad (plan básico)
- 🔄 Primera request tarda ~30 seg al despertar
- ✅ HTTPS automático incluido
- ✅ Deploy automático con git push

**Clever Cloud MySQL:**
- 256 MB de RAM (plan DEV)
- ~100 MB de storage
- 5 conexiones simultáneas
- phpMyAdmin incluido

### 🎯 Deploy Automático con GitHub

Render hace deploy automático cada vez que haces push:

```bash
git add .
git commit -m "feat: nueva funcionalidad"
git push origin main
# ✅ Render detecta el push y hace deploy automático
```

### 🔑 Variables de Entorno (Producción)

```env
# Clever Cloud MySQL (configurar manualmente)
DB_HOST=bmxxxxxxxx-mysql.services.clever-cloud.com
DB_NAME=bmxxxxxxxx
DB_USER=uxxxxxxxx
DB_PASS=xxxxxxxxxxxx
DB_PORT=3306

# Application (configurar manualmente)
API_KEY=<genera-con: openssl rand -hex 32>
APP_ENV=production
APP_DEBUG=false
PAYMENT_SUCCESS_RATE=1.0
```

**Generar API Key segura:**
```bash
openssl rand -hex 32
```

### 📊 Monitoreo y Logs

**Render Dashboard:**
- 📈 Métricas de uso (CPU, RAM, requests)
- 📝 Logs en tiempo real
- 🔄 Historial de deploys
- ⚡ Health checks automáticos

**Clever Cloud Dashboard:**
- 💾 Espacio usado
- 📊 Métricas de conexiones
- 🔍 Logs de MySQL
- 🛠️ phpMyAdmin integrado

### 🆘 Troubleshooting

**El servicio tarda en responder:**
- El plan básico de Render se duerme después de 15 min sin actividad
- Primera request tarda ~30 segundos al despertar
- Requests posteriores son instantáneas

**Error de conexión a base de datos:**
- Verificar credenciales de Clever Cloud en Render
- Ver logs en Render Dashboard → tu servicio → Logs
- Verificar que el addon MySQL esté activo en Clever Cloud

**Ver guía completa de troubleshooting:** [DEPLOY.md - Troubleshooting](DEPLOY.md#-troubleshooting)

## 📚 Documentación Adicional

- **[DEPLOY.md](DEPLOY.md)** - **🚀 Guía completa de deploy (Render + Clever Cloud)**
- **[SETUP.md](SETUP.md)** - Guía de instalación local paso a paso
- **[WEBHOOKS.md](WEBHOOKS.md)** - **🔔 Documentación de webhooks para pasarelas de pago**
- **[FEATURES.md](FEATURES.md)** - Características y funcionalidades detalladas
- **[DOCUMENTATION.md](DOCUMENTATION.md)** - Documentación técnica de arquitectura
- **[CHANGELOG.md](CHANGELOG.md)** - Historial de versiones y cambios
- **[VALIDACION_REQUERIMIENTOS.md](VALIDACION_REQUERIMIENTOS.md)** - Validación contra requisitos

## 🛠️ Scripts Disponibles

```bash
# Iniciar servidor de desarrollo
composer start
# o
php -S localhost:8000 -t public

# Regenerar autoload
composer dump-autoload

# Ejecutar tests (próximamente)
composer test

# Verificar código (próximamente)
composer lint
```

## 🌟 Principios de Diseño

Este proyecto implementa:

### Clean Architecture

- **Domain Layer:** Lógica de negocio independiente de frameworks
- **Application Layer:** Casos de uso que orquestan el dominio
- **Infrastructure Layer:** Detalles técnicos (BD, HTTP, etc.)

### SOLID Principles

- **S**ingle Responsibility: Cada clase tiene una única razón de cambio
- **O**pen/Closed: Abierto a extensión, cerrado a modificación
- **L**iskov Substitution: Las implementaciones son intercambiables
- **I**nterface Segregation: Interfaces específicas por cliente
- **D**ependency Inversion: Dependencias hacia abstracciones

### Patrones Implementados

- **Repository Pattern:** Abstracción del acceso a datos
- **Use Case Pattern:** Lógica de aplicación encapsulada
- **DTO Pattern:** Transferencia de datos entre capas
- **Value Object:** Tipos de dominio con validación
- **Dependency Injection:** Inversión de control
- **Singleton:** Conexión de base de datos

## 📊 Estado del Proyecto

- ✅ Arquitectura Clean Architecture implementada
- ✅ Domain layer completo (3 Entities, 4 ValueObjects, 5 Exceptions)
- ✅ Application layer completo (5 UseCases con DTOs)
- ✅ Infrastructure layer completo (3 Repositories, 3 Controllers, 2 Middleware)
- ✅ Base de datos MySQL con migraciones
- ✅ Autenticación por API Key
- ✅ Sistema de logging
- ✅ Interfaz de testing web
- ✅ Documentación completa
- ⏳ Tests unitarios (próximamente)
- ⏳ Tests de integración (próximamente)
- ⏳ Deploy en producción (próximamente)

## 🤝 Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add: Amazing feature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver archivo `LICENSE` para más detalles.

## 👨‍💻 Autor

**Anderson Lozano**
- Email: adslozanov@gmail.com
- GitHub: [@LozanoAndersonTheStain](https://github.com/LozanoAndersonTheStain)
- Proyecto: [virtual-balance-backend](https://github.com/LozanoAndersonTheStain/virtual-balance-backend)

---

⭐ Si este proyecto te fue útil, considera darle una estrella en GitHub!

**Anderson Lozano**
- Email: adslozanov@gmail.com
- GitHub: [@LozanoAndersonTheStain](https://github.com/LozanoAndersonTheStain)

## 📄 Licencia

Este proyecto es una prueba técnica para Virtualsoft - Integraciones.

---

**Desarrollado con ❤️ siguiendo Clean Architecture y principios SOLID**
