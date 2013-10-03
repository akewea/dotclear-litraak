<?php
//@@licence@@

/* DISPLAY
 ------------------------------------------------------------------------------*/
$starting_script = litraakPage::jsPageTabs($default_tab);

litraakPage::open(__('Litraak'),
$starting_script);

litraakPage::breadCrumb(' &rsaquo; '.__('About'));

global $__resources;
if (isset($__resources['help']['litraak-about']) && !empty($__resources['help']['litraak-about'])) {
	$f = $__resources['help']['litraak-about'];
	if (file_exists($f) && is_readable($f)) {
		$fc = file_get_contents($f);
		if (preg_match('|<body[^>]*?>(.*?)</body>|ms',$fc,$matches)) {
			echo $matches[1];
		} else {
			echo $fc;
		}
	}
}

litraakPage::close();
?>