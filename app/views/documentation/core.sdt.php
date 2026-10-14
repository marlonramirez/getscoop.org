<p>El núcleo de Scoop está diseñado bajo el paradigma de <b>Arquitectura Explícita</b>. El objetivo es minimizar las decisiones arbitrarias mediante convenciones sólidas, manteniendo una flexibilidad absoluta donde la infraestructura nunca condiciona al dominio.</p>

<p><ul>
    <li><a href="#context">Contexto y entorno</a></li>
    <li><a href="#config">Gestión de la configuración</a></li>
    <li><a href="#injector">Inversión de control (IoC)</a></li>
    <li><a href="#extend">Extensibilidad del Núcleo</a></li>
    <li><a href="#lifecycle">Ciclo de vida</a></li>
</ul></p>

<h2>
    <a href="#context">Contexto y entorno</a>
    <span class="anchor" id="context">...</span>
</h2>

<p class="alert">La clase <code>\Scoop\Bootstrap\Application</code> administra el arranque completo desde la v.0.8.5. <code>\Scoop\Bootstrap\Environment</code> está deprecado desde v0.8.6 en favor de Context; su implementación permanece como base de compatibilidad. La API estática de Context está deprecada desde v0.8.5. Para código nuevo, utiliza la instancia de Application en el punto de entrada y la inyección por constructor en los servicios.</p>

<pre><code class="language-php">require 'scoop/Bootstrap/Application.php';
$app = new \Scoop\Bootstrap\Application();
$app->run();
</code></pre>

<p>Sin argumentos, Application utiliza <code>app/config.php</code> y <code>app/storage</code>. Para cambiar estas ubicaciones, recibe la ruta, sin extensión, de un archivo PHP que retorna las opciones de arranque:</p>

<pre><code class="language-php">return [
    'config' => 'app/config',
    'storage' => 'app/storage',
    'stateless' => true,
];
</code></pre>

<p>La ruta del archivo se pasa al constructor de Application:</p>

<pre><code class="language-php">
require 'scoop/Bootstrap/Application.php';
$app = new \Scoop\Bootstrap\Application('app/runtime');
$app->run();
</code></pre>

<p>La configuración de servicios continúa en el archivo indicado por <code>config</code>. Las opciones de arranque y los providers son arrays distintos; el constructor de Application no recibe directamente un array.</p>

<p>
    <ul>
        <li><b>Resolución:</b> <code>$app->inject($id)</code> obtiene un servicio desde el Inyector de esa aplicación.</li>
        <li><b>Contexto:</b> <code>$context->getInjector()</code> permite resolver servicios en factorías y componentes de infraestructura que reciben una instancia de <code>\Scoop\Context</code>.</li>
        <li><b>Persistencia:</b> <code>Connection</code> dispone de una factoría para su resolución como provider; el nuevo formato utiliza las opciones directamente bajo <code>db</code>, sin bundles.</li>
    </ul>
</p>

<pre><code class="language-php">$service = $app->inject(MyServiceInterface::class);
$context = $app->inject('\Scoop\Context');
$injector = $context->getInjector();
</code></pre>

<p><code>stateless</code> vale <code>false</code> por defecto. Configúralo como <code>true</code> para evitar el inicio automático de sesiones; el ejemplo anterior utiliza esa opción.</p>

<p class="doc-alert p-md rounded-lg text-on-surface bg-warning-subtle border-warning"><b>Cambio previsto:</b> En futuras versiones, <code>stateless</code> será reemplazado por <code>stateful</code>, también con valor predeterminado <code>false</code>. La aplicación será stateless por defecto; <code>stateful =&gt; true</code> habilitará el inicio automático de sesiones. La opción de arranque <code>config</code> pasará a llamarse <code>map</code>. Estos cambios aún no se aplican a los ejemplos de la versión actual.</p>

<h2>
    <a href="#config">Gestión de la configuración</a>
    <span class="anchor" id="config">...</span>
</h2>

<p>El archivo de configuración principal (<code>app/config.php</code>) establece los ajustes vitales para el funcionamiento del motor. Para mantener la simplicidad sin abandonar la flexibilidad, Scoop permite segmentar estas definiciones en archivos independientes, utilizando <code>require</code> estándar o la <b>Resolución Diferida</b> (Lazy Loading) que vimos anteriormente.</p>

<pre><code class="language-php">return [
    'providers' => require 'config/providers.php',
    'routes' => 'app/routes',
    'app' => 'json:package'
];
</code></pre>

<h3>Resolución dinámica</h3>

<p>La instancia de <code>Context</code> implementa un sistema de <b>Resolución Diferida</b>. Para garantizar una huella de memoria mínima, Scoop no carga la configuración de forma masiva; en su lugar, utiliza <b>Lazy Loaders</b> que resuelven "intenciones" mediante prefijos en el momento exacto en que se solicitan.</p>

<p>
    <ul>
        <li><b><code>import:</code></b> Carga archivos PHP externos bajo demanda, ideal para segmentar grandes mapas de configuración.</li>
        <li><b><code>json:</code></b> Parsea y cachea archivos JSON (como el <code>package.json</code>) convirtiéndolos en arrays nativos.</li>
        <li><b><code>typeof:</code></b> Mapea las implementaciones de un tipo sin instanciarlas. Retorna un array de nombres de clase que implementan el contrato especificado, permitiendo selección manual o estrategias de resolución condicional.</li>
        <li><b><code>instanceof:</code></b> Instancia las clases halladas mediante <code>typeof</code>.</li>
    </ul>
</p>

<pre><code class="language-php">return [
    'app' => 'json:package',
    'messages' => [
        'es' => 'import:app/config/lang/es',
        'en' => 'import:app/config/lang/en'
    ],
    'ice' => [
        'commands' => 'typeof:App\Command\Handler',
    ],
    'Validators' => 'instanceof:App\Domain\Validator'
];
</code></pre>

<p>Antes de desplegar, ejecuta <code>php app/ice scan source</code> para generar los índices utilizados por <code>typeof:</code> e <code>instanceof:</code>.</p>

<h2>
    <a href="#injector">Inversión de control (IoC)</a>
    <span class="anchor" id="injector">...</span>
</h2>

<p>La Inversión de Control es el mecanismo que permite a Scoop gestionar la construcción de objetos. El <code>Injector</code> no es un almacén pasivo, sino un motor de <b>resolución recursiva</b>: consulta qué necesita una clase en el mapa de dependencias y fabrica automáticamente todo su grafo.</p>

<h3>Inyección Pre-compilada</h3>

<p>Ejecuta el escaneo de código para preparar las dependencias de tus servicios:</p>

<pre><code class="language-shell">php app/ice scan source</code></pre>

<p>Las clases concretas resolubles mediante autowiring no necesitan un provider explícito. Las interfaces requieren un provider que indique su implementación.</p>

<p>Los mapas forman parte de los artefactos del <i>build</i> y deben regenerarse en cada despliegue que incorpore cambios de código. Puede forzarse su reconstrucción mediante <code>php app/ice scan source -f</code>.</p>

<p>El autowiring admite dependencias de clase o interface y respeta los valores predeterminados de los parámetros opcionales. Cuando un constructor exige un valor primitivo o un parámetro sin tipo, su creación debe expresarse mediante una factoría configurada. Las dependencias del constructor de la propia factoría también son resueltas por el Injector.</p>

<h3>Definición de Contratos (Providers)</h3>

<p>Para que el motor sepa cómo traducir una abstracción (interface) en una implementación concreta, es necesario registrar el mapeo en el sistema de configuración. Se recomienda el uso de archivos independientes para separar la infraestructura de la lógica de negocio.</p>

<pre><code class="language-php">return [
    'providers' => require 'config/providers.php'
];
</code></pre>

<p>En el archivo de <i>providers</i>, definimos las reglas de interpretación. De esta manera, cada vez que el sistema encuentre una interface gestionada por el entorno IoC, la traducirá automáticamente a la clase configurada.</p>

<pre><code class="language-php">return [
    'App\Domain\Repository\Quote' => 'App\Infrastructure\Persistence\ArrayQuoteRepository',
    'Scoop\Log\Logger' => 'Scoop\Log\Factory\Logger:create'
];
</code></pre>

<h3>Ámbitos de Vida (Scopes)</h3>

<p>Los providers también admiten una definición por array para controlar el ciclo de vida de cada servicio:</p>

<pre><code class="language-php">return [
    UserRepository::class => [
        'service' => PostgresUserRepositoryFactory::class,
        'method' => 'create',
        'scope' => 'singleton'
    ]
];
</code></pre>

<p><ul>
    <li><b><code>request</code>:</b> ámbito predeterminado; reutiliza la instancia hasta que se limpia el Inyector en cada petición.</li>
    <li><b><code>singleton</code>:</b> conserva la misma instancia dependiendo del ciclo de vida del entorno; en FPM no hay diferencia con request, pero en entornos worker sobrevive entre peticiones.</li>
    <li><b><code>prototype</code>:</b> crea una instancia nueva en cada resolución.</li>
</ul></p>

<p>Las definiciones abreviadas mediante nombre de clase o notación <code>Clase:Método</code> continúan disponibles y utilizan el ámbito <code>request</code>.</p>

<p>El siguiente diagrama resume la resolución de un servicio y la reutilización de instancias según su scope. Los mapas precompilados se utilizan en producción; si no hay una definición disponible, el Inyector obtiene las dependencias mediante Reflection.</p>

<pre class="mermaid" data-diagram-title="Resolución de servicios y scopes" style="text-align:center">
flowchart LR
    GET["Resolver servicio"] --> EXISTS{"¿Instancia?"}
    EXISTS -->|Sí| RESULT["Servicio"]
    EXISTS -->|No| PROVIDER["Provider / clase"]

    subgraph SOURCES ["Fuentes de resolución"]
        direction TB
        MAP["Mapa disponible"]
        REFLECTION["Reflection fallback"]
        MAP -. Sin definición .-> REFLECTION
    end

    PROVIDER --> MAP
    MAP --> CREATE["Construir"]
    REFLECTION --> CREATE

    subgraph SCOPES ["Scopes"]
        direction TB
        REQUEST["request"]
        SINGLETON["singleton"]
        PROTOTYPE["prototype"]
    end

    CREATE --> REQUEST
    CREATE --> SINGLETON
    CREATE --> PROTOTYPE
    REQUEST --> RESULT
    SINGLETON --> RESULT
    PROTOTYPE --> RESULT

    style SOURCES fill:#21252b,stroke:#5c6370,color:#abb2bf
    style SCOPES fill:#21252b,stroke:#5c6370,color:#abb2bf
    style REQUEST fill:#282c34,stroke:#61afef,color:#abb2bf
    style SINGLETON fill:#282c34,stroke:#98c379,color:#abb2bf
    style PROTOTYPE fill:#282c34,stroke:#c678dd,color:#abb2bf
</pre>

<h3>Factorías inteligentes</h3>

<p>Cuando un objeto requiere una lógica de construcción que el autowiring no puede deducir (como inyectar <i>strings</i> de configuración o parámetros primitivos), Scoop emplea <b>Factorías</b> mediante el sufijo <code>:</code>.</p>

<p>El Inyector instancia primero la clase factoría (resolviendo sus propias dependencias por constructor) y posteriormente invoca el método indicado (<code>create</code>). Esto garantiza que el Dominio permanezca "puro" y libre de lógica de configuración.</p>

<pre><code class="language-php">class LogFactory {

    public function __construct(private \Scoop\Context $env) {}

    public function create(): \Scoop\Log\Logger {
        $logConfig = $this->env->getConfig('log', []);
        $handler = new \Scoop\Log\Factory\Handler($this->env->getInjector(), $logConfig);
        return new \Scoop\Log\Logger($handler);
    }
}
</code></pre>

<p class="doc-danger p-md rounded-lg text-on-surface bg-error-subtle border-error"><b>Nota de rigor:</b> El antiguo método manual <code>$injector->bind()</code> ha sido deprecado en favor de esta configuración declarativa por archivos. Esto permite que Scoop sea más predecible y habilita optimizaciones de caché en producción.</p>

<h2>
    <a href="#extend">Extensibilidad del Núcleo</a>
    <span class="anchor" id="extend">...</span>
</h2>

<p>Scoop es un motor diseñado para ser colonizado por el desarrollador. Debido a que el motor utiliza su propio <b>Injector</b> para inicializarse, es posible interceptar y extender el comportamiento del arranque sin modificar el código del core.</p>

<h3>Sobrescritura de la Configuración</h3>

<p>La clase <code>\Scoop\Bootstrap\Configuration</code> es la encargada de ejecutar el <code>setUp()</code> inicial. Puedes crear tu propia lógica de arranque heredando de esta clase y registrándola en tus <i>providers</i>.</p>

<pre><code class="language-php">class CustomConfiguration extends \Scoop\Bootstrap\Configuration {
    public function setUp() {
        parent::setUp();
        date_default_timezone_set($this->context->getConfig('timezone'));
        \ThirdParty\Library::init($this->context->getConfig('api_key'));
    }
}
</code></pre>

<p>Registro de dependencia.</p>

<pre><code class="language-php">[
    \Scoop\Bootstrap\Configuration::class => \App\Infrastructure\Boot\CustomConfiguration::class
]
</code></pre>

<p class="doc-alert p-md rounded-lg text-on-surface bg-warning-subtle border-warning"><b>Poder Arquitectónico:</b> Esto permite que Scoop se adapte a cualquier necesidad de infraestructura (inicialización de sesiones personalizadas, auditoría de conexiones, etc.) manteniendo el punto de entrada <code>index.php</code> limpio e inmutable.</p>

<h3>Custom Loaders</h3>

<p>Puedes registrar tus propios prefijos de carga dinámica en el archivo de configuración. Esto permite que el <code>Context</code> entienda nuevos protocolos de configuración.</p>

<pre><code class="language-php">'loaders' => [
    'yaml' => \App\Infrastructure\Loader\YamlLoader::class
]
</code></pre>

<h2>
    <a href="#lifecycle">Ciclo de vida</a>
    <span class="anchor" id="lifecycle">...</span>
</h2>

<p><code>Application::run()</code> atiende la petición HTTP usando las rutas, middlewares y controladores configurados. Los controladores reciben sus servicios por constructor y los datos de la petición mediante Request:</p>

<pre><code class="language-php">class PostController {

    public function __construct(
        private CreatePostUseCase $useCase,
        private PostValidator $validator
    ) {}

    public function post(Request $request) {
        $dto = $request->get(PostDTO::class)->fromBody($this->validator);
        return $this->useCase->execute($dto);
    }
}
</code></pre>

<p>Un controlador puede devolver una <code>Response</code>, una <code>View</code>, un array o un valor escalar. Los arrays se entregan como JSON, las vistas como HTML y los escalares como texto. Para definir el código de estado y las cabeceras, devuelve una Response:</p>

<pre><code class="language-php">return new \Scoop\Http\Message\Response(
    201,
    ['Content-Type' => 'application/json'],
    json_encode(['id' => $postId])
);
</code></pre>

<p>Después de enviar la respuesta, <code>run()</code> llama a <code>terminate()</code>: cierra la sesión activa, dispara <code>RequestFinished</code>, restablece los servicios de vista, limpia el gestor de entidades, vacía los logs y limpia el scope request del inyector. El gestor de entidades y el logger sólo se resuelven durante el cierre si ya fueron utilizados. Si una excepción no tiene estado HTTP mapeado, la aplicación termina antes de propagarla.</p>

<p>Configura <code>http.errors</code> para asociar tus excepciones con respuestas HTTP. Las excepciones no mapeadas se propagan al llamador. Consulta <a href="{{#view->route('doc', 'ecosystem')}}#exceptions">Excepciones y respuestas de error</a> para definir estados, cabeceras y vistas.</p>
