<?php
//@@licence@@

class litraakUtils
{

	const PROJECT_DEFAULT_EDIT_SIZE = 10;

	public static function getOrderCombo()
	{
		return array(
		__('Descending') => 'desc',
		__('Ascending') => 'asc'
		);
	}

	public static function getProjectsCombo($litraak)
	{
		$combo = array(
		'-' => ''
		);
		$rs = $litraak->getProjects(array('no_content' => true, 'order' => 'post_title asc'));
		while ($rs->fetch())
		{
			$combo[$rs->post_title] = (string) $rs->post_id;
		}
		return $combo;
	}

	public static function getMilestonesCombo($litraak, $project_id)
	{
		$combo = array(
		'-' => ''
		);
		$rs = $litraak->getMilestones(array('post_id' => $project_id, 'no_content' => true, 'order' => 'milestone_name asc'));
		while ($rs->fetch())
		{
			$combo[$rs->milestone_name] = (string) $rs->milestone_id;
		}
		return $combo;
	}

	public static function getTicketStatusCombo($litraak, $groups=false)
	{
		$combo = array(
		'-' => ''
		);
		foreach ($litraak->getAllTicketStatus() as $k => $v) {
			$combo[$v] = (string) $k;
		}

		if($groups){
			$combo[__('Groups')] = array(
			__('actives') => 'actives',
			__('wasted') => 'wasted'
			);
		}

		return $combo;
	}

	public static function getTicketTypesCombo($litraak, $public=false)
	{
		$combo = array(
		'-' => ''
		);
		$types = ($public) ? $litraak->getPublicTicketTypes() : $litraak->getAllTicketTypes();
		foreach ($types as $k => $v) {
			$combo[$v] = (string) $k;
		}
		return $combo;
	}

	public static function getTicketSortByCombo($show_project=false)
	{
		$res = array(
		__('Milestone') => 'milestone_name',
		__('Date') => 'ticket_upddt',
		__('Title') => 'ticket_title',
		__('Type') => 'ticket_type',
		__('Status') => 'ticket_status',
		);
			
		if($show_project){
			$res[__('Project')] = 'post_title';
		}

		return $res;
	}

	public static function getMilestoneStatusCombo($litraak)
	{
		$combo = array(
		'-' => ''
		);
		foreach ($litraak->getAllMilestoneStatus() as $k => $v) {
			$combo[$v] = (string) $k;
		}
		return $combo;
	}

	public static function getMilestoneSortByCombo()
	{
		return array(
		__('Date') => 'milestone_dt',
		__('Name') => 'milestone_name',
		__('Status') => 'milestone_status',
		);
	}


	public static function ProjectFirstImageHelper($size,$class="")
	{
		if (!preg_match('/^sq|t|s|m|o$/',$size)) {
			$size = 's';
		}

		global $core, $_ctx;

		$p_url = $core->blog->settings->system->public_url;
		$p_site = preg_replace('#^(.+?//.+?)/(.*)$#','$1',$core->blog->url);
		$p_root = $core->blog->public_path;

		$pattern = '(?:'.preg_quote($p_site,'/').')?'.preg_quote($p_url,'/');
		$pattern = sprintf('/<img.+?src="%s(.*?\.(?:jpg|gif|png))"/msu',$pattern);

		$src = '';

		# We first look in post content
		if ($_ctx->posts)
		{
			$subject = $_ctx->posts->post_content_xhtml.$_ctx->posts->post_excerpt_xhtml;
			if (preg_match_all($pattern,$subject,$m) > 0)
			{
				foreach ($m[1] as $i) {
					if (($src = self::ContentFirstImageLookup($p_root,$i,$size)) !== false) {
						$src = $p_url.'/'.dirname($i).'/'.$src;
						break;
					}
				}
			}
		}

		if ($src) {
			echo '<img alt="" src="'.$src.'" class="'.$class.'" />';
		}
	}

	private static function ContentFirstImageLookup($root,$img,$size)
	{
		# Get base name and extension
		$info = path::info($img);
		$base = $info['base'];

		if (preg_match('/^\.(.+)_(sq|t|s|m)$/',$base,$m)) {
			$base = $m[1];
		}

		$res = false;
		if ($size != 'o' && file_exists($root.'/'.$info['dirname'].'/.'.$base.'_'.$size.'.jpg'))
		{
			$res = '.'.$base.'_'.$size.'.jpg';
		}
		else
		{
			$f = $root.'/'.$info['dirname'].'/'.$base;
			if (file_exists($f.'.'.$info['extension'])) {
				$res = $base.'.'.$info['extension'];
			} elseif (file_exists($f.'.jpg')) {
				$res = $base.'.jpg';
			} elseif (file_exists($f.'.png')) {
				$res = $base.'.png';
			} elseif (file_exists($f.'.gif')) {
				$res = $base.'.gif';
			}
		}

		if ($res) {
			return $res;
		}
		return false;
	}

	public static function getProgressBar($progress=0, $text=null) {
		if($text == null){
			$text = $progress.' %';
		}
		return '<div class="progress-bar"><div class="progress-text">'.$text.
		'</div><div class="progress" style="width: '.$progress.'%;"></div></div>';
	}

	public static function getPercent($total, $still=0) {
		return round(100 * ($total - $still) / $total);
	}
}

?>