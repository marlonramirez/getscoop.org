<div class="col-3 mark-stack-scoop">
    <span class="fa-stack">
        <i class="fa fa-circle fa-stack-2x"></i>
        <i class="fa fa-leaf fa-stack-1x fa-inverse"></i>
    </span>
    <h2>Código limpio</h2>
    <p>
        La principal motivación de scoop es proporcionar al desarrollador una estructura inicial para
        la creación de aplicaciones web robustas basadas en Programación Orientada a Objetos, con este
        fin se ha generado todo un sistema que garantiza aspectos del código como la legibilidad,
        modularidad y accesibilidad; facilitando la creación de aplicaciones con arquitectura hexagonal,
        Domain Driven Disign y CQRS.
    </p>
</div>

<div class="col-3 mark-stack-scoop">
    <span class="fa-stack">
        <i class="fa fa-circle fa-stack-2x"></i>
        <i class="fa fa-briefcase fa-stack-1x fa-inverse"></i>
    </span>
    <h2>Tooling</h2>
    <p>
        Gracias a los manejadores de dependencias incorporados se pueden instalar una serie de herramientas 
        para mejorar la experiencia del desarrollador mediante composer en el caso de PHP y
        NodeJS junto con npm para la gestión de archivos JavaScript y CSS. Este tipo de herramientas
        incluyen la implementación de pruebas unitarias, análisis estático de código, lintter, gestión de assets.
    </p>
</div>

<div class="col-3 mark-stack-scoop">
    <span class="fa-stack">
        <i class="fa fa-circle fa-stack-2x"></i>
        <i class="fa fa-cogs fa-stack-1x fa-inverse"></i>
    </span>
    <h2>API RESTful</h2>
    <p>
        Scoop se basa en comunicaciones HTTP por lo cual cada controlador maneja explicitamente
        métodos <code>GET</code>, <code>POST</code>, <code>PUT</code> y <code>DELETE</code> con lo cual una clase 
        solo puede representar un recurso, la unica excepción a esto
        es separar cada petición a un controlador independiente para respetar el principio de single responsability.
        Las respuestas de la petición se basan por defecto en la solicitud que envía el cliente.
    </p>
</div>

<div class="col-3 mark-stack-scoop">
    <span class="fa-stack">
        <i class="fa fa-circle fa-stack-2x"></i>
        <i class="fa fa-database fa-stack-1x fa-inverse"></i>
    </span>
    <h2>Acceso a datos</h2>
    <p>
        SQO(Scoop|Simple Query Object) es un query builder sobre el cual se basa el sistema de persistencia.
        Una capa de abstracción orientada a objetos que simplifica el manejo de la base de datos, usando filtros
        dinámicos y posibilitando la generación de modelos orientados a datos. Tambien se cuenta con un sistema de
        persistencia para las entidades (Entity Persistence Management), el cual mapea las propiedades a ser
        persistidas como lo haria un ORM y se basa en el sistema de agregados de DDD para recuperar información.
    </p>
</div>

<div class="col-3 mark-stack-scoop">
    <span class="fa-stack">
        <i class="fa fa-circle fa-stack-2x"></i>
        <i class="fa fa-file-code-o fa-stack-1x fa-inverse"></i>
    </span>
    <h2>Sistema de plantillas</h2>
    <p>
        Hoy en día no se suelen manejar proyecto monoliticos en los cuales el back y front se encuentren bajo la
        misma infraestructura, tal vez esto este cambiando un poco con tecnicas con BFF (Backend For Frontend), 
        SSG (Static Site Generation) o lo que nos compete aquí y ahora SSR (Server Side Rendering). Scoop provee su
        propio sistema de plantillas para facilitar la rapida maquetación de vistas y realizar diversas tareas como
        blucles, condicionales, herencia, minificación inteligente de HTML.</p>
</div>

<div class="col-3 mark-stack-scoop">
    <span class="fa-stack">
        <i class="fa fa-circle fa-stack-2x"></i>
        <i class="fa fa-terminal fa-stack-1x fa-inverse"></i>
    </span>
    <h2>ICE</h2>
    <p>
        Interface Command Environment (ICE) es el sistema de scoop para manejar ciertas automatizaciones por medio de
        la interfaz de comandos o consola y no solo implementa las acciones establecidas por defecto si no que permite
        al desarrollador crear sus propias implementaciones modificando solo ciertos aspectos de una comunicación HTTP y
        permitiendo reusar servicios de aplicación tanto para peticiones HTTP como peticiones provenientes de las 
        interfaz de comandos.</p>
</div>
