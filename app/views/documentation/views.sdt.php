<p>Las vistas contienen el HTML de la aplicación y funcionan como aislante entre la presentación y la logica
que maneja el resto de la aplicación. Un <i>Scoop Dynamic Template</i>(sdt) no es más que un archivo php
con extensión .sdt.php por lo cual es posible incrustar etiquetas <code>&lt;?php ?&gt;</code> o usar la nomenclatura
de template <code>&#91;php</code> <code>php&#93;</code></p>

<p class="doc-alert">aunque sea posible usar código php directamente sobre la plantilla no es un uso aconsejable.</p>

<ul>
    <li><a href="#interpolation">Interpolación</a></li>
    <li><a href="#control">Estructuras de control</a></li>
    <li><a href="#special">Estructuras especiales</a></li>
    <li><a href="#services">Uso de servicios</a></li>
    <li><a href="#components">Componentes</a></li>
    <li><a href="#cache">Cache y minificación</a></li>
</ul>

<h2>
    <a href="#interpolation">Interpolación</a>
    <span class="anchor" id="interpolation">...</span>
</h2>

<p>La forma más basica de enlazar datos en scoop es con el uso de interpolaciones, usando la sintaxis de dobles llaves
&#123;{$var}&#125;. Internamente scoop lee y compila la plantilla combirtiendo las llaves en sentencias echo que
volcaran la información hacia el buffer de salida.</p>

<p>sdt permite usar expresiones PHP dentro del código de las interpolaciones. Por ejemplo podemos imprimir el valor de
una variable en minúscula de la siguiente forma:</p>

<pre class="prettyprint">
&lt;h1&gt;¡Bienvenido &#123;{strtolower($user)}&#125;!&lt;/h1&gt;
</pre>

<p>A pesar de no poder usar estructutras de control dentro de las interpolaciones, podemos hacer uso del operador
terneario.</p>

<pre class="prettyprint">
&lt;h1&gt;¡&#123;{count($users) > 1 ? 'Bienvenidos': 'Bienvenido'}&#125;!&lt;/h1&gt;
</pre>

<h2>
    <a href="#control-structure">Estructuras de control</a>
    <span class="anchor" id="control-structure">...</span>
</h2>
<p>Existen diferentes estructuras de control que pueden ser usadas desde una vista, estas no difieren mucho de las que se pueden
manejar directamente desde PHP, pero si se diferencian en su forma de uso, de esta manera cada apertura o cierre de la
estructura se debe realizar en una sola linea, no es posible colocar ningún tipo de caracter seguido de una estructura de control
pues el motor sdt la tomara como parte de la estructura misma. Cada estructura abre con el signo <code>@&lt;init&gt;</code> y cierra con el
signo <code>:&lt;end&gt;</code>.</p>

<h3>&#64;foreach</h3>

<pre class="prettyprint">
&lt;table&gt;
    &lt;thead&gt;
        &lt;tr&gt;
            &lt;th&gt;Key&lt;/th&gt;
            &lt;th&gt;Value&lt;/th&gt;
        &lt;/tr&gt;
    &lt;/thead&gt;
    &lt;tbody&gt;
        &#64;foreach $array as $key =&gt; $value
            &lt;tr&gt;
                &lt;td&gt;&#123;{$key}&#125;&lt;/td&gt;
                &lt;td&gt;&#123;{$value}&#125;&lt;/td&gt;
            &lt;/tr&gt;
        &#58;foreach
    &lt;/tbody&gt;
&lt;/table&gt;
</pre>

<h3>&#64;for</h3>

<pre class="prettyprint">
&lt;ul&gt;
    &#64;for $i = 0; $i &gt;= $length; $i++
        &lt;li&gt;Item &#123;{$i}&#125;&lt;/li&gt;
    &#58;for
&lt;/ul&gt;
</pre>

<h3>&#64;while</h3>

<pre class="prettyprint">
&lt;pre&gt;
    &#64;while feof($file)
        &#123;{$fgetc($file)}&#125;
    &#58;while
&lt;/pre&gt;
</pre>

<h3>&#64;if</h3>

<pre class="prettyprint lang-html">
&#64;if isset($user)
    &lt;h1&gt;Welcome &#123;{$user}&#125;!&lt;/h1&gt;
&#58;if
</pre>

<h3>&#64;else</h3>

<pre class="prettyprint">
&lt;header&gt;
    &#64;if isset($user)
        &lt;h1&gt;Welcome &#123;{$user}&#125;!&lt;/h1&gt;
    &#64;else
        &lt;a href="&#123;{&#35;view->route('login')}&#125;"&gt;Login&lt;/a&gt;
    &#58;if
&lt;/header&gt;
</pre>

<h3>&#64;elseif</h3>

<pre class="prettyprint">
&lt;header&gt;
    &#64;if isset($user)
        &lt;h1&gt;Welcome &#123;{$user}&#125;!&lt;/h1&gt;
    &#64;elseif isset($guess)
        &lt;h1&gt;Welcome &#123;{$guess}&#125;!&lt;/h1&gt;
    &#64;else
        &lt;a href="&#123;{&#35;view->route('login')}&#125;"&gt;Login&lt;/a&gt;
    &#58;if
&lt;/header&gt;
</pre>

<h2>
    <a href="#special-structure">Estructuras especiales</a>
    <span class="anchor" id="special-structure">...</span>
</h2>

<h3>&#64;extends</h3>

<pre class="prettyprint">
&#64;extends 'layers/layer'
</pre>

<h3>&#64;sprout</h3>

<h3>&#64;import</h3>

<pre class="prettyprint">
&#64;import 'partition/login'
</pre>

<h3>&#64;inject</h3>

<pre class="prettyprint">
&#64;inject \App\Service\Provider#provider
</pre>

<h2>
    <a href="#services">Uso de servicios</a>
    <span class="anchor" id="services">...</span>
</h2>

<pre class="prettyprint lang-html">
&#64;inject \App\Service\Provider#provider
...
&lt;h1&gt;Hello &#123;{&#35;provider->getName()}&#125;&lt;/h1&gt;
</pre>

<h2>
    <a href="#components">Componentes</a>
    <span class="anchor" id="components">...</span>
</h2>

<h2>
    <a href="#cache">Cache y minificación</a>
    <span class="anchor" id="cache">...</span>
</h2>
