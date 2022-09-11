@extends 'layers/layer'
<h2 class="head-main">Guia de desarrollo con scoop <a href="http://mirdware.com" rel="external"><img src="{{#view->img('logo-blanco.png')}}" alt="MirdWare" title="MirdWare" /></a></h2>
<div id="main-docs" class="main">
    <nav id="nav-docs" data-attr="style.marginLeft: marginMenu">
        <a href="#menu" id="menu-list" title="menú"></a>
        <ul>
            <li{{#view->isCurrentRoute('doc') ? ' class="active"' : ''}}>
                <a href="{{#view->route('doc')}}">
                    Iniciando con scoop <i class="fa fa-angle-double-right"></i>
                </a>
            </li>
            <li{{#view->isCurrentRoute('doc-config') ? ' class="active"' : ''}}>
                <a href="{{#view->route('doc-config')}}">
                    Configuración del entorno <i class="fa fa-angle-double-right"></i>
                </a>
            </li>
            <li{{#view->isCurrentRoute('doc-folder') ? ' class="active"' : ''}}>
                <a href="{{#view->route('doc-folder')}}">
                    Estructura de carpetas <i class="fa fa-angle-double-right"></i>
                </a>
            </li>
            <li{{#view->isCurrentRoute('doc-view') ? ' class="active"' : ''}}>
                <a href="{{#view->route('doc-view')}}">
                    Plantillas dinámicas <i class="fa fa-angle-double-right"></i>
                </a>
            </li>
            <li{{#view->isCurrentRoute('doc-model') ? ' class="active"' : ''}}>
                <a href="{{#view->route('doc-model')}}">
                    Diseño del dominio <i class="fa fa-angle-double-right"></i>
                </a>
            </li>
            <li{{#view->isCurrentRoute('doc-controller') ? ' class="active"' : ''}}>
                <a href="{{#view->route('doc-controller')}}">
                    Ciclo de vida <i class="fa fa-angle-double-right"></i>
                </a>
            </li>
            <li{{#view->isCurrentRoute('doc-resources') ? ' class="active"' : ''}}>
                <a href="{{#view->route('doc-resources')}}">
                    Recursos <i class="fa fa-angle-double-right"></i>
                </a>
            </li>
            <li{{#view->isCurrentRoute('doc-monitoring') ? ' class="active"' : ''}}>
                <a href="{{#view->route('doc-monitoring')}}">
                    Monitoreo <i class="fa fa-angle-double-right"></i>
                </a>
            </li>
            <li{{#view->isCurrentRoute('doc-ice') ? ' class="active"' : ''}}>
                <a href="{{#view->route('doc-ice')}}">
                    CLI/ICE <i class="fa fa-angle-double-right"></i>
                </a>
            </li>
        </ul>
    </nav>
    <section id="content-docs" data-attr="style:contentStyle">
        @sprout
    </section>
</div>