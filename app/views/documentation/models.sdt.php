@extends 'layers/docs'
<p>Es en este punto donde el manejo de scoop puede empezar a parecer complicado, pues no cuenta con un ORM que 
genere automaticamente el modelo partiendo desde el diseño de la base de datos (Modelo anémico) y esto aunque pueda 
parecer una desventaja no lo es tanto así ya que le da autonomia al programador de decidir sobre una arquitectura 
adecuada para su problema.</p>

<h2><a href="#agnosticism">Agnosticismo sobre los datos</a><span class="anchor" id="agnosticism">...</span></h2>
<p>El agnosticismo se define como la postura que considera que los valores de verdad de ciertas afirmaciones 
son desconocidas o inherentemente incognoscibles, el agnosticismo es la mera suspensión de la creencia.</p>

<p>Se dice que scoop es agnostica por que no tiene una creencia ferrea en el manejo de datos y en como debe 
ser implementado el dominio del negocio, esto quiere decir que se pueden usar diferentes técnicas para la consulta, 
inserción, actualización y eliminación de información, al igual que la metodología que mejor se ajuste al diseño del 
modelo (TDD, DDD, ATDD, BDD). Pero el agnosticismo se aplica solo a la manera de manejar los datos, ya que se sabe 
estos existen y deben ser tratados, por lo cual se han definido diversas herramientas para facilitar el procesamiento 
de los mismos.</p>

<h2><a href="#dbc">Data Base Conection</a><span class="anchor" id="dbc">...</span></h2>
<p>Scoop se basa en PDO para el manejo de conexiones, cualquier tipo de base de datos debe conectar mediante 
un driver PDO, esto garantiza la homogeniedad en el tratamiento de datos. Pero la implemententación de una 
conexión en especifico dentro de la aplicación se realiza usando la clase DBC acronimo de Data Base Connection y 
que se provee mediante los archivo de configuración.</p>

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
para usar alguna conexión se debe hacer uso de la clase DBC e incovar el método <code>DBC::get()</code>, 
cuando no se envia ningún parametro al método este toma la conexión default, para escoger otro tipo de conexión 
diferente se debe enviar la clave de la conexión <code>DBC::get('auth')</code>.</p>

<h2><a href="#sqo">SQO</a><span class="anchor" id="sqo">...</span></h2>
<p>No existe nada de malo con usar SQL dentro de una aplicación, pero se pueden crear herramientas que hagan 
el manejo de las sentencias mucho más flexibles y dinamicas, el mecanismo escogido por scoop para esta labor es 
SQO (Scoop Query Object), el cual presenta una manera más sencilla y orientada a objetos de realizar consultas a 
la base de datos.</p>

<p>Para usar SQO es necesario instanciar un objeto y pasar como parametro el nombre de la tabla principal 
<code>new \Scoop\Storage\SQO('book')</code>, si es necesario colocar un alias a la tabla o manejar una conexión 
diferente a la default es posible pasar dos parametros más.</p>

<pre class="prettyprint">
$bookSQO = new \Scoop\Storage\SQO('book', 'b', DBC::get('auth'));
</pre>

<p>A partir de acá es posible usar los métodos de SQO, estos son: create, update, read, delete y getLastId.</p>

<pre class="prettyprint">
$bookSQO->create([
    'name' => 'Angels & demons',
    'author' => 'Dan Brown',
    'year' => '2009'
])->run();
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
