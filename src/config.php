<?php
//@@licence@@

if (!defined('DC_CONTEXT_ADMIN')) { return; }

require_once dirname(__FILE__).'/inc/lib.litraak.pager.php';

$err = false;

$litraak_basename_url = $core->blog->settings->litraak->litraak_basename_url;
$litraak_add_css = $core->blog->settings->litraak->litraak_add_css;
$litraak_nb_projects_per_feed = $core->blog->settings->litraak->litraak_nb_projects_per_feed;
$litraak_nb_milestones_per_feed = $core->blog->settings->litraak->litraak_nb_milestones_per_feed;
$litraak_nb_tickets_per_feed = $core->blog->settings->litraak->litraak_nb_tickets_per_feed;

/* ACTION
------------------------------------------------------------------------------*/

if(!empty($_POST['upd'])){
	try {
		$litraak_basename_url = $_POST['litraak_basename_url'];
		$litraak_add_css = (boolean) $_POST['litraak_add_css'];
		$litraak_nb_projects_per_feed = (integer) $_POST['litraak_nb_projects_per_feed'];
		$litraak_nb_milestones_per_feed = (integer) $_POST['litraak_nb_milestones_per_feed'];
		$litraak_nb_tickets_per_feed = (integer) $_POST['litraak_nb_tickets_per_feed'];
		
		if ($litraak_basename_url == null || $litraak_basename_url == '') {
			throw new Exception(__('Empty basename URL.'));
		}		
		if ($litraak_nb_projects_per_feed == null || $litraak_nb_projects_per_feed < 1) {
			throw new Exception(__('Feed size must be of 1 at least.'));
		}
		if ($litraak_nb_milestones_per_feed == null || $litraak_nb_milestones_per_feed < 1) {
			throw new Exception(__('Feed size must be of 1 at least.'));
		}
		if ($litraak_nb_tickets_per_feed == null || $litraak_nb_tickets_per_feed < 1) {
			throw new Exception(__('Feed size must be of 1 at least.'));
		}
		
		if(!empty($_POST['upd'])){
			$core->blog->settings->addNameSpace('litraak');
			$core->blog->settings->litraak->put('litraak_basename_url', $litraak_basename_url, 'string');
			$core->blog->settings->litraak->put('litraak_add_css', $litraak_add_css, 'boolean');
			$core->blog->settings->litraak->put('litraak_nb_projects_per_feed', $litraak_nb_projects_per_feed, 'integer');
			$core->blog->settings->litraak->put('litraak_nb_milestones_per_feed', $litraak_nb_milestones_per_feed, 'integer');
			$core->blog->settings->litraak->put('litraak_nb_tickets_per_feed', $litraak_nb_tickets_per_feed, 'integer');
			
			http::redirect("plugin.php?p=litraak&tab=config&upd=1");
		}
		
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
		$err = true;
	}
}

/* DISPLAY
------------------------------------------------------------------------------*/
$starting_script = litraakPage::jsPageTabs($default_tab);

litraakPage::open(__('Litraak'), 
	$starting_script);

if (!$err && !empty($_REQUEST['upd'])) {
	echo '<p class="message">'.__('Configuration has been successfully updated.').'</p>';
}
	
litraakPage::breadCrumb(' &rsaquo; '.__('Configuration'));

$tabs = litraakPage::getHomeTabs('config');
$blocks = explode('%s', $tabs, 2);
echo $blocks[0];


echo
'<form action="plugin.php?p=litraak&tab=config" method="post" id="config-form">'.

'<fieldset><legend>'.__('Global').'</legend>'.
'<div class="two-cols"><div class="col">'.
'<p><label class="">'.__('Basename URL').
form::field('litraak_basename_url',50, '255',$litraak_basename_url).
'</label></p>'.
'</div><div class="col">'.
'<p><label class="classic">'.
form::checkbox('litraak_add_css','1',$litraak_add_css).
__('Include default CSS').'</label></p>'.
'</div></div>'.
'</fieldset>'.

'<fieldset><legend>'.__('Feeds').'</legend>'.
'<div class="three-cols"><div class="col">'.
'<p><label class="">'.__('Projects per feed').
form::field('litraak_nb_projects_per_feed',10, '11',$litraak_nb_projects_per_feed).
'</label></p>'.
'</div><div class="col">'.
'<p><label class="">'.__('Milestones per feed').
form::field('litraak_nb_milestones_per_feed',10, '11',$litraak_nb_milestones_per_feed).
'</label></p>'.
'</div><div class="col">'.
'<p><label class="">'.__('Tickets per feed').
form::field('litraak_nb_tickets_per_feed',10, '11',$litraak_nb_tickets_per_feed).
'</label></p>'.
'</div></div>'.
'</fieldset>'.

'<p>'.$core->formNonce().
'<input type="submit" name="upd" value="'.__('save').'" />'.
'</p>'.

'</form>';


litraakPage::helpBlock('config');
echo $blocks[1];
litraakPage::close();

?>