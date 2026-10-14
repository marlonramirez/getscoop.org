export default class DocumentationRenderer {
  constructor() {
    this.copyPlugin = null;
  }

  async render(element) {
    const codes = element.querySelectorAll('pre code');
    const diagrams = element.querySelectorAll('.mermaid:not([data-processed])');
    diagrams.forEach(diagram => diagram.setAttribute('aria-busy', 'true'));
    try {
      const highlighter = window.hljs;
      if (highlighter) {
        if (window.CopyButtonPlugin && !this.copyPlugin) {
          this.copyPlugin = new window.CopyButtonPlugin({ lang: 'es' });
          highlighter.addPlugin(this.copyPlugin);
        }
        codes.forEach(code => {
          try {
            if (!code.dataset.highlighted) highlighter.highlightElement(code);
          } finally {
            code.dataset.rendered = 'true';
          }
        });
      }
      if (window.mermaid && diagrams.length) {
        window.mermaid.initialize({
          startOnLoad: false,
          theme: 'dark',
          themeVariables: {
            primaryColor: '#61afef', primaryTextColor: '#abb2bf',
            primaryBorderColor: '#5c6370', lineColor: '#98c379',
            secondaryColor: '#282c34', tertiaryColor: '#21252b'
          }
        });
        await window.mermaid.run({ nodes: diagrams });
      }
    } finally {
      codes.forEach(code => { code.dataset.rendered = 'true'; });
      diagrams.forEach(diagram => {
        diagram.dataset.rendered = 'true';
        diagram.removeAttribute('aria-busy');
      });
    }
  }

  dispose() {
    if (this.copyPlugin) window.hljs?.removePlugin(this.copyPlugin);
    this.copyPlugin = null;
  }
}
