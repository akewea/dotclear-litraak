<?php
//@@licence@@

if (!defined('DC_RC_PATH')) { return; }

# Chargement des classes
$GLOBALS['__autoload']['litraak'] = dirname(__FILE__).'/inc/class.litraak.php';
$GLOBALS['__autoload']['litraakUtils'] = dirname(__FILE__).'/inc/lib.litraak.utils.php';
$GLOBALS['__autoload']['litraakPage'] = dirname(__FILE__).'/inc/lib.litraak.page.php';
$GLOBALS['__autoload']['litraakPublic'] = dirname(__FILE__).'/inc/lib.litraak.public.php';
$GLOBALS['__autoload']['litraakTemplates'] = dirname(__FILE__).'/inc/lib.litraak.templates.php';
$GLOBALS['__autoload']['litraakExtProject'] = dirname(__FILE__).'/inc/lib.litraak.extensions.php';
$GLOBALS['__autoload']['litraakExtMilestone'] = dirname(__FILE__).'/inc/lib.litraak.extensions.php';
$GLOBALS['__autoload']['litraakExtTicket'] = dirname(__FILE__).'/inc/lib.litraak.extensions.php';
$GLOBALS['__autoload']['litraakMedia'] = dirname(__FILE__).'/inc/class.litraak.media.php';
$GLOBALS['__autoload']['litraakRestMethods'] = dirname(__FILE__).'/inc/lib.litraak.services.php';
$GLOBALS['__autoload']['litraakFeeds'] = dirname(__FILE__).'/inc/lib.litraak.feeds.php';

$core->blog->settings->addNamespace('litraak');

# Nouveau type de post
$core->setPostType('litraak', 'plugin.php?p=litraak&projectid=%s', $core->blog->settings->litraak->litraak_basename_url.'/%s/');

# Services
$core->rest->addFunction('getTicketById',array('litraakRestMethods','getTicketById'));
$core->rest->addFunction('getMilestoneById',array('litraakRestMethods','getMilestoneById'));

?>