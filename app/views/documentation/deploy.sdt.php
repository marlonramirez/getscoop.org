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

<p>Snapshot reproducible del framework ejecutado el 21/08/2026 en Docker Linux x86_64: PHP 8.5.9, Opcache, PostgreSQL 17 y source <code>aef2d7c3c7f8</code>. Cada grupo se ejecutó completo en dos rondas independientes; se publica la mediana entre rondas. Los valores históricos fueron retirados para evitar mezclar fechas o infraestructura.</p>

<p class="doc-alert"><b>Metodología:</b> candidatos secuenciales, calentamiento previo, payloads y cardinalidades validados, estado administrado limpiado entre operaciones ORM y orden HTTP invertido en la segunda ronda. HTTP acumuló 120.000 respuestas esperadas, sin fallos de transporte ni contenido. Menor es mejor para latencia; mayor es mejor para req/s.</p>

<h3>Resolución de rutas</h3>
<p>Ruta dinámica al final del conjunto; Scoop usa su mapa compilado, Symfony Routing 8.1.2 su matcher compilado y FastRoute 1.3.1 su dispatcher generado.</p>
<table><thead><tr><th>Rutas</th><th>Motor</th><th>Mediana</th><th>p95</th></tr></thead><tbody>        <tr><td>100</td><td>Scoop</td><td>1,251 µs</td><td>1,900 µs</td></tr>
        <tr><td>100</td><td>Symfony compilado</td><td>2,500 µs</td><td>3,601 µs</td></tr>
        <tr><td>100</td><td>FastRoute</td><td>3,301 µs</td><td>5,200 µs</td></tr>
        <tr><td>1.000</td><td>Scoop</td><td>1,300 µs</td><td>1,551 µs</td></tr>
        <tr><td>1.000</td><td>Symfony compilado</td><td>5,701 µs</td><td>7,101 µs</td></tr>
        <tr><td>1.000</td><td>FastRoute</td><td>37,554 µs</td><td>91,959 µs</td></tr>
        <tr><td>10.000</td><td>Scoop</td><td>1,400 µs</td><td>1,750 µs</td></tr>
        <tr><td>10.000</td><td>Symfony compilado</td><td>22,953 µs</td><td>43,255 µs</td></tr>
        <tr><td>10.000</td><td>FastRoute</td><td>1.743,894 µs</td><td>2.596,212 µs</td></tr>
    </tbody></table>

<h3>Pipeline HTTP mínimo</h3>
<p>Mismo JSON sobre Nginx/PHP-FPM: Scoop, Slim 4.15.2, Symfony 8.1.2 y Laravel 13.24.0; 2.000 solicitudes por combinación y ronda.</p>
<table><thead><tr><th>Aplicación</th><th>Concurrencia</th><th>Req/s</th><th>Media</th><th>p50</th><th>p95</th><th>p99</th><th>Fallidas</th></tr></thead><tbody>
        <tr><td>Scoop</td><td>1</td><td>374,18</td><td>2,689 ms</td><td>2,5 ms</td><td>3,5 ms</td><td>4,5 ms</td><td>0</td></tr>
        <tr><td>Slim</td><td>1</td><td>407,62</td><td>2,460 ms</td><td>2,0 ms</td><td>3,0 ms</td><td>3,5 ms</td><td>0</td></tr>
        <tr><td>Symfony</td><td>1</td><td>282,34</td><td>3,545 ms</td><td>3,0 ms</td><td>4,0 ms</td><td>5,0 ms</td><td>0</td></tr>
        <tr><td>Laravel</td><td>1</td><td>128,40</td><td>7,795 ms</td><td>7,0 ms</td><td>9,5 ms</td><td>15,5 ms</td><td>0</td></tr>
        <tr><td>Scoop</td><td>16</td><td>1.478,20</td><td>12,042 ms</td><td>9,5 ms</td><td>26,0 ms</td><td>37,0 ms</td><td>0</td></tr>
        <tr><td>Slim</td><td>16</td><td>1.677,30</td><td>9,863 ms</td><td>8,0 ms</td><td>19,5 ms</td><td>30,0 ms</td><td>0</td></tr>
        <tr><td>Symfony</td><td>16</td><td>1.294,71</td><td>12,359 ms</td><td>11,0 ms</td><td>23,0 ms</td><td>30,5 ms</td><td>0</td></tr>
        <tr><td>Laravel</td><td>16</td><td>575,94</td><td>28,026 ms</td><td>24,0 ms</td><td>55,0 ms</td><td>81,5 ms</td><td>0</td></tr>
    </tbody></table>

<h3>Pipeline HTTP con DI y validación</h3>
<p>Petición funcional con routing, controlador y servicio resueltos por inyección, cinco campos con reglas equivalentes, errores materializados, transformación y JSON. Se comparan Scoop; Slim 4.15.2 con Symfony DependencyInjection 8.1.4 compilado y Validator 8.1.4; Symfony 8.1.2 con Validator 8.1.4; y Laravel 13.24.0. La etiqueta de Slim describe un stack explícito porque Slim no incorpora DI ni validación propios.</p>
<table><thead><tr><th>Payload</th><th>Aplicación</th><th>Concurrencia</th><th>HTTP esperado</th><th>Req/s</th><th>Media</th><th>p50</th><th>p95</th><th>p99</th><th>Fallos</th></tr></thead><tbody>
        <tr><td>Válido</td><td>Scoop</td><td>1</td><td>200</td><td>383,87</td><td>2,619 ms</td><td>2,5 ms</td><td>3,0 ms</td><td>3,5 ms</td><td>0</td></tr>
        <tr><td>Válido</td><td>Slim + DI/Validator</td><td>1</td><td>200</td><td>345,80</td><td>2,901 ms</td><td>2,0 ms</td><td>3,0 ms</td><td>4,0 ms</td><td>0</td></tr>
        <tr><td>Válido</td><td>Symfony</td><td>1</td><td>200</td><td>262,97</td><td>3,804 ms</td><td>3,5 ms</td><td>4,5 ms</td><td>5,0 ms</td><td>0</td></tr>
        <tr><td>Válido</td><td>Laravel</td><td>1</td><td>200</td><td>122,02</td><td>8,196 ms</td><td>8,0 ms</td><td>10,5 ms</td><td>14,0 ms</td><td>0</td></tr>
        <tr><td>Válido</td><td>Scoop</td><td>16</td><td>200</td><td>2.133,21</td><td>7,522 ms</td><td>7,0 ms</td><td>12,5 ms</td><td>16,5 ms</td><td>0</td></tr>
        <tr><td>Válido</td><td>Slim + DI/Validator</td><td>16</td><td>200</td><td>2.043,51</td><td>7,833 ms</td><td>7,0 ms</td><td>13,0 ms</td><td>21,5 ms</td><td>0</td></tr>
        <tr><td>Válido</td><td>Symfony</td><td>16</td><td>200</td><td>1.335,43</td><td>12,299 ms</td><td>11,0 ms</td><td>22,0 ms</td><td>29,5 ms</td><td>0</td></tr>
        <tr><td>Válido</td><td>Laravel</td><td>16</td><td>200</td><td>616,40</td><td>26,341 ms</td><td>24,0 ms</td><td>48,5 ms</td><td>71,0 ms</td><td>0</td></tr>
        <tr><td>Inválido</td><td>Scoop</td><td>1</td><td>400</td><td>341,85</td><td>2,928 ms</td><td>3,0 ms</td><td>3,0 ms</td><td>4,0 ms</td><td>0</td></tr>
        <tr><td>Inválido</td><td>Slim + DI/Validator</td><td>1</td><td>400</td><td>330,42</td><td>3,039 ms</td><td>3,0 ms</td><td>4,0 ms</td><td>4,0 ms</td><td>0</td></tr>
        <tr><td>Inválido</td><td>Symfony</td><td>1</td><td>400</td><td>254,44</td><td>3,932 ms</td><td>4,0 ms</td><td>5,0 ms</td><td>6,0 ms</td><td>0</td></tr>
        <tr><td>Inválido</td><td>Laravel</td><td>1</td><td>400</td><td>112,00</td><td>8,930 ms</td><td>8,0 ms</td><td>11,5 ms</td><td>14,5 ms</td><td>0</td></tr>
        <tr><td>Inválido</td><td>Scoop</td><td>16</td><td>400</td><td>1.600,03</td><td>10,005 ms</td><td>8,5 ms</td><td>19,0 ms</td><td>31,5 ms</td><td>0</td></tr>
        <tr><td>Inválido</td><td>Slim + DI/Validator</td><td>16</td><td>400</td><td>1.302,01</td><td>12,337 ms</td><td>10,5 ms</td><td>24,5 ms</td><td>32,0 ms</td><td>0</td></tr>
        <tr><td>Inválido</td><td>Symfony</td><td>16</td><td>400</td><td>979,51</td><td>16,482 ms</td><td>14,0 ms</td><td>33,0 ms</td><td>53,5 ms</td><td>0</td></tr>
        <tr><td>Inválido</td><td>Laravel</td><td>16</td><td>400</td><td>417,18</td><td>39,680 ms</td><td>35,5 ms</td><td>74,5 ms</td><td>101,5 ms</td><td>0</td></tr>
    </tbody></table>

<h3>Persistencia PostgreSQL</h3>
<p>10.000 filas; lecturas materializadas y escrituras transaccionales equivalentes. Controles: PDO, Doctrine DBAL 4.4.4 e Illuminate Database 13.24.0.</p>
<table><thead><tr><th>Operación</th><th>Implementación</th><th>Mediana</th><th>p95</th></tr></thead><tbody>
        <tr><td>Lectura de una fila</td><td>PDO preparado</td><td>321,987 µs</td><td>587,568 µs</td></tr>
        <tr><td>Lectura de una fila</td><td>Scoop Connection</td><td>440,100 µs</td><td>770,588 µs</td></tr>
        <tr><td>Lectura de una fila</td><td>Doctrine DBAL</td><td>564,464 µs</td><td>987,912 µs</td></tr>
        <tr><td>Lectura de una fila</td><td>Scoop Builder</td><td>522,659 µs</td><td>874,649 µs</td></tr>
        <tr><td>Lectura de una fila</td><td>Eloquent Builder</td><td>1.060,821 µs</td><td>1.685,091 µs</td></tr>
        <tr><td>INSERT de 100 filas + rollback</td><td>PDO</td><td>1.659,939 µs</td><td>2.464,830 µs</td></tr>
        <tr><td>INSERT de 100 filas + rollback</td><td>Doctrine DBAL</td><td>2.794,617 µs</td><td>3.917,494 µs</td></tr>
        <tr><td>INSERT de 100 filas + rollback</td><td>Scoop Builder</td><td>2.258,803 µs</td><td>3.480,938 µs</td></tr>
        <tr><td>INSERT de 100 filas + rollback</td><td>Eloquent Builder</td><td>3.790,024 µs</td><td>4.977,106 µs</td></tr>
    </tbody></table>

<h3>ORM CLI</h3>
<p>EPM, Doctrine ORM 3.6.8 y Eloquent 13.24.0 consultaron y materializaron el mismo esquema. Cada ORM limpió su estado administrado entre iteraciones.</p>
<table><thead><tr><th>Operación</th><th>EPM</th><th>Doctrine ORM</th><th>Eloquent</th></tr></thead><tbody>
        <tr><td>Carga por id</td><td>0,796 ms</td><td>0,645 ms</td><td>1,001 ms</td></tr>
        <tr><td>10 entidades</td><td>1,332 ms</td><td>0,823 ms</td><td>1,752 ms</td></tr>
        <tr><td>100 entidades</td><td>2,151 ms</td><td>2,039 ms</td><td>3,258 ms</td></tr>
        <tr><td>1.000 entidades</td><td>9,536 ms</td><td>15,144 ms</td><td>18,723 ms</td></tr>
    </tbody></table>

<h3>Relación autores + libros</h3>
<p>Cinco libros por autor completamente materializados y validados en cada iteración. EPM <code>aggregate('books')</code> y Doctrine ORM usan una consulta con join; Eloquent usa su eager loading idiomático de dos consultas.</p>
<table><thead><tr><th>Grafo</th><th>Implementación</th><th>Mediana</th><th>p95</th></tr></thead><tbody>
        <tr><td>10 autores + 50 libros</td><td>EPM aggregate</td><td>1.686,637 µs</td><td>2.771,206 µs</td></tr>
        <tr><td>10 autores + 50 libros</td><td>Doctrine ORM</td><td>3.472,885 µs</td><td>5.352,044 µs</td></tr>
        <tr><td>10 autores + 50 libros</td><td>Eloquent</td><td>4.329,030 µs</td><td>6.169,835 µs</td></tr>
        <tr><td>100 autores + 500 libros</td><td>EPM aggregate</td><td>6.847,257 µs</td><td>9.304,927 µs</td></tr>
        <tr><td>100 autores + 500 libros</td><td>Doctrine ORM</td><td>15.411,559 µs</td><td>22.208,762 µs</td></tr>
        <tr><td>100 autores + 500 libros</td><td>Eloquent</td><td>14.767,385 µs</td><td>21.332,214 µs</td></tr>
    </tbody></table>

<h3>ORM HTTP por id</h3>
<p>Los tres endpoints abrieron su ORM, cargaron una entidad, materializaron el título y devolvieron el mismo JSON; 2.000 solicitudes por combinación y ronda. El throughput absoluto está dominado por abrir una conexión física nueva a PostgreSQL en cada petición FPM; por eso este escenario compara el coste extremo a extremo de los ORM y no debe interpretarse como velocidad aislada de hidratación.</p>
<table><thead><tr><th>ORM</th><th>Concurrencia</th><th>Req/s</th><th>Media</th><th>p50</th><th>p95</th><th>p99</th><th>Fallidas</th></tr></thead><tbody>
        <tr><td>EPM</td><td>1</td><td>39,69</td><td>25,199 ms</td><td>24,5 ms</td><td>30,0 ms</td><td>43,0 ms</td><td>0</td></tr>
        <tr><td>Doctrine ORM</td><td>1</td><td>39,44</td><td>25,370 ms</td><td>24,5 ms</td><td>30,0 ms</td><td>43,5 ms</td><td>0</td></tr>
        <tr><td>Eloquent</td><td>1</td><td>40,44</td><td>24,763 ms</td><td>24,0 ms</td><td>29,0 ms</td><td>37,5 ms</td><td>0</td></tr>
        <tr><td>EPM</td><td>16</td><td>140,31</td><td>118,668 ms</td><td>110,0 ms</td><td>202,5 ms</td><td>266,0 ms</td><td>0</td></tr>
        <tr><td>Doctrine ORM</td><td>16</td><td>121,15</td><td>132,198 ms</td><td>120,0 ms</td><td>232,0 ms</td><td>314,0 ms</td><td>0</td></tr>
        <tr><td>Eloquent</td><td>16</td><td>101,85</td><td>158,816 ms</td><td>147,0 ms</td><td>282,0 ms</td><td>372,5 ms</td><td>0</td></tr>
    </tbody></table>

<h3>Servicios internos</h3>
<table><thead><tr><th>Operación</th><th>Muestras</th><th>Mediana</th><th>p95</th></tr></thead><tbody>
        <tr><td>Injector: singleton resuelto</td><td>100.000 por ronda</td><td>0,350 µs</td><td>0,550 µs</td></tr>
        <tr><td>Injector: configuración resuelta</td><td>100.000 por ronda</td><td>0,350 µs</td><td>0,600 µs</td></tr>
        <tr><td>SDT: lookup compilado</td><td>100.000 por ronda</td><td>14,052 µs</td><td>37,204 µs</td></tr>
        <tr><td>SDT: compilación en frío</td><td>5.000 por ronda</td><td>203,174 µs</td><td>379,294 µs</td></tr>
        <tr><td>Caché memoria: set</td><td>100.000 por ronda</td><td>1,500 µs</td><td>2,401 µs</td></tr>
        <tr><td>Caché memoria: hit</td><td>100.000 por ronda</td><td>0,400 µs</td><td>0,650 µs</td></tr>
    </tbody></table>

<p class="doc-alert"><b>Alcance:</b> estos resultados representan esta topología y estos casos concretos. El bundle canónico conserva las rondas, percentiles, versiones, hashes de instrumentos y salidas crudas para repetir o auditar la publicación.</p>

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
