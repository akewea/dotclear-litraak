<?php 
//@@licence@@

class litraakAdminProjectList extends adminGenericList
{
	public function display($page,$nb_per_page,$enclose_block='')
	{
		if ($this->rs->isEmpty())
		{
			echo '<p><strong>'.__('No projects').'</strong></p>';
		}
		else
		{
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);
			$pager->html_prev = $this->html_prev;
			$pager->html_next = $this->html_next;
			$pager->var_page = 'page';
			
			$html_block =
			'<table class="clear" id="projects-list"><tr>'.
			'<th colspan="2">'.__('Title').'</th>'.
			'<th>'.__('Last update').'</th>'.
			'<th>'.__('Last release').'</th>'.
			'<th>'.__('Next release').'</th>'.
			'<th>'.__('Tickets').'</th>'.
			'<th>'.__('Comments').'</th>'.
			'<th>'.__('Status').'</th>'.
			'</tr>%s</table>';
			
			if ($enclose_block) {
				$html_block = sprintf($enclose_block,$html_block);
			}
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
			
			$blocks = explode('%s',$html_block);
			
			echo $blocks[0];
			
			while ($this->rs->fetch())
			{
				echo $this->projectLine();
			}
			
			echo $blocks[1];
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
		}
	}
	
	private function projectLine()
	{
		if ($this->core->auth->check('categories',$this->core->blog->id)) {
			$cat_link = '<a href="category.php?id=%s">%s</a>';
		} else {
			$cat_link = '%2$s';
		}
		
		$img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
		switch ($this->rs->post_status) {
			case litraak::PROJECT_PENDING:
				$img_status = sprintf($img,__('pending'),'scheduled.png'); break;
			case litraak::PROJECT_PUBLISHED:
				$img_status = sprintf($img,__('published'),'check-on.png'); break;
		}
		
		$img = '<img alt="%1$s" title="%1$s" src="index.php?pf=litraak/img/%2$s" />';
		$img_phase = sprintf($img,__('unknown'),'wait.png');
		switch ($this->rs->project_phase) {
			case litraak::PROJECT_UNKNOWN:
				$img_phase = sprintf($img,__('unknown'),'wait.png'); break;
			case litraak::PROJECT_ABANDONED:
				$img_phase = sprintf($img,__('abandoned'),'abandoned.png'); break;
			case litraak::PROJECT_ALPHA:
				$img_phase = sprintf($img,__('alpha'),'alpha.png'); break;
			case litraak::PROJECT_BETA:
				$img_phase = sprintf($img,__('beta'),'beta.png'); break;
			case litraak::PROJECT_RC:
				$img_phase = sprintf($img,__('release candidate'),'release-candidate.png'); break;
			case litraak::PROJECT_RELEASED:
				$img_phase = sprintf($img,__('released'),'released.png'); break;
		}
		
		$attach = '';
		$nb_media = $this->rs->countMedia();
		$img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
		if ($nb_media > 0) {
			$attach_str = $nb_media == 1 ? __('%d attachment') : __('%d attachments');
			$attach = sprintf($img,sprintf($attach_str,$nb_media),'attach.png');
		}
		
		$res = '<tr class="line'.($this->rs->post_status == litraak::PROJECT_PENDING ? ' offline' : '').'"'.
		' id="p'.$this->rs->post_id.'">';
		
		$milestone_link = '<a href="plugin.php?p=litraak&amp;projectid='.$this->rs->post_id.'&amp;milestoneid=%s" title="%s">%s</a>';
		$next_release = $last_release = __('None');
		if($this->rs->hasNextRelease()){
			$next_release_progress = '';
			$next_release = sprintf($milestone_link, $this->rs->project_next_release_id, __('Next release'), $this->rs->next_release_name);
			$next_release_tickets = $next_release_tickets_count = $this->rs->next_release_nb_tickets;
			if($next_release_tickets > 0){
				$next_release_actives = $this->rs->next_release_nb_actives;
				$next_release_progress = '<br />'.litraakUtils::getProgressBar(litraakUtils::getPercent($next_release_tickets_count, $next_release_actives));
				$next_release_tickets = sprintf('<a href="plugin.php?p=litraak&amp;projectid='.$this->rs->post_id.'&amp;ticket_milestone=%s&amp;ticket_status=&amp;tab=tickets" title="'.__('This milestone tickets').'">%s</a>', $this->rs->project_next_release_id, $next_release_tickets);
				if($next_release_actives > 0 && $next_release_actives != $next_release_tickets_count){
					$next_release_actives = sprintf('<a href="plugin.php?p=litraak&amp;projectid='.$this->rs->post_id.'&amp;ticket_milestone=%s&amp;tab=tickets" title="'.__('This milestone active tickets').'">%s</a>', $this->rs->project_next_release_id, $next_release_actives);
					$next_release_tickets = sprintf('%2$s '.__('on').' %1$s', $next_release_tickets, $next_release_actives);
				}
			}
			
			$next_release.= ' ('.$next_release_tickets.')'.$next_release_progress;
		}
		if($this->rs->hasLastRelease()){
			$last_release = sprintf($milestone_link, $this->rs->project_last_release_id, __('Last release'), $this->rs->last_release_name);
		}		
			
		$tickets = $tickets_count = (integer) $this->rs->project_nb_tickets;
		if($tickets > 0){
			$all_tickets_link = '<a href="plugin.php?p=litraak&amp;projectid='.$this->rs->post_id.'&amp;tab=tickets&amp;ticket_status=" title="'.__('This project tickets').'">%s</a>';
			$tickets = sprintf($all_tickets_link, $tickets);
			$active_tickets = (integer) $this->rs->project_nb_actives;
			if($active_tickets > 0 && $active_tickets != $tickets_count){
				$active_tickets_link = '<a href="plugin.php?p=litraak&amp;projectid='.$this->rs->post_id.'&amp;ticket_status=actives&amp;tab=tickets" title="'.__('This project active tickets').'">%s</a>';
				$active_tickets = sprintf($active_tickets_link, $active_tickets);
				$tickets = sprintf('%2$s ('.__('on').' %1$s)', $tickets, $active_tickets);
			}
		}
		
		$comments_link = '<a href="plugin.php?p=litraak&amp;projectid='.$this->rs->post_id.'&amp;tab=comments" title="'.__('This project comments').'">%s</a>';
		$comments = (integer) $this->rs->nb_comment;
		if($comments > 0){
			$comments = sprintf($comments_link, $comments);
		}
		
		$res .=
		'<td class="nowrap">'.
		form::checkbox(array('entries[]'),$this->rs->post_id,'','','',!$this->rs->isEditable()).'</td>'.
		'<td class="maximal"><a href="plugin.php?p=litraak&amp;projectid='.$this->rs->post_id.'">'.
		html::escapeHTML($this->rs->post_title).'</a></td>'.
		'<td class="nowrap">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$this->rs->post_dt).'</td>'.
		'<td class="nowrap">'.$last_release.'</td>'.
		'<td class="nowrap">'.$next_release.'</td>'.
		'<td class="nowrap">'.$tickets.'</td>'.
		'<td class="nowrap">'.$comments.'</td>'.
		'<td class="nowrap status">'.$img_status.' '.$img_phase.' '.$attach.'</td>'.
		'</tr>';
		
		return $res;
	}
}

class litraakAdminMilestoneList extends adminGenericList
{
	public function display($page,$nb_per_page,$enclose_block='')
	{
		if ($this->rs->isEmpty())
		{
			echo '<p><strong>'.__('No milestone').'</strong></p>';
		}
		else
		{
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);
			$pager->html_prev = $this->html_prev;
			$pager->html_next = $this->html_next;
			$pager->var_page = 'page';
			
			$html_block =
			'<table class="clear" id="milestones-table"><tr>'.
			'<th>'.__('Name').'</th>'.
			'<th>'.__('Date').'</th>'.
			'<th>'.__('Tickets').'</th>'.
			'<th>'.__('Status').'</th>'.
			'<th></th>'.
			'</tr>%s</table>';
			
			if ($enclose_block) {
				$html_block = sprintf($enclose_block,$html_block);
			}
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
			
			$blocks = explode('%s',$html_block);
			
			echo $blocks[0];
			
			while ($this->rs->fetch())
			{
				echo $this->milestoneLine();
			}
			
			echo $blocks[1];
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
		}
	}
	
	private function milestoneLine()
	{
		$milestone_link = '<a href="plugin.php?p=litraak&amp;projectid=%s&amp;milestoneid=%s">%s</a>';
		$milestone_title = sprintf($milestone_link,$this->rs->post_id, $this->rs->milestone_id,
			html::escapeHTML($this->rs->milestone_name));
		$milestone_edit = sprintf($milestone_link,$this->rs->post_id, $this->rs->milestone_id,
			'<img src="images/edit-mini.png" alt="" title="'.__('Edit this milestone').'" />');
		
		$img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
		switch ($this->rs->milestone_status) {
			case litraak::MILESTONE_UNRELEASED:
				$img_status = sprintf($img,__('unreleased'),'scheduled.png');
				break;
			case litraak::MILESTONE_RELEASED:
				$img_status = sprintf($img,__('released'),'check-on.png');
				break;
		}
		
		$ticket_link = '<a href="plugin.php?p=litraak&amp;projectid=%s&amp;tab=tickets&amp;ticket_milestone=%s&amp;ticket_status=%s" title="'.
			__('View those tickets').'">'.
			'%s</a>';
		
		$nb_tickets_int = $nb_tickets = $this->rs->milestone_nb_tickets;
		if($nb_tickets > 0){
			$nb_tickets = sprintf($ticket_link, $this->rs->post_id, $this->rs->milestone_id, '', $nb_tickets);
		}
		
		$progress_bar = '';
		$nb_actives_int = $nb_actives = $this->rs->milestone_nb_actives;
		if($nb_actives > 0){
			if($this->rs->milestone_status == litraak::MILESTONE_UNRELEASED){
				$progress_bar = sprintf('<br />%s', litraakUtils::getProgressBar(litraakUtils::getPercent($nb_tickets_int, $nb_actives)));
			}
			$nb_actives = sprintf($ticket_link, $this->rs->post_id, $this->rs->milestone_id, 'actives', $nb_actives);
		}
		
		$nb_wasted_int = $nb_wasted = $this->rs->milestone_nb_wasted;
		if($nb_wasted > 0){
			$nb_wasted = sprintf($ticket_link, $this->rs->post_id, $this->rs->milestone_id, 'wasted', $nb_wasted);
		}
		
		$nb_closed_int = $nb_closed = ($nb_tickets_int - $nb_actives_int - $nb_wasted_int);
		if($nb_closed > 0){
			$nb_closed = sprintf($ticket_link, $this->rs->post_id, $this->rs->milestone_id, litraak::TICKET_CLOSED, $nb_closed);
		}
		
		$res = '<tr class="line'.($this->rs->milestone_status == litraak::MILESTONE_RELEASED ? ' offline' : '').'"'.
		' id="m'.$this->rs->milestone_id.'">';
		
		$res .=
		'<td class="maximal">'.$milestone_title.'</td>'.
		'<td class="nowrap">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$this->rs->milestone_dt).'</td>'.
		'<td class="nowrap">'.
			$nb_tickets.' '.($nb_tickets_int > 1 ? __('tickets') : __('ticket')).' : '.
			$nb_actives.' '.($nb_actives_int > 1 ? __('actives') : __('active')).', '.
			$nb_closed.' '.($nb_closed_int > 1 ? __('closeds') : __('closed')).', '.
			$nb_wasted.' '.($nb_wasted_int > 1 ? __('wasteds') : __('wasted')).'.'.
			$progress_bar.
		'<td class="nowrap status">'.$img_status.'</td>'.
		'<td class="nowrap status">'.$milestone_edit.'</td>'.
		'</tr>';
		
		return $res;
	}
}

class litraakAdminTicketList extends adminGenericList
{
	public function display($page,$nb_per_page, $show_project=false, $enclose_block='')
	{
		if ($this->rs->isEmpty())
		{
			echo '<p><strong>'.__('No tickets').'</strong></p>';
		}
		else
		{
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);
			$pager->html_prev = $this->html_prev;
			$pager->html_next = $this->html_next;
			$pager->var_page = 'page';
			
			$html_block =
			'<table class="clear" id="tickets-table"><tr>'.
			'<th colspan="2">'.__('Title').'</th>'.
			'<th>'.__('Date').'</th>'.
			(($show_project) ? '<th>'.__('Project').'</th>' : '').
			'<th>'.__('Milestone').'</th>'.
			'<th>'.__('Author').'</th>'.
			'<th>'.__('Type').'</th>'.
			'<th>'.__('Status').'</th>'.
			'<th></th>'.
			'</tr>%s</table>';
			
			if ($enclose_block) {
				$html_block = sprintf($enclose_block,$html_block);
			}
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
			
			$blocks = explode('%s',$html_block);
			
			echo $blocks[0];
			
			while ($this->rs->fetch())
			{
				echo $this->ticketLine($show_project);
			}
			
			echo $blocks[1];
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
		}
	}
	
	private function ticketLine($show_project=false)
	{
		$milestone_link = '<a href="plugin.php?p=litraak&amp;projectid=%s&amp;milestoneid=%s">%s</a>';		
		if ($this->rs->milestone_name) {
			$milestone_title = sprintf($milestone_link,$this->rs->post_id, $this->rs->milestone_id,
			html::escapeHTML($this->rs->milestone_name));
		} else {
			$milestone_title = __('None');
		}
		
		$ticket_link = '<a href="plugin.php?p=litraak'.(($show_project) ? '%s' : '&amp;projectid=%s').'&amp;ticketid=%s">%s</a>';
		$ticket_title = sprintf($ticket_link,(($show_project) ? '' : $this->rs->post_id), $this->rs->ticket_id,
			html::escapeHTML('Ticket #'.$this->rs->ticket_id)).
			' - '.html::escapeHTML($this->rs->ticket_title);
		$ticket_edit = sprintf($ticket_link,(($show_project) ? '' : $this->rs->post_id), $this->rs->ticket_id,
			'<img src="images/edit-mini.png" alt="" title="'.__('Edit this ticket').'" />');
		
		$project_link = '<a href="plugin.php?p=litraak&amp;projectid=%s">%s</a>';		
		$project_title = sprintf($project_link,$this->rs->post_id, html::escapeHTML($this->rs->post_title));
				
		$img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
		switch ($this->rs->ticket_status) {
			case litraak::TICKET_NEW:
				$img_status = sprintf($img,__('new'),'selected.png');
				break;
			case litraak::TICKET_ACCEPTED:
				$img_status = sprintf($img,__('accepted'),'check-on.png');
				break;
			case litraak::TICKET_CLOSED:
				$img_status = sprintf($img,__('closed'),'locker.png');
				break;
			case litraak::TICKET_REJECTED:
				$img_status = sprintf($img,__('rejected'),'junk.png');
				break;
			case litraak::TICKET_DELETED:
				$img_status = sprintf($img,__('deleted'),'check-off.png');
				break;
		}
		
		$img = '<img alt="%1$s" title="%1$s" src="index.php?pf=litraak/img/%2$s" />';
		switch ($this->rs->ticket_type) {
			case litraak::TICKET_BUG:
				$img_type = sprintf($img,__('bug'),'bug.png');
				break;
			case litraak::TICKET_TASK:
				$img_type = sprintf($img,__('task'),'task.png');
				break;
			case litraak::TICKET_IDEA:
				$img_type = sprintf($img,__('idea'),'idea.png');
				break;
		}
		
		$res = '<tr class="line'.(($this->rs->ticket_status >= litraak::TICKET_CLOSED) ? ' offline' : '').'"'.
		' id="t'.$this->rs->ticket_id.'">';
		
		$res .=
		'<td class="nowrap">'.
		form::checkbox(array('tickets[]'),$this->rs->ticket_id,'','','').'</td>'.
		'<td class="maximal">'.$ticket_title.'</td>'.
		'<td class="nowrap">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$this->rs->ticket_upddt).'</td>'.
		(($show_project) ? '<td class="nowrap">'.$project_title.'</td>' : '').
		'<td class="nowrap">'.$milestone_title.'</td>'.
		'<td class="nowrap">'.$this->rs->ticket_author.'</td>'.
		'<td class="nowrap">'.$img_type.'</td>'.
		'<td class="nowrap status">'.$img_status.'</td>'.
		'<td class="nowrap status">'.$ticket_edit.'</td>'.
		'</tr>';
		
		return $res;
	}
}

class litraakAdminTicketChangeList extends adminGenericList
{
	public function display($enclose_block='')
	{
		if ($this->rs->isEmpty())
		{
			echo '<p><strong>'.__('No changes').'</strong></p>';
		}
		else
		{
			$html_block =
			'<table class="clear" id="history"><tr>'.
			'<th colspan="1">'.__('Description').'</th>'.
			'<th>'.__('Date').'</th>'.
			'<th>'.__('Author').'</th>'.
			'<th>'.__('Status').'</th>'.
			'</tr>%s</table>';
			
			if ($enclose_block) {
				$html_block = sprintf($enclose_block,$html_block);
			}
			
			$blocks = explode('%s',$html_block);
			
			echo $blocks[0];
			
			while ($this->rs->fetch())
			{
				echo $this->changeLine();
			}
			
			echo $blocks[1];
		}
	}
	
	private function changeLine()
	{
		$img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
		switch ($this->rs->change_status) {
			case litraak::CHANGE_PRIVATE:
				$img_status = sprintf($img,__('private'),'locker.png');
				break;
			case litraak::CHANGE_PUBLIC:
				$img_status = sprintf($img,__('public'),'check-on.png');
				break;
		}
		
		$delete = sprintf('<a href="%1$s" title="%2$s"><img src="images/trash.png" alt="%2$s" /></a>',
			'plugin.php?p=litraak&amp;projectid='.$this->rs->post_id.'&amp;ticketid='.$this->rs->ticket_id.'&amp;delete='.$this->rs->change_id, 
			__('Delete'));
				
		$res = '<tr class="line'.($this->rs->change_status == litraak::CHANGE_PRIVATE ? ' offline' : '').'"'.
		' id="p'.$this->rs->change_id.'">';
		
		$res .=
		'<td class="maximal"><p class="ticket-change-title">'.$this->rs->getTitle().
			(($this->rs->hasComment())?' :</p><div class="ticket-change-comment">'.
			$this->rs->getComment().'</div>':'</p>').'</td>'.
		'<td class="nowrap">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$this->rs->change_dt).'</td>'.
		'<td class="nowrap"><acronym title="'.$this->rs->change_email.'">'.$this->rs->change_author.'</acronym></td>'.
		'<td class="nowrap status">'.$img_status.' '.$delete.'</td>'.
		'</tr>';
		
		return $res;
	}
}

?>