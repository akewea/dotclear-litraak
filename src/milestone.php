<?php
//@@licence@@

$err = false;

// Champs de la page
$project_id = isset($_GET['projectid']) ?	(integer) $_GET['projectid'] : 0;
$milestone_id = $_GET['milestoneid'] > 0 ?	(integer) $_GET['milestoneid'] : -1;

if(!empty($_POST['exit'])){
	http::redirect('plugin.php?p=litraak&projectid='.$project_id.'&tab=milestones',false);
}

if(!empty($_POST['upd']) || !empty($_POST['add'])){
try
	{
		$rs = $litraak->getProjects(array('post_id' => $project_id, 'post_type' => litraak::POST_TYPE));
		
		if ($rs->isEmpty()) {
			throw new Exception(__('Project does not exist.'));
		}
		
		$cur = $core->con->openCursor($core->prefix.'litraak_milestone');
		
		$cur->milestone_status = (integer) $_POST['milestone_status'];
		$cur->milestone_dt = $_POST['milestone_dt'];
		$cur->milestone_desc = $core->HTMLfilter($_POST['milestone_desc']);
		$cur->milestone_name = html::clean($_POST['milestone_name']);
		if(isset($_POST['milestone_url'])){
			$cur->milestone_url = html::clean($_POST['milestone_url']);
		}
		
		if(!empty($_POST['upd'])){
			$litraak->updMilestone($milestone_id, $cur);
		}else{	
			$cur->post_id = $project_id;		
			$milestone_id = $litraak->addMilestone($cur);
		}
		
		//http::redirect($core->getPostAdminURL($rs->post_type,$rs->post_id,false).'&co=1&creaco=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
		$err = true;
	}
}

if(!empty($_POST['dlt'])){
	try {
		$litraak->delMilestone($milestone_id);
		http::redirect('plugin.php?p=litraak&projectid='.$project_id.'&tab=milestones',false);
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

$milestone = $litraak->getMilestones(array('milestone_id' => $milestone_id, 'post_id' => $project_id));

$post_media = array();
if(!$milestone->isEmpty()){
	$project_title = $milestone->post_title;
	try {
		$core->media = new litraakMedia($core);
		$post_media = $core->media->getPostMedia($milestone_id);
	} catch (Exception $e) {}
}else{
	$project = $litraak->getProjects(array('post_id' => $project_id, 'no_content' => true));
	$project->fetch();
	$project_title = $project->post_title;
}

// Champs de saisie
$milestone_status = ($err)? $_POST['milestone_status'] : $milestone->milestone_status;
$milestone_url = ($err)? $_POST['milestone_url'] : $milestone->milestone_url;
$milestone_name = ($err)? $_POST['milestone_name'] : $milestone->milestone_name;
$milestone_desc = ($err)? $_POST['milestone_desc'] : $milestone->milestone_desc;
$milestone_dt = ($err)? $_POST['milestone_dt'] : $milestone->milestone_dt;

$status_combo = litraakUtils::getMilestoneStatusCombo($litraak);

/* Display
------------------------------------------------------------------------------*/

litraakPage::open(__('Litraak'),
	litraakPage::jsDatePicker().
	litraakPage::jsToolBar().
	litraakPage::jsLoad('index.php?pf=litraak/js/_milestone.js').
	litraakPage::jsConfirmClose('milestone-form')
);

if (!$err && !empty($_POST['upd'])) {
	echo '<p class="message">'.__('Milestone has been successfully updated.').'</p>';
}
if (!$err && !empty($_POST['add'])) {
	echo '<p class="message">'.__('Milestone has been successfully created.').'</p>';
}

litraakPage::breadCrumb(' &rsaquo; <a href="'.$litraak->getProjectAdminUrl($project_id).
	'">'.$project_title.'</a> &rsaquo; <a href="'.$litraak->getProjectAdminUrl($project_id).
	'&tab=milestones">'.__('Milestones').'</a> &rsaquo; '.
	(($milestone->milestone_id)?$milestone->milestone_name.' - '.
	'<a class="button" href="'.$litraak->getMilestonePublicUrl($milestone->post_url, $milestone->milestone_url).'">'.
	__('View milestone').'</a>':__('New milestone')));

$tabs = litraakPage::getProjectTabs($project_id, 'milestones');
$blocks = explode('%s', $tabs, 2);
echo $blocks[0];

$tickets_url = 'plugin.php?p=litraak&projectid='.$project_id.'&tab=tickets&ticket_milestone='.$milestone->milestone_id;

# Progress bar
$progress = '';
if($milestone->milestone_id > 0) {
	$progress .= 
		'<fieldset><legend>'.__('Tickets').'</legend>'.
		'<p>'.($milestone->milestone_nb_tickets > 0 ? '<a href="'.$tickets_url.'&ticket_status=">'.$milestone->milestone_nb_tickets.'</a>' : '0').' '.__('tickets');
	if($milestone->milestone_nb_tickets > 0){
		$progress .= 
		', '.($milestone->milestone_nb_actives > 0 ? '<a href="'.$tickets_url.'&ticket_status=actives">'.$milestone->milestone_nb_actives.'</a>' : '0').' '.__('actives').
		', '.($milestone->milestone_nb_wasted > 0 ? '<a href="'.$tickets_url.'&ticket_status=wasted">'.$milestone->milestone_nb_wasted.'</a>' : '0').' '.__('wasted');
	}
	$progress .= '.</p>';
	if($milestone->milestone_nb_tickets > 0 && $milestone->milestone_status == litraak::MILESTONE_UNRELEASED){
		$progress .= '<p>'.litraakUtils::getProgressBar(litraakUtils::getPercent($milestone->milestone_nb_tickets, $milestone->milestone_nb_actives)).'</p>';
	}
	$progress .= '</fieldset>';
}
	
echo
	'<form action="plugin.php?p=litraak&amp;projectid='.$project_id.'&amp;milestoneid='.
	$milestone->milestone_id.'" method="post" id="milestone-form">'.
	'<p><input type="submit" name="exit" value="'.__('Retour').'" /></p>'.
	
	'<div id="entry-sidebar">'.
	
	$progress.
	
	'<p><label>'.__('Status:').
	form::combo('milestone_status',$status_combo, $milestone_status).
	'</label></p>'.
	
	'<p><label>'.(($milestone_status == litraak::MILESTONE_UNRELEASED)? __('Due on:') : __('Released on:')).
	form::field('milestone_dt',16,16,$milestone_dt,'',3).
	'</label></p>'.
	
	'<div class="lockable">'.
	'<p><label>'.__('Basename:').
	form::field('milestone_url',20,255,$milestone_url).
	'</label></p>'.
	'<p class="form-note warn">'.
	__('Warning: If you set the URL manually, it may conflict with another milestone.').
	'</p>'.
	'</div>';

if ($milestone_id)
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
		'href="plugin.php?p=litraak&attachtoid='.$milestone_id.'&amp;attachid='.$f->media_id.'&amp;remove=1">'.
		'<img src="images/check-off.png" alt="'.__('remove').'" /></a>'.
		'</li>'.
		
		'</ul>'.
		'</div>';
	}
	unset($f);
	
	if (empty($post_media)) {
		echo '<p>'.__('No attachment.').'</p>';
	}
	echo '<p><a href="plugin.php?p=litraak&attachtoid='.$milestone_id.'">'.__('Add files to this milestone').'</a></p>';
}

echo
	'</div>'.
	'<div id="entry-content"><fieldset class="constrained">'.
	
	'<p><label class="required" title="'.__('Required field').'">'.__('Name:').
	form::field('milestone_name',30,255,html::escapeHTML($milestone_name)).
	'</label></p>'.
	
	'<p class="area"><label for="milestone_desc" class="required" title="'.
	__('Required field').'">'.__('Description:').'</label> '.
	form::textarea('milestone_desc',50,8,html::escapeHTML($milestone_desc)).
	'</p>'.
	
	'<p>'.$core->formNonce().
	(($milestone->milestone_id) ? '<input type="submit" name="upd" value="'.__('save').'" /> '.
	'<input type="submit" name="dlt" value="'.__('delete').'" /> ':''.
	'<input type="submit" name="add" value="'.__('save').'" /> ').
	'</p>'.
	'</fieldset></div>'.
	'</form>';

litraakPage::helpBlock('milestone');
echo $blocks[1];
litraakPage::close();

?>