<p>Scoop se basa en el paradigma de convención sobre configuración, lo que busca minimizar el número de decisiones que
un desarrollador necesita hacer, ganando simplicidad pero sin abandonar flexibilidad. Lamentablemente existen aspectos
no convencionales de la aplicación que se deben especificar y es aqui donde entra el sistema de configuración.</p>

<ul>
    <li><a href="#routes-config">Contexto y entorno</a></li>
    <li><a href="#basic-config">Configuraciones básicas</a></li>
    <li><a href="#routes">Rutas</a></li>
    <li><a href="#ioc">Inversión de control</a></li>
    <li><a href="#components">Componentes</a></li>
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

<pre class="prettyprint">
\Scoop\Context::load('app/config');
$app = new \Scoop\Bootstrap\Application();
echo $app->run();
</pre>

<p>Cuando un contexto es establecido se puede acceder al entorno mediante su método
<code>\Scoop\Context::getEnvironment()</code>, al cargador mediante <code>\Scoop\Context::getLoader()</code> y
 al inyector de dependencias mediante <code>\Scoop\Context::getInjector()</code>, tambien se pueden establecer
 conexiones y desconexiones a la base de datos mediante <code>\Scoop\Context::connect()</code> y <code>\Scoop\Context::disconnect()</code>
 respectivamente.</p>

 <p class="doc-danger">Desde la versión 0.6.4 el método <code>getInjector</code> se encuentra @deprecated en
 favor del método <code>\Scoop\Context::inject($dependency)</code>.</p>

 <p>Para realizar la conexión a diferentes bases de datos se pueden enviar dos argumentos más a <code>\Scoop\Context::connect($bundle, $options)</code>,
 lo mismo sucede con el método <code>\Scoop\Context::disconnect($bundle, $options)</code> para más información consulta <a href="{{#view->route('doc', 'ddd')}}#dbc">el uso del DBC</a>.</p>

<h2>
    <a href="#basic-config">Configuraciones básicas</a>
    <span class="anchor" id="basic-config">...</span>
</h2>

<p>El archivo de configuración establece los ajustes para el correcto funcionamiento de la aplicación,
aquí se encuentran datos para el acceso al sistema de persistencia, rutas, mensajes de error, entre muchos más.
Se pueden extender a otros archivos mediante <code>require</code> o carga perezosa.</p>

<pre class="prettyprint">
['routes' => require 'config/routes.php']
</pre>

<h3 id="lazy-loading">Carga peresoza</h3>

<p>Otra posibilidad de extender la configuración es mediante la carga peresoza de archivos, esta en vez de usar directamente
un  método de importanción como <code>require</code> hace uso de claves como <code>import</code> o <code>json</code>.</p>

<pre class="prettyprint">
['routes' => 'import:app/config/route']
</pre>

<p>Dentro de las diferencias a destacar en la carga peresoza es que se debe referenciar el archivo a cargar desde la raíz
del proyecto y no sobre el archivo donde se esta configurando el arreglo, la segunda es el uso de <code>:</code> para
separar el método de carga con la url del archivo y la última es la ausencia de extención para el tipo de archivo, esto se
debe a que cada método de carga tiene su propio tipo de extensión, así el método json solo cargara archivos con esta extensión,
mientras import hará lo mismo con los archivos .php.</p>

<h3>app</h3>

<p>dentro de app se pueden establecer todas las variables de entorno a las que puede acceder la aplicación, aqui se
encuentran variables como name y version, una tecnica que utiliza scoop para establecer variables es usar
package.json como archivo de configuración.</p>

<pre class="prettyprint">
['app' => 'json:package']
</pre>

<h3>db</h3>

<p>Scoop soporta multiples instancias de base de datos, con lo cual se pueden tener dentro de una misma aplicación
diferentes conexiones cada una de ellas debe ser establecida mediante db en el archivo de configuración, por defecto
se usara la conexión default.</p>

<pre class="prettyprint">
[
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
</pre>

<p class="doc-alert">Desde la versión <code>0.2.1</code> no se cuenta con soporte nativo para drivers diferentes a
los suministrados por PDO.</p>

<h3>messages</h3>

<p>Por defecto scoop mostrara un mensaje si este no se encuentra dentro del archivo de configuración, se pueden
manejar técnicas de internacionalización realizando la respectiva separación de mensajes por idioma, este tema
escapa al manejo de la herramienta, pero presta las condiciones para su implementación.</p>

<pre class="prettyprint">
[
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
</pre>

<h3>asset</h3>

<p>Finalmente se tiene la configuración de assets, estos se usan principalmente en las vistas para ubicar los recursos
publicos de la aplicación (Archivos css, javascript, imagenes, etc). El uso de asset dentro de scoop es muy sencillo,
la ruta principal de los archivos se ubica en <code>asset.path</code>, el resto de parametros son rutas relativas que
parten de esta ruta principal.</p>

<pre class="prettyprint">
[
    'asset' => [
        'path' => 'public/',
        'css' => 'css/',
        'js' => 'js/',
        'img' => 'images/'
    ]
]
</pre>

<p>En el anterior ejemplo para referirse al archivo stylesheet.css se debe seguir la ruta
<code>public/css/stylesheeet.css</code> y para acceder a esta desde una vista basta con solo colocar
<code>&#123;{#view->css('stylesheet.css')}&#125;</code>, por defecto se usa la configuración de ejemplo para ubicar los assets.</p>

<h2>
    <a href="#routes">Rutas</a>
    <span class="anchor" id="routes">...</span>
</h2>

<p class="doc-alert">Desde la version <code>0.2.2</code> cambio drasticamente el sistema de enrutamiento
del bootstrap, para favorecer la inclusión de proxies y alias en las rutas.</p>

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

<pre class="prettyprint">
[
    'user' => [
        'url' => '/user/{var}/'
    ]
]
</pre>

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

<pre class="prettyprint">
[
    'user' => [
        'url' => '/user/&#123;var&#125;/',
        'controller' => 'Controller\User'
    ]
]
</pre>

<p class="doc-alert">Desde la version <code>0.6.1</code> es posible separar los métodos HTTP en diferentes controladores,
esto beneficia el principio de single responsability.</p>

<pre class="prettyprint">
[
    'user' => [
        'url' => '/user/&#123;var&#125;/',
        'controller' => [
            'get' => 'Controller\UserReader',
            'post' => 'Controller\UserCreator',
            'put' => 'Controller\UserUpdater',
            'delete' => 'Controller\UserRemover'
        ]
    ]
]
</pre>
    </li>

    <li><h3>proxy</h3>
        <p>Los proxies son simples metodos que interactuan con la petición antes que esta llegue hasta el
        controlador, la interceptación de la petición es acumulativa, lo que quiere decir que todos los
        proxies establecidos en rutas anteriores son ejecutados en orden desde la ruta más corta hasta
        la más larga.</p>
<pre class="prettyprint">
[
    'user' => [
        'url' => '/user/{var}/',
        'proxy' => 'App\Interceptor\Verify'
    ],
    'home' => [
        'url' => '/user',
        'proxy' => 'App\Interceptor\Auth'
    ]
]
</pre>
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

<pre class="prettyprint">
[
    'doc' => [
        'url' => '/documentation/'
        'routes' => require 'routes/docs.php'
    ]
]
</pre>

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

<pre class="prettyprint">
[
    'doc' => [
        'url' => '/documentation/'
        'routes' => require 'routes/docs.php',
        'methods' => ['get' => 'view']
    ]
]
</pre>
    </li>
</ul>

<p>Todas estas propiedades se pueden combinar entre si, para generar un sistema robusto de enrutamiento.</p>
<pre class="prettyprint">
[
    'home' => [
        'url' => '/',
        'controller' => 'Controller\Home:get',
        'proxy' => 'App\Interceptor\Test',
        'routes' => require 'routes/main.php'
    ]
]
</pre>


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

<pre class="prettyprint">
\Scoop\Context::getInjector()->bind('\App\Repository\Quote', '\App\Repository\QuoteArray');
</pre>

<p class="doc-danger">Este método se encuentra @deprecated desde la versión 0..6.1 en favor del siguiente.</p>

<p>De esta manera cada vez que se use la interface <code>\App\Repository\Quote</code> dentro de un entorno
IoC esta se traducira automaticamente a la clase <code>\App\Repository\QuoteArray</code>. Aunque esta manera
de enlazar interfaces es funcional se recomienda el uso de archivos para separar logica de configuración,
para tal fin se puede establecer un key providers cuyo valor sea un par clave valor [inteface => class].</p>

<pre class="prettyprint">
[
    'providers' => [
        'App\Repository\Quote' => 'App\Repository\QuoteArray'
    ]
]
</pre>

<p>Finalmente para hacer uso de la dependencia, esta se debe recibir como argumento del contructor en la clase que
se desee.</p>

<h3 class="deprecated">Servicios</h3>
<p class="doc-danger">Desde la versión 0.6.2 la configuración de servicios se encuentra @deprecated. Se recomienda el uso
de inyección de dependencias.</p>
<p>Los servicios no se deben confundir con las dependencias, una dependecia se debe inyectar a la clase mediante
el constructor, en cambio un servicio es nombrado y es posible acceder a este desde cualquier parte del sistema
(incluso las vistas).</p>

<p class="doc-alert">Desde la versión 0.6.2 se pueden <a href="{{#view->route('doc', 'frontend')}}#services">inyectar dependencias en las vistas</a>.</p>

<pre class="prettyprint">
\Scoop\IoC\Service::register('auth', '\App\Controller\Auth');
</pre>

<p>dentro del archivo de configuración se debe establecer un par [name => classService]</p>

<pre class="prettyprint">
[
    'services' => [
        'auth' => '\App\Controller\Auth'
    ]
]
</pre>

<h2>
    <a href="#components">Componentes</a>
    <span class="anchor" id="components">...</span>
</h2>

<p>Los componentes en scoop son simples bloques de codigos HTML reutilizables y variables que se gestionan mediante
el uso de clases, cada componente tiene un nombre asociado dentro de la vista y un handler PHP, este par
[name => classHandler] se puede usar dentro de un archivo de configuración asociado mediante la clase de entorno o el uso
directo del método <code>\Scoop\View::registerComponents($components)</code>.</p>

<pre class="prettyprint">
[
    'components' => [
        'text' => 'App\Component\InputText'
    ]
]
</pre>

<p>Para usar un componente dentro de la vista se debe usar el método <code>&#123;{#view->compose{ComponentName}()}&#125;</code>.</p>
