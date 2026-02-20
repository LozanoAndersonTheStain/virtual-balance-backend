# 🚀 Guía Completa de Deploy

## Stack de Deployment
- **Backend PHP**: Render.com
- **Base de Datos**: Clever Cloud MySQL

---

## 📝 Parte 1: Configurar Clever Cloud (MySQL)

### 1️⃣ Crear Cuenta en Clever Cloud

1. Ve a [clever-cloud.com](https://www.clever-cloud.com)
2. Click en **"Login"** → **"Sign up"**
3. Regístrate con GitHub (recomendado) o email

### 2️⃣ Crear Base de Datos MySQL

**En Clever Cloud Dashboard:**

1. Click en **"Create..."** → **"an add-on"**
2. Selecciona **"MySQL"**
3. Nombre: `virtual-balance-db`
4. Plan: Selecciona el plan DEV (256 MB RAM)
5. Region: Selecciona la más cercana (ej: Paris, Montreal)
6. Click **"Create"**

**El addon se creará automáticamente** ✅

### 3️⃣ Obtener Credenciales de Conexión

**En Clever Cloud Dashboard:**

1. Click en tu addon MySQL `virtual-balance-db`
2. Ve a la pestaña **"Add-on Dashboard"** o **"Connection URI"**
3. Verás algo como:

```
mysql://user:password@host.clever-cloud.com:3306/database_name
```

**Desglosar credenciales:**
```
Host: bmxxxxxxxx-mysql.services.clever-cloud.com
Port: 3306
Database: bmxxxxxxxx
Username: uxxxxxxxx
Password: xxxxxxxxxxxx
```

**⚠️ IMPORTANTE:** Guarda estas credenciales, las necesitarás para Render.

### 4️⃣ Ejecutar Migraciones

**Opción A: Con phpMyAdmin (Web Interface)**

1. En el addon → Click en **"phpMyAdmin"** (botón verde)
2. Hará login automático
3. Click en la base de datos de la izquierda
4. Ve a la pestaña **"SQL"**
5. Copia y pega el contenido de `database/migrations/init_database.sql`
6. Click **"Go"** o **"Ejecutar"**

**Opción B: Con MySQL Workbench**
1. Abre MySQL Workbench
2. Nueva conexión con las credenciales de Clever Cloud
3. File → Open SQL Script → Selecciona `database/migrations/init_database.sql`
4. Click Execute (⚡)

**Opción C: Desde Terminal Local**
```bash
# Desde la carpeta del proyecto
mysql -h <CLEVER_HOST> -u <USER> -p<PASSWORD> <DATABASE> < database/migrations/init_database.sql
```

---

## 📋 Parte 2: Configurar Render.com (Backend PHP)

### 1️⃣ Crear Cuenta en Render

1. Ve a [render.com](https://render.com)
2. Click en **"Get Started"**
3. Regístrate con GitHub

### 2️⃣ Conectar Repositorio

1. En Render Dashboard → **"New +"** → **"Blueprint"**
2. Conecta tu cuenta de GitHub
3. Busca el repositorio: `LozanoAndersonTheStain/virtual-balance-backend`
4. Click **"Connect"**

### 3️⃣ Configurar Variables de Entorno

**Render detectará el archivo `render.yaml` automáticamente.**

**IMPORTANTE:** Antes de hacer deploy, edita las variables de entorno en Render Dashboard:

1. En el Blueprint → **"Environment Variables"**
2. Configura con los datos de Clever Cloud:

```env
# Base de Datos (Clever Cloud MySQL)
DB_HOST=bmxxxxxxxx-mysql.services.clever-cloud.com
DB_NAME=bmxxxxxxxx
DB_USER=uxxxxxxxx
DB_PASS=xxxxxxxxxxxx
DB_PORT=3306

# Application (auto-generadas)
API_KEY=<render-genera-automático>
APP_ENV=production
APP_DEBUG=false
PAYMENT_SUCCESS_RATE=1.0
```

**💡 Tip:** Copia y pega las credenciales exactas de Clever Cloud.

### 4️⃣ Deploy

1. Click **"Apply"** en el Blueprint
2. Render comenzará a:
   - ✅ Clonar el repositorio
   - ✅ Build de la imagen Docker (~3-5 min)
   - ✅ Deploy del servicio
3. Espera a que el estado sea **"Live"** 🟢

### 5️⃣ Verificar Deploy

```bash
# Obtén la URL de Render (algo como):
# https://virtual-balance-api.onrender.com

# Prueba el health check
curl https://virtual-balance-api.onrender.com/api/health \
  -H "X-API-Key: tu_api_key_de_render"
```

**Respuesta esperada:**
```json
{
  "status": "success",
  "message": "API is running",
  "timestamp": "2026-02-20T10:30:00",
  "database": "connected"
}
```

---

## 📋 Parte 3: Ejecutar Migraciones (Si no lo hiciste en Parte 1)

### Opción A: Desde Render Shell

1. En Render Dashboard → Tu servicio → **"Shell"**
2. Ejecuta:
```bash
# Instalar mysql client
apt-get update && apt-get install -y mysql-client

# Conectar a Clever Cloud MySQL
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME

# Dentro de MySQL, copiar y pegar el contenido de:
# database/migrations/init_database.sql
```

### Opción B: Desde Local

```bash
# Conectarte a Clever Cloud desde tu PC
mysql -h <CLEVER_HOST> -u <USER> -p<PASSWORD> <DATABASE> < database/migrations/init_database.sql
```

### Opción C: Usar phpMyAdmin de Clever Cloud

1. En Clever Cloud Dashboard → Tu addon MySQL
2. Click en el botón **"phpMyAdmin"**
3. Pestaña **"SQL"**
4. Pega el contenido de `database/migrations/init_database.sql`
5. Click **"Go"**

---

## ⚠️ Consideraciones Importantes

### Render.com:
- ⚠️ El servicio **se duerme después de 15 min de inactividad** (plan básico)
- ⏱️ Tarda ~30 segundos en despertar al recibir el primer request
- ✅ HTTPS automático incluido
- ✅ Deploy automático con GitHub

### Clever Cloud MySQL (Plan DEV):
- 256 MB de RAM
- Approx. 100 MB de storage
- 5 conexiones simultáneas
- phpMyAdmin incluido
- ⚠️ Backups: Configurar manualmente según necesidades

**Ideal para:**
- 📚 Portafolio y demos
- 🧪 Proyectos de prueba
- 🎓 Proyectos educativos
- 🚀 MVPs y prototipos

---

## 🔄 Deploy Automático con GitHub

Render hace deploy automático cada vez que haces push:

```bash
# Hacer cambios en tu código
git add .
git commit -m "feat: nueva funcionalidad"
git push origin main

# Render detectará el push y hará deploy automático
```

**Ver logs:**
1. Render Dashboard → Tu servicio → **"Logs"**
2. Logs en tiempo real durante el deploy

---

## 🆘 Troubleshooting

### El servicio se duerme (Plan Básico)

**Problema:** Primera request tarda 30 segundos
**Solución:** 
- Usar un servicio de "keep-alive" (hace ping cada 10 min)
- O esperar 30 seg en la primera llamada

### Error de conexión a Clever Cloud MySQL

**Verificar:**
```bash
# En Render Shell
echo $DB_HOST
echo $DB_USER
echo $DB_NAME

# Probar conexión
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "SHOW TABLES;"
```

**Si falla la conexión:**
- Verifica que las credenciales sean correctas
- Clever Cloud MySQL puede tardar 1-2 minutos en activarse después de crearlo
- Revisa la pestaña "Service dependencies" en tu addon

### Logs del servicio

```bash
# Ver logs en tiempo real desde Render Dashboard
# O por CLI:
render logs --tail
```

---

## 🎨 Dominio Personalizado (Opcional)

**Render proporciona un dominio:**
```
https://virtual-balance-api.onrender.com
```

**Para dominio custom:**
1. Render Dashboard → Settings → Custom Domains
2. Agregar tu dominio
3. Configurar DNS CNAME en tu proveedor

---

## 📊 Monitoreo

**En Render Dashboard:**
- 📈 Métricas de uso (CPU, RAM, requests)
- 📝 Logs en tiempo real
- 🔄 Historial de deploys
- ⚡ Health checks automáticos

**En Clever Cloud Dashboard:**
- 💾 Espacio usado
- 📊 Métricas de conexiones
- 📈 Query performance (en phpMyAdmin)
- 🔍 Logs de MySQL (pestaña Logs)

---

## ✅ Checklist de Deploy

- [ ] Cuenta en Clever Cloud creada
- [ ] Addon MySQL creado en Clever Cloud
- [ ] Credenciales de Clever Cloud guardadas
- [ ] Migraciones ejecutadas vía phpMyAdmin o MySQL
- [ ] Cuenta en Render.com creada
- [ ] Repositorio conectado con Render
- [ ] Variables de entorno configuradas en Render
- [ ] Blueprint aplicado y servicio deployed
- [ ] Health check OK (200)
- [ ] Endpoint de balance probado
- [ ] API Key configurada y funcionando

---

## 🚀 ¡Listo!

Tu API está deployada en:
```
https://tu-servicio.onrender.com/api
```

**Endpoints disponibles:**
- `GET /api/health` - Health check
- `POST /api/users/register` - Registrar usuario
- `GET /api/users/:documentId/balance` - Consultar saldo
- `POST /api/wallets/recharge` - Recargar wallet
- `POST /api/payments` - Realizar pago
- `POST /api/payments/:sessionId/confirm` - Confirmar pago

**Documentación completa:** Ver README.md

---

¿Problemas? Revisa los logs en Render Dashboard o contacta al soporte.
