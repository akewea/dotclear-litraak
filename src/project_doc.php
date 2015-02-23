<?php
//@@licence@@

$post_id = '';
$post_title = '';
$post_format = $core->auth->getOption('post_format');
$post_lang = $core->auth->getInfo('user_lang');
$post_excerpt = '';
$post_excerpt_xhtml = '';
$post_content = '';
$post_content_xhtml = '';

$can_view_page = true;
$can_edit_post = true /*$core->auth->check('usage,contentadmin',$core->blog->id)*/;
$can_delete = false;

# Formaters combo
foreach ($core->getFormaters() as $v) {
	$formaters_combo[$v] = $v;
}

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
		$post_title = $project->post_title;
		$post_format = $project->post_format;
		$post_lang = $project->post_lang;
		$post_excerpt = $project->post_excerpt;
		$post_excerpt_xhtml = $project->post_excerpt_xhtml;
		$post_content = $project->post_content;
		$post_content_xhtml = $project->post_content_xhtml;
		
		$can_edit_post = $project->isEditable();
		$can_delete= $project->isDeletable();
	}
}

# Format excerpt and content
if (!empty($_POST) && $can_edit_post)
{
	$post_format = $_POST['post_format'];
	$post_excerpt = $_POST['post_excerpt'];
		
	$core->blog->setPostContent(
		$post_id,$post_format,$post_lang,
		$post_excerpt,$post_excerpt_xhtml,$post_content,$post_content_xhtml
	);
}

# Create or update post
if (!empty($_POST) && !empty($_POST['save']) && $can_edit_post)
{
	$cur = $core->con->openCursor($core->prefix.'post');
	
	$cur->post_title = $post_title;
	$cur->post_dt = date('Y-m-d H:i',time());
	$cur->post_format = $post_format;
	$cur->post_excerpt = $post_excerpt;
	$cur->post_excerpt_xhtml = $post_excerpt_xhtml;
	$cur->post_content = $post_content;
	$cur->post_content_xhtml = $post_content_xhtml;
	
	# Update post
	try
	{
		$core->blog->updPost($post_id,$cur);
		
		http::redirect($litraak->getProjectAdminUrl($post_id, false).'&tab=edit-doc&upd=1');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

$project_doc_edit_size = (integer) $core->auth->getOption('litraak_doc_edit_size');
$project_doc_edit_size = ($project_doc_edit_size > 0)? $project_doc_edit_size : litraakUtils::PROJECT_DEFAULT_EDIT_SIZE;

/* DISPLAY
------------------------------------------------------------------------------*/

litraakPage::open(__('Litraak'),
	litraakPage::jsDatePicker().
	litraakPage::jsToolBar().
	litraakPage::jsModal().
	litraakPage::jsLoad('index.php?pf=litraak/js/_project.js').
	litraakPage::jsConfirmClose('project-form')
);

if (!empty($_GET['upd'])) {
		echo '<p class="message">'.__('Project documentation has been successfully updated.').'</p>';
}

# XHTML conversion
if (!empty($_GET['xconv']))
{
	$post_excerpt = $post_excerpt_xhtml;
	$post_content = $post_content_xhtml;
	$post_format = 'xhtml';
	
	echo '<p class="message">'.__('Don\'t forget to validate your XHTML conversion by saving your post.').'</p>';
}

litraakPage::breadCrumb(' &rsaquo; '.$project_title.(($project->isVisible()) ? ' - '.
	'<a class="button" href="'.$litraak->getProjectPublicUrl($project->post_url).'documentation">'.
	__('View documentation').'</a>' : ''));

$tabs = litraakPage::getProjectTabs($project_id, 'edit-doc');
$blocks = explode('%s', $tabs, 2);
echo $blocks[0];

echo '<form action="'.$litraak->getProjectAdminURL($project_id, false).'&tab=edit-doc" method="post" id="entry-form">';
echo '<div id="entry-sidebar">';

echo

'<p><label>'.__('Text formating:').
form::combo('post_format',$formaters_combo,$post_format,'',3).
'</label></p>';

echo '</div>';		// End #entry-sidebar

echo '<div id="entry-content"><fieldset class="constrained">';

echo
'<p class="area"><label class="required" title="'.__('Required field').'" '.
'for="post_excerpt">'.__('Documentation:').'</label> '.
form::textarea('post_excerpt',50,$project_doc_edit_size,html::escapeHTML($post_excerpt),'',2).
'</p>'.

'<p>'.
($post_id ? form::hidden('id',$post_id) : '').
'<input type="submit" value="'.__('save').' (s)" tabindex="4" '.
'accesskey="s" name="save" /> '.
$core->formNonce().
'</p>';

echo '</fieldset></div>';		// End #entry-content
echo '</form>';


litraakPage::helpBlock('project-doc');
echo $blocks[1];
litraakPage::close();

?>