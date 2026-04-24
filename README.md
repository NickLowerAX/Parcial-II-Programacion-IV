<h1> Parcial-II-Programaci-n-IV </h1>

<h2>Integrantes</h2>
<p>Noé Isaí Hernández Rivas / SMSS010623 -
Daniela Kristel Marquez Chavez / SMSS089923 -
Eduardo Antonio Fuentes Melara (SMSS093423)</p>

<h2>¿Cómo manejan la conexión a la BD y qué pasa si algunos de los datos son incorrectos?
Justifiquen la manera de validación de la conexión</h2>

<p>Usamos "XAMPP" para usar PHPMyAdmin para la BD y la conectamos con "PHP"..
Si los datos son incorrectos, se captura el error y se muestra un mensaje sin revelar información sensible.
Esto evita que el sistema falle o exponga datos.
</p>

<h2>¿Cuál es la diferencia entre $_GET y $_POST en PHP? ¿Cuándo es más apropiado usar
cada uno? Da un ejemplo real de tu proyecto</h2>

<p>$_GET: envía datos por la URL en cambio al usar $_POST: envíamos datos ocultos

Se usa POST en el login (para la seguridad)
Se usa GET en búsquedas de productos existentes</p>

<h2>Tu app va a usarse en una empresa de la zona oriental. ¿Qué riesgos de seguridad
identificas en una app web con BD que maneja datos de los usuarios? ¿Cómo los
mitigarían?</h2>

<h3>Riesgos encontrados:</h3>
  <p>Inyección SQL: ocurre cuando no se controlan los datos de entrada y pueden alterar consultas</p>
  <p>Robo de sesión: un atacante puede usar la sesión de otro usuario si no está protegida</p>
  <p>Exposición de errores: mostrar errores internos puede revelar información sensible del sistema</p>
  <p>Maneras para mitigar riesgos</p>
  
<p>separa datos de la consulta, evitando inyección SQL</p>
  <p>se puede usar "password_hash()" con el fin de proteger contraseñas al almacenarlas en forma encriptada</p>
  <p>controlando las sesiones ya que permite identificar y restringir el acceso a usuarios autenticados</p>
  <p>validacion de datos evitando entradas sospechosas o en si peligrosas</p>
  
<h3>En el mismo readme realizar un diccionario de datos con las tablas con el siguiente
formato:</h3>

<h2>Tabla: usuarios</h2>
<table border="1">
<tr><th>Columna</th><th>Tipo</th><th>Límite</th><th>¿Nulo?</th><th>Descripción</th></tr>
<tr><td>id</td><td>INT</td><td>-</td><td>No</td><td>ID del usuario</td></tr>
<tr><td>usuario</td><td>VARCHAR</td><td>50</td><td>No</td><td>Nombre de usuario</td></tr>
<tr><td>password</td><td>VARCHAR</td><td>255</td><td>No</td><td>Contraseña encriptada</td></tr>
<tr><td>creado_en</td><td>DATETIME</td><td>-</td><td>Sí</td><td>Fecha de creación</td></tr>
</table>

<h2>Tabla: productos</h2>
<table border="1">
<tr><th>Columna</th><th>Tipo</th><th>Límite</th><th>¿Nulo?</th><th>Descripción</th></tr>
<tr><td>id</td><td>INT</td><td>-</td><td>No</td><td>ID del producto</td></tr>
<tr><td>usuario_id</td><td>INT</td><td>-</td><td>No</td><td>Relación con usuario</td></tr>
<tr><td>nombre</td><td>VARCHAR</td><td>100</td><td>No</td><td>Nombre del producto</td></tr>
<tr><td>precio</td><td>DECIMAL</td><td>10,2</td><td>No</td><td>Precio</td></tr>
<tr><td>stock</td><td>INT</td><td>-</td><td>No</td><td>Cantidad disponible</td></tr>
<tr><td>stock_minimo</td><td>INT</td><td>-</td><td>No</td><td>Stock mínimo</td></tr>
</table>

<h2>Tabla: ventas</h2>
<table border="1">
<tr><th>Columna</th><th>Tipo</th><th>Límite</th><th>¿Nulo?</th><th>Descripción</th></tr>
<tr><td>id</td><td>INT</td><td>-</td><td>No</td><td>ID venta</td></tr>
<tr><td>usuario_id</td><td>INT</td><td>-</td><td>No</td><td>Usuario</td></tr>
<tr><td>total</td><td>DECIMAL</td><td>10,2</td><td>No</td><td>Total</td></tr>
<tr><td>fecha</td><td>DATETIME</td><td>-</td><td>Sí</td><td>Fecha</td></tr>
</table>

<h2>Tabla: detalle_ventas</h2>
<table border="1">
<tr><th>Columna</th><th>Tipo</th><th>Límite</th><th>¿Nulo?</th><th>Descripción</th></tr>
<tr><td>id</td><td>INT</td><td>-</td><td>No</td><td>ID detalle</td></tr>
<tr><td>venta_id</td><td>INT</td><td>-</td><td>No</td><td>ID venta</td></tr>
<tr><td>producto_id</td><td>INT</td><td>-</td><td>No</td><td>ID producto</td></tr>
<tr><td>cantidad</td><td>INT</td><td>-</td><td>No</td><td>Cantidad</td></tr>
<tr><td>precio_unitario</td><td>DECIMAL</td><td>10,2</td><td>No</td><td>Precio unitario</td></tr>
</table>
