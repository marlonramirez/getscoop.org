<p>Bienvenido a la documentación oficial de <b>Scoop</b>. Este motor está diseñado para transitar un camino evolutivo: desde la simplicidad de un micro-ruteador hasta la complejidad de una arquitectura distribuida.</p>

<p>Antes de entrar en materia técnica, definiremos los principios que rigen el sistema y las bases necesarias para operar con rigor arquitectónico.</p>

<ul>
    <li><a href="#filosofy">Filosofía de scoop</a></li>
    <li><a href="#good-practices">Buenas prácticas</a></li>
    <li><a href="#requirements">Requisitos y herramientas</a></li>
    <li><a href="#download">Medios de descarga</a></li>
    <li><a href="#prev-run">Antes de empezar</a></li>
    <li><a href="#roadmap">Roadmap de evolución</a></li>
</ul>

<h2>
    <a href="#filosofy">Filosofía de scoop</a>
    <span class="anchor" id="filosofy">...</span>
</h2>

<p>El <b>bootstrapping</b> es el proceso de diseñar entornos de ejecución complejos partiendo de un núcleo determinista. Scoop es ese núcleo: el <b>motor de ignición</b> que orquesta las funcionalidades esenciales de cualquier aplicación PHP Orientada a Objetos sin imponer una estructura rígida ni comportamientos ocultos.</p>

<p>Aunque Scoop ha evolucionado hasta consolidarse como un framework robusto, su esencia permanece ligada a la soberanía del desarrollador. A diferencia de los frameworks de consumo masivo que "poseen" la aplicación y dictan su forma, Scoop actúa como un <b>Bootstrap de arquitectura</b>. Proporcionamos el orden y la potencia de un motor profesional, pero garantizamos que la arquitectura y el dominio sigan perteneciendo enteramente al programador.</p>

<p>En lugar de basarse en la "magia negra" o en abstracciones opacas, Scoop se rige por la <b>Arquitectura Explícita</b>. El motor elimina la fricción en la implementación de patrones de alto nivel (Hexagonal, DDD) mediante tres pilares técnicos innegociables:</p>

<ul>
    <li><b>Imperturbabilidad del Dominio:</b> Las entidades y la lógica de negocio permanecen ciegas a la tecnología del motor. Tu código de negocio es inmortal.</li>
    <li><b>Rendimiento Predictivo:</b> Implementación de estructuras de alta eficiencia (Radix Trees, DI compilado) para asegurar que la complejidad arquitectónica no penalice el tiempo de ejecución.</li>
    <li><b>Integridad Atómica:</b> El dato nace válido o no entra al sistema. La validación y la hidratación de DTOs son una frontera de seguridad única y fail-fast.</li>
</ul>

<p>Scoop es <b>minimalista por rigor</b>. No busca simplificar el desarrollo ocultando la complejidad bajo capas de azúcar sintáctico, sino gestionarla con herramientas de precisión que respetan tanto el tiempo del procesador como la claridad del código fuente.</p>

<h2>
    <a href="#good-practices">Buenas prácticas</a>
    <span class="anchor" id="good-practices">...</span>
</h2>

<p>Scoop apuesta por la <b>libertad responsable</b>. El motor es flexible y no impone restricciones arbitrarias que entorpezcan el flujo creativo, otorgando al desarrollador un control total sobre el sistema. Sin embargo, esta flexibilidad permite en ocasiones el uso de prácticas desaconsejables en favor de una rapidez momentánea.</p>

<p>Consideramos que la mejor forma de aprender arquitectura es mediante el contraste. Por ello, a lo largo de esta guía, identificaremos explícitamente aquellas implementaciones que, aunque posibles, comprometen el desacoplamiento o la pureza del dominio. <b>La documentación actuará como un mentor:</b> cuando se presente una mala práctica o un atajo técnico peligroso, se advertirá sobre sus consecuencias y se explicará detalladamente la "buena práctica" o el patrón arquitectónico que Scoop recomienda en su lugar.</p>

<h3>Manejo de versiones</h3>

<p>Esta guia esta basada en la versión estable de scoop a la fecha, la cual es la {{#view->getConfig('app.version')}}.
Esto es importante tenerlo en cuenta ya que algún ejemplo inicial puede no funcionar en versiones
anteriores.</p>

<p>Esta guía está basada en la versión estable <b>{{#view->getConfig('app.version')}}</b>. Scoop sigue estrictamente el <a href="http://semver.org/lang/es/" rel="external">versionamiento semántico</a>. Esto garantiza que el núcleo sea predecible y que las actualizaciones de infraestructura no supongan un riesgo para la estabilidad de tu lógica de negocio.</p>

<h2>
    <a href="#requirements">Requisitos y herramientas</a>
    <span class="anchor" id="requirements">...</span>
</h2>

<p>Scoop es un motor agnóstico diseñado para la <b>resiliencia técnica</b>. Su arquitectura permite desplegar aplicaciones en una vasta gama de entornos, desde infraestructuras modernas en la nube hasta servidores legados de alta criticidad.</p>

<p>Aunque el desarrollo base de Scoop se realiza aprovechando las bondades de <b>PHP 8.5</b>, el núcleo mantiene una compatibilidad excepcional hacia atrás hasta <b>PHP 5.4</b>. Esta dualidad permite modernizar sistemas antiguos con patrones de diseño de última generación (DDD, Hexagonal) sin forzar migraciones traumáticas de infraestructura.</p>

<p>Para la persistencia, Scoop utiliza <b>PDO</b> como capa de abstracción nativa, garantizando la homogeneidad en el tratamiento de datos con motores como MySQL, PostgreSQL, SQL Server o cualquier sistema con soporte para drivers estándar.</p>

<p class="doc-alert"><b>Developer Experience (DX):</b> Desde la versión 0.6.1, Scoop incluye un servidor integrado que se levanta automáticamente al iniciar el entorno de desarrollo, eliminando la necesidad de configurar servidores locales externos para pruebas rápidas.</p>

<h3>Servidores Web y Ruteo</h3>

<p>Scoop requiere capacidades de reescritura de URL para habilitar su sistema de <b>File-System Routing</b>. Se cuenta con soporte nativo para Apache (vía <code>.htaccess</code>) y Nginx.</p>

<p>Para Nginx, la siguiente configuración es fundamental para garantizar que el motor gestione correctamente la jerarquía de carpetas y los parámetros dinámicos:</p>

<pre><code class="language-nginx">merge_slashes off;
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
   return 308 $uri/$is_args$args;;
}
location /(app|vendor|scoop|resources)/ {
    deny all;
}
location ~ \.(htaccess|htpasswd|ini|log|bak)$ {
    deny all;
}
</code></pre>

<h3>Infraestructura Aislada: Docker y Dev Containers</h3>
<p>Para garantizar la inmutabilidad del entorno y la paridad entre desarrollo y producción, Scoop recomienda el uso de contenedores:</p>

<ul>
    <li>
        <b>Docker:</b> Facilitamos la gestión de infraestructura mediante archivos de configuración en <code>.devcontainer/etc</code>. La imagen de producción está optimizada sobre <a href="https://dockerfile.readthedocs.io/en/latest/content/DockerImages/dockerfiles/php-apache.html">webdevops/php-apache</a>.
    </li>
    <li>
        <b>Dev Containers:</b> Implementamos la especificación de <a href="https://docs.github.com/en/codespaces/setting-up-your-project-for-codespaces/adding-a-dev-container-configuration/introduction-to-dev-containers">devcontainers</a> para un arranque instantáneo en entornos como GitHub Codespaces, asegurando que cada miembro del equipo trabaje sobre la misma infraestructura.
    </li>
</ul>

<h3>Gestión de Dependencias y Activos</h3>
<p>Scoop se apoya en herramientas estándar de la industria para maximizar la productividad:</p>

<ul>
    <li><b>Composer:</b> Orquestador esencial para la gestión de dependencias PHP y el autoloader de clases.</li>
    <li><b>NodeJS y npm:</b> Motor necesario para el procesamiento de activos del front-end.</li>
    <li><b>Vite (v0.8.0):</b> Scoop integra <b>Vite</b> como automatizador de activos por defecto, proporcionando una experiencia de desarrollo instantánea gracias a su sistema de HMR (Hot Module Replacement), sustituyendo el flujo de trabajo anterior basado en Gulp.</li>
</ul>

<h2>
    <a href="#download">Medios de descarga</a>
    <span class="anchor" id="download">...</span>
</h2>

<p>Puedes integrar Scoop en tu flujo de trabajo a través de distintos canales, dependiendo de la naturaleza de tu proyecto. La versión estable actual es la <b>{{#view->getConfig('app.version')}}</b>.</p>

<ul>
    <li>
        <h3>Composer (Recomendado)</h3>
        <p>Para iniciar un proyecto con la estructura de directorios estándar y todas las dependencias configuradas, utiliza el comando <code>create-project</code> desde tu terminal:</p>
        <pre><code class="language-shell">composer create-project mirdware/scoop nombre-del-proyecto -s dev</code></pre>
    </li>
    <li>
        <h3>GitHub</h3>
        <p>Si deseas colaborar con el núcleo del motor o prefieres una clonación directa del repositorio para un control granular de las ramas, puedes usar Git:</p>
        <pre><code class="language-shell">git clone https://github.com/mirdware/scoop.git</code></pre>
    </li>
    <li>
        <h3>Manual</h3>
        <p>Como alternativa rápida, puedes descargar el código fuente en un <a href="https://github.com/mirdware/scoop/archive/master.zip">archivo comprimido (.zip)</a>, extraerlo y renombrar el directorio raíz de acuerdo a tu proyecto.</p>
    </li>
</ul>

<h2>
    <a href="#prev-run">Antes de empezar</a>
    <span class="anchor" id="prev-run">...</span>
</h2>

<p>Una vez descargado el motor, es necesario inicializar los ecosistemas de PHP y JavaScript. Scoop está diseñado para ser productivo desde el primer comando.</p>

<h3>Inicialización del entorno</h3>
<p>Si no utilizas <b>Dev Containers</b>, ejecuta la siguiente secuencia en la raíz de tu proyecto para instalar dependencias y levantar el servidor de desarrollo:</p>

<pre><code class="language-shell">npm install && composer install && npm run dev</code></pre>

<p>Este comando realiza tres acciones críticas:</p>
<ol>
    <li><b>Gestión de paquetes:</b> Instala las librerías necesarias para el motor de vistas (Vite) y el núcleo (Composer).</li>
    <li><b>Hot Reload:</b> Levanta un servidor de desarrollo con refresco instantáneo que apunta por defecto al servidor integrado de PHP.</li>
    <li><b>Optimización inicial:</b> Prepara los assets para ser servidos de forma eficiente.</li>
</ol>

<p class="doc-alert"><b>Configuración de Red:</b> Si necesitas apuntar el proxy de desarrollo a un host diferente, puedes configurar la variable de entorno <code>PHP_HOST</code> en tu sistema.</p>

<h3>Preparación para Producción</h3>
<p>Cuando el sistema esté listo para ser desplegado, utiliza el modo de producción para garantizar que Scoop aplique todas las optimizaciones de rendimiento (minificación de assets y optimización de autoloader):</p>

<pre><code class="language-shell">npm install && composer install --optimize-autoloader --no-dev && npm start</code></pre>

<p>Para verificar que la instalación es correcta, accede a <a href="http://localhost:8000">http://localhost:8000</a>. Deberías ver la pantalla de bienvenida del motor, confirmando que tu estructura está lista para la fase de arquitectura.</p>

<h2>
    <a href="#roadmap">Roadmap de evolución</a>
    <span class="anchor" id="roadmap">...</span>
</h2>

<p>Scoop es un motor en constante refinamiento. Nuestra hoja de ruta no solo busca añadir funcionalidades, sino alcanzar la perfección técnica mediante el uso de estructuras de datos avanzadas y la implementación de patrones de aislamiento modular de alto nivel.</p>

<p>
    <pre class="mermaid" style="text-align:center">
graph TD
    %% Nodos de entrada
    R1[Request 1]
    R2[Request 2]
    R3[Request 3]

    subgraph WP ["Single PHP Process (Worker Mode)"]
        direction LR

        subgraph CA ["Context A: Billing"]
            direction TB
            I1[Injector A] --- C1[Config A]
        end

        subgraph CB ["Context B: Inventory"]
            direction TB
            I2[Injector B] --- C2[Config B]
        end

        subgraph CC ["Context C: Auth"]
            direction TB
            I3[Injector C] --- C3[Config C]
        end
    end

    %% Flujo de peticiones
    R1 --> I1
    R2 --> I2
    R3 --> I3

    %% Estilos para Atom One Dark
    style WP fill:#21252b,stroke:#5c6370,stroke-width:2px,color:#abb2bf

    style CA fill:#282c34,stroke:#61afef,color:#abb2bf
    style CB fill:#282c34,stroke:#98c379,color:#abb2bf
    style CC fill:#282c34,stroke:#d19a66,color:#abb2bf

    style R1 fill:#282c34,stroke:#abb2bf,color:#abb2bf
    style R2 fill:#282c34,stroke:#abb2bf,color:#abb2bf
    style R3 fill:#282c34,stroke:#abb2bf,color:#abb2bf

    style I1 fill:#3e4452,stroke:#61afef,color:#61afef
    style I2 fill:#3e4452,stroke:#98c379,color:#98c379
    style I3 fill:#3e4452,stroke:#d19a66,color:#d19a66
    </pre>
</p>

<div class="roadmap-box">
    <h3>v0.8.X: La Purificación del Hot Path</h3>
    <p>Esta fase se centra en eliminar la fricción del intérprete de PHP y maximizar el rendimiento del núcleo en entornos de ejecución tradicionales (FPM):</p>
    <ul>
        <li><b>Ruteo Algorítmico:</b> Implementación de <i>Radix Tree</i> para garantizar búsquedas de rutas en tiempo constante $O(L)$, eliminando la dependencia de expresiones regulares lineales.</li>
        <li><b>Hidratación de Alto Rendimiento:</b> Sustituir la API de <code>Reflection</code> por <code>\Closure::bind</code> para acceder a propiedades privadas de las entidades a velocidad de memoria nativa.</li>
        <li><b>Inyección Pre-compilada:</b> Generar mapas de dependencias estáticos dentro del IoC para eliminar el peaje del análisis de constructores en cada petición.</li>
        <li><b>Escaneo de Bajo Impacto:</b> Implementar lectura por tokens parciales (stream-based) en el descubrimiento de tipos para minimizar la huella de memoria en proyectos de gran escala.</li>
        <li><b>Compliance:</b> Evolución de <code>\Scoop\Http\Message</code> para una alineación total con las especificaciones RFC y los contratos semánticos de PSR.</li>
    </ul>

    <h3>v0.9: Orquestación de Dominios Aislados</h3>
    <p>La versión 0.9 transformará a Scoop en un gestor de Monolitos Modulares, optimizado para la nueva generación de servidores persistentes:</p>
    <ul>
        <li><b>Infraestructura Persistente:</b> Soporte nativo para <a href="https://frankenphp.dev/" target="_blank">FrankenPHP</a> (Worker Mode), permitiendo que el motor permanezca en memoria y elimine los tiempos de arranque.</li>
        <li><b>Aislamiento Físico:</b> Implementación de múltiples <b>Bounded Contexts</b> mediante instancias de <code>Context</code> independientes, garantizando que el acoplamiento entre módulos sea técnicamente imposible.</li>
        <li><b>Context Mapping:</b> Creación del sistema de <i>Bridges</i> para orquestar la comunicación y traducción de datos entre contextos de forma segura.</li>
        <li><b>Interceptor Bus:</b> Inclusión de middlewares dentro de los listeners del Event Bus, habilitando la <b>Programación Orientada a Aspectos (AOP)</b> en el flujo de eventos.</li>
        <li><b>Gestión de Ámbitos (Scopes):</b> Transición hacia definiciones de <i>providers</i> por array. Esto permite definir el ciclo de vida del objeto (<code>singleton</code>, <code>request</code>, <code>prototype</code>), una característica vital para entornos persistentes.
            <pre><code class="language-php">[
    'service' => 'App\Infraestructure\Repository\Factory\PostgresUser',
    'method' => 'create',
    'scope' => 'singleton'
]</code></pre>
        </li>
        <li><b>Interoperabilidad Estándar:</b> Implementación completa de <b>PSR-7</b> (Response) y <b>PSR-17</b> (HTTP Factories).</li>
        <li><s><b>PSR-18:</b> Implementación de cliente HTTP estándar.</s></li>
    </ul>
</div>

<p>Si deseas contribuir al desarrollo de Scoop, puedes realizar un <i>pull request</i> o reportar sugerencias en nuestro repositorio oficial de <a href="https://github.com/mirdware/scoop" target="_blank">GitHub</a>.</p>