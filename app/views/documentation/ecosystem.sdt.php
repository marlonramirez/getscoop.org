<p>Scoop provee un conjunto de servicios de soporte diseñados para garantizar la robustez del sistema en producción. Desde la gestión inteligente de errores hasta el entorno de consola extensible, estas herramientas permiten que la infraestructura de la aplicación sea tan sólida como su lógica de negocio.</p>

<p><ul>
    <li><a href="#exceptions">Manejo de Excepciones y Mapeo HTTP</a></li>
    <li><a href="#monitoring">Monitoreo y Logging</a></li>
    <li><a href="#cache">Caché</a></li>
    <li><a href="#http-client">HTTP Client</a></li>
    <li><a href="#events">Catálogo de eventos</a></li>
    <li><a href="#ice">ICE: Interface Command Environment</a></li>
    <li><a href="#i18n">Internacionalización (i18n)</a></li>
</ul></p>

<h2>
    <a href="#exceptions">Manejo de Excepciones y Mapeo HTTP</a>
    <span class="anchor" id="exceptions">...</span>
</h2>

<p>En Scoop, las excepciones de dominio son ciudadanos de primera clase. El motor permite desacoplar la lógica de error del negocio de la respuesta de infraestructura mediante <code>Http\Error\Mapper</code>.</p>

<p>Cualquier excepción puede ser mapeada a un código de estado HTTP específico, permitiendo además inyectar cabeceras personalizadas o definir vistas de error dedicadas.</p>

<pre><code class="language-php">'http' => [
    'errors' => [
        401 => [
            'title' => 'User not authorized',
            'headers' => [
                'WWW-Authenticate' => 'Digest realm="' . $domain . '",qop="auth",nonce="' . uniqid() . '",opaque="' . md5($domain) . '"'
            ],
            'exceptions' => [
                NotAuthorized::class
            ]
        ]
    ]
]
</code></pre>

<p class="doc-alert p-md rounded-lg text-on-surface bg-warning-subtle border-warning"><b>Observabilidad:</b> El sistema detecta automáticamente si la petición es Ajax/JSON o una navegación estándar, devolviendo el formato de error adecuado (un Payload JSON o un renderizado SDT) de forma transparente.</p>

<h2>
    <a href='#monitoring'>Monitoreo y Logging</a>
    <span class='anchor' id='monitoring'>...</span>
</h2>

<p>La configuración <code>log</code> define los handlers de los niveles <code>emergency</code>, <code>alert</code>, <code>critical</code>, <code>error</code>, <code>warning</code>, <code>notice</code>, <code>info</code> y <code>debug</code>. Cada nivel admite un array que asocia clases de handlers con sus argumentos de constructor y permite enviar un mensaje a múltiples destinos.</p>

<p><code>all</code> es un helper que agrega handlers comunes a todos los niveles habilitados. Sus handlers se combinan con los del nivel específico. Si un handler aparece en ambos, prevalecen los argumentos definidos para ese nivel.</p>

<ul>
    <li>Un nivel configurado explícitamente con <code>null</code> o <code>array()/[]</code> se silencia, incluso si <code>all</code> contiene handlers.</li>
    <li><code>all =&gt; null</code> o <code>all =&gt; array()/[]</code> no agrega handlers comunes; no silencia los niveles.</li>
    <li>Un nivel sin configuración utiliza los handlers de <code>all</code>. Si no hay handlers comunes, utiliza <code>Scoop\Log\Handler\Standard</code> y escribe en <code>stdout</code>.</li>
    <li>Cuando hay handlers configurados, <code>stdout</code> no se agrega automáticamente. Para escribir también allí, incluye <code>Scoop\Log\Handler\Standard</code> en el array.</li>
</ul>

<pre><code class="language-php">return [
    'all' =&gt; [
        'Scoop\Log\Handler\File' =&gt; [],
    ],
    'error' =&gt; [
        'Scoop\Log\Handler\Standard' =&gt; ['error' =&gt; true],
    ],
    'debug' =&gt; null,
    'info' =&gt; [],
];
</code></pre>

<p>En este ejemplo, <code>error</code> combina el handler heredado de <code>all</code> con el handler específico del nivel, por lo que escribe en el archivo común y en <code>stderr</code>. <code>debug</code> e <code>info</code> están silenciados. Los demás niveles utilizan únicamente <code>File</code>; ninguno de ellos agrega <code>Standard</code> ni escribe en <code>stdout</code>.</p>

<p class="doc-alert p-md rounded-lg text-on-surface bg-warning-subtle border-warning"><b>Fallback predeterminado:</b> <code>Standard</code> y <code>stdout</code> se utilizan automáticamente solo cuando el nivel no tiene handlers propios ni heredados desde <code>all</code>. Por tanto, este fallback no se aplica al ejemplo anterior.</p>

<h3>Formato y recuperación de datos</h3>

<p>Si no configuras <code>file</code>, File escribe en <code>logs/&lt;app.name&gt;-YYYY-MM-DD.log</code> dentro del storage de la aplicación.</p>

<h4>Plantillas para nombres de archivo</h4>

<p>El argumento <code>file</code> admite plantillas dinámicas. <code>{level}</code> se reemplaza por el nivel del registro y los demás bloques se interpretan como formatos de fecha de <code>DateTime::format()</code>. Una ruta sin bloques permanece fija.</p>

<pre><code class="language-php">'Scoop\Log\Handler\File' =&gt; [
    'file' =&gt; 'app/storage/logs/{Y-m-d}/{level}.log'
]</code></pre>

<p>Este ejemplo separa los registros por día y nivel. El cambio de archivo no elimina ni comprime registros anteriores.</p>

<h3>Inyección de dependencias de los handlers</h3>

<p>Los argumentos escalares del constructor se configuran por nombre. Las dependencias declaradas mediante una clase o interfaz se resuelven automáticamente utilizando los providers y sus scopes; los parámetros opcionales conservan su valor predeterminado. Si falta un argumento requerido, el handler no puede crearse y el fallo se reporta mediante el mecanismo de errores de PHP.</p>

<p>Los valores escalares, como <code>url</code> o una ruta <code>file</code> explícita, se proporcionan en la configuración. <code>formatter</code> admite un nombre de provider o una instancia; por defecto se utiliza <code>Scoop\Log\Formatter</code>. Los handlers personalizados pueden declarar dependencias de clase o interfaz en su constructor sin agregarlas manualmente al array.</p>

<p>El formatter predeterminado genera registros JSON con mensaje, contexto, nivel y fecha. Puedes sustituirlo mediante el argumento <code>formatter</code>.</p>

<h3>Entrega de logs</h3>

<p><code>Standard</code> escribe inmediatamente. <code>File</code> y <code>Slack</code> realizan entrega diferida mediante <code>Logger::flush()</code>. Para escribir archivos inmediatamente, configura <code>shouldDefered =&gt; false</code>.</p>

<p>Los handlers personalizados deben controlar y reportar sus errores sin afectar a los demás destinos. Si realizan entrega diferida, deben proporcionar un método público <code>flush()</code>.</p>

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
                    'blocks' => [[
                        'type' => 'section',
                        'text' => [
                            'type' => 'mrkdwn',
                            'text' => 'Something has happened:'
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
                'config' => ['blocks' => [
                    [
                        'type' => 'section',
                        'text' => [
                            'type' => 'mrkdwn',
                            'text' => 'Critical error email send with:'
                        ]
                    ]
                ]]
            ],
            File::class => ['file' => '/logs']
        ]
    ]
]
</code></pre>

<p>Puedes registrar listeners para eventos como <code>ConnectionOpened</code>, <code>ConnectionClosed</code> y <code>RequestFinished</code>. Para observar errores al terminar la petición, consulta <code>RequestFinished::getError()</code>.</p>

<h2>
    <a href='#cache'>Caché</a>
    <span class='anchor' id='cache'>...</span>
</h2>

<p>Scoop cumple funcionalmente con los contratos de <b>PSR-6</b> (Cache Item Pool) y <b>PSR-16</b> (Simple Cache), ofreciendo las operaciones y comportamientos definidos por ambos estándares. Las interfaces oficiales no se implementan directamente para preservar la retrocompatibilidad del core con PHP 5.4, por lo que el cumplimiento se expresa a nivel de contrato y API, no mediante vinculación nominal con las interfaces.</p>

<h3>Uso de la caché</h3>

<p>Utiliza la caché para reutilizar datos que no necesitas calcular o consultar en cada petición. Inyecta <code>\Scoop\Cache</code> en tu servicio: primero busca el valor y, si no existe o ha vencido, obtén los datos y guárdalos para las siguientes consultas.</p>

<pre><code class="language-php">$products = $this->cache->get('catalog.products');
if ($products === null) {
    $products = $this->productRepository->findAll();
    $this->cache->set('catalog.products', $products, 300);
}

return $products;
</code></pre>

<p>En este ejemplo, el catálogo se reutiliza durante cinco minutos. El tercer argumento de <code>set()</code> es su duración en segundos. Si lo omites, se aplica <code>cache.time</code>; con <code>0</code>, el valor no vence. Elige una clave que identifique todos los datos de la consulta, incluyendo filtros o usuario cuando corresponda.</p>

<p>Cuando cambies los datos originales, elimina la entrada para que la siguiente consulta obtenga el valor actualizado:</p>

<pre><code class="language-php">$this->productRepository->save($product);
$this->cache->delete('catalog.products');
</code></pre>

<h3>Dónde se guardan los datos</h3>

<p>Por defecto se utiliza <code>Scoop\Cache\Item\Pool\File</code>, que conserva los valores en archivos entre ejecuciones. Si sólo necesitas reutilizarlos durante la vida de una instancia, puedes elegir <code>Memory</code> desde <code>providers</code>:</p>

<pre><code class="language-php">'providers' => array(
    'Scoop\Cache\Item\Pool' => array(
        'service' => 'Scoop\Cache\Item\Pool\Memory',
        'scope' => 'singleton'
    )
)</code></pre>

<p>El código que consulta la caché sigue siendo el mismo. Con <code>Memory</code>, los valores se pierden al terminar la instancia y no se comparten con otros procesos.</p>

<h3>Guardar al finalizar una operación</h3>

<p>Si necesitas preparar varias entradas antes de guardarlas, inyecta <code>Scoop\Cache\Item\Pool</code> y utiliza el guardado diferido:</p>

<pre><code class="language-php">$item = $this->pool->getItem('catalog.products');
$item->set($products);
$item->expiresAfter(300);
$this->pool->saveDeferred($item);
$this->pool->commit();
</code></pre>

<p>Llama a <code>commit()</code> al finalizar la operación. Cada entrada se guarda por separado; el lote no constituye una transacción atómica.</p>

<h2>
    <a href='#http-client'>HTTP Client</a>
    <span class='anchor' id='http-client'>...</span>
</h2>

<p>Para la comunicación con servicios externos y la implementación de patrones como BFF o Microservicios, Scoop integra un <b>Cliente HTTP nativo</b> que cumple funcionalmente el contrato de <b>PSR-18</b>. Está construido sobre <b>cURL</b>, sin vincular nominalmente la interfaz oficial para preservar la compatibilidad con PHP 5.4.</p>

<h3>Capacidades Técnicas</h3>

<p><ul>
    <li><b>Gestión de Streams:</b> Utiliza el motor de <i>Streams</i> de Scoop para manejar cuerpos de petición y respuesta de gran tamaño sin saturar la memoria RAM.</li>
    <li><b>Seguridad de Transporte:</b> Soporte nativo para protocolos modernos (TLS/SSL) y gestión automática de métodos HTTP (incluyendo verbos personalizados).</li>
    <li><b>Manejo de Excepciones:</b> Diferencia técnicamente entre errores de red (<code>NetworkException</code>) y errores de petición (<code>RequestException</code>), permitiendo una gestión de fallos granular en el Dominio.</li>
    <li><b>Configuración Inmutable:</b> <code>withOption()</code> permite derivar un cliente con una opción de cURL adicional sin modificar la instancia compartida.</li>
</ul></p>

<pre><code class="language-php">public function get(): Response {
    $request = new Request('https://api.externa.com/v1/data', 'GET');
    return $this->httpClient->sendRequest($request);
}
</code></pre>

<pre><code class="language-php">$client = $this->httpClient
    ->withOption(CURLOPT_TIMEOUT, 5)
    ->withOption(CURLOPT_CONNECTTIMEOUT, 2);

$response = $client->sendRequest($request);
</code></pre>

<p>Si la opción ya contiene el mismo valor, <code>withOption()</code> puede devolver la misma instancia; en caso contrario devuelve una copia configurada. El cliente original permanece sin cambios.</p>

<h2>
    <a href="#events">Catálogo de eventos</a>
    <span class="anchor" id="events">...</span>
</h2>

<p>Scoop es un motor observable. El núcleo dispara <a href="{{#view->route('doc', 'application')}}#events">eventos</a> clave que permiten extender el comportamiento del sistema sin modificar el código fuente:</p>

<ul>
    <li class="doc-alert p-md rounded-lg text-on-surface bg-warning-subtle border-warning"><code>Scoop\Http\Event\ErrorOccurred:</code> Deprecado desde v0.8.6 en favor de <code>RequestFinished</code>. Sigue emitiéndose por compatibilidad cuando Application captura una excepción durante el enrutamiento o el flush de entidades; no cubre todos los errores del arranque o del cierre.</li>
    <li><code>Scoop\Http\Event\RequestFinished:</code> Se emite en <code>Application::terminate()</code>, antes de limpiar los servicios y vaciar los logs. <code>getError()</code> devuelve la excepción capturada o <code>null</code>; <code>getStatusCode()</code> devuelve el estado mapeado para ese error o <code>null</code> si no hubo mapeo de error. <code>getTime()</code> devuelve un <code>DateTime</code> con el momento de creación del evento, no la duración de la petición. Las excepciones sin mapear también ejecutan la terminación antes de propagarse.</li>
    <li><code>Scoop\Persistence\Event\ConnectionOpened:</code> Notifica la apertura de una conexión de base de datos. Provee acceso al objeto mediante <code>getConnection()</code>, ideal para auditoría o inyectar configuraciones de sesión SQL (ej. <code>SET TIMEZONE</code>).</li>
    <li><code>Scoop\Persistence\Event\ConnectionClosed:</code> Se dispara al cerrar la conexión o destruir el objeto Connection, permitiendo tareas de limpieza o métricas de tiempo de uso.</li>
</ul>

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

<p>Para arquitecturas extensibles, puedes usar el cargador <code>typeof</code>. Este recupera automáticamente todas las clases que implementan un contrato específico.</p>

<pre><code class="language-php">'commands' => 'typeof:App\Command\CommandInterface'
</code></pre>

<p>Cuando se utilizan cargadores dinámicos o listas indexadas, el comando debe implementar el método estático <code>getName()</code> para definir su palabra clave en la consola <code>ice</code>.</p>

<p>Ejecución desde la terminal.</p>

<pre><code class="language-shell">php app/ice notification</code></pre>

<h3>Comandos del Sistema</h3>

<p>ICE incluye comandos para las tareas habituales de desarrollo, mantenimiento y construcción del proyecto:</p>

<ul>
    <li><b><code>new</code>:</b> Genera artefactos iniciales, como nuevas estructuras de base de datos.</li>
    <li><b><code>scan routes</code>:</b> Construye el mapa optimizado de rutas.</li>
    <li><b><code>scan source</code>:</b> Analiza el código fuente y genera los mapas de tipos y dependencias utilizados por el sistema.</li>
    <li><b><code>preload</code>:</b> Resuelve anticipadamente un recurso gestionado por los cargadores diferidos de <code>Context</code>.</li>
    <li><b><code>clean</code>:</b> Elimina artefactos temporales, como la caché general o las vistas procesadas.</li>
    <li><b><code>dbup</code>:</b> Ejecuta las estructuras pendientes sobre la base de datos.</li>
</ul>

<pre><code class="language-shell">php app/ice scan source
php app/ice scan routes
php app/ice clean cache
php app/ice clean views
php app/ice dbup --name=default --schema=public</code></pre>

<p>Todos los comandos exponen su ayuda mediante <code>--help</code>. Los escáneres reutilizan sus mapas mientras las fuentes no cambien; el flag <code>-f</code> fuerza su reconstrucción. No se recomienda modificar manualmente los archivos generados en la caché.</p>

<h3>Anatomía de un Comando</h3>

<p>Para crear un comando en Scoop, se debe definir una clase que implemente la lógica de ejecución y su propia ayuda. El motor de ICE requiere que la clase posea al menos dos métodos fundamentales:</p>

<ul>
    <li><b><code>execute($request)</code>:</b> El punto de entrada de la lógica. Recibe un objeto <code>\Scoop\Command\Request</code> con los argumentos y opciones.</li>
    <li><b><code>help()</code>:</b> Define la documentación del comando, invocada automáticamente al usar el flag <code>--help</code>.</li>
</ul>

<p><pre><code class="language-php">class Router
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
            $this->writer->write("$command => &lt;link:$controller.php!&gt;");
        }
        $this->writer->write('', 'Run app/ice new COMMAND --help for more information');
    }
}
</code></pre></p>

<h3>Gestión de la Salida (Writer)</h3>

<p>Scoop provee la clase <code>Writer</code> para gestionar la salida por terminal de forma elegante. Utiliza un sistema de etiquetas para aplicar estilos ANSI de forma sencilla:</p>

<ul>
    <li><b>Estilos de texto:</b> <code>&lt;success:...!&gt;</code>, <code>&lt;error:...!&gt;</code>, <code>&lt;warn:...!&gt;</code>, <code>&lt;link:...!&gt;</code>.</li>
    <li><b>Estilos de bloque:</b> <code>&lt;info:...!&gt;</code>, <code>&lt;danger:...!&gt;</code>, <code>&lt;done:...!&gt;</code>.</li>
</ul>

<p>Por defecto se usa el standard output, pero se puede modificar mediante el método <code>withError</code> a standard error, recordemos que Writer es una clase inmutable y también se puede modificar el separator con <code>withSeparator</code>.</p>

<h4>Indicadores de Actividad y Progreso</h4>

<p><code>Writer</code> incorpora indicadores actualizables en la misma línea. El llamador controla la iteración del <i>spinner</i> o el avance del proceso, por lo que estos métodos pueden utilizarse dentro de cualquier ciclo de trabajo:</p>

<pre><code class="language-php">foreach ($files as $iteration => $file) {
    $this->writer->spinner($iteration, '&lt;link:[f]!&gt; Analizando fuentes...');
    $this->analyze($file);
}
$this->writer->write('&lt;done:Análisis completado!&gt;');

$total = count($files);
foreach ($files as $index => $file) {
    $this->compile($file);
    $this->writer->progress($index + 1, $total, 'Compilando &lt;success:[f]!&gt; [p]%');
}
$this->writer->write('&lt;done:Compilación completada!&gt;');</code></pre>

<p>En los mensajes personalizados, <code>[f]</code> representa el cuadro animado o la barra, mientras que <code>[p]</code> representa el porcentaje. Al llamar posteriormente a <code>write()</code>, Writer limpia la animación pendiente antes de imprimir el resultado definitivo. La compatibilidad con terminales VT100 y el mecanismo alternativo para terminales sin dicho soporte se seleccionan automáticamente.</p>

<h4>Personalización de Estilos</h4>

<p>Los estilos utilizados por Writer se configuran en la sección <code>ice.styles</code> del archivo de configuración. Al definir esta sección, el mapa configurado reemplaza los estilos predeterminados:</p>

<pre><code class="language-php">'ice' => [
    'commands' => 'typeof:App\Command\CommandInterface',
    'styles' => [
        'link' => [\Scoop\Command\Style\Color::BLUE],
        'success' => [\Scoop\Command\Style\Color::GREEN]
    ]
]</code></pre>

<h3>Procesamiento de la Petición (Request)</h3>

<p>El objeto <code>Request</code> inyectado en el método <code>execute</code> facilita el acceso a los datos de la terminal de forma tipada y segura:</p>

<ul>
    <li><b>Argumentos:</b> Valores posicionales tras el nombre del comando (<code>$request->getArguments()</code>).</li>
    <li><b>Opciones (Options):</b> Parámetros con nombre definidos con doble guion (<code>--name=valor</code>).</li>
    <li><b>Banderas (Flags):</b> Interruptores booleanos de un solo guion (<code>-f</code>).</li>
</ul>

<h3 id="command-pool">Supervisión de comandos</h3>

<p>Utiliza <code>\Scoop\Command\Pool</code> desde un comando CLI para ejecutar y supervisar otros procesos. Recibe el pool por inyección de constructor o resuélvelo desde el contexto:</p>

<pre><code class="language-php">$pool = $context->getInjector()->get('Scoop\Command\Pool');
$pool->run([
    'notifications' => [
        'command' => [PHP_BINARY, 'app/ice', 'consume-notifications'],
        'processes' => 2
    ],
    'report' => [
        'command' => [PHP_BINARY, 'app/ice', 'generate-report'],
        'policy' => 'never'
    ],
    'inventory' => [
        'command' => [PHP_BINARY, 'app/ice', 'sync-inventory'],
        'policy' => 'on-failure'
    ]
]);
</code></pre>

<p><code>run($workers)</code> recibe toda la configuración de una invocación. Cada clave identifica el comando en los eventos y cada valor es un mapa con <code>command</code>, <code>processes</code> y <code>policy</code>. <code>command</code> es obligatorio y contiene un array con el ejecutable y sus argumentos por separado. <code>processes</code> y <code>policy</code> son opcionales: sus valores por defecto son una réplica y <code>always</code>. El número de réplicas debe ser un entero positivo; los demás valores se normalizan a una réplica.</p>

<ul>
    <li><code>always</code>: reinicia el proceso cada vez que termina.</li>
    <li><code>never</code>: intenta ejecutarlo una sola vez por réplica.</li>
    <li><code>on-failure</code>: reinicia si no puede arrancar o termina sin código de éxito (<code>0</code>).</li>
</ul>

<p>Las políticas admitidas son <code>always</code>, <code>never</code> y <code>on-failure</code>. Una política desconocida produce una <code>InvalidArgumentException</code>. Omitir la política o indicar <code>null</code> utiliza <code>always</code>.</p>

<p><code>run($workers)</code> mantiene ocupado el comando supervisor hasta que todas las réplicas terminan según su política o se solicita el apagado. Para ejecutar de nuevo o cambiar la configuración, vuelve a pasar el mapa a <code>run()</code>; <code>run(array())</code> no inicia procesos.</p>

<h4>Configurar el cierre</h4>

<p>Para ajustar los plazos, construye el pool con el dispatcher de eventos de la aplicación:</p>

<pre><code class="language-php">$pool = new \Scoop\Command\Pool($dispatcher, 120, 60);
</code></pre>

<p>El segundo argumento es el tiempo máximo de cierre y el tercero la espera máxima entre reinicios, ambos en segundos. Los valores predeterminados son 120 y 60. Ejecuta los procesos en primer plano y detén el supervisor con <code>Ctrl+C</code> o la señal de apagado del servicio. Tus consumidores deben atender esa señal para terminar el trabajo en curso dentro del plazo; configura el gestor del servicio para que espere más tiempo que el pool.</p>

<p>El pool es compatible con PHP 5.4. El cierre ordenado mediante señales requiere PCNTL en Linux; en Windows está disponible desde PHP 7.4. En Windows con PHP 5.4 puedes ejecutar el pool, pero no se garantiza el cierre ordenado al interrumpirlo.</p>

<h4>Observar los procesos</h4>

<p>Registra listeners mediante el <a href="{{#view->route('doc', 'application')}}#events">Event Bus</a>. Los eventos del supervisor pertenecen a <code>Scoop\Command\Event</code>:</p>

<ul>
    <li><code>ProcessStarted</code>: el proceso fue creado; no indica que el servicio ya esté listo.</li>
    <li><code>ProcessExited</code>: el proceso terminó. <code>getExitCode()</code> devuelve su código de salida, o <code>-1</code> si no está disponible.</li>
    <li><code>ProcessFailed</code>: no se pudo iniciar el proceso.</li>
    <li><code>ProcessStopped</code>: el proceso fue cerrado durante el apagado del supervisor.</li>
</ul>

<p>Puedes escuchar un tipo concreto o <code>ProcessEvent</code> para recibirlos todos. La API común ofrece <code>getName()</code> (la clave del mapa pasado a <code>run()</code>), <code>getCommand()</code> (ejecutable y argumentos), <code>getReplica()</code> (índice desde cero dentro de cada nombre), <code>getPid()</code> (<code>null</code> si no pudo arrancar), <code>getTime()</code> (<code>DateTime</code>) y <code>getStartCount()</code> (número de arranques correctos de esa réplica durante la ejecución actual). Un intento fallido de creación no incrementa el contador.</p>

<p>Por ejemplo, este listener registra el inicio de cada réplica:</p>

<pre><code class="language-php">public function listen(\Scoop\Command\Event\ProcessStarted $event)
{
    $this->logger->info('Proceso de comando ICE iniciado', array(
        'name' => $event->getName(),
        'command' => $event->getCommand(),
        'replica' => $event->getReplica(),
        'pid' => $event->getPid(),
        'starts' => $event->getStartCount()
    ));
}
</code></pre>

<p>Controla los errores de tus listeners: una excepción interrumpe la supervisión y se propaga al comando llamador.</p>

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
        private \Scoop\Bootstrap\Configuration $config,
        private \Scoop\Http\Router $router
    ) {
    }

    public function process($request, $handler)
    {
        $language = $router->getCurrentRoute()->getParameter('lang');
        $this->config->setLanguage($language);
        return $handler->handle($request);
    }
}
</code></pre>

<p>En las vistas, se accede a las traducciones mediante el helper de inyección:</p>
<pre><code class="language-php-template">&lt;p&gt;&#123;{ #view->translate('welcome') }&#125;&lt;/p&gt;
</code></pre>
