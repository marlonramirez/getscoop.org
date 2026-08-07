<p>Scoop está diseñado para escalar desde despliegues atómicos —basados en la transferencia simple de archivos— hasta flujos de trabajo avanzados con tuberías de <b>Integración y Despliegue Continuo (CI/CD)</b>. La arquitectura del motor garantiza que, independientemente del método elegido, el sistema mantenga su integridad y alto rendimiento.</p>

<p><ul>
  <li><a href="#debug">Debug mode</a></li>
  <li><a href="#quality">Estándares de Calidad</a></li>
  <li><a href="#automation">Automatización con Hooks</a></li>
  <li><a href="#optimization">Optimización de Producción</a></li>
  <li><a href="#benchmarks">Benchmarks</a></li>
  <li><a href="#cicd">Integración Continua (GitHub Actions)</a></li>
</ul></p>

<h2>
    <a href="#debug">Debug mode</a>
    <span class="anchor" id="debug">...</span>
</h2>

<p>Scoop utiliza el Debug Mode para equilibrar la agilidad durante el desarrollo y el máximo rendimiento en producción. Este modo se activa automáticamente basándose en la directiva de PHP <code>display_errors</code>.</p>

<p>Cuando <code>DEBUG_MODE</code> es <b>true</b>, el motor activa el "escaneo en caliente" (Hot Scanning):</p>

<ul>
    <li><b>Ruteo Dinámico:</b> El <code>Router</code> invoca al escáner de rutas en cada petición para reflejar cambios inmediatos en <code>app/routes</code>.</li>
    <li><b>Autodescubrimiento de Tipos:</b> El <code>TypeMapper</code> escanea el <code>composer.json</code> y los directorios del proyecto para resolver implementaciones de interfaces al vuelo.</li>
    <li><b>Visibilidad Total:</b> El <code>Http\Error\Mapper</code> renderiza trazas completas de errores (Stack Trace), archivos y líneas afectadas.</li>
</ul>

<p>En entornos productivos, es obligatorio que <code>display_errors</code> esté desactivado. Mantener el Debug Mode activo en producción conlleva dos riesgos críticos:</p>

<ol>
    <li><b>Fuga de Información:</b> Las trazas de error pueden exponer credenciales, rutas de archivos y lógica interna del negocio.</li>
    <li><b>Degradación de Rendimiento:</b> El escaneo constante de archivos (I/O) y el análisis de tokens de PHP eliminan las optimizaciones de caché, aumentando significativamente el tiempo de respuesta.</li>
</ol>

<p>Al desactivar el Debug Mode, el sistema dejará de escanear el sistema de archivos y confiará exclusivamente en los mapas compilados. Asegúrese de haber ejecutado los comandos de <code>ice scan</code> antes de pasar a un entorno inmutable.</p>

<h2>
    <a href="#quality">Estándares de Calidad</a>
    <span class="anchor" id="quality">...</span>
</h2>

<p>Para asegurar que la aplicación cumpla con los estándares de la industria (como las normas PSR), Scoop integra soporte nativo para las herramientas líderes de análisis y pruebas del ecosistema PHP:</p>

<p><ul>
    <li><b>Pruebas (Unit & Integration):</b> Gracias al desacoplamiento del <b>Injector</b>, Scoop facilita el testeo unitario del Dominio (POPOs) y pruebas de integración mediante <code>PHPUnit</code>, permitiendo mockear adaptadores de infraestructura sin esfuerzo.</li>
    <li><b>Linter (Estilo):</b> Se utiliza <code>PHPCS</code> para garantizar que el código siga las normas de estilo definidas en <code>app/phpcs.xml</code>, manteniendo una base de código homogénea.</li>
    <li><b>Análisis Estático:</b> Para mitigar los riesgos de la flexibilidad del lenguaje, Scoop se apoya en <code>PHPStan</code>. Recomendamos niveles de análisis estrictos para validar la seguridad de tipos y la lógica de los grafos de inyección.</li>
</ul></p>

<h2>
    <a href="#automation">Automatización con Hooks</a>
    <span class="anchor" id="automation">...</span>
</h2>

<p>Para garantizar que ninguna "mala práctica" o error de sintaxis llegue al repositorio, Scoop recomienda el uso de <b>GrumPHP</b>. Esta herramienta actúa como un guardián en el entorno local, ejecutando la suite de calidad automáticamente antes de cada <i>commit</i>.</p>

<pre><code class="language-shell">composer require --dev phpro/grumphp-shim</code></pre>

<p>Configuración recomendada en <code>grumphp.yml</code>:</p>

<pre><code class="language-yaml">grumphp:
    process_timeout: null
    tasks:
        phpcs:
            standard: [app/phpcs.xml]
            whitelist_patterns:
                - /^src\/(.*)/
        phpstan:
            configuration: app/phpstan.neon
            ignore_patterns:
                - /^scoop\/(.*)/
        phpunit:
            config_file: app/phpunit.xml
</code></pre>

<h2>
    <a href="#optimization">Optimización de Producción</a>
    <span class="anchor" id="optimization">...</span>
</h2>

<p>A diferencia del entorno de desarrollo, donde Scoop prioriza la flexibilidad y el descubrimiento dinámico, en producción el motor debe operar en <b>Modo Inmutable</b>. El proceso de construcción (build) utiliza el CLI <code>ice</code> para retirar del <i>Hot Path</i> el descubrimiento de rutas, el escaneo de tipos y el análisis reflectivo de constructores:</p>

<pre><code class="language-shell">app/ice scan routes
app/ice scan source
app/ice preload json:package
app/ice preload json:composer
</code></pre>

<p>Este proceso transforma la jerarquía de archivos y las definiciones dinámicas en mapas PHP optimizados para <b>Opcache</b>. El mapa de rutas evita descubrir controladores durante la petición, mientras que los mapas de fuentes permiten resolver tipos y argumentos de constructor sin volver a inspeccionar cada firma. Estos comandos se encuentran en el <code>composer.json</code> mediante el comando <code>build</code>. Es imperativo que este proceso finalice con éxito antes del despliegue, ya que la versión de producción dependerá de los artefactos generados para su ejecución.</p>

<p class="doc-alert"><b>Caché de construcción:</b> Los mapas generados forman parte de los artefactos del despliegue y no deben editarse manualmente. Si se necesita reconstruirlos sin esperar cambios en las fuentes, ejecute los comandos de escaneo con el flag <code>-f</code>.</p>

<p>También tenemos el comando <code>app/ice dbup</code> que no puede ser ejecutado en compilación o creación de la imagen, si no que se debe ejecutar cuando se haya desplegado en el servidor para que logre conectar con la base de datos.</p>

<p class="doc-alert"><b>Pro-Tip de Despliegue:</b> Asegúrese siempre de ejecutar <code>composer install --optimize-autoloader --no-dev</code> en el servidor de destino para minimizar la latencia del cargador de clases de PHP.</p>

<h3>Estructura de archivos en despliegue</h3>

<p>Es vital no transferir los archivos de desarrollo para reducir la superficie de ataque y mejorar el rendimiento. Un despliegue "limpio" de Scoop debe contener únicamente los artefactos de ejecución.</p>

<pre><code class="language-shell">├─ app
|   ├─ config
|   |    ├─ lang
|   |    |    ├─ en.php
|   |    |    └─ es.php
|   |    ├─ db.php
|   |    └─ providers.php
|   ├─ storage
|   ├─ structs
|   ├─ views
|   ├─ config.php
|   └─ ice
├─ public
|   ├─ css
|   ├─ fonts
|   ├─ images
|   ├─ js
|   ├─ favicon.ico
|   ├─ humans.txt
|   └─ robots.txt
├─ scoop
├─ src
├─ vendor
├─ .htaccess
└─ index.php
</code></pre>

<p>Para profundizar en la organización de los archivos, consulte la sección de <a href="{{#view->route('doc', 'application')}}#structure">Estructura de directorios</a>.</p>

<p class="doc-alert"><b>Permisos de Escritura:</b> El proceso de construcción genera archivos dentro de <code>app/storage/cache</code>. Asegúrese de que el usuario del servidor web (ej. www-data) tenga permisos de lectura sobre estos archivos y permisos de escritura sobre la carpeta <code>storage</code> para logs y caché persistente.</p>

<h2>
    <a href="#benchmarks">Benchmarks</a>
    <span class="anchor" id="benchmarks">...</span>
</h2>

<p>Los siguientes resultados fueron obtenidos el 7 de agosto de 2026 sobre un entorno Docker Linux x86_64 ejecutado en WSL2. Todas las pruebas utilizaron <b>PHP 8.5.9</b>, <b>Opcache</b> habilitado y artefactos precompilados cuando el componente los soporta. Las cifras describen escenarios concretos y no constituyen una afirmación de superioridad general sobre otros frameworks.</p>

<p class="doc-alert"><b>Criterio de lectura:</b> En los microbenchmarks se reporta la mediana y el percentil 95 (p95), ambos en microsegundos. En HTTP se reportan peticiones por segundo y latencia media. Una cifra menor es mejor para latencia; una cifra mayor es mejor para throughput.</p>

<h3>Resolución de rutas</h3>

<p>Se resolvió una ruta dinámica ubicada al final de conjuntos de 100, 1.000 y 10.000 rutas. Scoop utilizó el mapa generado por <code>ice scan routes</code>; Symfony Routing 8.1.2 utilizó <code>CompiledUrlMatcher</code>; FastRoute 1.3.1 utilizó su dispatcher generado.</p>

<table>
    <thead><tr><th>Rutas</th><th>Motor</th><th>Mediana (µs)</th><th>p95 (µs)</th></tr></thead>
    <tbody>
        <tr><td>100</td><td>Scoop</td><td>3,000</td><td>4,401</td></tr>
        <tr><td>100</td><td>Symfony compilado</td><td>2,300</td><td>4,601</td></tr>
        <tr><td>100</td><td>FastRoute</td><td>5,501</td><td>8,501</td></tr>
        <tr><td>1.000</td><td>Scoop</td><td>3,100</td><td>3,200</td></tr>
        <tr><td>1.000</td><td>Symfony compilado</td><td>4,201</td><td>5,201</td></tr>
        <tr><td>1.000</td><td>FastRoute</td><td>55,309</td><td>110,819</td></tr>
        <tr><td>10.000</td><td>Scoop</td><td>2,100</td><td>3,500</td></tr>
        <tr><td>10.000</td><td>Symfony compilado</td><td>29,005</td><td>57,209</td></tr>
        <tr><td>10.000</td><td>FastRoute</td><td>3.698,192</td><td>5.919,195</td></tr>
    </tbody>
</table>

<p>En esta topología, el tiempo de Scoop se mantuvo estable al aumentar el número total de rutas. El resultado es consistente con su resolución <b>O(S)</b>, donde <code>S</code> representa el número de segmentos de la URL. El resultado de FastRoute corresponde al patrón sintético utilizado y no debe extrapolarse a todas las aplicaciones basadas en Slim.</p>

<h3>Pipeline HTTP en producción</h3>

<p>Se compararon aplicaciones completas que entregan el mismo payload JSON mediante <b>Nginx 1.29.8</b> y <b>PHP-FPM 8.5.9</b>. Las cuatro compartieron la misma imagen PHP, configuración de Opcache y cantidad de workers. Scoop utilizó la traducción Nginx de las reglas de su <code>.htaccess</code>. Se ejecutaron dos corridas independientes de 5.000 solicitudes por aplicación y nivel de concurrencia; la tabla contiene la corrida caliente final, sin peticiones fallidas.</p>

<table>
    <thead><tr><th>Aplicación</th><th>Concurrencia</th><th>Req/s</th><th>Latencia media (ms)</th><th>Fallidas</th></tr></thead>
    <tbody>
        <tr><td>Scoop 0.8.3</td><td>1</td><td>317,16</td><td>3,153</td><td>0</td></tr>
        <tr><td>Slim 4.15.2</td><td>1</td><td>408,30</td><td>2,449</td><td>0</td></tr>
        <tr><td>Symfony 8.1.2</td><td>1</td><td>280,37</td><td>3,567</td><td>0</td></tr>
        <tr><td>Laravel 13.24.0</td><td>1</td><td>127,88</td><td>7,820</td><td>0</td></tr>
        <tr><td>Scoop 0.8.3</td><td>16</td><td>1.150,73</td><td>13,904</td><td>0</td></tr>
        <tr><td>Slim 4.15.2</td><td>16</td><td>1.393,68</td><td>11,480</td><td>0</td></tr>
        <tr><td>Symfony 8.1.2</td><td>16</td><td>1.000,22</td><td>15,996</td><td>0</td></tr>
        <tr><td>Laravel 13.24.0</td><td>16</td><td>368,28</td><td>43,445</td><td>0</td></tr>
    </tbody>
</table>

<p>Scoop quedó entre Slim y Symfony en el escenario concurrente: procesó un 17 % menos solicitudes por segundo que Slim, un 15 % más que Symfony y más de tres veces el throughput de Laravel. Esta prueba mide el pipeline HTTP mínimo de cada aplicación; agregar middlewares, acceso a datos o serialización compleja puede cambiar el orden relativo.</p>

<h3>Persistencia</h3>

<p>Las pruebas se ejecutaron contra PostgreSQL 17 con 10.000 registros, dentro de la misma red Docker. Se compararon PDO, Scoop, Doctrine DBAL 4.4.4 e Illuminate Database 13.24.0. Las consultas individuales utilizaron parámetros; las inserciones bulk escribieron 100 filas en una sentencia y revirtieron la transacción.</p>

<table>
    <thead><tr><th>Operación</th><th>Implementación</th><th>Mediana</th><th>p95</th></tr></thead>
    <tbody>
        <tr><td>Lectura de una fila</td><td>PDO preparado</td><td>354,156 µs</td><td>1.425,429 µs</td></tr>
        <tr><td>Lectura de una fila</td><td>Scoop Connection</td><td>359,858 µs</td><td>1.300,608 µs</td></tr>
        <tr><td>Lectura de una fila</td><td>Doctrine DBAL</td><td>484,482 µs</td><td>1.921,023 µs</td></tr>
        <tr><td>Lectura de una fila</td><td>Scoop Builder</td><td>1.286,917 µs</td><td>3.336,962 µs</td></tr>
        <tr><td>Lectura de una fila</td><td>Eloquent Query Builder</td><td>1.601,970 µs</td><td>3.729,327 µs</td></tr>
        <tr><td>INSERT bulk de 100 filas</td><td>Doctrine DBAL</td><td>4,495 ms</td><td>6,319 ms</td></tr>
        <tr><td>INSERT bulk de 100 filas</td><td>PDO</td><td>4,600 ms</td><td>6,486 ms</td></tr>
        <tr><td>INSERT bulk de 100 filas</td><td>Scoop Builder</td><td>5,099 ms</td><td>6,251 ms</td></tr>
        <tr><td>INSERT bulk de 100 filas</td><td>Eloquent Query Builder</td><td>5,960 ms</td><td>8,967 ms</td></tr>
    </tbody>
</table>

<p><code>Scoop\Persistence\Connection</code> agregó aproximadamente un 1,6 % sobre la mediana de PDO en la lectura individual. En escritura masiva, Scoop quedó aproximadamente un 11 % por encima de PDO, un 13 % por encima de DBAL y un 14 % por debajo de Eloquent. La hidratación de una entidad nueva mediante el <code>Hydrator</code> de Scoop obtuvo una mediana de <b>11,402 µs</b> y un p95 de <b>17,802 µs</b>.</p>

<h3>EPM: ciclo completo del ORM</h3>

<p>La tercera batería evaluó EPM sobre PostgreSQL 17 con 1.000 autores y 5.000 libros. A diferencia de la medición aislada del <code>Hydrator</code>, estas cifras incluyen la consulta SQL, creación del grafo de objetos, mapa de identidad y seguimiento de cambios. Cada iteración limpió el estado del Entity Manager sin reconstruir el contenedor. Las escrituras se ejecutaron dentro de una transacción y finalizaron con rollback.</p>

<table>
    <thead><tr><th>Operación EPM</th><th>Iteraciones</th><th>Mediana</th><th>p95</th></tr></thead>
    <tbody>
        <tr><td>Cargar una entidad por id</td><td>500</td><td>947,275 µs</td><td>1.513,018 µs</td></tr>
        <tr><td>Consultar e hidratar 10 entidades</td><td>100</td><td>1.359,307 µs</td><td>2.107,965 µs</td></tr>
        <tr><td>Consultar e hidratar 100 entidades</td><td>100</td><td>2.466,994 µs</td><td>4.216,030 µs</td></tr>
        <tr><td>Consultar e hidratar 1.000 entidades</td><td>100</td><td>12.054,946 µs</td><td>18.668,665 µs</td></tr>
        <tr><td>Agregado: 100 autores y 500 libros</td><td>100</td><td>9.518,547 µs</td><td>13.182,034 µs</td></tr>
        <tr><td>Insertar, flush y rollback</td><td>300</td><td>1.855,782 µs</td><td>2.978,991 µs</td></tr>
        <tr><td>Detectar cambio, actualizar y rollback</td><td>300</td><td>1.461,242 µs</td><td>2.163,712 µs</td></tr>
        <tr><td>Flush sin cambios</td><td>500</td><td>780,676 µs</td><td>1.195,917 µs</td></tr>
        <tr><td>Eliminar, flush y rollback</td><td>300</td><td>2.364,131 µs</td><td>3.328,525 µs</td></tr>
    </tbody>
</table>

<p>La hidratación escaló de 1,36 ms para 10 entidades a 12,05 ms para 1.000. El agregado de 600 objetos se resolvió en 9,52 ms de mediana mediante una consulta con join explícito. El <code>flush()</code> sin modificaciones fue el escenario más económico, coherente con el seguimiento de cambios de EPM.</p>

<h4>Endpoint EPM con Nginx y PHP-FPM</h4>

<p>También se midió un endpoint JSON que inicializa Scoop, consulta EPM y devuelve la cantidad de entidades. Se utilizaron Nginx 1.29.8, PHP-FPM 8.5.9, Opcache y 500 solicitudes calientes por escenario. Todos los casos terminaron sin errores HTTP.</p>

<table>
    <thead><tr><th>Entidades</th><th>Concurrencia</th><th>Req/s</th><th>Media</th><th>p50</th><th>p95</th><th>p99</th></tr></thead>
    <tbody>
        <tr><td>1</td><td>1</td><td>37,42</td><td>26,726 ms</td><td>26 ms</td><td>30 ms</td><td>38 ms</td></tr>
        <tr><td>1</td><td>16</td><td>129,85</td><td>123,215 ms</td><td>115 ms</td><td>186 ms</td><td>214 ms</td></tr>
        <tr><td>100</td><td>1</td><td>35,25</td><td>28,368 ms</td><td>28 ms</td><td>31 ms</td><td>39 ms</td></tr>
        <tr><td>100</td><td>16</td><td>123,14</td><td>129,932 ms</td><td>119 ms</td><td>203 ms</td><td>229 ms</td></tr>
        <tr><td>1.000</td><td>1</td><td>24,82</td><td>40,291 ms</td><td>39 ms</td><td>46 ms</td><td>62 ms</td></tr>
        <tr><td>1.000</td><td>16</td><td>78,75</td><td>203,163 ms</td><td>195 ms</td><td>255 ms</td><td>335 ms</td></tr>
    </tbody>
</table>

<p class="doc-alert"><b>Memoria:</b> PHP reportó un pico de 4 MiB para los tres volúmenes del endpoint. La cifra corresponde a bloques reservados por el asignador de PHP y, por su granularidad, no demuestra que hidratar 1, 100 o 1.000 entidades tenga exactamente el mismo consumo interno.</p>

<h3>Comparación entre ORM</h3>

<p>Se comparó <b>EPM de Scoop 0.8.3</b> con <b>Doctrine ORM 3.6.8</b> y <b>Eloquent 13.24.0</b>. Los tres utilizaron PHP 8.5.9, Opcache, PostgreSQL 17 y esquemas equivalentes con 1.000 autores y 5.000 libros. En los microbenchmarks la conexión se calentó antes de medir y el estado administrado por cada ORM se limpió entre iteraciones. Las escrituras terminaron con rollback.</p>

<table>
    <thead><tr><th>Operación</th><th>EPM</th><th>Doctrine ORM</th><th>Eloquent</th></tr></thead>
    <tbody>
        <tr><td>Carga por id</td><td>0,904 ms</td><td><b>0,587 ms</b></td><td>1,337 ms</td></tr>
        <tr><td>10 entidades</td><td>0,949 ms</td><td><b>0,792 ms</b></td><td>1,196 ms</td></tr>
        <tr><td>100 entidades</td><td><b>2,144 ms</b></td><td>2,520 ms</td><td>2,837 ms</td></tr>
        <tr><td>1.000 entidades</td><td>14,951 ms</td><td>16,456 ms</td><td><b>12,836 ms</b></td></tr>
        <tr><td>Relación 1:N: 100 autores y 500 libros</td><td><b>9,143 ms</b></td><td>14,140 ms</td><td>13,572 ms</td></tr>
        <tr><td>Insertar, flush/save y rollback</td><td>2,152 ms</td><td><b>1,548 ms</b></td><td>1,706 ms</td></tr>
        <tr><td>Actualizar y rollback</td><td><b>1,179 ms</b></td><td>2,403 ms</td><td>2,573 ms</td></tr>
        <tr><td>Eliminar y rollback</td><td>2,574 ms</td><td><b>1,679 ms</b></td><td>2,513 ms</td></tr>
    </tbody>
</table>

<p>Las cifras son medianas. Doctrine fue más rápido en cargas pequeñas y en dos de las tres escrituras; Eloquent obtuvo la menor mediana para 1.000 entidades; EPM lideró la carga de 100 entidades, el agregado 1:N y la actualización con detección de cambios. En la relación, EPM y Doctrine utilizaron joins explícitos, mientras que el eager loading de Eloquent resolvió la relación mediante dos consultas. Por ello, el resultado mide la estrategia pública recomendada de cada ORM y no solamente el coste de hidratación.</p>

<table>
    <thead><tr><th>ORM</th><th>Concurrencia</th><th>Req/s</th><th>Media</th><th>p95</th><th>p99</th></tr></thead>
    <tbody>
        <tr><td>EPM</td><td>1</td><td>37,71</td><td>26,522 ms</td><td>30 ms</td><td>43 ms</td></tr>
        <tr><td>Doctrine ORM</td><td>1</td><td>36,38</td><td>27,490 ms</td><td>30 ms</td><td>34 ms</td></tr>
        <tr><td>Eloquent</td><td>1</td><td>37,63</td><td>26,577 ms</td><td>29 ms</td><td>33 ms</td></tr>
        <tr><td>EPM</td><td>16</td><td>114,52</td><td>139,714 ms</td><td>221 ms</td><td>231 ms</td></tr>
        <tr><td>Doctrine ORM</td><td>16</td><td>142,67</td><td>112,148 ms</td><td>134 ms</td><td>141 ms</td></tr>
        <tr><td>Eloquent</td><td>16</td><td>122,11</td><td>131,027 ms</td><td>170 ms</td><td>180 ms</td></tr>
    </tbody>
</table>

<p>El endpoint HTTP cargó una entidad por id y devolvió el mismo JSON mediante Nginx y PHP-FPM. Se realizaron 500 solicitudes calientes por ORM y nivel de concurrencia, todas exitosas. Con concurrencia 1 los resultados quedaron dentro de un margen aproximado del 4 %, confirmando que la apertura de la conexión física domina este escenario. Con concurrencia 16 Doctrine obtuvo el mayor throughput; esta tabla corresponde a una corrida controlada y debe repetirse para evaluar regresiones o diferencias pequeñas.</p>

<h4>Perfil de inicialización y conexión</h4>

<p>Para explicar la diferencia entre el pipeline HTTP mínimo y el endpoint con persistencia, se instrumentaron 200 solicitudes calientes con marcas de alta resolución. El perfil separó el bootstrap de Scoop, la resolución de EPM, la creación perezosa del objeto <code>Connection</code>, el primer viaje a PostgreSQL y una consulta posterior sobre la conexión ya abierta.</p>

<table>
    <thead><tr><th>Fase</th><th>Mediana</th><th>p95</th></tr></thead>
    <tbody>
        <tr><td>Carga inicial de clases</td><td>0,016 ms</td><td>0,036 ms</td></tr>
        <tr><td><code>Context::load()</code></td><td>0,467 ms</td><td>0,658 ms</td></tr>
        <tr><td>Resolución de EPM</td><td>0,161 ms</td><td>0,326 ms</td></tr>
        <tr><td>Creación del objeto <code>Connection</code></td><td>0,088 ms</td><td>0,213 ms</td></tr>
        <tr><td>Primera conexión física y <code>SELECT 1</code></td><td>19,058 ms</td><td>21,495 ms</td></tr>
        <tr><td><code>SELECT 1</code> con conexión reutilizada</td><td>0,879 ms</td><td>1,194 ms</td></tr>
        <tr><td>Consulta e hidratación EPM por id</td><td>3,711 ms</td><td>4,870 ms</td></tr>
        <tr><td>Serialización JSON</td><td>0,006 ms</td><td>0,009 ms</td></tr>
    </tbody>
</table>

<p>La instrumentación completa obtuvo una mediana de 24,610 ms. Este total incluyó consultas diagnósticas adicionales y no representa por sí solo un endpoint de aplicación. En esta topología, aproximadamente el 77 % del tiempo instrumentado correspondió a abrir la conexión física y completar el primer viaje a PostgreSQL. El bootstrap de Scoop y la resolución de EPM sumaron aproximadamente 0,63 ms. Por tanto, no es correcto atribuir los 23 ms de diferencia exclusivamente a la conexión, pero sí identificarla como el componente dominante.</p>

<p class="doc-alert"><b>Memoria de los ORM:</b> El pico reportado fue de 4 MiB en la mayoría de las operaciones y de 6 MiB en algunas cargas de 1.000 entidades. Estos valores reflejan bloques reservados por el asignador de PHP; no ofrecen resolución suficiente para ordenar los ORM por consumo fino de memoria.</p>

<h3>Servicios internos</h3>

<table>
    <thead><tr><th>Operación</th><th>Iteraciones</th><th>Mediana (µs)</th><th>p95 (µs)</th></tr></thead>
    <tbody>
        <tr><td>Injector: singleton resuelto</td><td>100.000</td><td>0,500</td><td>0,800</td></tr>
        <tr><td>Injector: contenedor y resolución en frío</td><td>10.000</td><td>101,687</td><td>216,473</td></tr>
        <tr><td>SDT: lookup de template compilado</td><td>100.000</td><td>21,098</td><td>37,595</td></tr>
        <tr><td>SDT: compilación en frío</td><td>5.000</td><td>448,081</td><td>1.028,537</td></tr>
        <tr><td>Caché en memoria: set</td><td>100.000</td><td>2,724</td><td>3,935</td></tr>
        <tr><td>Caché en memoria: hit</td><td>100.000</td><td>1,009</td><td>1,816</td></tr>
    </tbody>
</table>

<p class="doc-alert"><b>Reproducibilidad:</b> Los resultados pueden variar según CPU, sistema operativo, versión de Docker, configuración de FPM, Opcache, latencia de red y carga del anfitrión. Para comparar cambios entre versiones debe conservarse la misma topología, ejecutar calentamiento previo y repetir cada escenario varias veces.</p>

<h2>
    <a href="#cicd">Integración Continua (GitHub Actions)</a>
    <span class="anchor" id="cicd">...</span>
</h2>

<p>Scoop se integra de forma natural en flujos de trabajo modernos. A continuación, se presentan una serie de configuraciones avanzadas para <b>GitHub Actions</b> que ilustran diferentes estratégias de despliegue.</p>

<h3>FTP</h3>

<pre><code class="language-yaml">name: deploy
on:
  push:
    branches:
      - master
concurrency:
  group: ci-$&#123;{ github.ref }&#125;
  cancel-in-progress: true
jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v6
      - name: Set up PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2
          tools: composer
      - name: Install Composer dependencies
        run: composer install --prefer-dist --no-interaction --no-progress --optimize-autoloader
      - name: Compile to production
        run: composer build
      - name: Execute tests
        run: composer test
      - name: Set up Node.js
        uses: actions/setup-node@v6
        with:
          node-version: '24'
      - name: Build assets
        run: |
          npm install
          npm start
      - name: Clean up development files
        run: |
          rm -rf .git .github node_modules tests .vscode .devcontainer
          rm -f *.md app/*.neon app/*.xml *.yml app/router.php package* composer* .dockerignore Dockerfile jsconfig.json vite.config.js
          rm -rf app/scripts app/styles app/routes
      - name: Deploy
        uses: SamKirkland/FTP-Deploy-Action@v4.3.6
        with:
          server: $&#123;{ secrets.FTP_SERVER }&#125;
          username: $&#123;{ secrets.FTP_USERNAME }&#125;
          password: $&#123;{ secrets.FTP_PASSWORD }&#125;
          local-dir: ./
          server-dir: ./htdocs/
</code></pre>

<h3>AWS</h3>

<p>Pruebas, construcción de imagen Docker subiendo a ECR y despliegue automatizado en EC2.</p>

<pre><code class="language-yaml">name: CI/CD
on:
  pull_request:
    branches: master
  push:
    branches:
      - master
      - dev
concurrency:
  group: ci-$&#123;{ github.ref }&#125;
  cancel-in-progress: true
env:
  TAG: $&#123;{ github.sha }&#125;
  AWS_REGION: us-east-2
  ECR_REPOSITORY: hiring$&#123;{ github.ref_name == 'dev' && '-dev' || '' }&#125;
  ECR_REGISTRY: $&#123;{ secrets.AWS_ACCOUNT_ID }&#125;.dkr.ecr.us-east-2.amazonaws.com
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout code
        uses: actions/checkout@v4
        with:
          fetch-depth: 0
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2
          extensions: sockets amqp
          coverage: none
      - name: Install Composer dependencies
        run: composer install --prefer-dist --no-interaction --no-progress --optimize-autoloader
      - name: Compile to production
        run: composer build
      - name: Execute tests
        run: composer test
  build:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout code
        uses: actions/checkout@v4
        with:
          fetch-depth: 0
      - name: Configure AWS credentials
        uses: aws-actions/configure-aws-credentials@v4
        with:
          aws-access-key-id: $&#123;{ secrets.AWS_ACCESS_KEY_ID }&#125;
          aws-secret-access-key: $&#123;{ secrets.AWS_SECRET_ACCESS_KEY }&#125;
          aws-region: $&#123;{ env.AWS_REGION }&#125;
      - name: Login to Amazon ECR
        uses: aws-actions/amazon-ecr-login@v2
      - name: Set up Docker Buildx
        uses: docker/setup-buildx-action@v3
      - name: Build and export
        uses: docker/build-push-action@v6
        with:
          push: true
          provenance: false
          tags: $&#123;{ env.ECR_REGISTRY }&#125;/$&#123;{ env.ECR_REPOSITORY }&#125;:$&#123;{ env.TAG }&#125;
          cache-from: type=registry,ref=$&#123;{ env.ECR_REGISTRY }&#125;/$&#123;{ env.ECR_REPOSITORY }&#125;:cache
          cache-to: type=registry,ref=$&#123;{ env.ECR_REGISTRY }&#125;/$&#123;{ env.ECR_REPOSITORY }&#125;:cache,mode=max
  check:
    runs-on: ubuntu-latest
    if: github.event_name != 'pull_request'
    needs:
      - test
      - build
    steps:
      - name: All checks passed
        run: |
          echo ✅ Build and test successful.
          echo Image $&#123;{ env.ECR_REGISTRY }&#125;/$&#123;{ env.ECR_REPOSITORY }&#125;:$&#123;{ github.sha }&#125; is ready for deployment.
  deploy:
    runs-on: ubuntu-latest
    needs: check
    strategy:
      fail-fast: true
      matrix:
        include:
          - name: WEPS
            ip: 127.0.0.1
            branch: master
            protocol: tcp4
          - name: WEPS staging
            ip: 2a01:4f9:c013:fbdf::1
            branch: dev
            protocol: tcp6
    name: $&#123;{ matrix.name }&#125;
    steps:
      - name: Set up WARP
        uses: fscarmen/warp-on-actions@v1.3
        if: github.ref_name == matrix.branch && matrix.protocol == 'tcp6'
        with:
          stack: ipv6
          mode: client
      - name: Wait for IPv6 network readiness
        if: github.ref_name == matrix.branch && matrix.protocol == 'tcp6'
        run: |
          for i in {1..5}; do
            if ping -6 -c 1 $&#123;{ matrix.ip }&#125;; then
              echo "IPv6 network is ready."
              exit 0
            fi
            echo "Waiting for IPv6 network... (attempt $i)"
            sleep 5
          done
          echo "IPv6 network is not ready after several attempts."
          exit 1
      - name: deploy to server
        uses: appleboy/ssh-action@v1
        if: github.ref_name == matrix.branch
        with:
          host: $&#123;{ matrix.ip }&#125;
          username: deployer
          password: $&#123;{ secrets.DEPLOYER_PASSWORD }&#125;
          protocol: $&#123;{ matrix.protocol }&#125;
          envs: AWS_REGION,TAG,ECR_REGISTRY,ECR_REPOSITORY
          script: ./deploy.sh
</code></pre>

<p class="doc-alert"><b>Seguridad:</b> Nunca incluya secretos (llaves de cipher o contraseñas de DB) directamente en el archivo YAML. Utilice los <i>Secrets</i> de GitHub y mapee los valores mediante variables de entorno.</p>
