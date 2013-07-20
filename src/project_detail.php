<?php
//@@licence@@

$post_id = '';
$post_format = $core->auth->getOption('post_format');
$post_url = '';
$post_lang = $core->auth->getInfo('user_lang');
$post_title = '';
$post_content = '';
$post_content_xhtml = '';
$post_excerpt = '';
$post_excerpt_xhtml = '';
$post_notes = '';
$post_status = 0;
$post_open_comment = $core->blog->settings->system->allow_comments;
$project_phase = 0;
$project_update_template = '';
$project_open_ticket = 1;

$post_media = array();

$can_view_page = true;
$can_edit_post = true /*$core->auth->check('usage,contentadmin',$core->blog->id)*/;
$can_publish = true/*$core->auth->check('publish,contentadmin',$core->blog->id)*/;
$can_delete = false;

# If user can't publish
if (!$can_publish) {
	$post_status = litraak::PROJECT_PENDING;
}

# Status combo
foreach ($litraak->getAllProjectStatus() as $k => $v) {
	$status_combo[$v] = (string) $k;
}

# Phases combo
foreach ($litraak->getAllProjectPhases() as $k => $v) {
	$phases_combo[$v] = (string) $k;
}

# Formaters combo
foreach ($core->getFormaters() as $v) {
	$formaters_combo[$v] = $v;
}

# Languages combo
$rs = $core->blog->getLangs(array('order'=>'asc'));
$all_langs = l10n::getISOcodes(0,1);
$lang_combo = array('' => '', __('Most used') => array(), __('Available') => l10n::getISOcodes(1,1));
while ($rs->fetch()) {
	if (isset($all_langs[$rs->post_lang])) {
		$lang_combo[__('Most used')][$all_langs[$rs->post_lang]] = $rs->post_lang;
		unset($lang_combo[__('Available')][$all_langs[$rs->post_lang]]);
	} else {
		$lang_combo[__('Most used')][$rs->post_lang] = $rs->post_lang;
	}
}
unset($all_langs);
unset($rs);


# Get entry informations
if ((integer) $project_id > 0)
{
	if ($project->isEmpty())
	{
		$core->error->add(__('This project does not exist.'));
		$can_view_page = false;
	}
	else
	{
		$post_id = $project->post_id;
		$post_format = $project->post_format;
		$post_url = $project->post_url;
		$post_lang = $project->post_lang;
		$post_title = $project->post_title;
		$post_content = $project->post_content;
		$post_content_xhtml = $project->post_content_xhtml;
		$post_excerpt = $project->post_excerpt;
		$post_excerpt_xhtml = $project->post_excerpt_xhtml;
		$post_notes = $project->post_notes;
		$post_status = $project->post_status;
		$post_open_comment = (boolean) $project->post_open_comment;
		$project_phase = $project->project_phase;
		$project_update_template = $project->project_update_template;
		$project_open_ticket = (boolean) $project->project_open_ticket;
		
		$can_edit_post = $project->isEditable();
		$can_delete= $project->isDeletable();
		
		try {
			$core->media = new dcMedia($core);
			$post_media = $core->media->getPostMedia($post_id);
		} catch (Exception $e) {}
	}
}

# Format content
if (!empty($_POST) && $can_edit_post)
{
	$post_format = $_POST['post_format'];
	$post_content = $_POST['post_content'];
	
	$post_title = $_POST['post_title'];
	
	if (isset($_POST['post_status'])) {
		$post_status = (integer) $_POST['post_status'];
	}
	
	if (empty($_POST['post_dt'])) {
		$post_dt = '';
	} else {
		$post_dt = strtotime($_POST['post_dt']);
		$post_dt = date('Y-m-d H:i',$post_dt);
	}
	
	$post_open_comment = !empty($_POST['post_open_comment']);
	$post_lang = $_POST['post_lang'];
	
	$post_notes = $_POST['post_notes'];
	
	if (isset($_POST['post_url'])) {
		$post_url = $_POST['post_url'];
	}
	
	$core->blog->setPostContent(
		$post_id,$post_format,$post_lang,
		$post_excerpt,$post_excerpt_xhtml,$post_content,$post_content_xhtml
	);
	
	// Project
	if (isset($_POST['project_phase'])) {
		$project_phase = (integer) $_POST['project_phase'];
	}
	
	$project_update_template = $_POST['project_update_template'];
	$project_open_ticket = !empty($_POST['project_open_ticket']);
}

# Create or update post
if (!empty($_POST) && !empty($_POST['save']) && $can_edit_post)
{
	$cur = $core->con->openCursor($core->prefix.'post');
	
	$cur->post_title = $post_title;
	$cur->post_format = $post_format;
	$cur->post_lang = $post_lang;
	$cur->post_title = $post_title;
	$cur->post_content = $post_content;
	$cur->post_content_xhtml = $post_content_xhtml;
	$cur->post_excerpt = $post_excerpt;
	$cur->post_excerpt_xhtml = $post_excerpt_xhtml;
	$cur->post_notes = $post_notes;
	$cur->post_status = $post_status;
	$cur->post_open_comment = (integer) $post_open_comment;
	$cur->post_dt = date('Y-m-d H:i',time());
	$cur->post_type = litraak::POST_TYPE;	
	
	$cur_info = $core->con->openCursor($core->prefix.'litraak_project_info');
	
	$cur_info->project_phase = $project_phase;
	$cur_info->project_update_template = trim($project_update_template);
	$cur_info->project_open_ticket = (integer) $project_open_ticket;
	
	if (!empty($_POST['post_url'])) {
		$cur->post_url = $post_url;
	}elseif(isset($_POST['post_url']) || !$post_id){
		$cur->post_url = text::tidyURL($post_title, false);
	}
	
	# Update post
	if ($post_id)
	{
		try
		{
			litraak::validateProject($cur);
			
			$core->blog->updPost($post_id,$cur);
			$litraak->updProjectInfo($post_id, $cur_info);
			
			if(strpos($cur->post_url, '/') > -1){
				throw new Exception(__('URL is invalid'));
			}
			
			http::redirect($litraak->getProjectAdminUrl($post_id, false).'&upd=1');
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}
	else
	{
		$cur->user_id = $core->auth->userID();
		
		try
		{
			litraak::validateProject($cur);
			
			$return_id = $core->blog->addPost($cur);
			$litraak->updProjectInfo($return_id, $cur_info);
			
			http::redirect($litraak->getProjectAdminUrl($return_id, false).'&crea=1');
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}
}

if (!empty($_POST['delete']) && $can_delete)
{
	try {
		$core->blog->delPost($post_id);
		http::redirect($litraak->getAdminURL(false));
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

$project_desc_edit_size = (integer) $core->auth->getOption('litraak_desc_edit_size');
$project_desc_edit_size = ($project_desc_edit_size > 0)? $project_desc_edit_size : litraakUtils::PROJECT_DEFAULT_EDIT_SIZE;

/* DISPLAY
------------------------------------------------------------------------------*/

litraakPage::open(__('Litraak'),
	litraakPage::jsDatePicker().
	litraakPage::jsToolBar().
	litraakPage::jsModal().
	litraakPage::jsLoad('index.php?pf=litraak/js/_project.js').
	litraakPage::jsConfirmClose('project-form').
	litraakPage::jsPageTabs($default_tab)
);

if (!empty($_GET['upd'])) {
		echo '<p class="message">'.__('Project has been successfully updated.').'</p>';
}
elseif (!empty($_GET['crea'])) {
		echo '<p class="message">'.__('Project has been successfully created.').'</p>';
}
elseif (!empty($_GET['attached'])) {
	echo '<p class="message">'.__('File has been successfully attached.').'</p>';
}
elseif (!empty($_GET['rmattach'])) {
	echo '<p class="message">'.__('Attachment has been successfully removed.').'</p>';
}

litraakPage::breadCrumb(' &rsaquo; '.$project_title.(($project->isVisible()) ? ' - '.
	'<a class="button" href="'.$litraak->getProjectPublicUrl($project->post_url).'">'.
	__('View project').'</a>' : ''));

$tabs = litraakPage::getProjectTabs($project_id, 'edit-project');
$blocks = explode('%s', $tabs, 2);
echo $blocks[0];


# XHTML conversion
if (!empty($_GET['xconv']))
{
	$post_content = $post_content_xhtml;
	$post_format = 'xhtml';
	
	echo '<p class="message">'.__('Don\'t forget to validate your XHTML conversion by saving your post.').'</p>';
}

echo '<form action="'.$litraak->getProjectAdminURL($project_id, false).'" method="post" id="entry-form">';
echo '<div id="entry-sidebar">';

echo
'<p><label>'.__('Project status:').
form::combo('post_status',$status_combo,$post_status,'',3,!$can_publish).
'</label></p>'.

'<p><label>'.__('Project phase:').
form::combo('project_phase',$phases_combo,$project_phase,'',3).
'</label></p>'.

'<p><label>'.__('Text formating:').
form::combo('post_format',$formaters_combo,$post_format,'',3).
'</label></p>'.

'<p><label class="classic">'.form::checkbox('post_open_comment',1,$post_open_comment,'',3).' '.
__('Accept comments').'</label></p>'.

'<p><label class="classic">'.form::checkbox('project_open_ticket',1,$project_open_ticket,'',3).' '.
__('Accept tickets').'</label></p>'.

'<p><label>'.__('Update template:').
form::field('project_update_template',10,255,html::escapeHTML($project_update_template),'maximal',2).
'</label></p>'.

'<p><label>'.__('Project lang:').
form::combo('post_lang',$lang_combo,$post_lang,'',5).
'</label></p>'.

'<div class="lockable">'.
'<p><label>'.__('Basename:').
form::field('post_url',10,255,html::escapeHTML($post_url),'maximal',3).
'</label></p>'.
'<p class="form-note warn">'.
__('Warning: If you set the URL manually, it may conflict with another project.').
'</p>'.
'</div>';

if ($post_id)
{
	echo
	'<h3 class="clear">'.__('Attachments').'</h3>';
	foreach ($post_media as $f)
	{
		$ftitle = $f->media_title;
		if (strlen($ftitle) > 18) {
			$ftitle = substr($ftitle,0,16).'...';
		}
		echo
		'<div class="media-item">'.
		'<a class="media-icon" href="media_item.php?id='.$f->media_id.'">'.
		'<img src="'.$f->media_icon.'" alt="" title="'.$f->basename.'" /></a>'.
		'<ul>'.
		'<li><a class="media-link" href="media_item.php?id='.$f->media_id.'"'.
		'title="'.$f->basename.'">'.$ftitle.'</a></li>'.
		'<li>'.$f->media_dtstr.'</li>'.
		'<li>'.files::size($f->size).' - '.
		'<a href="'.$f->file_url.'">'.__('open').'</a>'.'</li>'.
		
		'<li class="media-action"><a class="attachment-remove" id="attachment-'.$f->media_id.'" '.
		'href="post_media.php?post_id='.$post_id.'&amp;media_id='.$f->media_id.'&amp;remove=1">'.
		'<img src="images/check-off.png" alt="'.__('remove').'" /></a>'.
		'</li>'.
		
		'</ul>'.
		'</div>';
	}
	unset($f);
	
	if (empty($post_media)) {
		echo '<p>'.__('No attachment.').'</p>';
	}
	echo '<p><a href="media.php?post_id='.$post_id.'">'.__('Add files to this project').'</a></p>';
}

echo '</div>';		// End #entry-sidebar

echo '<div id="entry-content"><fieldset class="constrained">';

echo
'<p class="col"><label class="required" title="'.__('Required field').'">'.__('Name:').
form::field('post_title',20,255,html::escapeHTML($post_title),'maximal',2).
'</label></p>'.

'<p class="area"><label class="required" title="'.__('Required field').'" for="post_content">'.__('Description:').'</label> '.
form::textarea('post_content',50,$project_desc_edit_size,html::escapeHTML($post_content),'',2).
'</p>'.

'<p class="area" id="notes-area"><label>'.__('Notes:').'</label>'.
form::textarea('post_notes',50,5,html::escapeHTML($post_notes),'',2).
'</p>';

echo
'<p>'.
($post_id ? form::hidden('id',$post_id) : '').
'<input type="submit" value="'.__('save').' (s)" tabindex="4" '.
'accesskey="s" name="save" /> '.
($can_delete ? '<input type="submit" value="'.__('delete').'" name="delete" />' : '').
$core->formNonce().
'</p>';

echo '</fieldset></div>';		// End #entry-content
echo '</form>';

if ($post_id && !empty($post_media))
{
	echo
	'<form action="post_media.php" id="attachment-remove-hide" method="post">'.
	'<div>'.form::hidden(array('post_id'),$post_id).
	form::hidden(array('media_id'),'').
	form::hidden(array('remove'),1).
	$core->formNonce().'</div></form>';
}

litraakPage::helpBlock('project');
echo $blocks[1];
litraakPage::close();

?>