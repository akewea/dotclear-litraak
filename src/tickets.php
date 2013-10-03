<?php
//@@licence@@

if (!defined('DC_CONTEXT_ADMIN')) { return; }

require_once dirname(__FILE__).'/inc/lib.litraak.pager.php';

/* Actions
 -------------------------------------------------------- */
if (!empty($_POST['action']) && !empty($_POST['tickets']))
{
	$tickets = $_POST['tickets'];
	$action = $_POST['action'];

	foreach ($tickets as $k => $v) {
		$tickets[$k] = (integer) $v;
	}

	$params['sql'] = 'AND T.ticket_id IN('.implode(',',$tickets).') ';

	$tickets = $litraak->getTickets($params);

	try {
		if (preg_match('/^(bug|task|idea)$/',$action))
		{
			switch ($action) {
				case 'task' : $type = litraak::TICKET_TASK; break;
				case 'idea' : $type = litraak::TICKET_IDEA; break;
				default : $type = litraak::TICKET_BUG; break;
			}

			while ($tickets->fetch()) {
				$litraak->updTicketType($tickets->ticket_id,$type);
			}
				
		} else if (preg_match('/^(new|accepted|closed|rejected|deleted)$/',$action)) {
			switch ($action) {
				case 'accepted' : $status = litraak::TICKET_ACCEPTED; break;
				case 'closed' : $status = litraak::TICKET_CLOSED; break;
				case 'rejected' : $status = litraak::TICKET_REJECTED; break;
				case 'deleted' : $status = litraak::TICKET_DELETED; break;
				default : $status = litraak::TICKET_NEW; break;
			}

			while ($tickets->fetch()) {
				$litraak->updTicketStatus($tickets->ticket_id,$status);
			}
		}
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}

	if (isset($_POST['redir'])){
		http::redirect($_POST['redir']);
	}
}


/* Prepare filters
-------------------------------------------------------- */
$status_combo = litraakUtils::getTicketStatusCombo($litraak, true);
$types_combo = litraakUtils::getTicketTypesCombo($litraak);
$projects_combo = litraakUtils::getProjectsCombo($litraak);
$sortby_combo = litraakUtils::getTicketSortByCombo(true);
$order_combo = litraakUtils::getOrderCombo();

/* Get tickets
-------------------------------------------------------- */
$project = isset($_GET['ticket_project']) ?	$_GET['ticket_project'] : '';
$type = isset($_GET['ticket_type']) ?	$_GET['ticket_type'] : '';
$status = isset($_GET['ticket_status']) ?	$_GET['ticket_status'] : 'actives';
$sortby = !empty($_GET['ticket_sortby']) ?	$_GET['ticket_sortby'] : 'ticket_upddt';
$order = !empty($_GET['ticket_order']) ?		$_GET['ticket_order'] : 'desc';

$show_filters = false;

$page = !empty($_GET['page']) ? (integer) $_GET['page'] : 1;
$nb_per_page =  30;

if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0) {
	if ($nb_per_page != $_GET['nb']) {
		$show_filters = true;
	}
	$nb_per_page = (integer) $_GET['nb'];
}

$params = array();
$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);
$params['no_content'] = true;
$params['post_id'] = $project_id;

# - Project filter
if ($project !== '' && in_array($project,$projects_combo)) {
	$params['post_id'] = $project;
	$show_filters = true;
}

# - Type filter
if ($type !== '' && in_array($type,$types_combo)) {
	$params['ticket_type'] = $type;
	$show_filters = true;
}

# - Status filter
if ($status !== '') {
	if($status == 'actives'){
		$params['ticket_status'] = litraak::getActiveTicketStatus();
	}else if($status == 'wasted'){
		$params['ticket_status'] = litraak::getWasteTicketStatus();
	}else if(in_array($status,$status_combo)){
		$params['ticket_status'] = $status;
	}
}
if($status != 'actives'){
	$show_filters = true;
}

# - Sortby and order filter
if ($sortby !== '' && in_array($sortby,$sortby_combo)) {
	if ($order !== '' && in_array($order,$order_combo)) {
		$params['order'] = $sortby.' '.$order.', ticket_upddt desc';
	}
	
	if ($sortby != 'ticket_upddt' || $order != 'desc') {
		$show_filters = true;
	}
}

# Get tickets
try {
	$posts = $litraak->getTickets($params);
	$counter = $litraak->getTickets($params,true);
	$post_list = new litraakAdminTicketList($core,$posts,$counter->f(0));
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

# Actions combo box
$combo_action = array();
$combo_action[__('Type')] = array(
__('bug') => 'bug',
__('task') => 'task',
__('idea') => 'idea'
);
$combo_action[__('Status')] = array(
__('new') => 'new',
__('accepted') => 'accepted',
__('closed') => 'closed',
__('rejected') => 'rejected',
__('deleted') => 'deleted'
);

/* DISPLAY
------------------------------------------------------------------------------*/
$starting_script = litraakPage::jsPageTabs($default_tab);
	
if (!$show_filters) {
	$starting_script .= litraakPage::jsLoad('js/filter-controls.js');
}

litraakPage::open(__('Litraak'), 
	$starting_script.
	litraakPage::jsLoad('index.php?pf=litraak/js/_tickets.js'));

litraakPage::breadCrumb(' &rsaquo; '.__('All tickets'));

$tabs = litraakPage::getHomeTabs('tickets');
$blocks = explode('%s', $tabs, 2);
echo $blocks[0];

if (!$core->error->flag())
{
	if (!$show_filters) {
		echo '<p><a id="filter-control" class="form-control" href="#">'.
		__('Filters').'</a></p>';
	}
	
	echo
	'<form action="plugin.php" method="get" id="filters-form">'.
	'<fieldset><legend>'.__('Filters').'</legend>'.
	'<div class="three-cols">'.
	
	'<div class="col">'.
	'<label>'.__('Project:').
	form::combo('ticket_project',$projects_combo,$project).'</label> '.
	'<label>'.__('Type:').
	form::combo('ticket_type',$types_combo,$type).'</label> '.
	'</div>'.
	
	'<div class="col">'.
	'<label>'.__('Status:').
	form::combo('ticket_status',$status_combo,$status).'</label> '.
	'</div>'.
	
	'<div class="col">'.
	'<p><label for="sortby">'.__('Order by:').'</label> '.
	form::combo('ticket_sortby',$sortby_combo,$sortby).' '.
	form::combo('ticket_order',$order_combo,$order).'</p>'.
	'<p><label class="classic">'.	form::field('nb',3,3,$nb_per_page).' '.
	__('Tickets per page').'</label> '.
	'<input type="submit" value="'.__('filter').'" /></p>'.
	'</div>'.
	
	'</div>'.
	'<br class="clear" />'. //Opera sucks
	'</fieldset>'.
	form::hidden(array('p'),'litraak').
	form::hidden(array('tab'),'tickets').
	'</form>';
	
	# Show posts
	$post_list->display($page,$nb_per_page,true, 
	'<form action="plugin.php?p=litraak&tab=tickets" method="post" id="form-tickets">'.

	'%s'.

	'<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.

	'<p class="col right">'.__('Selected tickets action:').' '.
	form::combo('action',$combo_action).
	'<input type="submit" value="'.__('ok').'" /></p>'.
	form::hidden(array('redir'),$_SERVER['REQUEST_URI']).
	$core->formNonce().
	'</div>'.
	'</form>'
	);
}

litraakPage::helpBlock('tickets');
echo $blocks[1];
litraakPage::close();

?>