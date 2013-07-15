<?php
//@@licence@@

class litraakPublic extends dcUrlHandlers
{

	// ##################### WIDGET ##########################

	public static function litraakProjectsWidget($w) {

		global $core;
		global $litraak;

		//Affichage page d'accueil seulement
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}

		$rs = $litraak->getProjects(array('no_content' => true));

		if ($rs->isEmpty()) {
			return;
		}

		$res =
		'<div class="litraak-projects-widget">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>';

		while ($rs->fetch()) {
			$res .= '<li><a href="'.$rs->getURL().'">'.
			html::escapeHTML($rs->post_title).'</a></li>';
		}

		$res .= '</ul><p class="more"><a href="'.$litraak->getPublicURL().'">'.
		__('More').'</a></p></div>';

		return $res;
	}


	// ##################### HEAD ##########################

	public static function publicHeadContent($core)
	{
		global $core;

		if($core->blog->settings->litraak->litraak_add_css){
			$url = $core->blog->getQmarkURL().'pf=litraak';
			echo
			'<style type="text/css">'."\n".
			'@import url('.$url.'/style.css);'."\n".
			"</style>\n";
		}
	}

	// ##################### URLs ##########################

	public static function litraakUrl($args)
	{
		global $_ctx;
		global $core;
		$litraak =& $GLOBALS['litraak'];

		$path = $args;
		$get_args = null;
		if(strpos($path, "?") > -1){
			list($path, $get_args) = explode("?", $path, 2);
		}
		if(substr($path, 0, 1) == "/"){
			$path = substr($path, 1);
		}
		if(substr($path, -1, 1) == "/"){
			$path = substr($path, 0, -1);
		}
		$path_items = explode("/", $path);

		if(count($path_items) == 1 && $path_items[0] == ''){
			// Page d'accueil : liste de produits
			self::projectsUrl($get_args);
			return;
		}else if(count($path_items) == 3 && $path_items[0] == 'feed'){
			// feeds génériques
			litraakFeeds::litraakGlobalFeeds($path_items, $get_args);
			return;
		}else{
			// Page produit : on vérifie l'existence du produit
			$project = $litraak->getProjects(array('post_url' => $path_items[0]));
			if($project->isEmpty()){
				self::p404();
				return;
			}
				
			if(count($path_items) == 1){
				self::projectUrl($project, $get_args);
				return;
			}else{
				switch($path_items[1]){
					case 'roadmap': self::roadmapUrl($project, $get_args); return;
					case 'documentation':
					case 'doc': self::documentationUrl($project, $get_args); return;
					case 'download': self::downloadsUrl($project, $get_args); return;
					case 'ticket': self::ticketUrl($project, $path_items, $get_args); return;
					case 'feed': litraakFeeds::litraakProjectFeeds($project, $path_items, $get_args); return;
					case 'update': self::updateUrl($project, $get_args); return;
				}

				$milestone = $litraak->getMilestones(array('post_id' => $project->post_id, 'milestone_url' => $path_items[1]));
				if(!$milestone->isEmpty()){
					self::milestoneUrl($project, $milestone, $get_args);
					return;
				}
			}
		}

		self::p404();
		return;
	}

	public static function projectsUrl($args)
	{
		global $_ctx;
		global $core;
		$litraak =& $GLOBALS['litraak'];

		$_ctx->posts = $litraak->getProjects();

		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/../default-templates');
		self::serveDocument('litraak.html');
		return;
	}

	public static function projectUrl($project, $args)
	{
		global $_ctx;
		global $core;
		$litraak =& $GLOBALS['litraak'];

		$_ctx->posts = $project;

		$_ctx->comment_preview = new ArrayObject();
		$_ctx->comment_preview['content'] = '';
		$_ctx->comment_preview['rawcontent'] = '';
		$_ctx->comment_preview['name'] = '';
		$_ctx->comment_preview['mail'] = '';
		$_ctx->comment_preview['site'] = '';
		$_ctx->comment_preview['preview'] = false;
		$_ctx->comment_preview['remember'] = false;

		$post_comment =
		isset($_POST['c_name']) && isset($_POST['c_mail']) &&
		isset($_POST['c_site']) && isset($_POST['c_content']) &&
		$_ctx->posts->commentsActive();

		# Posting a comment
		if ($post_comment)
		{
			# Spam trap
			if (!empty($_POST['f_mail'])) {
				http::head(412,'Precondition Failed');
				header('Content-Type: text/plain');
				echo "So Long, and Thanks For All the Fish";
				return;
			}
				
			$name = $_POST['c_name'];
			$mail = $_POST['c_mail'];
			$site = $_POST['c_site'];
			$content = $_POST['c_content'];
			$preview = !empty($_POST['preview']);
				
			if ($content != '')
			{
				if ($core->blog->settings->system->wiki_comments) {
					$core->initWikiComment();
				} else {
					$core->initWikiSimpleComment();
				}
				$content = $core->wikiTransform($content);
				$content = $core->HTMLfilter($content);
			}
				
			$_ctx->comment_preview['content'] = $content;
			$_ctx->comment_preview['rawcontent'] = $_POST['c_content'];
			$_ctx->comment_preview['name'] = $name;
			$_ctx->comment_preview['mail'] = $mail;
			$_ctx->comment_preview['site'] = $site;
				
			if ($preview)
			{
				# --BEHAVIOR-- publicBeforeCommentPreview
				$core->callBehavior('publicBeforeCommentPreview',$_ctx->comment_preview);

				$_ctx->comment_preview['preview'] = true;
			}
			else
			{
				# Post the comment
				$cur = $core->con->openCursor($core->prefix.'comment');
				$cur->comment_author = $name;
				$cur->comment_site = html::clean($site);
				$cur->comment_email = html::clean($mail);
				$cur->comment_content = $content;
				$cur->post_id = $_ctx->posts->post_id;
				$cur->comment_status = $core->blog->settings->system->comments_pub ? 1 : -1;
				$cur->comment_ip = http::realIP();

				$redir = $_ctx->posts->getURL();
				$redir .= strpos($redir,'?') !== false ? '&' : '?';

				try
				{
					if (!text::isEmail($cur->comment_email)) {
						throw new Exception(__('You must provide a valid email address.'));
					}

					# --BEHAVIOR-- publicBeforeCommentCreate
					$core->callBehavior('publicBeforeCommentCreate',$cur);
					if ($cur->post_id) {
						$comment_id = $core->blog->addComment($cur);
							
						# --BEHAVIOR-- publicAfterCommentCreate
						$core->callBehavior('publicAfterCommentCreate',$cur,$comment_id);
					}
						
					if ($cur->comment_status == 1) {
						$redir_arg = 'pub=1';
					} else {
						$redir_arg = 'pub=0';
					}
						
					header('Location: '.$redir.$redir_arg);
					return;
				}
				catch (Exception $e)
				{
					$_ctx->form_error = $e->getMessage();
					$_ctx->form_error;
				}
			}
		}

		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/../default-templates');
		self::serveDocument('litraak-project.html');
		return;
	}

	public static function documentationUrl($project, $args)
	{
		global $_ctx;
		global $core;
		$litraak =& $GLOBALS['litraak'];

		$_ctx->posts = $project;

		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/../default-templates');
		self::serveDocument('litraak-doc.html');
		return;
	}

	public static function downloadsUrl($project, $args)
	{
		global $_ctx;
		global $core;
		$litraak =& $GLOBALS['litraak'];

		$_ctx->posts = $project;

		$_ctx->milestone_status = litraak::MILESTONE_RELEASED;

		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/../default-templates');
		self::serveDocument('litraak-download.html');
		return;
	}

	public static function roadmapUrl($project, $args)
	{
		global $_ctx;
		global $core;
		$litraak =& $GLOBALS['litraak'];

		$_ctx->posts = $project;

		$_ctx->milestone_status = litraak::MILESTONE_UNRELEASED;

		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/../default-templates');
		self::serveDocument('litraak-roadmap.html');
		return;
	}
	
	public static function updateUrl($project, $args)
	{
		global $_ctx;
		global $core;
		global $litraak;
		
		// TODO Vérifier la présence du template "update".
		if(strlen($project->project_update_template) < 1){
			self::p404();
		}

		$_ctx->posts = $project;

		$_ctx->nb_milestones_per_page = $core->blog->settings->litraak->litraak_nb_milestones_per_feed;
		
		// Type de flux.
		$mime = 'application/xml';
		header('X-Robots-Tag: '.context::robotsPolicy($core->blog->settings->system->robots_policy,''));
		
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/../default-templates');
		self::serveDocument($project->project_update_template, $mime);
		return;
	}

	public static function ticketUrl($project, $path_items, $args)
	{
		global $_ctx;
		global $core;
		$litraak =& $GLOBALS['litraak'];

		$_ctx->posts = $project;

		if(count($path_items) > 2){
			$ticket = $litraak->getTickets(array('post_id' => $project->post_id, 'ticket_id' => $path_items[2]));
			if($ticket->isEmpty()){
				self::p404();
				return;
			}
			$_ctx->tickets = $ticket;
		}else if(!$project->ticketsActive()){
			self::p404();
			return;
		}

		$_ctx->ticket_preview = new ArrayObject();
		$_ctx->ticket_preview['desc'] = '';
		$_ctx->ticket_preview['rawdesc'] = '';
		$_ctx->ticket_preview['name'] = '';
		$_ctx->ticket_preview['mail'] = '';
		$_ctx->ticket_preview['title'] = '';
		$_ctx->ticket_preview['preview'] = false;
		$_ctx->ticket_preview['remember'] = false;

		$post_ticket =
		isset($_POST['t_name']) && isset($_POST['t_mail']) &&
		isset($_POST['t_title']) && isset($_POST['t_desc']);
			
		# Posting a ticket
		if ($post_ticket)
		{
			# Spam trap
			if (!empty($_POST['f_mail'])) {
				http::head(412,'Precondition Failed');
				header('Content-Type: text/plain');
				echo "So Long, and Thanks For All the Fish";
				return;
			}
				
			$name = $_POST['t_name'];
			$mail = $_POST['t_mail'];
			$title = $_POST['t_title'];
			$desc = $_POST['t_desc'];
			$preview = !empty($_POST['preview']);
				
			if ($desc != '')
			{
				if ($core->blog->settings->system->wiki_comments) {
					$core->initWikiComment();
				} else {
					$core->initWikiSimpleComment();
				}
				$desc = $core->wikiTransform($desc);
				$desc = $core->HTMLfilter($desc);
			}
				
			$_ctx->ticket_preview['desc'] = $desc;
			$_ctx->ticket_preview['rawdesc'] = $_POST['t_desc'];
			$_ctx->ticket_preview['name'] = $name;
			$_ctx->ticket_preview['mail'] = $mail;
			$_ctx->ticket_preview['title'] = $title;
				
			if ($preview)
			{
				$_ctx->ticket_preview['preview'] = true;
			}
			else
			{
				# Post the ticket
				$cur = $core->con->openCursor($core->prefix.'litraak_ticket');
				$cur->ticket_author = $name;
				$cur->ticket_title = html::clean($title);
				$cur->ticket_email = html::clean($mail);
				$cur->ticket_desc = $desc;
				$cur->post_id = $_ctx->posts->post_id;

				try
				{
					if (!text::isEmail($cur->ticket_email)) {
						throw new Exception(__('You must provide a valid email address.'));
					}

					if ($cur->post_id) {
						$ticket_id = $litraak->addTicket($cur);
					}
						
					header('Location: '.$litraak->getTicketPublicUrl($project->post_url, $ticket_id));
					return;
				}
				catch (Exception $e)
				{
					$_ctx->form_error = $e->getMessage();
					$_ctx->form_error;
				}
			}
		}

		$_ctx->ticket_change_preview = new ArrayObject();
		$_ctx->ticket_change_preview['desc'] = '';
		$_ctx->ticket_change_preview['rawdesc'] = '';
		$_ctx->ticket_change_preview['name'] = '';
		$_ctx->ticket_change_preview['mail'] = '';
		$_ctx->ticket_change_preview['preview'] = false;
		$_ctx->ticket_change_preview['remember'] = false;

		$post_change =
		isset($_POST['c_name']) && isset($_POST['c_mail']) && isset($_POST['c_desc']);
			
		# Posting a comment
		if ($post_change)
		{
			# Spam trap
			if (!empty($_POST['f_mail'])) {
				http::head(412,'Precondition Failed');
				header('Content-Type: text/plain');
				echo "So Long, and Thanks For All the Fish";
				return;
			}
				
			$name = $_POST['c_name'];
			$mail = $_POST['c_mail'];
			$desc = $_POST['c_desc'];
			$preview = !empty($_POST['preview']);
				
			if ($desc != '')
			{
				if ($core->blog->settings->system->wiki_comments) {
					$core->initWikiComment();
				} else {
					$core->initWikiSimpleComment();
				}
				$desc = $core->wikiTransform($desc);
				$desc = $core->HTMLfilter($desc);
			}
				
			$_ctx->ticket_change_preview['desc'] = $desc;
			$_ctx->ticket_change_preview['rawdesc'] = $_POST['c_desc'];
			$_ctx->ticket_change_preview['name'] = $name;
			$_ctx->ticket_change_preview['mail'] = $mail;
				
			if ($preview)
			{
				$_ctx->ticket_change_preview['preview'] = true;
			}
			else
			{
				# Post the comment
				try
				{
					if (!text::isEmail(html::clean($mail))) {
						throw new Exception(__('You must provide a valid email address.'));
					}

					if ($ticket) {
						// update du ticket
						$cur = $core->con->openCursor($core->prefix.'litraak_ticket');
						$litraak->updTicket($ticket->ticket_id, $cur, $desc, litraak::CHANGE_PUBLIC, $name, html::clean($mail));
					}
					
					header('Location: '.$litraak->getTicketPublicUrl($project->post_url, $ticket->ticket_id));
					return;
				}
				catch (Exception $e)
				{
					$_ctx->form_error = $e->getMessage();
					$_ctx->form_error;
				}
			}
		}

		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/../default-templates');
		self::serveDocument('litraak-ticket.html');
		return;
	}

	public static function milestoneUrl($project, $milestone, $args)
	{
		global $_ctx;
		global $core;
		$litraak =& $GLOBALS['litraak'];

		$_ctx->posts = $project;
		$_ctx->milestones = $milestone;

		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/../default-templates');
		self::serveDocument('litraak-milestone.html');
		return;
	}

}
?>