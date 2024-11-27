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

<pre><code class="language-shell">php app/ice notification</code></pre>

<pre><code class="language-php">class Creator
{
    private static $commands = array(
        'struct' => '\Scoop\Command\Creator\Struct'
    );
    private $bus;
    private $writer;

    public function __construct(\Scoop\Command\Writer $writer)
    {
        $this->writer = $writer;
        $this->bus = new \Scoop\Command\Bus(self::$commands);
    }

    public function execute($command)
    {
        $args = $command->getArguments();
        $commandName = array_shift($args);
        $this->bus->dispatch($commandName, $args);
    }

    public function help()
    {
        echo 'create new starter artifacts', PHP_EOL, PHP_EOL,
        'Commands:', PHP_EOL;
        foreach (self::$commands as $command => $controller) {
            echo $command, ' => ', $this->writer->writeLine("$controller.php", \Scoop\Command\Style\Color::BLUE);
        }
        echo PHP_EOL, 'Run app/ice new COMMAND --help for more information', PHP_EOL;
    }
}
</code></pre>

<h2>
    <a href='#i18n'>Internacionalización</a>
    <span class='anchor' id='i18n'>...</span>
</h2>

<p>El idioma de la aplicación se puede configurar por defecto desde el <a href="{{#view->route('doc', 'configuration')}}#basic-config">array de configuración</a>.
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
