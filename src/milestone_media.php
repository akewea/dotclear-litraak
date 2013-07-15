<?php
//@@licence@@

litraakPage::check('usage,contentadmin');

$post_id = !empty($_REQUEST['attachtoid']) ? (integer) $_REQUEST['attachtoid'] : null;
$media_id = !empty($_REQUEST['attachid']) ? (integer) $_REQUEST['attachid'] : null;

if (!$post_id) {
	exit;
}
$rs = $litraak->getMilestones(array('milestone_id' => $post_id));
if ($rs->isEmpty()) {
	exit;
}

if ($post_id && $media_id && !empty($_POST['attach']))
{
	$core->media = new litraakMedia($core);
	$core->media->addPostMedia($post_id,$media_id);
	http::redirect($litraak->getMilestoneAdminURL($rs->post_id,$rs->milestone_id,false));
}

try {
	$core->media = new litraakMedia($core);
	$f = $core->media->getPostMedia($post_id,$media_id);
	if (empty($f)) {
		$post_id = $media_id = null;
		throw new Exception(__('This attachment does not exist'));
	}
	$f = $f[0];
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

# Remove a media from en
if (($post_id && $media_id) || $core->error->flag())
{
	if (!empty($_POST['remove']))
	{
		$core->media->removePostMedia($post_id,$media_id);
		http::redirect($litraak->getMilestoneAdminURL($rs->post_id,$rs->milestone_id,false).'&rmattach=1');
	}
	elseif (isset($_POST['post_id'])) {
		http::redirect($litraak->getMilestoneAdminURL($rs->post_id,$rs->milestone_id,false));
	}
	
	if (!empty($_GET['remove']))
	{
		litraakPage::open(__('Remove attachment'));
		
		echo '<h2>'.__('Attachment').' &rsaquo; '.__('confirm removal').'</h2>';
		
		echo
		'<form action="plugin.php?p=litraak" method="post">'.
		'<p>'.__('Are you sure you want to remove this attachment?').'</p>'.
		'<p><input type="submit" value="'.__('cancel').'" /> '.
		' &nbsp; <input type="submit" name="remove" value="'.__('yes').'" />'.
		form::hidden('attachtoid',$post_id).
		form::hidden('attachid',$media_id).
		$core->formNonce().'</p>'.
		'</form>';
		
		litraakPage::close();
	}
}
?>