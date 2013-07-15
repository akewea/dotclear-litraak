$( function() {
	if (!document.getElementById) {
		return;
	}
	var tbTicket = new jsToolBar(document.getElementById('ticket_desc'));
	tbTicket.draw('xhtml');
	$('#ticket-form input[@name="delete"]').click( function() {
		return window.confirm(dotclear.msg.confirm_delete);
	});
	if(document.getElementById('ticket_comment')){
		var tbChange = new jsToolBar(document.getElementById('ticket_comment'));
		tbChange.draw('xhtml');
	}
});