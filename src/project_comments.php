<?php
//@@licence@@

$params = array('post_id' => $project_id, 'order' => 'comment_dt ASC');
	
$comments = $core->blog->getComments(array_merge($params,array('comment_trackback'=>0)));

# Actions combo box
$combo_action = array();
if ($can_edit_post && $core->auth->check('publish,contentadmin',$core->blog->id))
{
	$combo_action[__('publish')] = 'publish';
	$combo_action[__('unpublish')] = 'unpublish';
	$combo_action[__('mark as pending')] = 'pending';
	$combo_action[__('mark as junk')] = 'junk';
}

if ($can_edit_post && $core->auth->check('delete,contentadmin',$core->blog->id))
{
	$combo_action[__('delete')] = 'delete';
}

$has_action = !empty($combo_action) && !$comments->isEmpty();

/* DISPLAY
------------------------------------------------------------------------------*/

litraakPage::open(__('Litraak'),
	litraakPage::jsDatePicker().
	litraakPage::jsToolBar().
	litraakPage::jsModal().
	litraakPage::jsLoad('index.php?pf=litraak/js/_project.js').
	litraakPage::jsConfirmClose('project-form','comment-form').
	litraakPage::jsPageTabs($default_tab)
);

litraakPage::breadCrumb(' &rsaquo; <a href="'.$litraak->getProjectAdminUrl($project_id).
	'">'.$project_title.'</a> &rsaquo; '.__('Comments')/*.' - '.
	'<a class="button" href="'.$litraak->getCommentAdminUrl($project_id, '').'">'.
	__('New comment').'</a>'*/);// TODO page d'ajout de commentaire en se basant sur celle des articles.

$tabs = litraakPage::getProjectTabs($project_id, 'comments');
$blocks = explode('%s', $tabs, 2);
echo $blocks[0];

//if ($has_action) {
	echo '<form action="comments_actions.php" method="post">';
//}
if (!$comments->isEmpty()) {
	showComments($comments,$has_action);
} else {
	echo '<p>'.__('No comment').'</p>';
}

if ($has_action) {
	echo
	'<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.
	
	'<p class="col right">'.__('Selected comments action:').' '.
	form::combo('action',$combo_action).
	form::hidden('redir','plugin.php?p=litraak&projectid='.$project_id.'&amp;co=1').
	$core->formNonce().
	'<input type="submit" value="'.__('ok').'" /></p>'.
	'</div>'.
	'</form>';
}


# Show comments or trackbacks
function showComments($rs,$has_action)
{
	echo
	'<table class="comments-list"><tr>'.
	'<th colspan="2">'.__('Author').'</th>'.
	'<th>'.__('Date').'</th>'.
	'<th class="nowrap">'.__('IP address').'</th>'.
	'<th>'.__('Status').'</th>'.
	'<th>&nbsp;</th>'.
	'</tr>';
	
	while($rs->fetch())
	{
		$comment_url = 'comment.php?id='.$rs->comment_id;
		
		$img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
		switch ($rs->comment_status) {
			case 1:
				$img_status = sprintf($img,__('published'),'check-on.png');
				break;
			case 0:
				$img_status = sprintf($img,__('unpublished'),'check-off.png');
				break;
			case -1:
				$img_status = sprintf($img,__('pending'),'check-wrn.png');
				break;
			case -2:
				$img_status = sprintf($img,__('junk'),'junk.png');
				break;
		}
		
		echo
		'<tr class="line'.($rs->comment_status != 1 ? ' offline' : '').'"'.
		' id="c'.$rs->comment_id.'">'.
		
		'<td class="nowrap">'.
		($has_action ? form::checkbox(array('comments[]'),$rs->comment_id,'','','',0) : '').'</td>'.
		'<td class="maximal">'.html::escapeHTML($rs->comment_author).'</td>'.
		'<td class="nowrap">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$rs->comment_dt).'</td>'.
		'<td class="nowrap"><a href="comments.php?ip='.$rs->comment_ip.'">'.$rs->comment_ip.'</a></td>'.
		'<td class="nowrap status">'.$img_status.'</td>'.
		'<td class="nowrap status"><a href="'.$comment_url.'">'.
		'<img src="images/edit-mini.png" alt="" title="'.__('Edit this comment').'" /></a></td>'.
		
		'</tr>';
	}
	
	echo '</table>';
}

echo $blocks[1];
litraakPage::close();

?>