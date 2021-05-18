import $ from "jquery";

$(function () {
  if (!window.matchMedia) return;

  var current = $('head > link[rel="icon"][media]');
  $.each(current, function (i, icon) {
    var match = window.matchMedia(icon.media);
    function swap() {
      if (match.matches) {
        current.remove();
        current = $(icon).appendTo("head");
      }
    }
    match.addListener(swap);
    swap();
  });
});
