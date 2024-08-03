<p>El agnosticismo se define como la postura que considera que los valores de verdad de ciertas afirmaciones
son desconocidas o inherentemente incognoscibles, el agnosticismo es la mera suspensión de la creencia.</p>

<p>Decimos que scoop es agnostico por que no tiene una creencia ferrea  en como debe ser implementada la arquitectura de la aplicación. 
Lo que quiere decir que scoop esta diseñado para que sea lo menos intrusivo posible con el desarrollo de cualquier tipo de
aplicación, aunque esta pensada especificamente para el manejo de <a href="https://en.wikipedia.org/wiki/Hexagonal_architecture_(software)">arquitectura hexagonal</a>
con <a href="https://en.wikipedia.org/wiki/Domain-driven_design">Domain Driven Disign</a>. Se puede pensar en scoop como un 
conjunto de herramientas para mejorar la experiencia de desarrollo, no como un framework con opiniones fuertes de como 
dirigir el desarrollo.</p>

<ul>
    <li><a href="#structure">Estructura de directorios</a></li>
    <li><a href="#lifecycle">Ciclo de vida de una petición</a></li>
    <li><a href="#validations">Validaciones</a></li>
    <li><a href="#inject">Inyección de dependencias</a></li>
    <li><a href="#events">Eventos</a></li>
</ul>

<h2>
    <a href="#structure">Estructura de directorios</a>
    <span class="anchor" id="structure">...</span>
</h2>

<p>La estructura de directorios de scoop esta diseñada para dar un buen punto de arranque para el desarrollo de
aplicaciones orientadas a la web. Exiten multiples fromas de configurar la estructura de directorios, pero la
prestablecida es la más conveniente en la mayoria de los casos. Los archivos que se encuentran en la raíz del
proyecto son configuraciones de terceros o como <code>index.php</code> arraque del proyecto.</p>

<h3>.devcontainers</h3>
<p>Contiene los archivos de infraestructura que son utilizados tanto para configurar el entorno como para inyectar al contenedor docker;
    como en el caso de archivos php.ini y configuraciones de apache, para esto nos basamos en el uso de la imagen 
    <a href="https://dockerfile.readthedocs.io/en/latest/content/DockerImages/dockerfiles/php-apache.html#customization">webdevops/php-apache</a>.
    Cualquier carpeta o archivo generado en <code>.devcontainer/etc</code> se sincronizará con <code>/opt/docker/etc</code>.</p>
<p>Los archivos devcontainer.json, docker-compose.yml y Dockerfile son usados para configurar el entorno de desarrollo.</p>

<h3>app</h3>
<p>Contiene todo el código diferente al core del negocio pero que igual es necesario para la ejecución de la aplicación,
entre esto tenemos:</p>
<ul>
    <li><b>config/:</b> En esta carpeta es donde se deben referenciar los archivos de configuración.</li>
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
<p>Contiene el código principal de la aplicación, su estructura depende de como el usuario desee llevar su proyectos,
desde división por infraestructura y dominio, como por separación de artefactos (controladores, repositorios, servicios).</p>

<h2>
    <a href="#lifecycle">Ciclo de vida de una petición</a>
    <span class="anchor" id="#lifecycle">...</span>
</h2>

<pre class="prettyprint">
&lt;?php

namespace App\Infrastructure\Controller;

use App\Application\CreateInvoiceUseCase;
use Scoop\Controller;

class Home extends Controller
{
    private CreateInvoiceUseCase $useCase;

    public function __construct(CreateInvoiceUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function post()
    {
        $dto = $this->getRequest()->getBody();
        $invoice = $this->useCase->execute($dto);
        return 'Factura ' . $invoice['id'] . ' creada';
    }
}
</pre>

<h2>
    <a href="#validations">Validaciones</a>
    <span class="anchor" id="#lifecycle">...</span>
</h2>

<pre  class="prettyprint">
public function post()
{
    $dto = $this->getRequest()->getBody($this->validation);
    $invoice = $this->useCase->execute($dto);
    return 'Factura ' . $invoice['id'] . ' creada';
}
</pre>

<h2>
    <a href="#inject">Inyección de dependencias</a>
    <span class="anchor" id="#lifecycle">...</span>
</h2>

<pre class="prettyprint">
public function __construct(CreateInvoiceUseCase $useCase, InvoiceValidator $validator)
{
    $this->useCase = $useCase;
    $this->validator = $validator;
}
</pre>

<h2>
    <a href="#events">Eventos</a>
    <span class="anchor" id="#lifecycle">...</span>
</h2>

<pre class="prettyprint">
[
    'events' => [
        InvoiceCreated::class => [
            EmailInvoiceSender::class,
            ExternalBrokerSender::class
        ]
    ]

]
</pre>

<pre class="prettyprint">
public function execute()
{
    $invoice = new Invoice('FE001');
    $this->repository->save($invoice);
    $this->eventDispatcher->dispatch(new InvoiceCreated($invoice));
    return $invoice;
}
</pre>
