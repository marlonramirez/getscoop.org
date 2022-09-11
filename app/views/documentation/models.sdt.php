@extends 'layers/docs'
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

<h2><a href="#agnosticism">Agnosticismo sobre el modelo</a><span class="anchor" id="agnosticism">...</span></h2>
<p>El agnosticismo se define como la postura que considera que los valores de verdad de ciertas afirmaciones 
son desconocidas o inherentemente incognoscibles, el agnosticismo es la mera suspensión de la creencia.</p>

<p>Se dice que scoop es agnostica por que no tiene una creencia ferrea  en como debe ser implementado el dominio del 
negocio, esto quiere decir que se pueden usar diferentes técnicas, patrones de diseño y arquitecturas, al igual que 
el enfoque que mejor se ajuste al diseño del  modelo (TDD, DDD, ATDD, BDD). Pero el agnosticismo se aplica solo a la 
definición del domino ya que scoop cuenta con diversas herramientas para facilitar el procesamiento de datos e 
implementación de técnicas.</p>

<h2><a href="#dbc">Data Base Conection</a><span class="anchor" id="dbc">...</span></h2>
<p>Cualquier tipo de base de datos se debe conectar mediante un driver PDO, ya que este se encarga de administrar las
conexiones, esto garantiza la homogeniedad en el tratamiento de datos. El uso y configuración de la conexión se manejea 
mediante la clase DBC acronimo de Data Base Connection y que se provee mediante los archivo de configuración.</p>

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

<p>En el ejemplo anterior se proveen dos conexiones diferentes, cada una empaquetada con un nombre clave; 
para usar alguna conexión se debe hacer uso de la clase <code>\Scoop\Context</code> e incovar el método 
<code>connect</code>, cuando no se envia ningún parametro al método este toma la conexión default, para escoger 
otro tipo de conexión diferente se debe enviar la clave de la conexión <code>\Scoop\Conext::connect('auth')</code>.</p>

<h2><a href="#sqo">SQO</a><span class="anchor" id="sqo">...</span></h2>
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

<p class="doc-alert">A partir de la versión 5.6 se envia el nombre de la conexión y no la conexión como en anteriores versiones</p>

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
    ->filter('name like %:name%')}
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
array(
    'page' => 0,
    'size' => 12
);
</pre>

<p>Si no se suministra ninguno de estos valores, scoop tomara por defecto los acá descritos y retornara un arreglo asociativo
con una estructura similar a la siguiente.</p>

<pre class="prettyprint">
array(
    'page' => 0,
    'size' => 12,
    'result' => array(),
    'total' => 0
);
</pre>

<p>En donde page y size son los mismo datos enviados o colocados por defecto, mientras result y total hacen referencia a la
consulta realizada.</p>

<h2><a href="#repositories">Repositorios</a><span class="anchor" id="repositories">...</span></h2>

<h2><a href="#domain-events">Eventos de dominio</a><span class="anchor" id="domain-events">...</span></h2>
