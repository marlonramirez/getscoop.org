<p>Esta capa actúa como la frontera de entrada a la aplicación. Scoop separa la definición física de los puntos de acceso (endpoints) de la lógica de negocio, utilizando el sistema de archivos como el orquestador principal de la infraestructura web.</p>

<ul>
    <li><a href="#routing">app/routes</a></li>
    <li><a href="#params">Parámetros dinámicos</a></li>
    <li><a href="#security">Seguridad en la Entrada: Sanitización Automática</a></li>
    <li><a href="#endpoint">Definición de Endpoints</a></li>
    <li><a href="#middlewares">Jerarquía de Middlewares</a></li>
    <li><a href="#cors">Políticas de Red: CORS</a></li>
</ul>

<h2>
    <a href="#routing">app/routes</a>
    <span class="anchor" id="routing">...</span>
</h2>

<p>Desde la versión 0.8, Scoop implementa un ruteador inspirado en paradigmas modernos (como Next.js) donde el sistema de archivos es la fuente de la verdad. La jerarquía de directorios dentro de <code>app/routes</code> define automáticamente la topología de la aplicación.</p>

<p>Para que un directorio sea reconocido como una ruta válida, debe contener un archivo <code>endpoint.php</code>.</p>

<pre><code class="language-shell">app/routes/
├─ auth/
| └─ login/
| └─ endpoint.php
└─ endpoint.php
</code></pre>

<p>El path por defecto <code>app/routes</code> puede ser modificado desde la configuración general mediante la clave <b>routes</b>.</p>

<h2>
    <a href="#params">Parámetros dinámicos</a>
    <span class="anchor" id="params">...</span>
</h2>

<p>Los segmentos variables se definen nombrando directorios entre corchetes: <code>[param]</code>. El motor extrae automáticamente estos valores de la URI, aplicando un <code>urldecode</code>, y los pone a disposición del controlador o DTO.</p>

<pre><code class="language-shell">app/routes/
└─ blog/
└─ [slug]/
└─ endpoint.php
</code></pre>

<p>En este ejemplo, el valor "mi-primer-post" será capturado bajo la clave <code>slug</code>.</p>

<h3>Validación de parámetros de ruta</h3>

<p>A diferencia de la validación de negocio, el ruteador permite definir un <b>Validator</b> de infraestructura para asegurar que los parámetros de la URI cumplan con un contrato antes de disparar el controlador.</p>

<pre><code class="language-php">return [
    'controller' => Controller\User::class,
    'validator' => Validator\NumericIdValidator::class
];
</code></pre>

<p class="doc-alert"><b>Comportamiento Fail-fast:</b> Si el validador de la ruta falla, el motor aborta la petición y lanza una excepción <code>NotFound</code> (404), garantizando que el sistema nunca procese identificadores con formato inválido.</p>

<h2>
    <a href="#security">Seguridad en la Entrada: Sanitización Automática</a>
    <span class="anchor" id="security">...</span>
</h2>

<p>El objeto <code>Request</code> de Scoop no es solo un contenedor de datos; implementa una capa de <b>Defensa en Profundidad</b>. Por defecto, el motor aplica una sanitización agresiva a todos los strings provenientes de <code>$_GET</code>, <code>$_POST</code> y la URI.</p>

<ul>
    <li><b>Protección XSS:</b> El motor limpia etiquetas peligrosas (<code>&lt;script&gt;</code>, <code>&lt;object&gt;</code>, <code>&lt;applet&gt;</code>) y atributos de eventos (<code>onmouseover</code>, <code>onclick</code>).</li>
    <li><b>Normalización:</b> Decodifica entidades HTML y elimina caracteres nulos o invisibles que puedan evadir filtros de seguridad.</li>
</ul>

<p class="doc-alert"><b>Filosofía Purista:</b> Aunque el motor limpia los datos de infraestructura, Scoop recomienda que el Dominio siempre valide sus propios <i>Invariantes</i>. La sanitización de Scoop es una barrera de seguridad técnica, no una sustitución de la lógica de negocio.</p>

<h2>
    <a href="#endpoint">Definición de endpoints</a>
    <span class="anchor" id="endpoint">...</span>
</h2>

<p>El archivo <code>endpoint.php</code> es la unidad mínima de definición de una ruta. Debe retornar un array asociativo con las siguientes propiedades:</p>

<p>
    <ul>
        <li><b><code>id</code>:</b> (Opcional) Identificador único de la ruta. Es vital para generar URLs dinámicas mediante <code>view->route('id')</code> sin acoplarse al path físico.</li>
        <li><b><code>controller</code>:</b> Clase encargada de procesar la petición. Puede ser una sola clase (recurso REST) o un array mapeado por métodos HTTP (get, post, etc.).</li>
        <li><b><code>validator</code>:</b> (Opcional) Clase que hereda de <code>\Scoop\Validator</code> para validar los parámetros <code>[param]</code> de la URI.</li>
        <li><b><code>middlewares</code>:</b> (Opcional) Un array indexado de clases que actúan como filtros específicos para este endpoint, ejecutándose después de los middlewares de carpeta.</li>
    </ul>
</p>

<pre><code class="language-php">return [
    'id' => 'user.update',
    'controller' => Controller\UserUpdate::class,
    'validator' => Validator\IdValidator::class,
    'middlewares' => [
        Middleware\EnsureUserCanWrite::class
    ]
];
</code></pre>

<h3>Controladores por método (SRP)</h3>

<p>Para aplicaciones con lógica compleja, Scoop permite segmentar los controladores según el método HTTP, facilitando el cumplimiento del Principio de Responsabilidad Única:</p>

<pre><code class="language-php">return [
    'controller' => [
        'get' => \App\Infrastructure\Controller\User\Read::class,
        'post' => \App\Infrastructure\Controller\User\Create::class
    ]
];
</code></pre>

<p class="doc-alert"><b>Nota técnica:</b> Si el controlador es una clase invocable (implementa <code>__invoke</code>), el motor lo ejecutará directamente. De lo contrario, buscará un método con el mismo nombre que el verbo HTTP (get, post, etc.).</p>

<h2>
    <a href="#middlewares">Jerarquía de middlewares</a>
    <span class="anchor" id="middlewares">...</span>
</h2>

<p>La seguridad y la interceptación jerárquica se gestionan mediante archivos <code>middlewares.php</code>. A diferencia de los endpoints, este archivo debe retornar un <b>array indexado simple</b> (lista de clases) que implementen <b>PSR-15</b>.</p>

<pre><code class="language-php">return [
    Middleware\Session::class,
    Middleware\AdminGuard::class
];
</code></pre>

<h3>Herencia Acumulativa</h3>

<p>Los middlewares en Scoop son <b>aditivos</b>. El motor construye un <i>pipeline</i> acumulando todos los middlewares encontrados desde el directorio raíz hasta el endpoint final. Esto permite establecer capas de seguridad zonales:</p>

<ol>
    <li>Middlewares en <code>app/routes/middlewares.php</code> (Globales).</li>
    <li>Middlewares en <code>app/routes/admin/middlewares.php</code> (Zonales).</li>
    <li>Middlewares definidos dentro del <code>endpoint.php</code> (Específicos).</li>
</ol>

<h2>
    <a href="#cors">Políticas de Red: CORS</a>
    <span class="anchor" id="cors">...</span>
</h2>

<p>La gestión de orígenes cruzados se define en la configuración central, permitiendo que <code>Application.php</code> maneje de forma transparente el pre-vuelo (peticiones <code>OPTIONS</code>) y las cabeceras de seguridad necesarias para clientes modernos.</p>

<pre><code class="language-php">'cors' => [
    'origin' => 'https://dashboard.miapp.com',
    'methods' => 'POST, GET, OPTIONS',
    'headers' => 'Authorization, Content-Type'
]
</code></pre>

<p class="doc-alert"><b>Optimización de Producción:</b> Aunque en desarrollo Scoop escanea el sistema de archivos en cada petición, en producción se debe ejecutar el comando <code>app/ice cache routes</code>. Esto genera un mapa de rutas inmutable en memoria, eliminando el costo de I/O y garantizando un rendimiento superior.</p>
