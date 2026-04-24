<h1> Parcial-II-Programaci-n-IV </h1>
  
<h2>¿Cómo manejan la conexión a la BD y qué pasa si algunos de los datos son incorrectos?
Justifiquen la manera de validación de la conexión</h2>

<p>Usamos "XAMPP" para usar PHPMyAdmin para la BD y la conectamos con "PHP"..
Si los datos son incorrectos, se captura el error y se muestra un mensaje sin revelar información sensible.
Esto evita que el sistema falle o exponga datos.
</p>

<h2>¿Cuál es la diferencia entre $_GET y $_POST en PHP? ¿Cuándo es más apropiado usar
cada uno? Da un ejemplo real de tu proyecto</h2>


<p>$_GET: envía datos por la URL
$_POST: envía datos ocultos

Se usa POST en el login (para la seguridad)
Se usa GET en búsquedas de productos existentes</p>

<h2>Tu app va a usarse en una empresa de la zona oriental. ¿Qué riesgos de seguridad
identificas en una app web con BD que maneja datos de los usuarios? ¿Cómo los
mitigarían?</h2>

<p>Tenemos como riesgos: Inyección SQL, Robo de datos y Accesos no autorizados.
  Consultas preparadas
Validación de datos
Contraseñas encriptadas
Control de sesiones
</p>

<h2>En el mismo readme realizar un diccionario de datos con las tablas con el siguiente
formato:</h2>
