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
        <nav  style="float:right; padding: 20px 20px 0 0">
            <ul>
                <li>
                    <a href="{{#view->route('doc')}}">
                        <span>Documentación</span>
                        <i class="fa-stack" style="font-size: 1.2em;color:#ffb515;">
                            <i class="fa fa-circle fa-stack-2x"></i>
                            <i class="fa fa-book fa-stack-1x fa-inverse"></i>
                        </i>
                    </a>
                </li>
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
       <i>scoop</i> is a trademark of <a href="http://mirdware.com" rel="external">MirdWare</a> © 2025<br/>
        <a href="https://opensource.org/licenses/MIT">License</a>
    </footer>
</body>
</html>