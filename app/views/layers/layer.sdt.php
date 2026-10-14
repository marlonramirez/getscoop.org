<!DOCTYPE html>
<html lang="es">
    <head>
        <!-- Codificación de la pagina a utf-8 para que admita caracteres especiales -->
        <meta charset="utf-8" />
        <!-- Visualización en cualquier dispositivo utilizando responsive disign -->
        <meta name="viewport" content="width=device-width" />
        <script src="{{#view->asset('init_theme.js')}}?v={{#view->getConfig('app.version')}}"></script>
        <!-- Referencia a los datos del autor y material utilizado -->
        <link rel="author" href="{{#view->asset('humans.txt')}}" />
        <!-- Icono de la aplicación -->
        <link rel="shortcut icon" type="image/x-icon" href="{{#view->asset('favicon.ico')}}" />
        <!-- Enlace a la hoja de estilos general -->
        <link rel="stylesheet" href="{{#view->css(#view->getConfig('app.name').'.min.css')}}" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css">
        <link rel="stylesheet" href="https://unpkg.com/highlightjs-copy/dist/highlightjs-copy.min.css" />
        @if getenv('VITE_HOST')
            <script type="module" src="{{ROOT}}@vite/client"></script>
        :if
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/nginx.min.js"></script>
        <script src="https://unpkg.com/highlightjs-copy/dist/highlightjs-copy.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/fuse.js@6.6.2"></script>
        <script type="module" src="{{#view->js(#view->getConfig('app.name').'.min.js')}}"></script>
        @if isset($meta)
            @foreach $meta as $name => $value
                <meta name="{{$name}}" content="{{$value}}" />
            :foreach
        :if
        <!-- Titulo de la pagina -->
        <title>{{$title}} - {{#view->getConfig('app.name')}}</title>
    </head>

    <body>
        <header class="grid items-center w-full m-0 bg-surface text-on-surface">
            <figcaption class="m-0 min-w-0">
                <a href="{{ROOT}}" class="logo block h-auto overflow-visible">
                    <img src="{{#view->img('scoop.png')}}" alt="Scoop" class="block w-full h-auto">
                    <span></span>
                </a>
            </figcaption>
            <div class="search-container w-full max-w-lg min-w-0" data-index-url="{{ROOT}}index.json" data-docs-url="{{#view->route('welcome')}}">
                <form method="GET" class="search-form flex items-center bg-surface text-on-surface">
                    <span class="search-icon flex items-center">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15.7955 15.8111L21 21M18 10.5C18 14.6421 14.6421 18 10.5 18C6.35786 18 3 14.6421 3 10.5C3 6.35786 6.35786 3 10.5 3C14.6421 3 18 6.35786 18 10.5Z" stroke="#999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <input type="text" name="q" placeholder="¿Qué estás buscando?..." id="docs-search" />
                    <kbd class="search-shortcut">/</kbd>
                </form>
                <div id="search-results" class="search-results-dropdown bg-surface text-on-surface overflow-y-auto" style="display: none;"></div>
            </div>
            <nav class="header-actions flex items-center gap-md p-0">
                <button type="button" class="theme-toggle inline-flex items-center justify-center shrink-0 rounded-full bg-surface text-on-surface p-0 m-0" data-theme-toggle data-attr="ariaLabel:label;title:label" aria-label="Cambiar a modo oscuro" title="Cambiar a modo oscuro"><span class="theme-toggle-icon" aria-hidden="true"></span></button>
                <ul>
                    <li>
                        <a href="{{#view->route('welcome')}}">
                            <i class="icon-badge inline-flex items-center justify-center rounded-full bg-primary text-on-primary">
                                <span class="app-icon icon-book inline-block shrink-0" aria-hidden="true"></span>
                            </i>
                            <span>Documentación</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </header>
        <main>
            <sc-message></sc-message>
            @slot
        </main>
        <footer>
            <a style="float:left" href="http://mirdware.com" rel="external"><img src="{{#view->img('logo-blanco.png')}}" alt="MirdWare" title="MirdWare" /></a>
            <div id="mirdware-social">
                <a href="https://github.com/mirdware/scoop" rel="external">
                    <span class="app-icon icon-brand-github inline-block shrink-0" aria-hidden="true"></span> GitHub
                </a>
                <a href="https://twitter.com/mirdware" rel="external"><span class="app-icon icon-brand-twitter inline-block shrink-0" aria-hidden="true"></span> Twitter</a>
            </div>
            <i>scoop framework</i> is a trademark of <a href="http://mirdware.com" rel="external">MirdWare</a> © {{date('Y')}}<br/>
            <a href="https://opensource.org/licenses/MIT">License</a>
        </footer>
    </body>
</html>
