<?php

/**
 * Importation de la facture Total
 * On créé deux évenements par ligne de ce fichier : un loyer et un gestion+entretien
 * 
 */

/*
require('../config.php');
require('../class/evenement.class.php');
require('../class/ressource.class.php');
require('../lib/ressource.lib.php');
require('../class/contrat.class.php');
//*/

global $conf;
$entity = (isset($_REQUEST['entity'])) ? $_REQUEST['entity'] : $conf->entity;

$PDOdb=new TPDOdb;

// relever le point de départ
$timestart=microtime(true);

$idVoiture = getIdType('voiture');
$idParcours = getIdSociete($PDOdb, 'parcours');
if (!$idParcours){echo 'Pas de fournisseur (tiers) du nom de Parcours !';exit();}

if ($idParcours == 0){echo 'Aucun fournisseur du nom de "Parcours" ! ';exit;}

$TUser = array();// TODO mais à quoi ça sert ?!
$sql="SELECT rowid, lastname, firstname FROM ".MAIN_DB_PREFIX."user WHERE entity=".$conf->entity;
$PDOdb->Execute($sql);
while($PDOdb->Get_line()) {
	$TUser[strtolower($PDOdb->Get_field('firstname').' '.$PDOdb->Get_field('lastname'))] = $PDOdb->Get_field('rowid');
}

//chargement d'une liste :  plaque => typeVehicule (vu ou vp) 
$TVuVp = array();
$sql="SELECT rowid,  numId, typeVehicule FROM ".MAIN_DB_PREFIX."rh_ressource 
		WHERE entity=".$conf->entity." 
		AND fk_rh_ressource_type=".$idVoiture;
$PDOdb->Execute($sql);
while($row = $PDOdb->Get_line()) {
	$TVuVp[strtolower($row->numId)] = $row->typeVehicule;
}
//pre($conf->global->MAIN_INFO_SOCIETE_COUNTRY,1);exit;

list($fk_pays) = explode(':',$conf->global->MAIN_INFO_SOCIETE_COUNTRY);
//chargement des TVA.
$TTVA = array();
$sqlReq="SELECT rowid, taux FROM ".MAIN_DB_PREFIX."c_tva WHERE fk_pays=".$fk_pays;
$PDOdb->Execute($sqlReq);
while($PDOdb->Get_line()) {
	$TTVA[$PDOdb->Get_field('taux')] = $PDOdb->Get_field('rowid');
}


if (empty($nomFichier)){ exit("Aucun fichier fourni"); }
$message = 'Traitement du fichier '.$nomFichier.' : <br><br>';

$idImport = Tools::url_format(basename($nomFichier), false, true);

$PDOdb->Execute("DELETE FROM ".MAIN_DB_PREFIX."rh_evenement WHERE idImport='$idImport'");


$idRessFactice = createRessourceFactice($PDOdb, $idVoiture, $idImport, $entity, $idParcours);
$idSuperAdmin = getIdSuperAdmin($PDOdb);

$cptFactureLoyer = 0;
$cptFactureGestEntre = 0;
$cptNoAttribution = 0;
$TRessource = chargeVoiture($PDOdb);

//début du parsing
$numLigne = 0;
if (($handle = fopen($nomFichier, "r")) !== FALSE) {
	
	?>
<table class="border">
	<tr>
		<th>Message</th>
		<th>Ressource</th>
		<th>VU/VP</th>
		<th>Montant Loyer</th>
		<th>Montant Entretient</th>
		<th>Info</th>
	</tr>

<?php
	
	$totalHT = $totalTTC = 0;
	
	while(($infos = fgetcsv($handle, 0,';')) != false){
		//echo 'Traitement de la ligne '.$numLigne.'...';
	
		$numFacture = $infos[1];
		
		if ($numLigne >=1 && $numFacture!=''){
			//print_r($infos);
			
			$plaque = str_replace('-','',$infos[8]);
			$plaque = str_replace(' ','',$plaque);
			
			
			
			$timestamp = mktime(0,0,0,substr($infos[3], 3,2),substr($infos[3], 0,2), substr($infos[3], 6,4));
			$date = date("Y-m-d", $timestamp);
			
			?>
			<tr>
				<td>Ajout facture <?php echo $numFacture ?></td>
				<td><?php echo $plaque ?></td>
			<?php
		
			
			
			if (empty($plaque)){
				null;
			}
			
			if (!empty($TRessource[strtoupper($plaque)])){
				$idUser = ressourceIsEmpruntee($PDOdb, $TRessource[$plaque], $date);
				if ($idUser==0){ //si il trouve, on l'affecte à l'utilisateur 
					$idUser = $idSuperAdmin;
					$cptNoAttribution++;
					$msgInfo =  'Voiture non attribué le '.$date;
				}
				else {
					$msgInfo = 'Ok';
				}
				
				$id_ressource = $TRessource[$plaque];
				
				$ressource = new TRH_Ressource();
				$ressource->load($PDOdb, $id_ressource);
				$typeVehicule = $ressource->typevehicule;
			}
			else {
				$id_ressource = $idRessFactice;
				
				$idUser = $idSuperAdmin;
				$msgInfo = 'Pas de voiture correspondante';
				$cptNoVoiture ++;
				$typeVehicule = $infos[9];	
			}
			
			echo '<td>'.$typeVehicule.'</td>';
			
				//echo $idUser.'<br>';
				
			$PDOdb=new TPDOdb;
			
			/*
				 * Correction des taux d'import pour traitement retour
				 */
			//$typeVehicule = $info[9];	 
				 
				 
			$loyerTTC = (double)price2num($infos[22]);
			$loyerHT = (double)price2num($infos[12]);
		
			$taux = '20';
			if($typeVehicule == "VU") { null; }
			else {
				$taux='0';
				$loyerHT = $loyerTTC;
			} 
			
			//FACTURE SUR LE LOYER
			$fact = new TRH_Evenement;
			$fact->type = 'factureloyer';
			$fact->numFacture = $numFacture;
			$fact->fk_rh_ressource = $id_ressource;
			$fact->fk_user = $idUser;
			$fact->fk_rh_ressource_type = $idVoiture;
			$fact->motif = 'Facture mensuelle Parcours : Loyer';
			$fact->commentaire = 'Facture lié au contrat '.$infos[0];
			$fact->set_date('date_debut', $infos[10]);
			$fact->set_date('date_fin', $infos[4]);
			$fact->coutTTC = $loyerTTC;
			$fact->coutEntrepriseTTC =  $loyerTTC;
			$fact->TVA= $TTVA[$taux];
			$fact->coutEntrepriseHT = $loyerHT;
			$fact->entity =$entity;
			$fact->fk_fournisseur = $idParcours;
			$fact->idImport = $idImport;
			$fact->date_facture = dateToInt($infos[3]);
			$fact->save($PDOdb);
			$cptFactureLoyer++;
				
				
		
			$loyerTTC = (double)price2num($infos[23]) + (double)price2num($infos[24]); 
			$loyerHT = (double)price2num($infos[13]) + (double)price2num($infos[14]) ; 
		
			$taux = '20';
			if($typeVehicule == "VU") { null; }
			else {
				$taux="0";
				$loyerHT = $loyerTTC;
			} 
			
				
			//FACTURE SUR L'ENTRETIEN ET LA GESTION
			$factEnt = new TRH_Evenement;
			$factEnt->type = 'facturegestionetentretien';
			$factEnt->numFacture = $numFacture;
			$factEnt->fk_rh_ressource = $TRessource[$plaque];
			$factEnt->fk_user = $idUser;
			$factEnt->fk_rh_ressource_type = $idVoiture;
			$factEnt->motif = 'Facture mensuelle Parcours : Gestion et Entretien';
			$factEnt->commentaire = 'Facture lié au contrat '.$infos[0].',<br>
									Entretien TTC :'.floatval(strtr($infos[24], ',','.')).'€,<br>
									Gestion TTC :'.floatval(strtr($infos[23], ',','.')).'€';
			$factEnt->set_date('date_debut', $infos[10]);
			$factEnt->set_date('date_fin', $infos[4]);
			$factEnt->coutTTC = $loyerTTC;
			$factEnt->coutEntrepriseTTC = $loyerTTC;
			$factEnt->TVA= $TTVA[$taux];
			$factEnt->coutEntrepriseHT = $loyerHT ;
			$factEnt->fk_fournisseur = $idParcours;
			$factEnt->idImport = $idImport;
			$factEnt->date_facture = dateToInt($infos[3]);
			$factEnt->entity =$entity;
			$factEnt->save($PDOdb);
			$cptFactureGestEntre++;
				
			$totalHT+=	$fact->coutEntrepriseHT + $factEnt->coutEntrepriseHT;
			$totalTTC+=	$fact->coutEntrepriseTTC + $factEnt->coutEntrepriseTTC;
				
			?><td><?php echo $fact->coutEntrepriseHT.'/'.$fact->coutEntrepriseTTC ?></td><td><?php echo $factEnt->coutEntrepriseHT.'/'.$factEnt->coutEntrepriseTTC ?></td><td><?php echo $msgInfo ?></td></tr><?php
						
		}
		$numLigne++;
	
	
	} //while
	
	echo '</table>';
	
	//Fin du code PHP : Afficher le temps d'éxecution et le bilan.
	$message .='Total HT = ' .$totalHT.' / Total TTC = '.$totalTTC.' <br>';
	$message .= $cptNoVoiture.' plaques sans correspondance.<br>';
	$message .= $cptNoAttribution.' voitures non attribués<br>';
	$message .= $cptFactureLoyer.' factures loyer importés.<br>';
	$message .= $cptFactureGestEntre.' factures gestion+entretien importés.<br>';
	$timeend=microtime(true);
	$page_load_time = number_format($timeend-$timestart, 3);
	$message .= '<br>Fin du traitement. Durée : '.$page_load_time . " sec.<br><br>";
	echo $message;
	send_mail_resources('Import - Factures Parcours',$message);
}

function chargeVoiture(&$PDOdb){
	global $conf;
	$TRessource = array();
	$sql="SELECT r.rowid as 'ID', t.rowid as 'IdType', r.numId FROM ".MAIN_DB_PREFIX."rh_ressource as r 
	LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource_type as t on (r.fk_rh_ressource_type = t.rowid)
	WHERE (t.code='voiture' OR t.code='carte') ";
	$PDOdb->Execute($sql);
	while($PDOdb->Get_line()) {
		//$idVoiture = $PDOdb->Get_field('IdType');
		$TRessource[$PDOdb->Get_field('numId')] = $PDOdb->Get_field('ID');
		}
	return $TRessource;
}



	
