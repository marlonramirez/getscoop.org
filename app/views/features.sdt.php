<div class="col-3 mark-stack-scoop">
    <span class="fa-stack">
        <i class="fa fa-circle fa-stack-2x"></i>
        <i class="fa fa-leaf fa-stack-1x fa-inverse"></i>
    </span>
    <h2>Código limpio</h2>
    <p>La principal motivación de scoop es proporcionar al desarrollador una estructura inicial para la creación de aplicaciones web robustas basadas en Programación Orientada a Objetos, con este fin se ha generado todo un sistema que garantiza aspectos del código como la legibilidad, modularidad y accesibilidad; facilitando la creación de aplicaciones con arquitectura hexagonal, Domain Driven Disign y CQRS.</p>
</div>

<div class="col-3 mark-stack-scoop">
    <span class="fa-stack">
        <i class="fa fa-circle fa-stack-2x"></i>
        <i class="fa fa-microchip fa-stack-1x fa-inverse"></i>
    </span>
    <h2>IoC Recursivo</h2>
    <p>El corazón de Scoop es un motor de inyección de dependencias basado en <b>reflexión recursiva</b> y autowiring inteligente. Mediante la sintaxis de factoría <code>Class:method</code>, permite resolver grafos de objetos complejos y parámetros primitivos sin ensuciar el Dominio, garantizando que cada componente nazca con sus dependencias listas para operar.</p>
</div>

<div class="col-3 mark-stack-scoop">
    <span class="fa-stack">
        <i class="fa fa-circle fa-stack-2x"></i>
        <i class="fa fa-map-signs fa-stack-1x fa-inverse"></i>
    </span>
    <h2>System File Routing</h2>
    <p>Inspirado en paradigmas modernos de archivos, el ruteo de Scoop se define mediante la jerarquía de directorios en <code>app/routes</code>. Este sistema elimina archivos de configuración masivos, permite parámetros dinámicos mediante <code>[id]</code> y orquesta una <b>herencia de middlewares</b> automática, optimizando el rendimiento mediante compilación estática.</p>
</div>

<div class="col-3 mark-stack-scoop">
    <span class="fa-stack">
        <i class="fa fa-circle fa-stack-2x"></i>
        <i class="fa fa-database fa-stack-1x fa-inverse"></i>
    </span>
    <h2>Persistencia DDD</h2>
    <p>El binomio <b>SQO</b> (Query Builder atómico) y <b>EPM</b> (Entity Manager) ofrece una persistencia transparente basada en el patrón Data Mapper. Scoop prohíbe el Lazy Loading para proteger el rendimiento, obligando a una carga explícita de agregados mediante <code>aggregate()</code> y utilizando <b>Closure Binding</b> para una hidratación de entidades a velocidad nativa.</p>
</div>

<div class="col-3 mark-stack-scoop">
    <span class="fa-stack">
        <i class="fa fa-circle fa-stack-2x"></i>
        <i class="fa fa-file-code-o fa-stack-1x fa-inverse"></i>
    </span>
    <h2>Templates dinámicos</h2>
    <p>El sistema de plantillas <b>Simple Dynamic Template</b> es un transpilador que optimiza el renderizado en servidor. Permite inyectar servicios directamente en la vista con el símbolo <code>#</code>, crear <b>Smart Components</b> con lógica de clase y aplicar una minificación agresiva de HTML, garantizando un aislamiento total del scope y una latencia de respuesta mínima.</p>
</div>

<div class="col-3 mark-stack-scoop">
    <span class="fa-stack">
        <i class="fa fa-circle fa-stack-2x"></i>
        <i class="fa fa-terminal fa-stack-1x fa-inverse"></i>
    </span>
    <h2>Ecosistema ICE</h2>
    <p>El <b>Interface Command Environment</b> permite extender la potencia de Scoop a la terminal. No es solo un CLI de ayuda; es un bus de comandos que reutiliza los mismos servicios de aplicación y contextos que la capa web. Gracias a sus <b>Lazy Loaders</b> dinámicos, automatiza tareas de infraestructura, escaneo de tipos y gestión de <i>structs</i> de base de datos.</p>
</div>
