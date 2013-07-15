<?php
//@@licence@@

class litraakMedia extends dcMedia
{
	
	public function __construct($core,$type='')
	{
		parent::__construct($core,$type);
		
		$this->table_ref = $this->core->prefix.'litraak_milestone_media';
	}
	
	public function getPostMedia($post_id,$media_id=null)
	{
		$post_id = (integer) $post_id;
		
		$strReq =
		'SELECT media_file, M.media_id, media_path, media_title, media_meta, media_dt, '.
		'media_creadt, media_upddt, media_private, user_id '.
		'FROM '.$this->table.' M '.
		'INNER JOIN '.$this->table_ref.' PM ON (M.media_id = PM.media_id) '.
		"WHERE media_path = '".$this->path."' ".
		'AND milestone_id = '.$post_id.' ';
		
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
	
	/**
	Attaches a media to a post.
	
	@param	post_id	<b>integer</b>		Post ID
	@param	media_id	<b>integer</b>		Optionnal media ID
	*/
	public function addPostMedia($post_id,$media_id)
	{
		$post_id = (integer) $post_id;
		$media_id = (integer) $media_id;
		
		$f = $this->getPostMedia($post_id,$media_id);
		
		if (!empty($f)) {
			return;
		}
		
		$cur = $this->con->openCursor($this->table_ref);
		$cur->milestone_id = $post_id;
		$cur->media_id = $media_id;
		
		$cur->insert();
		$this->core->blog->triggerBlog();
	}
	
	/**
	Detaches a media from a post.
	
	@param	post_id	<b>integer</b>		Post ID
	@param	media_id	<b>integer</b>		Optionnal media ID
	*/
	public function removePostMedia($post_id,$media_id)
	{
		$post_id = (integer) $post_id;
		$media_id = (integer) $media_id;
		
		$strReq = 'DELETE FROM '.$this->table_ref.' '.
				'WHERE milestone_id = '.$post_id.' '.
				'AND media_id = '.$media_id.' ';
		
		$this->con->execute($strReq);
		$this->core->blog->triggerBlog();
	}
}
?>