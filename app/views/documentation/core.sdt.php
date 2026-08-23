<p>El núcleo de Scoop está diseñado bajo el paradigma de <b>Arquitectura Explícita</b>. El objetivo es minimizar las decisiones arbitrarias mediante convenciones sólidas, manteniendo una flexibilidad absoluta donde la infraestructura nunca condiciona al dominio.</p>

<p><ul>
    <li><a href="#context">Contexto y entorno</a></li>
    <li><a href="#config">Gestión de la configuración</a></li>
    <li><a href="#injector">Inversión de control (IoC)</a></li>
    <li><a href="#extend">Extensibilidad del Núcleo</a></li>
    <li><a href="#lifecycle">Ciclo de vida de una petición</a></li>
</ul></p>

<h2>
    <a href="#context">Contexto y entorno</a>
    <span class="anchor" id="context">...</span>
</h2>

<p>La clase <code>\Scoop\Context</code> es el punto de ignición del motor. No es un simple contenedor de configuración; es el orquestador que inicializa la <b>"burbuja" de ejecución inmutable</b>, encargándose de levantar el cargador de namespaces, el entorno y el Inyector de dependencias.</p>

<pre><code class="language-php">\Scoop\Context::load('app/config', [
    'storage' => 'app/storage',
    'stateless' => true
]);
$app = new \Scoop\Bootstrap\Application();
$app->run();
</code></pre>

<p>Una vez establecido el contexto enviando como parámetros la ubicación del archivo de configuración y un array opcional con la ubicación del storage y si la aplicación se ejecuta en modo stateless <i>(beta)</i>, el sistema permite acceder a los componentes del núcleo de forma desacoplada:</p>

<p>
    <ul>
        <li><b>inject:</b> Inyección de dependencias mediante service locator <code>Context::inject($provider)</code>.</li>
        <li><b>Persistencia:</b> Gestión de conexiones y desconexiones mediante <code>Context::connect($bundle)</code>.</li>
        <li><b>Resolución:</b> El método <code>Context::inject($id)</code> es la puerta de entrada para obtener cualquier servicio gestionado por el inyector.</li>
    </ul>
</p>

<pre><code class="language-php">$service = \Scoop\Context::inject(MyServiceInterface::class);
$db = \Scoop\Context::connect('default');
$environment = \Scoop\Context::inject('\Scoop\Bootstrap\Environment');
</code></pre>

<p class="doc-alert"><b>Evolución DX:</b> Desde la versión 0.6.4, se prioriza el uso de <code>Context::inject($id)</code>, eliminando la necesidad de interactuar directamente con las instancias internas del inyector.</p>

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

<p>La clase <code>Environment</code> implementa un sistema de <b>Resolución Diferida</b>. Para garantizar una huella de memoria mínima, Scoop no carga la configuración de forma masiva; en su lugar, utiliza <b>Lazy Loaders</b> que resuelven "intenciones" mediante prefijos en el momento exacto en que se solicitan.</p>

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

<p class="doc-alert"><b>Optimización O(1):</b> El loader <code>typeof:</code> no escanea el disco en cada petición. En producción consulta un índice de tipos pre-calculado por <code>ice</code>, permitiendo el autodescubrimiento de servicios sin peaje de rendimiento.</p>

<h2>
    <a href="#injector">Inversión de control (IoC)</a>
    <span class="anchor" id="injector">...</span>
</h2>

<p>La Inversión de Control es el mecanismo que permite a Scoop gestionar la construcción de objetos. El <code>Injector</code> no es un almacén pasivo, sino un motor de <b>resolución recursiva</b>: consulta qué necesita una clase en el mapa de dependencias y fabrica automáticamente todo su grafo.</p>

<h3>Inyección Pre-compilada</h3>

<p>Scoop analiza los constructores durante el proceso de escaneo y genera mapas PHP optimizados para Opcache. Esto retira del <i>Hot Path</i> el análisis repetitivo de sus parámetros sin perder la construcción diferida: una clase solo se instancia cuando se solicita al Injector.</p>

<pre><code class="language-shell">php app/ice scan source</code></pre>

<p>El Injector utiliza las definiciones precompiladas cuando están disponibles. Si una clase todavía no figura en los mapas, obtiene sus dependencias mediante Reflection y conserva la definición durante la ejecución actual. Este <i>fallback</i> no solo permite trabajar con artefactos incompletos: también hace posible iniciar comandos como <code>ice scan routes</code> antes de que existan todos los mapas necesarios para resolver sus propias dependencias.</p>

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

<pre class="mermaid" style="text-align:center">
flowchart LR
    subgraph SOURCES ["Fuentes de resolución"]
        direction TB
        GET[Context::inject<br/>Normalizar provider]
        CACHE[ice scan source<br/>Mapa de dependencias precompilado]
        REFLECTION[Reflection fallback]
        GET --> CACHE
        CACHE -. Si no existe en el mapa .-> REFLECTION
    end

    CACHE --> GRAPH[Resolver grafo de dependencias]
    REFLECTION --> GRAPH
    GRAPH --> SCOPE{Scope}

    SCOPE -->|request| REQUEST[Cache de la petición]
    SCOPE -->|singleton| SINGLETON[Cache singleton]
    SCOPE -->|prototype| PROTOTYPE[Nueva instancia]

    REQUEST --> RESULT[Servicio resuelto]
    SINGLETON --> RESULT
    PROTOTYPE --> RESULT

    CLEAN[Injector::clean] -. limpia .-> REQUEST
    CLEAN -. conserva .-> SINGLETON

    style SCOPE fill:#282c34,stroke:#d19a66,color:#abb2bf
    style SOURCES fill:#21252b,stroke:#5c6370,color:#abb2bf
    style CACHE fill:#3e4452,stroke:#98c379,color:#98c379
    style REFLECTION fill:#3e4452,stroke:#e5c07b,color:#e5c07b
    style REQUEST fill:#282c34,stroke:#61afef,color:#abb2bf
    style SINGLETON fill:#282c34,stroke:#98c379,color:#abb2bf
    style PROTOTYPE fill:#282c34,stroke:#c678dd,color:#abb2bf
</pre>

<p>Las definiciones abreviadas mediante nombre de clase o notación <code>Clase:Método</code> continúan disponibles y utilizan el ámbito <code>request</code>.</p>

<h3>Factorías inteligentes</h3>

<p>Cuando un objeto requiere una lógica de construcción que el autowiring no puede deducir (como inyectar <i>strings</i> de configuración o parámetros primitivos), Scoop emplea <b>Factorías</b> mediante el sufijo <code>:</code>.</p>

<p>El Inyector instancia primero la clase factoría (resolviendo sus propias dependencias por constructor) y posteriormente invoca el método indicado (<code>create</code>). Esto garantiza que el Dominio permanezca "puro" y libre de lógica de configuración.</p>

<pre><code class="language-php">class LogFactory {

    public function __construct(private Environment $env) {}

    public function create(): Logger {
        $logConfig = $this->env->getConfig('log', []);
        $handler = new \Scoop\Log\Factory\Handler($logConfig);
        return new \Scoop\Log\Logger($handler);
    }
}
</code></pre>

<p class="doc-danger"><b>Nota de rigor:</b> El antiguo método manual <code>$injector->bind()</code> ha sido deprecado en favor de esta configuración declarativa por archivos. Esto permite que Scoop sea más predecible y habilita optimizaciones de caché en producción.</p>

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
        date_default_timezone_set($this->environment->getConfig('timezone'));
        \ThirdParty\Library::init($this->environment->getConfig('api_key'));
    }
}
</code></pre>

<p>Registro de dependencia.</p>

<pre><code class="language-php">[
    \Scoop\Bootstrap\Configuration::class => \App\Infrastructure\Boot\CustomConfiguration::class
]
</code></pre>

<p class="doc-alert"><b>Poder Arquitectónico:</b> Esto permite que Scoop se adapte a cualquier necesidad de infraestructura (inicialización de sesiones personalizadas, auditoría de conexiones, etc.) manteniendo el punto de entrada <code>index.php</code> limpio e inmutable.</p>

<h3>Custom Loaders</h3>

<p>Puedes registrar tus propios prefijos de carga dinámica en el archivo de configuración. Esto permite que el <code>Environment</code> entienda nuevos protocolos de configuración.</p>

<pre><code class="language-php">'loaders' => [
    'yaml' => \App\Infrastructure\Loader\YamlLoader::class
]
</code></pre>

<h2>
    <a href="#lifecycle">Ciclo de vida de una petición</a>
    <span class="anchor" id="lifecycle">...</span>
</h2>

<p>La clase <code>Application</code> orquesta la petición a través de un <i>pipeline</i> diseñado para la eficiencia en entornos clásicos (Apache/Nginx + FPM) y preparado estructuralmente para futuras implementaciones en entornos persistentes.</p>

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

<h3>Fases del Ciclo de Vida:</h3>

<p><ol>
    <li><b>Context & Environment:</b> Captura del entorno global y encapsulamiento en un objeto inmutable <code>ServerRequest</code> (PSR-7), asegurando un estado inicial determinista.</li>
    <li><b>Routing:</b> Localización del <i>endpoint</i> y su jerarquía de middlewares. En producción, utiliza un <b>mapa pre-compilado</b> que garantiza una resolución O(S) evitando escanear el sistema de archivos y puede ser servido eficientemente mediante OPcache.</li>
    <li><b>Atomic Dispatching (Control Hand-off):</b> El Inyector resuelve el grafo de dependencias e instancia el controlador. Aquí, el motor <b>cede el control al desarrollador</b>: se ejecuta la lógica de negocio (Controlador/Casos de Uso) tras procesar la cadena de middlewares.</li>
    <li><b>Response Transformation:</b> El motor recupera el control para normalizar el retorno del desarrollador (Array, Vista o Escalar) en una respuesta PSR-7 inmutable.</li>
    <li><b>Resource Cleanup:</b> Volcamiento del stream al buffer de salida e invocación de <code>gc_collect_cycles()</code> para liberar el grafo de objetos y cerrar conexiones antes de que el servidor entregue la respuesta final.</li>
</ol></p>

<style>
    .mermaid .messageText, .mermaid .loopText { fill:#3e4452 !important; }
    .mermaid .labelText { fill:#f1f3f5 !important; }
</style>
<p><pre class="mermaid" style="text-align:center">
%%{init: {"themeVariables": {"signalTextColor": "#282c34", "labelTextColor": "#282c34", "loopTextColor": "#282c34", "actorTextColor": "#f1f3f5", "actorLineColor": "#5c6370", "signalColor": "#5c6370"}}}%%
sequenceDiagram
    participant Client
    participant Application
    participant Router
    participant Pipeline as Middleware Pipeline
    participant Controller
    participant UseCase as Use Case
    participant Domain

    Client->>Application: Request
    Application->>Application: Context & Environment
    Application->>Router: route(request)

    alt Ejecución correcta
        Router->>Pipeline: Controller + middlewares
        Pipeline->>Controller: Request
        Controller->>UseCase: Execute
        UseCase->>Domain: Business operation
        Domain-->>UseCase: Result
        UseCase-->>Controller: Result
        Controller-->>Pipeline: Array / View / Scalar / Response
        Pipeline-->>Application: Result
        Application->>Application: Response Transformer
    else Excepción en routing, middleware o controller
        Router--xApplication: Throwable
        Application->>Application: Http Error Mapper
    end

    Application->>Application: Resource Cleanup
    Application-->>Client: Response
</pre></p>

<p class="doc-alert"><b>Mantenibilidad:</b> Cualquier excepción lanzada en el dominio es interceptada por <code>Http\Error\Mapper</code>, que decide la respuesta adecuada basada en tu configuración de <code>http.errors</code>.</p>
