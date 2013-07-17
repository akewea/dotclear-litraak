<?php
//@@licence@@

if (!defined('DC_RC_PATH')) { return; }

# Initialisation de l'objet litraak.
global $litraak;
$litraak = new litraak($core);

// Ajout de l'icone au tableau de bord
if($core->blog->settings->litraak->litraak_enabled){
	
	// Ajout dans les favoris
	$core->addBehavior('adminDashboardFavs',	array('litraakAdmin', 'litraakDashboard'));
	
	// Ajout dans les menus
	$_menu['Plugins']->addItem(__('Litraak'),'plugin.php?p=litraak','index.php?pf=litraak/img/icon-small.png',
	                preg_match('/plugin.php\?p=litraak(&.*)?$/',$_SERVER['REQUEST_URI']),
	                $core->auth->check('usage,contentadmin',$core->blog->id));
	
	// Widget
	require dirname(__FILE__).'/_widgets.php';
	
	
	// TODO Ajouter les permissions
	// $core->auth->setPermissionType('litraak_access',__('access litraak'));
	// $core->auth->setPermissionType('litraak_projects_manage',__('manage litraak'));
	// $core->auth->setPermissionType('litraak_access',__('access litraak'));
}

# Enregistrement des fonctions d'exportation
$core->addBehavior('exportFull',array('litraakExport','exportFull'));
$core->addBehavior('exportSingle',array('litraakExport','exportSingle'));

# Préférences du blog
$core->addBehavior('adminBlogPreferencesForm',array('litraakAdmin','adminBlogPreferencesForm'));
$core->addBehavior('adminBeforeBlogSettingsUpdate',array('litraakAdmin','adminBeforeBlogSettingsUpdate'));

# Préférences utilisateur
$core->addBehavior('adminUserForm',array('litraakAdmin','adminUserForm'));
$core->addBehavior('adminPreferencesForm',array('litraakAdmin','adminPreferencesForm'));
$core->addBehavior('adminBeforeUserCreate',array('litraakAdmin','adminBeforeUserCreateOrUpdate'));
$core->addBehavior('adminBeforeUserUpdate',array('litraakAdmin','adminBeforeUserCreateOrUpdate'));
$core->addBehavior('adminBeforeUserOptionsUpdate',array('litraakAdmin','adminBeforeUserCreateOrUpdate'));

class litraakExport
{
	# Full export behavior
	public static function exportFull($core,$exp)
	{
		// TODO
		$exp->exportTable('litraak_milestone');
		$exp->exportTable('litraak_ticket');
		$exp->exportTable('litraak_download');
	}

	# Single blog export behavior	
	public static function exportSingle($core,$exp,$blog_id)
	{
		// TODO
		/*$exp->export('mytable',
			'SELECT * '.
			'FROM '.$core->prefix.'mytable '.
			'WHERE blog_id = \''.$blog_id.'\''
		);*/
	}
}

class litraakAdmin
{
	# Tableau de bord ##########################################################
	
	public static function litraakDashboard($core,$favs)
	{
		global $litraak;
		
		$params = array('ticket_status' => litraak::getActiveTicketStatus());
		$rs = $litraak->getTickets($params, true);		
		$icon_name = __('Litraak').(($rs->f(0) > 0) ? ' ('.sprintf(__('%s open tickets'), $rs->f(0)).')' : '');
		
		$favs['litraak'] = new ArrayObject(array(
				'litraak',
				$icon_name,
				'plugin.php?p=litraak',
				'index.php?pf=litraak/img/icon-small.png',
				'index.php?pf=litraak/img/icon.png',
				'usage,contentadmin',
				null,
				null));
	}
	
	public static function dashboardFavs($core,$favs)
	{
		$favs['fostrak'] = new ArrayObject(array(
				'fostrak',
				__('Fostrak'),
				'plugin.php?p=fostrak',
				'index.php?pf=fostrak/img/icon-16.png',
				'index.php?pf=fostrak/img/icon-64.png',
				'usage,contentadmin',
				null,
				null));
	}
	
	# Préférences du blog ######################################################
	
	public static function adminBlogPreferencesForm($core,$settings)
	{
		echo
		'<fieldset><legend>LitraAk</legend>'.
		'<div class="two-cols">'.
		'<div class="col">'.
		
		'<p><label class="classic">'.
		form::checkbox('litraak_enabled','1',$settings->litraak->litraak_enabled).
		__('Enable LitraAk').'</label></p>'.
		
		'</div>'.
		
		'</fieldset>';
	}
	
	public static function adminBeforeBlogSettingsUpdate($settings)
	{
		$settings->addNameSpace('litraak');
		$settings->litraak->put('litraak_enabled',!empty($_POST['litraak_enabled']),'boolean');
	}
	
	# Préférences utilisateur ##################################################
	
	public static function adminUserForm($rs)
	{
		$user_options = array(
					'litraak_desc_edit_size' => 30,
					'litraak_doc_edit_size' => 40
			);
		
		if($rs){
			$user_options = array_merge($user_options,$rs->options());
		}
		
		return self::litraakUserForm(
			$user_options['litraak_desc_edit_size'], 
			$user_options['litraak_doc_edit_size']);
	}
	
	public static function adminPreferencesForm($core)
	{
		return self::litraakUserForm(
			$core->auth->getOption('litraak_desc_edit_size'), 
			$core->auth->getOption('litraak_doc_edit_size'));
	}
	
	private static function litraakUserForm($desc_edit_size, $doc_edit_size, $litraak_dashboard_icon, $litraak_blog_menu_icon="toto")
	{		
		echo
		'<fieldset><legend>LitraAk</legend>'.
		'<div class="two-cols">'.
		'<div class="col">'.
		
		'<p><label>'.__('Project description edit field height:').' '.
		form::field('litraak_desc_edit_size',5,4,$desc_edit_size,'',11).
		'</label></p>'.
		
		'</div>'.
		'<div class="col">'.
				
		
		'<p><label>'.__('Project documentation edit field height:').' '.
		form::field('litraak_doc_edit_size',5,4,$doc_edit_size,'',11).
		'</label></p>'.
		
		'</div>'.
		
		'</div>'.
		'</fieldset>';
	}
	
	public static function adminBeforeUserCreateOrUpdate($cur, $id=0)
	{
		//print_r($_POST); exit;
		
		$cur->user_options['litraak_desc_edit_size'] = (integer) $_POST['litraak_desc_edit_size'];
		$cur->user_options['litraak_doc_edit_size'] = (integer) $_POST['litraak_doc_edit_size'];
		
		if ((integer) $cur->user_options['litraak_desc_edit_size'] < 1) {
			$cur->user_options['litraak_desc_edit_size'] = litraakUtils::PROJECT_DEFAULT_EDIT_SIZE;
		}
		
		if ((integer) $cur->user_options['litraak_doc_edit_size'] < 1) {
			$cur->user_options['litraak_doc_edit_size'] = litraakUtils::PROJECT_DEFAULT_EDIT_SIZE;
		}
	}
}

?>