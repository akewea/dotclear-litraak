<?php
//@@licence@@

if (!defined('DC_CONTEXT_ADMIN')) { return; }

// Redirection
$tab = (!empty($_GET['tab']) ? $_GET['tab'] : 'project-list');

if($tab == 'project-list'){ include dirname(__FILE__).'/projects.php';}
else if($tab == 'tickets'){ include dirname(__FILE__).'/tickets.php';}
else if($tab == 'config'){ include dirname(__FILE__).'/config.php';}

?>