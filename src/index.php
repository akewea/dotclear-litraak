<?php
//@@licence@@

if (!defined('DC_CONTEXT_ADMIN')) { return; }

// TODO Redirection vers la bonne page en fonction des paramètres

if(isset($_REQUEST['attachid'])){
	include dirname(__FILE__).'/milestone_media.php';
}else if(isset($_REQUEST['attachtoid'])){
	include dirname(__FILE__).'/media.php';
}else if(isset($_REQUEST['about'])){
	include dirname(__FILE__).'/about.php';
}else if(isset($_REQUEST['projectid'])){
	if(isset($_REQUEST['ticketid'])){
		include dirname(__FILE__).'/ticket.php';
	}elseif(isset($_REQUEST['milestoneid'])){
		include dirname(__FILE__).'/milestone.php';
	}else{
		include dirname(__FILE__).'/project.php';
	}
}else{
	if(isset($_REQUEST['ticketid'])){
		include dirname(__FILE__).'/ticket.php';
	}else{
		include dirname(__FILE__).'/home.php';
	}
}

?>