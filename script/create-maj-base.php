<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 * 
 */
 	define('INC_FROM_CRON_SCRIPT', true);
	
	require('../config.php');
	require('../class/ressource.class.php');
	require('../class/contrat.class.php');
	require('../class/evenement.class.php');
	require('../class/regle.class.php');
	require('../class/numeros_speciaux.class.php');

	$PDOdb=new TPDOdb;
	$PDOdb->db->debug=true;

	$o=new TRH_Ressource_type;
	$o->init_db_by_vars($PDOdb);
	
	$p=new TRH_Ressource_field;
	$p->init_db_by_vars($PDOdb);
	
	$p=new TRH_Ressource;
	$p->init_db_by_vars($PDOdb);
	
	$p=new TRH_Numero_special;
	$p->init_db_by_vars($PDOdb);
	
	$p=new TRH_Contrat;
	$p->init_db_by_vars($PDOdb);
	
	$p=new TRH_Contrat_Ressource;
	$p->init_db_by_vars($PDOdb);
	
	$p=new TRH_Evenement;
	$p->init_db_by_vars($PDOdb);
	//ALTER table customer modify Addr char(30)
	$sqlReq="ALTER TABLE ".MAIN_DB_PREFIX."rh_evenement MODIFY appels LONGTEXT";
	$PDOdb->Execute($sqlReq);
	
	$p=new TRH_Type_Evenement;
	$p->init_db_by_vars($PDOdb);
	
	$p=new TRH_Evenement_appel;
	$p->init_db_by_vars($PDOdb);
	
	$p=new TRH_Ressource_Regle;
	$p->init_db_by_vars($PDOdb);