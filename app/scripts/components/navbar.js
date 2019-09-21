function toogle($) {
  if ($.showNav) {
    $.reset();
  } else {
    $.showNav = 'block';
    $.classIcon = 'open';
  }
}

export default ($) => ({
  mount: () => window.addEventListener('resize', () => $.reset()),
  '#nav-icon': {
    click: () => toogle($)
  }
});
