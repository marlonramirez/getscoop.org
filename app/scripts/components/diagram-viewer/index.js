import template from './template.html?raw';

export default () => {
  let host, dialog, title, canvas, observer, opener;
  let baseWidth = 600;
  let zoom = 1;
  let previousOverflow;
  const resize = factor => {
    zoom = Math.min(4, Math.max(.25, factor));
    const svg = canvas.querySelector('svg');
    if (svg) svg.style.width = `${Math.round(baseWidth * zoom)}px`;
  };
  const closed = () => {
    if (previousOverflow !== undefined) document.body.style.overflow = previousOverflow;
    previousOverflow = undefined;
    canvas.replaceChildren();
    if (opener?.isConnected) opener.focus();
  };
  const enhance = () => host.querySelectorAll('.mermaid svg').forEach(svg => {
    svg.setAttribute('tabindex', '0');
    svg.setAttribute('aria-haspopup', 'dialog');
    svg.setAttribute('aria-label', 'Diagrama. Pulsa Enter para ampliar');
  });
  const open = svg => {
    if (dialog.open) return;
    opener = svg;
    const diagram = svg.closest('.mermaid');
    const headings = [...host.querySelectorAll('h2, h3, h4')];
    const heading = headings.filter(item => item.compareDocumentPosition(diagram) & Node.DOCUMENT_POSITION_FOLLOWING).pop();
    title.textContent = diagram.dataset.diagramTitle || heading?.textContent.replace(/\.{3}/g, '').trim() || document.title.split(' - ')[0];
    const copy = svg.cloneNode(true);
    baseWidth = Math.max(svg.viewBox?.baseVal.width || svg.getBoundingClientRect().width, 600);
    copy.removeAttribute('width');
    copy.removeAttribute('height');
    // The copy is display-only; retain references inside Mermaid's SVG but avoid a second keyboard trigger.
    copy.removeAttribute('tabindex');
    copy.removeAttribute('aria-haspopup');
    canvas.replaceChildren(copy);
    previousOverflow = document.body.style.overflow;
    dialog.showModal();
    document.body.style.overflow = 'hidden';
    resize(1);
    dialog.querySelector('[data-diagram-action="close"]').focus();
  };
  const click = event => {
    if (!dialog) return true;
    const control = event.target.closest('[data-diagram-action]');
    if (control && dialog.contains(control)) {
      const actions = { reduce: () => resize(zoom / 1.25), enlarge: () => resize(zoom * 1.25), reset: () => resize(1), close: () => dialog.close() };
      actions[control.dataset.diagramAction]?.();
      return;
    }
    if (event.target === dialog) {
      const bounds = dialog.getBoundingClientRect();
      if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) dialog.close();
      return true;
    }
    const svg = event.target.closest('.mermaid svg');
    if (!svg || event.target.closest('a')) return true;
    open(svg);
  };
  return {
    mount: event => {
      host = event.currentTarget;
      if (typeof HTMLDialogElement === 'undefined') return;
      const fragment = document.createElement('template');
      fragment.innerHTML = template;
      dialog = fragment.content.firstElementChild;
      title = dialog.querySelector('h2');
      canvas = dialog.querySelector('.diagram-canvas');
      host.append(dialog);
      enhance();
      observer = new MutationObserver(enhance);
      observer.observe(host, { childList: true, subtree: true });
    },
    unmount: () => {
      observer?.disconnect();
      if (!dialog) return;
      if (dialog.open) dialog.close();
      closed();
      dialog.remove();
    },
    _click: click,
    _keydown: event => {
      const svg = event.target.closest('.mermaid svg');
      if (!dialog || !svg || event.target !== svg || !['Enter', ' '].includes(event.key)) return true;
      open(svg);
    },
    close_: event => { if (event.target === dialog) closed(); }
  };
};
