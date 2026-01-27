<p>Las vistas en Scoop se gestionan mediante el motor SDT (Scoop Dynamic Template). Su función es actuar como un aislante térmico entre la presentación y la lógica de aplicación. Un archivo <code>.sdt.php</code> es transpilado a PHP nativo y almacenado en caché, aprovechando al máximo el Opcache del servidor.</p>

<p class="doc-alert">Aunque sea posible usar código PHP directamente sobre la plantilla, no es un uso aconsejable para mantener la pureza de la arquitectura.</p>

<ul>
    <li><a href="#assets">Gestión de activos</a></li>
    <li><a href="#interpolation">Interpolación</a></li>
    <li><a href="#control">Estructuras de control</a></li>
    <li><a href="#special">Estructuras especiales</a></li>
    <li><a href="#components">Arquitectura de componentes</a></li>
    <li><a href="#cache">Cache y minificación</a></li>
</ul>

<h2>
    <a href="#assets">Gestión de activos</a>
    <span class="anchor" id="assets">...</span>
</h2>


<p>Scoop abstrae la ubicación física de los recursos (CSS, JS, imágenes) permitiendo que la aplicación sea portable. Desde la versión 0.8, el motor integra <b>Vite</b> para gestionar el <i>Hot Module Replacement</i> (HMR) en desarrollo y la carga de activos optimizados en producción. La configuración de rutas se define de forma sencilla bajo la clave <code>asset</code>:</p>

<pre><code class="language-php">[
    'asset' => [
        'path' => 'public/',
        'css' => 'css/',
        'js' => 'js/',
        'img' => 'images/'
    ]
]
</code></pre>

<p>Para referenciar un recurso, se utilizan los helpers del objeto <code>view</code>. El motor resolverá automáticamente la ruta basándose en el entorno (Vite Dev Server o Production Build):</p>

<pre><code class="language-php-template">&lt;link rel="stylesheet" href="&#123;{#view->css('main.css')}&#125;"&gt;
&lt;script src="&#123;{#view->js('app.js')}&#125;"&gt;&lt;/script&gt;
</code></pre>

<h2>
    <a href="#interpolation">Interpolación</a>
    <span class="anchor" id="interpolation">...</span>
</h2>

<p>La vinculación de datos se realiza mediante dobles llaves. Scoop aplica una política de <b>seguridad por defecto</b>:</p>

<ul>
    <li><b><code>&#123;{$var}&#125;</code>:</b> Salida escapada automáticamente mediante <code>htmlspecialchars</code> para prevenir ataques XSS.</li>
    <li><b><code>&#123;{=$var}&#125;</code>:</b> Salida en crudo (Raw). Debe usarse con precaución y solo con contenido de total confianza.</li>
</ul>

<p>Se permite el uso de expresiones PHP y operadores ternarios dentro de las llaves para transformaciones rápidas de presentación:</p>

<pre><code class="language-php-template">&lt;h1&gt;&#123;{ strtolower($title) }&#125;&lt;/h1&gt;
&lt;p&gt;&#123;{ count($items) > 0 ? 'Listado disponible' : 'Vacio' }&#125;&lt;/p&gt;
</code></pre>

<h2>
    <a href="#control">Estructuras de control</a>
    <span class="anchor" id="control">...</span>
</h2>
<p>SDT utiliza una sintaxis simétrica diseñada para facilitar el parseo y la legibilidad. Cada estructura inicia con el símbolo <code>@</code> y finaliza con un delimitador estructural <code>:</code>. Es imperativo que cada apertura y cierre se realice en su propia línea.</p>

<h3>&#64;foreach</h3>

<pre><code class="language-php-template">&lt;table&gt;
    &lt;thead&gt;
        &lt;tr&gt;
            &lt;th&gt;Key&lt;/th&gt;
            &lt;th&gt;Value&lt;/th&gt;
        &lt;/tr&gt;
    &lt;/thead&gt;
    &lt;tbody&gt;
        &#64;foreach $array as $key =&gt; $value
            &lt;tr&gt;
                &lt;td&gt;&#123;{$key}&#125;&lt;/td&gt;
                &lt;td&gt;&#123;{$value}&#125;&lt;/td&gt;
            &lt;/tr&gt;
        &#58;foreach
    &lt;/tbody&gt;
&lt;/table&gt;
</code></pre>

<h3>&#64;for</h3>

<pre><code class="language-php-template">&lt;ul&gt;
    &#64;for $i = 0; $i &gt;= $length; $i++
        &lt;li&gt;Item &#123;{$i}&#125;&lt;/li&gt;
    &#58;for
&lt;/ul&gt;
</code></pre>

<h3>&#64;while</h3>

<pre><code class="language-php-template">&lt;pre&gt;
    &#64;while feof($file)
        &#123;{$fgetc($file)}&#125;
    &#58;while
&lt;/pre&gt;
</code></pre>

<h3>&#64;if</h3>

<pre><code class="language-php-template">&#64;if isset($user)
    &lt;h1&gt;Welcome &#123;{$user}&#125;!&lt;/h1&gt;
&#58;if
</code></pre>

<h3>&#64;else</h3>

<pre><code class="language-php-template">&lt;header&gt;
    &#64;if isset($user)
        &lt;h1&gt;Welcome &#123;{$user}&#125;!&lt;/h1&gt;
    &#64;else
        &lt;a href="&#123;{&#35;view->route('login')}&#125;"&gt;Login&lt;/a&gt;
    &#58;if
&lt;/header&gt;
</code></pre>

<h3>&#64;elseif</h3>

<pre><code class="language-php-template">&lt;header&gt;
    &#64;if isset($user)
        &lt;h1&gt;Welcome &#123;{$user}&#125;!&lt;/h1&gt;
    &#64;elseif isset($guess)
        &lt;h1&gt;Welcome &#123;{$guess}&#125;!&lt;/h1&gt;
    &#64;else
        &lt;a href="&#123;{&#35;view->route('login')}&#125;"&gt;Login&lt;/a&gt;
    &#58;if
&lt;/header&gt;
</code></pre>

<h2>
    <a href="#special">Estructuras especiales</a>
    <span class="anchor" id="special">...</span>
</h2>

<h3>&#64;extends</h3>

<p>Define la base estructural de la herencia de plantillas.</p>

<pre><code class="language-php-template">&#64;extends 'layers/layer'</code></pre>

<h3>&#64;slot</h3>

<p>Define un punto de inyección en el layout padre. Se pueden usar slots nombrados para organizar bloques de contenido específicos (<code>@slot[header]</code>).</p>

<h3>&#64;block</h3>

<p>Encapsula contenido destinado a un slot nombrado específico en el layout padre.</p>

<pre><code class="language-php-template">&#64;block[header]
    &lt;h1&gt;Hello World&lt;/h1&gt;
&#58;block</code></pre>

<h3>&#64;import</h3>

<p>Para incluir archivos parciales sin lógica adicional:</p>

<pre><code class="language-php-template">&#64;import 'partition/login'</code></pre>

<h3>&#64;inject</h3>

<p>Scoop permite que la vista sea proactiva inyectando sus propias dependencias de infraestructura. El símbolo <code>#</code> identifica un servicio resuelto por el <b>Injector</b>.</p>

<pre><code class="language-php-template">&#64;inject \App\Service\Provider#provider
...
&lt;h1&gt;Hello &#123;{&#35;provider->getName()}&#125;&lt;/h1&gt;
</code></pre>

<h2>
    <a href="#components">Arquitectura de componentes</a>
    <span class="anchor" id="components">...</span>
</h2>

<p>Los componentes en Scoop son <b>Custom Elements</b> prefijados con <code>sc-</code> y con su etiqueta de cierre. Permiten encapsular HTML y lógica en unidades reutilizables.</p>

<p>Para registrar un componente, se debe agregar su clase al array <code>components</code> en la configuración de la vista.</p>

<pre><code class="language-php">[
    'components' => [
        'text' => 'App\Component\InputText'
    ]
]
</code></pre>

<h3>Componentes de Vista (Template-only)</h3>

<p>Permiten reutilizar plantillas existentes como componentes sin necesidad de una clase controladora. Se invocan usando el prefijo <code>sc-view.</code> seguido del path del archivo (donde los puntos se traducen en separadores de carpeta).</p>

<pre><code class="language-php-template">&lt;sc-view.partials.card title="Producto" price={$price}&gt;
    &lt;p&gt;Descripción del producto...&lt;/p&gt;
&lt;/sc-view.partials.card&gt;
</code></pre>

<h3>Componentes Basados en Clase</h3>

<p>Un componente es una clase que puede recibir dependencias y debe implementar un método <code>render()</code> que retorne una instancia de <code>View</code>.</p>

<pre><code class="language-php">class Message
{
    const INFO = 'info';
    const SUCCESS = 'success';
    const ERROR = 'error';
    const WARNING = 'warning';
    private $request;

    public function __construct(\Scoop\Http\Message\Server\Request $request)
    {
        $this->request = $request;
    }

    public function render()
    {
        $message = (array) $this->request->flash()->get('message') + array('type' => 'not', 'text' => '');
        $view = new \Scoop\View('message');
        return $view->add('message', $message);
    }
}
</code></pre>

<h3>Paso de Propiedades (Props)</h3>

<p>Al invocar un componente, Scoop permite enviar información de dos formas distintas, diferenciando entre datos estáticos y expresiones evaluadas:</p>

<h4>Propiedades Literales (Strings)</h4>

<p>Se definen usando comillas dobles <code>""</code> o simples <code>''</code>. El valor se pasa al componente como una cadena de texto estática.</p>

<pre><code class="language-php-template">&lt;sc-button type="submit" label="Guardar cambios"&gt;&lt;/sc-button&gt;</code></pre>

<h4>Propiedades Dinámicas (Expresiones)</h4>

<p>Se definen envolviendo el valor en llaves <code>{}</code>. El contenido dentro de las llaves es tratado como <b>código PHP real</b> y se evalúa antes de ser entregado al componente.</p>

<pre><code class="language-php-template">&lt;sc-user-profile
user={$user};
isAdmin={$user->hasRole('admin')}
theme="dark"
&gt;&lt;/sc-user-profile&gt;</code></pre>

<h3>Uso en el Template</h3>

<p>Se invocan como etiquetas HTML, permitiendo el envío de bloques de contenido a slots internos.</p>
<pre><code class="language-php-template">&lt;sc-message /&gt;

&lt;sc-modal title="Borrar registro"&gt;
    @block[footer]
        &lt;button&gt;Confirmar&lt;/button&gt;
    :block
    &lt;p&gt;¿Desea eliminar este elemento?&lt;/p&gt;
&lt;/sc-modal&gt;
</code></pre>

<h2>
    <a href="#cache">Cache y minificación</a>
    <span class="anchor" id="cache">...</span>
</h2>

<p>Durante la fase de transpilación, Scoop aplica optimizaciones agresivas:</p>

<ol>
    <li><b>Limpieza de HTML:</b> Elimina comentarios y espacios en blanco redundantes, reduciendo el peso del archivo enviado al cliente.</li>
    <li><b>Pre-compilación:</b> Convierte directivas SDT en sentencias PHP nativas listas para <b>Opcache</b>.</li>
    <li><b>Aislamiento de Scope:</b> Cada vista se renderiza en su propia instancia de objeto, evitando colisiones de variables globales.</li>
</ol>
