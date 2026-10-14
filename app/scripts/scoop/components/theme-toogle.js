export default ($) => {
  const root = document.documentElement;
  const preference = window.matchMedia('(prefers-color-scheme: dark)');
  const isDark = () => root.dataset.theme === 'dark'
    || (root.dataset.theme !== 'light' && preference.matches);
  const update = () => $.label = isDark() ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro';
  let observer;

  return {
    mount: () => {
      preference.addEventListener('change', update);
      observer = new MutationObserver(update);
      observer.observe(root, { attributes: true, attributeFilter: ['data-theme'] });
      update();
    },
    click: () => {
      const theme = isDark() ? 'light' : 'dark';
      root.dataset.theme = theme;
      update();
    },
    unmount: () => {
      preference.removeEventListener('change', update);
      observer?.disconnect();
    }
  };
};
