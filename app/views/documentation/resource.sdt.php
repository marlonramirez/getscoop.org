<ul>
    <li><a href='#monitoring'>Monitoreo</a></li>
    <li><a href='#crypt'>Sistema de encriptación</a></li>
    <li><a href='#ice'>Interface Command Environment (ICE)</a></li>
    <li><a href='#i18n'>Internacionalización</a></li>
</ul>

<h2>
    <a href='#monitoring'>Monitoreo</a>
    <span class='anchor' id='monitoring'>...</span>
</h2>

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

<h2>
    <a href='#crypt'>Sistema de encriptación</a>
    <span class='anchor' id='crypt'>...</span>
</h2>

<pre><code class="language-php">[
    'vault' => ['secret' => 'myP4ssw0rd', 'encoding' => 'hex']
]
</code></pre>

<h2>
    <a href='#ice'>Interface Command Environment (ICE)</a>
    <span class='anchor' id='ice'>...</span>
</h2>

<pre><code class="language-php">[
    'commands' => [
        'notification' => '\App\Service\ReceiveNotification'
    ]
]
</code></pre>

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

<pre><code class="language-shell">php app/ice notification</code></pre>

<h2>
    <a href='#i18n'>Internacionalización</a>
    <span class='anchor' id='i18n'>...</span>
</h2>

<p>El idioma de la aplicación se puede configurar por defecto desde el <a href="{{#view->route('doc', 'configure')}}#basic-config">array de configuración</a>.
Si se desea realizar modificaciones del idioma de manera dinámica se puede hacer uso del método <code>useLenguage</code> del contexto;
para esto se pueden usar técnicas como el uso de midlewares.</p>

<p class="doc-danger">El siguiente ejemplo simula el uso de midlewares seún <a href="https://www.php-fig.org/psr/psr-15/">PSR-15</a>
que aún no ha sido implementado y puede ser suceptible a cambios.</p>

<pre><code class="language-php">class Midleware
{
    public function __construct(
        private \Scoop\Bootstrap\Configuration $conf
    ) {
    }

    public function process($request, $handler)
    {
        $this->conf->setLenguage($request->getVariable('lang'));
        $handler->handle($request);
    }
}
</code></pre>

<p>Las traducciones que maneja el sistema son:</p>

<ul>
    <li>Mensajes de error en la clase <code>\Scoop\Validator</code>, para esto se hace uso de la clase que realiza la validación.</li>
    <li>Campos de los mensajes en la clase <code>\Scoop\Validator</code>, para esto se hace uso del nombre del campo.</li>
    <li>Excepciones lanzadas y no controladas, para esto se hace uso del codigo de la excepción.</li>
    <li>Mensajes en las vistas mediante el helper <code>#view->translate($msg)</code></li>
</ul>
