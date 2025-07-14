# Proyecto DeportProyect - Backend

API RESTful para la gestión de usuarios, equipos, amistades e invitaciones en campeonatos deportivos, construido en PHP con PDO, JWT para autenticación y un MVC ligero.

---

## 🏗️ Estructura de carpetas

```
api-DeportProyect/
├── api/                # Front controller y rutas (routes.php)
├── src/
│   ├── Controllers/    # Controladores de recursos
│   ├── Models/         # Modelos de datos
│   ├── Config/         # Conexión BD y configuración
│   ├── Helpers/        # Funciones auxiliares (env, auth middleware)
│   └── Middlewares/    # Verificación JWT (auth)
├── vendor/             # Dependencias Composer
├── tests/              # Pruebas PHPUnit
└── README.md           # Documentación del proyecto
```

---

## ⚙️ Requisitos

- PHP 8.0+
- Composer
- MySQL / MariaDB
- Extensión PDO

---

## 🚀 Instalación

1. Clona el repositorio:

   ```bash
   git clone <repositorio> api-DeportProyect
   cd api-DeportProyect
   ```

2. Instala dependencias con Composer:

   ```bash
   composer install
   ```

3. Copia y configura el archivo de entorno:

   ```bash
   cp .env.example .env
   ```

   Ajusta las variables:

   ```ini
   DB_HOST=127.0.0.1
   DB_NAME=campeonatos_bd
   DB_USER=root
   DB_PASS=
   JWT_SECRET=TuClaveSecreta256Bits
   JWT_EXPIRATION=86400    # en segundos
   ```

4. Crea la base de datos y ejecuta el script SQL (`database.sql`) en tu servidor MySQL.

5. Configura tu virtual host o accede vía: `http://localhost/api-DeportProyect/api`.

---

## 📦 Variables de entorno

| Variable         | Descripción                     | Ejemplo              |
| ---------------- | ------------------------------- | -------------------- |
| `DB_HOST`        | Host de la base de datos        | `127.0.0.1`          |
| `DB_NAME`        | Nombre de la base de datos      | `campeonatos_bd`     |
| `DB_USER`        | Usuario de la base de datos     | `root`               |
| `DB_PASS`        | Contraseña de la base de datos  |                      |
| `JWT_SECRET`     | Clave para firmar tokens JWT    | `Clave-Secreta-2025` |
| `JWT_EXPIRATION` | Tiempo de expiración (segundos) | `86400` (24h)        |

---

## 💾 Base de datos

Incluye tablas principales:

- **usuario**: usuarios del sistema.
- **equipo**: equipos deportivos, con `propietario_id`.
- **solicitudamistad**: peticiones de amistad.
- **amistad**: relaciones de amistad activas.
- **invitacionequipo**: invitaciones a unirse a equipos.
- **miembrosequipo**: miembros activos de cada equipo.

Revisa `database.sql` para la definición completa.

---

## 🔐 Autenticación JWT

1. **Registro**: `POST /register` → crea nuevo usuario.
2. **Login**: `POST /login` → recibe JWT.
3. **Me**: `GET /me` → valida token y devuelve datos completos del usuario.

En todas las rutas protegidas debes enviar cabecera:

```
Authorization: Bearer <TOKEN>
```

---

## 📑 Endpoints principales

A continuación, algunos ejemplos con `curl`. Sustituye `<TOKEN>` y URLs según corresponda.

### Usuarios

```bash
# Listar todos
curl -X GET /usuarios -H "Authorization: Bearer <TOKEN>"

# Obtener uno
curl -X GET /usuarios/7 -H "Authorization: Bearer <TOKEN>"

# Obtener por email
curl -X POST /usuarios/email -H "Authorization: Bearer <TOKEN>" -H "Content-Type: application/json" -d '{"email":"john.doe@ejemplo.com"}'

# Actualizar
curl -X PUT /usuarios/7 -H "Authorization: Bearer <TOKEN>"   -H "Content-Type: application/json" -d '{"ciudad":"Medellín"}'

# Eliminar
curl -X DELETE /usuarios/7 -H "Authorization: Bearer <TOKEN>"
```

### Equipos

```bash
# Crear
curl -X POST /equipos -H "Authorization: Bearer <TOKEN>"   -H "Content-Type: application/json" -d '{"nombre":"Team A","anio_fundacion":2020}'

# Listar
curl -X GET /equipos -H "Authorization: Bearer <TOKEN>"

#Buscar por Nombre
curl -X POST /equipos/buscar -H "Authorization: Bearer <TOKEN>" -H "Content-Type: application/json" -d '{"nombre":"Example"}'

# Detalle
curl -X GET /equipos/3 -H "Authorization: Bearer <TOKEN>"

# Editar
curl -X PUT /equipos/3 -H "Authorization: Bearer <TOKEN>"   -H "Content-Type: application/json" -d '{"descripcion":"Club local"}'

# Borrar
curl -X DELETE /equipos/3 -H "Authorization: Bearer <TOKEN>"
```

### Amistades y solicitudes

```bash
# Enviar solicitud
curl -X POST /solicitudes-amistad -H "Authorization: Bearer <TOKEN>"   -H "Content-Type: application/json" -d '{"para_usuario_id":10}'

# Ver solicitudes
curl -X GET /solicitudes-amistad -H "Authorization: Bearer <TOKEN>"

# Aceptar solicitud
curl -X PUT /solicitudes-amistad/2 -H "Authorization: Bearer <TOKEN>"   -H "Content-Type: application/json" -d '{"estado":"aceptado"}'

# Listar amigos
curl -X GET "/solicitudes-amistad?amigos=1" -H "Authorization: Bearer <TOKEN>"
```

### Invitaciones a equipos

```bash
# Enviar invitación
curl -X POST /invitaciones-equipo -H "Authorization: Bearer <TOKEN>"   -H "Content-Type: application/json" -d '{"para_usuario_id":9,"equipo_id":5,"mensaje":"Únete!"}'

# Listar invitaciones
curl -X GET /invitaciones-equipo -H "Authorization: Bearer <TOKEN>"

# Aceptar invitación
curl -X PUT /invitaciones-equipo/3 -H "Authorization: Bearer <TOKEN>"   -H "Content-Type: application/json" -d '{"estado":"aceptado"}'
```

### Miembros de equipo

```bash
# Agregar miembro
curl -X POST /miembros-equipo -H "Authorization: Bearer <TOKEN>"   -H "Content-Type: application/json" -d '{"usuario_id":10,"equipo_id":5}'

# Listar
curl -X GET /miembros-equipo/5 -H "Authorization: Bearer <TOKEN>"

# Remover miembro
curl -X DELETE /miembros-equipo -H "Authorization: Bearer <TOKEN>"   -H "Content-Type: application/json" -d '{"usuario_id":10,"equipo_id":5}'
```

---

## 🧪 Pruebas

Ejecuta PHPUnit:

```bash
./vendor/bin/phpunit --colors=always
```

---

## 📝 Licencia

MIT © EliansRO