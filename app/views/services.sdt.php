@extends 'layers/layer'
<h2 class="head-main">¿Tienes una idea? Cuentanos <a href="http://mirdware.com" rel="external"><img src="{{#view->img('logo-blanco.png')}}" alt="MirdWare" title="MirdWare" /></a></h2>
<div class="main">
    <section id="service">
        <figcaption>
            <img src="{{#view->img('services.png')}}" alt="servicios web">
        </figcaption>
        <p>Sabemos que desarrollar un proyecto de software es costoso, por esta razón hemos creado scoop. Si aún asi tú
        proyecto se encuentra consumiendo tiempo y recursos innesarios para tú empresa ¡MirdWare puede ayudar! Ofrecemos una amplia gama de servicios innovadores, profesionales y precisos, desde asesorias o capacitaciones
        hasta desarrollos a la medida de tus necesidades. Sea cual sea tu problema consultanos, seguramente somos la
        solución que necesitas.</p>
    </section>
    <form id="contact-project" method="post" action="{{#view->route()}}" class="scoop-form">
        <fieldset class="box-shadow">
            <legend>Acerca de tí</legend>
            <span class="input-box">
                <label for="nombres">Nombres:</label>
                <input type="text" class="input" size="30" name="nombres" id="nombres" required />
                <label for="nombres" class="icon"></label>
            </span>
            <span class="input-box">
                <label for="apellidos">Apellidos:</label>
                <input type="text" class="input" size="30" name="apellidos" id="apellidos" required />
                <label for="apellidos" class="icon"></label>
            </span><br />
            <span class="input-box">
                <label for="company">Compañia:</label>
                <input type="text" class="input" size="20" name="company" id="company" />
            </span>
            <span class="input-box">
                <label for="email">Email:</label>
                <input type="email" class="input" size="30" name="email" id="email" required />
                <label for="email" class="icon"></label>
            </span>
            <span class="input-box">
                <label for="tel">Teléfono:</label>
                <input type="tel" class="input" size="12" name="tel" id="tel" required />
                <label for="tel" class="icon"></label>
            </span>
        </fieldset>

        <fieldset class="box-shadow">
            <legend>Acerca de tú proyecto</legend>
            <div>
                <label for="desarrollo">Tipo:</label>
                <div class="input-box radio-group">
                    <span class="radio">
                        <input type="radio" name="tipo" value="1" id="desarrollo" checked />
                        <label for="desarrollo">Desarrollo</label>
                    </span>
                    <span class="radio">
                        <input type="radio" name="tipo" value="2" id="asesoria" />
                        <label for="asesoria">Asesoria</label>
                    </span>
                    <span class="radio">
                        <input type="radio" name="tipo" value="3" id="otro" />
                        <label for="otro">Otro</label>
                    </span>
                </div>
            </div>
            <div class="input-box">
                <label for="presupuesto">Presupuesto:</label>
                <span style="margin-right:.5em">$</span><input type="number" size="20" class="input" name="presupuesto" id="presupuesto" />
            </div>
            <div class="group-box">
                <label for="descripcion">Descripción:</label>
                <div class="input-box">
                    <textarea class="input" id="descripcion" rows="7" name="descripcion" required></textarea>
                    <label for="descripcion" class="icon"></label>
                </div>
            </div>
        </fieldset>
        <div class="center"><input type="submit" value="Enviar proyecto" class="btn" /></div>
    </form>
</div>