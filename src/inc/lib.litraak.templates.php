<?php
//@@licence@@

class litraakTemplates
{

	public static function LitraakURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$litraak->getPublicURL()').'; ?>';
	}

	public static function LitraakFeedURL($attr)
	{
		$type = !empty($attr['type']) ? $attr['type'] : 'atom';

		if (!preg_match('#^(rss2|atom)$#',$type)) {
			$type = 'atom';
		}

		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$litraak->getPublicURL()."feed/'.$type.'/projects"').'; ?>';
	}

	public static function LitraakTicketsFeedURL($attr)
	{
		$type = !empty($attr['type']) ? $attr['type'] : 'atom';

		if (!preg_match('#^(rss2|atom)$#',$type)) {
			$type = 'atom';
		}

		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php if($_ctx->posts && $_ctx->posts->count() == 1){
		echo '.sprintf($f,'$litraak->getProjectPublicURL($_ctx->posts->post_url)."feed/'.$type.'/tickets"').'; 
		}else{
		echo '.sprintf($f,'$litraak->getPublicURL()."feed/'.$type.'/tickets"').'; 
		}?>';
	}

	public static function LitraakReleasesFeedURL($attr)
	{
		$type = !empty($attr['type']) ? $attr['type'] : 'atom';

		if (!preg_match('#^(rss2|atom)$#',$type)) {
			$type = 'atom';
		}

		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php if($_ctx->posts && $_ctx->posts->count() == 1){
		echo '.sprintf($f,'$litraak->getProjectPublicURL($_ctx->posts->post_url)."feed/'.$type.'/releases"').'; 
		}else{
		echo '.sprintf($f,'$litraak->getPublicURL()."feed/'.$type.'/releases"').'; 
		}?>';
	}


	public static function LitraakProjects($attr,$content) {
		$lastn = 0;
		if (isset($attr['lastn'])) {
			$lastn = abs((integer) $attr['lastn'])+0;
		}

		$p = 'if (!isset($_page_number)) { $_page_number = 1; }'."\n";

		if ($lastn > 0) {
			$p .= "\$params['limit'] = ".$lastn.";\n";
		} else {
			$p .= "\$params['limit'] = \$_ctx->nb_entry_per_page;\n";
		}

		if (!isset($attr['ignore_pagination']) || $attr['ignore_pagination'] == "0") {
			$p .= "\$params['limit'] = array(((\$_page_number-1)*\$params['limit']),\$params['limit']);\n";
		} else {
			$p .= "\$params['limit'] = array(0, \$params['limit']);\n";
		}

		if (!empty($attr['url'])) {
			$p .= "\$params['post_url'] = '".addslashes($attr['url'])."';\n";
		}

		if (empty($attr['no_context']))
		{
			$p .=
			'if ($_ctx->exists("users")) { '.
				"\$params['user_id'] = \$_ctx->users->user_id; ".
			"}\n";

			$p .=
			'if ($_ctx->exists("categories")) { '.
				"\$params['cat_id'] = \$_ctx->categories->cat_id; ".
			"}\n";

			$p .=
			'if ($_ctx->exists("archives")) { '.
				"\$params['post_year'] = \$_ctx->archives->year(); ".
				"\$params['post_month'] = \$_ctx->archives->month(); ".
				"unset(\$params['limit']); ".
			"}\n";

			$p .=
			'if ($_ctx->exists("langs")) { '.
				"\$params['post_lang'] = \$_ctx->langs->post_lang; ".
			"}\n";

			$p .=
			'if (isset($_search)) { '.
				"\$params['search'] = \$_search; ".
			"}\n";
		}

		$sortby = 'post_title';
		$order = 'asc';
		if (isset($attr['sortby'])) {
			switch ($attr['sortby']) {
				case 'name': $sortby = 'post_title'; break;
				case 'date' : $sortby = 'post_dt'; break;
				case 'id' : $sortby = 'post_id'; break;
			}
		}
		if (isset($attr['order']) && preg_match('/^(desc|asc)$/i',$attr['order'])) {
			$order = $attr['order'];
		}

		$p .= "\$params['order'] = '".$sortby." ".$order."';\n";

		if (isset($attr['no_content']) && $attr['no_content']) {
			$p .= "\$params['no_content'] = true;\n";
		}

		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->post_params = $params;'."\n";
		$res .= '$_ctx->posts = $litraak->getProjects($params); unset($params);'."\n";
		$res .= "?>\n";

		$res .=
		'<?php while ($_ctx->posts->fetch()) : ?>'.$content.'<?php endwhile; '.
		'$_ctx->posts = null; $_ctx->post_params = null; ?>';

		return $res;
	}

	public static function LitraakProjectAttachmentCount($attr)
	{
		$none = 'no attachment';
		$one = 'one attachment';
		$more = '%d attachments';

		if (isset($attr['none'])) {
			$none = addslashes($attr['none']);
		}
		if (isset($attr['one'])) {
			$one = addslashes($attr['one']);
		}
		if (isset($attr['more'])) {
			$more = addslashes($attr['more']);
		}

		return
		"<?php if (\$_ctx->posts->countMedia() == 0) {\n".
		"  printf(__('".$none."'),(integer) \$_ctx->posts->countMedia());\n".
		"} elseif (\$_ctx->posts->countMedia() == 1) {\n".
		"  printf(__('".$one."'),(integer) \$_ctx->posts->countMedia());\n".
		"} else {\n".
		"  printf(__('".$more."'),(integer) \$_ctx->posts->countMedia());\n".
		"} ?>";
	}

	public static function LitraakProjectsFooter($attr,$content) { return $GLOBALS['core']->tpl->EntriesFooter($attr,$content); }
	public static function LitraakProjectsHeader($attr,$content) { return $GLOBALS['core']->tpl->EntriesHeader($attr,$content); }
	public static function LitraakProjectDescription($attr,$content) { return $GLOBALS['core']->tpl->EntryContent($attr); }
	public static function LitraakProjectAuthorCommonName($attr) { return $GLOBALS['core']->tpl->EntryAuthorCommonName($attr); }
	public static function LitraakProjectAuthorDisplayName($attr) { return $GLOBALS['core']->tpl->EntryAuthorDisplayName($attr); }
	public static function LitraakProjectBasename($attr) { return $GLOBALS['core']->tpl->EntryBasename($attr); }
	public static function LitraakProjectCommentCount($attr) { return $GLOBALS['core']->tpl->EntryCommentCount($attr); }
	public static function LitraakProjectDocumentation($attr) { return $GLOBALS['core']->tpl->EntryExcerpt($attr); }
	public static function LitraakProjectDate($attr) { return $GLOBALS['core']->tpl->EntryDate($attr); }
	public static function LitraakProjectFeedID($attr) { return $GLOBALS['core']->tpl->EntryFeedID($attr); }
	public static function LitraakProjectID($attr) { return $GLOBALS['core']->tpl->EntryID($attr); }
	public static function LitraakProjectIfFirst($attr) { return $GLOBALS['core']->tpl->EntryIfFirst($attr); }
	public static function LitraakProjectIfOdd($attr) { return $GLOBALS['core']->tpl->EntryIfOdd($attr); }
	public static function LitraakProjectLang($attr) { return $GLOBALS['core']->tpl->EntryLang($attr); }
	public static function LitraakProjectName($attr) { return $GLOBALS['core']->tpl->EntryTitle($attr); }
	public static function LitraakProjectTime($attr) { return $GLOBALS['core']->tpl->EntryTime($attr); }
	public static function LitraakProjectURL($attr) { return $GLOBALS['core']->tpl->EntryURL($attr); }

	public static function LitraakProjectIf($attr,$content) {
		$if = array();
		$operator = isset($attr['operator']) ? $this->getOperator($attr['operator']) : '&&';

		if (isset($attr['tickets_active'])) {
			$sign = (boolean) $attr['tickets_active'] ? '' : '!';
			$if[] = $sign.'$_ctx->posts->ticketsActive()';
		}

		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$GLOBALS['core']->tpl->EntryIf($attr,$content).'<?php endif; ?>';
		} else {
			return $GLOBALS['core']->tpl->EntryIf($attr,$content);
		}
	}

	public static function LitraakProjectFirstImage($attr)
	{
		$size = !empty($attr['size']) ? $attr['size'] : '';
		$class = !empty($attr['class']) ? $attr['class'] : '';
		$with_category = !empty($attr['with_category']) ? 'true' : 'false';

		return "<?php echo litraakUtils::ProjectFirstImageHelper('".addslashes($size)."','".addslashes($class)."'); ?>";
	}

	public static function LitraakProjectPhase($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$litraak->getProjectPhase($_ctx->posts->project_phase)').'; ?>';
	}

	public static function LitraakProjectLastReleaseName($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->last_release_name').'; ?>';
	}

	public static function LitraakProjectLastReleaseURL($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$litraak->getMilestonePublicURL($_ctx->posts->post_url, $_ctx->posts->last_release_url)').'; ?>';
	}

	public static function LitraakProjectLastReleaseDate($attr) {
		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}

		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,"\$_ctx->posts->getLastReleaseDate('".$format."')").'; ?>';
	}

	public static function LitraakProjectIfLastRelease($attr, $content) {
		$sign = (isset($attr['not']) && (boolean) $attr['not']) ? '!' : '';
		return '<?php if('.$sign.'($_ctx->posts != null && $_ctx->posts->hasLastRelease())) : ?>'.$content.'<?php endif; ?>';
	}

	public static function LitraakProjectNextReleaseName($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->next_release_name').'; ?>';
	}

	public static function LitraakProjectNextReleaseURL($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$litraak->getMilestonePublicURL($_ctx->posts->post_url, $_ctx->posts->next_release_url)').'; ?>';
	}

	public static function LitraakProjectNextReleaseDate($attr) {
		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}

		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,"\$_ctx->posts->getNextReleaseDate('".$format."')").'; ?>';
	}

	public static function LitraakProjectIfNextRelease($attr, $content) {
		$sign = (isset($attr['not']) && (boolean) $attr['not']) ? '!' : '';
		return '<?php if('.$sign.'($_ctx->posts != null && $_ctx->posts->hasNextRelease())) : ?>'.$content.'<?php endif; ?>';
	}

	public static function LitraakComments($attr,$content)
	{
		$p =
		"if (\$_ctx->posts !== null) { ".
			"\$params['post_id'] = \$_ctx->posts->post_id; ".
		"}\n";

		$lastn = 0;
		if (isset($attr['lastn'])) {
			$lastn = abs((integer) $attr['lastn'])+0;
		}

		if ($lastn > 0) {
			$p .= "\$params['limit'] = ".$lastn.";\n";
		} else {
			$p .= "if (\$_ctx->nb_comment_per_page !== null) { \$params['limit'] = \$_ctx->nb_comment_per_page; }\n";
		}

		if (empty($attr['no_context']))
		{
			$p .=
			'if ($_ctx->exists("langs")) { '.
				"\$params['sql'] = \"AND P.post_lang = '\".\$core->blog->con->escape(\$_ctx->langs->post_lang).\"' \"; ".
			"}\n";
		}

		$order = 'asc';
		if (isset($attr['order']) && preg_match('/^(desc|asc)$/i',$attr['order'])) {
			$order = $attr['order'];
		}

		$p .= "\$params['order'] = 'comment_dt ".$order."';\n";

		if (isset($attr['no_content']) && $attr['no_content']) {
			$p .= "\$params['no_content'] = true;\n";
		}

		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->comments = $litraak->getComments($params); unset($params);'."\n";

		$res .= "?>\n";

		$res .=
		'<?php while ($_ctx->comments->fetch()) : ?>'.$content.'<?php endwhile; $_ctx->comments = null; ?>';

		return $res;
	}

	public static function LitraakMilestones($attr, $content) {
		$p = '';

		if (!empty($attr['released'])) {
			$p .= "\$params['milestone_status'] = '".(($attr['released'])? litraak::MILESTONE_RELEASED : litraak::MILESTONE_UNRELEASED)."';\n";
		}

		if (!empty($attr['id'])) {
			$p .= "\$params['milestone_id'] = '".addslashes($attr['id'])."';\n";
		}

		$lastn = 0;
		if (isset($attr['lastn'])) {
			$lastn = abs((integer) $attr['lastn'])+0;
		}

		if ($lastn > 0) {
			$p .= "\$params['limit'] = ".$lastn.";\n";
		} else {
			$p .= "if (\$_ctx->nb_milestones_per_page !== null) { \$params['limit'] = \$_ctx->nb_milestones_per_page; }\n";
		}

		$sortby = 'milestone_dt';
		$order = 'desc';
		if (isset($attr['sortby'])) {
			switch ($attr['sortby']) {
				case 'name': $sortby = 'milestone_name'; break;
				case 'date' : $sortby = 'milestone_dt'; break;
				case 'id' : $sortby = 'milestone_id'; break;
				case 'status' : $sortby = 'milestone_status'; break;
			}
		}
		if (isset($attr['order']) && preg_match('/^(desc|asc)$/i',$attr['order'])) {
			$order = $attr['order'];
		}

		$p .= "\$params['order'] = '".$sortby." ".$order."';\n";

		if (isset($attr['no_content']) && $attr['no_content']) {
			$p .= "\$params['no_content'] = true;\n";
		}

		if (empty($attr['no_context']))
		{
			$p .=
			'if ($_ctx->exists("posts")) { '.
				"\$params['post_id'] = \$_ctx->posts->post_id; ".
			"}\n";

			$p .=
			'if ($_ctx->exists("milestone_status")) { '.
				"\$params['milestone_status'] = \$_ctx->milestone_status; ".
				"\$params['order'] = 'milestone_dt '.((\$_ctx->milestone_status == ".litraak::MILESTONE_UNRELEASED.")?'asc':'desc');".
			"}\n";
		}

		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->milestone_params = $params;'."\n";
		$res .= '$_ctx->milestones = $litraak->getMilestones($params); unset($params);'."\n";
		$res .= "?>\n";

		$res .=
		'<?php while ($_ctx->milestones->fetch()) : ?>'.$content.'<?php endwhile; '.
		'$_ctx->milestones = null; $_ctx->milestone_params = null; ?>';

		return $res;
	}

	public static function LitraakMilestonesHeader($attr,$content)
	{
		return self::GenericHeader($attr,$content,'milestones');
	}

	public static function LitraakMilestonesFooter($attr,$content)
	{
		return self::GenericFooter($attr,$content,'milestones');
	}

	public static function LitraakMilestoneIf($attr, $content) {
		$if = array();

		$operator = isset($attr['operator']) ? $this->getOperator($attr['operator']) : '&&';

		if (isset($attr['id'])) {
			$id = trim($attr['id']);
			if (substr($id,0,1) == '!') {
				$id = substr($id,1);
				$if[] = '$_ctx->milestones->milestone_id != "'.addslashes($id).'"';
			} else {
				$if[] = '$_ctx->milestones->milestone_id == "'.addslashes($id).'"';
			}
		}

		if (isset($attr['first'])) {
			$sign = (boolean) $attr['first'] ? '=' : '!';
			$if[] = '$_ctx->milestones->index() '.$sign.'= 0';
		}

		if (isset($attr['odd'])) {
			$sign = (boolean) $attr['odd'] ? '=' : '!';
			$if[] = '($_ctx->milestones->index()+1)%2 '.$sign.'= 1';
		}

		if (isset($attr['released'])) {
			$sign = (boolean) $attr['released'] ? '' : '!';
			$if[] = $sign.'$_ctx->milestones->isReleased()';
		}

		if (isset($attr['has_attachment'])) {
			$sign = (boolean) $attr['has_attachment'] ? '' : '!';
			$if[] = $sign.'$_ctx->milestones->countMedia()';
		}

		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}

	public static function LitraakMilestoneURL($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$litraak->getMilestonePublicURL($_ctx->milestones->post_url, $_ctx->milestones->milestone_url)').'; ?>';
	}

	public static function LitraakMilestoneName($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->milestones->milestone_name').'; ?>';
	}

	public static function LitraakMilestoneDescription($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->milestones->milestone_desc').'; ?>';
	}

	public static function LitraakMilestoneDate($attr) {
		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}

		$iso8601 = !empty($attr['iso8601']);
		$rfc822 = !empty($attr['rfc822']);

		$f = $GLOBALS['core']->tpl->getFilters($attr);

		if ($rfc822) {
			return '<?php echo '.sprintf($f,"\$_ctx->milestones->getRFC822Date()").'; ?>';
		} elseif ($iso8601) {
			return '<?php echo '.sprintf($f,"\$_ctx->milestones->getISO8601Date()").'; ?>';
		} else {
			return '<?php echo '.sprintf($f,"\$_ctx->milestones->getDate('".$format."')").'; ?>';
		}
	}

	public static function LitraakMilestoneStatus($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$litraak->getMilestoneStatus($_ctx->milestones->milestone_status)').'; ?>';
	}

	public static function LitraakMilestoneFeedID($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->milestones->getFeedID()').'; ?>';
	}

	public static function LitraakMilestoneIfFirst($attr)
	{
		return self::GenericIfFirst($attr, 'milestones');
	}

	public static function LitraakMilestoneIfOdd($attr)
	{
		return self::GenericIfOdd($attr, 'milestones');
	}

	public static function LitraakMilestoneAttachments($attr,$content)
	{
		$ifs = array();
		if (!empty($attr['ext'])) {
			$ifs[] = 'substr($attach_f->file_url, -'.(strlen($attr['ext']) + 1).') == ".'.$attr['ext'].'"';
		}

		$res =
		"<?php\n".
		'if ($_ctx->milestones !== null && $litraak->media) {'."\n".
			'$_ctx->attachments = new ArrayObject($litraak->media->getPostMedia($_ctx->milestones->milestone_id));'."\n".
		"?>\n".

		'<?php foreach ($_ctx->attachments as $attach_i => $attach_f) : '.
		'$GLOBALS[\'attach_i\'] = $attach_i; $GLOBALS[\'attach_f\'] = $attach_f;'.
		'$_ctx->file_url = $attach_f->file_url; ?>';

		if(count($ifs) > 0){
			$res .= '<?php if('.join(" && ", $ifs).') { ?>'.$content.'<?php } ?>';
		}else{
			$res .=	$content;
		}

		$res .=
		'<?php endforeach; $_ctx->attachments = null; unset($attach_i,$attach_f,$_ctx->file_url); ?>'.
		"<?php } ?>\n";

		return $res;
	}

	public static function LitraakMilestoneAttachmentCount($attr)
	{
		$none = 'no attachment';
		$one = 'one attachment';
		$more = '%d attachments';

		if (isset($attr['none'])) {
			$none = addslashes($attr['none']);
		}
		if (isset($attr['one'])) {
			$one = addslashes($attr['one']);
		}
		if (isset($attr['more'])) {
			$more = addslashes($attr['more']);
		}

		return
		"<?php if (\$_ctx->milestones->countMedia() == 0) {\n".
		"  printf(__('".$none."'),(integer) \$_ctx->milestones->countMedia());\n".
		"} elseif (\$_ctx->milestones->countMedia() == 1) {\n".
		"  printf(__('".$one."'),(integer) \$_ctx->milestones->countMedia());\n".
		"} else {\n".
		"  printf(__('".$more."'),(integer) \$_ctx->milestones->countMedia());\n".
		"} ?>";
	}

	public static function LitraakMilestoneProgressBar($attr)
	{
		$res =
		"<?php\n".
		'if ($_ctx->milestones !== null) {'."\n".
		'echo litraakUtils::getProgressBar($_ctx->milestones->getProgress(), ($_ctx->milestones->getTicketNumber() > 0)? $_ctx->milestones->getProgress()." %": "-");'.
		"} ?>\n";

		return $res;
	}

	public static function LitraakMilestoneTicketNumber($attr)
	{
		$none = '%d ticket';
		$one = '%d ticket';
		$more = '%d tickets';

		if (isset($attr['none'])) {
			$none = addslashes($attr['none']);
		}
		if (isset($attr['one'])) {
			$one = addslashes($attr['one']);
		}
		if (isset($attr['more'])) {
			$more = addslashes($attr['more']);
		}

		$type = '';
		if (isset($attr['type'])) {
			$type = addslashes($attr['type']);
		}

		return
		"<?php if (\$_ctx->milestones->getTicketNumber('".$type."') == 0) {\n".
		"  printf(__('".$none."'),(integer) \$_ctx->milestones->getTicketNumber('".$type."'));\n".
		"} elseif (\$_ctx->milestones->getTicketNumber('".$type."') == 1) {\n".
		"  printf(__('".$one."'),(integer) \$_ctx->milestones->getTicketNumber('".$type."'));\n".
		"} else {\n".
		"  printf(__('".$more."'),(integer) \$_ctx->milestones->getTicketNumber('".$type."'));\n".
		"} ?>";
	}

	public static function LitraakMilestoneProjectName($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->milestones->post_title').'; ?>';
	}

	public static function LitraakMilestoneProjectURL($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$litraak->getProjectPublicURL($_ctx->milestones->post_url)').'; ?>';
	}

	public static function LitraakTickets($attr, $content) {
		$p = '';

		// TODO géréer des noms 'alias' pour les statuts
		if (!empty($attr['status'])) {
			$p .= "\$params['ticket_status'] = '".addslashes($attr['status'])."';\n";
		}

		$p .= "\$params['ticket_status_not'] = '".(!empty($attr['status_not'])?addslashes($attr['status_not']):litraak::TICKET_DELETED)."';\n";

		if (!empty($attr['id'])) {
			$p .= "\$params['ticket_id'] = '".addslashes($attr['id'])."';\n";
		}

		$lastn = 0;
		if (isset($attr['lastn'])) {
			$lastn = abs((integer) $attr['lastn'])+0;
		}

		if ($lastn > 0) {
			$p .= "\$params['limit'] = ".$lastn.";\n";
		} else {
			$p .= "if (\$_ctx->nb_tickets_per_page !== null) { \$params['limit'] = \$_ctx->nb_tickets_per_page; }\n";
		}

		$p .= 'if ($_ctx->exists("milestones")) { '.
				"\$params['milestone_id'] = \$_ctx->milestones->milestone_id; ".
			"}\n";
		$p .= "\$params['post_id'] = \$_ctx->posts->post_id;\n";

		if (!empty($attr['no_milestone'])) {
			if((boolean) $attr['no_milestone']){
				$p .= "\$params['no_milestone'] = '1';\n";
			}else{
				$p .= "\$params['with_milestone'] = '1';\n";
			}
		}

		if (empty($attr['no_context']))
		{
			$p .=
			'if ($_ctx->exists("posts")) { '.
				"\$params['post_id'] = \$_ctx->posts->post_id; ".
			"}\n";

			$p .=
			'if ($_ctx->exists("milestones")) { '.
				"\$params['milestone_id'] = \$_ctx->milestones->milestone_id; ".
			"}\n";
		}

		$sortby = 'ticket_status';
		$order = 'asc';
		if (isset($attr['sortby'])) {
			switch ($attr['sortby']) {
				case 'status': $sortby = 'ticket_status'; break;
				case 'title' : $sortby = 'ticket_title'; break;
				case 'date' : $sortby = 'ticket_upddt'; break;
				case 'id' : $sortby = 'ticket_id'; break;
			}
		}
		if (isset($attr['order']) && preg_match('/^(desc|asc)$/i',$attr['order'])) {
			$order = $attr['order'];
		}

		$p .= "\$params['order'] = '".$sortby." ".$order."';\n";

		if (isset($attr['no_content']) && $attr['no_content']) {
			$p .= "\$params['no_content'] = true;\n";
		}

		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->ticket_params = $params;'."\n";
		$res .= '$_ctx->tickets = $litraak->getTickets($params); unset($params);'."\n";
		$res .= "?>\n";

		$res .=
		'<?php while ($_ctx->tickets->fetch()) : ?>'.$content.'<?php endwhile; '.
		'$_ctx->tickets = null; $_ctx->ticket_params = null; ?>';

		return $res;
	}

	public static function LitraakTicketsHeader($attr,$content)
	{
		return self::GenericHeader($attr,$content,'tickets');
	}

	public static function LitraakTicketsFooter($attr,$content)
	{
		return self::GenericFooter($attr,$content,'tickets');
	}

	public static function LitraakTicketIf($attr, $content) {
		$if = array();

		$operator = isset($attr['operator']) ? $this->getOperator($attr['operator']) : '&&';

		if (isset($attr['id'])) {
			$id = trim($attr['id']);
			if (substr($id,0,1) == '!') {
				$id = substr($id,1);
				$if[] = '$_ctx->tickets->ticket_id != "'.addslashes($id).'"';
			} else {
				$if[] = '$_ctx->tickets->ticket_id == "'.addslashes($id).'"';
			}
		}

		if (isset($attr['first'])) {
			$sign = (boolean) $attr['first'] ? '=' : '!';
			$if[] = '$_ctx->tickets->index() '.$sign.'= 0';
		}

		if (isset($attr['exists'])) {
			$sign = (boolean) $attr['exists'] ? '' : '!';
			$if[] = $sign.'($_ctx->tickets != null && !$_ctx->tickets->isEmpty())';
		}

		if (isset($attr['has_milestone'])) {
			$sign = (boolean) $attr['has_milestone'] ? '' : '!';
			$if[] = $sign.'$_ctx->tickets->hasMilestone()';
		}

		if (isset($attr['odd'])) {
			$sign = (boolean) $attr['odd'] ? '=' : '!';
			$if[] = '($_ctx->tickets->index()+1)%2 '.$sign.'= 1';
		}

		if (isset($attr['type'])) {
			$sign = (boolean) $attr['type'] ? '' : '!';
			$if[] = $sign.'$_ctx->tickets->isType("'.addslashes($attr['type']).'")';
		}

		if (isset($attr['comments_active'])) {
			$sign = (boolean) $attr['comments_active'] ? '' : '!';
			$if[] = $sign.'($_ctx->tickets != null && $_ctx->tickets->ticket_open_comment)';
		}

		if (isset($attr['status'])) {
			$sign = '';
			$status = trim($attr['status']);
			if (substr($status,0,1) == '!') {
				$status = substr($status,1);
				$sign = '!';
			}
			$if[] = $sign.'$_ctx->tickets->isStatus("'.addslashes($status).'")';
		}

		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}

	public static function LitraakTicketIfExists($attr, $content) {
		$sign = (isset($attr['not']) && (boolean) $attr['not']) ? '!' : '';
		return '<?php if('.$sign.'($_ctx->tickets != null && !$_ctx->tickets->isEmpty())) : ?>'.$content.'<?php endif; ?>';
	}

	public static function LitraakTicketIfDeleted($attr, $content) {
		$sign = (isset($attr['not']) && (boolean) $attr['not']) ? '!' : '';
		return '<?php if('.$sign.'($_ctx->tickets != null && !$_ctx->tickets->isEmpty() && $_ctx->tickets->ticket_status == '.litraak::TICKET_DELETED.')) : ?>'.$content.'<?php endif; ?>';
	}

	public static function LitraakTicketId($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->tickets->ticket_id').'; ?>';
	}

	public static function LitraakTicketURL($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$litraak->getTicketPublicURL($_ctx->tickets->post_url, $_ctx->tickets->ticket_id)').'; ?>';
	}

	public static function LitraakTicketTitle($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->tickets->ticket_title').'; ?>';
	}

	public static function LitraakTicketDescription($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->tickets->ticket_desc').'; ?>';
	}

	public static function LitraakTicketAuthor($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->tickets->ticket_author').'; ?>';
	}

	public static function LitraakTicketDate($attr) {
		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}

		$iso8601 = !empty($attr['iso8601']);
		$rfc822 = !empty($attr['rfc822']);

		$f = $GLOBALS['core']->tpl->getFilters($attr);

		if ($rfc822) {
			return '<?php echo '.sprintf($f,"\$_ctx->tickets->getRFC822Date()").'; ?>';
		} elseif ($iso8601) {
			return '<?php echo '.sprintf($f,"\$_ctx->tickets->getISO8601Date()").'; ?>';
		} else {
			return '<?php echo '.sprintf($f,"\$_ctx->tickets->getDate('".$format."')").'; ?>';
		}
	}

	public static function LitraakTicketFeedID($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->tickets->getFeedID()').'; ?>';
	}

	public static function LitraakTicketStatus($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$litraak->getTicketStatus($_ctx->tickets->ticket_status)').'; ?>';
	}

	public static function LitraakTicketIfFirst($attr)
	{
		return self::GenericIfFirst($attr, 'tickets');
	}

	public static function LitraakTicketIfOdd($attr)
	{
		return self::GenericIfOdd($attr, 'tickets');
	}

	public static function LitraakTicketType($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$litraak->getTicketType($_ctx->tickets->ticket_type)').'; ?>';
	}

	public static function LitraakTicketMilestoneURL($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$litraak->getMilestonePublicURL($_ctx->posts->post_url, $_ctx->tickets->milestone_url)').'; ?>';
	}

	public static function LitraakTicketMilestoneName($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->tickets->milestone_name').'; ?>';
	}

	public static function LitraakTicketProjectName($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->tickets->post_title').'; ?>';
	}

	public static function LitraakTicketProjectURL($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$litraak->getProjectPublicURL($_ctx->tickets->post_url)').'; ?>';
	}

	// ### TICKET PREVIEW ######################################################

	public static function IfTicketPreview($attr,$content)
	{
		return
		'<?php if ($_ctx->ticket_preview !== null && $_ctx->ticket_preview["preview"]) : ?>'.
		$content.
		'<?php endif; ?>';
	}

	public static function TicketPreviewName($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->ticket_preview["name"]').'; ?>';
	}

	public static function TicketPreviewEmail($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->ticket_preview["mail"]').'; ?>';
	}

	public static function TicketPreviewTitle($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->ticket_preview["title"]').'; ?>';
	}

	public static function TicketPreviewDescription($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		if (!empty($attr['raw'])) {
			$co = '$_ctx->ticket_preview["rawdesc"]';
		} else {
			$co = '$_ctx->ticket_preview["desc"]';
		}

		return '<?php echo '.sprintf($f,$co).'; ?>';
	}

	public static function TicketPreviewCheckRemember($attr)
	{
		return
		"<?php if (\$_ctx->ticket_preview['remember']) { echo ' checked=\"checked\"'; } ?>";
	}

	// ### TICKET CHANGES ######################################################

	public static function LitraakTicketChanges($attr, $content) {

		$sortby = 'change_dt';
		$order = 'asc';
		if (isset($attr['order']) && preg_match('/^(desc|asc)$/i',$attr['order'])) {
			$order = $attr['order'];
		}

		$p_order = "'order' => '".$sortby." ".$order."'";

		$res =
		"<?php\n".
		'if ($_ctx->tickets !== null && $litraak) {'."\n".
			'$_ctx->ticket_changes = $litraak->getTicketChanges(array("ticket_id" => $_ctx->tickets->ticket_id, '.$p_order.'));'."\n".
		"?>\n".

		'<?php while ($_ctx->ticket_changes->fetch()) : ?>'.$content.'<?php endwhile; '.
		'$_ctx->ticket_changes = null; ?>'.

		"<?php } ?>\n";

		return $res;
	}

	public static function LitraakTicketChangesHeader($attr,$content)
	{
		return self::GenericHeader($attr,$content,'ticket_changes');
	}

	public static function LitraakTicketChangesFooter($attr,$content)
	{
		return self::GenericFooter($attr,$content,'ticket_changes');
	}

	public static function LitraakTicketChangeIf($attr, $content) {
		$if = array();

		$operator = isset($attr['operator']) ? $this->getOperator($attr['operator']) : '&&';

		if (isset($attr['first'])) {
			$sign = (boolean) $attr['first'] ? '=' : '!';
			$if[] = '$_ctx->ticket_changes->index() '.$sign.'= 0';
		}

		if (isset($attr['has_comment'])) {
			$sign = (boolean) $attr['has_comment'] ? '' : '!';
			$if[] = $sign.'$_ctx->ticket_changes->hasComment()';
		}

		if (isset($attr['odd'])) {
			$sign = (boolean) $attr['odd'] ? '=' : '!';
			$if[] = '($_ctx->ticket_changes->index()+1)%2 '.$sign.'= 1';
		}

		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}

	public static function LitraakTicketChangeTitle($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->ticket_changes->getTitle()').'; ?>';
	}

	public static function LitraakTicketChangeDescription($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->ticket_changes->change_comment').'; ?>';
	}

	public static function LitraakTicketChangeAuthor($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->ticket_changes->change_author').'; ?>';
	}

	public static function LitraakTicketChangeDate($attr) {
		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}

		$iso8601 = !empty($attr['iso8601']);
		$rfc822 = !empty($attr['rfc822']);

		$f = $GLOBALS['core']->tpl->getFilters($attr);

		if ($rfc822) {
			return '<?php echo '.sprintf($f,"\$_ctx->ticket_changes->getRFC822Date()").'; ?>';
		} elseif ($iso8601) {
			return '<?php echo '.sprintf($f,"\$_ctx->ticket_changes->getISO8601Date()").'; ?>';
		} else {
			return '<?php echo '.sprintf($f,"\$_ctx->ticket_changes->getDate('".$format."')").'; ?>';
		}
	}

	public static function LitraakTicketChangeIfFirst($attr)
	{
		return self::GenericIfFirst($attr, 'ticket_changes');
	}

	public static function LitraakTicketChangeIfOdd($attr)
	{
		return self::GenericIfOdd($attr, 'ticket_changes');
	}

	// ### TICKET CHANGE PREVIEW ######################################################

	public static function IfTicketChangePreview($attr,$content)
	{
		return
		'<?php if ($_ctx->ticket_change_preview !== null && $_ctx->ticket_change_preview["preview"]) : ?>'.
		$content.
		'<?php endif; ?>';
	}

	public static function TicketChangePreviewName($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->ticket_change_preview["name"]').'; ?>';
	}

	public static function TicketChangePreviewEmail($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->ticket_change_preview["mail"]').'; ?>';
	}

	public static function TicketChangePreviewDescription($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		if (!empty($attr['raw'])) {
			$co = '$_ctx->ticket_change_preview["rawdesc"]';
		} else {
			$co = '$_ctx->ticket_change_preview["desc"]';
		}

		return '<?php echo '.sprintf($f,$co).'; ?>';
	}

	public static function TicketChangePreviewCheckRemember($attr)
	{
		return
		"<?php if (\$_ctx->ticket_change_preview['remember']) { echo ' checked=\"checked\"'; } ?>";
	}

	// ### HELPERS #############################################################

	protected function getOperator($op)
	{
		switch (strtolower($op))
		{
			case 'or':
			case '||':
				return '||';
			case 'and':
			case '&&':
			default:
				return '&&';
		}
	}

	private static function GenericIfFirst($attr, $ctx_object)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'first';
		$ret = html::escapeHTML($ret);

		return
		'<?php if ($_ctx->'.$ctx_object.'->index() == 0) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}

	private static function GenericIfOdd($attr, $ctx_object)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'odd';
		$ret = html::escapeHTML($ret);

		return
		'<?php if (($_ctx->'.$ctx_object.'->index()+1)%2 == 1) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}

	private static function GenericHeader($attr,$content, $ctx_object)
	{
		return
		"<?php if (\$_ctx->".$ctx_object."->isStart()) : ?>".
		$content.
		"<?php endif; ?>";
	}

	private static function GenericFooter($attr,$content, $ctx_object)
	{
		return
		"<?php if (\$_ctx->".$ctx_object."->isEnd()) : ?>".
		$content.
		"<?php endif; ?>";
	}


}
?>