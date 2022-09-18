@extends 'layers/docs'
<p>La estructura de directorios de scoop esta diseñada para dar un buen punto de arranque para el desarrollo de
aplicaciones orientadas a la web. Exiten multiples fromas de configurar la estructura de directorios, pero la
prestablecida es la más conveniente en la mayoria de los casos. Los archivos que se encuentran en la raíz del
proyecto son configuraciones de terceros o como <code>index.php</code> arraque del proyecto.</p>

<p>Las carpetas que componen un proyecto scoop son las siguiente:</p>

<h2>app</h2>
<p>Contiene todo el código diferente al core del negocio pero que igual es necesario para la ejecución de la aplicación,
entre esto tenemos codigo javascript, css(stylus), vistas y configuraciones.</p>

<h2>public</h2>
<p>Contiene todo los assets compilados y listos para ser entregado al cliente, además de imagenes, archivos usados
para la indexación en motores de busqueda y fuente de letras.</p>

<h2>scoop</h2>
<p>Carpeta principal del bootstrap, contiene todo lo necesario para arrancar el proyecto.</p>

<h2>src</h2>
<p>Contiene el código principal de la aplicación, su estructura depende de como el usuario desee llevar su proyectos,
desde división por infraestructura y dominio, como por separación de artefactos (controladores, repositorios, servicios).</p>
