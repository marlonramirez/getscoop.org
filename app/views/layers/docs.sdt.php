@extends 'layers/layer'
<h2 class="head-main">Guia de desarrollo con scoop</h2>
<div id="main-docs" class="main">
    <nav id="nav-docs" data-attr="style.marginLeft: marginMenu">
        <a href="#menu" id="menu-list" title="menú"></a>
        <ul>
            @foreach $menu as $name => $item
                <li{{=$view === $item['view'] ? ' class="active"' : ''}}>
                    @if $name
                        <a href="{{#view->route('doc', $name)}}">
                            {{$item['title']}} <i class="fa fa-angle-double-right"></i>
                        </a>
                    @else
                        <a href="{{#view->route('welcome')}}">
                            {{$item['title']}} <i class="fa fa-angle-double-right"></i>
                        </a>
                    :if
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
mermaid.initialize({
        startOnLoad: true,
        theme: 'dark',
        themeVariables: {
            primaryColor: '#61afef',
            primaryTextColor: '#abb2bf',
            primaryBorderColor: '#5c6370',
            lineColor: '#98c379',
            secondaryColor: '#282c34',
            tertiaryColor: '#21252b'
        }
    });
hljs.addPlugin(
  new CopyButtonPlugin({
    lang: "es"
  })
);
hljs.highlightAll();</script>
