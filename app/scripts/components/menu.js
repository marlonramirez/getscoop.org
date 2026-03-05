export default ($) => ({
    '#menu-list': {
        _click: () => {
          $.menuClass.toggle("active");
          $.contentClass.toggle("active");
        }
    }
});
