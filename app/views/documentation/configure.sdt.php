@extends 'layers/docs'
<p>La configuración de una aplicación scoop se basa en clases que ayudan a establecer un entorno adecuado para su ejecución, dentro de la clase se debe determinar la ubicación de los archivos de configuración, la manera en como se
enlazan las interfaces y las clases, registrar servicios y definir todas las variables que afectaran el desarrollo.</p>

<h2>
    <a href="#routes-config">Configuración</a>
    <span class="anchor" id="routes-config">...</span>
</h2>

<p>Desde las clases de entorno se puede establecer el origen del archivo de configuración, que no es otra cosa que un array con los parametros basicos de la aplicación. Para establecer la ruta al archivo basta con ejecutar el constructor  de la clase <code>\Scoop\Bootstrap\Environment</code> de la cual hereda el entorno.</p>

<pre class="prettyprint">
namespace App;

class Production extends \Scoop\Bootstrap\Environment
{
    public function __construct()
    {
        parent::__construct('app/config'));
    }
}
</pre>

<p class="doc-alert">A pesar que scoop prefiere usar convención sobre configuración es lo suficientemente flexible para permitir un gran nivel de personalización mediante este archivo, aca solo se estudiaran las configuraciones basicas con las que cuenta el bootstrap, para profundizar en la variedad de posibilidades que presenta scoop le recomendamos visitar la sección ajustes avanzados.</p>

<p>El archivo config.php o archivo de configuración esablece los ajustes basicos para el correcto funcionamiento de la aplicación, aquí se encuentran datos para el acceso al sistema de persistencia, rutas, mensajes de error, entre muchos más. Se pueden extender a otros archivos mediante <code>require</code> o <code>file_gets_content</code>.</p>

<pre class="prettyprint">
return array(
    'routes' => require 'config/routes.php'
);
</pre>

<p>Aparte del manejo de rutas scoop cuenta con los siguientes parametros configurables dentro del bootstrap:</p>

<ul>
    <li><h3>app</h3>

        <p>dentro de app se pueden establecer todas las variables de entorno a las que puede acceder la
        aplicación, aqui se encuentran variables como name y version, una tecnica que utiliza scoop para
        establecer variables es usar package.json como archivo de configuración.</p>

<pre class="prettyprint">
return array(
    'app' => json_decode(file_get_contents(
        __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'package.json'
    ), true)
);
</pre>
    </li>

    <li><h3>db</h3>

        <p>Scoop soporta multiples instancias de base de datos, con lo cual se pueden tener dentro de una misma aplicación
        diferentes conexiones cada una de ellas debe ser establecida mediante db en el archivo de configuración, por defecto
        se usara la conexión default.</p>

<pre class="prettyprint">
return array(
    'db' => array(
        'default' => array(
            'database' => 'scoop',
            'user' => 'scoop',
            'password' => '1s4Gr34tB00t5tr4p',
            'host' => 'localhost',
            'driver' => 'pgsql'
        ),
        'auth' => array(
            'database' => 'auth',
            'user' => 'scoop',
            'password' => 'myS1st3m4uth',
            'host' => 'localhost',
            'driver' => 'mysql'
        )
    )
);
</pre>

        <p class="doc-alert">Desde la versión <code>0.2.1</code> no se cuenta con soporte nativo para drivers
        diferentes a los suministrados por PDO.</p>
    </li>

    <li><h3>asset</h3>

        <p>Finalmente se tiene la configuración de assets, estos se usan principalmente en las vistas para
        ubicar los recursos publicos de la aplicación (Archivos css, javascript, imagenes, etc). El uso de asset
        dentro de scoop es muy sencillo, la ruta principal de los archivos se ubica en <code>asset.path</code>,
        el resto de parametros son rutas relativas que parten de esta ruta principal.</p>

<pre class="prettyprint">
return array(
    'asset' => array(
        'path' => 'public/',
        'css' => 'css/',
        'js' => 'js/',
        'img' => 'images/'
    )
);
</pre>

        <p>En el anterior ejemplo para referirse al archivo stylesheet.css se debe seguir la ruta
        <code>public/css/stylesheeet.css</code> y para acceder a esta desde una vista basta con solo
        colocar <code>#view->css('stylesheet.css')</code>.</p>
    </li>
</ul>

<h2>
    <a href="#routes">Rutas</a>
    <span class="anchor" id="routes">...</span>
</h2>

<p class="doc-alert">Desde la version <code>0.2.2</code> cambio drasticamente el sistema de enrutamiento
del bootstrap, para favorecer la inclusión de proxies y alias en las rutas.</p>

<p>Dentro del archivo routes.php se establecen las propiedades de una ruta, no es
un sistema de ruteo simple como en anteriores versiones, si no que establece una serie de caracteristicas
como la interceptación de peticiones y generación de rutas dinamicamente, sin sacrificar en ningún momento
la funcionalidad y caracteristicas que tenia el anterior sistema.</p>

<p>La configuración de rutas no solo se limita a indicarle al bootstrap hacia que controlador debe dirigir la
petición. Se modifico el funcionamiento del array en donde la clave era la ruta y el valor era el controlador,
ahora la clave es el alias de la ruta y el valor un array asociativo con las siguientes propiedades:</p>

<ul>
    <li><h3>url</h3>
        <p>Es la propiedad principal de la ruta e indica la composición del path, es la unica propiedad obligatoria.
        Se ha abandonado la idea de un enrutamiento hibrido o compartido por lo cual se deben especificar dentro
        de la ruta datos como los tipos de variables que seran suministradas al controlador.</p>

<pre class="prettyprint">
return array(
    'user' => array(
        'url' => '/user/&#123;var&#125;/'
    )
);
</pre>

        <p>El uso de variables se limita a dos tipo: <code>&#123;var&#125;</code> e <code>&#123;int&#125;</code>, en el
        primero se puede suministrar cualquier tipo de dato consistente con el formato url y el segundo filtra solo
        valores númericos, scoop toma todas las variables como parametros opcionales hacia el controlador, es este
        último el que debe establecer cuales son realmente opcionales y cuales obligatorios.</p>
    </li>

    <li><h3>controller</h3>
        <p>Establece el controlador hacia el cual debe apuntar la ruta, ahora se debe indicar hasta que metodo
        del controlador se debe enrutar la petición con el uso del caracter <code>:</code>.</p>

<pre class="prettyprint">
return array(
    'user' => array(
        'url' => '/user/&#123;var&#125;/',
        'controller' => 'Controller\User:get'
    )
);
</pre>
        <p>En caso que el controller no posea el caracter este debe implementar la interface <i>Resource</i>.</p>
    </li>

    <li><h3>proxy</h3>
        <p>Los proxies son simples metodos que interactuan con la petición antes que esta llegue hasta el
        controlador, la interceptación de la petición es acumulativa, lo que quiere decir que todos los
        proxies establecidos en rutas anteriores son ejecutados en orden desde la ruta más corta hasta
        la más larga.</p>
<pre class="prettyprint">
return array(
    'user' => array(
        'url' => '/user/&#123;var&#125;/',
        'proxy' => 'App\Interceptor\Test:validateUser'
    ),
    'home' => array(
        'url' => '/',
        'proxy' => 'App\Interceptor\Test:auth'
    )
);
</pre>
        <p>En el ejemplo anterior primero se ejecuta el metodo <code>auth</code> de la clase
        <code>/App/Interceptor/Test</code> y luego el metodo <code>validateUser</code> de la misma clase.</p>
    </li>

    <li><h3>routes</h3>
        <p>El sistema de enrutamiento que maneja scoop es fragmentado al igual que sucede con el archivo config.php.
        Para hacer uso de este sistema se debe establecer la propiedad routes y dentro un array que continuara
        el ruteo de la aplicación, para obtener el array se puede hacer uso de las mismas tecnicas de <code>require</code>
        o <code>file_gets_content</code> que en el archivo de configuración.</p>

<pre class="prettyprint">
return array(
    'doc' => array(
        'url' => '/documentation/'
        'routes' => require 'routes/docs.php'
    )
);
</pre>

    <p>Las url establecidas dentro de una subruta heredaran automaticamente la url de la ruta principal. De esta
    manera una url <code>routes/</code> dentro de la subruta <code>routes/docs</code> se accedera como
    <code>/documentation/routes/</code>.</p>
    </li>
</ul>

<p>Todas estas propiedades se pueden combinar entre si, para generar un sistema robusto de enrutamiento.</p>
<pre class="prettyprint">
return array(
    'home' => array(
        'url' => '/',
        'controller' => 'Controller\Home:get',
        'interceptor' => 'App\Interceptor\Test:auth',
        'routes' => require 'routes/main.php'
    )
);
</pre>


<h2>
    <a href="#inject-parameters">Inyección de dependencias</a>
    <span class="anchor" id="inject-parameters">...</span>
</h2>

<p>Una parte importante de usar la inversión de control es establecer como traducir las interfaces a
implementaciones, para esto scoop usa el método <code>bind</code> de la clase
<code>\Scoop\IoC\Injector</code>, dicho método recibe dos párametros, el primero es el nombre de
la interface y el segundo el nombre de la clase que implementa dicha interface.</p>

<pre class="prettyprint">
\Scoop\IoC\Injector::bind('\App\Repository\Quote', '\App\Repository\QuoteArray');
</pre>

<p>De esta manera cada vez que se use la interface <code>\App\Repository\Quote</code> dentro de un entorno
IoC esta se traducira automaticamente a la clase <code>\App\Repository\QuoteArray</code>. Aunque esta manera de enlazar interfaces es funcional se recomienda el uso de archivos para separar logica de configuración, para tal fin se puede usar el método <code>bind</code> de la clase <code>\Scoop\Bootstrap\Environment</code>, la cual debe extender la clases de entorno.</p>

<pre class="prettyprint">
class Test extends \Scoop\Bootstrap\Environment
{
    public function __construct()
    {
        ...
        $this->bind('app/config/dependencies');
    }
}
</pre>

<p>El archivo de configuración se establece con un par [inteface => class].</p>

<pre class="prettyprint">
return array(
    'App\Repository\Quote' => 'App\Repository\QuoteArray'
);
</pre>

<h2>
    <a href="#components">Componentes</a>
    <span class="anchor" id="components">...</span>
</h2>

<p>Los componentes en scoop son simples bloques de codigos HTML reutilizables y variables que se gestionan mediante el uso de clases, cada componente tiene un nombre asociado dentro de la vista y un handler PHP, este par [nombre => handler] se puede usar dentro de un archivo de configuración asociado mediante la clase de entorno (de manera similar a la <a href="#inject-parameters">inyección de dependencias</a>) o el uso directo del método <code>registerComponents</code> de la clase <code>\Scoop\View</code>.</p>
