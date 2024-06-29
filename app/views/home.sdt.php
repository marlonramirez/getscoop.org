@extends 'layers/layer'
<div id="welcome">
<a href="https://github.com/mirdware/scoop" target="_blank">
            <img style="position: absolute; top: 0; left: 0; border: 0;" decoding="async" width="149" height="149" src="https://github.blog/wp-content/uploads/2008/12/forkme_left_gray_6d6d6d.png?resize=149%2C149" class="attachment-full size-full" alt="Fork me on GitHub" loading="lazy" data-recalc-dims="1">
        </a>
    <h1>Scoop</h1>
    <h2>Simple Characteristics of Object-Oriented PHP</h2>
    <a href="{{#view->route('doc')}}#download" class="btn download">
        <i class="fa fa-download"></i> Descargar <small>(v. {{#view->getConfig('app.version')}})</small>
    </a>
</div>
<div class="main">

    <div class="col-3 mark-stack-scoop">
        <span class="fa-stack">
            <i class="fa fa-circle fa-stack-2x"></i>
            <i class="fa fa-leaf fa-stack-1x fa-inverse"></i>
        </span>
        <h2>Código limpio</h2>
        <p>La principal motivación de scoop es proporcionar al desarrollador un esquema básico para
        la creación de aplicaciones web robustas basadas en Programación Orientada a Objetos, con este
        fin se ha generado todo un sistema que garantiza aspectos del código como la legibilidad,
        modularidad y accesibilidad.</p>
    </div>

    <div class="col-3 mark-stack-scoop">
        <span class="fa-stack">
            <i class="fa fa-circle fa-stack-2x"></i>
            <i class="fa fa-briefcase fa-stack-1x fa-inverse"></i>
        </span>
        <h2>Herramientas scoop</h2>
        <p>Existen muchísimas herramientas disponibles que facilitan el desarrollo tanto en el lado
        del cliente como del servidor. Scoop se basa en composer para el manejo de dependencias PHP y
        NodeJS junto con npm para la gestión de archivos JavaScript y CSS.</p>
    </div>

    <div class="col-3 mark-stack-scoop">
        <span class="fa-stack">
            <i class="fa fa-circle fa-stack-2x"></i>
            <i class="fa fa-cogs fa-stack-1x fa-inverse"></i>
        </span>
        <h2>API RESTful</h2>
        <p>Un sistema basado en servicios RESTful nunca fue tan facil de crear, scoop se basa en comunicaciones HTTP
        por lo cual cada controlador maneja métodos <code>GET</code>, <code>POST</code>, <code>PUT</code> y 
        <code>DELETE</code> de manera explicita, además devuelve recursos basados en la solicitud que envía el cliente.</p>
    </div>

    <div class="col-3 mark-stack-scoop">
        <span class="fa-stack">
            <i class="fa fa-circle fa-stack-2x"></i>
            <i class="fa fa-database fa-stack-1x fa-inverse"></i>
        </span>
        <h2>Acceso a datos</h2>
        <p>SQO(Scoop|Simple Query Object) es un query builder sobre el cual se basa el sistema de persistencia.
        Una capa de abstracción orientada a objetos que simplifica el manejo de la base de datos, usando filtros
        dinámicos y posibilitando la generación de modelos orientados a datos.</p>
    </div>

    <div class="col-3 mark-stack-scoop">
        <span class="fa-stack">
            <i class="fa fa-circle fa-stack-2x"></i>
            <i class="fa fa-file-code-o fa-stack-1x fa-inverse"></i>
        </span>
        <h2>Sistema de plantillas</h2>
        <p>Es posible usar PHP para generar las vistas de tú aplicación, pero esto no es tan sencillo y
        divertido como crearlas con sdt(Scoop|Simple Dynamic Templates) el sistema de plantillas que proporciona scoop, aparte de simplificar
        la escritura de código en la vista se provee herramientas como la minificación inteligente de
        HTML.</p>
    </div>

    <div class="col-3 mark-stack-scoop">
        <span class="fa-stack">
            <i class="fa fa-circle fa-stack-2x"></i>
            <i class="fa fa-code-fork fa-stack-1x fa-inverse"></i>
        </span>
        <h2>Haciendo comunidad</h2>
        <p>Scoop es un proyecto relativamente nuevo, así que tanto su base de usuarios como colaboradores
        es muy reducida. Si hasta ahora estas empezando en PHP o ya eres un experto ZEND acreditado,
        discute tús ideas en el foro, reporta errores o implementa nuevas funcionalidades mediante
        <a href="https://github.com/marlonramirez/scoop" rel="external">github</a>.</p>
    </div>
</div>