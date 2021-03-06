<?php

/**
 * Importation de la facture Total
 * On créé un évenement par ligne de ce fichier
 * et une évenement de type facture
 */

/*  
require('../config.php');
require('../class/evenement.class.php');
require('../class/ressource.class.php');
require('../lib/ressource.lib.php');//*/

global $conf;

$PDOdb=new TPDOdb;
$PDOdbEvent=new TPDOdb;

// relever le point de départ
$timestart=microtime(true);

$TUser = array();
$sql="SELECT rowid, lastname, firstname FROM ".MAIN_DB_PREFIX."user WHERE entity=".$conf->entity;
$PDOdb->Execute($sql);
while($PDOdb->Get_line()) {
	$TUser[strtolower($PDOdb->Get_field('firstname').' '.$PDOdb->Get_field('lastname'))] = $PDOdb->Get_field('rowid');
}
$idVoiture = getIdType('voiture');
$idEuromaster = getIdSociete($PDOdb, 'euromaster');
if (!$idEuromaster){echo 'Pas de fournisseur (tiers) du nom de Euromaster !';exit();}
$TRessource = chargeVoiture($PDOdb);
$TNonAttribuee = array();
$TNoPlaque = array();
if (empty($nomFichier)){$nomFichier = "./fichierImports/B60465281_Masterplan-CPRO_M_20130430.csv";}
$entity = (isset($_REQUEST['entity'])) ? $_REQUEST['entity'] : $conf->entity;
$message = 'Traitement du fichier '.$nomFichier.' : <br><br>';

//pour avoir un joli nom, on prend la chaine après le dernier caractère /  et on remplace les espaces par des underscores
$idImport = Tools::url_format(basename($nomFichier), false, true);

$PDOdb->Execute("DELETE FROM ".MAIN_DB_PREFIX."rh_evenement WHERE idImport='$idImport'");


$idRessFactice = createRessourceFactice($PDOdb, $idVoiture, $idImport, $entity, $idEuromaster);
$idSuperAdmin = getIdSuperAdmin($PDOdb);

$ressource_source = new TRH_Evenement;
$ressource_source->load_liste($PDOdb);

//début du parsing
$numLigne = 0;
if (($handle = fopen($nomFichier, "r")) !== FALSE) {
	
	?>
<table class="border">
	<tr>
		<th>Message</th>
		<th>Ressource</th>
		<th>Montant</th>
		<th>TVA</th>
		<th>Info</th>
	</tr>

<?
	
	
	
	while(($infos = fgetcsv($handle, 0,';','"')) != false) {
		//echo 'Traitement de la ligne '.$numLigne.'...';
		if ($numLigne >=1 && !empty($infos)) {
			
			$plaque = str_replace('-','',$infos[0]);
			$plaque = str_replace(' ','',$plaque);
			
			//if(empty($plaque)) continue; si avoir plaque vide alors faut faire le taf
			
			$timestamp = mktime(0,0,0,intval(substr($infos[3], 3,2)),intval(substr($infos[3], 0,2)), intval(substr($infos[3], 6,4)));
			$date = date("Y-m-d", $timestamp);
		
			$numero =  $infos[24];
		
			
			$style = '';
			if (!empty($plaque) && !empty($TRessource[$plaque])){
				$idUser = ressourceIsEmpruntee($PDOdb, $TRessource[$plaque], $date);
				if ($idUser==0){ //si il trouve, on l'affecte à l'utilisateur 
					$idUser = $idSuperAdmin;
					$cptNoAttribution++;
					$info = 'Voiture non attribué le '.$date.' : '.$plaque.'<br>';
				}
				else {
					$info = 'Ok';	
				}
				$id_ressource = $TRessource[$plaque];
				
				$ressource = new TRH_Ressource();
                $ressource->load($PDOdb, $TRessource[$plaque]);
                $typeVehicule = $ressource->typevehicule;
				
			}	
			else {
				$idUser = $idSuperAdmin;
				$TNoPlaque[$plaque] = 1 ;
				$cptNoVoiture ++;
				
				$id_ressource = $idRessFactice;
				
				$info = 'Véhicule non trouvé';
				$style = 'background-color:red;';
			}
			
			?>
			<tr style="<?=$style ?>">
				<td>Ajout facture <?=$numero ?></td>
				<td><?=$plaque ?></td>
			<?
		
			
		
			$temp = new TRH_Evenement;
			
			$loyerHT = (double)strtr($infos[9], ',','.');
			$loyerTTC = strtr($infos[27], ',','.');
			 
			$taux = '20';
            if($typeVehicule == "VU") { null; }
            else {
                   $taux="0";
                   $loyerHT = $loyerTTC;
            } 
			
			$temp->fk_rh_ressource = $id_ressource;
			$temp->type = 'facture';
			$temp->fk_user = $idUser;
			$temp->set_date('date_debut', $date);
			$temp->set_date('date_fin', $date);
			$temp->coutEntrepriseHT = $loyerHT;
			$temp->coutTTC = $loyerTTC;
			$temp->coutEntrepriseTTC = $loyerTTC;
			$temp->numFacture = $numero;
			$temp->motif = $infos[7];
			$temp->commentaire = $infos[6];
			$temp->fk_fournisseur = $idEuromaster;
			$temp->entity = $entity;
			
			//$ttva = array_keys($temp->TTVA , floatval());
			
			$temp->TVA = getTVAId($ressource_source->TTVA,$taux);
			//$temp->compteFacture = $infos[13];
			$temp->idImport = $idImport;
			$temp->numFacture = $numero;
			$temp->date_facture = dateToInt($infos[25]);
			
			
			$temp->save($PDOdbEvent);
		
			?><td><?=$infos[27] ?></td><td><?=$ressource_source->TTVA[$temp->TVA] ?></td><td><?=$info ?></td></tr><?
		
		}
		$numLigne++;
		
	}
	?></table><?
	//Fin du code PHP : Afficher le temps d'éxecution et les résultats.
	if (!empty($TNoPlaque)){
		$message .= 'Voitures non trouvées :<br>';}
	foreach($TNoPlaque as $plaque=>$rien){
		$message .= $plaque.'<br>';}
	
	if (!empty($TNonAttribuee)){
		$message .= 'Voitures non attribué :<br>';}
	foreach($TNonAttribuee as $date=>$plaque){
		$message.= $plaque.' non attribuée le '.$date.'<br>';}
	$message .= '<br>';
	
	$timeend=microtime(true);
	$page_load_time = number_format($timeend-$timestart, 3);
	$message .= 'Fin du traitement. Durée : '.$page_load_time . " sec.<br><br>";
	send_mail_resources('Import - Factures Euromaster',$message);
	echo $message;
	
	
	
}

function getTVAId(&$TTVA, $tva) {
		
	foreach($TTVA as $id=>$taux) {
		$ecart = abs((double)$tva-(double)$taux);
		if($ecart <= 1) return $id;
		/*else print "($taux $tva)".$ecart.'<br />';*/
	}
	//print_r ($tva);
	return -1;
	
}

function chargeAssocies(&$PDOdb){
	global $conf;
	$sqlReq="SELECT rowid, fk_rh_ressource 
	FROM ".MAIN_DB_PREFIX."rh_ressource 
	WHERE entity=".$conf->entity;
	$TAssoc = array();
	$PDOdb->Execute($sqlReq);
	while($PDOdb->Get_line()) {
		$TAssoc[$PDOdb->Get_field('rowid')] = $PDOdb->Get_field('fk_rh_ressource');
	}
	return $TAssoc;
	
	
}

function getUser(&$listeEmprunts , $id, $jour){
	if (empty($listeEmprunts[$id])){return 0;}
	foreach ($listeEmprunts[$id] as $k => $value) {
		if ( ($value['debut'] <= date("Y-m-d",$jour))  
			&& ($value['fin'] >= date("Y-m-d",$jour)) ){
				return $value['fk_user'];
		}
	}
	return 0;
}

function chargeEmprunts(&$PDOdb){
	global $conf;
	$sqlReq="SELECT DISTINCT e.date_debut, e.date_fin , e.fk_user, e.fk_rh_ressource, u.firstname, u.lastname 
	FROM ".MAIN_DB_PREFIX."rh_evenement as e  
	LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (e.fk_user=u.rowid) 
	WHERE e.type='emprunt'
	AND e.entity=".$conf->entity."
	ORDER BY date_debut";
	$TUsers = array();
	$PDOdb->Execute($sqlReq);
	while($PDOdb->Get_line()) {
		$TUsers[$PDOdb->Get_field('fk_rh_ressource')][] = array(
			'debut'=>$PDOdb->Get_field('date_debut')
			,'fin'=>$PDOdb->Get_field('date_fin')
			,'fk_user'=>$PDOdb->Get_field('fk_user')
			,'user'=>$PDOdb->Get_field('firstname').' '.$PDOdb->Get_field('lastname')
		);
	}
	return $TUsers;
}

function chargeVoiture(&$PDOdb){
	global $conf;
	$TRessource = array();
	$sql="SELECT r.rowid as 'ID', t.rowid as 'IdType', r.numId FROM ".MAIN_DB_PREFIX."rh_ressource as r 
	LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource_type as t on (r.fk_rh_ressource_type = t.rowid)
	WHERE 1 AND (t.code='voiture' OR t.code='cartearea') "; // r.entity=".$conf->entity."

	$PDOdb->Execute($sql);
	while($PDOdb->Get_line()) {
		$TRessource[$PDOdb->Get_field('numId')] = $PDOdb->Get_field('ID');
		}
	return $TRessource;
}



/*
 * prend un format d/m/Y et renvoie un timestamp
 */
function dateToInt($chaine){
	return mktime(0,0,0,substr($chaine,3,2),substr($chaine,0,2),substr($chaine,6,4));
}
