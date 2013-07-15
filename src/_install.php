<?php
//@@licence@@

if (!defined('DC_CONTEXT_ADMIN')) { return; }
 
$m_version = $core->plugins->moduleInfo('litraak','version');
$i_version = $core->getVersion('litraak');
 
if (version_compare($i_version,$m_version,'>=')) {
	return;
}

global $core;
 
# Création des setting (s'ils existent, ils ne seront pas écrasés)
$settings = new dcSettings($core,null);
$settings->addNamespace('litraak');
$settings->litraak->put('litraak_enabled',false,'boolean','Enable LitraAk',false,true);
$settings->litraak->put('litraak_add_css',true,'boolean','Add LitraAk default public CSS',false,true);
$settings->litraak->put('litraak_basename_url','litraak','string','LitraAk URL basename',false,true);
$settings->litraak->put('litraak_nb_tickets_per_feed',$core->blog->settings->system->nb_comment_per_feed,'integer','LitraAk tickets feed size',false,true);
$settings->litraak->put('litraak_nb_projects_per_feed',$core->blog->settings->system->nb_comment_per_feed,'integer','LitraAk projects feed size',false,true);
$settings->litraak->put('litraak_nb_milestones_per_feed',$core->blog->settings->system->nb_comment_per_feed,'integer','LitraAk milestones feed size',false,true);

// Création du schéma.
$s = new dbStruct($core->con,$core->prefix);

// Table des versions
$s->litraak_milestone
	->milestone_id('bigint', 0, false)
	->post_id('bigint', 0, false)
	->milestone_name('varchar', 50, false)
	->milestone_desc('text', 0, true)
	->milestone_url('varchar', 50, false, "'-'")
	->milestone_dt('timestamp', 0, false)
	->milestone_tz('varchar', 128, false, "'UTC'")
	->milestone_status('smallint', 0, false, 0)
	->milestone_nb_tickets('bigint', 0, false, 0)
	->milestone_nb_actives('bigint', 0, false, 0)
	->milestone_nb_wasted('bigint', 0, false, 0)
	->primary('pk_litraak_milestone','milestone_id')
	->index('idx_litraak_milestone_post_id','btree','post_id')
	->unique('uk_litraak_milestone_url_post_id','milestone_url','post_id')
;

// Table des tickets
$s->litraak_ticket
	->ticket_id('bigint', 0, false)
	->post_id('bigint', 0, false)
	->milestone_id('bigint', 0, true)
	->ticket_type('smallint', 0, false, 0)
	->ticket_title('varchar', 255, false)
	->ticket_desc('text', 0, false)
	->ticket_email('varchar', 255, false)
	->ticket_author('varchar', 255, false)
	->ticket_dt('timestamp', 0, false)
	->ticket_tz('varchar', 128, false, "'UTC'")
	->ticket_upddt('timestamp', 0, false)
	->ticket_status('smallint', 0, false, 0)
	->ticket_open_comment('smallint', 0, true, 1)
	->primary('pk_litraak_ticket','ticket_id')
	->index('idx_litraak_ticket_post_id','btree','post_id')
	->index('idx_litraak_ticket_milestone_id','btree','milestone_id')
;

// Table des changements sur les tickets
$s->litraak_ticket_change
	->change_id('bigint', 0, false)
	->ticket_id('bigint', 0, false)
	->change_title('varchar', 255, false)
	->change_comment('text', 0, true)
	->change_email('varchar', 255, false)
	->change_author('varchar', 255, false)
	->change_dt('timestamp', 0, false)
	->change_tz('varchar', 128, false, "'UTC'")
	->change_status('smallint', 0, false, 0)
	->primary('pk_litraak_ticket_change','change_id')
	->index('idx_litraak_ticket_change_ticket_id','btree','ticket_id')
	->index('idx_litraak_ticket_change_author','btree','change_author')
	->index('idx_litraak_ticket_change_email','btree','change_email')
;

// Table des bugs/taches
$s->litraak_milestone_media
	->media_id('bigint', 0, false)
	->milestone_id('bigint', 0, false)
	->primary('pk_litraak_milestone_media','media_id', 'milestone_id')
	->index('idx_litraak_milestone_media','btree','milestone_id')
;

// Table des infos d'un projet
$s->litraak_project_info
	->post_id('bigint', 0, false)
	->project_phase('smallint', 0, false, 0)
	->project_next_release_id('bigint', 0, true)
	->project_last_release_id('bigint', 0, true)
	->project_nb_tickets('bigint', 0, false, 0)
	->project_nb_actives('bigint', 0, false, 0)
	->project_nb_wasted('bigint', 0, false, 0)
	->project_update_template('varchar', 255, false)
	->project_open_ticket('smallint', 0, true, 1)
	->primary('pk_litraak_project_info','post_id')
;


$si = new dbStruct($core->con,$core->prefix);
$changes = $si->synchronize($s);

// Mise à jour SQL pour la version 0.2.0
if($i_version < '0.2.0'){
	$core->con->execute('UPDATE '.$core->prefix.'post
		SET 
			post_content=(@tmp:=post_content),
			post_content_xhtml=(@tmp_xhtml:=post_content_xhtml),
			post_content=post_excerpt,
			post_excerpt=@tmp,
			post_content_xhtml=post_excerpt_xhtml,
			post_excerpt_xhtml=@tmp_xhtml
		WHERE post_type = "litraak" and post_excerpt_xhtml is not null');
}

// Mise à jour SQL pour la version 0.3.0
if($i_version < '0.3.0'){
	$core->con->execute('
		UPDATE '.$core->prefix.'litraak_project_info 
		SET 
			project_phase = (
			select post_status 
			from '.$core->prefix.'post 
			where '.$core->prefix.'post.post_id = '.$core->prefix.'litraak_project_info.post_id
			)');
	
	$core->con->execute('
		UPDATE '.$core->prefix.'post 
		SET post_status = 1
		WHERE post_type = "litraak" AND post_status <> 0');
	
	$core->con->execute('
		UPDATE '.$core->prefix.'post 
		SET post_status = -2
		WHERE post_type = "litraak" AND post_status = 0');
}

// Mise à jour SQL pour la version 0.3.0
if($i_version < '0.4.0'){
	$core->con->execute('
		UPDATE '.$core->prefix.'litraak_project_info PI
		SET 
			project_nb_actives = (
			select count(distinct ticket_id) 
			from '.$core->prefix.'litraak_ticket T 
			where T.post_id = PI.post_id
			and ticket_status < '.litraak::TICKET_CLOSED.'
			),
			project_nb_wasted = (
			select count(distinct ticket_id) 
			from '.$core->prefix.'litraak_ticket T 
			where T.post_id = PI.post_id
			and ticket_status > '.litraak::TICKET_CLOSED.'
			)');
}

$core->setVersion('litraak',$m_version);
?>