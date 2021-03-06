dotclear.productExpander = function(line) {
	var td = line.firstChild;
	var img = document.createElement('img');
	img.src = dotclear.img_plus_src;
	img.alt = dotclear.img_plus_alt;
	img.className = 'expand';
	$(img).css('cursor', 'pointer');
	img.line = line;
	img.onclick = function() {
		dotclear.viewProductContent(this, this.line);
	};
	td.insertBefore(img, td.firstChild);
};
dotclear.viewProductContent = function(img, line) {
	var postId = line.id.substr(1);
	var tr = document.getElementById('pe' + postId);
	if (!tr) {
		tr = document.createElement('tr');
		tr.id = 'pe' + postId;
		var td = document.createElement('td');
		td.colSpan = 8;
		td.className = 'expand';
		tr.appendChild(td);
		img.src = dotclear.img_minus_src;
		img.alt = dotclear.img_minus_alt;
		$.get('services.php', {
			f : 'getPostById',
			id : postId,
			post_type : ''
		}, function(data) {
			var rsp = $(data).children('rsp')[0];
			if (rsp.attributes[0].value == 'ok') {
				var post_content = $(rsp).find('post_display_content').text();
				var res = '';
				if (post_content) {
					res += post_content;
				}
				$(td).append(res);
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
	$('#projects-list tr.line').each( function() {
		dotclear.productExpander(this);
	});
	$('.checkboxes-helpers').each(function(){
		dotclear.checkboxesHelpers(this);
	});
	$('#form-entries td input[type=checkbox]').enableShiftClick();
	dotclear.postsActionsHelper();
});