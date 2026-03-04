function toogle($) {
  if ($.marginMenu === '0') {
    $.marginMenu = '';
    $.contentStyle = {
      marginLeft: '',
      marginRight: '',
      overflow: ''
    };
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
    '#menu-list': {
        _click: () => toogle($)
    }
});
