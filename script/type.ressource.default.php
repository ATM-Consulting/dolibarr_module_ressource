<?php
/*
 * init ressource par défaut
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 * 
 */
 	define('INC_FROM_CRON_SCRIPT', true);
	
	require('../config.php');
	require('../class/ressource.class.php');
	require('../class/contrat.class.php');
	require('../class/evenement.class.php');
	require('../class/regle.class.php');

	$PDOdb=new TPDOdb;
	$PDOdb->db->debug=true;

//Création des types d'évenement par défaut.
$tempEven = new TRH_Type_Evenement;
$tempEven->chargement($PDOdb, '', 'all', '0000', 'faux', 0);
$tempEven = new TRH_Type_Evenement;
$tempEven->chargement($PDOdb, 'Divers', 'divers', '0000', 'faux', 0);
$tempEven = new TRH_Type_Evenement;
$tempEven->chargement($PDOdb, 'Réparation', 'reparation', '0000', 'faux', 0);
$tempEven = new TRH_Type_Evenement;
$tempEven->chargement($PDOdb, 'Accident', 'accident', '0000', 'faux', 0);
$tempEven = new TRH_Type_Evenement;
$tempEven->chargement($PDOdb, 'Facture', 'facture', '0000', 'faux', 0);




//VOITURE
	$tempType = new TRH_Ressource_type;
	$tempType->chargement($PDOdb, 'Voiture', 'voiture', 1);
$cpt = 0;
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb, 'Immatriculation', 'immatriculation','chaine', 0, $cpt, '', 1, $tempType->rowid);$cpt++;
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Marque', 'marqueVoit', 'chaine',0, $cpt, '', 1, $tempType->rowid);$cpt++;
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Modèle', 'modleVoit', 'chaine',0, $cpt, '', 1, $tempType->rowid);$cpt++;
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Modèle version commerciale', 'modleVoitversioncomm', 'chaine',0, $cpt, '', 1, $tempType->rowid);$cpt++;
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Bail', 'bailVoit', 'liste',0, $cpt, 'IMMO;LOCATION;CREDIT BAIL;EN ATTENTE', 1, $tempType->rowid, "oui");$cpt++;
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Puissance Fiscale', 'pf', 'chaine',0, $cpt, '', 1, $tempType->rowid);$cpt++;
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Localisation', 'localisationvehicule', 'chaine',1, $cpt, '', 1, $tempType->rowid);$cpt++;
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Type de véhicule', 'typeVehicule', 'liste',0, $cpt, 'VU;VP', 1, $tempType->rowid);$cpt++;
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Clé', 'cle', 'checkbox',1, $cpt, '', 1, $tempType->rowid);$cpt++;
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Kit de Sécurité', 'kit', 'checkbox',1, $cpt, '', 1, $tempType->rowid);$cpt++;

	$tempEven = new TRH_Type_Evenement;
	$tempEven->chargement($PDOdb, 'Facture Loyer', '','0000', 'faux', $tempType->rowid);
	$tempEven = new TRH_Type_Evenement;
	$tempEven->chargement($PDOdb, 'Facture Gestion et Entretien', '','0000', 'faux', $tempType->rowid);
	$tempEven = new TRH_Type_Evenement;
	$tempEven->chargement($PDOdb, 'Changement de pneu', '', '0000', 'faux', $tempType->rowid);


//CARTE TOTAL
	$tempType = new TRH_Ressource_type;
	$tempType->chargement($PDOdb, 'Carte Total', 'cartetotal', 1);
	$cpt = 0;

	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Numéro carte', 'totalnumcarte', 'chaine',0, $cpt, '', 1, $tempType->rowid);$cpt++;
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Compte support', 'totalcomptesupport', 'chaine',1, $cpt, '', 1, $tempType->rowid);$cpt++;
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Type support', 'totaltypesupport', 'chaine',1, $cpt, '', 1, $tempType->rowid);$cpt++;
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Infos saisis en station', 'totalinfostation', 'chaine',1, $cpt, '', 1, $tempType->rowid);$cpt++;
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Libellé estampé', 'totallibeestampe', 'chaine',1, $cpt, '', 1, $tempType->rowid);$cpt++;
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Adresse estampée', 'totaladresseestampe', 'chaine',1, $cpt, '', 1, $tempType->rowid);$cpt++;
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Type code confidentiel', 'totaltypecodeconfidentiel', 'chaine',1, $cpt, '', 1, $tempType->rowid);$cpt++;
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Carburant', 'totalcarburant', 'chaine',1, $cpt, '', 1, $tempType->rowid);$cpt++;
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Plafond carburant', 'totalplafondcarburant', 'chaine',1, $cpt, '', 1, $tempType->rowid);$cpt++;
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Type plafond Carburant', 'totaltypeplafond', 'chaine',1, $cpt, '', 1, $tempType->rowid);$cpt++;
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Produit', 'totalproduit', 'chaine',1, $cpt, '', 1, $tempType->rowid);$cpt++;
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Périodicité plafond carburant', 'totalperiodiciteplafond', 'chaine',1, $cpt, '', 1, $tempType->rowid);$cpt++;
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Quantité plafond carburant', 'totalqtplafond', 'float',1, $cpt, '', 1, $tempType->rowid);$cpt++;
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Unité plafond carburant', 'totaluniteplafond', 'chaine',1, $cpt, '', 1, $tempType->rowid);$cpt++;
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Options service', 'totaloptionservice', 'chaine',1, $cpt, '', 1, $tempType->rowid);$cpt++;
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Plafond service', 'totalplafondservice', 'chaine',1, $cpt, '', 1, $tempType->rowid);$cpt++;
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Service', 'totalserviceplafondservice', 'chaine',1, $cpt, '', 1, $tempType->rowid);$cpt++;
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Périodicité plafond service', 'totalperiodiciteplafondservice', 'chaine',1, $cpt, '', 1, $tempType->rowid);$cpt++;
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Quantité plafond service', 'totalqtplafondservice', 'float',1, $cpt, '', 1, $tempType->rowid);$cpt++;
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Unité plafond service', 'totaluniteplafondservice', 'chaine',1, $cpt, '', 1, $tempType->rowid);$cpt++;
	
	
	$tempEven = new TRH_Type_Evenement;
	$tempEven->chargement($PDOdb, 'Péage TVA', '', '0000', 'faux', $tempType->rowid);
	$tempEven = new TRH_Type_Evenement;
	$tempEven->chargement($PDOdb, 'FRAIS DE SERVICE PEAGE', '','0000', 'faux', $tempType->rowid);
	$tempEven = new TRH_Type_Evenement;
	$tempEven->chargement($PDOdb, 'Gazole Premier', '','0000', 'faux', $tempType->rowid);
	$tempEven = new TRH_Type_Evenement;
	$tempEven->chargement($PDOdb, 'Gazole Excellium','', '0000', 'faux', $tempType->rowid);
	$tempEven = new TRH_Type_Evenement;
	$tempEven->chargement($PDOdb, 'GESTION +', '',  '0000', 'faux', $tempType->rowid);
	$tempEven = new TRH_Type_Evenement;
	$tempEven->chargement($PDOdb, 'FRAIS DE SERVICE PEAGE PARKING','', '0000', 'faux', $tempType->rowid);
	$tempEven = new TRH_Type_Evenement;
	$tempEven->chargement($PDOdb, 'FRAIS DE SERVICE PEAGE', '','0000', 'faux', $tempType->rowid);
	$tempEven = new TRH_Type_Evenement;
	$tempEven->chargement($PDOdb, 'Carte à puce offline', '','0000', 'faux', $tempType->rowid);
	$tempEven = new TRH_Type_Evenement;
	$tempEven->chargement($PDOdb, 'Parking', '', '0000', 'faux', $tempType->rowid);
	$tempEven = new TRH_Type_Evenement;
	$tempEven->chargement($PDOdb, 'SECU 24/24', '','0000', 'faux', $tempType->rowid);
	
	


	
	
	

//BADGE AREA
	$tempType = new TRH_Ressource_type;
	$tempType->chargement($PDOdb, 'Badge Area', 'badgearea', 1);
	
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Numéro carte', 'numcarte', 'chaine',0, 0, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Immatriculation carte', 'immCarte', 'chaine',0, 1, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Compte support', 'comptesupport', 'chaine',1, 4, '', 1, $tempType->rowid);
	
	$tempEven = new TRH_Type_Evenement;
	$tempEven->chargement($PDOdb, 'Trajet', '', '0000', 'faux', $tempType->rowid);
	
//TELEPHONE
	$tempType = new TRH_Ressource_type;
	$tempType->chargement($PDOdb, 'Téléphone', 'telephone', 1);
	
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Marque', 'marquetel', 'chaine',0, 0, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Modèle', 'modletel', 'chaine',0, 1, '', 1, $tempType->rowid);
	
	
	
//CARTE SIM
	$tempType = new TRH_Ressource_type;
	$tempType->chargement($PDOdb, 'Carte SIM', 'carteSim', 1);
	
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Numéro', 'numeroTel', 'chaine',0, 0, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Coût minute interne', 'coutMinuteInt', 'float',0, 1, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Coût minute externe', 'coutMinuteExt', 'float',0, 2, '', 1, $tempType->rowid);
	
	
	
	$tempEven = new TRH_Type_Evenement;
	$tempEven->chargement($PDOdb, 'Facture Téléphonique', 'factTel', '0000', 'faux', $tempType->rowid);
	
	
	/*$tempField = new TRH_Ressource_field;
	$tempField->chargement($PDOdb,'Coût minutaire interne', 'coutMinuteInterne', 'chaine',0, 1, '', 1, $tempType->rowid);

	$tempField->chargement($PDOdb,'Coût minutaire externe', 'coutMinuteExterne', 'chaine',0, 2, '', 1, $tempType->rowid);
	/*

	$tempField->chargement($PDOdb,'Communications vers fixe métropolitain en Euros ht', 'commFixeMetrop', 'chaine',1, 3, '', 1, $tempType->rowid);

	$tempField->chargement($PDOdb,'Communications vers mobiles Orange en Euros ht', 'commMobileOrange', 'chaine',1, 4, '', 1, $tempType->rowid);

	$tempField->chargement($PDOdb,'Communications vers mobiles SFR en Euros ht', 'commMobileSFR', 'chaine',1, 5, '', 1, $tempType->rowid);

	$tempField->chargement($PDOdb,'Communications vers mobiles Bouygues en Euros ht', 'commMobileBouygues', 'chaine',1, 6, '', 1, $tempType->rowid);

	$tempField->chargement($PDOdb,'Communications vers l\'international en Euros ht', 'commToInternational', 'chaine',1, 7, '', 1, $tempType->rowid);

	$tempField->chargement($PDOdb,'Communications depuis l\'international en Euros ht', 'commFromInternational', 'chaine',1, 8, '', 1, $tempType->rowid);

	$tempField->chargement($PDOdb,'Communications internes en Euros ht', 'commInterne', 'chaine',1, 9, '', 1, $tempType->rowid);

	$tempField->chargement($PDOdb,'Communications VPNonsite en Euros ht', 'commVPN', 'chaine',1, 10, '', 1, $tempType->rowid);

	$tempField->chargement($PDOdb,'Connexions GPRS en Euros ht', 'connGPRS', 'chaine',1, 11, '', 1, $tempType->rowid);

	$tempField->chargement($PDOdb,'Connexions GPRS depuis l\'international en Euros ht', 'connGPRSFromInternational', 'chaine',1, 12, '', 1, $tempType->rowid);

	$tempField->chargement($PDOdb,'Connexions 3G en Euros ht', 'conn3G', 'chaine',1, 13, '', 1, $tempType->rowid);

	$tempField->chargement($PDOdb,'Connexions 3G depuis l’international en Euros ht', 'conn3GFromInternational', 'chaine',1, 14, '', 1, $tempType->rowid);

	$tempField->chargement($PDOdb,'SMS en Euros ht', 'sms', 'chaine',1, 15, '', 1, $tempType->rowid);

	$tempField->chargement($PDOdb,'SMS sans frontière en Euros ht', 'smsSansFrontiere', 'chaine',1, 16, '', 1, $tempType->rowid);

	$tempField->chargement($PDOdb,'Service SMS en Euros ht', 'serviceSMS', 'chaine',1, 17, '', 1, $tempType->rowid);

	$tempField->chargement($PDOdb,'MMS en Euros ht', 'mms', 'chaine',1, 18, '', 1, $tempType->rowid);

	$tempField->chargement($PDOdb,'MMS sans frontière en Euros ht', 'mmsSansFrontiere', 'chaine',1, 19, '', 1, $tempType->rowid);

	$tempField->chargement($PDOdb,'Connexions Wifi en Euros ht', 'ConnexionsWifi', 'chaine',1, 20, '', 1, $tempType->rowid);

	$tempField->chargement($PDOdb,'Connexions Wifi surtaxes en Euros ht', 'ConnexionsWifiSurtaxes', 'chaine',1, 21, '', 1, $tempType->rowid);

	$tempField->chargement($PDOdb,'Wifi depuis l\'international en Euros ht', 'WifiFromInternational', 'chaine',1, 22, '', 1, $tempType->rowid);

	$tempField->chargement($PDOdb,'Autres communications en Euros ht', 'autresCommunications', 'chaine',1, 23, '', 1, $tempType->rowid);

	$tempField->chargement($PDOdb,'Communications au-delà Optima en Euros ht', 'commOptima', 'chaine',1, 24, '', 1, $tempType->rowid);

	$tempField->chargement($PDOdb,'Dépassement facturation utilisateur en Euros ht', 'depassementFacturation', 'chaine',1, 25, '', 1, $tempType->rowid);

	$tempField->chargement($PDOdb,'Déduction forfait unique en Euros ht', 'deductionForfait', 'chaine',1, 26, '', 1, $tempType->rowid);

	$tempField->chargement($PDOdb,'Total communications en Euros ht', 'totalComm', 'chaine',1, 27, '', 1, $tempType->rowid);

	$tempField->chargement($PDOdb,'Communications semaine en Euros ht', 'commSemaine', 'chaine',1, 28, '', 1, $tempType->rowid);

	$tempField->chargement($PDOdb,'Communications week-end en Euros ht', 'commWeekEnd', 'chaine',1, 29, '', 1, $tempType->rowid);

	$tempField->chargement($PDOdb,'Libellé de la flotte', 'libFlotte', 'chaine',1, 30, '', 1, $tempType->rowid);
	 */
		

$PDOdb->close();




	