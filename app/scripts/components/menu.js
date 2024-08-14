function reset($) {
  $.marginMenu = '';
  $.contentStyle = {
    marginLeft: '',
    marginRight: '',
    overflow: ''
  };
}

function toogle($) {
  if ($.marginMenu === '0') {
    reset($);
  } else {
    $.marginMenu = '0';
    $.contentStyle = {
      marginLeft: '16.5em',
      marginRight: '-15em',
      overflow: 'hidden'
    };
  }
}

export default ($) => ({
    mount: () => console.log($),
    '#menu-list': {
        click: () => toogle($)
    }
});
