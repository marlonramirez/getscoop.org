@extends 'layers/layer'
<h2 class="head-main">Guia de desarrollo con scoop</h2>
<div id="main-docs" class="main">
    <nav id="nav-docs" data-attr="style.marginLeft: marginMenu">
        <a href="#menu" id="menu-list" title="menú"></a>
        <ul>
            @foreach $menu as $name => $item
                <li{{$view === $item['view'] ? ' class="active"' : ''}}>
                    <a href="{{#view->route('doc', $name ? $name : null)}}">
                        {{$item['title']}} <i class="fa fa-angle-double-right"></i>
                    </a>
                </li>
            :foreach
            </li>
        </ul>
    </nav>
    <section id="content-docs" data-attr="style:contentStyle">
        @import "documentation/$view"
    </section>
</div>
<script>
hljs.addPlugin(
  new CopyButtonPlugin({
    lang: "es"
  })
);
hljs.highlightAll();</script>
