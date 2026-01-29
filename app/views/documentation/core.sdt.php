<p>El núcleo de Scoop está diseñado bajo el paradigma de <b>Arquitectura Explícita</b>. El objetivo es minimizar las decisiones arbitrarias mediante convenciones sólidas, manteniendo una flexibilidad absoluta donde la infraestructura nunca condiciona al dominio.</p>

<ul>
    <li><a href="#context">Contexto y entorno</a></li>
    <li><a href="#config">Gestión de la configuración</a></li>
    <li><a href="#injector">Inversión de control (IoC)</a></li>
    <li><a href="#extend">Extensibilidad del Núcleo</a></li>
    <li><a href="#lifecycle">Ciclo de vida de una petición</a></li>
</ul>

<h2>
    <a href="#context">Contexto y entorno</a>
    <span class="anchor" id="context">...</span>
</h2>

<p>La clase <code>\Scoop\Context</code> es el punto de ignición del motor. No es un simple contenedor de configuración; es el orquestador que inicializa la <b>"burbuja" de ejecución inmutable</b>, encargándose de levantar el cargador de namespaces, el entorno y el Inyector de dependencias.</p>

<pre><code class="language-php">\Scoop\Context::load('app/config');
$app = new \Scoop\Bootstrap\Application();
echo $app->run();
</code></pre>

<p>Una vez establecido el contexto, el sistema permite acceder a los componentes del núcleo de forma desacoplada:</p>

<p>
    <ul>
        <li><b>Entorno:</b> Acceso a la configuración mediante <code>Context::getEnvironment()</code>.</li>
        <li><b>Persistencia:</b> Gestión de conexiones y desconexiones mediante <code>Context::connect($bundle)</code>.</li>
        <li><b>Resolución:</b> El método <code>Context::inject($id)</code> es la puerta de entrada para obtener cualquier servicio gestionado por el inyector.</li>
    </ul>
</p>

<pre><code class="language-php">$service = \Scoop\Context::inject(MyServiceInterface::class);
$db = \Scoop\Context::connect('default');
$environment = \Scoop\Context::getEnvironment();
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
        <li><b><code>instanceof:</code></b> Es el cargador más avanzado. Recupera todas las clases que implementan un contrato específico consultando un <b>índice de abstracciones pre-calculado</b>, logrando una resolución $O(1)$ en entornos de producción.</li>
    </ul>
</p>

<pre><code class="language-php">return [
    'app' => 'json:package',
    'messages' => [
        'es' => 'import:app/config/lang/es',
        'en' => 'import:app/config/lang/en'
    ],
    'commands' => 'instanceof:App\Command\Command'
];
</code></pre>

<p class="doc-alert"><b>Optimización O(1):</b> El loader <code>instanceof:</code> no escanea el disco en cada petición. Consulta un índice de tipos pre-calculado por <code>ice</code>, permitiendo el autodescubrimiento de servicios sin peaje de rendimiento.</p>

<h2>
    <a href="#injector">Inversión de control (IoC)</a>
    <span class="anchor" id="injector">...</span>
</h2>

<p>La Inversión de Control es el mecanismo que permite a Scoop gestionar la construcción de objetos. El <code>Injector</code> no es un almacén pasivo, sino un motor de <b>resolución recursiva</b>: analiza qué necesita una clase para nacer y fabrica automáticamente todo su grafo de dependencias.</p>

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

<ol>
    <li><b>Request Discovery:</b> Normaliza el entorno global en un objeto inmutable de Petición.</li>
    <li><b>Hierarchical Routing:</b> Localiza el código mediante el sistema de archivos (jerarquía de middlewares).</li>
    <li><b>Recursive Dispatching:</b> El Inyector construye el grafo completo (Controlador + Casos de Uso).</li>
    <li><b>Response Formatting:</b> Transforma retornos (Arrays, Vistas o Objetos) en HTTP nativo con gestión automática de <b>CORS</b>.</li>
    <li><b>Garbage Collection:</b> Invoca <code>gc_collect_cycles()</code>, garantizando la liberación inmediata de memoria antes de finalizar el ciclo.</li>
</ol>

<p class="doc-alert"><b>Mantenibilidad:</b> Cualquier excepción lanzada en el dominio es interceptada por el <code>ExceptionManager</code>, que decide la respuesta adecuada basada en tu configuración de <code>http.errors</code>.</p>
