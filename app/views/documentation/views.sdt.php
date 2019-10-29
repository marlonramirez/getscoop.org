@extends 'layers/docs'
<p>Las vistas contienen el HTML de la aplicación y funcionan como aislante entre la presentación y la logica
que manejan el resto de la aplicación. Un <i>Scoop Dinamic Template</i>(sdt) no es más que un archivo php 
con extensión .sdt.php por lo cual es posible incrustar etiquetas <code>&lt;?php ?&gt;</code></p>

<p class="doc-alert">aunque sea posible usar código php directamente sobre la plantilla no es un uso aconsejable.</p>

<h2>
    <a href="#iterpolation">Interpolación</a>
    <span class="anchor" id="interpolation">...</span>
</h2>

<h2>
    <a href="#control-structure">Estructuras de control</a>
    <span class="anchor" id="control-structure">...</span>
</h2>
<p>Existen diferentes estructuras de control que pueden ser usadas desde una vista, estas no difieren mucho de las que se pueden
manejar directamente desde PHP, pero si se diferencian en su forma de uso, de esta manera cada apertura o cierre de la
estructura se debe realizar en una sola linea, no es posible colocar ningún tipo de caracter seguido de una estructura de control
pues el motor sdt la tomara como parte de la estructura misma. Cada estructura abre con el signo <code>@&lt;init&gt;</code> y cierra con el
signo <code>:&lt;end&gt;</code>.</p>

<h3>Foreach</h3>

<h3>For</h3>

<h3>While</h3>

<h3>If</h3>

<h3>Else</h3>

<h3>Ifelse</h3>

<h2>
    <a href="#special-structure">Estructuras especiales</a>
    <span class="anchor" id="special-structure">...</span>
</h2>

<h3>Extends</h3>

<h3>Sprout</h3>

<h3>Import</h3>

<h2>
    <a href="#cache">Cache y minificación</a>
    <span class="anchor" id="cache">...</span>
</h2>
