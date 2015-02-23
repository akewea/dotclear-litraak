<?php
//@@licence@@

if (!defined('DC_CONTEXT_ADMIN')) { return; }

require_once dirname(__FILE__).'/inc/lib.litraak.pager.php';

/* Actions
-------------------------------------------------------- */
if (!empty($_POST['action']) && !empty($_POST['entries']))
{
	$entries = $_POST['entries'];
	$action = $_POST['action'];
	
	foreach ($entries as $k => $v) {
		$entries[$k] = (integer) $v;
	}
	
	$params['sql'] = 'AND P.post_id IN('.implode(',',$entries).') ';
	
	$posts = $litraak->getProjects($params);
	
	if (preg_match('/^(publish|unpublish)$/',$action))
	{
		switch ($action) {
			case 'unpublish' : $status = litraak::PROJECT_PENDING; break;
			default : $status = litraak::PROJECT_PUBLISHED; break;
		}
		
		try
		{
			while ($posts->fetch()) {
				$core->blog->updPostStatus($posts->post_id,$status);
			}
			
			if (isset($_POST['redir'])){
				http::redirect($_POST['redir']);
			}
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}
}

/* Prepare filters
-------------------------------------------------------- */
$status_combo = array(
'-' => ''
);
foreach ($litraak->getAllProjectStatus() as $k => $v) {
	$status_combo[$v] = (string) $k;
}

$phases_combo = array(
'-' => ''
);
foreach ($litraak->getAllProjectPhases() as $k => $v) {
	$phases_combo[$v] = (string) $k;
}

$sortby_combo = array(
__('Date') => 'post_dt',
__('Name') => 'post_title',
__('Status') => 'post_status',
__('Phase') => 'project_phase',
);

$order_combo = array(
__('Descending') => 'desc',
__('Ascending') => 'asc'
);

# Actions combo box
$combo_action = array();
if ($core->auth->check('publish,contentadmin',$core->blog->id))
{
	$combo_action[__('Status')] = array(
		__('Publish') => 'publish',
		__('Unpublish') => 'unpublish'
	);
}

/* Get posts
-------------------------------------------------------- */
$status = isset($_GET['status']) ?	$_GET['status'] : '';
$phase = isset($_GET['phase']) ?	$_GET['phase'] : '';
$sortby = !empty($_GET['sortby']) ?	$_GET['sortby'] : 'post_title';
$order = !empty($_GET['order']) ?		$_GET['order'] : 'asc';

$show_filters = false;

$page = !empty($_GET['page']) ? (integer) $_GET['page'] : 1;
$nb_per_page =  30;

if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0) {
	if ($nb_per_page != $_GET['nb']) {
		$show_filters = true;
	}
	$nb_per_page = (integer) $_GET['nb'];
}

$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);
$params['no_content'] = true;

# - Status filter
if ($status !== '' && in_array($status,$status_combo)) {
	$params['post_status'] = $status;
	$show_filters = true;
}

# - Phase filter
if ($phase !== '' && in_array($phase,$phases_combo)) {
	$params['project_phase'] = $phase;
	$show_filters = true;
}

# - Sortby and order filter
if ($sortby !== '' && in_array($sortby,$sortby_combo)) {
	if ($order !== '' && in_array($order,$order_combo)) {
		$params['order'] = $sortby.' '.$order;
	}
	
	if ($sortby != 'post_title' || $order != 'asc') {
		$show_filters = true;
	}
}

# Get posts
try {
	$posts = $litraak->getProjects($params);
	$counter = $litraak->getProjects($params,true);
	$post_list = new litraakAdminProjectList($core,$posts,$counter->f(0));
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

/* DISPLAY
-------------------------------------------------------- */
$starting_script = '';
if (!$show_filters) {
	$starting_script .= litraakPage::jsLoad('js/filter-controls.js');
	$starting_script .=
	'<script type="text/javascript">'."\n".
	"//<![CDATA["."\n".
	dcPage::jsVar('dotclear.msg.show_filters', $show_filters ? 'true':'false')."\n".
	dcPage::jsVar('dotclear.msg.filter_posts_list',__('Show filters and display options'))."\n".
	dcPage::jsVar('dotclear.msg.cancel_the_filter',__('Cancel filters and display options'))."\n".
	"//]]>".
	"</script>";
}

litraakPage::open(__('Litraak'), $starting_script.
								litraakPage::jsLoad('index.php?pf=litraak/js/_projects.js')
								);

litraakPage::breadCrumb(' &rsaquo; '.__('Projects').' - '.
		'<a class="button" href="'.$litraak->getProjectAdminUrl('', true).'">'.__('New project').'</a>');

$tabs = litraakPage::getHomeTabs('project-list');
$blocks = explode('%s', $tabs, 2);
echo $blocks[0];

if (!$core->error->flag())
{
	
	echo
	'<form action="plugin.php" method="get" id="filters-form">'.
	'<fieldset><legend>'.__('Filters').'</legend>'.
	'<div class="three-cols">'.
	
	'<div class="col">'.
	'<label>'.__('Status:').
	form::combo('status',$status_combo,$status).'</label> '.
	'</div>'.
	
	'<div class="col">'.
	'<label>'.__('Phase:').
	form::combo('phase',$phases_combo,$phase).'</label> '.
	'</div>'.
	
	'<div class="col">'.
	'<p><label for="sortby">'.__('Order by:').'</label>'.
	form::combo('sortby',$sortby_combo,$sortby).' '.
	form::combo('order',$order_combo,$order).'</p>'.
	'<p><label class="classic">'.	form::field('nb',3,3,$nb_per_page).' '.
	__('Projects per page').'</label> '.
	'<input type="submit" value="'.__('filter').'" /></p>'.
	'</div>'.
	
	'</div>'.
	'<br class="clear" />'. //Opera sucks
	'</fieldset>'.
	form::hidden(array('p'),'litraak').
	'</form>';
	
	# Show posts
	$post_list->display($page,$nb_per_page,
	'<form action="plugin.php?p=litraak" method="post" id="form-entries">'.
	
	'%s'.
	
	'<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.
	
	'<p class="col right">'.__('Selected projects action:').' '.
	form::combo('action',$combo_action).
	'<input type="submit" value="'.__('ok').'" /></p>'.
	form::hidden(array('redir'),$_SERVER['REQUEST_URI']).
	$core->formNonce().
	'</div>'.
	'</form>'
	);
}

litraakPage::helpBlock('projects');
echo $blocks[1];
litraakPage::close();

?>