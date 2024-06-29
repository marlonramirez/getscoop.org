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
            'handler' => File::class, 
            'file' => '/logs'
        ],
        Level::NOTICE => [
            'handler' => Slack::class,
            'url' => 'https://hooks.slack.com/services/T00/B0000/XXXXXXXXXXXXXX',
            'config' => ['blocks': [
                [
                    'type': 'section',
                    'text': [
                        'type': 'mrkdwn',
                        'text': 'Danny Torrence left the following review for your property:'
                    ]
                ]
            ]]
        ],
        Level::WARNING => [
            'handler'=> Email::class,
            'email' => 'admin@sespesoft.com'
        ],
        Level::CRITICAL => [
            'handler' => All::class,
            'email' => 'admin@sespesoft.com',
            'url' => 'https://hooks.slack.com/services/T00/B0000/XXXXXXXXXXXXXX'
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
    'valut' => ['secret' => 'myP4ssw0rd', 'encoding' => 'hex']
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