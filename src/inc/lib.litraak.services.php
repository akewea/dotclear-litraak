<?php
//@@licence@@

class litraakRestMethods
{
	
	public static function getTicketById($core,$get)
	{
		if (empty($get['id'])) {
			throw new Exception('No ticket ID');
		}
				
		$rs = $GLOBALS['litraak']->getTickets(array('ticket_id' => (integer) $get['id']));
		
		if ($rs->isEmpty()) {
			throw new Exception('No ticket for this ID');
		}
		
		$rsp = new xmlTag('ticket');
		$rsp->id = $rs->ticket_id;
		
		$rsp->ticket_dt($rs->ticket_dt);
		$rsp->ticket_upddt($rs->ticket_upddt);
		$rsp->ticket_author($rs->ticket_author);
		$rsp->ticket_title($rs->ticket_title);
		$rsp->ticket_desc($rs->ticket_desc);
		$rsp->ticket_status($rs->ticket_status);
		$rsp->ticket_type($rs->ticket_type);
		$rsp->project_name($rs->post_title);
		$rsp->project_url($rs->post_url);
		$rsp->project_id($rs->post_id);
		$rsp->project_dt($rs->post_dt);
		
		if ($core->auth->userID()) {
			$rsp->ticket_email($rs->ticket_email);
		}
		
		return $rsp;
	}
	
	public static function getMilestoneById($core,$get)
	{
		if (empty($get['id'])) {
			throw new Exception('No milestone ID');
		}
				
		$rs = $GLOBALS['litraak']->getMilestones(array('milestone_id' => (integer) $get['id']));
		
		if ($rs->isEmpty()) {
			throw new Exception('No milestone for this ID');
		}
		
		$rsp = new xmlTag('milestone');
		$rsp->id = $rs->milestone_id;
		
		$rsp->milestone_dt($rs->milestone_dt);
		$rsp->milestone_name($rs->milestone_name);
		$rsp->milestone_desc($rs->milestone_desc);
		$rsp->milestone_status($rs->milestone_status);
		$rsp->project_name($rs->post_title);
		$rsp->project_url($rs->post_url);
		$rsp->project_id($rs->post_id);
		$rsp->project_dt($rs->post_dt);
		
		return $rsp;
	}
}
?>