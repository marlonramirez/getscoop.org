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
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css">
        <link rel="stylesheet" href="https://unpkg.com/highlightjs-copy/dist/highlightjs-copy.min.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/nginx.min.js"></script>
        <script src="https://unpkg.com/highlightjs-copy/dist/highlightjs-copy.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/fuse.js@6.6.2"></script>
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
            <div class="search-container">
                <form method="GET" class="search-form">
                    <span class="search-icon">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15.7955 15.8111L21 21M18 10.5C18 14.6421 14.6421 18 10.5 18C6.35786 18 3 14.6421 3 10.5C3 6.35786 6.35786 3 10.5 3C14.6421 3 18 6.35786 18 10.5Z" stroke="#999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <input type="text" name="q" placeholder="¿Qué estás buscando?..." id="docs-search" />
                    <kbd class="search-shortcut">/</kbd>
                </form>
                <div id="search-results" class="search-results-dropdown" style="display: none;"></div>
            </div>
            <nav>
                <ul>
                    <li>
                        <a href="{{#view->route('welcome')}}">
                            <i class="fa-stack">
                                <i class="fa fa-circle fa-stack-2x"></i>
                                <i class="fa fa-book fa-stack-1x fa-inverse"></i>
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
                    <i class="fa fa-github"></i> GitHub
                </a>
                <a href="https://twitter.com/mirdware" rel="external"><i class="fa fa-twitter"></i> Twitter</a>
            </div>
            <i>scoop framework</i> is a trademark of <a href="http://mirdware.com" rel="external">MirdWare</a> © {{date('Y')}}<br/>
            <a href="https://opensource.org/licenses/MIT">License</a>
        </footer>
<script>
let fuse;
let currentFocus = -1;
const searchInput = document.getElementById('docs-search');
const resultsContainer = document.getElementById('search-results');

fetch(`{{ROOT}}index.json`)
    .then(response => response.json())
    .then(data => {
        const options = {
            keys: ['title', 'content'],
            threshold: 0.3,
            includeScore: true
        };
        fuse = new Fuse(data, options);
    });
searchInput.addEventListener('input', (e) => {
    const query = e.target.value;
    currentFocus = -1;
    if (query.length < 2) {
        resultsContainer.style.display = 'none';
        return;
    }
    const results = fuse.search(query);
    displayResults(results);
});

function displayResults(results) {
    if (results.length === 0) {
        resultsContainer.innerHTML = '<div class="no-results">No se encontraron coincidencias</div>';
    } else {
        resultsContainer.innerHTML = results.slice(0, 8).map(result => {
            const item = result.item;
            const url = `{{ROOT}}docs/${item.file === 'intro' ? '' : item.file + '/'}${item.anchor ? '#' + item.anchor : ''}`;

            return `
                <a href="${url}" class="result-item">
                    <div class="result-header">
                        <span class="result-title">${item.title}</span>
                        <span class="result-badge">${item.file}</span>
                    </div>
                    <span class="result-preview">${item.content.substring(0, 100)}...</span>
                </a>
            `;
        }).join('');
    }
    resultsContainer.style.display = 'block';
}

document.addEventListener('click', (e) => {
    if (!e.target.closest('.search-container')) {
        resultsContainer.style.display = 'none';
    }
});

document.addEventListener('keydown', (e) => {
    if (e.key === '/' && document.activeElement !== searchInput) {
        e.preventDefault();
        searchInput.focus();
    }
});

searchInput.addEventListener('keydown', function(e) {
    const resultsList = document.getElementById('search-results');
    let items = resultsList.getElementsByClassName('result-item');
    if (e.key === "ArrowDown") {
        currentFocus++;
        addActive(items);
    } else if (e.key === "ArrowUp") {
        currentFocus--;
        addActive(items);
    } else if (e.key === "Enter") {
        if (currentFocus > -1) {
            if (items[currentFocus]) {
                e.preventDefault();
                items[currentFocus].click();
            }
        }
        resultsList.style.display = 'none';
    } else if (e.key === "Escape") {
        resultsList.style.display = 'none';
    }
});

function addActive(items) {
    if (!items || items.length === 0) return false;
    removeActive(items);
    if (currentFocus >= items.length) currentFocus = 0;
    if (currentFocus < 0) currentFocus = items.length - 1;
    items[currentFocus].classList.add("active");
    items[currentFocus].scrollIntoView({ block: 'nearest' });
}

function removeActive(items) {
    for (let i = 0; i < items.length; i++) {
        items[i].classList.remove("active");
    }
}
</script>
    </body>
</html>
