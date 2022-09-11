<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Codificación de la pagina a utf-8 para que admita caracteres especiales -->
    <meta charset="utf-8" />
    <!-- Visualización en cualquier dispositivo utilizando responsive disign -->
    <meta name="viewport" content="width=device-width" />
    @if isset($meta)
        <meta name="description" content="{{$meta['description']}}" />
        <meta name="keywords" content="{{$meta['keywords']}}" />
    :if
    <!-- Referencia a los datos del autor y material utilizado -->
    <link rel="author" href="{{#view->asset('humans.txt')}}" />
    <!-- Icono de la aplicación -->
    <link rel="shortcut icon" type="image/x-icon" href="{{#view->asset('favicon.ico')}}" />
    <!-- Enlace a la hoja de estilos general -->
    <link rel="stylesheet" href="{{#view->css(#view->getConfig('app.name').'.min.css')}}" />
    <script src="https://cdn.rawgit.com/google/code-prettify/master/loader/run_prettify.js"></script>
    <script src="{{#view->js(#view->getConfig('app.name').'.min.js')}}" async></script>
    <!-- Titulo de la pagina -->
    <title>{{$title}} - {{#view->getConfig('app.name')}}</title>
</head>

<body>
    <header>
        <figcaption>
            <a href="{{ROOT}}" class="logo">
                <img src="{{#view->img('scoop.png')}}" alt="Scoop">
                <span></span>
            </a>
        </figcaption>

        <div id="nav-icon" data-attr="className:classIcon">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <nav data-attr="style.display:showNav">
            <ul>
                <li><a href="{{#view->route('doc')}}">Documentación</a></li>
                <li><a href="{{ROOT}}api/">Ecosistema</a></li>
                <li><a href="{{#view->route('services')}}">Servicios</a></li>
                <li><a href="{{#view->route('about')}}">Acerca</a></li>
            </ul>
        </nav>
    </header>
    <main>
        {{#view->composeMessage()}}
        @sprout
    </main>
    <footer>
        <div id="mirdware-social">
            <a href="https://github.com/mirdware/scoop" rel="external">
                <i class="fa fa-github"></i> GitHub
            </a>
            <a href="https://twitter.com/mirdware" rel="external"><i class="fa fa-twitter"></i> Twitter</a>
        </div>
       <i>scoop</i> is a trademark of <a href="http://mirdware.com" rel="external">MirdWare</a> © 2015<br/>
        <a href="https://opensource.org/licenses/MIT">License</a>
    </footer>
</body>
</html>