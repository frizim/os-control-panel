"use strict";

import "../scss/login/login.scss";
import "bootstrap/dist/js/bootstrap.bundle.min.js";

let input = document.querySelectorAll('.validate-input .input100');

document.querySelector(".validate-form").addEventListener("submit", e => {
    let check = true;

    for(const field of input) {
        if(!validate(field)){
            showValidate(field);
            check=false;
        }
    }

    if(!check) {
        e.preventDefault();
    }
});

input.forEach(el => {
    el.addEventListener("focus", e => {
        hideValidate(e.target);
    });
})

function validate(input) {
    if(input.getAttribute("type") === "email" || input.getAttribute("name") === "email") {
        if(input.value.trim().match(/^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{1,5}|[0-9]{1,3})(\]?)$/) == null) {
            return false;
        }
    }
    else if(input.value.trim() == ''){
        return false;
    }
}

function showValidate(input) {
    input.parentElement.classList.add("alert-validate");
}

function hideValidate(input) {
    input.parentElement.classList.remove("alert-validate");
}
