document.getElementById("isc").addEventListener("show.bs.modal", event => {
    let identCol = event.relatedTarget.parentElement;
    let uuid = identCol.dataset.uuid;
    document.getElementById("isc-ident-uuid").setAttribute("value", uuid);
    document.getElementById("isc-ident-name").innerText = identCol.previousElementSibling.innerText;

});

document.getElementById("idc").addEventListener("show.bs.modal", event => {
    let identCol = event.relatedTarget.parentElement;
    let uuid = identCol.dataset.uuid;
    document.getElementById("idc-ident-uuid").setAttribute("value", uuid);
    document.getElementById("idc-ident-name").innerText = identCol.previousElementSibling.innerText;
});