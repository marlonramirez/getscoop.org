<p>
    Scoop esta creado para realizar desde despliegues sencillos que solo implican copiar y pegar los archivos del proyecto en
    el host, hasta despliegues mucho más avanzados como pipelines que incluyan CI/CD. De la misma manera como funciona la
    <a href="{{#view->route('doc')}}#download">creación del proyecto</a> existen diversas formas de desplegar una aplicación y
    de esto dependera tambien las herramientas que debemos usar.
</p>

<ul>
    <li><a href="#testing">Pruebas</a></li>
    <li><a href="#linter">Linter</a></li>
    <li><a href="#static">Análisis estatico de código</a></li>
    <li><a href="#cicd">CI/CD</a></li>
    <li><a href="#without-containers">Sin contenedores</a></li>    
</ul>

<h2>
    <a href="#testing">Pruebas</a>
    <span class="anchor" id="testing">...</span>
</h2>

<h2>
    <a href="#linter">Linter</a>
    <span class="anchor" id="linter">...</span>
</h2>

<h2>
    <a href="#static">Análisis estatico de código</a>
    <span class="anchor" id="static">...</span>
</h2>

<h2>
    <a href="#cicd">CI/CD</a>
    <span class="anchor" id="cicd">...</span>
</h2>

<h2>
    <a href="#without-containers">Sin contenedores</a>
    <span class="anchor" id="without-containers">...</span>
</h2>

<p>
    Para asegurarnos que las reglas de estilos, análisis estático y pruebas ocurran sin configurar pipelines de CI/CD 
    o igual teniendo la configuración pero obteniendo un resultado inmediato; debemos hacer uso de hooks, para esto 
    nos podemos apoyar en grumphp.
</p>

<pre><code class="language-shell">composer require --dev phpro/grumphp-shim</code></pre>

<p>Luego configuramos el archivo <code>grumphp.yml</code></p>

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

<p>Si se va  a realizar un despliegue totalmente manual, se debe tener en cuenta que no es necesario desplegar todos los archivos de una
aplicación scoop y al contrario esto puede llegar a ser contraproducente, los archivos que deben ser desplegados son los minimos para su funcionamiento
evitando cualquier archivo de configuración de herramientas de desarrollo como la gestión de javascript, css y demás.</p>

<pre><code class="language-shell">├─ app
|   ├─ config
|   |    ├─ lang
|   |    |    ├─ en.php
|   |    |    └─ es.php
|   |    ├─ routes.php
|   |    └─ providers.php
|   ├─ storage
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
├─ composer.json
├─ index.php
└─ package.json
</code></pre>

<p class="doc-alert">Para más información revisé la sección de <a href="{{#view->route('doc', 'architecture')}}#structure">estructura de directorios</a>.</p>
