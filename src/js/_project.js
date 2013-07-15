dotclear.commentExpander = function(line) {
	var td = line.firstChild;
	var img = document.createElement('img');
	img.src = dotclear.img_plus_src;
	img.alt = dotclear.img_plus_alt;
	img.className = 'expand';
	$(img).css('cursor', 'pointer');
	img.line = line;
	img.onclick = function() {
		dotclear.viewCommentContent(this, this.line);
	};
	td.insertBefore(img, td.firstChild);
};

dotclear.viewCommentContent = function(img, line) {
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
			f : 'getCommentById',
			id : commentId
		}, function(data) {
			var rsp = $(data).children('rsp')[0];
			if (rsp.attributes[0].value == 'ok') {
				var comment = $(rsp).find('comment_display_content').text();
				if (comment) {
					$(td).append(comment);
					var comment_email = $(rsp).find('comment_email').text();
					var comment_site = $(rsp).find('comment_site').text();
					var comment_ip = $(rsp).find('comment_ip').text();
					var comment_spam_disp = $(rsp).find('comment_spam_disp')
							.text();
					$(td).append(
							'<p><strong>' + dotclear.msg.website + '</strong> '
									+ comment_site + '<br />' + '<strong>'
									+ dotclear.msg.email + '</strong> '
									+ comment_email + '<br />'
									+ comment_spam_disp + '</p>');
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
	if (!document.getElementById) {
		return;
	}
	if (document.getElementById('edit-project')) {
		var formatField = $('#post_format').get(0);
		$(formatField).change( function() {
			contentTb.switchMode(this.value);
		});
		var contentTb = new jsToolBar(document.getElementById('post_content'));
		contentTb.context = 'post';
	}
	if (document.getElementById('edit-doc')) {
		var formatField = $('#post_format').get(0);
		$(formatField).change( function() {
			excerptTb.switchMode(this.value);
		});
		var excerptTb = new jsToolBar(document.getElementById('post_excerpt'));
		excerptTb.context = 'post';
	}
	if (document.getElementById('comment_content')) {
		var commentTb = new jsToolBar(document
				.getElementById('comment_content'));
	}
	$('#post-preview')
			.modalWeb($(window).width() - 40, $(window).height() - 40);
	$('#edit-project')
			.onetabload(
					function() {
						dotclear.hideLockable();
						$('input[@name="delete"]')
								.click(
										function() {
											return window
													.confirm(dotclear.msg.confirm_delete_post);
										});
						$('#notes-area label').toggleWithLegend(
								$('#notes-area').children().not('label'), {
									cookie : 'dcx_post_notes',
									hide : $('#post_notes').val() == ''
								});
						$('#post_lang').parent().toggleWithLegend(
								$('#post_lang'), {
									cookie : 'dcx_post_lang'
								});
						contentTb.switchMode(formatField.value);
						$('a.attachment-remove')
								.click(
										function() {
											this.href = '';
											var m_name = $(this).parents('ul')
													.find('li:first>a').attr(
															'title');
											if (window
													.confirm(dotclear.msg.confirm_remove_attachment
															.replace('%s',
																	m_name))) {
												var f = $(
														'#attachment-remove-hide')
														.get(0);
												f.elements['media_id'].value = this.id
														.substring(11);
												f.submit();
											}
											return false;
										});
					});
	$('#edit-doc')
		.onetabload(
			function() {
				excerptTb.switchMode(formatField.value);
			});
	$('#comments').onetabload( function() {
		$('.comments-list tr.line').each( function() {
			dotclear.commentExpander(this);
		});
		$('.checkboxes-helpers').each( function() {
			dotclear.checkboxesHelpers(this);
		});
		dotclear.commentsActionsHelper();
	});
	$('#add-comment').onetabload( function() {
		commentTb.draw('xhtml');
	});
});