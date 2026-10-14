# EDA311_HITO3_QuispeFernando

Aplicacion web CRUD con **PHP + MySQL** para gestionar `clientes`, `pedidos` y `productos`, con una
**lista doblemente enlazada** implementada desde cero y ordenamiento **Bubble Sort** aplicado directamente sobre los nodos.

## 1. Requisitos de software
- PHP 8.0 o superior con la extension `mysqli` habilitada
- MySQL 8.x (base `tienda_quispe_fernando` facilitada por el docente) y usuario con permisos SELECT/INSERT/UPDATE/DELETE
- Servidor web: Apache (XAMPP en Windows) **o** el servidor integrado de PHP
- Git y un navegador web

## 2. Descargar el proyecto
```bash
git clone https://github.com/TU_USUARIO/EDA311_HITO3_QuispeFernando.git
cd EDA311_HITO3_QuispeFernando
```

## 3. Configurar el entorno PHP / servidor web
**Opcion A - XAMPP (Windows):** instala XAMPP, inicia el modulo *Apache*, copia la carpeta del proyecto en `C:\xampp\htdocs\` y verifica que en `php.ini` este activa la linea `extension=mysqli`.

**Opcion B - Servidor integrado de PHP:** desde la carpeta del proyecto ejecuta `php -S localhost:8000`.

## 4. Configurar la conexion a MySQL
1. Copia `config.example.php` como `config.php`.
2. Edita `config.php` con los datos entregados por el docente (`host`, `puerto`, `usuario`, `password`, `base`).
3. `config.php` esta en `.gitignore`: **nunca se sube al repositorio**.

La base ya trae las tablas. Estructura explorada:
- `clientes(id_cliente PK AI, nombre, email UNIQUE, edad)`
- `productos(id_producto PK AI, nombre varchar(100), categoria varchar(50), precio decimal(10,2), stock int)`
- `pedidos(id_pedido PK AI, id_cliente FK -> clientes.id_cliente, fecha date, total decimal(10,2))`

**Regla de borrado (consultada en `information_schema.REFERENTIAL_CONSTRAINTS`):** la clave foranea `pedidos.id_cliente -> clientes.id_cliente` usa `NO ACTION`. Por eso MySQL impide eliminar un cliente que tenga pedidos; la aplicacion captura el error 1451 y muestra un mensaje. Para eliminar al cliente primero se eliminan sus pedidos.

Opcional: si las tablas estan vacias y el docente lo permite, ejecuta `sql/datos_ejemplo.sql` en MySQL Workbench.

## 5. Ejecutar
- XAMPP: abre `http://localhost/EDA311_HITO3_QuispeFernando/`
- Servidor integrado: abre `http://localhost:8000/`

## 6. Estructura del proyecto
```
index.php               Menu principal
gestionar.php           CRUD + vistas de lista y Bubble Sort (?tabla=clientes|pedidos|productos)
config.example.php      Plantilla de configuracion (sin credenciales)
includes/conexion.php   Conexion mysqli
includes/tablas.php     Definicion de campos y criterio de orden por tabla
includes/crud.php       Registrar, modificar, eliminar, validar (consultas preparadas)
includes/cabecera.php, pie.php   Plantilla HTML
src/Nodo.php            Nodo de la lista
src/ListaDoblementeEnlazada.php   Lista + Bubble Sort
src/CargadorLista.php   Carga de registros MySQL -> lista
css/estilos.css         Estilos
sql/datos_ejemplo.sql   Datos de prueba (opcional)
```

## 7. Lista doblemente enlazada
Cada `Nodo` guarda un registro (`datos`) y las referencias `anterior` y `siguiente`. `ListaDoblementeEnlazada` mantiene `cabeza`, `cola` y `tamano`, e incluye `insertarAlFinal()`, `recorrer()` (cabeza a cola) y `recorrerInverso()` (cola a cabeza). `cargarListaDesdeMySQL()` ejecuta la consulta y va insertando cada fila como un nodo.

## 8. Bubble Sort
`ordenarBurbuja($campo, $numerico, $ascendente)` recorre la lista comparando cada nodo con su siguiente; si estan desordenados intercambia sus datos. Tras cada pasada, el ultimo elemento comparado queda en su lugar (`limite`), y el proceso termina cuando una pasada no hace intercambios. **No convierte la lista a arreglo.**

Criterios de orden (comparan el campo indicado):
| Tabla | Criterio | Justificacion |
|---|---|---|
| clientes | nombre (A-Z, sin distinguir mayusculas) | Facilita buscar clientes alfabeticamente |
| productos | precio (menor a mayor) | Permite comparar costos |
| pedidos | total (menor a mayor) | Permite ver pedidos por monto |

La pantalla muestra tambien pasadas, comparaciones e intercambios, y permite elegir orden ascendente o descendente.
