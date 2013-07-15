<?php
//@@licence@@

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('litraakWidgets','initWidgets'));
$core->addBehavior('initDefaultWidgets',array('litraakWidgets','initDefaultWidgets'));

class litraakWidgets
{
	public static function initWidgets($w)
	{
		global $core;
		
		// Widget des projets
		$w->create('litraak',__('LitraAk project list'),array('litraakPublic','litraakProjectsWidget'));
		$w->litraak->setting('title', __('Title:'), __('My projects'));
		$w->litraak->setting('homeonly', __('Home page only'), 0,'check');
	}
	
	public static function initDefaultWidgets($w,$d)
	{
		// NOP
	}
}




?>