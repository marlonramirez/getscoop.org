@extends 'layers/layer'
<h2 class="head-main">Guia de desarrollo con scoop <a href="http://mirdware.com" rel="external"><img src="{#view->img('logo-blanco.png')}" alt="MirdWare" title="MirdWare" /></a></h2>
<div id="main-docs" class="main">
    <nav id="nav-docs" data-attr="style.marginLeft: marginMenu">
        <a href="#menu" id="menu-list" title="menú"></a>
        <ul>
            <li{#config->isCurrentRoute('doc') ? ' class="active"' : ''}>
                <a href="{#view->route('doc')}">
                    Iniciando con scoop <i class="fa fa-angle-double-right"></i>
                </a>
            </li>
            <li{#config->isCurrentRoute('doc-config') ? ' class="active"' : ''}>
                <a href="{#view->route('doc-config')}">
                    Configuración del entorno <i class="fa fa-angle-double-right"></i>
                </a>
            </li>
            <li{#config->isCurrentRoute('doc-model') ? ' class="active"' : ''}>
                <a href="{#view->route('doc-model')}">
                    Domain-Driven Design <i class="fa fa-angle-double-right"></i>
                </a>
            </li>
            <li{#config->isCurrentRoute('doc-view') ? ' class="active"' : ''}>
                <a href="{#view->route('doc-view')}">
                    Scoop Dynamic Templates <i class="fa fa-angle-double-right"></i>
                </a>
            </li>
            <li{#config->isCurrentRoute('doc-controller') ? ' class="active"' : ''}>
                <a href="{#view->route('doc-controller')}">
                    Inversion of Control <i class="fa fa-angle-double-right"></i>
                </a>
            </li>
            <li{#config->isCurrentRoute('doc-view') ? ' class="active"' : ''}>
                <a href="{#view->route('doc-view')}">
                    Tareas automatizadas <i class="fa fa-angle-double-right"></i>
                </a>
            </li>
            <li{#config->isCurrentRoute('doc-model') ? ' class="active"' : ''}>
                <a href="{#view->route('doc-model')}">
                    Estilos predefinidos <i class="fa fa-angle-double-right"></i>
                </a>
            </li>
            <li{#config->isCurrentRoute('doc-model') ? ' class="active"' : ''}>
                <a href="{#view->route('doc-model')}">
                    Scalar components <i class="fa fa-angle-double-right"></i>
                </a>
            </li>
        </ul>
    </nav>
    <section id="content-docs" data-attr="style:contentStyle">
        @sprout
    </section>
</div>