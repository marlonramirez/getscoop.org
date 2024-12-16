<p>Scoop se basa en el paradigma de convención sobre configuración, lo que busca minimizar el número de decisiones que
un desarrollador necesita hacer, ganando simplicidad pero sin abandonar flexibilidad. Lamentablemente existen aspectos
no convencionales de la aplicación que se deben especificar y es aqui donde entra el sistema de configuración.</p>

<ul>
    <li><a href="#routes-config">Contexto y entorno</a></li>
    <li><a href="#basic-config">Configuraciones básicas</a></li>
    <li><a href="#routes">Rutas</a></li>
    <li><a href="#ioc">Inversión de control</a></li>
    <li><a href="#components">Componentes</a></li>
    <li><a href="#exceptions">Manejo de excepciones</a></li>
    <li><a href="#cors">CORS</a></li>
</ul>


<h2>
    <a href="#routes-config">Contexto y entorno</a>
    <span class="anchor" id="routes-config">...</span>
</h2>

<p>Si nos fijamos una de las primeras cosas que realiza el sistema es establecer un entorno de ejecución,
este no se instancia directamente si no que se carga mediante un contexto <code>\Scoop\Context</code>, el contexto
no solo genera el entorno si no el cargador de namespaces y el injector de dependencias, se debe pasar la ruta
del archivo de configuración al momento de cargar el contexto. Una vez definido el contexto se puede instanciar
la aplicación.</p>

<pre><code class="language-php">\Scoop\Context::load('app/config');
$app = new \Scoop\Bootstrap\Application();
echo $app->run();
</code></pre>

<p>Cuando un contexto es establecido se puede acceder al entorno mediante su método
<code>Context::getEnvironment()</code>, al cargador mediante <code>Context::getLoader()</code> y
 al inyector de dependencias mediante <code>Context::getInjector()</code>, tambien se pueden establecer
 conexiones y desconexiones a la base de datos mediante <code>Context::connect()</code> y <code>Context::disconnect()</code>
 respectivamente.</p>

 <p class="doc-danger">Desde la versión 0.6.4 el método <code>getInjector</code> se encuentra @deprecated en
 favor del método <code>Context::inject($id)</code> y desde la versión 0.7.3 el método <code>getLoader</code>.</p>

 <p>Para realizar la conexión a diferentes bases de datos se pueden enviar dos argumentos más a <code>\Context::connect($bundle, $options)</code>,
 lo mismo sucede con el método <code>Context::disconnect($bundle, $options)</code> para más información consulta <a href="{{#view->route('doc', 'ddd')}}#dbc">el uso del DBC</a>.</p>

<h2>
    <a href="#basic-config">Configuraciones básicas</a>
    <span class="anchor" id="basic-config">...</span>
</h2>

<p>El archivo de configuración establece los ajustes para el correcto funcionamiento de la aplicación,
aquí se encuentran datos para el acceso al sistema de persistencia, rutas, mensajes de error, entre muchos más.
Se pueden extender a otros archivos mediante <code>require</code> o carga perezosa.</p>

<pre><code class="language-php">['providers' => require 'config/providers.php']</code></pre>

<h3 id="lazy-loading">Carga peresoza</h3>

<p>Otra posibilidad de extender la configuración es mediante la carga peresoza de archivos, esta en vez de usar directamente
un  método de importanción como <code>require</code> hace uso de claves como <code>import</code>, <code>instanceof</code>
o <code>json</code>.</p>

<pre><code class="language-php">[
    'messages' => [
        'es' => 'import:app/config/lang/es',
        'en' => 'import:app/config/lang/en'
    ]
]
</code></pre>

<p>Dentro de las diferencias a destacar en la carga peresoza es que se debe referenciar el archivo a cargar desde la raíz
del proyecto y no sobre el archivo donde se esta configurando el arreglo, la segunda es el uso de <code>:</code> para
separar el método de carga con la url del archivo y la última es la ausencia de extención para el tipo de archivo, esto se
debe a que cada método de carga tiene su propio tipo de extensión, así el método json solo cargara archivos con esta extensión,
mientras import hará lo mismo con los archivos .php.</p>

<p>El método <b>instanceof</b> lo que trata de hacer es capturar cualquier clase que implementé o extendidá la interface o clase
especificada.</p>

<pre><code class="language-php">['queryHandlers' => 'instanceof:App\Shared\Application\Query']</code></pre>

<h3>app</h3>

<p>dentro de app se pueden establecer todas las variables de entorno a las que puede acceder la aplicación, aqui se
encuentran variables como name y version, una tecnica que utiliza scoop para establecer variables es usar
package.json como archivo de configuración.</p>

<pre><code class="language-php">['app' => 'json:package']</code></pre>

<h3>db</h3>

<p>Scoop soporta multiples instancias de base de datos, con lo cual se pueden tener dentro de una misma aplicación
diferentes conexiones cada una de ellas debe ser establecida mediante db en el archivo de configuración, por defecto
se usara la conexión default.</p>

<pre><code class="language-php">[
    'db' => [
        'default' => [
            'database' => 'scoop',
            'user' => 'scoop',
            'password' => '1s4Gr34tB00t5tr4p',
            'host' => 'localhost',
            'port' => 5432,
            'driver' => 'pgsql'
        ],
        'auth' => [
            'database' => 'auth',
            'user' => 'scoop',
            'password' => 'myS1st3m4uth',
            'host' => 'localhost',
            'port' => 3306,
            'driver' => 'mysql'
        ]
    ]
]
</code></pre>

<p class="doc-alert">Desde la versión <code>0.2.1</code> no se cuenta con soporte nativo para drivers diferentes a
los suministrados por PDO.</p>

<h3>messages</h3>

<p>Por defecto scoop mostrara un mensaje si este no se encuentra dentro del archivo de configuración, se pueden
manejar técnicas de <a href="{{#view->route('doc', 'resources')}}#i18n">internacionalización</a> realizando la respectiva separación de mensajes por idioma, este tema
escapa al manejo de la herramienta, pero presta las condiciones para su implementación.</p>

<pre><code class="language-php">[
    'messages' => [
        'es' => [
            'required' => 'Complete este campo',
            'email' => 'Introduzca una dirección de correo valida'
        ],
        'en' => [
            'required' => 'Please fill out this field',
            'email' => 'Please include a valid email'
        ]
    ]
]
</code></pre>

<h3>asset</h3>

<p>Finalmente se tiene la configuración de assets, estos se usan principalmente en las vistas para ubicar los recursos
publicos de la aplicación (Archivos css, javascript, imagenes, etc). El uso de asset dentro de scoop es muy sencillo,
la ruta principal de los archivos se ubica en <code>asset.path</code>, el resto de parametros son rutas relativas que
parten de esta ruta principal.</p>

<pre><code class="language-php">[
    'asset' => [
        'path' => 'public/',
        'css' => 'css/',
        'js' => 'js/',
        'img' => 'images/'
    ]
]
</code></pre>

<p>En el anterior ejemplo para referirse al archivo stylesheet.css se debe seguir la ruta
<code>public/css/stylesheeet.css</code> y para acceder a esta desde una vista basta con solo colocar
<code>&#123;{#view->css('stylesheet.css')}&#125;</code>, por defecto se usa la configuración de ejemplo para ubicar los assets.</p>

<h3>Lenguaje</h3>

<p>Para configurar un idioma por defecto diferente al español se debe crear una key <code>lang</code> con valor del idioma a cargar en messages.</p>

<pre><code class="language-php">['lang' => 'es']</code></pre>

<h2>
    <a href="#routes">Rutas</a>
    <span class="anchor" id="routes">...</span>
</h2>

<p class="doc-danger">Desde la version <code>0.8</code> cambio drasticamente el sistema de enrutamiento
hacia <a href="#app-routes">app/routes</a>.</p>

<p>Dentro del archivo de rutas se establecen las propiedades que definen una URL, no es un sistema de ruteo
simple como en anteriores versiones, si no que establece una serie de caracteristicas como la interceptación
de peticiones y generación de rutas dinamicamente, sin sacrificar en ningún momento la funcionalidad y caracteristicas
que tenia el anterior sistema.</p>

<p class="doc-alert">Desde la version <code>0.5.4</code> Las rutas hacen parte integral del archivo de configuración
y por este motivo su ubicación dependere de la configuración realizada.</p>

<p>La configuración de rutas no solo se limita a indicarle al bootstrap hacia que controlador debe dirigir la
petición. Se modifico el funcionamiento del arreglo en donde la clave era la ruta y el valor era el controlador,
ahora la clave es el alias de la ruta y el valor un array asociativo con las siguientes propiedades:</p>

<ul>
    <li><h3>url</h3>
        <p>Es la propiedad principal de la ruta e indica la composición del path, es la unica propiedad obligatoria.
        Se ha abandonado la idea de un enrutamiento hibrido o compartido por lo cual se deben especificar dentro
        de la ruta datos como los tipos de variables que seran suministradas al controlador.</p>

<pre><code class="language-php">[
    'user' => [
        'url' => '/user/{var}/'
    ]
]
</code></pre>

        <p>El uso de variables se limita a dos tipo: <code>{var}</code> e <code>{int}</code>, en el
        primero se puede suministrar cualquier tipo de dato consistente con el formato url y el segundo filtra solo
        valores númericos, scoop toma todas las variables como parametros opcionales hacia el controlador, es este
        último el que debe establecer cuales son realmente opcionales y cuales obligatorios.</p>
    </li>

    <li><h3>controller</h3>
        <p>Establece el controlador hacia el cual debe apuntar la ruta. En caso que no especifique de manera explicita
        la forma de manejar métodos https. el ruteador verificara que existan dentro del controlador métodos con el mismo
        nombre de su contraparte en http, de no ser así se denerara un error 405.</p>
        <p class="doc-alert">Desde la version <code>0.5.6</code> no se usa el signo <code>:</code> para separar controlador
        de método, en su lugar se creo la propiedad <i>methods</i>. En la versión 0.6.1 se deprecio el uso de methods, así
        solo son permitidos métodos HTTP.</p>

<pre><code class="language-php">[
    'user' => [
        'url' => '/user/&#123;var&#125;/',
        'controller' => 'Controller\User'
    ]
]
</code></pre>

<p class="doc-alert">Desde la version <code>0.6.1</code> es posible separar los métodos HTTP en diferentes controladores,
esto beneficia el principio de single responsability. Cada una de las clases se puede implementar con el nombre del método http
como funciona cuando se declara un solo controlador o con el método <code>__invoke</code> o incluso declarar directamente la función</p>

<pre><code class="language-php">[
    'user' => [
        'url' => '/user/&#123;var&#125;/',
        'controller' => [
            'get' => 'Controller\UserReader',
            'post' => 'Controller\UserCreator',
            'put' => 'Controller\UserUpdater',
            'delete' => 'Controller\UserRemover'
        ]
    ],
    'health': [
        'url' => '/health-check',
        'controller' => [
            'get' => fn() => ['status' => 'OK']
        ]
    ]
]
</code></pre>
    </li>

    <li><h3>proxy</h3>
        <p>Los proxies son simples metodos que interactuan con la petición antes que esta llegue hasta el
        controlador, la interceptación de la petición es acumulativa, lo que quiere decir que todos los
        proxies establecidos en rutas anteriores son ejecutados en orden desde la ruta más corta hasta
        la más larga.</p>
<pre><code class="language-php">[
    'user' => [
        'url' => '/user/{var}/',
        'proxy' => 'App\Interceptor\Verify'
    ],
    'home' => [
        'url' => '/user',
        'proxy' => 'App\Interceptor\Auth'
    ]
]
</code></pre>
        <p>Un proxy debe implementar el método <code>execute</code> al cual se le pasará como argumento petición interceptada,
        en el ejemplo anterior se ejecutara primero el Proxy <code>/App/Interceptor/Auth</code> seguido de
        <code>App\Interceptor\Verify</code>. Vale la pena mencionar que un Proxy no puede devolver ningún valor para su
        encadenamiento, simplemente lanzar excepciones o redirigir peticiones.</p>
    </li>

    <li><h3>routes</h3>
        <p>El sistema de enrutamiento que maneja scoop es fragmentado al igual que sucede con el archivo de configuración.
        Para hacer uso de este sistema se debe establecer la propiedad routes y dentro un areglo que se encargará de continuar
        el ruteo de la aplicación, para obtener el array se puede hacer uso de las mismas tecnicas de require
        o carga peresoza que en el archivo de configuración. Cabe mencionar que aunque sea posible realizar la carga peresoza desde
        la propiedad route no es una practica recomendable dado que desde el principio se deberan cargar todas las rutas y
        de esta manera la carga peresoza perdera toda su utilidad.</p>

<pre><code class="language-php">[
    'doc' => [
        'url' => '/documentation/'
        'routes' => require 'routes/docs.php'
    ]
]
</code></pre>

    <p>Las url establecidas dentro de una subruta heredaran automaticamente la url de la ruta principal. De esta
    manera una url <code>routes/</code> dentro de la subruta <code>routes/docs</code> se accedera como
    <code>/documentation/routes/</code>.</p>
    </li>

    <li><h3 class="deprecated">methods</h3>
        <p class="doc-danger">La propiedad methods ha sido declara @deprecated desde la versión 0.6.1</p>
        <p>Finalmente tenemos la configuración de métodos, esta es opcional ya que como se menciono anteriormente si no se
        especifica ningún método el router intentara encontrar uno que se llame igual que el método http ejecutado. En caso
        de querer enmascarar el nombre del método o que varias peticiones compartan un controlador, se debe especificar en un
        arreglo el nombre del método http (get, post, put, delete...) en minuscula y el nombre del método del controlador.</p>

<pre><code class="language-php">[
    'doc' => [
        'url' => '/documentation/'
        'routes' => require 'routes/docs.php',
        'methods' => ['get' => 'view']
    ]
]
</code></pre>
    </li>
</ul>

<p>Todas estas propiedades se pueden combinar entre si, para generar un sistema robusto de enrutamiento.</p>
<pre><code class="language-php">[
    'home' => [
        'url' => '/',
        'controller' => 'Controller\Home:get',
        'proxy' => 'App\Interceptor\Test',
        'routes' => require 'routes/main.php'
    ]
]
</code></pre>

<h2>
    <a href="#app-routes">app/routes</a>
    <span class="anchor" id="app-routes">...</span>
</h2>

<p>Se ha tratado de simplificar y mejorar el sistema de enrutamiento inspirados en el manejado por NextJS.
De esta manera se basa en el sistema de archivos para la generación de las rutas lo primero es definir la carpeta 
que servira como base del enrutamiento, por defecto es app/routes pero puede ser configurada con el key <code>routes</code>.</p>

<p>Desde esta carpeta se empezaran a crear los archivos que conformaran el sistema de enrutamiento, estos son basicamente <code>endpoint.php</code>
y <code>midlewares.php</code> cada carpeta del sistema se tomara como parte de la url, el archivo endpoint servira como enrutador hacia el controlador
y contará con un id para continuar manejando la identificación de rutas; en caso de contar con la key id esta ruta no se indexara y no podra ser
identificada de manera dinamica para conocer su ubicación.</p>

<p>De esta manera el archivo <code>app/routes/endpoint.php</code> llevara a un contenido como el siguiente.</p>

<pre><code class="language-php">return [
    'id' => 'home',
    'controller' => 'App\Infraestructure\Controller\Get'
];
</code></pre>

<p>De igual manera se puede seguir usando el sistema para dividir peticiones en diferentes controladores.</p>

<pre><code class="language-php">return [
    'controller' => [
        'get' => 'Controller\UserReader',
        'post' => 'Controller\UserCreator',
        'put' => 'Controller\UserUpdater',
        'delete' => 'Controller\UserRemover'
    ]
];
</code></pre>

<p>El otro archivo es midlewares que a diferencia de los proxies manejados en anteriores versiones si usa el standard
<a href="https://www.php-fig.org/psr/psr-15/">PSR15</a>.</p>

<pre><code class="language-php">return [
    '*' => ['App\Infraestructure\Midleware\Auth']
];
</code></pre>

<p>Como se puede observar no es una simple lista de midleware a ejecutar dentro de la ruta indicada, si no que tiene una key
para identificar a que sub-rutas se les debe aplicar los midlewares; usando el comodin <b>*</b> se puede aplicar a todas. En caso de
agrupar varias rutas se puede hacer con el separador pipe(|).</p>

<pre><code class="language-php">return [
    'admin|secret' => ['App\Infraestructure\Midleware\Auth']
];
</code></pre>

<p>Con estas simples instrucciones se modifica el sistema antiguo de configuración mediante arrays, facilitando tanto el uso como la
organización, ya que ahora no sera posible crear en un solo array todo el sistema de rutas.</p>

<h2>
    <a href="#ioc">Inversión de control</a>
    <span class="anchor" id="ioc">...</span>
</h2>

<p>La inversión de control (Inversion of Control, IoC) es un principio de diseño de software en el
que el flujo de ejecución de un programa se invierte respecto a los métodos de programación tradicionales.
En los métodos de programación tradicionales el programador especifica la secuencia de decisiones y procedimientos
que pueden darse durante el ciclo de vida de un programa mediante llamadas a funciones. En su lugar, en la inversión
de control se especifican respuestas deseadas a sucesos o solicitudes de datos concretas, dejando que algún tipo de
entidad o arquitectura externa lleve a cabo las acciones de control que se requieran en el orden necesario y
para el conjunto de sucesos que tengan que ocurrir.</p>

<p>Una parte importante de usar la inversión de control es establecer como interpretar las abstracciones a
implementaciones, para esto scoop usa el método <code>bind</code> de la clase
<code>\Scoop\IoC\Injector</code>, dicho método recibe dos párametros, el primero es el nombre de
la interface y el segundo el nombre de la clase que implementa dicha interface.</p>

<pre><code class="language-php">\Scoop\Context::getInjector()->bind('\App\Repository\Quote', '\App\Repository\QuoteArray');</code></pre>

<p class="doc-danger">Este método se encuentra @deprecated desde la versión 0..6.1 en favor del siguiente.</p>

<p>De esta manera cada vez que se haga uso de la interface <code>\App\Repository\Quote</code> dentro de un entorno
IoC esta se traducira automaticamente a la clase <code>\App\Repository\QuoteArray</code>. Se recomienda el uso de 
archivos para separar logica de configuración, para tal fin se puede establecer un key providers cuyo valor sea un 
par clave valor [inteface => class].</p>

<pre><code class="language-php">[
    'providers' => [
        'App\Repository\Quote' => 'App\Repository\QuoteArray',
        'Scoop\Log\Logger' => 'Scoop\Log\factory\Logger:create'
    ]
]
</code></pre>

<p>Desde la versión <code>0.7.4</code> se pueden usar factorias para la creación de los objetos, para esto se
debe establecer el factory method mediante <b>:</b> como se observa en <code>Scoop\Log\factory\Logger:create</code>.
Una vez establecidas las reglas de transformación de interfaces o factorias cada vez que se inyecten, el sistema de
inversión sabra como resolverló.</p>

<pre><code class="language-php">class Logger
{
    private $environment;

    public function __construct(\Scoop\Bootstrap\Environment $environment)
    {
        $this->environment = $environment;
    }

    public function create()
    {
        return new \Scoop\Log\Logger(
            new \Scoop\Log\Factory\Handler(
                $this->environment->getConfig('log', array()),
                $this->environment->getConfig('storage', 'app/storage/')
                . 'logs/' . $this->environment->getConfig('app.name')
                . '-' . date('Y-m-d') . '.log'
            )
        );
    }
}
</code></pre>

<p class="doc-alert">Para más información revise la sección de <a href="{{#view->route('doc', 'architecture')}}#inject">inyección de dependencias</a>.</p>

<h3 class="deprecated">Servicios</h3>
<p class="doc-danger">Desde la versión 0.6.2 la configuración de servicios se encuentra @deprecated. Se recomienda el uso
de inyección de dependencias.</p>
<p>Los servicios no se deben confundir con las dependencias, una dependecia se debe inyectar a la clase mediante
el constructor, en cambio un servicio es nombrado y es posible acceder a este desde cualquier parte del sistema
(incluso las vistas).</p>

<p class="doc-alert">Desde la versión 0.6.2 se pueden <a href="{{#view->route('doc', 'frontend')}}#services">inyectar dependencias en las vistas</a>.</p>

<pre><code class="language-php">\Scoop\IoC\Service::register('auth', '\App\Controller\Auth');</code></pre>

<p>dentro del archivo de configuración se debe establecer un par [name => classService]</p>

<pre><code class="language-php">[
    'services' => [
        'auth' => '\App\Controller\Auth'
    ]
]
</code></pre>

<h2>
    <a href="#components">Componentes</a>
    <span class="anchor" id="components">...</span>
</h2>

<p>Los componentes en scoop son simples bloques de codigos HTML reutilizables y variables que se gestionan mediante
el uso de clases, cada componente tiene un nombre asociado dentro de la vista y un handler PHP, este par
[name => classHandler] se puede usar dentro de un archivo de configuración asociado mediante la clase de entorno o el uso
directo del método <code>View::registerComponents</code>.</p>

<pre><code class="language-php">[
    'components' => [
        'text' => 'App\Component\InputText'
    ]
]
</code></pre>

<p>Para usar un componente dentro de la vista se debe usar el método <code>&#123;{#view->compose{ComponentName}()}&#125;</code>.</p>

<h2>
    <a href="#exceptions">Manejo de excepciones</a>
    <span class="anchor" id="exceptions">...</span>
</h2>

<p>Cualquier tipo de excepción puede ser mapeado a un error https mediante las keys <code>http.errors.${code}.exceptions</code>. Si se desea
agregar más cabeceras se debe agregar el key headers al codigo de error, así como la configuración del titulo (en caso de manejar vistas).</p>

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

<h2>
    <a href="#cors">CORS</a>
    <span class="anchor" id="cors">...</span>
</h2>

<pre><code class="language-php">[
    'cors' => [
        'origin' => 'https://sespesoft.com,http://localhost',
        'methods' => 'POST,PUT,GET',
        'headers' => 'Authorization'
    ]
]
</code></pre>
