@extends 'layers/layer'
<h2 class="head-main block mt-0 bg-surface text-on-surface">Guia de desarrollo con scoop</h2>
<div id="main-docs" class="main">
    <nav id="nav-docs" data-attr="classList:menuClass">
        <a href="#menu" id="menu-list" title="menú"></a>
        <ul class="bg-surface text-on-surface rounded-lg overflow-hidden mx-0">
            @foreach $menu as $name => $item
                <li{{=$view === $item['view'] ? ' class="active"' : ''}}>
                    @if $name
                        <a href="{{#view->route('doc', $name)}}" class="flex items-center justify-between gap-sm">
                            {{$item['title']}} <span class="app-icon icon-chevrons-right inline-block shrink-0" aria-hidden="true"></span>
                        </a>
                    @else
                        <a href="{{#view->route('welcome')}}" class="flex items-center justify-between gap-sm">
                            {{$item['title']}} <span class="app-icon icon-chevrons-right inline-block shrink-0" aria-hidden="true"></span>
                        </a>
                    :if
                </li>
            :foreach
            </li>
        </ul>
    </nav>
    <div id="content-docs" class="min-w-0 overflow-x-hidden text-on-surface font-body">
        <section data-attr="classList:contentClass">
            @import "documentation/$view"
        </section>
    </div>
</div>
