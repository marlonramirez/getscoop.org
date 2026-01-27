<p>Scoop provee un conjunto de servicios de soporte diseñados para garantizar la robustez del sistema en producción. Desde la gestión inteligente de errores hasta el entorno de consola extensible, estas herramientas permiten que la infraestructura de la aplicación sea tan sólida como su lógica de negocio.</p>

<ul>
    <li><a href="#exceptions">Manejo de Excepciones y Mapeo HTTP</a></li>
    <li><a href="#monitoring">Monitoreo y Logging</a></li>
    <li><a href="#cache">Caché distribuida</a></li>
    <li><a href="#http-client">HTTP Client</a></li>
    <li><a href="#crypt">Vault: Seguridad Criptográfica</a></li>
    <li><a href="#ice">ICE: Interface Command Environment</a></li>
    <li><a href="#i18n">Internacionalización (i18n)</a></li>
</ul>

<h2>
    <a href="#exceptions">Manejo de Excepciones y Mapeo HTTP</a>
    <span class="anchor" id="exceptions">...</span>
</h2>

<p>En Scoop, las excepciones de dominio son ciudadanos de primera clase. El motor permite desacoplar la lógica de error del negocio de la respuesta de infraestructura mediante el <code>ExceptionManager</code>.</p>

<p>Cualquier excepción puede ser mapeada a un código de estado HTTP específico, permitiendo además inyectar cabeceras personalizadas o definir vistas de error dedicadas.</p>

<pre><code class="language-php">'http' => [
    'errors' => [
        401 => [
            'title' => 'User not authorized',
            'headers' => array('WWW-Authenticate' => 'Digest realm="' . $domain . '",qop="auth",nonce="' . uniqid() . '",opaque="' . md5($domain) . '"')
            'exceptions' => [NotAuhorized::class]
        ]
    ]
]
</code></pre>

<p class="doc-alert"><b>Observabilidad:</b> El sistema detecta automáticamente si la petición es Ajax/JSON o una navegación estándar, devolviendo el formato de error adecuado (un Payload JSON o un renderizado SDT) de forma transparente.</p>

<h2>
    <a href='#monitoring'>Monitoreo y Logging</a>
    <span class='anchor' id='monitoring'>...</span>
</h2>

<p>El sistema de log de Scoop implementa el estándar <b>PSR-3</b> y se basa en una arquitectura de <b>Handlers</b>. Esto permite que un mismo mensaje sea procesado por múltiples canales (archivo, Slack, Email) dependiendo de su nivel de severidad.</p>

<pre><code class="language-php">[
    'log' => [
        Level::INFO => [
            File::class => null
        ],
        Level::NOTICE => [
            Slack::class => [
                'url' => 'https://hooks.slack.com/services/T00/B0000/XXXXXXXXXXXXXX',
                'config' => [
                    'blocks': [[
                        'type': 'section',
                        'text': [
                            'type': 'mrkdwn',
                            'text': 'Something has happened:'
                        ]]
                    ]
                ]
            ]
        ],
        Level::WARNING => [
            Email::class => ['email' => ['support@sespesoft.com']]
        ],
        Level::CRITICAL => [
            Email::class => ['email' => ['support@sespesoft.com', 'admin@sespesoft.com']],
            Slack::class => [
                'url' => 'https://hooks.slack.com/services/T00/B0000/XXXXXXXXXXXXXX',
                'config' => ['blocks': [
                    [
                        'type': 'section',
                        'text': [
                            'type': 'mrkdwn',
                            'text': 'Critical error email send with:'
                        ]
                    ]
                ]]
            ],
            File::class => ['file' => '/logs']
        ]
    ]
]
</code></pre>

<p>Cada Handler es resuelto por el <b>Injector</b>, lo que permite que el sistema de log sea totalmente extensible y configurable mediante inyección de dependencias.</p>

<p>El código de <code>scoop/Persistence/DBC.php</code> y <code>scoop/Bootstrap/Application.php</code> dispara eventos como <code>ConnectionOpened</code>, <code>ConnectionClosed</code> y <code>ErrorOccurred</code>.</p>

<h2>
    <a href='#cache'>Caché distribuida</a>
    <span class='anchor' id='cache'>...</span>
</h2>

<p>Scoop implementa un sistema de almacenamiento temporal que cumple simultáneamente con los estándares <b>PSR-6</b> (Cache Item Pool) para una gestión granular y <b>PSR-16</b> (Simple Cache) para operaciones rápidas. Esta dualidad permite que el motor se adapte tanto a necesidades complejas de persistencia diferida como a casos de uso de alto rendimiento.</p>

<h3>Arquitectura de Almacenamiento</h3>

<p>El sistema se basa en un <i>Item Pool</i> extensible. Scoop provee de serie dos <i>drivers</i> de alta eficiencia:</p>

<p>
    <ul>
        <li><b><code>FilePool</code>:</b> Persistencia en disco con una estructura de directorios jerárquica (basada en el hash de la clave) para evitar cuellos de botella en el sistema de archivos del SO ante volúmenes masivos de datos.</li>
        <li><b><code>MemoryPool</code>:</b> Ideal para pruebas unitarias o procesos de corta duración que requieren velocidad de acceso volátil.</li>
    </ul>
</p>

<pre><code class="language-php">$this->cache->set('user_session_1', $userData, 3600);
$data = $this->cache->get('user_session_1');
</code></pre>

<p class="doc-alert"><b>Rendimiento de Autor:</b> El sistema soporta <b>Deferred Saving</b> (Guardado Diferido). Puedes preparar múltiples cambios en el pool y ejecutarlos atómicamente al final del ciclo de vida mediante el método <code>commit()</code>, optimizando las operaciones de I/O.</p>

<h2>
    <a href='#http-client'>HTTP Client</a>
    <span class='anchor' id='http-client'>...</span>
</h2>

<p>Para la comunicación con servicios externos y la implementación de patrones como BFF o Microservicios, Scoop integra un <b>Cliente HTTP nativo</b> alineado con el estándar <b>PSR-18</b>. Está construido sobre <b>cURL</b>, eliminando la necesidad de dependencias externas pesadas (como Guzzle) y manteniendo el motor extremadamente ligero.</p>

<h3>Capacidades Técnicas</h3>

<p>
    <ul>
        <li><b>Gestión de Streams:</b> Utiliza el motor de <i>Streams</i> de Scoop para manejar cuerpos de petición y respuesta de gran tamaño sin saturar la memoria RAM.</li>
        <li><b>Seguridad de Transporte:</b> Soporte nativo para protocolos modernos (TLS/SSL) y gestión automática de métodos HTTP (incluyendo verbos personalizados).</li>
        <li><b>Manejo de Excepciones:</b> Diferencia técnicamente entre errores de red (<code>NetworkException</code>) y errores de petición (<code>RequestException</code>), permitiendo una gestión de fallos granular en el Dominio.</li>
    </ul>
</p>

<pre><code class="language-php">public function get(): Response {
    $request = new Request('https://api.externa.com/v1/data', 'GET');
    return $this->httpClient->sendRequest($request);
}
</code></pre>

<h2>
    <a href='#crypt'>Vault: Seguridad Criptográfica</a>
    <span class='anchor' id='crypt'>...</span>
</h2>

<p>Scoop incluye <b>Vault</b>, un sistema de encriptación de alta seguridad basado en <b>AES-256-GCM</b>. Está diseñado para proteger datos sensibles en reposo (como tokens de terceros o información personal) garantizando la integridad mediante una etiqueta de autenticación (AEAD).</p>

<pre><code class="language-php">[
    'vault' => ['secret' => 'myP4ssw0rd', 'encoding' => 'hex']
]
</code></pre>

<p>Para usarlo, basta con inyectar la clase <code>Vault</code> en tu servicio o repositorio:</p>

<pre><code class="language-php">$encrypted = $this->vault->encrypt("Dato sensible");
$plainText = $this->vault->decrypt($encrypted);
</code></pre>

<h2>
    <a href='#ice'>ICE: Interface Command Environment</a>
    <span class='anchor' id='ice'>...</span>
</h2>

<p><b>ICE</b> es el orquestador de consola de Scoop. No es solo un script de ayuda, es un <b>Command Bus</b> extensible que utiliza el mismo sistema de inyección y contexto que la aplicación web.</p>

<h3>Extensibilidad de Comandos</h3>

<p>Puedes registrar tus propios comandos de infraestructura mapeando una clave al nombre de una clase. Cada comando tiene acceso total al <b>Injector</b> para resolver sus dependencias.</p>

<pre><code class="language-php">[
    'commands' => [
        'notification' => '\App\Service\ReceiveNotification'
    ]
]
</code></pre>

<p>Ejecución desde la terminal.</p>

<pre><code class="language-shell">php app/ice notification</code></pre>

<h3>Anatomía de un Comando</h3>

<p>Para crear un comando en Scoop, se debe definir una clase que implemente la lógica de ejecución y su propia ayuda. El motor de ICE requiere que la clase posea al menos dos métodos fundamentales:</p>

<ul>
    <li><b><code>execute($request)</code>:</b> El punto de entrada de la lógica. Recibe un objeto <code>\Scoop\Command\Request</code> con los argumentos y opciones.</li>
    <li><b><code>help()</code>:</b> Define la documentación del comando, invocada automáticamente al usar el flag <code>--help</code>.</li>
</ul>

<pre><code class="language-php">class Router
{
    private $bus;
    private $writer;
    private $msg;

    public function __construct($msg, \Scoop\Command\Writer $writer, \Scoop\Command\Bus $bus)
    {
        $this->writer = $writer;
        $this->msg = $msg;
        $this->bus = $bus;
    }

    public function execute($command)
    {
        $args = $command->getArguments();
        $commandName = array_shift($args);
        $this->bus->dispatch($commandName, $args);
    }

    public function help()
    {
        $commands = $this->bus->getCommands();
        $this->writer->write($this->msg, '', 'Commands:');
        foreach ($commands as $command => $controller) {
            $this->writer->write("$command => &lt;link!$controller.php!&gt;");
        }
        $this->writer->write('', 'Run app/ice new COMMAND --help for more information');
    }
}
</code></pre>

<h3>Gestión de la Salida (Writer)</h3>

<p>Scoop provee la clase <code>Writer</code> para gestionar la salida por terminal de forma elegante. Utiliza un sistema de etiquetas para aplicar estilos ANSI de forma sencilla:</p>

<ul>
    <li><b>Estilos de texto:</b> <code>&lt;success:...!&gt;</code>, <code>&lt;error:...!&gt;</code>, <code>&lt;warn:...!&gt;</code>, <code>&lt;link:...!&gt;</code>.</li>
    <li><b>Estilos de bloque:</b> <code>&lt;info:...!&gt;</code>, <code>&lt;danger:...!&gt;</code>, <code>&lt;done:...!&gt;</code>.</li>
</ul>

<p>Por defecto se usa el estandar output, pero se puede modificar mediante el método <code>withError</code> a estandard error, recordemos que Writer es una clase inmutable y tambien se puede modificar el separator con <code>withSeparator</code>.</p>

<h3>Procesamiento de la Petición (Request)</h3>

<p>El objeto <code>Request</code> inyectado en el método <code>execute</code> facilita el acceso a los datos de la terminal de forma tipada y segura:</p>

<ul>
    <li><b>Argumentos:</b> Valores posicionales tras el nombre del comando (<code>$request->getArguments()</code>).</li>
    <li><b>Opciones (Options):</b> Parámetros con nombre definidos con doble guion (<code>--name=valor</code>).</li>
    <li><b>Banderas (Flags):</b> Interruptores booleanos de un solo guion (<code>-f</code>).</li>
</ul>

<h2>
    <a href='#i18n'>Internacionalización (i18n)</a>
    <span class='anchor' id='i18n'>...</span>
</h2>

<p>Scoop gestiona la localización de forma centralizada en <code>app/config/lang</code>. El sistema desacopla los nombres técnicos de los campos (infraestructura) de los mensajes amigables para el usuario (presentación).</p>

<pre><code class="language-php">return [
    'fields' => [
        'email_address' => 'correo electrónico'
    ],
    'failures' => [
        \Scoop\Validation\Rule\Required::class => 'El campo {field} es obligatorio.'
    ],
    'messages' => [
        'welcome' => 'Bienvenido al sistema'
    ]
];
</code></pre>

<p>Se puede definir un lenguaje por defecto mediante la clase <code>lang</code>.</p>

<pre><code class="language-php">['lang' => 'es']</code></pre>

<h3>Uso Dinámico</h3>

<p>El idioma puede ser modificado en tiempo de ejecución mediante middlewares (PSR-15), permitiendo aplicaciones multi-idioma basadas en la sesión o cabeceras del navegador.</p>

<pre><code class="language-php">class Middleware
{
    public function __construct(
        private \Scoop\Bootstrap\Configuration $conf
    ) {
    }

    public function process($request, $handler)
    {
        $this->conf->setLanguage($request->getHeaderLine('Accept-Language'));
        $handler->handle($request);
    }
}
</code></pre>

<p>En las vistas, se accede a las traducciones mediante el helper de inyección:</p>
<pre><code class="language-php-template">&lt;p&gt;&#123;{ #view->translate('welcome') }&#125;&lt;/p&gt;
</code></pre>
