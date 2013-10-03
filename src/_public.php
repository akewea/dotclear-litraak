<?php
//@@licence@@

if (!defined('DC_RC_PATH')) { return; }

# Initialisation de l'objet litraak.
global $litraak;
$litraak = new litraak($core);

// Pour utilisation avec la balise "tpl:Widget"
require dirname(__FILE__).'/_widgets.php';

if($core->blog->settings->litraak->litraak_enabled){
	$core->url->register('litraak','litraak','^'.$core->blog->settings->litraak->litraak_basename_url.'(.*)$',array('litraakPublic','litraakUrl'));
	$core->addBehavior('publicHeadContent',array('litraakPublic','publicHeadContent'));
}

$core->tpl->addValue('LitraakURL',array('litraakTemplates','LitraakURL'));
$core->tpl->addValue('LitraakFeedURL',array('litraakTemplates','LitraakFeedURL'));
$core->tpl->addValue('LitraakTicketsFeedURL',array('litraakTemplates','LitraakTicketsFeedURL'));
$core->tpl->addValue('LitraakReleasesFeedURL',array('litraakTemplates','LitraakReleasesFeedURL'));

$core->tpl->addBlock('LitraakProjects',array('litraakTemplates','LitraakProjects'));
$core->tpl->addBlock('LitraakProjectsFooter',array('litraakTemplates','LitraakProjectsFooter'));
$core->tpl->addBlock('LitraakProjectsHeader',array('litraakTemplates','LitraakProjectsHeader'));
$core->tpl->addValue('LitraakProjectDescription',array('litraakTemplates','LitraakProjectDescription'));
$core->tpl->addValue('LitraakProjectAttachmentCount',array('litraakTemplates','LitraakProjectAttachmentCount'));
$core->tpl->addValue('LitraakProjectAuthorCommonName',array('litraakTemplates','LitraakProjectAuthorCommonName'));
$core->tpl->addValue('LitraakProjectAuthorDisplayName',array('litraakTemplates','LitraakProjectAuthorDisplayName'));
$core->tpl->addValue('LitraakProjectBasename',array('litraakTemplates','LitraakProjectBasename'));
$core->tpl->addValue('LitraakProjectCommentCount',array('litraakTemplates','LitraakProjectCommentCount'));
$core->tpl->addValue('LitraakProjectDocumentation',array('litraakTemplates','LitraakProjectDocumentation'));
$core->tpl->addValue('LitraakProjectDate',array('litraakTemplates','LitraakProjectDate'));
$core->tpl->addValue('LitraakProjectFeedID',array('litraakTemplates','LitraakProjectFeedID'));
$core->tpl->addValue('LitraakProjectFirstImage',array('litraakTemplates','LitraakProjectFirstImage'));
$core->tpl->addValue('LitraakProjectID',array('litraakTemplates','LitraakProjectID'));
$core->tpl->addBlock('LitraakProjectIf',array('litraakTemplates','LitraakProjectIf'));
$core->tpl->addValue('LitraakProjectIfFirst',array('litraakTemplates','LitraakProjectIfFirst'));
$core->tpl->addValue('LitraakProjectIfOdd',array('litraakTemplates','LitraakProjectIfOdd'));
$core->tpl->addValue('LitraakProjectLang',array('litraakTemplates','LitraakProjectLang'));
$core->tpl->addValue('LitraakProjectName',array('litraakTemplates','LitraakProjectName'));
$core->tpl->addValue('LitraakProjectTime',array('litraakTemplates','LitraakProjectTime'));
$core->tpl->addValue('LitraakProjectURL',array('litraakTemplates','LitraakProjectURL'));
$core->tpl->addValue('LitraakProjectStatus',array('litraakTemplates','LitraakProjectPhase')); // TODO deprecated : à supprimer en v1.0.0
$core->tpl->addValue('LitraakProjectPhase',array('litraakTemplates','LitraakProjectPhase'));
$core->tpl->addValue('LitraakProjectLastReleaseName',array('litraakTemplates','LitraakProjectLastReleaseName'));
$core->tpl->addValue('LitraakProjectLastReleaseURL',array('litraakTemplates','LitraakProjectLastReleaseURL'));
$core->tpl->addValue('LitraakProjectLastReleaseDate',array('litraakTemplates','LitraakProjectLastReleaseDate'));
$core->tpl->addBlock('LitraakProjectIfLastRelease',array('litraakTemplates','LitraakProjectIfLastRelease'));
$core->tpl->addValue('LitraakProjectNextReleaseName',array('litraakTemplates','LitraakProjectNextReleaseName'));
$core->tpl->addValue('LitraakProjectNextReleaseURL',array('litraakTemplates','LitraakProjectNextReleaseURL'));
$core->tpl->addValue('LitraakProjectNextReleaseDate',array('litraakTemplates','LitraakProjectNextReleaseDate'));
$core->tpl->addBlock('LitraakProjectIfNextRelease',array('litraakTemplates','LitraakProjectIfNextRelease'));

$core->tpl->addBlock('LitraakComments',array('litraakTemplates','LitraakComments'));

$core->tpl->addBlock('LitraakMilestones',array('litraakTemplates','LitraakMilestones'));
$core->tpl->addBlock('LitraakMilestonesFooter',array('litraakTemplates','LitraakMilestonesFooter'));
$core->tpl->addBlock('LitraakMilestonesHeader',array('litraakTemplates','LitraakMilestonesHeader'));
$core->tpl->addBlock('LitraakMilestoneIf',array('litraakTemplates','LitraakMilestoneIf'));
$core->tpl->addValue('LitraakMilestoneURL',array('litraakTemplates','LitraakMilestoneURL'));
$core->tpl->addValue('LitraakMilestoneName',array('litraakTemplates','LitraakMilestoneName'));
$core->tpl->addValue('LitraakMilestoneDescription',array('litraakTemplates','LitraakMilestoneDescription'));
$core->tpl->addValue('LitraakMilestoneDate',array('litraakTemplates','LitraakMilestoneDate'));
$core->tpl->addValue('LitraakMilestoneStatus',array('litraakTemplates','LitraakMilestoneStatus'));
$core->tpl->addValue('LitraakMilestoneFeedID',array('litraakTemplates','LitraakMilestoneFeedID'));
$core->tpl->addValue('LitraakMilestoneIfFirst',array('litraakTemplates','LitraakMilestoneIfFirst'));
$core->tpl->addValue('LitraakMilestoneIfOdd',array('litraakTemplates','LitraakMilestoneIfOdd'));
$core->tpl->addBlock('LitraakMilestoneAttachments',array('litraakTemplates','LitraakMilestoneAttachments'));
$core->tpl->addValue('LitraakMilestoneAttachmentCount',array('litraakTemplates','LitraakMilestoneAttachmentCount'));
$core->tpl->addValue('LitraakMilestoneProgressBar',array('litraakTemplates','LitraakMilestoneProgressBar'));
$core->tpl->addValue('LitraakMilestoneTicketNumber',array('litraakTemplates','LitraakMilestoneTicketNumber'));
$core->tpl->addValue('LitraakMilestoneProjectName',array('litraakTemplates','LitraakMilestoneProjectName'));
$core->tpl->addValue('LitraakMilestoneProjectURL',array('litraakTemplates','LitraakMilestoneProjectURL'));

$core->tpl->addBlock('LitraakTickets',array('litraakTemplates','LitraakTickets'));
$core->tpl->addBlock('LitraakTicketsFooter',array('litraakTemplates','LitraakTicketsFooter'));
$core->tpl->addBlock('LitraakTicketsHeader',array('litraakTemplates','LitraakTicketsHeader'));
$core->tpl->addBlock('LitraakTicketIf',array('litraakTemplates','LitraakTicketIf'));
$core->tpl->addBlock('LitraakTicketIfExists',array('litraakTemplates','LitraakTicketIfExists'));
$core->tpl->addBlock('LitraakTicketIfDeleted',array('litraakTemplates','LitraakTicketIfDeleted'));
$core->tpl->addValue('LitraakTicketId',array('litraakTemplates','LitraakTicketId'));
$core->tpl->addValue('LitraakTicketFeedID',array('litraakTemplates','LitraakTicketFeedID'));
$core->tpl->addValue('LitraakTicketURL',array('litraakTemplates','LitraakTicketURL'));
$core->tpl->addValue('LitraakTicketTitle',array('litraakTemplates','LitraakTicketTitle'));
$core->tpl->addValue('LitraakTicketDescription',array('litraakTemplates','LitraakTicketDescription'));
$core->tpl->addValue('LitraakTicketAuthor',array('litraakTemplates','LitraakTicketAuthor'));
$core->tpl->addValue('LitraakTicketDate',array('litraakTemplates','LitraakTicketDate'));
$core->tpl->addValue('LitraakTicketStatus',array('litraakTemplates','LitraakTicketStatus'));
$core->tpl->addValue('LitraakTicketType',array('litraakTemplates','LitraakTicketType'));
$core->tpl->addValue('LitraakTicketIfFirst',array('litraakTemplates','LitraakTicketIfFirst'));
$core->tpl->addValue('LitraakTicketIfOdd',array('litraakTemplates','LitraakTicketIfOdd'));
$core->tpl->addValue('LitraakTicketMilestoneName',array('litraakTemplates','LitraakTicketMilestoneName'));
$core->tpl->addValue('LitraakTicketMilestoneURL',array('litraakTemplates','LitraakTicketMilestoneURL'));
$core->tpl->addValue('LitraakTicketProjectName',array('litraakTemplates','LitraakTicketProjectName'));
$core->tpl->addValue('LitraakTicketProjectURL',array('litraakTemplates','LitraakTicketProjectURL'));

$core->tpl->addBlock('IfTicketPreview',array('litraakTemplates','IfTicketPreview'));
$core->tpl->addValue('TicketPreviewName',array('litraakTemplates','TicketPreviewName'));
$core->tpl->addValue('TicketPreviewEmail',array('litraakTemplates','TicketPreviewEmail'));
$core->tpl->addValue('TicketPreviewTitle',array('litraakTemplates','TicketPreviewTitle'));
$core->tpl->addValue('TicketPreviewDescription',array('litraakTemplates','TicketPreviewDescription'));
$core->tpl->addValue('TicketPreviewCheckRemember',array('litraakTemplates','TicketPreviewCheckRemember'));

$core->tpl->addBlock('LitraakTicketChanges',array('litraakTemplates','LitraakTicketChanges'));
$core->tpl->addBlock('LitraakTicketChangesFooter',array('litraakTemplates','LitraakTicketChangesFooter'));
$core->tpl->addBlock('LitraakTicketChangesHeader',array('litraakTemplates','LitraakTicketChangesHeader'));
$core->tpl->addBlock('LitraakTicketChangeIf',array('litraakTemplates','LitraakTicketChangeIf'));
$core->tpl->addValue('LitraakTicketChangeAuthor',array('litraakTemplates','LitraakTicketChangeAuthor'));
$core->tpl->addValue('LitraakTicketChangeTitle',array('litraakTemplates','LitraakTicketChangeTitle'));
$core->tpl->addValue('LitraakTicketChangeDescription',array('litraakTemplates','LitraakTicketChangeDescription'));
$core->tpl->addValue('LitraakTicketChangeDate',array('litraakTemplates','LitraakTicketChangeDate'));
$core->tpl->addValue('LitraakTicketChangeIfFirst',array('litraakTemplates','LitraakTicketChangeIfFirst'));
$core->tpl->addValue('LitraakTicketChangeIfOdd',array('litraakTemplates','LitraakTicketChangeIfOdd'));

$core->tpl->addBlock('IfTicketChangePreview',array('litraakTemplates','IfTicketChangePreview'));
$core->tpl->addValue('TicketChangePreviewName',array('litraakTemplates','TicketChangePreviewName'));
$core->tpl->addValue('TicketChangePreviewEmail',array('litraakTemplates','TicketChangePreviewEmail'));
$core->tpl->addValue('TicketChangePreviewDescription',array('litraakTemplates','TicketChangePreviewDescription'));
$core->tpl->addValue('TicketChangePreviewCheckRemember',array('litraakTemplates','TicketChangePreviewCheckRemember'));

?>