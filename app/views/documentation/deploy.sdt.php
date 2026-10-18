<p>Scoop está diseñado para escalar desde despliegues atómicos —basados en la transferencia simple de archivos— hasta flujos de trabajo avanzados con tuberías de <b>Integración y Despliegue Continuo (CI/CD)</b>. La arquitectura del motor garantiza que, independientemente del método elegido, el sistema mantenga su integridad y alto rendimiento.</p>

<p><ul>
  <li><a href="#debug">Debug mode</a></li>
  <li><a href="#quality">Estándares de Calidad</a></li>
  <li><a href="#automation">Automatización con Hooks</a></li>
  <li><a href="#optimization">Optimización de Producción</a></li>
  <li><a href="#frankenphp">Docker con FrankenPHP</a></li>
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
    <li><b>Inyección dinámica:</b> El Injector ignora completamente los mapas precompilados de dependencias y analiza los constructores mediante Reflection. De esta forma, los cambios en las firmas se reflejan sin ejecutar previamente <code>ice scan source</code>.</li>
    <li><b>Trazabilidad de vistas SDT:</b> Las plantillas se compilan sin minificación y conservan sus saltos de línea. Cuando falla el PHP generado, el archivo <code>.debug.php</code> permite ubicar la línea correspondiente en el <code>.sdt.php</code> original.</li>
    <li><b>Visibilidad Total:</b> El <code>Http\Error\Mapper</code> renderiza trazas completas de errores (Stack Trace), archivos y líneas afectadas.</li>
</ul>

<p>En entornos productivos, es obligatorio que <code>display_errors</code> esté desactivado. Mantener el Debug Mode activo en producción conlleva dos riesgos críticos:</p>

<ol>
    <li><b>Fuga de Información:</b> Las trazas de error pueden exponer credenciales, rutas de archivos y lógica interna del negocio.</li>
    <li><b>Degradación de Rendimiento:</b> El escaneo constante de archivos (I/O) y el análisis de tokens de PHP eliminan las optimizaciones de caché, aumentando significativamente el tiempo de respuesta.</li>
</ol>

<p>Al desactivar el Debug Mode, el sistema dejará de escanear el sistema de archivos y confiará exclusivamente en los mapas compilados. Las vistas volverán a utilizar archivos <code>.php</code> minificados, separados de los artefactos <code>.debug.php</code> de desarrollo. Asegúrese de haber ejecutado los comandos de <code>ice scan</code> antes de pasar a un entorno inmutable.</p>

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

<p class="doc-alert p-md rounded-lg text-on-surface bg-warning-subtle border-warning"><b>Caché de construcción:</b> Los mapas generados forman parte de los artefactos del despliegue y no deben editarse manualmente. Si se necesita reconstruirlos sin esperar cambios en las fuentes, ejecute los comandos de escaneo con el flag <code>-f</code>.</p>

<p>También tenemos el comando <code>app/ice dbup</code> que no puede ser ejecutado en compilación o creación de la imagen, si no que se debe ejecutar cuando se haya desplegado en el servidor para que logre conectar con la base de datos.</p>

<p class="doc-alert p-md rounded-lg text-on-surface bg-warning-subtle border-warning"><b>Pro-Tip de Despliegue:</b> Asegúrese siempre de ejecutar <code>composer install --optimize-autoloader --no-dev</code> en el servidor de destino para minimizar la latencia del cargador de clases de PHP.</p>

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

<p class="doc-alert p-md rounded-lg text-on-surface bg-warning-subtle border-warning"><b>Permisos de Escritura:</b> El proceso de construcción genera archivos dentro de <code>app/storage/cache</code>. Asegúrese de que el usuario del servidor web (ej. www-data) tenga permisos de lectura sobre estos archivos y permisos de escritura sobre la carpeta <code>storage</code> para logs y caché persistente.</p>

<h2>
    <a href="#frankenphp">Docker con FrankenPHP</a>
    <span class="anchor" id="frankenphp">...</span>
</h2>

<p>El entorno Docker de Scoop utiliza <b>FrankenPHP 1.13.0 y PHP 8.5 en modo clásico</b>, con una ejecución independiente por petición.</p>

<p>Después de modificar <code>.devcontainer</code>, reconstruye el contenedor de desarrollo. En producción, reconstruye y reemplaza la imagen para actualizar código o configuración.</p>

<h3>Configuración compartida</h3>

<p>La carpeta <code>.devcontainer</code> contiene tres árboles que reproducen las rutas del sistema operativo dentro de la imagen:</p>

<ul>
    <li><code>common/etc/php/extensions.list</code>: extensiones para ambos ambientes, una por línea y sin comentarios. Xdebug se instala adicionalmente sólo en desarrollo.</li>
    <li><code>common/etc/php/conf.d/00-common.ini</code> y <code>common/etc/caddy/scoop.caddy</code>: ajustes PHP y rutas HTTP compartidos.</li>
    <li><code>dev/etc/php/conf.d/dev.ini</code> y <code>dev/etc/caddy/Caddyfile</code>: errores visibles, recarga de OPcache y configuración de Xdebug y HTTP local.</li>
    <li><code>prod/etc/php/conf.d/prod.ini</code> y <code>prod/etc/caddy/Caddyfile</code>: errores ocultos, OPcache sin comprobación de cambios y servidor de producción.</li>
</ul>

<p>Añade las extensiones PHP que necesites a <code>common/etc/php/extensions.list</code> y reconstruye el contenedor. Aplica la configuración compartida en <code>common/</code> y los ajustes de cada entorno en <code>dev/</code> o <code>prod/</code>.</p>

<h3>Desarrollo con Dev Containers</h3>

<ol>
    <li>Iniciar Docker con contenedores Linux y abrir el proyecto en VS Code.</li>
    <li>Ejecutar <b>Dev Containers: Rebuild and Reopen in Container</b>.</li>
    <li>Esperar a que se instalen las dependencias de npm y Composer y termine <code>php app/ice dbup</code>.</li>
    <li>Abrir <code>http://localhost:8000</code>.</li>
</ol>

<p>Al adjuntar VS Code se inician Vite y FrankenPHP. Trabaja desde <code>http://localhost:8000</code>; la salida de ambos procesos aparece identificada como <code>vite</code> y <code>php</code>. Si vuelves a adjuntar el editor, comprueba que no haya otra instancia usando los mismos puertos.</p>

<p>Compose monta el proyecto en <code>/app</code> y utiliza volúmenes separados para <code>vendor</code>, <code>node_modules</code> y PostgreSQL. <code>CHOKIDAR_USEPOLLING=true</code> y <code>CHOKIDAR_INTERVAL=300</code> permiten detectar ediciones desde Windows en Docker Desktop; el polling puede desactivarse donde los eventos nativos de archivos funcionen correctamente. Xdebug se conecta a <code>host.docker.internal:9003</code>: activar la escucha del depurador, mapear <code>/app</code> al proyecto local y utilizar <code>XDEBUG_TRIGGER=scoop</code>.</p>

<h3>Imagen de producción</h3>

<pre><code class="language-shell">docker build -t scoop-frankenphp-production .
docker run --name scoop --sysctl net.ipv4.ip_unprivileged_port_start=0 \
  -p 80:80 scoop-frankenphp-production
</code></pre>

<p>El build prepara los assets y las dependencias de producción. La aplicación se ejecuta con el usuario 1000; utiliza ese UID al dar permisos a los volúmenes que necesite escribir.</p>

<p>Para ejecutar una tarea CLI en un contenedor temporal, añade el comando al nombre de la imagen:</p>

<pre><code class="language-shell">docker run --rm scoop-frankenphp-production php app/ice --help
</code></pre>

<p>Pasar <code>DB_HOST</code>, <code>DB_USER</code>, <code>DB_PASSWORD</code> y <code>DB_NAME</code> según la infraestructura del despliegue. La base de datos debe ser accesible desde el contenedor. Las migraciones se ejecutan después de conectar los servicios:</p>

<pre><code class="language-shell">docker exec scoop php app/ice dbup
</code></pre>

<p>PHP CLI está disponible para las tareas de Scoop. La configuración de producción desactiva <code>opcache.validate_timestamps</code>; los cambios de código se despliegan reconstruyendo y reemplazando el contenedor. Persistir <code>/app/app/storage</code> y <code>/tmp/php-sessions</code> cuando se necesite conservar almacenamiento y sesiones entre reemplazos, o configurar sus respectivos backends. El almacenamiento debe ser escribible por el usuario 1000 y preservar los mapas de caché generados durante la construcción.</p>

<h3>HTTPS y proxies</h3>

<p>Por defecto, <code>SERVER_NAME=:80</code> sirve HTTP en el puerto 80. Para HTTPS directo, indicar un dominio que apunte al servidor, permitir acceso a 80 y 443 y persistir los datos de Caddy:</p>

<pre><code class="language-shell">docker run --name scoop --sysctl net.ipv4.ip_unprivileged_port_start=0 \
  -p 80:80 -p 443:443 -p 443:443/udp \
  -e SERVER_NAME=example.com \
  -v scoop_caddy_data:/data -v scoop_caddy_config:/config \
  scoop-frankenphp-production
</code></pre>

<p>Sustituir <code>example.com</code> por el dominio real y añadir las variables y conexiones de la aplicación. Caddy gestiona automáticamente la emisión y renovación de certificados. Si Traefik u otra capa termina TLS, conservar <code>SERVER_NAME=:80</code> y conectar el proxy al puerto HTTP del contenedor. El healthcheck permanece en un listener HTTP interno independiente del dominio y de HTTPS.</p>

<h3>Logs del contenedor</h3>

<p>Consulta los logs con <code>docker logs -f scoop</code>. En producción se muestran en JSON; durante el desarrollo, consulta la salida del proceso <code>php</code> en el editor. Ajusta los niveles en el <code>Caddyfile</code> del entorno correspondiente.</p>

<p>Esto no sustituye los handlers de log de tu aplicación. Para consultar también sus mensajes desde Docker, configura el handler <code>Standard</code>; un handler <code>File</code> sigue escribiendo en su archivo. Consulta <a href="{{#view->route('doc', 'ecosystem')}}#monitoring">Monitoreo y Logging</a>.</p>

<h3>Healthcheck y verificación</h3>

<p>Los dos Dockerfiles definen el healthcheck mediante cURL de PHP y el comando de Scoop:</p>

<pre><code class="language-shell">php app/ice check health --port=8001 --path=health-check
</code></pre>

<p>El listener 8001 está limitado a <code>127.0.0.1</code> dentro del contenedor y no se publica en producción. El chequeo requiere una respuesta HTTP 200 de <code>/health-check/</code>; no sustituye una prueba específica de base de datos u otros servicios. Docker lo ejecuta cada 30 segundos, con 90 segundos de margen inicial, un timeout de 15 segundos y tres fallos consecutivos antes de marcar el contenedor como <code>unhealthy</code>.</p>

<pre><code class="language-shell">docker exec scoop php -m
docker exec scoop frankenphp validate --config /etc/caddy/Caddyfile --adapter caddyfile
docker exec scoop php app/ice check health --port=8001 --path=health-check
docker inspect scoop --format '&#123;&#123;.State.Health.Status&#125;&#125;'
</code></pre>

<p>La configuración compartida sirve archivos estáticos únicamente desde <code>public/</code> y envía las demás rutas al front controller de Scoop. Comprobar la página principal, assets, sesiones y rutas inexistentes; los archivos privados deben devolver 404. <code>/index.php</code> redirige a <code>/</code> y las rutas sin extensión reciben slash final conservando su query string.</p>

<h2>
    <a href="#benchmarks">Benchmarks</a>
    <span class="anchor" id="benchmarks">...</span>
</h2>

<p>Snapshot reproducible del framework ejecutado el 21/08/2026 en Docker Linux x86_64: PHP 8.5.9, Opcache, PostgreSQL 17 y source <code>aef2d7c3c7f8</code>. Cada grupo se ejecutó completo en dos rondas independientes; se publica la mediana entre rondas. Los valores históricos fueron retirados para evitar mezclar fechas o infraestructura.</p>

<p class="doc-alert p-md rounded-lg text-on-surface bg-warning-subtle border-warning"><b>Metodología:</b> candidatos secuenciales, calentamiento previo, payloads y cardinalidades validados, estado administrado limpiado entre operaciones ORM y orden HTTP invertido en la segunda ronda. HTTP acumuló 120.000 respuestas esperadas, sin fallos de transporte ni contenido. Menor es mejor para latencia; mayor es mejor para req/s.</p>

<h3>Resolución de rutas</h3>
<p>Ruta dinámica al final del conjunto; Scoop usa su mapa compilado, Symfony Routing 8.1.2 su matcher compilado y FastRoute 1.3.1 su dispatcher generado.</p>
<table class="scoop-table w-full max-w-full my-md text-on-surface"><thead><tr><th scope="col">Rutas</th><th scope="col">Motor</th><th scope="col">Mediana</th><th scope="col">p95</th></tr></thead><tbody>        <tr><td data-label="Rutas"><span>100</span></td><td data-label="Motor"><span>Scoop</span></td><td data-label="Mediana"><span>1,251 µs</span></td><td data-label="p95"><span>1,900 µs</span></td></tr>
    <tr><td data-label="Rutas"><span>100</span></td><td data-label="Motor"><span>Symfony compilado</span></td><td data-label="Mediana"><span>2,500 µs</span></td><td data-label="p95"><span>3,601 µs</span></td></tr>
    <tr><td data-label="Rutas"><span>100</span></td><td data-label="Motor"><span>FastRoute</span></td><td data-label="Mediana"><span>3,301 µs</span></td><td data-label="p95"><span>5,200 µs</span></td></tr>
    <tr><td data-label="Rutas"><span>1.000</span></td><td data-label="Motor"><span>Scoop</span></td><td data-label="Mediana"><span>1,300 µs</span></td><td data-label="p95"><span>1,551 µs</span></td></tr>
    <tr><td data-label="Rutas"><span>1.000</span></td><td data-label="Motor"><span>Symfony compilado</span></td><td data-label="Mediana"><span>5,701 µs</span></td><td data-label="p95"><span>7,101 µs</span></td></tr>
    <tr><td data-label="Rutas"><span>1.000</span></td><td data-label="Motor"><span>FastRoute</span></td><td data-label="Mediana"><span>37,554 µs</span></td><td data-label="p95"><span>91,959 µs</span></td></tr>
    <tr><td data-label="Rutas"><span>10.000</span></td><td data-label="Motor"><span>Scoop</span></td><td data-label="Mediana"><span>1,400 µs</span></td><td data-label="p95"><span>1,750 µs</span></td></tr>
    <tr><td data-label="Rutas"><span>10.000</span></td><td data-label="Motor"><span>Symfony compilado</span></td><td data-label="Mediana"><span>22,953 µs</span></td><td data-label="p95"><span>43,255 µs</span></td></tr>
    <tr><td data-label="Rutas"><span>10.000</span></td><td data-label="Motor"><span>FastRoute</span></td><td data-label="Mediana"><span>1.743,894 µs</span></td><td data-label="p95"><span>2.596,212 µs</span></td></tr>
</tbody></table>

<h3>Pipeline HTTP mínimo</h3>
<p>Mismo JSON sobre Nginx/PHP-FPM: Scoop, Slim 4.15.2, Symfony 8.1.2 y Laravel 13.24.0; 2.000 solicitudes por combinación y ronda.</p>
<table class="scoop-table w-full max-w-full my-md text-on-surface"><thead><tr><th scope="col">Aplicación</th><th scope="col">Concurrencia</th><th scope="col">Req/s</th><th scope="col">Media</th><th scope="col">p50</th><th scope="col">p95</th><th scope="col">p99</th><th scope="col">Fallidas</th></tr></thead><tbody>
    <tr><td data-label="Aplicación"><span>Scoop</span></td><td data-label="Concurrencia"><span>1</span></td><td data-label="Req/s"><span>374,18</span></td><td data-label="Media"><span>2,689 ms</span></td><td data-label="p50"><span>2,5 ms</span></td><td data-label="p95"><span>3,5 ms</span></td><td data-label="p99"><span>4,5 ms</span></td><td data-label="Fallidas"><span>0</span></td></tr>
    <tr><td data-label="Aplicación"><span>Slim</span></td><td data-label="Concurrencia"><span>1</span></td><td data-label="Req/s"><span>407,62</span></td><td data-label="Media"><span>2,460 ms</span></td><td data-label="p50"><span>2,0 ms</span></td><td data-label="p95"><span>3,0 ms</span></td><td data-label="p99"><span>3,5 ms</span></td><td data-label="Fallidas"><span>0</span></td></tr>
    <tr><td data-label="Aplicación"><span>Symfony</span></td><td data-label="Concurrencia"><span>1</span></td><td data-label="Req/s"><span>282,34</span></td><td data-label="Media"><span>3,545 ms</span></td><td data-label="p50"><span>3,0 ms</span></td><td data-label="p95"><span>4,0 ms</span></td><td data-label="p99"><span>5,0 ms</span></td><td data-label="Fallidas"><span>0</span></td></tr>
    <tr><td data-label="Aplicación"><span>Laravel</span></td><td data-label="Concurrencia"><span>1</span></td><td data-label="Req/s"><span>128,40</span></td><td data-label="Media"><span>7,795 ms</span></td><td data-label="p50"><span>7,0 ms</span></td><td data-label="p95"><span>9,5 ms</span></td><td data-label="p99"><span>15,5 ms</span></td><td data-label="Fallidas"><span>0</span></td></tr>
    <tr><td data-label="Aplicación"><span>Scoop</span></td><td data-label="Concurrencia"><span>16</span></td><td data-label="Req/s"><span>1.478,20</span></td><td data-label="Media"><span>12,042 ms</span></td><td data-label="p50"><span>9,5 ms</span></td><td data-label="p95"><span>26,0 ms</span></td><td data-label="p99"><span>37,0 ms</span></td><td data-label="Fallidas"><span>0</span></td></tr>
    <tr><td data-label="Aplicación"><span>Slim</span></td><td data-label="Concurrencia"><span>16</span></td><td data-label="Req/s"><span>1.677,30</span></td><td data-label="Media"><span>9,863 ms</span></td><td data-label="p50"><span>8,0 ms</span></td><td data-label="p95"><span>19,5 ms</span></td><td data-label="p99"><span>30,0 ms</span></td><td data-label="Fallidas"><span>0</span></td></tr>
    <tr><td data-label="Aplicación"><span>Symfony</span></td><td data-label="Concurrencia"><span>16</span></td><td data-label="Req/s"><span>1.294,71</span></td><td data-label="Media"><span>12,359 ms</span></td><td data-label="p50"><span>11,0 ms</span></td><td data-label="p95"><span>23,0 ms</span></td><td data-label="p99"><span>30,5 ms</span></td><td data-label="Fallidas"><span>0</span></td></tr>
    <tr><td data-label="Aplicación"><span>Laravel</span></td><td data-label="Concurrencia"><span>16</span></td><td data-label="Req/s"><span>575,94</span></td><td data-label="Media"><span>28,026 ms</span></td><td data-label="p50"><span>24,0 ms</span></td><td data-label="p95"><span>55,0 ms</span></td><td data-label="p99"><span>81,5 ms</span></td><td data-label="Fallidas"><span>0</span></td></tr>
</tbody></table>

<h3>Pipeline HTTP con DI y validación</h3>
<p>Petición funcional con routing, controlador y servicio resueltos por inyección, cinco campos con reglas equivalentes, errores materializados, transformación y JSON. Se comparan Scoop; Slim 4.15.2 con Symfony DependencyInjection 8.1.4 compilado y Validator 8.1.4; Symfony 8.1.2 con Validator 8.1.4; y Laravel 13.24.0. La etiqueta de Slim describe un stack explícito porque Slim no incorpora DI ni validación propios.</p>
<table class="scoop-table w-full max-w-full my-md text-on-surface"><thead><tr><th scope="col">Payload</th><th scope="col">Aplicación</th><th scope="col">Concurrencia</th><th scope="col">HTTP esperado</th><th scope="col">Req/s</th><th scope="col">Media</th><th scope="col">p50</th><th scope="col">p95</th><th scope="col">p99</th><th scope="col">Fallos</th></tr></thead><tbody>
    <tr><td data-label="Payload"><span>Válido</span></td><td data-label="Aplicación"><span>Scoop</span></td><td data-label="Concurrencia"><span>1</span></td><td data-label="HTTP esperado"><span>200</span></td><td data-label="Req/s"><span>383,87</span></td><td data-label="Media"><span>2,619 ms</span></td><td data-label="p50"><span>2,5 ms</span></td><td data-label="p95"><span>3,0 ms</span></td><td data-label="p99"><span>3,5 ms</span></td><td data-label="Fallos"><span>0</span></td></tr>
    <tr><td data-label="Payload"><span>Válido</span></td><td data-label="Aplicación"><span>Slim + DI/Validator</span></td><td data-label="Concurrencia"><span>1</span></td><td data-label="HTTP esperado"><span>200</span></td><td data-label="Req/s"><span>345,80</span></td><td data-label="Media"><span>2,901 ms</span></td><td data-label="p50"><span>2,0 ms</span></td><td data-label="p95"><span>3,0 ms</span></td><td data-label="p99"><span>4,0 ms</span></td><td data-label="Fallos"><span>0</span></td></tr>
    <tr><td data-label="Payload"><span>Válido</span></td><td data-label="Aplicación"><span>Symfony</span></td><td data-label="Concurrencia"><span>1</span></td><td data-label="HTTP esperado"><span>200</span></td><td data-label="Req/s"><span>262,97</span></td><td data-label="Media"><span>3,804 ms</span></td><td data-label="p50"><span>3,5 ms</span></td><td data-label="p95"><span>4,5 ms</span></td><td data-label="p99"><span>5,0 ms</span></td><td data-label="Fallos"><span>0</span></td></tr>
    <tr><td data-label="Payload"><span>Válido</span></td><td data-label="Aplicación"><span>Laravel</span></td><td data-label="Concurrencia"><span>1</span></td><td data-label="HTTP esperado"><span>200</span></td><td data-label="Req/s"><span>122,02</span></td><td data-label="Media"><span>8,196 ms</span></td><td data-label="p50"><span>8,0 ms</span></td><td data-label="p95"><span>10,5 ms</span></td><td data-label="p99"><span>14,0 ms</span></td><td data-label="Fallos"><span>0</span></td></tr>
    <tr><td data-label="Payload"><span>Válido</span></td><td data-label="Aplicación"><span>Scoop</span></td><td data-label="Concurrencia"><span>16</span></td><td data-label="HTTP esperado"><span>200</span></td><td data-label="Req/s"><span>2.133,21</span></td><td data-label="Media"><span>7,522 ms</span></td><td data-label="p50"><span>7,0 ms</span></td><td data-label="p95"><span>12,5 ms</span></td><td data-label="p99"><span>16,5 ms</span></td><td data-label="Fallos"><span>0</span></td></tr>
    <tr><td data-label="Payload"><span>Válido</span></td><td data-label="Aplicación"><span>Slim + DI/Validator</span></td><td data-label="Concurrencia"><span>16</span></td><td data-label="HTTP esperado"><span>200</span></td><td data-label="Req/s"><span>2.043,51</span></td><td data-label="Media"><span>7,833 ms</span></td><td data-label="p50"><span>7,0 ms</span></td><td data-label="p95"><span>13,0 ms</span></td><td data-label="p99"><span>21,5 ms</span></td><td data-label="Fallos"><span>0</span></td></tr>
    <tr><td data-label="Payload"><span>Válido</span></td><td data-label="Aplicación"><span>Symfony</span></td><td data-label="Concurrencia"><span>16</span></td><td data-label="HTTP esperado"><span>200</span></td><td data-label="Req/s"><span>1.335,43</span></td><td data-label="Media"><span>12,299 ms</span></td><td data-label="p50"><span>11,0 ms</span></td><td data-label="p95"><span>22,0 ms</span></td><td data-label="p99"><span>29,5 ms</span></td><td data-label="Fallos"><span>0</span></td></tr>
    <tr><td data-label="Payload"><span>Válido</span></td><td data-label="Aplicación"><span>Laravel</span></td><td data-label="Concurrencia"><span>16</span></td><td data-label="HTTP esperado"><span>200</span></td><td data-label="Req/s"><span>616,40</span></td><td data-label="Media"><span>26,341 ms</span></td><td data-label="p50"><span>24,0 ms</span></td><td data-label="p95"><span>48,5 ms</span></td><td data-label="p99"><span>71,0 ms</span></td><td data-label="Fallos"><span>0</span></td></tr>
    <tr><td data-label="Payload"><span>Inválido</span></td><td data-label="Aplicación"><span>Scoop</span></td><td data-label="Concurrencia"><span>1</span></td><td data-label="HTTP esperado"><span>400</span></td><td data-label="Req/s"><span>341,85</span></td><td data-label="Media"><span>2,928 ms</span></td><td data-label="p50"><span>3,0 ms</span></td><td data-label="p95"><span>3,0 ms</span></td><td data-label="p99"><span>4,0 ms</span></td><td data-label="Fallos"><span>0</span></td></tr>
    <tr><td data-label="Payload"><span>Inválido</span></td><td data-label="Aplicación"><span>Slim + DI/Validator</span></td><td data-label="Concurrencia"><span>1</span></td><td data-label="HTTP esperado"><span>400</span></td><td data-label="Req/s"><span>330,42</span></td><td data-label="Media"><span>3,039 ms</span></td><td data-label="p50"><span>3,0 ms</span></td><td data-label="p95"><span>4,0 ms</span></td><td data-label="p99"><span>4,0 ms</span></td><td data-label="Fallos"><span>0</span></td></tr>
    <tr><td data-label="Payload"><span>Inválido</span></td><td data-label="Aplicación"><span>Symfony</span></td><td data-label="Concurrencia"><span>1</span></td><td data-label="HTTP esperado"><span>400</span></td><td data-label="Req/s"><span>254,44</span></td><td data-label="Media"><span>3,932 ms</span></td><td data-label="p50"><span>4,0 ms</span></td><td data-label="p95"><span>5,0 ms</span></td><td data-label="p99"><span>6,0 ms</span></td><td data-label="Fallos"><span>0</span></td></tr>
    <tr><td data-label="Payload"><span>Inválido</span></td><td data-label="Aplicación"><span>Laravel</span></td><td data-label="Concurrencia"><span>1</span></td><td data-label="HTTP esperado"><span>400</span></td><td data-label="Req/s"><span>112,00</span></td><td data-label="Media"><span>8,930 ms</span></td><td data-label="p50"><span>8,0 ms</span></td><td data-label="p95"><span>11,5 ms</span></td><td data-label="p99"><span>14,5 ms</span></td><td data-label="Fallos"><span>0</span></td></tr>
    <tr><td data-label="Payload"><span>Inválido</span></td><td data-label="Aplicación"><span>Scoop</span></td><td data-label="Concurrencia"><span>16</span></td><td data-label="HTTP esperado"><span>400</span></td><td data-label="Req/s"><span>1.600,03</span></td><td data-label="Media"><span>10,005 ms</span></td><td data-label="p50"><span>8,5 ms</span></td><td data-label="p95"><span>19,0 ms</span></td><td data-label="p99"><span>31,5 ms</span></td><td data-label="Fallos"><span>0</span></td></tr>
    <tr><td data-label="Payload"><span>Inválido</span></td><td data-label="Aplicación"><span>Slim + DI/Validator</span></td><td data-label="Concurrencia"><span>16</span></td><td data-label="HTTP esperado"><span>400</span></td><td data-label="Req/s"><span>1.302,01</span></td><td data-label="Media"><span>12,337 ms</span></td><td data-label="p50"><span>10,5 ms</span></td><td data-label="p95"><span>24,5 ms</span></td><td data-label="p99"><span>32,0 ms</span></td><td data-label="Fallos"><span>0</span></td></tr>
    <tr><td data-label="Payload"><span>Inválido</span></td><td data-label="Aplicación"><span>Symfony</span></td><td data-label="Concurrencia"><span>16</span></td><td data-label="HTTP esperado"><span>400</span></td><td data-label="Req/s"><span>979,51</span></td><td data-label="Media"><span>16,482 ms</span></td><td data-label="p50"><span>14,0 ms</span></td><td data-label="p95"><span>33,0 ms</span></td><td data-label="p99"><span>53,5 ms</span></td><td data-label="Fallos"><span>0</span></td></tr>
    <tr><td data-label="Payload"><span>Inválido</span></td><td data-label="Aplicación"><span>Laravel</span></td><td data-label="Concurrencia"><span>16</span></td><td data-label="HTTP esperado"><span>400</span></td><td data-label="Req/s"><span>417,18</span></td><td data-label="Media"><span>39,680 ms</span></td><td data-label="p50"><span>35,5 ms</span></td><td data-label="p95"><span>74,5 ms</span></td><td data-label="p99"><span>101,5 ms</span></td><td data-label="Fallos"><span>0</span></td></tr>
</tbody></table>

<h3>Persistencia PostgreSQL</h3>
<p>10.000 filas; lecturas materializadas y escrituras transaccionales equivalentes. Controles: PDO, Doctrine DBAL 4.4.4 e Illuminate Database 13.24.0.</p>
<table class="scoop-table w-full max-w-full my-md text-on-surface"><thead><tr><th scope="col">Operación</th><th scope="col">Implementación</th><th scope="col">Mediana</th><th scope="col">p95</th></tr></thead><tbody>
    <tr><td data-label="Operación"><span>Lectura de una fila</span></td><td data-label="Implementación"><span>PDO preparado</span></td><td data-label="Mediana"><span>321,987 µs</span></td><td data-label="p95"><span>587,568 µs</span></td></tr>
    <tr><td data-label="Operación"><span>Lectura de una fila</span></td><td data-label="Implementación"><span>Scoop Connection</span></td><td data-label="Mediana"><span>440,100 µs</span></td><td data-label="p95"><span>770,588 µs</span></td></tr>
    <tr><td data-label="Operación"><span>Lectura de una fila</span></td><td data-label="Implementación"><span>Doctrine DBAL</span></td><td data-label="Mediana"><span>564,464 µs</span></td><td data-label="p95"><span>987,912 µs</span></td></tr>
    <tr><td data-label="Operación"><span>Lectura de una fila</span></td><td data-label="Implementación"><span>Scoop Builder</span></td><td data-label="Mediana"><span>522,659 µs</span></td><td data-label="p95"><span>874,649 µs</span></td></tr>
    <tr><td data-label="Operación"><span>Lectura de una fila</span></td><td data-label="Implementación"><span>Eloquent Builder</span></td><td data-label="Mediana"><span>1.060,821 µs</span></td><td data-label="p95"><span>1.685,091 µs</span></td></tr>
    <tr><td data-label="Operación"><span>INSERT de 100 filas + rollback</span></td><td data-label="Implementación"><span>PDO</span></td><td data-label="Mediana"><span>1.659,939 µs</span></td><td data-label="p95"><span>2.464,830 µs</span></td></tr>
    <tr><td data-label="Operación"><span>INSERT de 100 filas + rollback</span></td><td data-label="Implementación"><span>Doctrine DBAL</span></td><td data-label="Mediana"><span>2.794,617 µs</span></td><td data-label="p95"><span>3.917,494 µs</span></td></tr>
    <tr><td data-label="Operación"><span>INSERT de 100 filas + rollback</span></td><td data-label="Implementación"><span>Scoop Builder</span></td><td data-label="Mediana"><span>2.258,803 µs</span></td><td data-label="p95"><span>3.480,938 µs</span></td></tr>
    <tr><td data-label="Operación"><span>INSERT de 100 filas + rollback</span></td><td data-label="Implementación"><span>Eloquent Builder</span></td><td data-label="Mediana"><span>3.790,024 µs</span></td><td data-label="p95"><span>4.977,106 µs</span></td></tr>
</tbody></table>

<h3>ORM CLI</h3>
<p>EPM, Doctrine ORM 3.6.8 y Eloquent 13.24.0 consultaron y materializaron el mismo esquema. Cada ORM limpió su estado administrado entre iteraciones.</p>
<table class="scoop-table w-full max-w-full my-md text-on-surface"><thead><tr><th scope="col">Operación</th><th scope="col">EPM</th><th scope="col">Doctrine ORM</th><th scope="col">Eloquent</th></tr></thead><tbody>
    <tr><td data-label="Operación"><span>Carga por id</span></td><td data-label="EPM"><span>0,796 ms</span></td><td data-label="Doctrine ORM"><span>0,645 ms</span></td><td data-label="Eloquent"><span>1,001 ms</span></td></tr>
    <tr><td data-label="Operación"><span>10 entidades</span></td><td data-label="EPM"><span>1,332 ms</span></td><td data-label="Doctrine ORM"><span>0,823 ms</span></td><td data-label="Eloquent"><span>1,752 ms</span></td></tr>
    <tr><td data-label="Operación"><span>100 entidades</span></td><td data-label="EPM"><span>2,151 ms</span></td><td data-label="Doctrine ORM"><span>2,039 ms</span></td><td data-label="Eloquent"><span>3,258 ms</span></td></tr>
    <tr><td data-label="Operación"><span>1.000 entidades</span></td><td data-label="EPM"><span>9,536 ms</span></td><td data-label="Doctrine ORM"><span>15,144 ms</span></td><td data-label="Eloquent"><span>18,723 ms</span></td></tr>
</tbody></table>

<h3>Relación autores + libros</h3>
<p>Cinco libros por autor completamente materializados y validados en cada iteración. EPM <code>aggregate('books')</code> y Doctrine ORM usan una consulta con join; Eloquent usa su eager loading idiomático de dos consultas.</p>
<table class="scoop-table w-full max-w-full my-md text-on-surface"><thead><tr><th scope="col">Grafo</th><th scope="col">Implementación</th><th scope="col">Mediana</th><th scope="col">p95</th></tr></thead><tbody>
    <tr><td data-label="Grafo"><span>10 autores + 50 libros</span></td><td data-label="Implementación"><span>EPM aggregate</span></td><td data-label="Mediana"><span>1.686,637 µs</span></td><td data-label="p95"><span>2.771,206 µs</span></td></tr>
    <tr><td data-label="Grafo"><span>10 autores + 50 libros</span></td><td data-label="Implementación"><span>Doctrine ORM</span></td><td data-label="Mediana"><span>3.472,885 µs</span></td><td data-label="p95"><span>5.352,044 µs</span></td></tr>
    <tr><td data-label="Grafo"><span>10 autores + 50 libros</span></td><td data-label="Implementación"><span>Eloquent</span></td><td data-label="Mediana"><span>4.329,030 µs</span></td><td data-label="p95"><span>6.169,835 µs</span></td></tr>
    <tr><td data-label="Grafo"><span>100 autores + 500 libros</span></td><td data-label="Implementación"><span>EPM aggregate</span></td><td data-label="Mediana"><span>6.847,257 µs</span></td><td data-label="p95"><span>9.304,927 µs</span></td></tr>
    <tr><td data-label="Grafo"><span>100 autores + 500 libros</span></td><td data-label="Implementación"><span>Doctrine ORM</span></td><td data-label="Mediana"><span>15.411,559 µs</span></td><td data-label="p95"><span>22.208,762 µs</span></td></tr>
    <tr><td data-label="Grafo"><span>100 autores + 500 libros</span></td><td data-label="Implementación"><span>Eloquent</span></td><td data-label="Mediana"><span>14.767,385 µs</span></td><td data-label="p95"><span>21.332,214 µs</span></td></tr>
</tbody></table>

<h3>ORM HTTP por id</h3>
<p>Los tres endpoints abrieron su ORM, cargaron una entidad, materializaron el título y devolvieron el mismo JSON; 2.000 solicitudes por combinación y ronda. El throughput absoluto está dominado por abrir una conexión física nueva a PostgreSQL en cada petición FPM; por eso este escenario compara el coste extremo a extremo de los ORM y no debe interpretarse como velocidad aislada de hidratación.</p>
<table class="scoop-table w-full max-w-full my-md text-on-surface"><thead><tr><th scope="col">ORM</th><th scope="col">Concurrencia</th><th scope="col">Req/s</th><th scope="col">Media</th><th scope="col">p50</th><th scope="col">p95</th><th scope="col">p99</th><th scope="col">Fallidas</th></tr></thead><tbody>
    <tr><td data-label="ORM"><span>EPM</span></td><td data-label="Concurrencia"><span>1</span></td><td data-label="Req/s"><span>39,69</span></td><td data-label="Media"><span>25,199 ms</span></td><td data-label="p50"><span>24,5 ms</span></td><td data-label="p95"><span>30,0 ms</span></td><td data-label="p99"><span>43,0 ms</span></td><td data-label="Fallidas"><span>0</span></td></tr>
    <tr><td data-label="ORM"><span>Doctrine ORM</span></td><td data-label="Concurrencia"><span>1</span></td><td data-label="Req/s"><span>39,44</span></td><td data-label="Media"><span>25,370 ms</span></td><td data-label="p50"><span>24,5 ms</span></td><td data-label="p95"><span>30,0 ms</span></td><td data-label="p99"><span>43,5 ms</span></td><td data-label="Fallidas"><span>0</span></td></tr>
    <tr><td data-label="ORM"><span>Eloquent</span></td><td data-label="Concurrencia"><span>1</span></td><td data-label="Req/s"><span>40,44</span></td><td data-label="Media"><span>24,763 ms</span></td><td data-label="p50"><span>24,0 ms</span></td><td data-label="p95"><span>29,0 ms</span></td><td data-label="p99"><span>37,5 ms</span></td><td data-label="Fallidas"><span>0</span></td></tr>
    <tr><td data-label="ORM"><span>EPM</span></td><td data-label="Concurrencia"><span>16</span></td><td data-label="Req/s"><span>140,31</span></td><td data-label="Media"><span>118,668 ms</span></td><td data-label="p50"><span>110,0 ms</span></td><td data-label="p95"><span>202,5 ms</span></td><td data-label="p99"><span>266,0 ms</span></td><td data-label="Fallidas"><span>0</span></td></tr>
    <tr><td data-label="ORM"><span>Doctrine ORM</span></td><td data-label="Concurrencia"><span>16</span></td><td data-label="Req/s"><span>121,15</span></td><td data-label="Media"><span>132,198 ms</span></td><td data-label="p50"><span>120,0 ms</span></td><td data-label="p95"><span>232,0 ms</span></td><td data-label="p99"><span>314,0 ms</span></td><td data-label="Fallidas"><span>0</span></td></tr>
    <tr><td data-label="ORM"><span>Eloquent</span></td><td data-label="Concurrencia"><span>16</span></td><td data-label="Req/s"><span>101,85</span></td><td data-label="Media"><span>158,816 ms</span></td><td data-label="p50"><span>147,0 ms</span></td><td data-label="p95"><span>282,0 ms</span></td><td data-label="p99"><span>372,5 ms</span></td><td data-label="Fallidas"><span>0</span></td></tr>
</tbody></table>

<h3>Servicios internos</h3>
<table class="scoop-table w-full max-w-full my-md text-on-surface"><thead><tr><th scope="col">Operación</th><th scope="col">Muestras</th><th scope="col">Mediana</th><th scope="col">p95</th></tr></thead><tbody>
    <tr><td data-label="Operación"><span>Injector: singleton resuelto</span></td><td data-label="Muestras"><span>100.000 por ronda</span></td><td data-label="Mediana"><span>0,350 µs</span></td><td data-label="p95"><span>0,550 µs</span></td></tr>
    <tr><td data-label="Operación"><span>Injector: configuración resuelta</span></td><td data-label="Muestras"><span>100.000 por ronda</span></td><td data-label="Mediana"><span>0,350 µs</span></td><td data-label="p95"><span>0,600 µs</span></td></tr>
    <tr><td data-label="Operación"><span>SDT: lookup compilado</span></td><td data-label="Muestras"><span>100.000 por ronda</span></td><td data-label="Mediana"><span>14,052 µs</span></td><td data-label="p95"><span>37,204 µs</span></td></tr>
    <tr><td data-label="Operación"><span>SDT: compilación en frío</span></td><td data-label="Muestras"><span>5.000 por ronda</span></td><td data-label="Mediana"><span>203,174 µs</span></td><td data-label="p95"><span>379,294 µs</span></td></tr>
    <tr><td data-label="Operación"><span>Caché memoria: set</span></td><td data-label="Muestras"><span>100.000 por ronda</span></td><td data-label="Mediana"><span>1,500 µs</span></td><td data-label="p95"><span>2,401 µs</span></td></tr>
    <tr><td data-label="Operación"><span>Caché memoria: hit</span></td><td data-label="Muestras"><span>100.000 por ronda</span></td><td data-label="Mediana"><span>0,400 µs</span></td><td data-label="p95"><span>0,650 µs</span></td></tr>
</tbody></table>

<p class="doc-alert p-md rounded-lg text-on-surface bg-warning-subtle border-warning"><b>Alcance:</b> estos resultados representan esta topología y estos casos concretos. El bundle canónico conserva las rondas, percentiles, versiones, hashes de instrumentos y salidas crudas para repetir o auditar la publicación.</p>

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
        run: composer run build
      - name: Set up Node.js
        uses: actions/setup-node@v6
        with:
          node-version: '24'
      - name: Build assets
        run: |
          npm install
          npm run build
      - name: Clean up development files
        run: |
          rm -rf .git .github node_modules tests .vscode .dockercontainer .devcontainer
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

<p class="doc-alert p-md rounded-lg text-on-surface bg-warning-subtle border-warning"><b>Seguridad:</b> Nunca incluya secretos (llaves de cipher o contraseñas de DB) directamente en el archivo YAML. Utilice los <i>Secrets</i> de GitHub y mapee los valores mediante variables de entorno.</p>
