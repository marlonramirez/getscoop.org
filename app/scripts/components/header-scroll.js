export default () => {
  let header;
  let hero;
  let lastY = 0;
  let hidden = 0;
  let frame = 0;
  let observer;

  const reset = () => {
    lastY = Math.max(0, window.scrollY);
    hidden = 0;
    document.body.style.removeProperty('--site-header-offset');
  };

  const update = () => {
    frame = 0;
    const y = Math.max(0, window.scrollY);
    const previousY = lastY;
    const delta = y - previousY;
    lastY = y;
    const height = header.offsetHeight;
    const hideStart = hero ? y + hero.getBoundingClientRect().bottom - height : 0;
    if (y <= hideStart || header.contains(document.activeElement)) {
      hidden = 0;
    } else if (delta > 0) {
      hidden = Math.min(height, hidden + y - Math.max(previousY, hideStart));
    } else if (delta < 0) {
      hidden = Math.max(0, hidden + delta);
    }
    document.body.style.setProperty('--site-header-offset', `${height - hidden}px`);
  };
  const scroll = () => {
    if (!frame) frame = window.requestAnimationFrame(update);
  };

  return {
    mount: event => {
      header = event.currentTarget;
      hero = document.querySelector('#welcome');
      if (!header || (!hero && !document.querySelector('#main-docs'))) return;
      document.body.setAttribute('data-scroll-header', '');
      reset();
      window.addEventListener('scroll', scroll, { passive: true });
      window.addEventListener('pageshow', reset);
      header.addEventListener('focusin', reset);
      observer = new ResizeObserver(reset);
      observer.observe(header);
      if (hero) observer.observe(hero);
    },
    unmount: () => {
      window.removeEventListener('scroll', scroll);
      window.removeEventListener('pageshow', reset);
      header?.removeEventListener('focusin', reset);
      window.cancelAnimationFrame(frame);
      observer?.disconnect();
      document.body.style.removeProperty('--site-header-offset');
      document.body.removeAttribute('data-scroll-header');
    }
  };
};
