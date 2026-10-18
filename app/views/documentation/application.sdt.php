<P>La capa de aplicación es el corazón operativo del sistema. Scoop se define como un motor <b>agnóstico</b>: no impone una creencia férrea sobre la implementación interna, sino que suspende la creencia para otorgar soberanía total al arquitecto.</P>

<P>Aunque Scoop está optimizado para implementar <b>Arquitectura Hexagonal</b> y <b>DDD</b>, su diseño permite escalar desde servicios simples hasta sistemas complejos sin intrusiones del motor en tu lógica de negocio.</P>

<ul>
    <li><a href="#structure">Estructura de directorios</a></li>
    <li><a href="#frontend">Frontend y sistema de diseño</a></li>
    <li><a href="#dtos">Integridad Atómica con DTOs y Validación</a></li>
    <li><a href="#flash">Mensajes entre peticiones</a></li>
    <li><a href="#use-cases">Orquestación de Casos de Uso</a></li>
    <li><a href="#events">Desacoplamiento con Bus de Eventos</a></li>
</ul>

<h2>
    <a href="#structure">Estructura de directorios</a>
    <span class="anchor" id="structure">...</span>
</h2>

<p>Scoop propone una organización física que separa la configuración del motor de la lógica del negocio. Aunque la estructura es configurable, la preestablecida garantiza un arranque sólido para la mayoría de proyectos:</p>

<pre><code class="language-shell">├─ .devcontainer
|   ├─ common/etc/
|   |   ├─ caddy/scoop.caddy
|   |   └─ php/
|   |       ├─ extensions.list
|   |       └─ conf.d/00-common.ini
|   ├─ dev/etc/
|   |   ├─ caddy/Caddyfile
|   |   └─ php/conf.d/dev.ini
|   ├─ prod/etc/
|   |   ├─ caddy/Caddyfile
|   |   └─ php/conf.d/prod.ini
|   ├─ devcontainer.json
|   ├─ docker-compose.yml
|   └─ Dockerfile
├─ app
|   ├─ config
|   |    ├─ lang
|   |    |    ├─ en.php
|   |    |    └─ es.php
|   |    ├─ db.php
|   |    └─ providers.php
|   ├─ routes
|   ├─ scripts
|   ├─ storage
|   ├─ structs
|   ├─ styles
|   ├─ views
|   ├─ config.php
|   ├─ ice
|   ├─ phpcs.xml
|   ├─ phpstan.neon
|   ├─ phpunit.xml
|   └─ router.php
├─ node_modules
├─ public
|   ├─ assets
|   |    ├─ fonts
|   |    ├─ icons
|   |    └─ images
|   ├─ css
|   ├─ fonts
|   ├─ images
|   ├─ js
|   ├─ favicon.ico
|   ├─ humans.txt
|   └─ robots.txt
├─ scoop
├─ src
├─ tests
├─ vendor
├─ .dockerignore
├─ .gitattributes
├─ .gitignore
├─ .htaccess
├─ composer.json
├─ composer.lock
├─ Dockerfile
├─ index.php
├─ jsconfig.json
├─ package.json
├─ package-lock.json
├─ vite.config.js
└─ README.md
</code></pre>

<h3>.devcontainer</h3>
<p>Contiene la configuración de PHP y Caddy para FrankenPHP. Los árboles <code>common/</code>, <code>dev/</code> y <code>prod/</code> reproducen las rutas del sistema operativo: los Dockerfiles copian <code>common/</code> y el árbol del ambiente correspondiente a <code>/</code> durante la construcción.</p>
<p>Los archivos devcontainer.json, docker-compose.yml y Dockerfile son usados para configurar el entorno de desarrollo.</p>

<h3>app</h3>
<p>Contiene todo el código diferente al core del negocio pero que igual es necesario para la ejecución de la aplicación,
entre esto tenemos:</p>

<p><ul>
    <li><b>config/:</b> En esta carpeta es donde se deben referenciar los archivos de configuración.</li>
    <li><b>routes/:</b> Sistema de archivos para definir las rutas de la aplicación.</li>
    <li><b>scripts/:</b> En esta carpeta es donde se deben referenciar los archivos de javascript.</li>
    <li><b>storage/:</b> En esta carpeta es donde se deben referenciar los archivos de cache o sin seguimiento.</li>
    <li><b>styles/:</b> Contiene los estilos CSS y sus variables de tema. La entrada app.styl conserva compatibilidad con extensiones y helpers Stylus durante la transición.</li>
    <li><b>views/:</b> En esta carpeta es donde se deben referenciar los archivos de templates o vistas.</li>
    <li><b>config.php:</b> Este es el archivo principal de configuración, el cual se referencia al momento de cargar el entorno.</li>
    <li><b>ice:</b> Este es el archivo donde se ejecutan los comandos de consola del sistema.</li>
    <li><b>phpcs.xml:</b> En este archivo se encuentran las reglas del linter php.</li>
    <li><b>router.php:</b> Este archivo sirve como sistema rewrite para el servidor php standalone.</li>
</ul></p>

<h3>public</h3>

<p>Contiene todos los assets compilados y listos para ser entregado al cliente, además de imágenes, archivos usados
para la indexación en motores de búsqueda y fuente de letras. Normalmente no deben ser modificados más que para agregar, modificar o eliminar
assets de la aplicación.</p>

<p><ul>
    <li><b>css/:</b> En esta carpeta es donde se deben referenciar los archivos css transpilados y minificados.</li>
    <li><b>fonts/:</b> Fuentes propias de la aplicación. La subcarpeta <code>awesome/</code> contiene las fuentes de Font Awesome generadas por el build y se ignora en Git.</li>
    <li><b>images/:</b> Imágenes propias de la aplicación. La subcarpeta <code>icons/</code> contiene los SVG referenciados por los estilos o imports y se ignora en Git.</li>
    <li><b>licences/:</b> Licencias que deben acompañar a los recursos distribuidos; se generan durante el build y se incluyen en el despliegue aunque estén ignoradas en Git.</li>
    <li><b>js/:</b> En esta carpeta es donde se deben referenciar los archivos javascript transpilados y minificados.</li>
    <li><b>favicon:</b> Este archivo es el que se carga por defecto como icono en el template principal de la aplicación.</li>
    <li><b>humans.txt:</b> Este archivo es el que se carga por defecto para ser leido por humanos en el template principal de la aplicación.</li>
    <li><b>robots.txt:</b> Este archivo es el que se carga por defecto para ser leido por humanos en el template principal de la aplicación.</li>
</ul></p>

<h3>scoop</h3>
<p>Carpeta principal del bootstrap, contiene todo lo necesario para arrancar el proyecto. No debe ser modificada o alterada,
    en futuras actualizaciones se planea enviar a la carpeta vendor gestionada por composer.
</p>

<h3>src</h3>
<p>Scoop no impone una organización interna en esta carpeta. Eres libre de estructurarla por capas (Dominio/Aplicación/Infraestructura) o por contextos (Bounded Contexts), manteniendo tu código principal libre de acoplamientos con el motor.</p>

<h2>
    <a href="#frontend">Frontend y sistema de diseño</a>
    <span class="anchor" id="frontend">...</span>
</h2>

<p>Trabaja los estilos en <code>app/styles/</code> y el comportamiento en <code>app/scripts/</code>. Usa <code>npm run dev</code> mientras desarrollas y genera los archivos de producción con <code>npm run build</code>.</p>

<h3>Personalizar estilos y temas</h3>
<p>Crea tus hojas CSS en <code>app/styles/</code> e impórtalas desde <code>app/styles/app.styl</code>. Puedes seguir usando Stylus en los proyectos existentes pero ten en cuenta que esta deprecado y en futuras versiones no sera soportado oficialmente.</p>

<pre><code class="language-stylus">@import "custom.css"
</code></pre>

<p>Para cambiar colores, fuentes y espaciado, establece las variables de <code>core/palette.css</code>, <code>core/typography.css</code> y <code>core/spacing.css</code>. Usa los roles <code>on-*</code> para mantener el contraste del texto sobre cada fondo o sobreescribe en tus propias hojas de estilos:</p>

<pre><code class="language-css">:root {
    --primary: light-dark(#1766b5, #a6c8ff);
    --on-primary: light-dark(#fff, #10151e);
    --font-family-body: system-ui, sans-serif;
    --space-unit: .25rem;
}
</code></pre>

<p>El tema sigue la preferencia del sistema. Puedes forzar una selección con <code>data-theme</code> en <code>html</code> o usar el botón <code>data-theme-toggle</code> de la plantilla:</p>

<pre><code class="language-javascript">document.documentElement.dataset.theme = 'dark';
</code></pre>

<h3>Componer interfaces e iconos</h3>
<p>Usa las clases de <code>app/styles/utilities/</code> para combinar espaciado, distribución, tamaños y colores. Por ejemplo:</p>

<pre><code class="language-html">&lt;section class="flex items-center gap-sm p-md bg-surface text-on-surface"&gt;
    &lt;button class="btn bg-primary text-on-primary"&gt;Guardar&lt;/button&gt;
&lt;/section&gt;
</code></pre>

<p>Para añadir un SVG de <a href="https://tabler.io/icons">Tabler Icons</a>, referencia su nombre con <code>@icon</code>. El alias busca primero tus SVG en <code>app/styles/scoop/icons/</code> y, si no existen allí, utiliza la variante <code>outline</code> de Tabler. Puedes elegir una variante explícita con <code>@icon/filled/heart.svg</code> o <code>@icon/outline/heart.svg</code>:</p>

<pre><code class="language-css">.favorite-icon {
    display: inline-block;
    width: 1em;
    height: 1em;
    background: currentColor;
    mask: url("@icon/heart.svg") center / contain no-repeat;
}
</code></pre>

<p>Marca los iconos decorativos con <code>aria-hidden="true"</code>; los botones que sólo contienen un icono necesitan un nombre accesible.</p>

<h3>Elementos preconstruidos con HTML y CSS</h3>
<p>Los estilos de <code>app/styles/scoop/</code> incluyen tablas, pestañas, campos, botones y mensajes. Usa su estructura HTML para obtener la presentación; añade componentes JavaScript cuando necesites enviar datos, cargar contenido o gestionar estados.</p>

<h4>Pestañas</h4>
<p>Dentro de <code>scoop-tabs</code>, coloca cada radio <code>name="tab"</code> seguido de su <code>label</code> y su <code>.panel</code>, en ese orden. Marca la pestaña inicial con <code>checked</code>. La selección y la adaptación a móvil funcionan con CSS, sin JavaScript:</p>

<pre><code class="language-html">&lt;div class="scoop-tabs"&gt;
    &lt;input type="radio" name="tab" id="tab-general" checked&gt;
    &lt;label for="tab-general"&gt;General&lt;/label&gt;
    &lt;section class="panel"&gt;Contenido general&lt;/section&gt;

    &lt;input type="radio" name="tab" id="tab-details"&gt;
    &lt;label for="tab-details"&gt;Detalles&lt;/label&gt;
    &lt;section class="panel"&gt;Contenido detallado&lt;/section&gt;
&lt;/div&gt;
</code></pre>

<h4>Campos y botones</h4>
<p>Usa <code>input-box</code> para envolver un campo <code>input</code>, <code>group-box</code> para agrupar campos y <code>btn</code> para botones. Puedes añadir <code>success</code>, <code>error</code>, <code>warning</code> o <code>info</code> al botón. Para radios y casillas, usa un contenedor <code>radio</code> o <code>checkbox</code> con el campo seguido de un <code>label</code> asociado.</p>

<pre><code class="language-html">&lt;form action="/contact" method="post"&gt;
    &lt;div class="input-box"&gt;
        &lt;label for="email"&gt;Correo&lt;/label&gt;
        &lt;input class="input" id="email" name="email" type="email" required&gt;
    &lt;/div&gt;
    &lt;button class="btn" type="submit"&gt;Enviar&lt;/button&gt;
&lt;/form&gt;
</code></pre>

<p>El formulario conserva el envío y la validación nativos del navegador. Registra <code>Form</code>, descrito más abajo, si quieres enviar los datos mediante JavaScript y mostrar la respuesta en la página.</p>

<h4>Mensajes</h4>
<p>Para un aviso estático usa <code>id="msg"</code> y una clase de estado: <code>success</code>, <code>error</code>, <code>warning</code> o <code>info</code>. La clase <code>not</code> lo oculta. Añade <code>Message</code> y <code>Messenger</code> cuando necesites actualizarlo o cerrarlo desde JavaScript.</p>

<pre><code class="language-html">&lt;div id="msg" class="info" role="status"&gt;
    Cambios guardados.
&lt;/div&gt;
</code></pre>

<h4>Tablas responsive</h4>
<p>Añade <code>scoop-table</code> a la tabla y <code>data-label</code> a cada celda. Hasta 600 px, las filas se muestran como campos etiquetados:</p>

<pre><code class="language-html">&lt;table class="scoop-table w-full"&gt;
    &lt;thead&gt;&lt;tr&gt;&lt;th scope="col"&gt;Motor&lt;/th&gt;&lt;/tr&gt;&lt;/thead&gt;
    &lt;tbody&gt;&lt;tr&gt;&lt;td data-label="Motor"&gt;Scoop&lt;/td&gt;&lt;/tr&gt;&lt;/tbody&gt;
&lt;/table&gt;
</code></pre>

<h3 id="frontend-components">Añadir comportamiento JavaScript</h3>
<p>Crea tus componentes en <code>app/scripts/components/</code> y regístralos en <code>app/scripts/module.js</code>. <code>app.js</code> ejecuta el módulo:</p>

<pre><code class="language-javascript">import { Module } from 'scalar';
import MyComponent from '@/components/my-component';

export default new Module()
.compose('[data-my-component]', MyComponent);
</code></pre>

<p>Para definir eventos, propiedades, servicios o Web Components, consulta la <a href="https://github.com/mirdware/scalar">documentación de Scalar</a>.</p>

<h3>Activar comportamiento con los componentes de Scoop</h3>
<p>Importa los componentes desde <code>@/scoop/components/</code> y añade sus registros al módulo existente. Los selectores de este ejemplo los eliges tú: una clase o atributo por sí solo no activa el comportamiento.</p>

<pre><code class="language-javascript">import { Module } from 'scalar';
import Form from '@/scoop/components/form';
import Pageable from '@/scoop/components/pageable';
import Message from '@/scoop/components/message';
import Modal from '@/scoop/components/modal';

// Conserva también los registros que ya tenga tu módulo.
export default new Module()
.compose('form.scoop-form', Form)
.compose('[data-pageable]', Pageable)
.compose('[data-message]', Message)
.compose('#scoop-modal', Modal);
</code></pre>

<h4>Formularios: Form</h4>
<p>Para enviar el formulario del ejemplo mediante JavaScript, añade <code>class="scoop-form"</code> y registra <code>Form</code> como se muestra arriba. Mantén <code>action</code>, <code>method</code> y los nombres de los campos. Usa <code>enctype="multipart/form-data"</code> para archivos; extiende <code>Form</code> y personaliza <code>done</code> o <code>fail</code> si necesitas otra respuesta. Incluye también el contenedor Message del siguiente ejemplo para mostrar los avisos de respuesta.</p>

<h4>Cambiar tema: Theme toggle</h4>
<p>Reutiliza el botón <code>data-theme-toggle</code> de la plantilla para alternar entre claro y oscuro. Para conservar la selección al volver a abrir la página, añade este script en el <code>head</code>, antes del CSS:</p>

<pre><code class="language-html">&lt;script&gt;
try {
    const theme = localStorage.getItem('scoop.theme');
    if (theme === 'light' || theme === 'dark') {
        document.documentElement.dataset.theme = theme;
    }
} catch (error) {}
&lt;/script&gt;
</code></pre>

<pre><code class="language-html">&lt;button type="button" class="theme-toggle" data-theme-toggle
    data-attr="ariaLabel:label;title:label" aria-label="Cambiar tema"&gt;
    &lt;span class="theme-toggle-icon" aria-hidden="true"&gt;&lt;/span&gt;
&lt;/button&gt;
</code></pre>

<h4>Paginar y filtrar: Pageable</h4>
<p>Registra <code>Pageable</code> sobre el contenedor de la lista. Añade enlaces <code>.prev</code> y <code>.next</code> vinculados a <code>prev</code> y <code>next</code> con Scalar, y vincula la lista a <code>data</code>. El endpoint devuelve <code>result</code>, <code>page</code> (desde cero), <code>size</code> y <code>total</code>. Un formulario dentro del contenedor permite filtrar; configura el endpoint y los nombres con <code>data-options</code>.</p>

<h4>Mostrar avisos: Message y Messenger</h4>
<p>Sobre el aviso <code>#msg</code>, añade <code>data-message</code>, vincula el texto a <code>msg</code> y la clase de estado a <code>type</code> con Scalar. Registra <code>Message</code> y añade un botón <code>.close</code>. Inyecta <code>@/scoop/services/Messenger</code> para llamar a <code>showSuccess(texto)</code>, <code>showError(texto)</code>, <code>showInfo(texto)</code> o <code>showAlert(texto)</code>.</p>

<pre><code class="language-html">&lt;div id="msg" class="not" data-message data-attr="className:type" role="status"&gt;
    &lt;span data-bind="msg"&gt;&lt;/span&gt;
    &lt;button type="button" class="close" aria-label="Cerrar aviso"&gt;&lt;/button&gt;
&lt;/div&gt;
</code></pre>

<p>Usa este contenedor en lugar del aviso estático anterior, una sola vez en la plantilla. El registro <code>.compose('[data-message]', Message)</code> lo conecta con Messenger; los formularios Form utilizan ese mismo servicio para sus avisos.</p>

<h4>Abrir contenido: Modal</h4>
<p>Prepara <code>#scoop-modal</code> con <code>.header</code> (un título <code>h2</code> o <code>h3</code> y un <code>div</code> de cierre) y <code>.body</code>. Registra <code>Modal</code> e inyecta <code>@/scoop/services/Modal</code>: <code>await modal.open(url, titulo)</code> carga el HTML y <code>modal.close()</code> lo cierra.</p>

<pre><code class="language-html">&lt;div id="scoop-modal"&gt;
    &lt;div class="header"&gt;
        &lt;h3&gt;&lt;/h3&gt;
        &lt;div role="button" aria-label="Cerrar modal"&gt;Cerrar&lt;/div&gt;
    &lt;/div&gt;
    &lt;div class="body"&gt;&lt;/div&gt;
&lt;/div&gt;
</code></pre>

<h4>Buscar con filtros: Search</h4>
<p>Para recoger filtros en un modal, compón el elemento devuelto por <code>modal.open()</code> con <code>@/scoop/components/search</code> y espera <code>send(filtros)</code>. En Pageable puedes usar un botón <code>.modal</code> con <code>title</code> dentro de un formulario con <code>data-modal="URL-del-formulario"</code>. Esta búsqueda recoge campos del formulario; la búsqueda de la documentación utiliza un componente propio.</p>

<h3 id="frontend-assets">Build y documentación de herramientas</h3>
<p>Ejecuta <code>npm run dev</code> para desarrollar y <code>npm run build</code> para generar los assets. Despliega también los archivos generados en <code>public/</code>, aunque estén ignorados por Git. Si una clase se construye dinámicamente y desaparece del build, añade su nombre completo a <code>app/styles/safelist.json</code>.</p>

<p>El build escribe las hojas de estilo en <code>public/css/</code>, los scripts en <code>public/js/</code> y los recursos importados en <code>public/assets/</code>, separados en <code>fonts/</code>, <code>icons/</code> e <code>images/</code>. Conserva el recurso original en la ubicación desde la que lo importas; los archivos generados pueden cambiar de nombre al reconstruir.</p>

<p>Consulta las opciones de cada herramienta en su documentación:</p>
<ul>
    <li><a href="https://vite.dev/guide/">Vite</a> y <a href="https://vite.dev/plugins/">sus plugins</a>: desarrollo, assets y configuración del build.</li>
    <li><a href="https://lightningcss.dev/">Lightning CSS</a>: procesamiento de CSS y compatibilidad con navegadores.</li>
    <li><a href="https://purgecss.com/safelisting">PurgeCSS</a>: conservar clases dinámicas.</li>
    <li><a href="https://github.com/sapphi-red/vite-plugin-static-copy">vite-plugin-static-copy</a>: copiar recursos adicionales.</li>
    <li><a href="https://stylus-lang.com/">Stylus</a>: sintaxis y helpers del preprocesador.</li>
</ul>

<p>Puedes reemplazar cualquiera de estas librerías o sistemas según las necesidades de tu proyecto.</p>

<h2>
    <a href="#dtos">Integridad Atómica con DTOs y Validación</a>
    <span class="anchor" id="dtos">...</span>
</h2>

<p>En Scoop, la entrada de datos no es un simple array; es un contrato. La "Aduana de Datos" garantiza que la lógica de aplicación solo trabaje con información limpia, tipada e inmutable mediante el uso de <b>Data Transfer Objects (DTO)</b>.</p>

<h3>Definición del Validador</h3>

<p>Para desacoplar la validación del controlador, se crean clases que heredan de <code>\Scoop\Validator</code>. Al sobrescribir el método <code>validate</code>, defines las reglas que el motor debe verificar.</p>

<pre><code class="language-php">class UserValidator extends \Scoop\Validator
{
    public function validate($data): bool
    {
        $required = new Required();
        $this->add('name', $required, new MinLength(8), new MaxLength(40))
        ->add('password', $required, new Same('password2'))
        ->add('password2', $required);
        return parent::validate($data);
    }
}
</code></pre>

<h3>Hidratación Segura</h3>

<p>El flujo recomendado en Scoop v0.8 utiliza el método <code>fromBody()</code> del Request. Si la validación falla, el motor rompe el flujo de ejecución (fail-fast), evitando estados inconsistentes.</p>

<pre><code class="language-php">public function post(Request $request)
{
    $dto = $request->get(CreateInvoiceDTO::class)->fromBody($this->validator);
    return $this->useCase->execute($dto);
}
</code></pre>

<h2>
    <a href="#use-cases">Orquestación de Casos de Uso</a>
    <span class="anchor" id="use-cases">...</span>
</h2>

<p>Los servicios de aplicación o <b>Casos de Uso</b> son los encargados de coordinar la lógica. Gracias al Inyector de Scoop, estos servicios reciben sus dependencias (Repositorios, Bus de Eventos) de forma automática mediante el constructor.</p>

<pre><code class="language-php">class CreateInvoiceUseCase
{
    public function __construct(
        private InvoiceRepository $repository,
        private EventBus $eventBus
    ) {}

    public function execute(CreateInvoiceDTO $dto): Invoice
    {
        $invoice = Invoice::create($dto->customerId, $dto->amount);
        $this->repository->save($invoice);
        foreach ($invoice->releaseEvents() as $event) {
            $this->eventBus->publish($event);
        }
        return $invoice;
    }
}
</code></pre>

<h2>
    <a href="#flash">Mensajes entre peticiones</a>
    <span class="anchor" id="flash">...</span>
</h2>

<p>Scoop integra un sistema de Flash Messages persistente pero efímero. Permite enviar notificaciones o datos de estado entre peticiones de forma segura, garantizando que la información se limpie automáticamente una vez consumida por la vista.</p>

<pre><code class="language-php">$this->request->flash()->get('message');
</code></pre>

<p>La forma común de manejar mensajes es mediante la clase <code>Route</code>.</p>

<pre><code class="language-php">$route = new Route('filing');
$request->redirect($route->withMessage('Factura creada correctamente'), 303);
</code></pre>

<p>Para enviar cualquier otro tipo de información se puede usar directamente el método <code>set</code> de <code>flash</code>.</p>

<h2>
    <a href="#events">Desacoplamiento con Bus de Eventos</a>
    <span class="anchor" id="events">...</span>
</h2>

<p>Scoop facilita la comunicación entre componentes mediante un <b>Event Bus</b> basado en objetos simples (POPOs). Esto permite que el sistema reaccione a hechos del dominio sin que las clases se conozcan entre sí.</p>

<h3>Configuración de Suscriptores</h3>

<p>La asociación entre un evento y sus listeners se define de forma declarativa en el mapa de configuración:</p>

<pre><code class="language-php">[
    'events' => [
        InvoiceCreated::class => [
            SendWelcomeEmail::class,
            NotifyAccountingSystem::class
        ]
    ]
]
</code></pre>

<h3>Middlewares</h3>

<p>La configuración anterior permite la declaración simple de listeners o subscriptores, pero estos pueden ser decorados con middlewares de diversas maneras. La primera es mediante la misma configuración.</p>

<pre><code class="language-php">[
    'events' => [
        InvoiceCreated::class => [
            SendWelcomeEmail::class,
            NotifyAccountingSystem::class => [DBMiddleware::class]
        ]
    ]
]
</code></pre>

<p>La segunda opción permite que el propio listener declare sus middlewares mediante el método estático <code>getMiddlewares</code>. Esta aproximación es ideal cuando se utiliza el cargador <code>typeof</code> para el autodescubrimiento de listeners, permitiendo que cada uno transporte su propia configuración de infraestructura.</p>

<pre><code class="language-php">class NotifyAccountingSystem
{
    public static function getMiddlewares()
    {
        return [DBMiddleware::class];
    }
}
</code></pre>

<p>Cabe la pena recordar que la configuración prima sobre la declaración del método.</p>

<h3>Listeners Inyectables</h3>

<p>Cada Listener es una clase gestionada por el Inyector, permitiendo que realice tareas de infraestructura (como enviar un email o registrar un log) de forma totalmente aislada.</p>

<pre><code class="language-php">class SendWelcomeEmail
{
    public function __construct(private Mailer $mailer) {}

    public function listen(InvoiceCreated $event): void
    {
        $invoice = $event->getInvoice();
        $this->mailer->send($invoice->getCustomerEmail(), "Factura creada");
    }
}
</code></pre>

<p class="doc-alert p-md rounded-lg text-on-surface bg-warning-subtle border-warning"><b>Pureza del Dominio:</b> Los eventos en Scoop no requieren heredar de ninguna clase base del motor. Son mensajes puros de tu negocio que Scoop se encarga de transportar y entregar.</p>
