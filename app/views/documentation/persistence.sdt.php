<p>La capa de persistencia atómica de Scoop está diseñada para ofrecer un control total sobre la infraestructura de datos. Proporciona las herramientas necesarias para interactuar con el motor de base de datos de forma directa y eficiente, eliminando las capas de abstracción pesadas cuando lo que se requiere es rendimiento bruto y manipulación precisa del esquema.</p>

<p>A través de esta capa, Scoop gestiona la conectividad, el versionamiento de la estructura y la ejecución de sentencias DML, sirviendo como el cimiento técnico sobre el cual se construye la lógica de persistencia de la aplicación.</p>

<p><ul>
    <li><a href="#connection">Infraestructura de Conexión</a></li>
    <li><a href="#quoting">Sistema de Quoting Universal</a></li>
    <li><a href="#builder">Builder Service (Factory)</a></li>
    <li><a href="#structs">Estructura como Código</a></li>
    <li><a href="#sqo">Consultas Atómicas (SQO)</a></li>
</ul></p>

<h2>
    <a href="#connection">Infraestructura de Conexión</a>
    <span class="anchor" id="connection">...</span>
</h2>

<p>La clase <code>Connection</code> constituye el adaptador de bajo nivel de Scoop. Este componente encapsula la lógica de <b>PDO</b> para garantizar un tratamiento de datos homogéneo, permitiendo que la aplicación interactúe con diversos motores (MySQL, PostgreSQL, SQLite, SQL Server) de forma transparente para el Dominio.</p>

<p class="doc-alert"><b>Deprecación:</b> La clase <code>DBC</code> (Data Base Connection) está deprecada desde v0.8. Use <code>Connection</code> en su lugar. DBC permanece disponible para compatibilidad hacia atrás.</p>

<h3>Configuración de Bundles</h3>

<p>Scoop permite definir múltiples conexiones de forma declarativa bajo la clave <code>db</code> en el archivo de configuración. Cada conexión se empaqueta como un <b>bundle</b> identificado por un nombre clave, permitiendo que una misma aplicación orqueste diferentes fuentes de datos simultáneamente.</p>

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

<h3>Gestión del Ciclo de Vida</h3>

<p>Para invocar una conexión, se utiliza el <b>Contexto</b> del motor. El método <code>Context::connect()</code> actúa como un gestor de estado que recupera la instancia solicitada. Si no se suministra un parámetro, el motor asume el bundle <code>default</code>.</p>

<pre><code class="language-php">$db = \Scoop\Context::connect();
$authDb = \Scoop\Context::connect('auth');
</code></pre>

<p class="doc-alert"><b>Lazy Handshake (Rendimiento Crítico):</b> Definir múltiples bundles no penaliza el rendimiento del sistema. Scoop implementa una política de conexión perezosa: el motor no abre el socket de conexión física ni consume recursos de red hasta el instante exacto en que un componente solicita el bundle. Esto es vital para arquitecturas de microservicios o BFFs que solo requieren persistencia en rutas específicas.</p>

<h2>
    <a href="#quoting">Sistema de Quoting Universal</a>
    <span class="anchor" id="quoting">...</span>
</h2>

<p>Scoop v0.8.1 introduce un sistema de quoting universal que permite escribir queries SQL una vez y ejecutarlas en cualquier motor de base de datos sin modificaciones. Esto se logra mediante dos métodos complementarios en la clase <code>Connection</code>.</p>

<h3>quoteColumn() - Identificadores SQL</h3>

<p>El método <code>quoteColumn()</code> aplica automáticamente el quote correcto según el motor de base de datos conectado:</p>

<pre><code class="language-php">$connection = \Scoop\Context::connect();

/** Auto-detecta el motor y aplica quotes correctos:
MySQL: `users`
PostgreSQL: "users"
SQL Server: [users]
*/
$table = $connection->quoteColumn('users');

/** Soporta notación schema.table.column
MySQL: `public`.`users`.`id`
PostgreSQL: "public"."users"."id"
SQL Server: [public].[users].[id]
*/
$column = $connection->quoteColumn('public.users.id');
</code></pre>

<p><b>Validación automática:</b> El método valida que el identificador tenga un formato SQL válido (<code>/^[a-zA-Z_][a-zA-Z0-9_\.\$]*$/</code>), previniendo inyecciones SQL accidentales.</p>

<h3>quoteCriteria() - Sintaxis SQL Universal</h3>

<p>El método <code>quoteCriteria()</code> permite usar la <b>sintaxis de SQL Server con brackets</b> <code>[column]</code> como notación universal para garantizar que tus alias y columnas no colisionen con palabras reservadas del motor SQL. Scoop automáticamente convierte a quotes específicos del motor:</p>

<pre><code class="language-php">/** Escribes UNA VEZ (sintaxis SQL Server) y ejecuta en CUALQUIER motor:
MySQL:      `status` = :status AND `user`.`id` = :id
PostgreSQL: "status" = :status AND "user"."id" = :id
SQL Server: [status] = :status AND [user].[id] = :id (sin cambios)
*/
$where = $connection->quoteCriteria('[status] = :status AND [user].[id] = :id');
</code></pre>

<p><b>Ventajas del sistema:</b></p>
<ul>
    <li><b>Write Once, Run Anywhere:</b> Queries portables entre motores</li>
    <li><b>SQL Server como Base:</b> Sintaxis más restrictiva detecta errores temprano</li>
    <li><b>Zero Overhead:</b> Simple string replacement, sin parsers complejos</li>
    <li><b>Expresiones Complejas:</b> Funciona con WHERE, HAVING, ORDER BY, GROUP BY, JOIN</li>
</ul>

<h3>Soporte Multi-Motor</h3>

<p>El sistema de quoting soporta completamente los siguientes motores:</p>

<table>
    <thead>
        <tr>
            <th>Motor</th>
            <th>Quote Character</th>
            <th>Ejemplo</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>MySQL</td>
            <td><code>`</code> (backtick)</td>
            <td><code>`users`.`name`</code></td>
        </tr>
        <tr>
            <td>PostgreSQL</td>
            <td><code>"</code> (double quote)</td>
            <td><code>"users"."name"</code></td>
        </tr>
        <tr>
            <td>SQLite</td>
            <td><code>"</code> (double quote)</td>
            <td><code>"users"."name"</code></td>
        </tr>
        <tr>
            <td>SQL Server</td>
            <td><code>[]</code> (brackets)</td>
            <td><code>[users].[name]</code></td>
        </tr>
        <tr>
            <td>FreeTDS (dblib)</td>
            <td><code>[]</code> (brackets)</td>
            <td><code>[users].[name]</code></td>
        </tr>
    </tbody>
</table>

<h2>
    <a href="#builder">Builder Service (Factory)</a>
    <span class="anchor" id="builder">...</span>
</h2>

<p>El <code>Builder</code> es un servicio factory que crea instancias de consultas SQL (<code>SQO</code>). A partir de v0.8.1, se recomienda inyectar el Builder en lugar de instanciar SQO directamente, siguiendo el patrón de Dependency Injection.</p>

<h3>Uso Básico</h3>

<pre><code class="language-php">class UserController
{
    private $builder;

    public function __construct(private \Scoop\Persistence\Builder $builder)
    {
        $this->builder = $builder;
    }

    public function listUsers()
    {
        $users = $this->builder->build('users', 'u', 'default');
        // Equivalente a (deprecado):
        // $users = new \Scoop\Persistence\SQO('users', 'u', 'default');
    }
}
</code></pre>

<h3>Patrón Repository</h3>

<p>El Builder se integra perfectamente con el patrón Repository:</p>

<pre><code class="language-php">class UserRepository
{
    private $builder;

    public function __construct(\Scoop\Persistence\Builder $builder)
    {
        $this->builder = $builder;
    }

    public function findByEmail($email)
    {
        return $this->builder
            ->build('users')
            ->read()
            ->restrict('[email] = :email')
            ->run(['email' => $email])
            ->fetch();
    }

    public function create($data)
    {
        return $this->builder
            ->build('users')
            ->create($data)
            ->run();
    }
}
</code></pre>

<h3>Ventajas del Builder</h3>

<ul>
    <li><b>Testability:</b> Fácil de mockear en tests unitarios</li>
    <li><b>Dependency Injection:</b> Compatible con contenedores DI</li>
    <li><b>Separation of Concerns:</b> Repositories no conocen detalles de conexión</li>
    <li><b>Framework Convention:</b> Sigue patrones establecidos de la industria</li>
</ul>

<h2>
    <a href="#structs">Estructuras como Código</a>
    <span class="anchor" id="structs">...</span>
</h2>

<p>Los <b>Structs</b> constituyen el sistema de versionamiento de base de datos de Scoop. A diferencia de las migraciones tradicionales que utilizan lenguajes intermedios, Scoop apuesta por el uso de <b>SQL nativo</b>. Esto garantiza que cada instrucción ejecutada sea transparente, aprovechando al máximo las capacidades específicas de cada motor (PostgreSQL, MySQL, etc.) sin el "impuesto de abstracción" de un Query Builder.</p>

<p>Fiel al principio de inmutabilidad, un Struct no implementa mecanismos de <i>rollback</i> automáticos. Scoop fomenta una estrategia de <b>despliegue hacia adelante (Forward-only)</b>: cualquier modificación o corrección del esquema debe realizarse mediante un nuevo archivo de estructura, garantizando un histórico de cambios íntegro y predecible.</p>

<h3>Creación de Estructuras</h3>

<p>La ejecución de los Structs es estrictamente secuencial, basada en el peso lexicográfico de los archivos (usualmente determinado por un <i>timestamp</i>). Para garantizar el orden correcto y facilitar la organización, se utiliza el comando <code>new struct</code> del CLI <code>ice</code>.</p>

<pre><code class="language-shell">php app/ice new struct --schema=auth --name=create_users_table</code></pre>

<p>Parámetros soportados:</p>

<p><ul>
    <li><b><code>--schema</code>:</b> Permite organizar los archivos en subcarpetas lógicas. Esto facilita el aislamiento de estructuras por módulos o <b>Bounded Contexts</b>.</li>
    <li><b><code>--name</code>:</b> Añade un sufijo descriptivo al nombre del archivo para facilitar su identificación manual más allá de la marca de tiempo.</li>
</ul></p>

<h3>Sincronización y Ejecución (dbup)</h3>

<p>Una vez definidos los archivos SQL en el directorio <code>app/structs</code>, se utiliza el comando <code>dbup</code> para sincronizar el estado deseado con la base de datos física. Scoop rastrea internamente qué archivos han sido ejecutados para evitar duplicidades.</p>

<pre><code class="language-shell">php app/ice dbup --name=default --schema=auth --user=postgres --password=$DB_PASSWORD</code></pre>

<p>Opciones de ejecución:</p>

<p><ul>
    <li><b><code>--schema</code>:</b> Ejecuta únicamente los Structs contenidos en un directorio o "esquema" específico, permitiendo despliegues modulares.</li>
    <li><b><code>--name</code>:</b> Especifica el <i>bundle</i> de conexión definido en la configuración (por defecto utiliza <code>default</code>).</li>
    <li><b><code>--user</code> / <code>--password</code>:</b> Permite sobrescribir las credenciales de conexión en tiempo de ejecución, ideal para procesos de CI/CD o mantenimiento por parte de administradores de bases de datos (DBA).</li>
</ul></p>

<p class="doc-alert"><b>Ubicación Personalizada:</b> El directorio base de los archivos SQL se define por defecto en <code>app/structs</code>, pero puede ser modificado globalmente desde el archivo de configuración principal de la aplicación.</p>

<h2>
    <a href="#sqo">Consultas Atómicas (SQO)</a>
    <span class="anchor" id="sqo">...</span>
</h2>

<p><b>SQO</b> constituye el motor atómico de persistencia de Scoop. Proporciona una interfaz orientada a objetos para interactuar con la base de datos de forma fluida y dinámica, eliminando la fragilidad de concatenar strings SQL manuales sin el peaje de rendimiento de un ORM pesado.</p>

<p>Para instanciar un objeto <code>SQO</code>, se debe indicar la tabla principal y, opcionalmente, un alias y el nombre del <i>bundle</i> de conexión:</p>

<pre><code class="language-php">$books = new \Scoop\Persistence\SQO('book', 'b', 'default');
</code></pre>

<p>Un objeto SQO provee métodos para orquestar las cuatro operaciones fundamentales (CRUD) y la recuperación de metadatos de identidad (<code>getLastId</code>).</p>

<h3>Creación e Inserción de Datos</h3>

<p>El método <code>create()</code> devuelve una factoría (<code>SQO\Factory</code>) que permite gestionar la inserción de datos bajo tres modalidades técnicas:</p>

<h4>Inserción Atómica (Asociativa)</h4>

<p>La forma más directa es pasar un array asociativo donde las llaves corresponden a las columnas de la tabla. Scoop se encarga de la sanitización y el reemplazo de valores de forma transparente.</p>

<pre><code class="language-php">$books->create([
    'name' => 'Angels & Demons',
    'author' => 'Dan Brown',
    'year' => '2009'
])->run();
</code></pre>

<h4>Inserción Múltiple (Chaining)</h4>

<p>SQO permite encadenar llamadas a <code>create()</code> para generar una única sentencia SQL de inserción múltiple, optimizando los tiempos de red y ejecución del motor de base de datos.</p>

<pre><code class="language-php">$books->create(['name' => 'It', 'author' => 'Stephen King'])
->create(['name' => 'The Shining', 'author' => 'Stephen King'])
->run();
</code></pre>

<h4>Cargas Masivas (Bulk Load)</h4>

<p>Para procesar grandes volúmenes de datos (como la lectura de un CSV), se puede definir un orden de columnas inicial y posteriormente suministrar solo los valores indexados, reduciendo el consumo de memoria por iteración.</p>

<pre><code class="language-php">$creator = $books->create(['name', 'author', 'year']);
if (($handle = fopen("test.csv", "r")) !== false) {
    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
        $creator->create($data)
    }
}
$creator->run();
</code></pre>

<h4>INSERT SELECT</h4>

<p>SQO permite realizar inserciones basadas en el resultado de una consulta previa pasando un objeto <code>SQO\Reader</code>.</p>

<pre><code class="language-php">$reader = $oldBooks->read('name', 'author')->filter('year > :year');
$books->create(['name', 'author'], $reader)->run(['year' => 1989]);
</code></pre>

<h3>Lectura y Filtrado</h3>

<p>El método <code>read()</code> habilita una interfaz fluida para la recuperación de datos, permitiendo el uso de filtros dinámicos, donde la regla solo se añade al SQL si el parámetro está presente (<code>filter</code>), y restricciones de infraestructura, que son obligatorias y lanzan una excepción si el parámetro no es suministrado (<code>restrict</code>).</p>

<pre><code class="language-php">$books->read('name', 'author')
->filter('name LIKE %:name%')
->restrict('year = 2009')
->run(['name' => 'Angels']);
</code></pre>

<h3>Actualización y Eliminación</h3>

<p>Las operaciones de escritura (<code>update</code> y <code>delete</code>) exigen el uso de <code>restrict()</code> para delimitar el alcance de la operación, garantizando la seguridad de los datos.</p>

<pre><code class="language-php">$books->update(['year' => 2010])->restrict('id = :id')->run(['id' => 1]);
$books->delete()->restrict('id = :id')->run(['id' => 1]);
</code></pre>

<h3 id="pagination">Paginación Nativa</h3>

<p>SQO integra la paginación como un ciudadano de primera clase. El método <code>page()</code> automatiza la ejecución de dos consultas paralelas (obtención de datos y conteo total) para devolver una estructura de metadatos completa.</p>

<pre><code class="language-php">$result = $books->read()->page([
    'page' => 0,
    'size' => 12
]);
</code></pre>

<p>El motor retornará un objeto estructurado listo para ser procesado por la capa de aplicación o la vista:</p>

<pre><code class="language-php">[
    'page' => 0,
    'size' => 12,
    'total' => 150,
    'result' => [...]
]
</code></pre>

<h3>Operadores atómicos</h3>

<p>El método <code>update</code> detecta operadores matemáticos al final de las llaves (+, -, *, /, %); con lo cual puedes realizar actualizaciones incrementales directamente en la base de datos de forma segura.</p>

<pre><code class="language-php">$books->update([
    'stock+' => 10,
    'views-' => 1
])->restrict('id = :id')->run(['id' => 1]);
</code></pre>
