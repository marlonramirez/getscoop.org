<p>El desarrollo de software se divide en dos grandes dimensiones: <b>estratégica</b> y <b>táctica</b>. La fase estratégica se centra en el modelado de la solución para resolver las necesidades de un dominio de negocio; seguidamente, el proceso táctico define los patrones de arquitectura y las tecnologías que sostendrán dicho modelo.</p>

<p>Scoop proporciona el andamiaje táctico necesario pero se declara <b>agnóstico al modelo de negocio</b>. El motor no impone una creencia férrea sobre la implementación, otorgando soberanía total al arquitecto para diseñar la estrategia que mejor se adapte al problema. Para materializar esta visión, Scoop integra <b>EPM (Entity Persistence Management)</b>.</p>

<p>EPM es un motor de persistencia basado en el patrón <b>Data Mapper</b> y diseñado bajo el principio de <b>Persistence Ignorance</b>. A diferencia de otros sistemas, tus entidades de dominio no heredan de clases del framework ni contienen anotaciones que corrompan su propósito. El mapeo se define de forma externa, garantizando que tu Dominio permanezca puro, imperturbable y altamente testeable.</p>

<ul>
    <li><a href="#entities">Mapeo de Entidades POPO</a></li>
    <li><a href="#vo">Value Objects (Objetos de Valor)</a></li>
    <li><a href="#relations">Gestión Compleja de Relaciones</a></li>
    <li><a href="#inheritance">Estrategias de Herencia y Polimorfismo</a></li>
    <li><a href="#types">Custom Types: Extendiendo el Motor</a></li>
    <li><a href="#repositories">Repositorios y Agregados</a></li>
    <li><a href="#dsl">Abstracción de Consulta complejas</a></li>
</ul>

<h2>
    <a href="#entities">Mapeo de Entidades POPO</a>
    <span class="anchor" id="entities">...</span>
</h2>

<p>Dentro de la clave <code>entities</code> se debe generar el mapeo con cada una de las clases que representa la entidad dentro de la aplicación. Las propiedades principales son <code>table</code> (nombre de la tabla, obligatorio) y <code>properties</code> (mapeo de campos).</p>

<p>Dentro de <code>properties</code> se define cada atributo de la entidad. El único valor obligatorio es el tipo (<code>type</code>).</p>

<p><b>Tipos nativos soportados:</b> <code>string</code>, <code>int</code>, <code>numeric</code>, <code>date</code>, <code>bool</code>, <code>serial</code> (autoincremental) y <code>json</code>.</p>

<p>Si se utiliza un tipo de dato no especificado o un <a href="#types">Custom Type</a>, el sistema inferirá el tipo basándose en la información de la base de datos. Otros elementos como la columna (<code>column</code>) son opcionales; si no se definen, Scoop convertirá automáticamente el nombre de la propiedad a <i>snake_case</i> y lo asignara como nombre de columna.</p>

<pre><code class="language-php">[
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
</code></pre>

<p>El motor espera por defecto una propiedad <code>id</code>. Si la entidad utiliza una nomenclatura distinta para su identificador, debe especificarse mediante la clave <code>id</code>:</p>

<pre><code class="language-php">[
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
</code></pre>

<h2>
    <a href="#vo">Value Objects (Objetos de Valor)</a>
    <span class="anchor" id="vo">...</span>
</h2>

<p>Scoop permite mapear <b>Value Objects</b> mediante la clave <code>values</code>. Esto habilita el uso de propiedades compuestas inmutables que el motor aplana automáticamente en la base de datos durante la persistencia.</p>

<pre><code class="language-php">[
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
</code></pre>

<p>Si la columna de la base de datos se llama diferente a la propiedad, se debe agregar la clave correspondiente para aclararlo (<code>column</code>).</p>

<h2>
    <a href="#relations">Gestión Compleja de Relaciones</a>
    <span class="anchor" id="relations">...</span>
</h2>

<p>Las relaciones definen la topología de los Agregados. Scoop gestiona la hidratación recursiva basándose en un array de definición: <code>[ClaseDestino, PropiedadInversa, TipoRelacion]</code>.</p>

<pre><code class="language-php">[
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
</code></pre>

<h3>Relaciones Muchos a Muchos (MANY_TO_MANY)</h3>

<p>Estas relaciones requieren una definición externa para configurar la <b>tabla de rompimiento</b>. Se utiliza el sufijo <code>:nombre_relacion</code> para vincular la propiedad de la entidad con la configuración de la tabla de relación.</p>

<pre><code class="language-php">[
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
</code></pre>

<h2>
    <a href="#inheritance">Estrategias de Herencia y Polimorfismo</a>
    <span class="anchor" id="inheritance">...</span>
</h2>

<p>La forma recomendada para el manejo de herencia es <b>Class Table Inheritance (CTI)</b>, donde cada subclase posee su propia tabla. Scoop sincroniza automáticamente la jerarquía de clases con el esquema relacional.</p>

<p class="doc-alert"><b>Nota de Arquitectura:</b> El uso de discriminadores es potente para el polimorfismo, pero puede implicar consultas adicionales en ciertas estrategias de carga. Scoop también permite implementar <i>Single Table Inheritance</i> (STI) para maximizar el rendimiento manteniendo el aislamiento lógico.</p>

<p>Si se requiere distinguir clases en una jerarquía, se configura un <code>discriminator</code>. Este define una columna que indica al motor qué tipo de clase instanciar.</p>

<pre><code class="language-php">[
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
</code></pre>

<p>El sistema de persistencia soporta herencia recursiva; mediante el <code>DiscriminatorMapper</code>, Scoop puede resolver jerarquías polimórficas complejas, instanciando la subclase exacta basándose en el estado de la base de datos de forma totalmente transparente para el dominio.</p>

<p class="doc-danger">En futuras versiones se puede implementar una única tabla por subclase (Concrete Table Inheritance).</p>

<h2>
    <a href="#types">Custom Types: Extendiendo el Motor</a>
    <span class="anchor" id="types">...</span>
</h2>

<p>Como se mencionó anteriormente, existen <a href="#entities">tipos definidos</a> para las entidades, pero en ocasiones esto puede no ser suficiente. Scoop permite crear tipos personalizados registrando la clase que implementa la lógica de transformación.</p>

<pre><code class="language-php">[
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
</code></pre>

<p>Para la lógica de transformación específica, la clase debe implementar al menos los métodos <code>assemble</code> (hidratación hacia la entidad) y <code>disassemble</code> (persistencia hacia la DB).</p>

<pre><code class="language-php">class State extends Integer
{
    public function disassemble(mixed $value): int
    {
        return match (true) {
            $value instanceof InactiveState => 0,
            $value instanceof ActiveState => 1,
            default => 1
        };
    }

    public function assemble(mixed $value): StateValue
    {
        return match ($value) {
            0 => new InactiveState(),
            1 => new ActiveState(),
            default => new ActiveState()
        };
    }
}
</code></pre>

<p>Otros métodos opcionales incluyen <code>isAutoincremental</code> (para refrescar valores generados por la DB) y <code>comparate</code> (para definir la lógica de igualdad en la detección de cambios).</p>

<pre class="language-php"><code>class Serial extends Integer
{
    public function isAutoincremental()
    {
        return true;
    }
}
</code></pre>

<p>Para el caso de <code>comparate</code> se debe establecer solo cuando la comparación entre lo que se genera desde la entidad y lo que se obtiene de la base de datos llea a ser diferente, un ejemplo es al momento de guardar un tipo CHARACTER el cual es llenado de espacios hasta completar la cantidad de caracteres definidos, por lo cual en la entidad puedo tener un campo <code>'001'</code> que guardá en base de datos <code>'&nbsp;&nbsp;&nbsp;&nbsp;001'</code>; algo similar sucede por ejemplo con los tipos de coma flotante.</p>

<pre class="language-php"><code>class Varchar
{
    public function disassemble($value)
    {
        return trim($value);
    }

    public function assemble($value)
    {
        return trim($value);
    }

    public function comparate($oldValue, $newValue)
    {
        return trim($oldValue) === $newValue;
    }
}
</code></pre>

<h2>
    <a href="#repositories">Repositorios y Agregados</a>
    <span class="anchor" id="repositories">...</span>
</h2>

<p>En Scoop, el <b>Repositorio</b> es el puerto de salida que protege la integridad del dominio. Al inyectar el <code>EntityManager</code>, puedes gestionar Agregados completos de forma eficiente.</p>

<h3>Carga Explícita (Anti N+1)</h3>

<p>Scoop prohíbe el <i>Lazy Loading</i> por diseño. Para recuperar relaciones, se debe utilizar el método <b><code>aggregate()</code></b>, permitiendo que el motor realice Joins inteligentes u optimizaciones de carga en lote.</p>

<pre><code class="language-php">&lt;?php

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
</code></pre>

<p class="doc-alert"><b>Unit of Work:</b> El motor rastrea internamente los cambios en las entidades. Al invocar <code>$this->em->flush()</code>, Scoop genera y ejecuta atómicamente todas las sentencias SQL de actualización necesarias.</p>

<h2>
    <a href="#dsl">Abstracción de Consulta complejas</a>
    <span class="anchor" id="dsl">...</span>
</h2>

<p>Para evitar que la lógica de persistencia (SQL) se filtre hacia la capa de aplicación, Scoop recomienda usar un sistema de <b>Criteria</b>. Esto permite definir intenciones de búsqueda dinámicas mediante un <i>Domain-Specific Language</i> (DSL) que el motor traduce automáticamente. No se provee directamente la implementación del patrón dado que este debería hacer parte del dominio mismo del negocio.</p>

<p>El método <code>matching()</code> recibe tres componentes fundamentales para orquestar la consulta:</p>

<ol>
    <li><b>DSL:</b> La regla de filtrado expresada en términos de las propiedades de la entidad (ej: <code>payments.customer.email = :email</code>).</li>
    <li><b>Filters:</b> Los valores que alimentarán los parámetros del DSL.</li>
    <li><b>Order:</b> La definición del ordenamiento de los resultados.</li>
</ol>

<pre><code class="language-php">public function search(Criteria $criteria): array
{
    $mapper = new CriteriaEPM($criteria);
    return $this->em->search(Invoice::class)
    ->aggregate('items')
    ->aggregate('customer')
    ->aggregate('payments.customer')
    ->matching($mapper->getDSL(), $mapper->getFilters(), $mapper->getOrder());
}
</code></pre>

<p class="doc-alert"><b>Pureza Táctica:</b> Con Criteria, el desarrollador puede construir filtros complejos desde la interfaz de usuario o servicios de aplicación sin que estas capas conozcan la estructura de la base de datos o la sintaxis SQL subyacente.</p>
