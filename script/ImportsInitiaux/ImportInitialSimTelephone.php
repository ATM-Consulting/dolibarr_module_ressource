<?php
set_time_limit(0);
ini_set('memory_limit','512M');


require('../../config.php');
require('../../class/evenement.class.php');
require('../../class/ressource.class.php');
require('../../lib/ressource.lib.php');

global $conf;
$PDOdb=new TPDOdb;
// relever le point de départ
$timestart=microtime(true);


//on charge quelques listes pour avoir les clés externes.
$TTrigramme = array();
$sql="SELECT rowid, lastname, firstname,login FROM ".MAIN_DB_PREFIX."user WHERE entity IN (0,".$conf->entity.")";
$PDOdb->Execute($sql);
while($PDOdb->Get_line()) {
	 /*strtoupper($PDOdb->Get_field('firstname')).' '.strtoupper($PDOdb->Get_field('name'))*/
	$TTrigramme[strtolower($PDOdb->Get_field('login'))] = $PDOdb->Get_field('rowid');
	}


$TGroups = array();
$sql="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup WHERE entity IN (0,".$conf->entity.")";
$PDOdb->Execute($sql);
while($PDOdb->Get_line()) {
	$TGroups[$PDOdb->Get_field('nom')] = $PDOdb->Get_field('rowid');
}
//------------------------------------------------------------------------------------------
//--------------------------------------Import des téléphones-------------------------------
//------------------------------------------------------------------------------------------

$idTel = getIdType('telephone');
$idSim = getIdType('carteSim');
$TUserInexistants = array();
$TUser = array();
$sql="SELECT rowid, lastname, firstname FROM ".MAIN_DB_PREFIX."user WHERE entity IN (0,".$conf->entity.") ";
$PDOdb->Execute($sql);
while($PDOdb->Get_line()) {
	$TUser[strtoupper($PDOdb->Get_field('name').' '.$PDOdb->Get_field('firstname'))] = $PDOdb->Get_field('rowid');
	}
//print_r($TUser);exit();

$nomFichier = "reglesAppels.csv";
echo 'Traitement du fichier '.$nomFichier.' : <br>';
$cptTel = 0;
$cptAttr = 0;
$TNumero = getIDRessource($PDOdb, $idSim);

//début du parsing
$numLigne = 0;
if (($handle = fopen("../fichierImports/".$nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle, 0,'\r')) != false){
		if ($numLigne >=1){
			$infos = explode(';', $data[0]);
			//print_r($infos);
			//echo '<br>';
			$nom = $infos[26];
			$prenom = $infos[27];
			$numIdSim = $infos[4]; //on prend le nom GSM complet : 336...
			
			if (empty($nom)){
				null;
			}
			/*else if (empty($TUser[strtoupper($nom.' '.$prenom)])){
				$TUserInexistants[$nom.' '.$prenom] = 1;
				null; //si l'user n'existe pas
			}*/
			else if (!empty($TNumero[$numIdSim])){
				//echo $numIdSim." déjà créé.<br>";
				null;//si le numéro existe
			}
			else {
				$tab = explode('(', $infos[23]);
				$modle = strtolower($tab[0]);
				if ( (strpos($modle,'samsung')!== FALSE) || (strpos($modle,'galaxy')!== FALSE) || (strpos($modle,'s2')!== FALSE) ){
					$marque = 'Samsung';
				}
				else if (strpos($modle,'htc')  !== FALSE){
					$marque = 'HTC';
				}
				else if (strpos($modle,'iphone')  !== FALSE){
					$marque = 'Apple';
				}
				else {
					$marque = '';
				}
				$modle = ucwords($modle);
				//echo 'Marque : '.$marque.'    modèle : '.$modle.'<br>';
				
			
				$tel = new TRH_Ressource;
				$tel->fk_rh_ressource_type = $idTel;
				$tel->load_ressource_type($PDOdb);
				$tel->numId = 'Téléphone n°'.$cptTel;
				$tel->libelle = empty($marque) ? 'Téléphone' : $marque.' '.$modle;
				$tel->fk_proprietaire = $TAgence[strtolower($infos[3])];
				$tel->set_date('date_vente', '');
				$tel->set_date('date_garantie', '');
				$tel->marquetel = $marque;	
				$tel->modletel	= $modle;
				$tel->save($PDOdb);
				
				$sim = new TRH_Ressource;
				$sim->fk_rh_ressource_type = $idSim;
				$sim->fk_rh_ressource = $tel->getId(); //association de la carte Sim au Téléphone.
				$sim->load_ressource_type($PDOdb);
				$sim->numId = $numIdSim;
				$sim->libelle = 'Carte Sim '.$numIdSim;
				$sim->fk_proprietaire = $TAgence[strtolower($infos[3])];
				$sim->set_date('date_vente', '');
				$sim->set_date('date_garantie', '');
				$sim->numerotel = $numIdSim;
				$sim->coutminuteint = 0.09;
				$sim->coutminuteext = 0.09;
				$sim->save($PDOdb);
				$TNumero[$numIdSim] = $sim->getId();
				
				if (!empty($TUser[strtoupper($nom.' '.$prenom)])){
					$cptAttr++;
					$emprunt = new TRH_Evenement;
					$emprunt->type = 'emprunt';
					$emprunt->fk_user = $TUser[strtoupper($nom.' '.$prenom)];
					$emprunt->fk_rh_ressource = $tel->getId();
					$emprunt->fk_rh_ressource_type = $idTel;
					$emprunt->set_date('date_debut', '01/01/2013');
					$emprunt->set_date('date_fin', '31/12/2013');
					$emprunt->save($PDOdb);
				}
				else {
					$TUserInexistants[$nom.' '.$prenom] = 1;
				}
				$cptTel ++;
			}		
		}
		$numLigne++;
	}
}
echo 'Liste des '.count($TUserInexistants).' utilisateurs non trouvés dans la base : <br>'; 
foreach ($TUserInexistants as $nom => $value) {
	echo $nom.', ';
}
echo '<br>';
echo $cptTel.' telephone créés importes.<br>';
echo $cptAttr.' Telephones liés à un user.<br>'; 

 


//Fin du code PHP : Afficher le temps d'éxecution
$timeend=microtime(true);
$page_load_time = number_format($timeend-$timestart, 3);
echo 'Fin du traitement. Durée : '.$page_load_time . " sec<br><br>";
$PDOdb->close();

