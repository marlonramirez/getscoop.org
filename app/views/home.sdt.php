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
    @import 'features'
</div>
<div class="info">
    @import 'about'
</div>
<div class="main">
    @import 'credits'
</div>
