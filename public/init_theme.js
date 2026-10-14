(function () {
  try {
    var theme = localStorage.getItem('scoop.theme');
    if (theme === 'light' || theme === 'dark') document.documentElement.setAttribute('data-theme', theme);
  } catch (error) { }
}());
