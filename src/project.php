<?php
//@@licence@@

$can_edit_post = true; // TODO Ajouter la gestion des droits en fonction de l'utilisateur.
$can_view_page = true;

$project_id = (integer) $_GET['projectid'] > 0 ?	(integer) $_GET['projectid'] : -1;
$project = $litraak->getProjects(array('post_id' => $project_id));
if($project->isEmpty()){
	$project_id = '';
	$project_title = __('New project');
}else{
	$project_title = $project->post_title;
}

/* DISPLAY
-------------------------------------------------------- */
$tab = 'edit-project';
if (!$can_edit_post) {
	$tab = '';
}
if (!empty($_GET['tab'])) {
	$tab = $_GET['tab'];
}else if(!empty($_GET['co'])){
	$tab = 'comments';
}

# Exit if we cannot view page
if (!$can_view_page) {
	litraakPage::helpBlock('core_post');
	litraakPage::close();
	exit;
}

// Redirection
if($tab == 'edit-project'){ include dirname(__FILE__).'/project_detail.php';}
else if($project_id > 0){
	if($tab == 'edit-doc'){ include dirname(__FILE__).'/project_doc.php';}
	else if($tab == 'milestones'){ include dirname(__FILE__).'/project_milestones.php';}
	else if($tab == 'tickets'){ include dirname(__FILE__).'/project_tickets.php';}
	else if($tab == 'comments'){ include dirname(__FILE__).'/project_comments.php';}
}

?>
