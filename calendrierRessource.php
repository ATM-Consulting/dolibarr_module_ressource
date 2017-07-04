<?php
//TODO fullcalendar instead of wdcalendar

	require('config.php');
	require('./class/ressource.class.php');
	require('./lib/ressource.lib.php');
	
	llxHeader('', $langs->trans('RessourceCalendar'), '', '', 0,0,
			array('/fullcalendar/lib/moment/min/moment.min.js', '/fullcalendar/lib/fullcalendar/dist/fullcalendar.min.js','/fullcalendar/lib/fullcalendar/dist/lang/'.$langjs.'.js')
			,array('/fullcalendar/lib/fullcalendar/dist/fullcalendar.min.css','/fullcalendar/css/fullcalendar.css')
	);
		
	$PDOdb=new TPDOdb;
	$ressource=new TRH_ressource;
	$ressource->load($PDOdb, $_REQUEST['id']);
	
	$fiche = isset($_REQUEST['fiche']) ? $_REQUEST['fiche'] : false;
	//$idCombo = isset($_REQUEST['idCombo']) ? $_REQUEST['idCombo'] : 0; 
	$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
	$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : $ressource->fk_rh_ressource_type;
	$fk_user = isset($_REQUEST['fk_user']) ? $_REQUEST['fk_user'] : 0;
	$typeEven = isset($_REQUEST['typeEven']) ? $_REQUEST['typeEven'] : 'all' ;
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form2','GET');
	echo $form->hidden('action', 'afficher');
	//echo $form->hidden('id',$ressource->getId());
	//echo 'Type : '.$type.' id : '.$id.' user : '.$fk_user.' even : '.$typeEven.'<br>';
	$url = ($id>0 ? 'id='.$id : '').($type>0 ? '&type='.$type : '' ).($idCombo>0 ? 'idCombo='.$idCombo : '').($fk_user>0 ? '&fk_user='.$fk_user : '' ).'&typeEven='.($typeEven ? $typeEven : 'all' );
	
	//LISTE DE USERS
	$TUser = array('');
	$sqlReq="SELECT rowid, firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE entity IN (0,".$conf->entity.")";
	$PDOdb->Execute($sqlReq);
	while($PDOdb->Get_line()) {
		$TUser[$PDOdb->Get_field('rowid')] = htmlentities($PDOdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1')." ".htmlentities($PDOdb->Get_field('name'), ENT_COMPAT , 'ISO8859-1');
		}
	
	$TRessource = array('');
	$sqlReq="SELECT rowid,libelle, numId FROM ".MAIN_DB_PREFIX."rh_ressource WHERE entity IN (0,".$conf->entity.")";
	if ($type>0){$sqlReq .= " AND fk_rh_ressource_type = ".$type;}
		$PDOdb->Execute($sqlReq);
		while($PDOdb->Get_line()) {
			$TRessource[$PDOdb->Get_field('rowid')] = $PDOdb->Get_field('libelle').' '.$PDOdb->Get_field('numId');
			}

	$ressource->load_liste_type_ressource($PDOdb);
	$TType = array_merge($ressource->TType);
	if ($fiche) {
		$TTypeEvent = getTypeEvent($ressource->fk_rh_ressource_type,true);
	}
	else {
		$TTypeEvent = getTypeEvent($type, true);
	}

	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/calendrier.tpl.php'
		,array()
		,array(
			'ressource'=>array(
				'id' => $id//$ressource->getId()
				,'entete'=>getLibelle($ressource)
				,'titreAgenda'=>load_fiche_titre("Agenda des ressources",'', 'title.png', 0, '')
				,'idHidden'=>$form->hidden('id', $id)
				,'fiche'=> $fiche
				,'ficheHidden'=>$form->hidden('fiche', $fiche)
				,'type'=>$form->combo('', 'type', $TType, $type)
				,'typeURL'=>$type
				,'idRessource'=>$form->combo('', 'id', $TRessource, $id)
				,'fk_user'=>$form->combo('', 'fk_user', $TUser, $fk_user)
				,'typeEven'=>$form->combo('', 'typeEven', $TTypeEvent, $typeEven)
				,'typeEvenURL'=>$typeEven
				,'URL'=>$url
				,'btValider'=>$form->btsubmit('Valider', 'valider')
				,'numId'=>$ressource->numId
				,'libelle'=>$ressource->libelle
			)
			,'view'=>array(
				'mode'=>$mode
				,'userDroitAgenda'=>((int)$user->rights->ressource->agenda->manageAgenda)
				,'head'=>dol_get_fiche_head(ressourcePrepareHead($ressource, 'ressource')  , 'calendrier', 'Ressource')
				,'onglet'=>dol_get_fiche_head(array()  , '', 'Agenda')
			)
			
			
		)	
		
	);
	
	$form->end();

	llxFooter();
	
