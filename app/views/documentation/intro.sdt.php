<p>Esta es la documentación oficial de scoop, la cual se encuentra diseñada para empezar desde los conceptos más
básicos hasta llegar a los más complejos. Actualmente existe un roadmap con varias tareas por realizar como:</p>

<ul>
    <li>Complementar esta guia, si deseas ayudar realizá un pull request en <a href="https://github.com/marlonramirez/getscoop.org" target="_blank">github</a>.</li>
    <li><s><b>[v0.7]</b> Cambiar el sistema de controladores (Http\Controller? command\Controller).</s></li>
    <li><s><b>[v0.7]</b> Implementar el sistema de factorias (se eliminaron los tags, ya que no tenian sentido con las factorias).</s></li>
    <li><b>[v0.7]</b> Sobreescribir la configuración del entorno (testing e internacionalización).</li>
    <li><b>[v0.7]</b> Soportar uso de herencia con discriminator map en los value objects (state pattern).</li>
    <li><b>[v0.7]</b> Ajustar que no lance excepción si no se ha definido una propiedad para la relación (EPM).</li>
    <li><s><b>[v0.7]</b> Crear nuevo cargador tipo <i>instanceOf</i> para cargar automaticamente las clases que implementen una interface o extienden una clase.</s></li>
    <li><b>[v0.7]</b> Implementación de customTypes para el mapeo de entidades (EPM).</li>
    <li><b>[v0.8]</b> Cambiar la creación de componentes para hacerlo más usable estilo &lt;s-message /&gt;.</li>
    <li><b>[v0.8]</b> Modificar el sistema de excepciones, para que cada excepción pueda manejar su propia parametrización.</li>
    <li><b>[v0.8]</b> Cambiar la ejecución de dbup para que implemente RecursiveDirectoryIterator + RegexIterator en vez de glob y que ejecute solo la raz s no se pasa schema.</li>
    <li><b>[v0.8]</b> Implementar <a href="https://www.php-fig.org/psr/psr-16/" target="_blank">PSR-16</a> para almacenar en memoria las entidades (UPCu) y separar el sistema IoC del caché.</li>
    <li><b>[v0.8]</b> Cambiar el sistema de empaquetado para los assets (Rolldown, <i>RSpack, Farm</i> o Mako).</li>
    <li><b>[v0.8]</b> Obtener un objeto tipado desde el Payload (request) <code>$request->getBody($validator)->to(\App\Aplication\DTO::class)</code>.</li>
    <li><b>[v0.8]</b> Iniciar con la implementación de <a href="https://www.php-fig.org/psr/psr-7/" target="_blank">PSR-7</a> (Response).</li>
    <li><b>[v0.8]</b> Implementar multiples bounded context.</li>
    <li><b>[v0.8]</b> Implementar el sistema de bridges (comunicación entre bounded context).</li>
    <li><b>[v0.8]</b> Implementar <a href="https://www.php-fig.org/psr/psr-15/" target="_blank">PSR-15</a>.</li>
    <li><b>[v0.8]</b> Modificar el sistema de enrutamiento a un sisema de carpetas (similar a NextJS)</li>
    <li><b>[v0.9]</b> Implementar completamente <a href="https://www.php-fig.org/psr/psr-7/" target="_blank">PSR-7</a>.</li>
    <li><b>[v0.9]</b> Implementar <a href="https://www.php-fig.org/psr/psr-17/" target="_blank">PSR-17</a>.</li>
    <li><b>[v0.9]</b> Implementar <a href="https://www.php-fig.org/psr/psr-18/" target="_blank">PSR-18</a>.</li>
</ul>

<p>Antes de entrar en materia se van a aclarar algunas cosas sobre el uso y manejo de la herramienta.</p>

<ul>
    <li><a href="#filosofy">Filosofía de scoop</a></li>
    <li><a href="#good-practices">Buenas prácticas</a></li>
    <li><a href="#requirements">Requisitos y herramientas</a></li>
    <li><a href="#download">Medios de descarga</a></li>
    <li><a href="#prev-run">Antes de empezar</a></li>
</ul>

<h2>
    <a href="#filosofy">Filosofía de scoop</a>
    <span class="anchor" id="filosofy">...</span>
</h2>

<p>El bootstrapping es el proceso mediante el cual se diseñan y desarrollan entornos de programación
cada vez más complejos, partiendo de sistemas mucho más simples denominados bootstrap. Básicamente se
trata del core o motor que pone en marcha el software. Es decir que scoop es la capa más simple y
básica, pero que a su vez se encarga de dirigir las funcionalidades y procesos de cualquier aplicación
web escrita en PHP Orientada a Objetos.</p>

<p>Es importante diferenciar un sistema de bootstrapping y un framework, ya que un bootstrap no puede
ser un framework; pero un framework puede conciderarse un sistema de bootstrapping, ya que este se usa
para crear sistemas más complejos como aplicaciones web, mientras que con un bootstrap se puede generar
sistemas como frameworks o directamente aplicaciones web.</p>

<p>Scoop es un bootstrap escrito de una manera facil y elegante, conservando y teniendo siempre en
cuenta los principios <a href="http://es.wikipedia.org/wiki/Principio_KISS" rel="external">KISS</a>,
<a href="https://es.wikipedia.org/wiki/SOLID" rel="external">SOLID</a> y
<a href="http://es.wikipedia.org/wiki/No_te_repitas" rel="external">DRY</a>. Scoop intenta que el
proceso de desarrollar aplicaciones web orientadas a objetos con PHP no duela, facilitando algunas tareas
propias de una arquitectura por capas como el enrutamiento, inyección de dependencias y manejo de plantillas.</p>

<p>Scoop es simple; pero potente y flexible, proporciona poderosas herramientas para la creación de
frameworks o aplicaciones robustas, siempre teniendo en cuenta la comodidad del desarrollador.</p>

<h2>
    <a href="#good-practices">Buenas prácticas</a>
    <span class="anchor" id="good-practices">...</span>
</h2>

<p>En ocaciones otorgar libertad y flexibilidad al desarrollador significa dejar la puerta abierta a las
llamadas malas prácticas y como no se puede controlar todo a la vez y para mejorar la experiencia al momento
de desarrollar, en ocaciones es posible el uso de estas malas prácticas; aunque a lo largo de la guia se irán
explicando para advertir al usuario de su desaconcejable uso y la buena pratica que puede utilizar en su lugar.</p>

<p>Cuando la mala práctica sea muy grave o no estaba claro que lo era, será establecido en la misma documentación
para mas adelante explicarlo de la forma correcta.</p>

<h3>Manejo de versiones</h3>

<p>Esta guia esta basada en la versión estable de scoop a la fecha, la cual es la {{#view->getConfig('app.version')}}.
Esto es importante tenerlo en cuenta ya que algún ejemplo inicial puede no funcionar en versiones
anteriores.</p>

<p>Scoop trata de seguir el <a href="http://semver.org/lang/es/" rel="external">versionamiento semántico</a>
propuesto por Tom Preston-Werner.</p>

<h2>
    <a href="#requirements">Requisitos y herramientas</a>
    <span class="anchor" id="requirements">...</span>
</h2>

<p>Actualmente se cuenta con soporte para servidores apache y nginx, es necesario tener activo el modulo rewrite
de apache para el enrutamiento, algunas caracteristicas SEO y de seguridad. Para el caso de nginx se debe realizar
la configuración del archivo nginx.conf.</p>

<pre class="prettyprint lang-html">
merge_slashes off;
rewrite (.*)//+(.*) $1/$2 permanent;
location / {
    root html;
    index index.html index.htm index.php;
    try_files $uri $uri/ /?$args @rw-scoop;
    expires max;
}
location @rw-scoop {
    rewrite ^/(.*)(/?)$ /index.php?route=$1&$args? last;
}
if ($host ~* ^www\.(.*)) {
    set $host_without_www $1;
    rewrite ^/(.*)$ $scheme://$host_without_www/$1 permanent;
}
location ~ ^([^.\?]*[^/])$ {
   try_files $uri @addslash;
}
location @addslash {
   return 301 $uri/;
}
location /(app|vendor|scoop|resources)/ {
    deny all;
}
location ~ \.(htaccess|htpasswd|ini|log|bak)$ {
    deny all;
}
</pre>

<p class="doc-alert">Desde la versión 0.6.1 se incluye un servidor integrado, este se ejecuta automaticamente al ejecutar el entorno
de desarrollo.</p>

<p>Scoop ha sido desarrollado con <i>PHP 8.1</i> con soporte desde <i>PHP 5.3</i>. La percistencia
se puede manejar con MySQL, postgreSQL, SQLServer o cualquier motor de base de datos con soporte para PDO.</p>

<p>Es recomendable aunque no obligatorio instalar algunas de las herramientas que usa scoop para la
automatización de procesos, esto garantiza una mayor productividad en el desarrollo de aplicaciones.</p>

<ul>
    <li>
        <h3>Docker</h3>
        <p>Para simplificar el uso de la herramienta se usa <a href="https://docs.docker.com/get-docker/">docker</a> para el manejo de infraestructura.</p>
        <p>Si se desea
        realizar algún cambio de configuración se debe modificar el directorio <code>.devcontainer/etc</code>.</p>
        <p>La imagen de producción se basa en <a href="https://dockerfile.readthedocs.io/en/latest/content/DockerImages/dockerfiles/php-apache.html">webdevops/php-apache</a>.</p>
    </li>
    <li>
        <h3>Dev containers</h3>
        <p>Para agilizar el desarrollo de aplicaciones hacemos uso de la especificación de <a href="https://docs.github.com/en/codespaces/setting-up-your-project-for-codespaces/adding-a-dev-container-configuration/introduction-to-dev-containers">devcontainers</a>.</p>
        <p>La imagen de producción se basa en <a href="https://dockerfile.readthedocs.io/en/latest/content/DockerImages/dockerfiles/php-dev.html">webdevops/php-dev</a></p>
    </li>
</ul>

<p>En caso de no querer o poder instalar docker, se pueden manejar las herramientas de manera "manual".</p>

<ul>
    <li>
        <h3>Composer</h3>
        <p>Scoop utiliza <a href="https://getcomposer.org/download/" rel="external">composer</a>
        para el manejo de dependecias PHP.</p>
    </li>
    <li>
        <h3>NodeJS y npm</h3>
        <p><a href="http://nodejs.org/download/" rel="external">NodeJS</a> provee a scoop
        con un grupo de herramientas para el manejo de assets.</p>
    </li>
    <li>
        <h3 class="deprecated">Gulp</h3>
        <p>Desde la versión 0.1.4 el bootstrap utiliza <a href="http://gulpjs.com" rel="external">gulp</a>
        como automatizador de tareas.</p>
        <pre class="prettyprint">npm install -g gulp</pre>
        <p class="doc-alert">Desde la versión <code>0.4.1</code> no es necesario instalar gulp como libreria global,
        cada repositorio genera su propia instancia de gulp.</p>
    </li>
</ul>

<h2>
    <a href="#download">Medios de descarga</a>
    <span class="anchor" id="download">...</span>
</h2>

<p>Descargue las distintas versiones de scoop mediante el medio que más se ajuste a sus necesidades.
La versión actual del bootstrap es la {{#view->getConfig('app.version')}}.</p>
<ul>
    <li>
        <h3>Composer</h3>
        <p>Antes de usar este medio de instalación asegurese de tener composer instalado en su maquina,
        luego utiliza el comando <code>create-project</code> en la terminal.</p>
        <pre class="prettyprint">composer create-project mirdware/scoop project-name -s dev</pre>
    </li>
    <li>
        <h3>GitHub</h3>
        <p>Scoop se desarrolla usando github como sistema de control de versiones, mediante este
        se puede clonar todo el repositorio del proyecto.</p>
        <pre class="prettyprint lang-html">git clone https://github.com/mirdware/scoop.git</pre>
    </li>
    <li>
        <h3>Manual</h3>
        <p>Es el método más facíl y rapido para instalar scoop pero no el más recomendado, tan sencillo
        como <a href="https://github.com/mirdware/scoop/archive/master.zip">descargar</a> y
        descomprimir para luego renombrar la carpeta del proyecto.</p>
    </li>
</ul>

<h2>
    <a href="#prev-run">Antes de empezar</a>
    <span class="anchor" id="prev-run">...</span>
</h2>

<p>Al comenzar un nuevo proyecto sin <a href="https://docs.github.com/en/codespaces/setting-up-your-project-for-codespaces/adding-a-dev-container-configuration/introduction-to-dev-containers">devcontainers</a>
se deben instalar las librerias o dependencias tanto javascript como PHP ingresando los siguientes comandos en el directorio raíz de la aplicación.</p>

<h3>Desarrollo</h3>
<pre class="prettyprint">npm install && composer install && npm run dev</pre>

<h3>Producción</h3>
<pre class="prettyprint">npm install && composer install --optimize-autoloader --no-dev && npm start</pre>

<p>Al ejecutar modo producción la aplicación no se queda en escucha si no que realiza la minificación de archivos Javascript y CSS.
Para levantar el entorno de desarrollo se debe usar <code>npm run dev</code> el comando levantara un
proxy hot-reload que por defecto apunta a un servidor php built creado desde gulp. Para apuntar el proxy
a un host diferente se debe configurara la variable de entorno <code>PHP_HOST</code>.</p>

<p>Para probar que todo ha salido bien ingresa a <a href="//localhost:8001">tu entorno local</a>, ya
deberias tener instalada en tú maquina toda la estructura para usar scoop.</p>
