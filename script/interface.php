<?php

if (!defined('NOCSRFCHECK')) define('NOCSRFCHECK', 1);
if (!defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', 1);
define('INC_FROM_CRON_SCRIPT', true);
set_time_limit(0);
ini_set('memory_limit','1024M');

require('../config.php');
require('../lib/ressource.lib.php');

//Interface qui renvoie les emprunts de ressources d'un utilisateur
$PDOdb=new TPDOdb;

$get = isset($_REQUEST['get'])?$_REQUEST['get']:'emprunt';

_get($PDOdb, $get);

function _get(&$PDOdb, $case) {
	switch (strtolower($case)) {
		case 'emprunt':
			__out( _emprunt($PDOdb, $_REQUEST['fk_user'], $_REQUEST['date_debut'], $_REQUEST['date_fin']));
			break;
		case 'orange':
			//__out(_exportOrange($PDOdb, $_REQUEST['date_debut'], $_REQUEST['date_fin'], $_REQUEST['entity']));
			__out(_exportOrange2($PDOdb, $_REQUEST['date_debut'], $_REQUEST['date_fin'], $_REQUEST['entity'], $_REQUEST['idImport']));
			//print_r(_exportOrange($PDOdb, $_REQUEST['date_debut'], $_REQUEST['date_fin'], $_REQUEST['entity']));
			break;
		case 'autocomplete':
			__out(_autocomplete($PDOdb,$_REQUEST['fieldcode'],$_REQUEST['term']));
			break;
		default:
			__out(_exportVoiture($PDOdb, $_REQUEST['date_debut'], $_REQUEST['date_fin'], $_REQUEST['entity'],
						$_REQUEST['fk_fournisseur'], $_REQUEST['idTypeRessource'] , $_REQUEST['idImport'] ));
			break;
	}
}

//Autocomplete sur les différents champs d'une ressource
function _autocomplete(&$PDOdb,$fieldcode,$value){
	$sql = "SELECT DISTINCT(".$fieldcode.")
			FROM ".MAIN_DB_PREFIX."rh_ressource
			WHERE ".$fieldcode." LIKE '".$value."%'
			ORDER BY ".$fieldcode." ASC"; //TODO Rajouté un filtre entité ?
	$PDOdb->Execute($sql);
	
	while ($PDOdb->Get_line()) {
		$TResult[] = $PDOdb->Get_field($fieldcode);
	}
	
	$PDOdb->close();
	return $TResult;
}
