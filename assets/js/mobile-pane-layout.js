(function () {
  function getHeaderEl() {
    return (
      document.getElementById('masthead') ||
      document.querySelector('.site-header') ||
      document.querySelector('header.site-header') ||
      document.querySelector('header')
    );
  }

  function setHeaderVar() {
    var header = getHeaderEl();
    if (!header) return;

    var h = Math.ceil(header.getBoundingClientRect().height);
    if (h > 0) {
      document.documentElement.style.setProperty('--glandore-header-h', h + 'px');
    }
  }

  var t = null;
  function onResize() {
    if (t) window.clearTimeout(t);
    t = window.setTimeout(setHeaderVar, 100);
  }

  document.addEventListener('DOMContentLoaded', function () {
    setHeaderVar();
    window.addEventListener('resize', onResize, { passive: true });
  });
})();
