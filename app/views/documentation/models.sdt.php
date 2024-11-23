<p>El desarrollo de software se divide en dos grandes partes: <i>estratégica</i> y <i>táctica</i>, cuyo orden de implementación
es definido de acuerdo al estado de avance del proyecto que se va a trabajar con este enfoque. Para un proyecto
que esté en etapa de definición y análisis, el punto de partida va a ser la <b>parte estratégica</b>, donde se busca
un modelo que nos ayude a entender y resolver las necesidades o problemas que se tienen en un dominio de negocio;
seguidamente se inicia el proceso táctico donde se eligen los patrones de arquitectura a utilizar para diseñar la
solución, al igual que las tecnologías a usar en la solución de cada modelo del negocio.</p>

<p>El otro escenario se presenta cuando tenemos un proyecto que ya tiene algunas de sus implementaciones realizadas.
En este caso, se inicia por la <b>parte táctica</b>, refactorizando los componentes para que se cumpla con los principios
de desacoplamiento y separación de responsabilidades y después se empieza a incluir de a poco la parte estratégica,
en búsqueda del modelamiento de los nuevos requerimientos y de las funcionalidades ya existentes.</p>

<p>Scoop ayuda en la parte táctica del proyecto pero se declara agnostica al modelo del negocio, lo cual significa que
deja definir una estrategía propia al momento de usar una arquitectura(Hexagonal, Clear, Onion, etc).</p>

<ul>
    <li><a href="#dbc">Data Base Connection</a></li>
    <li><a href="#structs">structs</a></li>
    <li><a href="#sqo">Query Objects (SQO)</a></li>
    <li><a href="#epm">Entity Persistence Manager (EPM)</a></li>
    <li><a href="#repositories">Repositorios</a></li>
    <li><a href="#dsl">Criteria (DSL)</a></li>
</ul>

<h2>
    <a href="#dbc">Data Base Conection</a>
    <span class="anchor" id="dbc">...</span>
</h2>
<p>Cualquier tipo de base de datos se debe conectar mediante un driver PDO, ya que este se encarga de administrar las
conexiones, esto garantiza la homogeniedad en el tratamiento de datos. El uso y configuración de la conexión se manejea
mediante la clase DBC acronimo de Data Base Connection y que se provee mediante los archivo de configuración.</p>

<pre class="prettyprint">
[
    'db' => [
        'default' => [
            'database' => 'scoop',
            'user' => 'scoop',
            'password' => '1s4Gr34tB00t5tr4p',
            'host' => 'localhost',
            'driver' => 'pgsql'
        ],
        'auth' => [
            'database' => 'auth',
            'user' => 'scoop',
            'password' => 'myS1st3m4uth',
            'host' => 'localhost',
            'driver' => 'mysql'
        ]
    ]
]
</pre>

<p>En el ejemplo anterior se proveen dos conexiones diferentes, cada una empaquetada con un nombre clave;
para usar alguna conexión se debe hacer uso de la clase <code>\Scoop\Context</code> e incovar el método
<code>connect</code>, cuando no se envia ningún parametro al método este toma la conexión default, para escoger
otro tipo de conexión diferente se debe enviar la clave de la conexión <code>\Scoop\Conext::connect('auth')</code>.</p>

<h2>
    <a href="#structs">Structs</a>
    <span class="anchor" id="structs">...</span>
</h2>

<h3>Creación</h3>

<pre  class="prettyprint">php app/ice new struct --schema=auth --name=data</pre>

<h3>Ejecución</h3>

<pre  class="prettyprint">php app/ice dbup --name=default --schema=auth --user=postgres --password=$POSTGRES_PASSWORD</pre>

<h2>
    <a href="#sqo">Query Objects</a>
    <span class="anchor" id="sqo">...</span>
</h2>
<p>No existe nada de malo con usar SQL dentro de una aplicación, pero se pueden usar herramientas que hagan
el manejo de las sentencias mucho más flexibles y dinamicas, el mecanismo escogido por scoop para esta labor es
SQO(Scoop|Simple Query Object), el cual presenta una manera más sencilla y orientada a objetos de realizar consultas a
la base de datos.</p>

<p>Al instanciar un objeto SQO es necesario pasar como parametro el nombre de la tabla principal
<code>new \Scoop\Storage\SQO('book')</code>, si es necesario colocar un alias o manejar una conexión
diferente a <i>default</i> se pueden usar dos argumentos más.</p>
<pre class="prettyprint">
$bookSQO = new \Scoop\Storage\SQO('book', 'alias', 'connectionName');
</pre>

<p class="doc-alert">A partir de la versión 0.5.6 se envia el nombre de la conexión y no la conexión como en anteriores versiones</p>

<p>A partir de acá es posible usar los métodos de SQO: create, update, read, delete y getLastId. Los cuatro primeros
pertenecen al CRUD y cada uno devuelve un objeto DML especifico para cada caso, mientras  <code>getLastId</code> retorna
el último id insertado mediante el objeto.</p>

<h3>Creación</h3>
<pre class="prettyprint">
$bookSQO->create([
    'name' => 'Angels & demons',
    'author' => 'Dan Brown',
    'year' => '2009'
])->run();
</pre>

<h3>Lectura</h3>
<pre class="prettyprint">
$bookSQO->read()
    ->filter('name like %:name%')
    ->restrict('year = 2009')
    ->run();
</pre>

<h3>Actualización</h3>
<pre class="prettyprint">
$bookSQO->update([
    'name' => 'Angels & demons',
    'author' => 'Dan Brown',
    'year' => '2009'
])->restrict('id = :id')->run();
</pre>

<h3>Eliminación</h3>
<pre class="prettyprint">
$bookSQO->delete()
    ->restrict('id = :id')
    ->run();
</pre>

<h3>Paginación</h3>
<p>De manera sencilla es posible paginar el resultado de una consulta mediante SQO, lo que se debe tener en cuenta
es el arreglo que necesita el método page para funcionar.</p>

<pre class="prettyprint">
[
    'page' => 0,
    'size' => 12
]
</pre>

<p>Si no se suministra ninguno de estos valores, scoop tomara por defecto los acá descritos y retornara un arreglo asociativo
con una estructura similar a la siguiente.</p>

<pre class="prettyprint">
[
    'page' => 0,
    'size' => 12,
    'result' => array(),
    'total' => 0
]
</pre>

<p>En donde page y size son los mismo datos enviados o colocados por defecto, mientras result y total hacen referencia a la
consulta realizada.</p>

<h2>
    <a href="#epm">Entity Persistence Management (EPM)</a>
    <span class="anchor" id="epm">...</span>
</h2>

<p>El mapeo se realiza desde el archivo de configuración principal bajo la key <code>model</code>.</p>

<h3>Entidades</h3>

<p>Dentro del key <code>entities</code> se debe generar el mapeo con cada una de las clases que representa la entidad
dentro de la aplicación, las keys principales en esta configuración son <code>table</code> y <code>properties</code>. Dentro
de table se coloca el nombre de la tabla, este valor es obligatorio para realizar el correcto mapeo de entidades.</p>

<p>Dentro de properties defino cada propiedad de la entidad, el unico valor obligatorio en este punto es el tipo, como tipo
se puede definir los siguientes:</p>

<ul>
    <li>string</li>
    <li>int</li>
    <li>numeric</li>
    <li>date</li>
    <li>bool</li>
    <li>serial</li>
</ul>

<p>Si se coloca un tipo de dato diferente a los especificados o a un custom type el sistema tratara de inferir el tipo. El resto de elementos
para la definición de properties como la columna(column) son opcionales y/o dependen del tipo que se esta asignanado. En el caso de
column si se establece este sera el nombre que buscara dentro de la base de datos, en caso de no definirse se convertira en snake case
la propiedad y este sera el nombre que buscará</p>

<pre class="prettyprint">
[
    'entities' => [
        Invoice::class => [
            'table' => 'public.invoices',
            'properties' => [
                'id' => ['type' => 'serial'],
                'number' => ['type' => 'string', 'length' => 20],
                'customer' => ['type' => 'int', 'column' => 'customer_id']
            ]
        ]
    ]
]
</pre>

<p>Las entidades por defecto esperan que exista una propiedad id, de no ser así se debe especificar cual de todas las propiedades
va a funir como identificar de la entidad, esto se realiza mediante el key <code>id</code>.</p>

<pre class="prettyprint">
[
    'entities' => [
        Invoice::class => [
            'table' => 'public.invoices',
            'id' => 'invoiceId'
            'properties' => [
                'invoiceId' => ['type' => 'serial']
            ]
        ]
    ]
]
</pre>

<h3>Relaciones</h3>

<p>Otra key que se debe configurar dentro de la entidad es <code>relations</code>, esta me define todas las entiades que maneja esa entidad
en concreto como key de la relación se debe colocar el mismo nombre de la propiedad y como valor un array de tres pocisiones en el cual
la primera posición representa la clase con la cual se relaciona, la seguna el campo que recibe la relación en la otra entidad (en caso de existir),
la última posición representa el tipo de relacvión que se establece: ONE_TO_MANY, MANY_TO_MANY y MANY_TO_ONE.</p>

<pre class="prettyprint">
[
    'entities' => [
        Invoice::class => [
            'table' => 'public.invoices',
            'properties' => [
                'id' => ['type' => 'serial'],
                'state' => ['type' => 'smallint'],
                'number' => ['type' => 'string', 'length' => 20],
                'customer' => ['type' => 'int', 'column' => 'customer_id']
            ],
            'relations' => [
                'items' => [Item::class, 'invoice', Relation::ONE_TO_MANY],
                'payments' => [Payment::class, 'invoices:invoice_payments', Relation::MANY_TO_MANY],
                'customer' => [Customer::class, 'invoices', Relation::MANY_TO_ONE]
            ]
        ]
    ]
]
</pre>

<p>Caso aparte merecen las relaciones MANY_TO_MANY, pues estas deben ser definidas fuera de la entidad en su propia key <code>relations</code>
en estas se deben definir el nombre de la relación que luego debera ser usada dentro de la relación e la entiad despues del caracter <code>:</code>
en el segundo parametro de la misma. Una vez definida la key de la relación deben estabecer las key <code>table</code> y <code>entities</code>
en la primera se coloca la tabla que sirve como tabla de "rompomiento" en la base de datos, en a segunda key se definen las entidades que se relacionan,
cada una definiendo su tipo(type) y columna(column).</p>

<pre class="prettyprint">
[
    'relations' => [
        'invoice_payments' => [
            'table' => 'public.payment_invoice',
            'entities' => [
                Invoice::class => ['type' => 'int', 'column' => 'invoice_id'],
                Payment::class => ['type' => 'int', 'column' => 'payment_id']
            ]
        ]
        ]
]
</pre>

<h3>Custom types</h3>

<p>Como se menciono anteriormente existen <a href="#epm">tipos definido</a> para las entidades, ero esto en ocaciones puede
llegar a no ser suficiente por lo cual se pueden crear tipos personalizados. Para esto lo primero que se debe hacer es
registrar el tipo en el key correspondiente colocando el nombre del tipo y la clase que lo implementa, ara hacer uso del tipo
se debe usar con el nombre dentro e la entiad.</p>

<pre class="prettyprint">
[
    'entities' => [
        Customer::class => [
            'table' => 'public.customers',
            'properties' => [
                'id' => ['type' => 'serial'],
                'name' => ['type' => 'string', 'length' => 20],
                'state' => ['type' => 'state']
            ]
        ]
    ],
    'types' => ['state' => State::class]
]
</pre>

<p>Implementar un tipo es crear una clase con los métodos <code>assemble</code> y <code>disassemble</code>; el primero
es para ingresar el dato desde la tabla a la entidad y el segundo para enviar el dato a la tabla; en este último
se debe tener especial cuidado ues no solo viene el dato desde la entidad, el sistema usa este método de igual manera
para saber que dato se encuentra ersistido y realizar la comparación al momento de generar actualizaciones, esto
quiere decir que disassemble se usa tanto para guardar de la entidad a la tabla como para normalizar los datos que se sacan
de la tabla.</p>

<pre class="prettyprint">
class State extends Integer
{
    public function disassemble(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }
        return match (true) {
            $value instanceof ActiveState => 0,
            $value instanceof InactiveState => 1,
            default => 1
        };
    }

    public function assemble(mixed $value): StateValue
    {
        return match ($value) {
            0 => new ActiveState(),
            1 => new InactiveState(),
            default => new ActiveState()
        };
    }
}
</pre>

<h3>Value Objects</h3>

<p>A parte de los tipos predefinidos para cada entidad o los custom types tambien se puede usar los value objects como tipo de una
propiedad, para esto se debe definir dentro del grupo de <code>values</code> con las propiedades del value object que continenen a
su vez el tipo y la columna (de ser necesaria).</p>

<pre class="prettyprint">
    [
    'properties' => [
        Customer::class => [
            'table' => 'public.customers',
            'properties' => [
                'id' => ['type' => 'serial'],
                'name' => ['type' => 'string', 'length' => 20],
                'address' => ['type' => Address::class],
                'email' => ['type' => Email::class],
                'state' => ['type' => 'state']
                ]
        ]
    ],
    'values' => [
        Email::class => [
            'value' => ['type' => 'string', 'length' => 60]
            ],
            Address::class => [
            'street' => ['type' => 'string'],
            'city' => ['type' => 'string'],
            'zip' => ['type' => 'string', 'column' => 'zip_code']
        ]
    ]
    ]
</pre>

<h3>Herencia</h3>

<p>La forma recomendable para el manejo de herencia es crear una tabla por subclase (Class Table Inheritance), para esto solo basta
con mapear cada una de las tablas a cada clase y el sistema cuando halla la herencia sabe a que clase debe mapear cada propiedad.</p>

<p class="doc-alert">Se debe tener en cuenta que usar discriminator no es lo más recomendado dado que el sistema debe conocer el valor
de cada columna para saber que clase instanciar, esto hace que se realicen consultas extras a la base de datos. Una vez mencionado esto
se puede decir que existen casos de uso interesantes para ente enfoque como el implementar el mapeo en una sola tabla (Single Table Inheritance)
esto permite tener el discriminator sin la desventaja de realizar multiples consultas a la base de datos.</p>

<p>Si se ve en la necesidad de distinguir clases que implementen herencia se debe configurar un <code>discriminator</code>, este
es una columna dentro de la tabla que no carga en la entida pero que indica que tipo de clase debe generarse. El map como
debo mapear el valor e la base de datos a la clase que debo instanciar, en caso que el dato guardado no corresponda a ninguno de
los mapeados se tratara de instanciar una clase del tipo especificado, en el caso presentacod a continuación esta clase sería
<code>Invoice</code>.</p>

<pre class="prettyprint">
[
    'entities' => [
        Customer::class => [
            'table' => 'public.customers',
            'discriminator' => [
                'column' => 'type',
                'map' => [
                    PremiumCustomer::class => 1
                ]
            ],
            'properties' => [
                'id' => ['type' => 'serial'],
                'name' => ['type' => 'string', 'length' => 20]
            ]
        ]
    ]
]
</pre>

<p class="doc-danger">En futuras versiones se puede implementar una única tabla por subclase (Concrete Table Inheritance).</p>

<h2>
    <a href="#repositories">Repositorios</a>
    <span class="anchor" id="repositories">...</span>
</h2>

<pre class="prettyprint">
&lt;?php

namespace App\Infrastructure\Repository;

use Scoop\Persistence\Entity\Manager;
use App\Domain\Repository\InvoiceCommand as InvoiceRepository;
use App\Domain\Entity\Invoice;
use App\Domain\Value\InvoiceId;


class InvoiceCommand implements InvoiceRepository
{
    private Manager $em;

    public function __construct(Manager $em)
    {
        $this->em = $em;
    }
    
    public function save(Invoice $invoice): void
    {
        $this->em->save($invoice);
        $this->em->flush();
    }

    public function getId(InvoiceId $id): Invoice
    {
        return $this->em->search(Invoice::class)
        ->aggregate('items')
        ->aggregate('customer')
        ->aggregate('payments.customer')
        ->get($id->getValue());
    }

    public function searchByEmail($email): array
    {
        return $this->em->search(Invoice::class)
        ->aggregate('items')
        ->aggregate('customer')
        ->aggregate('payments.customer')
        ->matching('payments.customer.email = :name', ['name' => $email]);
    }
}
</pre>

<h2>
    <a href="#dsl">Criteria (DSL)</a>
    <span class="anchor" id="dsl">...</span>
</h2>
<pre class="prettyprint">
public function search(Criteria $criteria): array
{
    $mapper = new CriteriaEPM($criteria);
    return $this->em->search(Invoice::class)
    ->aggregate('items')
    ->aggregate('customer')
    ->aggregate('payments.customer')
    ->matching($mapper->getDSL(), $mapper->getFilters(), $mapper->getOrder());
}
</pre>
