<ul>
    <li><a href='#monitoring'>Monitoreo</a></li>
    <li><a href='#crypt'>Sistema de encriptación</a></li>
    <li><a href='#ice'>Interface Command Environment (ICE)</a></li>
</ul>

<h2>
    <a href='#monitoring'>Monitoreo</a>
    <span class='anchor' id='monitoring'>...</span>
</h2>

<pre class='prettyprint'>
[
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
</pre>

<h2>
    <a href='#crypt'>Sistema de encriptación</a>
    <span class='anchor' id='crypt'>...</span>
</h2>

<pre class='prettyprint'>
[
    'vault' => ['secret' => 'myP4ssw0rd', 'encoding' => 'hex']
]
</pre>

<h2>
    <a href='#ice'>Interface Command Environment (ICE)</a>
    <span class='anchor' id='ice'>...</span>
</h2>

<pre class='prettyprint'>
[
    'commands' => [
        'notification' => '\App\Service\ReceiveNotification'
    ]
]
</pre>

<pre class='prettyprint'>
php app/ice notification
</pre>

<pre class="prettyprint">
class Creator
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
</pre>

<h2>
    <a href='#i18n'>Internacionalización</a>
    <span class='anchor' id='i18n'>...</span>
</h2>


