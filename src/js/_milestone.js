$( function() {
	var milestone_dtPick = new datePicker($('#milestone_dt').get(0));
	milestone_dtPick.img_top = '1.5em';
	milestone_dtPick.draw();
	dotclear.hideLockable();
});
$( function() {
	if (!document.getElementById) {
		return;
	}
	var tbMilestone = new jsToolBar(document.getElementById('milestone_desc'));
	tbMilestone.draw('xhtml');
	$('#milestone-form input[@name="delete"]').click( function() {
		return window.confirm(dotclear.msg.confirm_delete);
	});
});