@extends 'layers/layer'
<div id="welcome">
    <a href="https://github.com/marlonramirez/scoop" rel="external"><img src="https://s3.amazonaws.com/github/ribbons/forkme_right_white_ffffff.png" alt="Fork me on GitHub" id="ribbon-github"></a>
    <h1>Scoop</h1>
    <h2>Simple Characteristics of Object-Oriented PHP</h2>
    <a href="{#view->route('doc')}#download" class="btn download">
        <i class="fa fa-download"></i> Descargar <small>(v. {#config->get('app.version')})</small>
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
        NodeJS junto con npm para la gestión de archivos
        JavaScript y CSS.</p>
    </div>

    <div class="col-3 mark-stack-scoop">
        <span class="fa-stack">
            <i class="fa fa-circle fa-stack-2x"></i>
            <i class="fa fa-cogs fa-stack-1x fa-inverse"></i>
        </span>
        <h2>API RESTful</h2>
        <p>Un sistema basado en servicios RESTful nunca fue tan facil de crear, solo basta con
        implementar la interfaz <code>Scoop\Http\Resource</code> a un controlador para que este se convierta en un recurso con
        metodos accesores <code>GET</code>, <code>POST</code>, <code>PUT</code> y <code>DELETE</code>.</p>
    </div>

    <div class="col-3 mark-stack-scoop">
        <span class="fa-stack">
            <i class="fa fa-circle fa-stack-2x"></i>
            <i class="fa fa-database fa-stack-1x fa-inverse"></i>
        </span>
        <h2>Acceso a datos</h2>
        <p>SQO es la base del sistema de persistencia que maneja scoop, una capa de abstracción orientada
        a objetos que simplifica el manejo de base de datos y mediante la cual es posible generar modelos
        orientados tanto a objetos como a datos.</p>
    </div>

    <div class="col-3 mark-stack-scoop">
        <span class="fa-stack">
            <i class="fa fa-circle fa-stack-2x"></i>
            <i class="fa fa-file-code-o fa-stack-1x fa-inverse"></i>
        </span>
        <h2>Sistema de plantillas</h2>
        <p>Es posible usar PHP para generar las vistas de tú aplicación, pero esto no es tan sencillo y
        divertido como crearlas con el sistema de plantillas que proporciona scoop, aparte de simplificar
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
        github.</p>
    </div>
</div>