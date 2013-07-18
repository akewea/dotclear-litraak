<?php
//@@licence@@

require_once dirname(__FILE__).'/inc/lib.litraak.pager.php';

$err = false;

// Champs de la page
$project_id = isset($_REQUEST['projectid']) ?	(integer) $_REQUEST['projectid'] : 0;
$ticket_id = $_REQUEST['ticketid'] > 0 ?	(integer) $_REQUEST['ticketid'] : -1;

if(!empty($_POST['exit'])){
	http::redirect('plugin.php?p=litraak'.(($project_id > 0) ? '&projectid='.$project_id : '').'&tab=tickets',false);
}

if(!empty($_GET['delete']) && ((integer) $_GET['delete']) > 0){
	try{
		$litraak->delTicketChange((integer) $_GET['delete']);
		http::redirect('plugin.php?p=litraak'.(($project_id > 0) ? '&projectid='.$project_id : '').'&ticketid='.$ticket_id.'&dlt=1#history',false);
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

if(!empty($_POST['upd']) || !empty($_POST['add'])){
try
	{
		$rs = $litraak->getProjects(array('post_id' => $project_id, 'post_type' => litraak::POST_TYPE));
		
		if ($rs->isEmpty()) {
			throw new Exception(__('Project does not exist.'));
		}
		
		if(!empty($_POST['milestone_id'])){
			$rs = $litraak->getMilestones(array('post_id' => $project_id, 'milestone_id' => (integer) $_POST['milestone_id'], 'post_type' => litraak::POST_TYPE));
			
			if ($rs->isEmpty()) {
				throw new Exception(__('Milestone does not exist.'));
			}
		}
		
		$cur = $core->con->openCursor($core->prefix.'litraak_ticket');
		
		$cur->ticket_status = (integer) $_POST['ticket_status'];
		$cur->ticket_type = (integer) $_POST['ticket_type'];
		$cur->ticket_open_comment = (integer) $_POST['ticket_open_comment'];
		$cur->ticket_desc = $core->HTMLfilter($_POST['ticket_desc']);
		$cur->ticket_title = html::clean($_POST['ticket_title']);
		$cur->milestone_id = (integer) $_POST['milestone_id'];
		
		if(!empty($_POST['add'])){
			$cur->ticket_author = html::escapeHTML($core->auth->getInfo('user_cn'));
			$cur->ticket_email = html::clean(html::escapeHTML($core->auth->getInfo('user_email')));
		}
		
		if(!empty($_POST['upd'])){
			$change_comment_public = !empty($_POST['ticket_comment_public']);
			$change_comment = $core->HTMLfilter($_POST['ticket_comment']);
		
			if($change_comment_public || $change_comment == ''){
				$litraak->updTicket($ticket_id, $cur, $change_comment);
			}else{
				$litraak->updTicket($ticket_id, $cur, '');
				$litraak->addCommentChange($ticket_id, $change_comment, 0);
			}
		}else{	
			$cur->post_id = $project_id;		
			$ticket_id = $litraak->addTicket($cur);
		}
		
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
		$err = true;
	}
}

if(!empty($_POST['dlt'])){
	try {
		$litraak->delTicket($ticket_id);
		http::redirect('plugin.php?p=litraak'.(($project_id > 0) ? '&projectid='.$project_id : '').'&tab=tickets',false);
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

$ticket = $litraak->getTickets(array('ticket_id' => $ticket_id, 'post_id' => $project_id));

if(!$ticket->isEmpty()){
	$project_title = $ticket->post_title;
}else{
	$project = $litraak->getProjects(array('post_id' => $project_id, 'no_content' => true));
	$project->fetch();
	$project_title = $project->post_title;
}

// Champs de saisie
$ticket_type = ($err)? $_POST['ticket_type'] : $ticket->ticket_type;
$milestone_id = ($err)? $_POST['milestone_id'] : $ticket->milestone_id;
$ticket_status = ($err)? $_POST['ticket_status'] : $ticket->ticket_status;
$ticket_email = ($err)? $_POST['ticket_email'] : $ticket->ticket_email;
$ticket_author = ($err)? $_POST['ticket_author'] : $ticket->ticket_author;
$ticket_title = ($err)? $_POST['ticket_title'] : $ticket->ticket_title;
$ticket_desc = ($err)? $_POST['ticket_desc'] : $ticket->ticket_desc;
$ticket_comment = ($err)? $_POST['ticket_comment'] : '';
$ticket_comment_public = ($err)? $_POST['ticket_comment_public'] : 1;
$ticket_open_comment = ($err)? $_POST['ticket_open_comment'] : $ticket->isEmpty() ? 1 : $ticket->ticket_open_comment;

$milestones_combo = litraakUtils::getMilestonesCombo($litraak, $project_id);
$status_combo = litraakUtils::getTicketStatusCombo($litraak);
$types_combo = litraakUtils::getTicketTypesCombo($litraak);

/* Display
------------------------------------------------------------------------------*/

litraakPage::open(__('Litraak'),
	litraakPage::jsPageTabs($default_tab).
	litraakPage::jsConfirmClose('ticket-form').
	litraakPage::jsToolBar().
	litraakPage::jsLoad('index.php?pf=litraak/js/_ticket.js')
);

if (!$err && !empty($_POST['upd'])) {
	echo '<p class="message">'.__('Ticket has been successfully updated.').'</p>';
}
if (!$err && !empty($_POST['add'])) {
	echo '<p class="message">'.__('Ticket has been successfully created.').'</p>';
}
if (!empty($_GET['dlt'])) {
	echo '<p class="message">'.__('Ticket change has been successfully deleted.').'</p>';
}

if($project_id > 0){
	litraakPage::breadCrumb(' &rsaquo; <a href="'.$litraak->getProjectAdminUrl($project_id, true).
		'">'.$project_title.'</a> &rsaquo; <a href="'.$litraak->getProjectAdminUrl($project_id, true).
		'&amp;tab=tickets">'.__('Tickets').'</a> &rsaquo; '.
		(($ticket->ticket_id) ? '#'.$ticket->ticket_id.' '.$ticket->ticket_title.' - '.
		'<a class="button" href="'.$litraak->getTicketPublicUrl($ticket->post_url, $ticket_id, true).'">'.
		__('View ticket').'</a>':__('New ticket')));
	
	$tabs = litraakPage::getProjectTabs($project_id, 'tickets');
}else{
	litraakPage::breadCrumb(' &rsaquo; <a href="'.$litraak->getAdminUrl(true).
		'&amp;tab=tickets">'.__('Tickets').'</a> &rsaquo; '.
		'#'.$ticket->ticket_id.' '.$ticket->ticket_title.' ('.
		$ticket->post_title.') - '.
		'<a class="button" href="'.$litraak->getTicketPublicUrl($ticket->post_url, $ticket_id, true).'">'.
		__('View ticket').'</a>');
	
	$tabs = litraakPage::getHomeTabs('tickets');
}
$blocks = explode('%s', $tabs, 2);
echo $blocks[0];

$milestone_links = '';
if($ticket->milestone_id > 0){
	$milestone_url = 'plugin.php?p=litraak&projectid='.$project_id.'&tab=milestones&milestoneid='.$ticket->milestone_id;
	$tickets_url = 'plugin.php?p=litraak&projectid='.$project_id.'&tab=tickets&ticket_milestone='.$ticket->milestone_id;
	$milestone_links .= 
		'<p><a href="'.$milestone_url.'">'.__('This milestone details').' &raquo;</a></p>'.
		'<p><a href="'.$tickets_url.'">'.__('This milestone other tickets').' &raquo;</a></p>';
}

echo
	'<form action="plugin.php?p=litraak" method="post" id="ticket-form">'.
	'<p><input type="submit" name="exit" value="'.__('Back').'" /></p>'.
	
	'<div id="entry-sidebar">'.

	'<p><label class="required" title="'.__('Required field').'">'.__('Type:').
	form::combo('ticket_type',$types_combo, $ticket_type).
	'</label></p>'.
	
	'<p><label class="required" title="'.__('Required field').'">'.__('Status:').
	form::combo('ticket_status',$status_combo, $ticket_status).
	'</label></p>'.
	
	'<p><label>'.__('Milestone:').
	form::combo('milestone_id',$milestones_combo, $milestone_id).
	'</label></p>'.
	$milestone_links.
	
	'<p><label class="classic">'.form::checkbox('ticket_open_comment',1,$ticket_open_comment,'',3).' '.
	__('Accept comments').'</label></p>';
	
if(!$ticket->isEmpty()){
	echo
		'<p><strong>'.__('Date:').'</strong> '.
		dt::dt2str(__('%Y-%m-%d %H:%M'),$ticket->ticket_upddt).
		'</p>';
}

echo
	'<p><strong>'.__('Name:').'</strong> '.
	(($ticket_author)?$ticket_author:html::escapeHTML($core->auth->getInfo('user_cn'))).
	'</p>'.
	
	'<p><strong>'.__('Email:').'</strong> '.
	(($ticket_email)?$ticket_email:html::escapeHTML($core->auth->getInfo('user_email'))).
	'</p>'.
	
	'</div>'.
	
	'<div id="entry-content"><fieldset class="constrained">'.
	
	'<p><label class="required" title="'.__('Required field').'">'.__('Title:').
	form::field('ticket_title',50,255,$ticket_title).
	'</label></p>'.
	
	'<p class="area"><label for="ticket_desc" class="required" title="'.
	__('Required field').'">'.__('Description:').'</label> '.
	form::textarea('ticket_desc',50,8,html::escapeHTML($ticket_desc)).
	'</p>';
	
if($ticket_id > 0){
	echo
	'<p class="area"><label for="ticket_comment">'.__('Add an update comment:').'</label> '.
	form::textarea('ticket_comment',50,5,html::escapeHTML($ticket_comment)).
	'</p>'.
	'<p><label class="classic">'.form::checkbox('ticket_comment_public',1,$ticket_comment_public,'',3).' '.
	__('Show this comment on public pages').'</label></p>';
}

echo
	'<p>'.$core->formNonce().
	(($ticket->ticket_id)?'<input type="submit" name="upd" value="'.__('save').
	'" /> <input type="submit" name="dlt" value="'.__('delete').'" />':'<input type="submit" name="add" value="'.__('save').'" />').
	'</p>'.
	
	form::hidden(array('ticketid'), $ticket->ticket_id).
	(($project_id > 0) ? form::hidden(array('projectid'), $project_id):'').
	'</fieldset>'.
	'</div></form>';
	

if($ticket_id > 0){

	echo
		'<hr /><h3>'.__('Ticket changes').'</h3>';
	
	// On récupère les changements
	$params = array('ticket_id' => $ticket_id);
	$changes = $litraak->getTicketChanges($params);
	$counter = $litraak->getTicketChanges($params,true);
	
	if(!$changes->isEmpty()){
		$change_list = new litraakAdminTicketChangeList($core,$changes, 0);
		$change_list->display(0,0,'');
	}else{
		echo '<p><i>'.__('No change for this ticket.').'</i></p>';
	}
}

litraakPage::helpBlock('ticket');
echo $blocks[1];
litraakPage::close();

?>