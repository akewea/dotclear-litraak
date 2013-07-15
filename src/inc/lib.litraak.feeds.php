<?php
//@@licence@@

class litraakFeeds extends dcUrlHandlers
{

	static $FEED_TYPES = array('rss2', 'atom');
	static $GLOBAL_FEED_TYPES = array('tickets', 'releases', 'projects');
	static $PROJECT_FEED_TYPES = array('tickets', 'releases');

	public static function litraakGlobalFeeds($path_items, $args)
	{
		if(count($path_items) == 3 && $path_items[0] == 'feed'){

			if(		in_array($path_items[1], self::$FEED_TYPES)
			&& in_array($path_items[2], self::$GLOBAL_FEED_TYPES)){
				switch($path_items[2]){
					case 'tickets': self::litraakTicketsFeed(null, $path_items[1]); exit;
					case 'releases': self::litraakMilestonesFeed(null, $path_items[1]); exit;
					case 'projects': self::litraakProjectsFeed($path_items[1]); exit;
				}
			}
		}

		self::p404();
		exit;
	}

	public static function litraakProjectFeeds($project, $path_items, $args)
	{
		if(count($path_items) == 4 && $path_items[1] == 'feed'){

			if(		in_array($path_items[2], self::$FEED_TYPES)
			&& in_array($path_items[3], self::$PROJECT_FEED_TYPES)){
				switch($path_items[3]){
					case 'tickets': self::litraakTicketsFeed($project, $path_items[2]); exit;
					case 'releases': self::litraakMilestonesFeed($project, $path_items[2]); exit;
				}
			}

			self::p404();
			exit;
		}
	}

	protected static function litraakProjectsFeed($type='rss2')
	{
		global $_ctx;
		global $core;

		$_ctx->nb_entry_per_page = $core->blog->settings->litraak->litraak_nb_projects_per_feed;

		self::litraakServeFeed('projects', $type);
	}

	protected static function litraakTicketsFeed($project, $type='rss2')
	{
		global $_ctx;
		global $core;

		if($project != null){
			$_ctx->posts = $project;
		}
		
		$_ctx->nb_tickets_per_page = $core->blog->settings->litraak->litraak_nb_tickets_per_feed;

		self::litraakServeFeed('tickets', $type);
	}

	protected static function litraakMilestonesFeed($project, $type='rss2')
	{
		global $_ctx;
		global $core;

		if($project != null){
			$_ctx->posts = $project;
		}
		
		$_ctx->nb_milestones_per_page = $core->blog->settings->litraak->litraak_nb_milestones_per_feed;

		self::litraakServeFeed('milestones', $type);
	}

	protected static function litraakServeFeed($tpl, $type='rss2')
	{
		global $core;

		$mime = 'application/xml';
		if($type == 'atom'){
			$mime = 'application/atom+xml';
		}

		header('X-Robots-Tag: '.context::robotsPolicy($core->blog->settings->system->robots_policy,''));
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/../default-templates');
		self::serveDocument('litraak-'.$type.'-'.$tpl.'.xml', $mime);
		exit;
	}
}
?>