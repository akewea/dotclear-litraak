<?php
//@@licence@@

class litraakExtProject extends rsExtPost
{
	public static function countMedia($rs)
	{
		if (isset($rs->_nb_media[$rs->index()]))
		{
			return $rs->_nb_media[$rs->index()];
		}
		else
		{
			$strReq =
			'SELECT count(media_id) '.
			'FROM '.$rs->core->prefix.'post_media '.
			'WHERE post_id = '.(integer) $rs->post_id.' ';
			
			$res = (integer) $rs->core->con->select($strReq)->f(0);
			$rs->_nb_media[$rs->index()] = $res;
			
			$strReq =
			'SELECT count(media_id) '.
			'FROM '.$rs->core->prefix.'litraak_milestone_media MD '.
			'INNER JOIN '.$rs->core->prefix.'litraak_milestone M on M.milestone_id = MD.milestone_id '.
			'WHERE M.post_id = '.(integer) $rs->post_id.' ';
			
			$res = (integer) $rs->core->con->select($strReq)->f(0);
			$rs->_nb_media[$rs->index()] += $res;
			
			return $rs->_nb_media[$rs->index()];
		}
	}
	
	public static function hasNextRelease($rs)
	{
		return (!$rs->isEmpty() && $rs->exists('next_release_name') && $rs->next_release_name);
	}
	
	public static function hasLastRelease($rs)
	{
		return (!$rs->isEmpty() && $rs->exists('last_release_name') && $rs->last_release_name);
	}
	
	public static function getLastReleaseDate($rs,$format)
	{
		if ($format) {
			return dt::dt2str($format,$rs->last_release_dt);
		} else {
			return dt::dt2str($rs->core->blog->settings->system->date_format,$rs->last_release_dt);
		}
	}
	
	public static function getNextReleaseDate($rs,$format)
	{
		if ($format) {
			return dt::dt2str($format,$rs->next_release_dt);
		} else {
			return dt::dt2str($rs->core->blog->settings->system->date_format,$rs->next_release_dt);
		}
	}
	
	public static function isVisible($rs)
	{
		return 	(!$rs->isEmpty() && $rs->exists('post_status') && $rs->post_status == 1);
	}
	
	public static function ticketsActive($rs)
	{
		return $rs->project_open_ticket;
	}
}

class litraakExtMilestone
{
	public static function getDate($rs,$format)
	{
		if ($format) {
			return dt::dt2str($format,$rs->milestone_dt);
		} else {
			return dt::dt2str($rs->core->blog->settings->system->date_format,$rs->milestone_dt);
		}
	}
	
	public static function getTS($rs)
	{
		return strtotime($rs->milestone_dt);
	}
	
	public static function getISO8601Date($rs)
	{
		return dt::iso8601($rs->getTS(),$rs->milestone_tz);
	}
	
	public static function getRFC822Date($rs)
	{
		return dt::rfc822($rs->getTS(),$rs->milestone_tz);
	}
	
	public static function isReleased($rs)
	{
		return $rs->milestone_status == litraak::MILESTONE_RELEASED;
	}
	
	public static function countMedia($rs)
	{
		if (isset($rs->_nb_media[$rs->index()]))
		{
			return $rs->_nb_media[$rs->index()];
		}
		else
		{
			$strReq =
			'SELECT count(media_id) '.
			'FROM '.$rs->core->prefix.'litraak_milestone_media '.
			'WHERE milestone_id = '.(integer) $rs->milestone_id.' ';
			
			$res = (integer) $rs->core->con->select($strReq)->f(0);
			$rs->_nb_media[$rs->index()] = $res;
			return $res;
		}
	}
	
	public static function getProgress($rs)
	{
		$nb_tickets = (integer) $rs->milestone_nb_tickets - (integer) $rs->milestone_nb_wasted;
		if($nb_tickets > 0){
			return litraakUtils::getPercent($nb_tickets, (integer) $rs->milestone_nb_actives);
		}
		
		return 0;
	}
	
	public static function getTicketNumber($rs, $type='')
	{
		switch($type){
			case 'actives': return (integer) $rs->milestone_nb_actives;
			case 'closed': return (integer) ($rs->milestone_nb_tickets - $rs->milestone_nb_wasted - $rs->milestone_nb_actives);
			case 'wasted': return $rs->milestone_nb_wasted;
			default: return (integer) $rs->milestone_nb_tickets;
		}
	}
	
	public static function getFeedID($rs)
	{
		return 'urn:md5:'.md5($rs->core->blog->uid.$rs->milestone_id);
		
		$url = parse_url($rs->core->blog->url);
		$date_part = date('Y-m-d',strtotime($rs->milestone_dt));
		
		return 'tag:'.$url['host'].','.$date_part.':'.$rs->milestone_id;
	}
}

class litraakExtTicket
{
	public static function getDate($rs,$format)
	{
		if ($format) {
			return dt::dt2str($format,$rs->ticket_upddt);
		} else {
			return dt::dt2str($rs->core->blog->settings->system->date_format,$rs->ticket_upddt);
		}
	}
	
	public static function getTS($rs)
	{
		return strtotime($rs->ticket_upddt);
	}
	
	public static function getISO8601Date($rs)
	{
		return dt::iso8601($rs->getTS(),$rs->ticket_tz);
	}
	
	public static function getRFC822Date($rs)
	{
		return dt::rfc822($rs->getTS(),$rs->ticket_tz);
	}
	
	public static function isType($rs, $type)
	{
		switch($type){
			case 'bug': return $rs->ticket_type == litraak::TICKET_BUG;
			case 'task': return $rs->ticket_type == litraak::TICKET_TASK;
			case 'idea': return $rs->ticket_type == litraak::TICKET_IDEA;
		}
		return false;
	}
	
	public static function isStatus($rs, $status)
	{
		switch($status){
			case 'new': return $rs->ticket_status == litraak::TICKET_NEW;
			case 'accepted': return $rs->ticket_status == litraak::TICKET_ACCEPTED;
			case 'closed': return $rs->ticket_status == litraak::TICKET_CLOSED;
			case 'rejected': return $rs->ticket_status == litraak::TICKET_REJECTED;
			case 'deleted': return $rs->ticket_status == litraak::TICKET_DELETED;
			case 'active': return $rs->ticket_status < litraak::TICKET_CLOSED;
		}
		return false;
	}
	
	public static function hasMilestone($rs)
	{
		return $rs->exists('milestone_id') && $rs->milestone_id > 0;
	}
	
	/**
	Returns ticket feed unique ID.
	
	@param	rs	Invisible parameter
	@return	<b>string</b>
	*/
	public static function getFeedID($rs)
	{
		return 'urn:md5:'.md5($rs->core->blog->uid.$rs->ticket_id);
		
		$url = parse_url($rs->core->blog->url);
		$date_part = date('Y-m-d',strtotime($rs->ticket_upddt));
		
		return 'tag:'.$url['host'].','.$date_part.':'.$rs->ticket_id;
	}
}

class litraakExtTicketChange
{
	public static function getDate($rs,$format)
	{
		if ($format) {
			return dt::dt2str($format,$rs->change_dt);
		} else {
			return dt::dt2str($rs->core->blog->settings->system->date_format,$rs->change_dt);
		}
	}
	
	public static function getTS($rs)
	{
		return strtotime($rs->change_dt);
	}
	
	public static function getISO8601Date($rs)
	{
		return dt::iso8601($rs->getTS(),$rs->change_tz);
	}
	
	public static function getRFC822Date($rs)
	{
		return dt::rfc822($rs->getTS(),$rs->change_tz);
	}
	
	public static function getTitle($rs)
	{
		$litraak =& $GLOBALS['litraak'];
		
		eval('$res = '.$rs->change_title.';');
		return $res;
	}
	
	public static function getComment($rs)
	{
		return $rs->change_comment;
	}
	
	public static function hasComment($rs)
	{
		return $rs->change_comment != null;
	}
}
?>