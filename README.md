# Cafetería Express — versión sencilla

Proyecto académico PHP 8.2 + MySQL. **Solo cinco secciones CRUD**: categorías, productos, movimientos de inventario, ventas y detalle de ventas. No incluye reportes, dashboard adicional, API ni frameworks. El código PHP contiene comentarios explicativos en las partes importantes.

## Instalar en XAMPP

1. Descomprime el ZIP en `C:\xampp\htdocs\cafeteria_express`.
2. Inicia Apache y MySQL desde XAMPP.
3. Crea una base de datos llamada `cafeteria_express` (o usa la base remota de Aiven ya existente).
4. Importa **en este orden** `database/estructura.sql` y `database/procedimientos.sql` usando un cliente que admita `DELIMITER` (por ejemplo MySQL CLI o phpMyAdmin). Si la BD ya tiene datos, haz respaldo antes de importar.
5. Configura la conexión en variables `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` y `DB_SSL_CA` (si usas Aiven). El proyecto también admite el archivo privado local `C:/xampp/private/aiven-config.php` si ya lo tenías configurado.
6. Entra en `http://localhost/cafeteria_express/`. Si no existe contraseña local, se abrirá `configurar.php` para que la crees una sola vez. Después ingresa desde `login.php`.

**No hay contraseña predeterminada.** La eliges tú. El archivo `config/admin-auth.php` se genera automáticamente y no debe subirse a GitHub.

## Publicar en Render

1. Sube los archivos del proyecto a tu repositorio GitHub **sin contraseñas ni certificados privados**.
2. Usa el Dockerfile de la raíz para crear el servicio web de Render.
3. En **Environment** configura `APP_ENV=production`, `APP_ADMIN_PASSWORD` (una contraseña privada), `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DB_SSL_CA` (ruta a un certificado CA disponible en el contenedor). No basta con escribir una ruta: el certificado debe estar presente y ser legible. En Render puedes usar un archivo secreto montado en esa ruta.
4. Importa previamente la estructura y los procedimientos en Aiven con una cuenta con permisos suficientes. La app no crea tablas automáticamente.
5. Comprueba los cinco CRUD en el servicio publicado.

## Archivos principales

- `login.php`: formulario de acceso.
- `configurar.php`: crea la contraseña local inicial desde localhost.
- `salir.php`: cierra sesión.
- `index.php`: menú de las cinco secciones.
- `includes/comun.php`: navegación, sesión y protección CSRF.
- `includes/crud.php`: código común para crear, consultar, editar y eliminar.
- `includes/esquemas.php`: campos de cada formulario.
- `config/conexion.php`: conexión PDO a MySQL.
- `modulos/*.php`: cinco pantallas CRUD.
- `database/estructura.sql`: cinco tablas y relaciones.
- `database/procedimientos.sql`: 25 procedimientos y triggers de inventario/ventas.
- `database/permisos_ejemplo.sql`: ejemplo de usuario limitado a lectura y ejecución de procedimientos (adaptar al servidor).
- `assets/`: diseño responsive y menú móvil.
- `Dockerfile`: publicación con Apache en Render.

## Relaciones (modelo ER y relacional)

`categorias (1) → (N) productos (1) → (N) movimientos_inventario`

`ventas (1) → (N) detalle_ventas (N) ← (1) productos`

PK: `id_categoria`, `id_producto`, `id_movimiento`, `id_venta`, `id_detalle`. FK: `productos.id_categoria`, `movimientos_inventario.id_producto`, `detalle_ventas.id_venta`, `detalle_ventas.id_producto`.

## Prueba funcional mínima

1. Crear y editar categoría; comprobar listado y eliminación de categoría sin productos.
2. Crear producto y actualizar precio; comprobar que una categoría con productos no se elimina por integridad referencial.
3. Crear movimiento ENTRADA, editarlo, borrarlo; comprobar existencias antes/después.
4. Crear venta COMPLETADA; crear detalle con cantidad y precio; comprobar subtotal, total y stock.
5. Cancelar la venta y comprobar que se devuelve stock; reactivarla y comprobar descuento.
6. Editar y eliminar detalles; eliminar venta; verificar que se conservan totales y existencias coherentes.
7. Repetir pruebas en Render con Aiven.

**Límites de verificación:** pasar `php -l` y pruebas HTTP no demuestra que los procedimientos se ejecuten correctamente en Aiven. Deben probarse contra tu base de datos real. El código usa MySQL y no puede probarse con SQLite sin cambiar la implementación.

## Reparar error 1305: falta sp_categorias_listar

Desde CMD en Windows, después de hacer una copia de seguridad de la base de datos:

```cmd
cd /d C:\xampp\htdocs\cafeteria_express
C:\xampp\php\php.exe database\instalar.php
```

Este instalador usa exactamente `config/conexion.php`, igual que la aplicación: si
estás conectado a Aiven, instala allí; si estás conectado a MySQL local, instala
localmente. Crea tablas solo si faltan y reinstala los 25 procedimientos y 9
triggers sin vaciar tablas. Necesita privilegios CREATE ROUTINE, ALTER ROUTINE y
TRIGGER. No lo ejecutes mientras haya usuarios registrando ventas. Después
comprueba que aparezca `OK: sp_categorias_listar funciona`.
