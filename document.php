<?php
	require('config.php');
	require('./class/contrat.class.php');
	require('./class/ressource.class.php');
	require('./lib/ressource.lib.php');
	
	require_once(DOL_DOCUMENT_ROOT."/core/class/html.form.class.php");
	require_once(DOL_DOCUMENT_ROOT."/core/class/html.formfile.class.php");
	require_once(DOL_DOCUMENT_ROOT."/core/class/fileupload.class.php");
	require_once(DOL_DOCUMENT_ROOT."/core/lib/functions2.lib.php");
	
	$langs->load('ressource@ressource');
	$langs->load('main');
	$langs->load('other');
	
	$PDOdb=new TPDOdb;
	$object=new TRH_ressource;
	
	if(isset($_REQUEST['id'])) {
		$object->load($PDOdb, $_REQUEST['id']);
		_fiche($PDOdb, $object);
	}
	
	$PDOdb->close();
	llxFooter();
	
	function _fiche(&$PDOdb, &$object) {
		global $db,$user,$conf,$langs;
		llxHeader('','Fichiers joints');
		$dir_base = DOL_DATA_ROOT.'/ressource/';
		$upload_dir_base = $dir_base.'ressource/';
		
		$confirm = $_REQUEST['confirm'];
		$action = $_REQUEST['action'];
		
		$error = false;
		$message = false;
		$formconfirm = false;
		
		$html = new Form($db);
		$formfile = new FormFile($db);
		
		$upload_dir = $upload_dir_base.dol_sanitizeFileName($object->getId());
			
		/*
		 * Actions
		 */
		include_once DOL_DOCUMENT_ROOT . '/core/actions_linkedfiles.inc.php';
	
	
		// Get all files
		$sortfield  = GETPOST("sortfield", 'alpha');
		$sortorder  = GETPOST("sortorder", 'alpha');
		$page       = GETPOST("page", 'int');
		
		if ($page == -1)
		{
		    $page = 0;
		}
		
		$offset = $conf->liste_limit * $page;
		$pageprev = $page - 1;
		$pagenext = $page + 1;
		
		if (!$sortorder) $sortorder = "ASC";
		if (!$sortfield) $sortfield = "name";
		
		$filearray = dol_dir_list($upload_dir, "files", 0, '', '\.meta$', $sortfield, (strtolower($sortorder) == 'desc' ? SORT_DESC : SORT_ASC), 1);
		$totalsize = 0;
		foreach($filearray as $key => $file)
		{
			$totalsize += $file['size'];
		}
		
		if ($action == 'delete')
		{
			$formconfirm = $html->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->getId().'&urlfile='.urldecode($_REQUEST['urlfile']), $langs->trans('DeleteFile'), $langs->trans('ConfirmDeleteFile'), 'confirm_deletefile', '', 0, 0);
		}
		
		$can_upload = 1;
		
		echo dol_get_fiche_head(ressourcePrepareHead($object, 'ressource'), 'document', 'Ressource');
		
		echo ($message ? dol_htmloutput_mesg($message, '', ($error ? 'error' : 'ok'), 0) : '');

		echo ($formconfirm ? $formconfirm : '');
		
		printLibelle($object);
		
		if($user->rights->ressource->ressource->uploadFiles){
			$formfile->form_attach_new_file($_SERVER["PHP_SELF"].'?id='.$object->getId(), '', 0, 0, $can_upload);
			$formfile->list_of_documents($filearray, $object, 'ressource', '&id='.$object->getId(),0,'ressource/'.$object->getId().'/',1);
		}else{
			$formfile->list_of_documents($filearray, $object, 'ressource', '&id='.$object->getId(),0,'ressource/'.$object->getId().'/',0);
		}
		
		?><div style="clear:both"></div><?php
		
		dol_fiche_end();
		llxFooter();
		
		$db->close();
	}