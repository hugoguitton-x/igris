import "./styles/admin.scss";

import "bootstrap";

import '@fortawesome/fontawesome-free/js/fontawesome'
import '@fortawesome/fontawesome-free/js/solid'
import '@fortawesome/fontawesome-free/js/regular'
import '@fortawesome/fontawesome-free/js/brands'

import $ from "jquery";

$("#menu-toggle").on('click', function (e) {
  e.preventDefault();
  $("#wrapper").toggleClass("toggled");
});
