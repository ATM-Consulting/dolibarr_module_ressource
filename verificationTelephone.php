<?php
	/**
	 * Ce script vérifie la consommation des cartes TOTAL : à savoir si l'utilisation de la carte est abusive 
	 */ 
	require('./config.php');
	require('./class/evenement.class.php');
	require('./class/ressource.class.php');
	require('./lib/ressource.lib.php');
	
	global $conf;
	$PDOdb=new TPDOdb;
	
	$mesg = '';
	$error=false;
	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				_fiche($PDOdb,  'new');
				break;
			case 'view':
				_fiche($PDOdb, 'view');
				break;
			case 'save':
				//$date_debut=$_REQUEST['date_debut'];
				//$date_fin=$_REQUEST['date_fin'];
				$date_debut = $_REQUEST['date_debut'];
				$date_debut = mktime(0,0,0,substr($date_debut, 3,2),substr($date_debut, 0,2), substr($date_debut, 6,4));
				$date_fin = $_REQUEST['date_fin'];
				$date_fin = mktime(0,0,0,substr($date_fin, 3,2),substr($date_fin, 0,2), substr($date_fin, 6,4));
				_genererRapport($PDOdb, $date_debut, $date_fin, 'view');
				break;
		}
	}else{
		 _fiche($PDOdb, 'view');
	}
	
	$PDOdb->close();
	llxFooter();
	
	
function _fiche(&$PDOdb, $mode) {
	global $db, $user, $langs, $conf;
	
	llxHeader('','Vérification des consommations téléphonique');
	print dol_get_fiche_head(array()  , '', 'Vérification');
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('action', 'save');
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/verificationTelephone.tpl.php'
		,array(
		)
		,array(
			'infos'=>array(
				'titre'=>load_fiche_titre("Vérification des consommations téléphoniques",'', 'title.png', 0, '')
				,'date_debut'=>$form->calendrier('Date de début', 'date_debut', time()-3600*24*31*12, 12)
				,'date_fin'=>$form->calendrier('Date de fin', 'date_fin', time(), 12)
				//,'action'=>$form->hidden('action','save')
			)
			,'view'=>array(
				'mode'=>$mode
			)
		)	
		
	);
	
	
	llxFooter();
}

function _genererRapport(&$PDOdb, $date_debut, $date_fin, $mode) {
	global $db, $user, $langs, $conf;
	
	llxHeader('','Vérification des consommations téléphonique');
	print dol_get_fiche_head(array()  , '', 'Vérification');
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff('new');
	echo $form->hidden('action', 'save');
	
	$TUser = array();
	$TRowidUser = array();
	$sql="SELECT rowid, lastname, firstname, login FROM ".MAIN_DB_PREFIX."user WHERE entity IN (0,".$conf->entity.")";
	$PDOdb->Execute($sql);
	while($PDOdb->Get_line()) {
		$TUser[strtolower($PDOdb->Get_field('firstname').' '.$PDOdb->Get_field('name'))] = $PDOdb->Get_field('rowid');
		$TRowidUser[] = $PDOdb->Get_field('rowid');		
	}
	
	$TGroups= array();
	$sql="SELECT fk_user, fk_usergroup FROM ".MAIN_DB_PREFIX."usergroup_user WHERE entity IN (0,".$conf->entity.")";
	$PDOdb->Execute($sql);
	while($PDOdb->Get_line()) {
		$TGroups[$PDOdb->Get_field('fk_usergroup')][] = $PDOdb->Get_field('fk_user');
	}
	
	$TSim= array();
	$idSim = getIdType('cartesim');
	$idTel = getIdType('telephone');
	$sql="SELECT rowid, fk_rh_ressource, numId FROM ".MAIN_DB_PREFIX."rh_ressource 
	WHERE fk_rh_ressource_type=".$idSim;
	$PDOdb->Execute($sql);
	while($row = $PDOdb->Get_line()) {
		$TSim[$row->rowid] = array('tel'=>$row->fk_rh_ressource
										,'numId'=>$row->numId);}
	
	$TTel = array();
	$sql="SELECT rowid, libelle FROM ".MAIN_DB_PREFIX."rh_ressource 
	WHERE fk_rh_ressource_type=".$idTel;
	$PDOdb->Execute($sql);
	while($row = $PDOdb->Get_line()) {
		$TTel[$row->rowid] = $row->libelle;}
	
	//print_r($TRessource);exit();
	
	$TLimites = load_limites_telephone($PDOdb, $TGroups, $TRowidUser);
	
	//echo '<br><br><br>';
	/*foreach ($TLimites as $key => $value) {
		echo $key.' ';	
		print_r($value);
		echo '<br>';
	}*/
	
	
	
	$sql="SELECT dureeI, dureeE, duree, u.rowid as 'idUser', u.lastname, u.firstname, fk_rh_ressource
	FROM ".MAIN_DB_PREFIX."rh_evenement as e
	LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (u.rowid=e.fk_user)
	LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as c ON (c.fk_object = e.fk_user)
	WHERE e.type='factTel' 
	AND (e.date_debut<='".date("Y-m-d", $date_fin)."' AND e.date_debut>='".date("Y-m-d", $date_debut)."')";
	
	
	$TTelephone = array();
	$PDOdb->Execute($sql);
	$k=0;

	
	while($row = $PDOdb->Get_line()) {
		$lim = $TLimites[$row->idUser]['lim']/60;
		$dep = $row->duree;
		$choix = ($lim != 0) ? 'gen' : 'extint';
		$limI = $TLimites[$row->idUser]['limInterne']/60;
		$depI = $row->dureeI;
		$limE = $TLimites[$row->idUser]['limExterne']/60;
		$depE = $row->dureeE;
		
		if ( ($choix=='gen' && $dep>$lim) || ($choix=='extint' && ($depI>$limI || $depE>$limE))  ){ 
		
	
			$TTelephone[$k][0] = 'Orange';
			$TTelephone[$k][1] = htmlentities($row->firstname.' '.$row->name, ENT_COMPAT , 'ISO8859-1');
			$TTelephone[$k][2] = $TSim[$row->fk_rh_ressource]['numId'];//numéro de téléphone
			$TTelephone[$k][3] = $TTel[$TSim[$row->fk_rh_ressource]['tel']]; //type de téléphone
			$TTelephone[$k][4] = $choix;
			$TTelephone[$k][5] = intToString($lim);
			$TTelephone[$k][6] = intToString($limI);
			$TTelephone[$k][7] = intToString($limE);
			$TTelephone[$k][8] = intToString($dep);
			$TTelephone[$k][9] = intToString($depI);
			$TTelephone[$k][10] = intToString($depE);
			$TTelephone[$k][11] = ($k%2==0) ? 'pair' : 'impair' ; //type de téléphone
			$k++;
		
		}
	}
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/verificationTelephone.tpl.php'
		,array(
			'tabTel'=>$TTelephone
		)
		,array(
			'infos'=>array(
				'titre'=>load_fiche_titre("Vérification des consommations téléphoniques",'', 'title.png', 0, '')
				,'date_debut'=>$form->calendrier('Date de début', 'date_debut', $date_debut, 12)
				,'date_fin'=>$form->calendrier('Date de fin', 'date_fin', $date_fin, 12)
				//,'action'=>$form->hidden('action','save')
			)
			,'view'=>array(
				'mode'=>$mode
			)
		)	
		
	);
	$form->end();
	
}
