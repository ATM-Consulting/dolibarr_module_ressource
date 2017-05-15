<?php
require('../config.php');
require('../lib/ressource.lib.php');
global $conf;

if(isset($_REQUEST['type'])) {
		
		//echo $_REQUEST['type'];
		/*$TRessource = array('');
		$PDOdb =new TPDOdb;
		
		$sqlReq="SELECT rowid,libelle, numId FROM ".MAIN_DB_PREFIX."rh_ressource WHERE entity=".$conf->entity."
		AND fk_rh_ressource_type=".$_REQUEST['type'];
		$PDOdb->Execute($sqlReq);
		while($PDOdb->Get_line()) {
			$TRessource[$PDOdb->Get_field('rowid')] = $PDOdb->Get_field('libelle').' '.$PDOdb->Get_field('numId');
			}*/
		
		$TRessource = getRessource($_REQUEST['type']);
		echo json_encode($TRessource);
		
		exit();
	}
	