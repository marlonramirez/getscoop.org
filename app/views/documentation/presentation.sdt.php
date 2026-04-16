<p>Las vistas en Scoop se gestionan mediante el motor SDT (Scoop Dynamic Template). Su función es actuar como un aislante térmico entre la presentación y la lógica de aplicación. Un archivo <code>.sdt.php</code> es transpilado a PHP nativo y almacenado en caché, aprovechando al máximo el Opcache del servidor.</p>

<p class="doc-alert">Aunque sea posible usar código PHP directamente sobre la plantilla, no es un uso aconsejable para mantener la pureza de la arquitectura.</p>

<p><ul>
    <li><a href="#assets">Gestión de activos</a></li>
    <li><a href="#interpolation">Interpolación</a></li>
    <li><a href="#control">Estructuras de control</a></li>
    <li><a href="#special">Estructuras especiales</a></li>
    <li><a href="#security-directives">Directivas de seguridad</a></li>
    <li><a href="#components">Arquitectura de componentes</a></li>
    <li><a href="#cache">Cache y minificación</a></li>
</ul></p>

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

<p><ul>
    <li><b><code>&#123;{$var}&#125;</code>:</b> Salida escapada automáticamente mediante <code>htmlspecialchars</code> para prevenir ataques XSS.</li>
    <li><b><code>&#123;{=$var}&#125;</code>:</b> Salida en crudo (Raw). Debe usarse con precaución y solo con contenido de total confianza.</li>
</ul></p>

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
    <a href="#security-directives">Directivas de seguridad</a>
    <span class="anchor" id="security-directives">...</span>
</h2>

<p>Scoop proporciona directivas especializadas para implementar patrones de seguridad web estándar de forma automática.</p>

<h3>&#64;csrf</h3>

<p>La directiva <code>&#64;csrf</code> inyecta automáticamente un token de protección CSRF (Cross-Site Request Forgery) en el lugar correcto según el contexto. Scoop detecta si la directiva está dentro de <code>&lt;head&gt;</code> o dentro de un <code>&lt;form&gt;</code> y genera el código apropiado:</p>

<h4>Uso en &lt;head&gt; (para AJAX)</h4>

<pre><code class="language-php-template">&lt;head&gt;
    &lt;title&gt;Mi Aplicación&lt;/title&gt;
    &#64;csrf
&lt;/head&gt;
</code></pre>

<p><b>Genera:</b></p>

<pre><code class="language-html">&lt;meta name="csrf-token" content="a1b2c3d4..."&gt;
</code></pre>

<p>Este meta tag puede ser leído por código JavaScript para incluir el token en peticiones AJAX:</p>

<pre><code class="language-javascript">const token = document.querySelector('meta[name="csrf-token"]').content;

fetch('/api/users', {
    method: 'POST',
    headers: {
        'X-CSRF-Token': token,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({name: 'John'})
});
</code></pre>

<h4>Uso en Formularios</h4>

<pre><code class="language-php-template">&lt;form method="POST" action="/users"&gt;
    &#64;csrf
    &lt;input type="text" name="name"&gt;
    &lt;input type="email" name="email"&gt;
    &lt;button type="submit"&gt;Enviar&lt;/button&gt;
&lt;/form&gt;
</code></pre>

<p><b>Genera:</b></p>

<pre><code class="language-html">&lt;input type="hidden" name="csrf-token" value="a1b2c3d4..."&gt;
</code></pre>

<h4>Validación Automática</h4>

<p>Cuando el middleware <code>CsrfGuard</code> está habilitado, Scoop valida automáticamente el token en todas las peticiones que modifican datos (POST, PUT, PATCH, DELETE). No requiere configuración adicional en los controladores.</p>

<pre><code class="language-php">[
    'middlewares' => [
        '\\Scoop\\Security\\Middleware\\CsrfGuard',
    ]
]
</code></pre>

<p class="doc-alert"><b>Importante:</b> La directiva <code>&#64;csrf</code> solo inyecta el token. El middleware <code>CsrfGuard</code> es necesario para validarlo en el servidor. Sin el middleware, el token se genera pero no se valida.</p>

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

<h3>Invocación sin azúcar sintáctico</h3>

<p>La etiqueta <code>&lt;sc-*&gt;</code> es una conveniencia del compilador SDT. En tiempo de ejecución se traduce a una llamada directa al método <code>#view->compose()</code>, que acepta tres parámetros: el nombre del componente, un array asociativo de propiedades y una cadena con el contenido hijo (<i>children</i>).</p>

<p>Invocar el método directamente es útil cuando el nombre del componente se determina en tiempo de ejecución, cuando se genera el contenido hijo de forma programática, o cuando se
trabaja fuera del compilador SDT.</p>

<pre><code class="language-php-template">&#123;{= #view->compose('message', array(), '') }&#125;</code></pre>

<p>Equivale exactamente a:</p>

<pre><code class="language-php-template">&lt;sc-message&gt;&lt;/sc-message&gt;</code></pre>

<p>Para invocar componente con propiedades y contenido estático:</p>

<pre><code class="language-php-template">&#123;{= #view->compose(
    'modal',
    array('title' => 'Delete record'),
    '&lt;p&gt;Are you sure you want to delete this item?&lt;/p&gt;'
) }&#125;</code></pre>

<p>Cuando el contenido hijo requiere lógica de presentación propia, se captura con <code>ob_start</code> / <code>ob_get_clean</code> antes de invocar el método:</p>

<pre><code class="language-php-template">&lt;?php ob_start() ?&gt;
&#64;foreach $items as $item
    &lt;li&gt;&#123;{$item->name}&#125;&lt;/li&gt;
&#58;foreach
&lt;?php $children = ob_get_clean() ?&gt;
&#123;{= #view->compose('card', array('title' => $title), $children) }&#125;</code></pre>

<p>Los componentes de vista siguen la misma firma; el prefijo <code>view.</code> indica al motor que debe cargar una plantilla SDT en lugar de una clase:</p>

<pre><code class="language-php-template">&lt;?php ob_start() ?&gt;
    &lt;p&gt;Product description...&lt;/p&gt;
&lt;?php $children = ob_get_clean() ?&gt;
&#123;{= #view->compose(
    'view.partials.card',
    array('title' => 'Product', 'price' => $price),
    $children
) }&#125;</code></pre>

<p>Los puntos en el nombre del componente de vista se traducen a separadores de carpeta, por lo que <code>view.partials.card</code> resuelve a <code>app/views/partials/card.sdt.php</code>.</p>

<p>Los bloques <code>@block[name]</code> que se definen dentro de una etiqueta <code>&lt;sc-*&gt;</code> son también HTML plano que el método <code>Heritage::parseBlocks</code> procesa internamente. Para replicar ese comportamiento de forma explícita, se incluye la directiva <code>@block</code> dentro de la cadena de contenido hijo:</p>

<pre><code class="language-php-template">&lt;?php ob_start() ?&gt;
@block[footer]
    &lt;button&gt;Confirm&lt;/button&gt;
:block
&lt;p&gt;Are you sure?&lt;/p&gt;
&lt;?php $children = ob_get_clean() ?&gt;
&#123;{= #view->compose('modal', array('title' => 'Confirm action'), $children) }&#125;</code></pre>

<p class="doc-alert"><b>Nota:</b> la salida de <code>#view->compose()</code> no está escapada por el motor, por lo que siempre debe invocarse con la sintaxis de salida en crudo <code>&#123;{=&nbsp;...&nbsp;}&#125;</code> y no con <code>&#123;{&nbsp;...&nbsp;}&#125;</code>.</p>

<h2>
    <a href="#cache">Cache y minificación</a>
    <span class="anchor" id="cache">...</span>
</h2>

<p>Durante la fase de transpilación, Scoop aplica optimizaciones agresivas:</p>

<p><ol>
    <li><b>Limpieza de HTML:</b> Elimina comentarios y espacios en blanco redundantes, reduciendo el peso del archivo enviado al cliente.</li>
    <li><b>Pre-compilación:</b> Convierte directivas SDT en sentencias PHP nativas listas para <b>Opcache</b>.</li>
    <li><b>Aislamiento de Scope:</b> Cada vista se renderiza en su propia instancia de objeto, evitando colisiones de variables globales.</li>
</ol></p>
