import { inject } from 'scalar';
import SearchIndex from '../services/SearchIndex';

export default inject(SearchIndex)(($, index) => {
  let host, input, results, engine, controller;
  let currentFocus = -1;
  let failed = false;
  const close = () => {
    results.style.display = 'none';
    currentFocus = -1;
  };
  const message = text => {
    const element = document.createElement('div');
    element.className = 'no-results text-center text-secondary';
    element.textContent = text;
    results.replaceChildren(element);
    results.style.display = 'block';
  };
  const search = () => {
    currentFocus = -1;
    const query = input.value.trim();
    if (query.length < 2) return close();
    if (failed) return message('La búsqueda no está disponible. Inténtalo de nuevo más tarde.');
    if (!engine) return message('Cargando búsqueda…');
    const matches = engine.search(query).slice(0, 8);
    if (!matches.length) return message('No se encontraron coincidencias');
    const links = matches.map(({ item }) => {
      const link = document.createElement('a');
      link.className = 'result-item flex flex-col bg-surface text-on-surface no-underline';
      const page = item.file === 'intro' ? '' : encodeURIComponent(item.file) + '/';
      link.href = host.dataset.docsUrl + page + (item.anchor ? '#' + encodeURIComponent(item.anchor) : '');
      const heading = document.createElement('div');
      heading.className = 'result-header';
      const title = document.createElement('span');
      title.className = 'result-title text-on-surface';
      title.textContent = item.title;
      const badge = document.createElement('span');
      badge.className = 'result-badge';
      badge.textContent = item.file;
      const preview = document.createElement('span');
      preview.className = 'result-preview text-secondary mt-xs';
      preview.textContent = String(item.content || '').substring(0, 100) + '…';
      heading.append(title, badge);
      link.append(heading, preview);
      return link;
    });
    results.replaceChildren(...links);
    results.style.display = 'block';
  };
  const outsideClick = event => { if (!host.contains(event.target)) close(); };
  const shortcut = event => {
    const editable = event.target.closest('input, textarea, select, [contenteditable]:not([contenteditable="false"])');
    if (event.key !== '/' || editable || event.ctrlKey || event.altKey || event.metaKey) return;
    event.preventDefault();
    input.focus();
    input.select();
  };
  const navigate = event => {
    const items = [...results.querySelectorAll('.result-item')];
    if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
      if (!items.length || results.style.display === 'none') return true;
      currentFocus = event.key === 'ArrowDown'
        ? (currentFocus + 1) % items.length
        : (currentFocus < 0 ? items.length - 1 : (currentFocus - 1 + items.length) % items.length);
      items.forEach((item, position) => item.classList.toggle('active', position === currentFocus));
      items[currentFocus].scrollIntoView({ block: 'nearest' });
    } else if (event.key === 'Enter') {
      if (currentFocus >= 0 && items[currentFocus]) items[currentFocus].click();
    } else if (event.key === 'Escape') {
      close();
      input.blur();
    } else return true;
  };
  return {
    mount: event => {
      host = event.currentTarget;
      input = host.querySelector('#docs-search');
      results = host.querySelector('#search-results');
      controller = new AbortController();
      document.addEventListener('click', outsideClick);
      document.addEventListener('keydown', shortcut);
      index.load(host.dataset.indexUrl, controller.signal).then(value => {
        if (controller.signal.aborted) return;
        engine = value;
        if (document.activeElement === input) search();
      }).catch(error => {
        if (controller.signal.aborted) return;
        failed = true;
        if (document.activeElement === input) search();
      });
    },
    unmount: () => {
      controller.abort();
      document.removeEventListener('click', outsideClick);
      document.removeEventListener('keydown', shortcut);
    },
    _submit: () => {},
    '#docs-search': { input: search, focus: search, _keydown: navigate }
  };
});
