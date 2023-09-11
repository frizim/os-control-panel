$('#isc').on('show.bs.modal', function(event) {
    let identCol = $(event.relatedTarget).parent();
    let uuid = identCol.data('uuid');
    $('#isc-ident-uuid').attr('value', uuid);
    $('#isc-ident-name').text(identCol.prev().text());
});
$('#idc').on('show.bs.modal', function(event) {
    let identCol = $(event.relatedTarget).parent();
    let uuid = identCol.data('uuid');
    $('#idc-ident-uuid').attr('value', uuid);
    $('#idc-ident-name').text(identCol.prev().text());
});