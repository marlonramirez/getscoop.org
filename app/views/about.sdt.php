@extends 'layers/layer'
<h2 class="head-main">¡Esto es Scoop! <a href="http://mirdware.com" rel="external"><img src="{#view->img('logo-blanco.png')}" alt="MirdWare" title="MirdWare" /></a></h2>
<div class="main">
    <div style="overflow:auto">
        <figcaption class="about-scoop">
            <img src="{#view->img('about_scoop.png')}">
        </figcaption>
        <article class="about-scoop">
            <p>Scoop es un sistema de bootstrapping open source desarrollado por 
            <a href="http://mirdware.org" rel="external">MirdWare</a>. Originalmente fue concebido como 
            estructura básica para la elaboración de aplicaciones web modulares con PHP bajo una arquitectura 
            MVC <i>(Model-View-Controller)</i>, permitiendo un estilo orientado a objetos. Scoop fue 
            desarrollado por Marlon Ramírez a mediados del año 2012 poco despúes de realizar su proyecto 
            de grado para optar por el titulo de ingeniero de sistemas, años más tarde se publico una 
            primera versión del proyecto bajo 
            <a href="https://raw.githubusercontent.com/mirdware/Jetro/master/LICENSE" rel="external">
            licencia MIT</a> de código abierto.</p>

            <p>Bajo el desarrollo de MirdWare el proyecto cuenta con una amplia gama de recursos no solamente orientado 
            al desarrollo en PHP, al ser un bootstrap posee una gran base de herramientas open source que cohesionan diversas 
            tecnologias creando un completo ambiente de desarrollo web. Disponible en su versión {#config->get('app.version')}, 
            scoop cuenta con algunos sitios y aplicaciones tanto empresariales como independientes.</p>
            
            <p>¿Por que un bootstrap? La filosofía detras de scoop no permite que sea considerado un 
            framework de desarrollo, es más cercano a un 
            <a href="http://en.wikipedia.org/wiki/Software_engine" rel="external">motor de software</a> 
            pero se debe tener en cuente el fuerte factor de integración de tecnologias que implementa, lo cual lo convierte 
            en un sistema de bootsrapping para aplicaciones web.</p>
        </article>
    </div>

    <div class="col-3">
        <h2 class="h-green"><i class="fa fa-wrench"></i> Roadmap</h2>
        <p>Scoop continua siendo un proyecto poco  estable al cual se le deben realizar númerosos cambios 
        y mejoras, pero siempre teniendo en mente la filosofía y dinámica del proyecto. De momento lo más 
        crítico es completar la <a href="https://github.com/marlonramirez/getscoop.org" target="_blank">documentación</a> para contar con una guía de rápido 
        acceso y entender el funcionamiento del bootstrap en profundidad, otro asunto por tratar es el 
        sistema de persistencia y modelado del negocio tomando como base DDD (Domain-Driven Design).</p>
    </div>
    
    <div class="col-3">
        <h2 class="h-green"><i class="fa fa-money"></i> Contribuye</h2>
        <p>MirdWare no opta por una política de donaciones, así que una manera de contribuir 
        monetariamente es contratar alguno de nuestros <a href="{#view->route('services')}">servicios</a>, 
        también puedes contribuir con código mediante la cuenta de 
        <a href="https://github.com/mirdware/scoop" target="_blank">github</a>, recuerda que scoop 
        se encuentra liberado bajo una <b>licencia MIT</b> por lo cual estas en la libertad de realizar cualquier
        tipo de modificación o mejora personal, pero recuerda que muchos más programadores pueden beneficiarse de
        estos cambios.</p>
    </div>

    <div class="col-3">
        <h2 class="h-green"><i class="fa fa-users"></i> Créditos</h2>
        <ul class="data-contributor">
            <li>Marlon Ramírez Duque</li>
            <li>Arquitectura y programación</li>
            <li>
                <a href="https://github.com/marlonramirez" rel="external">
                    <i class="fa fa-github"></i> marlonramirez
                </a>
            </li>
            <li>
                <a href="http://twitter.com/marlonyramirez" rel="external">
                    <i class="fa fa-twitter"></i> @marlonyramirez
                </a>
            </li>
            <li>
                <a href="mailto:marlonramirez@outlook.com?Subject=Contacto%20Scoop">
                    <i class="fa fa-envelope"></i> marlonramirez@outlook.com
                </a>
            </li>
        </ul>
        <a href="#" style="float:right;font-size:.8em">Más colaboradores</a>
    </div>
    
</div>