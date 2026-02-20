# 🚀 Guía de Configuración - Virtual Balance Backend

## 📋 Prerrequisitos

- PHP >= 8.0
- MySQL >= 5.7
- Composer
- Servidor web (Apache/Nginx) o PHP built-in server

## ⚙️ Configuración Paso a Paso

### 1. Clonar el Repositorio

```bash
git clone https://github.com/LozanoAndersonTheStain/virtual-balance-backend.git
cd virtual-balance-backend
```

### 2. Instalar Dependencias

```bash
composer install
```

### 3. Configurar Variables de Entorno

```bash
# Copiar archivo de ejemplo
cp .env.example .env

# Editar .env con tus credenciales
```

Configurar en `.env`:
```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=virtual_balance
DB_USER=root
DB_PASS=tu_password

API_KEY=tu_api_key_secreta_aqui
```

### 4. Crear Base de Datos

**Opción A: Usando MySQL CLI**
```bash
mysql -u root -p < database/migrations/init_database.sql
```

**Opción B: Manual**
```sql
-- Crear base de datos
CREATE DATABASE virtual_balance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE virtual_balance;

-- Ejecutar migraciones una por una
SOURCE database/migrations/001_create_users_table.sql;
SOURCE database/migrations/002_create_wallets_table.sql;
SOURCE database/migrations/003_create_transactions_table.sql;
```

### 5. Iniciar Servidor

**Opción A: PHP Built-in Server (Development)**
```bash
composer start
# O manualmente:
php -S localhost:8000 -t public
```

**Opción B: Apache/Nginx**
- Configurar document root: `/public`
- Habilitar mod_rewrite (Apache)

### 6. Verificar Instalación

```bash
# Probar health check
curl http://localhost:8000/api/health
```

## 🧪 Probar la API

### 1. Registrar Usuario

```bash
curl -X POST http://localhost:8000/api/users/register \
  -H "Content-Type: application/json" \
  -H "X-API-Key: tu_api_key_secreta_aqui" \
  -d '{
    "document": "1234567890",
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "phone": "3001234567"
  }'
```

### 2. Consultar Saldo

```bash
curl -X GET "http://localhost:8000/api/users/1234567890/balance" \
  -H "X-API-Key: tu_api_key_secreta_aqui"
```

### 3. Recargar Saldo

```bash
curl -X POST http://localhost:8000/api/transactions/recharge \
  -H "Content-Type: application/json" \
  -H "X-API-Key: tu_api_key_secreta_aqui" \
  -d '{
    "document": "1234567890",
    "amount": 50000
  }'
```

### 4. Confirmar Recarga

```bash
# Usar el token y session_id del paso anterior
curl -X POST http://localhost:8000/api/transactions/confirm \
  -H "Content-Type: application/json" \
  -H "X-API-Key: tu_api_key_secreta_aqui" \
  -d '{
    "token": "tok_xxxxx",
    "session_id": "sess_xxxxx"
  }'
```

### 5. Realizar Pago

```bash
curl -X POST http://localhost:8000/api/transactions/payment \
  -H "Content-Type: application/json" \
  -H "X-API-Key: tu_api_key_secreta_aqui" \
  -d '{
    "document": "1234567890",
    "amount": 10000,
    "description": "Pago de prueba"
  }'
```

## 📚 Endpoints Disponibles

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/health` | Health check |
| POST | `/api/users/register` | Registrar usuario |
| GET | `/api/users/{document}/balance` | Consultar saldo |
| POST | `/api/transactions/recharge` | Iniciar recarga |
| POST | `/api/transactions/payment` | Realizar pago |
| POST | `/api/transactions/confirm` | Confirmar transacción |

## 🔐 Autenticación

Todas las rutas (excepto `/api/health`) requieren header:
```
X-API-Key: tu_api_key_secreta_aqui
```

## 📝 Logs

Los logs se guardan en:
```
logs/app.log
```

## � Deploy a Producción

### Opción 1: Railway.app (Recomendado para PHP)

Railway es una plataforma moderna que soporta PHP nativamente y es muy fácil de usar.

#### Instalación de Railway CLI

```bash
# Con npm
npm install -g @railway/cli

# O con curl (Linux/Mac)
curl -fsSL https://railway.app/install.sh | sh
```

#### Desplegar en Railway

```bash
# 1. Login en Railway
railway login

# 2. Inicializar proyecto
railway init

# 3. Agregar MySQL database
railway add mysql

# 4. Configurar variables de entorno
railway variables set API_KEY=$(openssl rand -hex 32)
railway variables set APP_ENV=production
railway variables set APP_DEBUG=false
railway variables set PAYMENT_SUCCESS_RATE=1.0

# 5. Deploy
railway up

# 6. Ejecutar migraciones
railway run mysql -u root -p${MYSQLDATABASE} < database/migrations/init_database.sql
```

#### Configurar Variables de Entorno en Railway Dashboard

1. Ve a tu proyecto en railway.app
2. Click en "Variables"
3. Agrega las siguientes:
   ```
   DB_HOST=${{MYSQLHOST}}
   DB_PORT=${{MYSQLPORT}}
   DB_NAME=${{MYSQLDATABASE}}
   DB_USER=${{MYSQLUSER}}
   DB_PASS=${{MYSQLPASSWORD}}
   API_KEY=<genera-uno-seguro>
   APP_ENV=production
   APP_DEBUG=false
   PAYMENT_SUCCESS_RATE=1.0
   ```

**Archivo de configuración:** El proyecto incluye `railway.json` con la configuración necesaria.

#### Conectar con GitHub (Deploy Automático)

Railway puede hacer deploy automático cada vez que haces push a GitHub:

1. **En el dashboard de Railway:**
   - Ve a tu proyecto
   - Click en **Settings** → **Source**
   - Conecta tu repositorio de GitHub
   - Selecciona la rama `main`

2. **Deploy automático:**
   - Cada `git push` disparará un nuevo deploy
   - Railway ejecutará `composer install` automáticamente
   - El servidor se reiniciará con la nueva versión

3. **Verificar deploy:**
   ```bash
   # Ver logs en tiempo real
   railway logs
   
   # Ver el dominio asignado
   railway domain
   
   # Abrir en el navegador
   railway open
   ```

#### Troubleshooting Railway

**Ver logs:**
```bash
railway logs --tail 100
```

**Conectarse a MySQL:**
```bash
# Obtener variables de entorno
railway variables

# Conectarse directamente
railway run mysql -h $MYSQLHOST -u $MYSQLUSER -p$MYSQLPASSWORD $MYSQLDATABASE
```

**Restart del servicio:**
```bash
railway restart
```

**Archivo de configuración:** El proyecto incluye `Procfile` para Heroku.

### Opción 4: VPS (DigitalOcean, AWS, etc.)

Para un VPS tradicional con más control.

#### Configuración en Ubuntu/Debian

```bash
# 1. Actualizar sistema
sudo apt update && sudo apt upgrade -y

# 2. Instalar LAMP stack
sudo apt install apache2 mysql-server php8.0 php8.0-mysql php8.0-mbstring php8.0-xml -y

# 3. Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 4. Clonar repositorio
cd /var/www
sudo git clone https://github.com/LozanoAndersonTheStain/virtual-balance-backend.git
cd virtual-balance-backend

# 5. Configurar permisos
sudo chown -R www-data:www-data /var/www/virtual-balance-backend
sudo chmod -R 755 /var/www/virtual-balance-backend

# 6. Instalar dependencias
sudo -u www-data composer install --no-dev --optimize-autoloader

# 7. Configurar .env
sudo -u www-data cp .env.example .env
sudo -u www-data nano .env
# Editar con las credenciales correctas

# 8. Crear base de datos
sudo mysql -u root -p < database/migrations/init_database.sql

# 9. Configurar Apache Virtual Host
sudo nano /etc/apache2/sites-available/virtual-balance.conf
```

**Contenido del Virtual Host:**
```apache
<VirtualHost *:80>
    ServerName tu-dominio.com
    DocumentRoot /var/www/virtual-balance-backend/public

    <Directory /var/www/virtual-balance-backend/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/virtual-balance-error.log
    CustomLog ${APACHE_LOG_DIR}/virtual-balance-access.log combined
</VirtualHost>
```

```bash
# 10. Habilitar sitio y mod_rewrite
sudo a2ensite virtual-balance.conf
sudo a2enmod rewrite
sudo systemctl restart apache2

# 11. Configurar SSL con Let's Encrypt (Recomendado)
sudo apt install certbot python3-certbot-apache -y
sudo certbot --apache -d tu-dominio.com
```

### Variables de Entorno para Producción

**⚠️ IMPORTANTE:** Nunca expongas credenciales reales en el código.

```env
# Base de datos
DB_HOST=<host-de-tu-bd>
DB_PORT=3306
DB_NAME=virtual_balance
DB_USER=<usuario-bd>
DB_PASS=<password-seguro>

# API Security
API_KEY=<genera-con: openssl rand -hex 32>

# Environment
APP_ENV=production
APP_DEBUG=false

# Business Logic
PAYMENT_SUCCESS_RATE=1.0
```

### Generar API Key Segura

```bash
# Linux/Mac
openssl rand -hex 32

# PowerShell (Windows)
-join ((48..57) + (65..90) + (97..122) | Get-Random -Count 64 | % {[char]$_})

# Online
https://www.random.org/strings/?num=1&len=64&digits=on&upperalpha=on&loweralpha=on&unique=on&format=html&rnd=new
```

### Verificar Deployment

Una vez desplegado, verifica que todo funcione:

```bash
# Health check
curl https://tu-dominio.com/api/health

# Debe retornar:
{
  "success": true,
  "message": "API funcionando correctamente",
  "data": {
    "status": "ok",
    "database": "connected"
  }
}
```

### Monitoreo y Logs

**Railway:**
```bash
railway logs
```

**Render:**
- Ver logs en el dashboard: Logs tab

**Heroku:**
```bash
heroku logs --tail
```

**VPS:**
```bash
# Apache logs
sudo tail -f /var/log/apache2/virtual-balance-error.log

# Application logs
tail -f logs/app.log
```

### Configuración de Dominio Personalizado

**Railway:**
1. Settings → Domains → Add Custom Domain
2. Agregar registro CNAME en tu DNS provider

**Render:**
1. Settings → Custom Domains → Add Custom Domain
2. Configurar DNS según instrucciones

**Heroku:**
```bash
heroku domains:add www.tu-dominio.com
# Configurar DNS con el valor proporcionado
```

## �🐛 Troubleshooting

### Error de conexión a BD
- Verificar credenciales en `.env`
- Verificar que MySQL esté corriendo
- Verificar que la base de datos exista

### Error "Class not found"
```bash
composer dump-autoload
```

### Permisos de logs
```bash
chmod -R 775 logs/
```

## 🎯 Siguiente Paso: Frontend

Una vez el backend esté funcionando, puedes crear el frontend con:
- HTML/CSS/JavaScript vanilla
- Vue.js 3
- React
- Alpine.js

## 📞 Soporte

Para dudas o problemas:
- Email: adslozanov@gmail.com
- GitHub Issues: https://github.com/LozanoAndersonTheStain/virtual-balance-backend/issues
