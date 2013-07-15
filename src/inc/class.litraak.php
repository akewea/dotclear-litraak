<?php
//@@ licence@@

class litraak
{

	const POST_TYPE = 'litraak';
	
	const PROJECT_PENDING = '-2';
	const PROJECT_PUBLISHED = '1';
		
	const PROJECT_ABANDONED = '-1';
	const PROJECT_UNKNOWN = '0';
	const PROJECT_ALPHA = '1';
	const PROJECT_BETA = '2';
	const PROJECT_RC = '3';
	const PROJECT_RELEASED = '4';
	
	const MILESTONE_UNRELEASED = '0';
	const MILESTONE_RELEASED = '1';
	
	const TICKET_NEW = '0';
	const TICKET_ACCEPTED = '1';
	const TICKET_CLOSED = '2';
	const TICKET_DELETED = '3';
	const TICKET_REJECTED = '4';
	
	const TICKET_BUG = '0';
	const TICKET_TASK = '1';
	const TICKET_IDEA = '2';
	
	const CHANGE_PRIVATE = '0';
	const CHANGE_PUBLIC = '1';
	
	protected $core;
	public $con;
	public $prefix;
	public $media;
	
	public $project_status;
	public $project_phases;
	public $milestone_status;
	public $ticket_status;
	public $ticket_types;
	public $change_status;
	
	public function __construct($core)
	{
		$this->con =& $core->con;
		$this->prefix = $core->prefix;
		$this->core =& $core;
		$this->media = new litraakMedia($core);
		
		if ($core->blog)
		{
			$this->project_status[self::PROJECT_PENDING] = __('pending');
			$this->project_status[self::PROJECT_PUBLISHED] = __('published');
			
			$this->project_phases[self::PROJECT_ABANDONED] = __('abandoned');
			$this->project_phases[self::PROJECT_UNKNOWN] = __('unknown');
			$this->project_phases[self::PROJECT_ALPHA] = __('alpha');
			$this->project_phases[self::PROJECT_BETA] = __('beta');
			$this->project_phases[self::PROJECT_RC] = __('release candidate');
			$this->project_phases[self::PROJECT_RELEASED] = __('released');
			
			$this->milestone_status[self::MILESTONE_UNRELEASED] = __('unreleased');
			$this->milestone_status[self::MILESTONE_RELEASED] = __('released');
			
			$this->ticket_status[self::TICKET_NEW] = __('new');
			$this->ticket_status[self::TICKET_ACCEPTED] = __('accepted');
			$this->ticket_status[self::TICKET_CLOSED] = __('closed');
			$this->ticket_status[self::TICKET_REJECTED] = __('rejected');
			$this->ticket_status[self::TICKET_DELETED] = __('deleted');
			
			$this->ticket_types[self::TICKET_BUG] = __('bug');
			$this->ticket_types[self::TICKET_TASK] = __('task');
			$this->ticket_types[self::TICKET_IDEA] = __('idea');
			
			$this->change_status[self::CHANGE_PRIVATE] = __('private');
			$this->change_status[self::CHANGE_PUBLIC] = __('public');
		}
	}
	
	// ### URLs ################################################################
	
	public function getProjectAdminURL($id, $escaped=true){
		return $this->core->getPostAdminURL(litraak::POST_TYPE, $id, $escaped);
	}
	
	public function getProjectPublicURL($project_url, $escaped=true){
		return $this->core->blog->url.$this->core->getPostPublicURL(litraak::POST_TYPE, $project_url, $escaped);
	}
	
	public function getAdminURL($escaped=true){
		$url = 'plugin.php?p=litraak';
		return $escaped ? html::escapeURL($url) : $url;
	}
	
	public function getPublicURL($escaped=true){
		$url = $this->core->blog->url.$this->core->blog->settings->litraak->litraak_basename_url.'/';
		return $escaped ? html::escapeURL($url) : $url;
	}
	
	public function getMilestoneAdminURL($project_id, $milestone_id){
		return $this->core->getPostAdminURL(litraak::POST_TYPE, $project_id, false).'&milestoneid='.$milestone_id;
	}
	
	public function getMilestonePublicURL($project_url, $milestone_url){
		return $this->core->blog->url.$this->core->getPostPublicURL(litraak::POST_TYPE, $project_url, false).$milestone_url;
	}
	
	public function getTicketAdminURL($project_id, $ticket_id){
		return $this->core->getPostAdminURL(litraak::POST_TYPE, $project_id, false).'&ticketid='.$ticket_id;
	}
	
	public function getTicketPublicURL($project_url, $ticket_id){
		return $this->core->blog->url.$this->core->getPostPublicURL(litraak::POST_TYPE, $project_url, false).'ticket/'.$ticket_id;
	}

	public function getCommentAdminURL($project_id, $comment_id){
		return $this->core->getPostAdminURL(litraak::POST_TYPE, $project_id, false).'&commentid='.$comment_id;
	}
	
	// ### PROJECTS ############################################################
	
	public function getProjects($params=array(),$count_only=false)
	{
		if ($count_only)
		{
			$strReq = 'SELECT count(P.post_id) ';
		}
		else
		{
			if (!empty($params['no_content'])) {
				$content_req = '';
			} else {
				$content_req =
				'post_excerpt, post_excerpt_xhtml, '.
				'post_content, post_content_xhtml, post_notes, ';
			}
			
			if (!empty($params['columns']) && is_array($params['columns'])) {
				$content_req .= implode(', ',$params['columns']).', ';
			}
			
			$strReq =
			'SELECT P.post_id, P.blog_id, P.user_id, P.cat_id, post_dt, '.
			'post_tz, post_creadt, post_upddt, post_format, post_password, '.
			'post_url, post_lang, post_title, '.$content_req.
			'post_type, post_meta, post_status, post_selected, post_position, '.
			'post_open_comment, post_open_tb, nb_comment, nb_trackback, '.
			'U.user_name, U.user_firstname, U.user_displayname, U.user_email, '.
			'U.user_url, '.
			'PI.project_phase, PI.project_nb_tickets, PI.project_last_release_id, PI.project_next_release_id, '.
			'PI.project_update_template, PI.project_open_ticket, PI.project_nb_actives, PI.project_nb_wasted, '.
			'ML.milestone_name as last_release_name, ML.milestone_url as last_release_url, '.
			'ML.milestone_dt as last_release_dt, ML.milestone_tz as last_release_tz, '.
			'MN.milestone_name as next_release_name, MN.milestone_url as next_release_url, '.
			'MN.milestone_nb_tickets as next_release_nb_tickets, MN.milestone_nb_actives as next_release_nb_actives, '.
			'MN.milestone_dt as next_release_dt, MN.milestone_tz as next_release_tz ';
		}
		
		$strReq .=
		'FROM '.$this->prefix.'post P '.
		'INNER JOIN '.$this->prefix.'user U ON U.user_id = P.user_id '.
		'LEFT JOIN '.$this->prefix.'litraak_project_info PI ON PI.post_id = P.post_id '.
		'LEFT JOIN '.$this->prefix.'litraak_milestone ML ON ML.milestone_id = PI.project_last_release_id '.
		'LEFT JOIN '.$this->prefix.'litraak_milestone MN ON MN.milestone_id = PI.project_next_release_id ';
				
		if (!empty($params['from'])) {
			$strReq .= $params['from'].' ';
		}
		
		$strReq .=
		"WHERE P.blog_id = '".$this->con->escape($this->core->blog->id)."' ";
		
		if (!$this->core->auth->check('contentadmin',$this->core->blog->id)) {
			$strReq .= 'AND (post_status != '.self::PROJECT_PENDING.' ';			
			//if ($this->core->auth->userID()) {
			//	$strReq .= "OR P.user_id = '".$this->con->escape($this->core->auth->userID())."')";
			//} else {
				$strReq .= ') ';
			//}
		}
		
		# Adding parameters
		$strReq .= "AND post_type = '".litraak::POST_TYPE."' ";
		
		if (!empty($params['post_id'])) {
			if (is_array($params['post_id'])) {
				array_walk($params['post_id'],create_function('$v,$k','if($v!==null){$v=(integer)$v;}'));
			} else {
				$params['post_id'] = array((integer) $params['post_id']);
			}
			$strReq .= 'AND P.post_id '.$this->con->in($params['post_id']);
		}
		
		if (!empty($params['post_url'])) {
			$strReq .= "AND post_url = '".$this->con->escape($params['post_url'])."' ";
		}
		
		/* Other filters */
		if (isset($params['post_status'])) {
			$strReq .= 'AND post_status = '.(integer) $params['post_status'].' ';
		}
		
		if (isset($params['project_phase'])) {
			$strReq .= 'AND project_phase = '.(integer) $params['project_phase'].' ';
		}
		
		if (!empty($params['post_lang'])) {
			$strReq .= "AND P.post_lang = '".$this->con->escape($params['post_lang'])."' ";
		}
		
		if (!empty($params['search']))
		{
			$words = text::splitWords($params['search']);
			
			if (!empty($words))
			{
				# --BEHAVIOR-- corePostSearch
				if ($this->core->hasBehavior('corePostSearch')) {
					$this->core->callBehavior('corePostSearch',$this->core,array($words,$strReq,$params));
				}
				
				if ($words)
				{
					foreach ($words as $i => $w) {
						$words[$i] = "post_words LIKE '%".$this->con->escape($w)."%'";
					}
					$strReq .= 'AND '.implode(' AND ',$words).' ';
				}
			}
		}
		
		if (!empty($params['sql'])) {
			$strReq .= $params['sql'].' ';
		}
		
		if (!$count_only)
		{
			if (!empty($params['order'])) {
				$strReq .= 'ORDER BY '.$this->con->escape($params['order']).' ';
			} else {
				$strReq .= 'ORDER BY post_title ASC ';
			}
		}
		
		if (!$count_only && !empty($params['limit'])) {
			$strReq .= $this->con->limit($params['limit']);
		}
		
		$rs = $this->con->select($strReq);
		$rs->core = $this->core;
		$rs->_nb_media = array();
		$rs->extend('litraakExtProject');
		
		return $rs;
	}
	
	public static function validateProject($cur)
	{
		if ($cur->post_title == '') {
			throw new Exception(__('No project name'));
		}
		
		if ($cur->post_content == '') {
			throw new Exception(__('No project description'));
		}
		
		if ($cur->post_content_xhtml == '') {
			throw new Exception(__('No project description'));
		}
	}
	
	private function getProjectInfoCursor($cur)
	{
		if ($cur->project_phase === null) {
			$cur->project_phase = self::PROJECT_UNKNOWN;
		}
	}
	
	public function updProjectInfo($id,$cur)
	{
		$id = (integer) $id;
		
		if (empty($id)) {
			throw new Exception(__('No such project ID'));
		}
		
		$rs = $this->getProjects(array('post_id' => $id));
		
		if ($rs->isEmpty()) {
			throw new Exception(__('No such project ID'));
		}
		
		$this->getProjectInfoCursor($cur);
		
		if($rs->exists('project_phase')){
			$cur->update('WHERE post_id = '.$id.' ');
		}else{
			$cur->post_id = $id;
			$cur->insert();
		}
	}
	
	// ### MILESTONES ##########################################################
	
	public function getMilestones($params=array(),$count_only=false)
	{
		if ($count_only)
		{
			$strReq = 'SELECT count(milestone_id) ';
		}
		else
		{
			$strReq =
			'SELECT M.milestone_id, milestone_dt, milestone_tz, milestone_name, '.
			'milestone_desc, milestone_status, milestone_nb_tickets, milestone_url, '.
			'milestone_nb_actives, milestone_nb_wasted, '.
			'P.post_title, P.post_url, P.post_id, P.post_type, P.user_id, P.post_dt ';
		}
		
		$strReq .=
		'FROM '.$this->prefix.'litraak_milestone M '.
		'INNER JOIN '.$this->prefix.'post P ON M.post_id = P.post_id ';
		
		if (!empty($params['from'])) {
			$strReq .= $params['from'].' ';
		}
		
		$strReq .=
		"WHERE P.blog_id = '".$this->con->escape($this->core->blog->id)."' ";
		
		$strReq .= "AND post_type = '".litraak::POST_TYPE."' ";
		
		if (!$this->core->auth->check('contentadmin',$this->core->blog->id)) {
			$strReq .= 'AND post_status <> 0 ';
		}
		
		if (!empty($params['post_id'])) {
			$strReq .= 'AND P.post_id = '.(integer) $params['post_id'].' ';
		}
		
		if (!empty($params['milestone_id'])) {
			$strReq .= 'AND milestone_id = '.(integer) $params['milestone_id'].' ';
		}
		
		if (!empty($params['milestone_url'])) {
			$strReq .= "AND milestone_url = '".$this->con->escape($params['milestone_url'])."' ";
		}
		
		if (isset($params['milestone_status'])) {
			$strReq .= 'AND milestone_status = '.(integer) $params['milestone_status'].' ';
		}
		
		if (!empty($params['milestone_status_not']))
		{
			$strReq .= 'AND milestone_status <> '.(integer) $params['milestone_status_not'].' ';
		}
		
		if (!empty($params['sql'])) {
			$strReq .= $params['sql'].' ';
		}
		
		if (!$count_only)
		{
			if (!empty($params['order'])) {
				$strReq .= 'ORDER BY '.$this->con->escape($params['order']).' ';
			} else {
				$strReq .= 'ORDER BY milestone_dt DESC ';
			}
		}
		
		if (!$count_only && !empty($params['limit'])) {
			$strReq .= $this->con->limit($params['limit']);
		}
		
		$rs = $this->con->select($strReq);
		$rs->core = $this->core;
		$rs->_nb_media = array();
		$rs->extend('litraakExtMilestone');
		
		return $rs;
	}
	
	public function addMilestone($cur)
	{
		$this->con->writeLock($this->prefix.'litraak_milestone');
		try
		{
			# Get ID
			$rs = $this->con->select(
				'SELECT MAX(milestone_id) '.
				'FROM '.$this->prefix.'litraak_milestone ' 
			);
			
			$cur->milestone_id = (integer) $rs->f(0) + 1;
			
			$cur->milestone_tz = $this->core->blog->settings->system->blog_timezone;
			$cur->milestone_url = $this->getMilestoneURL($cur->milestone_url, $cur->milestone_name, $cur->milestone_id, $cur->post_id);
		
			$this->getMilestoneCursor($cur);
			
			$cur->insert();
			$this->con->unlock();
		}
		catch (Exception $e)
		{
			$this->con->unlock();
			throw $e;
		}
		
		$this->triggerMilestone($cur->milestone_id);
		
		return $cur->milestone_id;
	}
	
	public function updMilestone($id,$cur)
	{
		/*if (!$this->core->auth->check('usage,contentadmin',$this->id)) {
			throw new Exception(__('You are not allowed to update comments'));
		}*/
		
		$id = (integer) $id;
		
		if (empty($id)) {
			throw new Exception(__('No such milestone ID'));
		}
		
		$rs = $this->getMilestones(array('milestone_id' => $id));
		
		if ($rs->isEmpty()) {
			throw new Exception(__('No such milestone ID'));
		}
		
		if ($cur->milestone_url !== null) {
			$cur->milestone_url = $this->getMilestoneURL($cur->milestone_url, $cur->milestone_name, $cur->milestone_id, $cur->post_id);
		}
		
		$this->getMilestoneCursor($cur);
			
		$this->triggerMilestone($id, true);
		
		$cur->update('WHERE milestone_id = '.$id.' ');
		
		$this->triggerMilestone($id);
	}
	
	public function delMilestone($id)
	{
		/*if (!$this->core->auth->check('delete,contentadmin',$this->id)) {
			throw new Exception(__('You are not allowed to delete comments'));
		}*/
		
		$id = (integer) $id;
		
		if (empty($id)) {
			throw new Exception(__('No such milestone ID'));
		}
		
		$strReq = 'DELETE FROM '.$this->prefix.'litraak_milestone '.
				'WHERE milestone_id = '.$id.' ';
		
		$this->con->execute($strReq);
	}
	
	private function getMilestoneCursor($cur)
	{
		if ($cur->milestone_desc !== null && $cur->milestone_desc == '') {
			throw new Exception(__('Empty milestone description'));
		}
		
		if ($cur->milestone_name !== null && $cur->milestone_name == '') {
			throw new Exception(__('Empty milestone name'));
		}
		
		if ($cur->milestone_dt !== null && $cur->milestone_dt == '') {
			throw new Exception(__('Empty milestone date'));
		}
		
		if ($cur->milestone_status === null) {
			$cur->milestone_status = self::MILESTONE_UNRELEASED;
		}
	}
	
	public function getMilestoneName($id)
	{
		$strReq =
		'SELECT milestone_id, milestone_name '.
		'FROM '.$this->prefix.'litraak_milestone M '.
		'WHERE milestone_id = '.(integer) $id.' ';
		
		$rs = $this->con->select($strReq);
		
		if($rs->IsEmpty()){
			return;
		}
		
		return $rs->milestone_name;
	}
	
	public function getMilestoneURL($url,$milestone_name,$milestone_id,$post_id)
	{
		$url = trim($url);
		
		# If URL is empty, we create a new one
		if ($url == '')
		{
			# Transform with format
			$url = text::tidyURL($milestone_name);
		}
		else
		{
			$url = text::tidyURL($url);
		}
		
		$url = str_replace('/', '-', $url);
		
		# Let's check if URL is taken...
		$strReq = 'SELECT milestone_url FROM '.$this->prefix.'litraak_milestone '.
				"WHERE milestone_url = '".$this->con->escape($url)."' ".
				'AND milestone_id <> '.(integer) $milestone_id. ' '.
				"AND post_id = '".(integer) $post_id."' ".
				'ORDER BY milestone_url DESC';
		
		$rs = $this->con->select($strReq);
		
		if (!$rs->isEmpty())
		{
			$strReq = 'SELECT milestone_url FROM '.$this->prefix.'litraak_milestone '.
					"WHERE milestone_url LIKE '".$this->con->escape($url)."%' ".
					'AND milestone_id <> '.(integer) $milestone_id. ' '.
					"AND post_id = '".(integer) $post_id."' ".
					'ORDER BY milestone_url DESC ';
			
			$rs = $this->con->select($strReq);
			$a = array();
			while ($rs->fetch()) {
				$a[] = $rs->milestone_url;
			}
			
			natsort($a);
			$t_url = end($a);
			
			if (preg_match('/(.*?)([0-9]+)$/',$t_url,$m)) {
				$i = (integer) $m[2];
				$url = $m[1];
			} else {
				$i = 1;
			}
			
			return $url.($i+1);
		}
		
		# URL is empty?
		if ($url == '') {
			throw new Exception(__('Empty milestone URL'));
		}
		
		return $url;
	}
	
	// ### MILESTONE MEDIA #####################################################
	// TODO A supprimer si plus besoin.
	
	public function getMilestoneMedia($post_id,$media_id=null)
	{
		$post_id = (integer) $post_id;
		
		$strReq =
		'SELECT media_file, M.media_id, media_path, media_title, media_meta, media_dt, '.
		'media_creadt, media_upddt, media_private, user_id '.
		'FROM '.$this->table.' M '.
		'INNER JOIN '.$this->table_ref.' PM ON (M.media_id = PM.media_id) '.
		"WHERE media_path = '".$this->path."' ".
		'AND post_id = '.$post_id.' ';
		
		if ($media_id) {
			$strReq .= 'AND M.media_id = '.(integer) $media_id.' ';
		}
		
		$rs = $this->con->select($strReq);
		
		$res = array();
		
		while ($rs->fetch()) {
			$f = $this->fileRecord($rs);
			if ($f !== null) {
				$res[] = $f;
			}
		}
		
		return $res;
	}
	
	public function addMilestoneMedia($post_id,$media_id)
	{
		$post_id = (integer) $post_id;
		$media_id = (integer) $media_id;
		
		$f = $this->getPostMedia($post_id,$media_id);
		
		if (!empty($f)) {
			return;
		}
		
		$cur = $this->con->openCursor($this->table_ref);
		$cur->post_id = $post_id;
		$cur->media_id = $media_id;
		
		$cur->insert();
		$this->core->blog->triggerBlog();
	}
	
	/**
	Detaches a media from a post.
	
	@param	post_id	<b>integer</b>		Post ID
	@param	media_id	<b>integer</b>		Optionnal media ID
	*/
	public function removeMilestoneMedia($post_id,$media_id)
	{
		$post_id = (integer) $post_id;
		$media_id = (integer) $media_id;
		
		$strReq = 'DELETE FROM '.$this->table_ref.' '.
				'WHERE post_id = '.$post_id.' '.
				'AND media_id = '.$media_id.' ';
		
		$this->con->execute($strReq);
		$this->core->blog->triggerBlog();
	}
	
	// ### TICKETS #############################################################
	
	public function getTickets($params=array(),$count_only=false)
	{
		if ($count_only)
		{
			$strReq = 'SELECT count(ticket_id) ';
		}
		else
		{
			$strReq =
			'SELECT T.ticket_id, ticket_type, ticket_dt, ticket_tz, ticket_title, '.
			'ticket_desc, ticket_email, ticket_author, ticket_upddt, ticket_status, ticket_open_comment, '.
			'M.milestone_id, M.milestone_name, M.milestone_url, M.milestone_status, '.
			'P.post_title, P.post_url, P.post_id, P.post_type, P.user_id, P.post_dt ';
		}
		
		$strReq .=
		'FROM '.$this->prefix.'litraak_ticket T '.
		'LEFT OUTER JOIN '.$this->prefix.'litraak_milestone M ON M.milestone_id = T.milestone_id '.
		'INNER JOIN '.$this->prefix.'post P ON T.post_id = P.post_id ';
		
		if (!empty($params['from'])) {
			$strReq .= $params['from'].' ';
		}
		
		$strReq .=
		"WHERE P.blog_id = '".$this->core->con->escape($this->core->blog->id)."' ";
		
		$strReq .= "AND post_type = '".litraak::POST_TYPE."' ";
		
		if (!$this->core->auth->check('contentadmin',$this->core->blog->id)) {
			$strReq .= 'AND post_status <> 0 ';
		}
		
		if (!empty($params['post_id'])) {
			$strReq .= 'AND P.post_id = '.(integer) $params['post_id'].' ';
		}
		
		if (!empty($params['milestone_id'])) {
			$strReq .= 'AND M.milestone_id = '.(integer) $params['milestone_id'].' ';
		}
		
		if (!empty($params['no_milestone'])) {
			$strReq .= 'AND M.milestone_id is null ';
		}else if (!empty($params['with_milestone'])) {
			$strReq .= 'AND M.milestone_id is not null ';
		}
		
		if (!empty($params['ticket_id'])) {
			$strReq .= 'AND ticket_id = '.(integer) $params['ticket_id'].' ';
		}
		
		if (isset($params['ticket_type'])) {
			$strReq .= 'AND ticket_type = '.(integer) $params['ticket_type'].' ';
		}
		
		if (!empty($params['ticket_type_not']))
		{
			$strReq .= 'AND ticket_type <> '.(integer) $params['ticket_type_not'].' ';
		}
		
		if (isset($params['ticket_status'])) {
			if (is_array($params['ticket_status']) && !empty($params['ticket_status'])) {
				$strReq .= 'AND ticket_status '.$this->con->in($params['ticket_status']);
			} else {
				$strReq .= "AND ticket_status = ".(integer) $params['ticket_status']." ";
			}
		}
		
		if (!empty($params['ticket_status_not']))
		{
			$strReq .= 'AND ticket_status <> '.(integer) $params['ticket_status_not'].' ';
		}
		
		if (isset($params['q_author'])) {
			$q_author = $this->con->escape(str_replace('*','%',strtolower($params['q_author'])));
			$strReq .= "AND LOWER(ticket_author) LIKE '".$q_author."' ";
		}
		
		if (!empty($params['sql'])) {
			$strReq .= $params['sql'].' ';
		}
		
		if (!$count_only)
		{
			$strReq .= 'ORDER BY ';
			if (!empty($params['order'])) {
				$strReq .= $this->con->escape($params['order']).', ';
			}
			$strReq .= 'ticket_status ASC, ticket_type ASC, ticket_upddt DESC ';
		}
		
		if (!$count_only && !empty($params['limit'])) {
			$strReq .= $this->con->limit($params['limit']);
		}
		
		$rs = $this->con->select($strReq);
		$rs->core = $this->core;
		$rs->extend('litraakExtTicket');
		
		return $rs;
	}
	
	public function addTicket($cur)
	{
		$this->con->writeLock($this->prefix.'litraak_ticket');
		try
		{
			# Get ID
			$rs = $this->con->select(
				'SELECT MAX(ticket_id) '.
				'FROM '.$this->prefix.'litraak_ticket ' 
			);
			
			$cur->ticket_id = (integer) $rs->f(0) + 1;
			$cur->ticket_upddt = date('Y-m-d H:i:s');
			
			$offset = dt::getTimeOffset($this->core->blog->settings->system->blog_timezone);
			$cur->ticket_dt = date('Y-m-d H:i:s',time() + $offset);
			$cur->ticket_tz = $this->core->blog->settings->system->blog_timezone;
			
			$this->getTicketCursor($cur);
			
			$cur->insert();
			$this->con->unlock();
		}
		catch (Exception $e)
		{
			$this->con->unlock();
			throw $e;
		}
		
		$this->triggerTicket($cur->ticket_id);
		
		return $cur->ticket_id;
	}
	
	private function updTicketCursor($id, $cur, $old, $comment='', $status=1, $author='', $email='')
	{
		$cur->ticket_upddt = date('Y-m-d H:i:s');
		
		$this->triggerTicket($id, true);
		
		$cur->update('WHERE ticket_id = '.$id.' ');
		
		$this->triggerTicket($id);
		$this->triggerTicketChange($id, $old, $comment, $status, 
			($author != '') ? $author : html::escapeHTML($this->core->auth->getInfo('user_cn')), 
			($email != '') ? $email : html::escapeHTML($this->core->auth->getInfo('user_email')));
	}
	
	public function checkTicketUpdate($id)
	{
		/*if (!$this->core->auth->check('usage,contentadmin',$this->id)) {
			throw new Exception(__('You are not allowed to update comments'));
		}*/
		
		$id = (integer) $id;
		
		if (empty($id)) {
			throw new Exception(__('No such ticket ID'));
		}
		
		$rs = $this->getTickets(array('ticket_id' => $id));
		
		if ($rs->isEmpty()) {
			throw new Exception(__('No such ticket ID'));
		}
		
		return $rs;
	}
	
	public function updTicket($id, $cur, $comment='', $status=1, $author='', $email='')
	{
		$rs = $this->checkTicketUpdate($id);
		
		$this->getTicketCursor($cur);
			
		$this->updTicketCursor($id, $cur, $rs, $comment, $status, $author, $email);
	}
	
	public function updTicketStatus($id, $ticket_status, $comment='', $status=1, $author='', $email='')
	{
		global $core;
		$rs = $this->checkTicketUpdate($id);
		
		if ($ticket_status === null) {
			$ticket_status = self::TICKET_NEW;
		}
		
		if($rs->ticket_status == $ticket_status){
			return;
		}
			
		$cur = $core->con->openCursor($core->prefix.'litraak_ticket');
		$cur->ticket_status = $ticket_status;
		
		$this->updTicketCursor($id, $cur, $rs, $comment, $status, $author, $email);
	}
	
	public function updTicketType($id, $ticket_type, $comment='', $status=1, $author='', $email='')
	{
		global $core;
		$rs = $this->checkTicketUpdate($id);
		
		if ($ticket_type === null) {
			$ticket_type = self::TICKET_BUG;
		}
		
		if($rs->ticket_type == $ticket_type){
			return;
		}
		
		$cur = $core->con->openCursor($core->prefix.'litraak_ticket');
		$cur->ticket_type = $ticket_type;
		
		$this->updTicketCursor($id, $cur, $rs, $comment, $status, $author, $email);
	}
	
	public function updTicketMilestone($id, $milestone_id, $comment='', $status=1, $author='', $email='')
	{
		global $core;
		$rs = $this->checkTicketUpdate($id);
		
		if ($milestone_id === null) {
			return;
		}
		
		if($rs->milestone_id == $milestone_id){
			return;
		}
		
		$cur = $core->con->openCursor($core->prefix.'litraak_ticket');
		$cur->milestone_id = $milestone_id;
		
		$this->updTicketCursor($id, $cur, $rs, $comment, $status, $author, $email);
	}
	
	public function delTicket($id)
	{
		/*if (!$this->core->auth->check('delete,contentadmin',$this->id)) {
			throw new Exception(__('You are not allowed to delete comments'));
		}*/
		
		$id = (integer) $id;
		
		if (empty($id)) {
			throw new Exception(__('No such ticket ID'));
		}
		
		$strReq = 'DELETE FROM '.$this->prefix.'litraak_ticket '.
				'WHERE ticket_id = '.$id.' ';
		
		$this->triggerTicket($id,true);
		$this->con->execute($strReq);
	}
	
	private function getTicketCursor($cur)
	{
		if ($cur->ticket_desc !== null && $cur->ticket_desc == '') {
			throw new Exception(__('Empty ticket description.'));
		}
		
		if ($cur->ticket_title !== null && $cur->ticket_title == '') {
			throw new Exception(__('Empty ticket title.'));
		}
		
		if ($cur->ticket_author !== null && $cur->ticket_author == '') {
			throw new Exception(__('Empty ticket author name.'));
		}
		
		if ($cur->ticket_email != '' && !text::isEmail($cur->ticket_email)) {
			throw new Exception(__('Email address is not valid.'));
		}
		
		if ($cur->ticket_status === null) {
			$cur->ticket_status = self::TICKET_NEW;
		}
		
		if ($cur->ticket_type === null) {
			$cur->ticket_type = self::TICKET_BUG;
		}
	}
	
	// ### TICKETS CHANGES #####################################################
	
	public function getTicketChanges($params=array(),$count_only=false)
	{
		if ($count_only)
		{
			$strReq = 'SELECT count(change_id) ';
		}
		else
		{
			$strReq =
			'SELECT C.change_id, change_title, change_comment, change_email, '.
			'change_author, change_dt, change_tz, change_status, '.
			'T.post_id, T.milestone_id, T.ticket_id, T.post_id ';
		}
		
		$strReq .=
		'FROM '.$this->prefix.'litraak_ticket_change C '.
		'INNER JOIN '.$this->prefix.'litraak_ticket T ON T.ticket_id = C.ticket_id ';
		
		if (!empty($params['from'])) {
			$strReq .= $params['from'].' ';
		}
		
		$strReq .=
		"WHERE 1 = 1 ";
		
		if (!$this->core->auth->check('contentadmin',$this->core->blog->id)) {
			$strReq .= 'AND change_status = '.self::CHANGE_PUBLIC.' ';
		}
		
		if (!empty($params['post_id'])) {
			$strReq .= 'AND T.post_id = '.(integer) $params['post_id'].' ';
		}
		
		if (!empty($params['ticket_id'])) {
			$strReq .= 'AND T.ticket_id = '.(integer) $params['ticket_id'].' ';
		}
		
		if (!empty($params['milestone_id'])) {
			$strReq .= 'AND T.milestone_id = '.(integer) $params['milestone_id'].' ';
		}
		
		if (!empty($params['sql'])) {
			$strReq .= $params['sql'].' ';
		}
		
		if (!$count_only)
		{
			$strReq .= 'ORDER BY ';
			if (!empty($params['order'])) {
				$strReq .= $this->con->escape($params['order']).', ';
			}
			$strReq .= 'change_dt ASC ';
		}
		
		if (!$count_only && !empty($params['limit'])) {
			$strReq .= $this->con->limit($params['limit']);
		}
		
		$rs = $this->con->select($strReq);
		$rs->core = $this->core;
		$rs->extend('litraakExtTicketChange');
		
		return $rs;
	}
	
	public function addTicketChange($cur)
	{
		$this->con->writeLock($this->prefix.'litraak_ticket_change');
		try
		{
			# Get ID
			$rs = $this->con->select(
				'SELECT MAX(change_id) '.
				'FROM '.$this->prefix.'litraak_ticket_change ' 
			);
			
			$cur->change_id = (integer) $rs->f(0) + 1;
			$cur->change_dt = date('Y-m-d H:i:s');
			
			$offset = dt::getTimeOffset($this->core->blog->settings->system->blog_timezone);
			$cur->change_dt = date('Y-m-d H:i:s',time() + $offset);
			$cur->change_tz = $this->core->blog->settings->system->blog_timezone;
			
			$this->getTicketChangeCursor($cur);
			
			$cur->insert();
			$this->con->unlock();
		}
		catch (Exception $e)
		{
			$this->con->unlock();
			throw $e;
		}
		
		return $cur->change_id;
	}
	
	public function delTicketChange($id)
	{
		// TODO Gestion des droits
		/*if (!$this->core->auth->check('delete,contentadmin',$this->id)) {
			throw new Exception(__('You are not allowed to delete comments'));
		}*/
		
		$id = (integer) $id;
		
		if (empty($id)) {
			throw new Exception(__('No such ticket change ID'));
		}
		
		$strReq = 'DELETE FROM '.$this->prefix.'litraak_ticket_change '.
				'WHERE change_id = '.$id.' ';
		
		$this->con->execute($strReq);
	}
	
	private function getTicketChangeCursor($cur)
	{
		if ($cur->change_author !== null && $cur->change_author == '') {
			throw new Exception(__('You must provide an author name'));
		}
		
		if ($cur->change_email != '' && !text::isEmail($cur->change_email)) {
			throw new Exception(__('Email address is not valid.'));
		}
		
		if ($cur->change_status === null) {
			$cur->change_status = self::CHANGE_PUBLIC;
		}
	}
	
	// ### COMMENTS ############################################################
	
	public function getComments($params=array(),$count_only=false)
	{
		$params['post_type'] = litraak::POST_TYPE;
		
		if ($count_only)
		{
			$strReq = 'SELECT count(comment_id) ';
		}
		else
		{
			if (!empty($params['no_content'])) {
				$content_req = '';
			} else {
				$content_req = 'comment_content, ';
			}
			
			$strReq =
			'SELECT C.comment_id, comment_dt, comment_tz, comment_upddt, '.
			'comment_author, comment_email, comment_site, '.
			$content_req.' comment_trackback, comment_status, '.
			'comment_spam_status, comment_spam_filter, comment_ip, '.
			'P.post_title, P.post_url, P.post_id, P.post_password, P.post_type, '.
			'P.post_dt, P.user_id, U.user_email, U.user_url ';
		}
		
		$strReq .=
		'FROM '.$this->prefix.'comment C '.
		'INNER JOIN '.$this->prefix.'post P ON C.post_id = P.post_id '.
		'INNER JOIN '.$this->prefix.'user U ON P.user_id = U.user_id ';
		
		if (!empty($params['from'])) {
			$strReq .= $params['from'].' ';
		}
		
		$strReq .=
		"WHERE P.blog_id = '".$this->con->escape($this->core->blog->id)."' ";
		
		if (!$this->core->auth->check('contentadmin',$this->core->blog->id)) {
			$strReq .= 'AND comment_status = 1 AND P.post_status != '.self::PROJECT_PENDING.' ';
			
			/*if ($this->without_password) {
				$strReq .= 'AND post_password IS NULL ';
			}
			$strReq .= ') ';
			
			if ($this->core->auth->userID()) {
				$strReq .= "OR P.user_id = '".$this->con->escape($this->core->auth->userID())."')";
			} else {
				$strReq .= ') ';
			}*/
		}
		
		$strReq .= "AND post_type = '".litraak::POST_TYPE."' ";
		
		if (!empty($params['post_id'])) {
			$strReq .= 'AND P.post_id = '.(integer) $params['post_id'].' ';
		}
		
		if (!empty($params['comment_id'])) {
			$strReq .= 'AND comment_id = '.(integer) $params['comment_id'].' ';
		}
		
		if (isset($params['comment_status'])) {
			$strReq .= 'AND comment_status = '.(integer) $params['comment_status'].' ';
		}
		
		if (!empty($params['comment_status_not']))
		{
			$strReq .= 'AND comment_status <> '.(integer) $params['comment_status_not'].' ';
		}
		
		if (isset($params['comment_ip'])) {
			$strReq .= "AND comment_ip = '".$this->con->escape($params['comment_ip'])."' ";
		}

		if (isset($params['q_author'])) {
			$q_author = $this->con->escape(str_replace('*','%',strtolower($params['q_author'])));
			$strReq .= "AND LOWER(comment_author) LIKE '".$q_author."' ";
		}
		
		if (!empty($params['search']))
		{
			$words = text::splitWords($params['search']);
			
			if (!empty($words))
			{
				# --BEHAVIOR coreCommentSearch
				if ($this->core->hasBehavior('coreCommentSearch')) {
					$this->core->callBehavior('coreCommentSearch',$this->core,array($words,$strReq,$params));
				}
				
				if ($words)
				{
					foreach ($words as $i => $w) {
						$words[$i] = "comment_words LIKE '%".$this->con->escape($w)."%'";
					}
					$strReq .= 'AND '.implode(' AND ',$words).' ';
				}
			}
		}
		
		if (!empty($params['sql'])) {
			$strReq .= $params['sql'].' ';
		}
		
		if (!$count_only)
		{
			if (!empty($params['order'])) {
				$strReq .= 'ORDER BY '.$this->con->escape($params['order']).' ';
			} else {
				$strReq .= 'ORDER BY comment_dt DESC ';
			}
		}
		
		if (!$count_only && !empty($params['limit'])) {
			$strReq .= $this->con->limit($params['limit']);
		}
		
		$rs = $this->con->select($strReq);
		$rs->core = $this->core;
		$rs->extend('rsExtComment');
		
		# --BEHAVIOR-- coreBlogGetComments
		$this->core->callBehavior('coreBlogGetComments',$rs);
		
		return $rs;
	}	
	
	// ### TRIGGERS ############################################################
	
	public function triggerTicket($id,$del=false)
	{
		$id = (integer) $id;
		
		$strReq = 'SELECT milestone_id, post_id '.
				'FROM '.$this->prefix.'litraak_ticket '.
				'WHERE ticket_id = '.$id.' ';
		
		$rs = $this->con->select($strReq);
		
		$milestone_id = $rs->milestone_id;
		$post_id = $rs->post_id;
		
		// Nb tickets projet
		$strReq = 'SELECT COUNT(post_id) '.
				'FROM '.$this->prefix.'litraak_ticket '.
				'WHERE post_id = '.(integer) $post_id.' ';
		if ($del) {
			$strReq .= 'AND ticket_id <> '.$id.' ';
		}
		$rs = $this->con->select($strReq);
		if ($rs->isEmpty()) {
			return;
		}
		$nb_tickets_project = (integer) $rs->f(0);
		
		// Nb tickets actives du projet
		$strReq = 'SELECT COUNT(milestone_id) '.
				'FROM '.$this->prefix.'litraak_ticket '.
				'WHERE post_id = '.(integer) $post_id.' '.
				'AND ticket_status < '.self::TICKET_CLOSED.' ';
		if ($del) {
			$strReq .= 'AND ticket_id <> '.$id.' ';
		}
		$rs = $this->con->select($strReq);
		if ($rs->isEmpty()) {
			return;
		}
		$nb_actives_project = (integer) $rs->f(0);
		
		// Nb tickets rejected or deleted (wasted) du projet
		$strReq = 'SELECT COUNT(milestone_id) '.
				'FROM '.$this->prefix.'litraak_ticket '.
				'WHERE post_id = '.(integer) $post_id.' '.
				'AND ticket_status > '.self::TICKET_CLOSED.' ';
		if ($del) {
			$strReq .= 'AND ticket_id <> '.$id.' ';
		}
		$rs = $this->con->select($strReq);
		if ($rs->isEmpty()) {
			return;
		}
		$nb_wasted_project = (integer) $rs->f(0);
		
		// Nb tickets
		$strReq = 'SELECT COUNT(milestone_id) '.
				'FROM '.$this->prefix.'litraak_ticket '.
				'WHERE milestone_id = '.(integer) $milestone_id.' ';
		if ($del) {
			$strReq .= 'AND ticket_id <> '.$id.' ';
		}
		$rs = $this->con->select($strReq);
		if ($rs->isEmpty()) {
			return;
		}
		$nb_tickets = (integer) $rs->f(0);
		
		// Nb tickets actives
		$strReq = 'SELECT COUNT(milestone_id) '.
				'FROM '.$this->prefix.'litraak_ticket '.
				'WHERE milestone_id = '.(integer) $milestone_id.' '.
				'AND ticket_status < '.self::TICKET_CLOSED.' ';
		if ($del) {
			$strReq .= 'AND ticket_id <> '.$id.' ';
		}
		$rs = $this->con->select($strReq);
		if ($rs->isEmpty()) {
			return;
		}
		$nb_actives = (integer) $rs->f(0);
		
		// Nb tickets rejected or deleted (wasted)
		$strReq = 'SELECT COUNT(milestone_id) '.
				'FROM '.$this->prefix.'litraak_ticket '.
				'WHERE milestone_id = '.(integer) $milestone_id.' '.
				'AND ticket_status > '.self::TICKET_CLOSED.' ';
		if ($del) {
			$strReq .= 'AND ticket_id <> '.$id.' ';
		}
		$rs = $this->con->select($strReq);
		if ($rs->isEmpty()) {
			return;
		}
		$nb_wasted = (integer) $rs->f(0);
		
		// MAJ Milestone
		$cur = $this->con->openCursor($this->prefix.'litraak_milestone');
		$cur->milestone_nb_tickets = $nb_tickets;
		$cur->milestone_nb_actives = $nb_actives;
		$cur->milestone_nb_wasted = $nb_wasted;
		$cur->update('WHERE milestone_id = '.(integer) $milestone_id);
		
		// Test l'existence des infos
		$strReq = 'SELECT post_id '.
				'FROM '.$this->prefix.'litraak_project_info '.
				'WHERE post_id = '.(integer) $post_id.' ';
		$rs = $this->con->select($strReq);
		
		// MAJ Project infos
		$cur = $this->con->openCursor($this->prefix.'litraak_project_info');
		$cur->project_nb_tickets = $nb_tickets_project;
		$cur->project_nb_actives = $nb_actives_project;
		$cur->project_nb_wasted = $nb_wasted_project;
		if ($rs->isEmpty()) {
			$cur->post_id = $post_id;
			$cur->insert();
		}else{
			$cur->update('WHERE post_id = '.(integer) $post_id);
		}
	}
	
	public function triggerMilestone($id,$del=false)
	{
		$id = (integer) $id;
		
		$strReq = 'SELECT post_id '.
				'FROM '.$this->prefix.'litraak_milestone '.
				'WHERE milestone_id = '.$id.' ';
		
		$rs = $this->con->select($strReq);
		
		$post_id = $rs->post_id;
		
		// Last release
		$strReq = 'SELECT milestone_id '.
				'FROM '.$this->prefix.'litraak_milestone '.
				'WHERE post_id = '.(integer) $post_id.' '.
				'AND milestone_status = '.self::MILESTONE_RELEASED.' ';
		if ($del) {
			$strReq .= 'AND milestone_id <> '.$id.' ';
		}
		$strReq .= 'ORDER BY milestone_dt DESC LIMIT 1 ';
		
		$rs = $this->con->select($strReq);
		$last_release_id = (integer) $rs->milestone_id;
		
		// Last release
		$strReq = 'SELECT milestone_id '.
				'FROM '.$this->prefix.'litraak_milestone '.
				'WHERE post_id = '.(integer) $post_id.' '.
				'AND milestone_status = '.self::MILESTONE_UNRELEASED.' ';
		if ($del) {
			$strReq .= 'AND milestone_id <> '.$id.' ';
		}
		$strReq .= 'ORDER BY milestone_dt ASC LIMIT 1 ';
		
		$rs = $this->con->select($strReq);
		$next_release_id = (integer) $rs->milestone_id;
		
		// Test l'existence des infos
		$strReq = 'SELECT post_id '.
				'FROM '.$this->prefix.'litraak_project_info '.
				'WHERE post_id = '.(integer) $post_id.' ';
		$rs = $this->con->select($strReq);
		
		// MAJ Project Infos
		$cur = $this->con->openCursor($this->prefix.'litraak_project_info');
		$cur->project_last_release_id = $last_release_id;
		$cur->project_next_release_id = $next_release_id;
		
		if ($rs->isEmpty()) {
			$cur->post_id = $post_id;
			$cur->insert();
		}else{
			$cur->update('WHERE post_id = '.(integer) $post_id);
		}
	}
	
	public function triggerTicketChange($id, $old_ticket, $comment, $status=1, $author='', $email='')
	{
		$id = (integer) $id;
		
		$params = array();
		$params['ticket_id'] = $id;
		$ticket = $this->getTickets($params);
		
		if($ticket->isEmpty()){
			return;
		}
		
		$no_change = true;
		
		// Modification du type
		if($ticket->ticket_type != $old_ticket->ticket_type){
			$this->addTriggerChange($id, 'sprintf(__("<strong>Type</strong> changed from <em>%s</em> to <em>%s</em>"),'.
			'$litraak->getTicketType('.$old_ticket->ticket_type.'),'.
			'$litraak->getTicketType('.$ticket->ticket_type.'))', $comment, $status, $author, $email);
			$no_change = false;
		}

		// Modification du statut
		if($ticket->ticket_status != $old_ticket->ticket_status){
			$this->addTriggerChange($id, 'sprintf(__("<strong>Status</strong> changed from <em>%s</em> to <em>%s</em>"),'.
			'$litraak->getTicketStatus('.$old_ticket->ticket_status.'),'.
			'$litraak->getTicketStatus('.$ticket->ticket_status.'))', $comment, $status, $author, $email);
			$no_change = false;
		}
		
		// Modification de la milestone
		if($ticket->milestone_id != $old_ticket->milestone_id){
			if(!$ticket->milestone_id){
				$this->addTriggerChange($id, 'sprintf(__("<strong>Milestone</strong> unset from <em>%s</em>"),'.
				'$litraak->getMilestoneName('.$old_ticket->milestone_id.'))', $comment, $status, $author, $email);
			}else if(!$old_ticket->milestone_id){
				$this->addTriggerChange($id, 'sprintf(__("<strong>Milestone</strong> set to <em>%s</em>"),'.
				'$litraak->getMilestoneName('.$ticket->milestone_id.'))', $comment, $status, $author, $email);
			}else{
				$this->addTriggerChange($id, 'sprintf(__("<strong>Milestone</strong> changed from <em>%s</em> to <em>%s</em>"),'.
				'$litraak->getMilestoneName('.$old_ticket->milestone_id.'),'.
				'$litraak->getMilestoneName('.$ticket->milestone_id.'))', $comment, $status, $author, $email);
			}
			$no_change = false;
		}
		
		// Modification du titre ou de la description
		if($ticket->ticket_desc != $old_ticket->ticket_desc 
				|| $ticket->ticket_title != $old_ticket->ticket_title){
			$this->addTriggerChange($id, '__("<strong>Description</strong> changed")', $comment, $status, $author, $email);
			$no_change = false;
		}
		
		// Aucun changements, mais un commentaire
		if($no_change && $comment != ''){
			$this->addCommentChange($id, $comment, $status, $author, $email);
		}
		
	}
	
	private function addTriggerChange($ticket_id, $title, $comment, $status, $author, $email){
		$cur = $this->con->openCursor($this->prefix.'litraak_ticket_change');
		$cur->ticket_id = $ticket_id;
		$cur->change_title = $title;
		$cur->change_comment = $comment;
		$cur->change_status = $status;
		$cur->change_email = $email;
		$cur->change_author = $author;
		
		$this->addTicketChange($cur);
	}
	
	public function addCommentChange($ticket_id, $comment, $status=1, $author='', $email=''){
		$title = 'sprintf(__("<strong>Comment</strong> added by <em>%s</em>"),"'.(($author != '') ? $author : html::escapeHTML($this->core->auth->getInfo('user_cn'))).'")';
		$this->addTriggerChange($ticket_id, $title, $comment, $status, 
			($author != '') ? $author : html::escapeHTML($this->core->auth->getInfo('user_cn')), 
			($email != '') ? $email : html::escapeHTML($this->core->auth->getInfo('user_email')));
	}
	
	// ### STATUS ##############################################################
	
	public function getAllProjectStatus()
	{
		return $this->project_status;
	}
	
	public function getProjectStatus($status)
	{
		if (isset($this->project_status[$status])) {
			return $this->project_status[$status];
		}
		return $this->project_status[self::PROJECT_PENDING];
	}
	
	public function getAllProjectPhases()
	{
		return $this->project_phases;
	}
	
	public function getProjectPhase($phase)
	{
		if (isset($this->project_phases[$phase])) {
			return $this->project_phases[$phase];
		}
		return $this->project_phases[self::PROJECT_UNKNOWN];
	}
	
	public function getAllMilestoneStatus()
	{
		return $this->milestone_status;
	}
	
	public function getMilestoneStatus($status)
	{
		if (isset($this->milestone_status[$status])) {
			return $this->milestone_status[$status];
		}
		return $this->milestone_status[self::MILESTONE_UNRELEASED];
	}
	
	public function getAllTicketStatus()
	{
		return $this->ticket_status;
	}
	
	public function getTicketStatus($status)
	{
		if (isset($this->ticket_status[$status])) {
			return $this->ticket_status[$status];
		}
		return $this->ticket_status[self::TICKET_NEW];
	}
	
	public static function getActiveTicketStatus()
	{
		return array(self::TICKET_NEW, self::TICKET_ACCEPTED);
	}
	
	public static function getWasteTicketStatus()
	{
		return array(self::TICKET_REJECTED, self::TICKET_DELETED);
	}
	
	public function getAllTicketTypes()
	{
		return $this->ticket_types;
	}
	
	public function getTicketType($type)
	{
		if (isset($this->ticket_types[$type])) {
			return $this->ticket_types[$type];
		}
		return $this->ticket_types[self::TICKET_BUG];
	}
	
}

?>