dotclear.ticketExpander = function(line) {
	var td = line.firstChild;
	var img = document.createElement('img');
	img.src = dotclear.img_plus_src;
	img.alt = dotclear.img_plus_alt;
	img.className = 'expand';
	$(img).css('cursor', 'pointer');
	img.line = line;
	img.onclick = function() {
		dotclear.viewTicketContent(this, this.line);
	};
	td.insertBefore(img, td.firstChild);
};
dotclear.viewTicketContent = function(img, line) {
	var commentId = line.id.substr(1);
	var tr = document.getElementById('ce' + commentId);
	if (!tr) {
		tr = document.createElement('tr');
		tr.id = 'ce' + commentId;
		var td = document.createElement('td');
		td.colSpan = 6;
		td.className = 'expand';
		tr.appendChild(td);
		img.src = dotclear.img_minus_src;
		img.alt = dotclear.img_minus_alt;
		$.get('services.php', {
			f : 'getTicketById',
			id : commentId
		}, function(data) {
			var rsp = $(data).children('rsp')[0];
			if (rsp.attributes[0].value == 'ok') {
				var ticket = $(rsp).find('ticket_desc').text();
				if (ticket) {
					$(td).append(ticket);
					var ticket_email = $(rsp).find('ticket_email').text();
					$(td).append(
							'<p><strong>'
									+ dotclear.msg.email + '</strong> '
									+ ticket_email + '</p>');
				}
			} else {
				alert($(rsp).find('message').text());
			}
		});
		$(line).toggleClass('expand');
		line.parentNode.insertBefore(tr, line.nextSibling);
	} else if (tr.style.display == 'none') {
		$(tr).toggle();
		$(line).toggleClass('expand');
		img.src = dotclear.img_minus_src;
		img.alt = dotclear.img_minus_alt;
	} else {
		$(tr).toggle();
		$(line).toggleClass('expand');
		img.src = dotclear.img_plus_src;
		img.alt = dotclear.img_plus_alt;
	}
};
$( function() {
	$('#tickets-table tr.line').each( function() {
		dotclear.ticketExpander(this);
	});
	$('.checkboxes-helpers').each(function(){
		dotclear.checkboxesHelpers(this);
	});
	$('#form-tickets td input[type=checkbox]').enableShiftClick();
	dotclear.postsActionsHelper();
});