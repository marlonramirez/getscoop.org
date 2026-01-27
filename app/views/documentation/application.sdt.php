<P>La capa de aplicación es el corazón operativo del sistema. Scoop se define como un motor <b>agnóstico</b>: no impone una creencia férrea sobre la implementación interna, sino que suspende la creencia para otorgar soberanía total al arquitecto.</P>

<P>Aunque Scoop está optimizado para implementar <b>Arquitectura Hexagonal</b> y <b>DDD</b>, su diseño permite escalar desde servicios simples hasta sistemas complejos sin intrusiones del motor en tu lógica de negocio.</P>

<ul>
    <li><a href="#structure">Estructura de directorios</a></li>
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

<pre><code class="language-shell">├─ .devcontainers
|   ├─ etc
|   |   ├─ httpd
|   |   |    └─ custom.conf
|   |   └─ php
|   |        └─ php.ini
|   ├─ devcontainer.json
|   ├─ docker-compose.yml
|   └─ Dockerfile
├─ app
|   ├─ config
|   |    ├─ lang
|   |    |    ├─ en.php
|   |    |    └─ es.php
|   |    ├─ routes.php
|   |    └─ providers.php
|   ├─ routes
|   ├─ scripts
|   ├─ storage
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
├─ .dockignore
├─ .gitattributes
├─ .gitignore
├─ .htaccess
├─ composer.json
├─ Dockerfile
├─ gulpfile.js
├─ index.php
├─ jsconfig.json
├─ package.json
└─ README.md
</code></pre>

<h3>.devcontainers</h3>
<p>Contiene los archivos de infraestructura que son utilizados tanto para configurar el entorno como para inyectar al contenedor docker;
    como en el caso de archivos php.ini y configuraciones de apache, para esto nos basamos en el uso de la imagen
    <a href="https://dockerfile.readthedocs.io/en/latest/content/DockerImages/dockerfiles/php-apache.html#customization">webdevops/php-apache</a>.
    Cualquier carpeta o archivo generado en <code>.devcontainer/docker</code> se sincronizará con <code>/opt/docker</code>.</p>
<p>Los archivos devcontainer.json, docker-compose.yml y Dockerfile son usados para configurar el entorno de desarrollo.</p>

<h3>app</h3>
<p>Contiene todo el código diferente al core del negocio pero que igual es necesario para la ejecución de la aplicación,
entre esto tenemos:</p>
<ul>
    <li><b>config/:</b> En esta carpeta es donde se deben referenciar los archivos de configuración.</li>
    <li><b>routes/:</b> Sistema de archivos para definir las rutas de la aplicación.</li>
    <li><b>scripts/:</b> En esta carpeta es donde se deben referenciar los archivos de javascript.</li>
    <li><b>storage/:</b> En esta carpeta es donde se deben referenciar los archivos de cache o sin seguimiento.</li>
    <li><b>styles/:</b> En esta carpeta es donde se deben referenciar los archivos de css (stylus).</li>
    <li><b>views/:</b> En esta carpeta es donde se deben referenciar los archivos de templates o vistas.</li>
    <li><b>config.php:</b> Este es el archivo principal de configuración, el cual se referencia al momento de cargar el entorno.</li>
    <li><b>ice:</b> Este es el archivo donde se ejecutan los comandos de consola del sistema.</li>
    <li><b>phpcs.xml:</b> En este archivo se encuentran las reglas del lintter php.</li>
    <li><b>router.php:</b> Este archivo sirve como sistema rewrite para el servidor php standalone.</li>
</ul>

<h3>public</h3>
<p>Contiene todo los assets compilados y listos para ser entregado al cliente, además de imagenes, archivos usados
para la indexación en motores de busqueda y fuente de letras. Normalmente no deben ser modificados más que para agregar, modificar o eliminar
assets de la aplicación.</p>
<ul>
    <li><b>css/:</b> En esta carpeta es donde se deben referenciar los archivos css transpilados y minificados.</li>
    <li><b>fonts/:</b> En esta carpeta es donde se deben referenciar los archivos de fuentes de letras.</li>
    <li><b>images/:</b> En esta carpeta es donde se deben referenciar los archivos de imagenes.</li>
    <li><b>js/:</b> En esta carpeta es donde se deben referenciar los archivos javascript transpilados y minificados.</li>
    <li><b>favicon:</b> Este archivo es el que se carga por defecto como icono en el template principal de la aplicación.</li>
    <li><b>humans.txt:</b> Este archivo es el que se carga por defecto para ser leido por humanos en el template principal de la aplicación.</li>
    <li><b>robots.txt:</b> Este archivo es el que se carga por defecto para ser leido por humanos en el template principal de la aplicación.</li>
</ul>

<h3>scoop</h3>
<p>Carpeta principal del bootstrap, contiene todo lo necesario para arrancar el proyecto. No debe ser modificada o alterada,
    en futuras actualizaciones se planea enviar a la carpeta vendor gestionada por composer.
</p>

<h3>src</h3>
<p>Scoop no impone una organización interna en esta carpeta. Eres libre de estructurarla por capas (Dominio/Aplicación/Infraestructura) o por contextos (Bounded Contexts), manteniendo tu código principal libre de acoplamientos con el motor.</p>

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
        ->add('password', $required, new Same('password2'));
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

<p>La asociación entre un Evento y sus Listeners se define de forma declarativa en el mapa de configuración:</p>

<pre><code class="language-php">[
    'events' => [
        InvoiceCreated::class => [
            SendWelcomeEmail::class,
            NotifyAccountingSystem::class
        ]
    ]
]
</code></pre>

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

<p class="doc-alert"><b>Pureza del Dominio:</b> Los eventos en Scoop no requieren heredar de ninguna clase base del motor. Son mensajes puros de tu negocio que Scoop se encarga de transportar y entregar.</p>
