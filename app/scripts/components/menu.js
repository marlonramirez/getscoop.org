function toogle($) {
  if ($.marginMenu === '0') {
    $.reset();
  } else {
    $.marginMenu = '0';
    $.contentStyle = {
      marginLeft: '16.5em',
      marginRight: '-15em'
    };
  }
}

export default ($) => ({
    mount: () => window.addEventListener("resize", () => $.reset()),
    '#menu-list': {
        click: () => toogle($)
    }
});
