<?php
//@@licence@@

class litraakPage extends dcPage
{
	
	//const SELECTED_TAB = '<div class="multi-part" id="%s" title="%s">%s</div>';
	//const LINK_TAB = '<a href="%s" id="%s" class="multi-part" title="%s">%s</a>';
	const SELECTED_TAB = '<li class="part-tabs-active"><a href="#">%s</a></li>';
	const LINK_TAB = '<li><a href="%s">%s</a></li>';
	
	public static function open($title='', $head='')
	{
		echo
		'<html>'."\n".
		"<head>\n".
		'   <title>'.$title.'</title>'."\n".
		$head.
		'	<style type="text/css">@import "index.php?pf=litraak/admin.css";</style>'.
		"</head>\n".
		'<body>'."\n";
	}
	
	public static function close()
	{		
		echo
		'<div id="litraak-footer"><p>'.
		'LitraAk | '.sprintf('<a href="plugin.php?p=litraak&amp;about">%s</a>', __('About')).''.
		'<img src="index.php?pf=litraak/img/icon-24.png" alt="LitraAk" class="litraak-logo" /></p></div>'."\n".
		
		'</body></html>';
	}
	
	public static function breadCrumb($content='')
	{		
		global $core;
		$litraak =& $GLOBALS['litraak'];
		
		echo 
		'<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.
		'<a href="'.$litraak->getAdminURL(false).'">'.__('Litraak').'</a>'.
		$content.'</h2>';
	}
	
	public static function getHomeTabs($tab='project-list')
	{
		$litraak =& $GLOBALS['litraak'];
		
		$tabs = array();
		
		$tabs['project-list'] = 	array( 'name' => __('Projects'), 'url' => $litraak->getAdminUrl(true).'&amp;tab=project-list');
		$tabs['tickets'] = 			array( 'name' => __('Tickets'), 'url' => $litraak->getAdminUrl(true).'&amp;tab=tickets');
		$tabs['config'] = 			array( 'name' => __('Configuration'), 'url' => $litraak->getAdminUrl(true).'&amp;tab=config');
		
		return self::getTabs($tabs, $tab);
	}
	
	public static function getProjectTabs($project_id, $tab='edit-project')
	{
		$litraak =& $GLOBALS['litraak'];
		
		$tabs = array();
		
		$tabs['edit-project'] = array( 'name' => __('Edit project'), 'url' => $litraak->getProjectAdminUrl($project_id, true).'&amp;tab=edit-project');
		
		if($project_id > 0)
		{
			$tabs['edit-doc'] = 	array( 'name' => __('Documentation'), 'url' => $litraak->getProjectAdminUrl($project_id, true).'&amp;tab=edit-doc');
			$tabs['milestones'] = 	array( 'name' => __('Milestones'), 'url' => $litraak->getProjectAdminUrl($project_id, true).'&amp;tab=milestones');
			$tabs['tickets'] = 		array( 'name' => __('Tickets'), 'url' => $litraak->getProjectAdminUrl($project_id, true).'&amp;tab=tickets');
			$tabs['comments'] = 	array( 'name' => __('Comments'), 'url' => $litraak->getProjectAdminUrl($project_id, true).'&amp;tab=comments');		
		}
		
		return self::getTabs($tabs, $tab);
	}
	
	private static function getTabs($tabs, $current)
	{
		$res = '<div class="part-tabs"><ul>';
		foreach($tabs as $id => $tab){
			if($current == $id)
			{
				$res .= sprintf(self::SELECTED_TAB, $tab['name']);
			}else{
				$res .= sprintf(self::LINK_TAB, $tab['url'], $tab['name']);
			}
		}
		
		$res .= '</ul></div><div>%s</div>';
		 
		return $res;
	}
	
	public static function helpBlock($name='')
	{		
		if(empty($name)){
			return parent::helpBlock('litraak');
		}else{
			return parent::helpBlock('litraak-'.$name);
		}
	}
	
}

?>