<?php
class TRH_Ressource_Regle  extends TObjetStd {
	
	function __construct(){
		parent::set_table(MAIN_DB_PREFIX.'rh_ressource_regle');
		
		parent::add_champs('choixApplication','type=chaine;');
		parent::add_champs('choixLimite','type=chaine;');
		
		//valeurs
		parent::add_champs('duree, dureeInt, dureeExt','type=entier;');
		parent::add_champs('natureRefac','type=chaine;');
		parent::add_champs('montantRefac','type=float;');
		parent::add_champs('dataIllimite,dataIphone,mailforfait,smsIllimite,data15Mo,carteJumelle,numeroExclus','type=chaine;'); //booléen

		
		parent::add_champs('fk_user, fk_usergroup','type=entier;');
		parent::add_champs('fk_rh_ressource_type, entity','type=entier;index;');
		
		$this->choixApplication = 'all';
		$this->choixLimite = 'extint';
		$this->TUser = array();
		$this->TGroup  = array();
		$this->TChoixLimite = array(
			'gen'=>'Générale'
			,'extint'=>'Interne/Externe'
		);
		$this->TChoixApplication = array(
			'all'=>'Tous'
			,'group'=>'Groupe'
			,'user'=>'Utilisateur'
		);
		
		parent::_init_vars();
		parent::start();
	}
	
	function load_liste(&$PDOdb){
		global $conf;
		
		//LISTE DE GROUPES
		$this->TGroup  = array();
		$sqlReq="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup WHERE entity IN (0,".$conf->entity.")";
		$PDOdb->Execute($sqlReq);
		while($PDOdb->Get_line()) {
			$this->TGroup[$PDOdb->Get_field('rowid')] = htmlentities($PDOdb->Get_field('nom'), ENT_COMPAT , 'ISO8859-1');
			}
		
		//LISTE DE USERS
		$this->TUser = array();
		$sqlReq="SELECT rowid, firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE entity IN (0,".$conf->entity.") ORDER BY lastname, firstname";
		$PDOdb->Execute($sqlReq);
		while($PDOdb->Get_line()) {
			$this->TUser[$PDOdb->Get_field('rowid')] = $PDOdb->Get_field('firstname')." ".$PDOdb->Get_field('lastname');
			}
		
	}
	
	function load_by_fk_user(&$PDOdb, $fk_user){
		$sqlReq="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_ressource_regle WHERE fk_user='".$fk_user."'";
		$PDOdb->Execute($sqlReq);
		if ($PDOdb->Get_line()) {
			return $this->load($PDOdb, $PDOdb->Get_field('rowid'));
		}
		return false;
	}
		
		
	function load(&$PDOdb, $id) {
		//global $conf;
		parent::load($PDOdb, $id);
	}
	
	
	
	function save(&$PDOdb) {
		global $conf;
		$this->entity = $conf->entity;
		
		switch ($this->choixApplication){
			case 'all':$this->fk_user = 0;$this->fk_usergroup=0;break;
			case 'user':
				$this->load_by_fk_user($PDOdb, $this->fk_user);
				$this->fk_usergroup = NULL;
				break;
			case 'group':$this->fk_user = NULL;break;
		}
		
		switch ($this->choixLimite){
			case 'gen':
				$this->dureeInt = 0;
				$this->dureeExt = 0;
				break;
			case 'extint':
				$this->duree = 0;
				break;
		}
		
		parent::save($PDOdb);
	}
	
	
}	



