"use strict";

import "../scss/dashboard.scss";
import "bootstrap/dist/js/bootstrap.bundle.min.js";

const body = document.getElementsByTagName("body")[0];
const scrollToTop = document.querySelector(".scroll-to-top");

function toggleClass(el, cl) {
  if (!el) {
    return;
  }

  if (el.classList.contains(cl)) {
    el.classList.remove(cl);
  }
  else {
    el.classList.add(cl);
  }
}

document.getElementById("sidebarToggle").addEventListener("click", e => {
  e.preventDefault();
  toggleClass(body, "sidebar-toggled");
  toggleClass(document.getElementsByClassName("sidebar")[0], "toggled");
});

if (scrollToTop) {
  document.onscroll = e => {
    if (document.scrollTop > 100) {
      scrollToTop.classList.add("visible");
    }
    else {
      scrollToTop.classList.remove("visible");
    }
  };
}