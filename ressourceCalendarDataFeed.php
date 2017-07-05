<?php


//TODO bullshit code, need rewrite !
require('config.php');
require('./lib/ressource.lib.php');

dol_include_once('/ressource/class/ressource.class.php');

$PDOdb=new TPDOdb;

    	$idRessourceSup = 0;
    	
    	if (isset($_REQUEST['id'])){
			//on regarde si la ressource courante est une sous-ressource
			$sql = "SELECT fk_rh_ressource FROM ".MAIN_DB_PREFIX."rh_ressource 
			WHERE rowid=".$_REQUEST['id']."
			AND entity IN (0, ".$conf->entity.")";
			$PDOdb->Execute($sql);
			if ($row=$PDOdb->Get_line()) {
				$idRessourceSup = $row->fk_rh_ressource ;
			}
		}
		
		if ($idRessourceSup!= 0){$type = 0;}
		else {$type = $_REQUEST['type'];}
		//echo $sql.' '.$id;
		$ret = listCalendarByRange($PDOdb,  GETPOST('start'), GETPOST('end'), 
					$type, $_REQUEST['id'], $_REQUEST['fk_user'], $_REQUEST['typeEven'],$idRessourceSup);
        

		echo json_encode($ret); 

function listCalendarByRange(&$PDOdb, $date_start, $date_end, $idTypeRessource=0, $idRessource = 0,$fk_user = 0, $typeEven = 'all', $idRessourceSup = 0){
	global $user,$conf,$langs,$db;
  $TEvent = array();
  
  $TTypeEvent = getTypeEvent($idTypeRessource);
 // $TRessource = getRessource(0);
 // $TUser = getUsers();
 
  
	$sql = "SELECT e.rowid,  e.date_debut, e.date_fin, e.isAllDayEvent, e.fk_user, e.color, e.type, e.fk_rh_ressource 
	FROM ".MAIN_DB_PREFIX."rh_evenement as e 
	LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (e.fk_rh_ressource = r.rowid)
	WHERE ";
	
	$sql .= " 1 ";
	
	$sql.= " AND e.date_debut<='".$date_end."' AND e.date_fin>='".$date_start."'"; 

	//$sql .= " AND date_debut<='".php2MySqlTime($ed)."' AND date_fin >= '". php2MySqlTime($sd)."' ";
	//$sql .= " `date_debut` between '"
    //  .php2MySqlTime($sd)."' and '". php2MySqlTime($ed)."'";
    
	if ($idTypeRessource!=0) {$sql .= " AND r.fk_rh_ressource_type=".$idTypeRessource;}
	
	if ($idRessource!=0) {
		if ($idRessourceSup!=0){	
			$sql .= " AND (e.fk_rh_ressource=".$idRessource." OR e.fk_rh_ressource=".$idRessourceSup.") ";}
		else {$sql .= " AND e.fk_rh_ressource=".$idRessource;}
	}
	if ($fk_user!=0) {
		$sql .= " AND e.fk_user=".$fk_user;
	}
	if ($typeEven && $typeEven!='all') {
		$sql .= " AND e.type='".$typeEven."'";
	}
	//echo $sql;
	/*else{
    	$sql.=" AND e.fk_rh_ressource=".$idRessource;
	}//*/
	
	if (!$user->rights->ressource->agenda->viewAgenda){
    	$sql.=" AND e.fk_user=".$user->id;
	}
//	echo '     '.$sql.'      ';exit;
    $Tab = $PDOdb->ExecuteAsArray($sql);
//	var_dump($PDOdb);exit;
    foreach($Tab as &$row) {

      if ($row->type == 'emprunt'){
      	$url= 'attribution.php?id='.$row->fk_rh_ressource.'&idEven='.$row->rowid.'&action=view';
      }
	  else {
	  	$url= 'evenement.php?id='.$row->fk_rh_ressource.'&idEven='.$row->rowid.'&action=view';
	  }
	 
	  $moreOneDay=(int)( strtotime($row->date_debut) < strtotime($row->date_fin) );
		
	 
	  //on écrit l'intitulé du calendrier en fonction des données de la fonction
	  $label= '';
	
	  $ressource = new TRH_Ressource();
	  $ressource->load($PDOdb, $row->fk_rh_ressource);
	  $label.=(String) $ressource;
	  $label.=' [ '.dol_print_date(strtotime($row->date_debut)).' - '.dol_print_date(strtotime($row->date_fin)).' ]';
	  $label.=  ($typeEven=='all') ? $TTypeEvent[$row->type] : '' ;
	 
//var_dump($row);exit;

	  $timeDebut = strtotime($row->date_debut);
	  $timeFin= strtotime($row->date_fin);
	  
	  $userRess=new User($db);
	  $userRess->fetch($row->fk_user);
	  
	  if (empty($label)){
	  	$label=' Emprunt ';
	  }
	  
		 $TEvent[]=array(
		 		'id'=>$row->rowid
		 		,'title'=>$label
		 		,'allDay'=>(int)$row->isAllDayEvent
		 		,'start'=>(empty($timeDebut) ? '' : date('Y-m-d H:i:s',(int)$timeDebut))
		 		,'end'=>(empty($timeFin) ? '' : date('Y-m-d H:i:s',(int)$timeFin))
		 		,'url'=>$url
		 		,'editable'=>0
		 		,'color'=>'#66ff66'
		 		,'isDarkColor'=>0
		 		,'colors'=>''
		 		,'note'=>''
		 		,'statut'=>''
		 		,'fk_soc'=>0
		 		,'fk_contact'=>0
		 		,'fk_user'=>$row->fk_user
		 		,'fk_project'=>0
		 		,'societe'=>''
		 		,'contact'=>''
		 		,'user'=>$userRess->getFullName($langs)
		 		,'project'=>''
		 		,'more'=>''
		 );
	 
      
    }
	
  return $TEvent;
}


