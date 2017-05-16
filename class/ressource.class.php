<?php
	//require('./class/evenement.class.php');
	
class TRH_Ressource extends TObjetStd {
	function __construct() { /* declaration */
		parent::set_table(MAIN_DB_PREFIX.'rh_ressource');
		parent::add_champs('libelle','type=chaine;');
		parent::add_champs('numId','type=chaine;');
		parent::add_champs('bailvoit','type=chaine;');
		parent::add_champs('date_achat, date_vente, date_garantie','type=date;');
		
		//types énuméré
		parent::add_champs('statut','type=chaine;');
		
		//clé étrangere : groupes propriétaire et utilisatrice
		parent::add_champs('fk_utilisatrice','type=entier;index;');	//groupe : pointe sur llx_usergroup
		parent::add_champs('fk_entity_utilisatrice','type=entier;index;');	//fk_entity_utilisatrice : pointe sur llx_entity
		parent::add_champs('fk_proprietaire,entity','type=entier;index;');//fk_propriétaire pointe sur llx_entity
		parent::add_champs('fk_loueur','type=entier;index;');//fk_loueur pointe sur llx_societe
		
		//clé étrangère : type de la ressource
		parent::add_champs('fk_rh_ressource_type','type=entier;index;');
		//clé étrangère : ressource associé
		parent::add_champs('fk_rh_ressource','type=entier;index;');
		
		parent::_init_vars();
		parent::start();
		
		$this->TField=array();
		$this->ressourceType=new TRH_Ressource_type;

		$this->TType = array();
		$this->TBail = array('Location'=>'Location','Immo'=>'Immo', 'Crédit Bail'=>'Crédit Bail', 'Véh. Attente'=>'Véh. Attente');
		
		$this->TRessource = array('');
		$this->TEvenement = array();
		
		$this->TAgence = array('');
		$this->TFournisseur = array('');
		$this->TTVA = array();
		$this->TContratAssocies = array(); 	//tout les objets rh_contrat_ressource liés à la ressource
		$this->TContratExaustif = array(); 	//tout les objets contrats
		$this->TListeContrat = array(); 	//liste des id et libellés de tout les contrats
		$this->TEntity = array();
	}
	function getClone() {
		
		$clone = clone $this;
		
		$clone->start();
		
		$clone->numId = ''; 
		
		return $clone;
	}
	function load_liste_type_ressource(&$PDOdb){
		//chargement d'une liste de tout les types de ressources
		$temp = new TRH_Ressource_type;
		$Tab = TRequeteCore::get_id_from_what_you_want($PDOdb, MAIN_DB_PREFIX.'rh_ressource_type', array());
		$this->TType = array('');
		foreach($Tab as $k=>$id){
			$temp->load($PDOdb, $id);
			$this->TType[$temp->getId()] = $temp->libelle;
		}
		
	}
	
	function load_agence(&$PDOdb){
		global $conf;
		$this->TAgence = array('');
		$sqlReq="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup WHERE entity IN (0,".$conf->entity.")";
		$PDOdb->Execute($sqlReq);
		while($PDOdb->Get_line()) {
			$this->TAgence[$PDOdb->Get_field('rowid')] = $PDOdb->Get_field('nom');
			}
		
		$this->TFournisseur = array('');
		$sql="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."societe";
		$PDOdb->Execute($sql);
		while($PDOdb->Get_line()) {
			$this->TFournisseur[$PDOdb->Get_field('rowid')] = $PDOdb->Get_field('nom');
			}

		
	}
	
	function load_liste_entity(&$PDOdb){
		global $conf;
		
		$sql="SELECT rowid,label FROM ".MAIN_DB_PREFIX."entity WHERE 1";
		$PDOdb->Execute($sql);
		while($PDOdb->Get_line()) {
			$this->TEntity[$PDOdb->Get_field('rowid')] = $PDOdb->Get_field('label');
			}
		
		
	}
	
	function load_by_numId(&$PDOdb, $numId){
		$sqlReq="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_ressource WHERE numId='".$numId."'";
		$PDOdb->Execute($sqlReq);
		if ($PDOdb->Get_line()) {
			return $this->load($PDOdb, $PDOdb->Get_field('rowid'));
		}
		return false;
	}
	
	function load(&$PDOdb, $id, $annexe=true) {
		global $conf;
		$res = parent::load($PDOdb, $id);

		$this->load_ressource_type($PDOdb);
	
		if($annexe) {
			
			//chargement d'une liste de toutes les ressources (pour le combo "ressource associé")
			// AA à supprimer et mettre cette horreur ailleur
				$sqlReq="SELECT rowid,libelle, numId FROM ".MAIN_DB_PREFIX."rh_ressource WHERE rowid!=".$this->getId()." ORDER BY fk_rh_ressource_type, numId";
				$PDOdb->Execute($sqlReq);
				while($PDOdb->Get_line()) {
					$this->TRessource[$PDOdb->Get_field('rowid')] = $PDOdb->Get_field('numId').' '.$PDOdb->Get_field('libelle');
				}	
		}
			
		return $res;
	}
	
	/**
	 * charge des infos sur les évenements associés à cette ressource dans le tableau TEvenements[]
	 * Seulement les evenements du type spécifié.
	 */
	function load_evenement(&$PDOdb, $type=array('emprunt')){
		global $conf;
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."rh_evenement WHERE fk_rh_ressource=".$this->getId();
		$sql.=" AND ( 0 ";
		foreach ($type as $value) {
			 $sql.= "OR type LIKE '".$value."' ";
		}
		$sql .= ")  ORDER BY date_fin";
		$PDOdb->Execute($sql);
		$Tab=array();
		while($PDOdb->Get_line()){
			$Tab[]=$PDOdb->Get_field('rowid');
		}
		$this->TEvenement = array();
		foreach($Tab as $k=>$id) {
			$this->TEvenement[$k] = new TRH_Evenement ;
			$this->TEvenement[$k]->load($PDOdb, $id);
		}
		
	}
	
	
	/**
	 * charge tout les contrats associé à cette ressource.
	 */
	function load_contrat(&$PDOdb){
		global $conf;
		$this->TContratExaustif = array();
		foreach($this->TListeContrat as $k=>$id) {
			$this->TContratExaustif[$k] = new TRH_Contrat ;
			$this->TContratExaustif[$k]->load($PDOdb, $k);
		}
		
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."rh_contrat_ressource WHERE fk_rh_ressource=".$this->getId()."
		AND entity IN (0,".$conf->entity.")";
		$PDOdb->Execute($sql);
		$Tab=array();
		while($PDOdb->Get_line()){
			$Tab[]=$PDOdb->Get_field('rowid');
		}
		$this->TContratAssocies = array();
		foreach($Tab as $k=>$id) {
			$this->TContratAssocies[$id] = new TRH_Contrat_Ressource;
			$this->TContratAssocies[$id]->load($PDOdb, $id);
		}
		// AA c'est un contrat ça ? (outre le fait que je ne comprends pas toutes ces notions de contrats)
		$this->TTVA = array();
		$sqlReq="SELECT rowid, taux FROM ".MAIN_DB_PREFIX."c_tva WHERE fk_pays=".$conf->global->MAIN_INFO_SOCIETE_COUNTRY[0];
		$PDOdb->Execute($sqlReq);
		while($PDOdb->Get_line()) {
			$this->TTVA[$PDOdb->Get_field('rowid')] = $PDOdb->Get_field('taux');
			}
		
		$this->TListeContrat = array(); 	//liste des id et libellés de tout les contrats
		$sqlReq="SELECT rowid, libelle FROM ".MAIN_DB_PREFIX."rh_contrat WHERE fk_rh_ressource_type =".$this->fk_rh_ressource_type;
		$PDOdb->Execute($sqlReq);
		while($PDOdb->Get_line()) {
			$this->TListeContrat[$PDOdb->Get_field('rowid')] = $PDOdb->Get_field('libelle');
			}
	}
	
	/**
	 * Retourne une liste de type ATM des contrats associés à la ressource
	 */
	function liste_contrat(&$PDOdb){
		global $user, $conf;
		$r = new TListviewTBS('lol');
		$sql="SELECT DISTINCT a.rowid as 'ID',  c.rowid as 'IDContrat' , c.libelle as 'Libellé',
			DATE(c.date_debut) as 'Date début', DATE(c.date_fin) as 'Date fin', a.commentaire as 'Commentaire'
			FROM ".MAIN_DB_PREFIX."rh_contrat_ressource as a
			LEFT JOIN ".MAIN_DB_PREFIX."rh_contrat as c ON (a.fk_rh_contrat = c.rowid)
			LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (a.fk_rh_ressource = r.rowid)
			WHERE a.fk_rh_ressource=".$this->getId();
		$TOrder = array('Date début'=>'ASC');
		if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
		if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
		
		$res = $r->render($PDOdb, $sql, array(
			'limit'=>array(
				'page'=>$page
				,'nbLine'=>'30'
			)
			,'link'=>array(
				'ID'=>'<a href="?id='.$this->getId().'&idAssoc=@ID@">@val@</a>'
				,'Libellé'=>'<a href="contrat.php?id=@IDContrat@">@val@</a>'
			)
			,'translate'=>array()
			,'hide'=>array('DateCre', 'IDContrat')
			,'type'=>array(
				'Date début'=>'date'
				,'Date fin'=>'date'
				)
			,'liste'=>array(
				'titre'=>'Liste des contrats associés'
				,'image'=>img_picto('','title.png', '', 0)
				,'picto_precedent'=>img_picto('','previous.png', '', 0)
				,'picto_suivant'=>img_picto('','next.png', '', 0)
				,'noheader'=> (int)isset($_REQUEST['socid'])
				,'messageNothing'=>"Il n'y a aucun contrat à afficher"
				,'order_down'=>img_picto('','1downarrow.png', '', 0)
				,'order_up'=>img_picto('','1uparrow.png', '', 0)
			)
			,'orderBy'=>$TOrder
		));
		return $res;
	}

	/**
	 * La fonction renvoie le rowid de l'user qui a la ressource à la date T, 0 sinon.
	 */
	function isEmpruntee(&$PDOdb, $jour){ // AA bizarrement, oui j'ai toujours aimé le Franglais
		global $conf;
		
		// AA Par contre je la function peut se résumer en une seule requete
		
		$sql = "SELECT u.rowid, e.date_debut as 'debut', e.date_fin as 'fin'
				FROM ".MAIN_DB_PREFIX."user as u
				LEFT JOIN ".MAIN_DB_PREFIX."rh_evenement as e ON (e.fk_user = u.rowid)
				LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (e.fk_rh_ressource = r.rowid)
				WHERE r.rowid =".$this->getId()."
				AND e.type='emprunt'";
		$PDOdb->Execute($sql);
		$Tab=array();
		while($PDOdb->Get_line()){
			if ( date("Y-m-d",strtotime($PDOdb->Get_field('debut'))) <= $jour  
				&& date("Y-m-d",strtotime($PDOdb->Get_field('fin'))) >= $jour ){
				$Tab[]=$PDOdb->Get_field('rowid');	
			}
			
		}
		if (! empty($Tab)){
			return $Tab[0];}
		else {
			return 0;}
	}
	
	function nouvelEmprunt($PDOdb, $TValue, $forceEmprunt = false)
	{
		$fk_emprunt = $this->nouvelEmpruntSeChevauche($PDOdb, $this->getId(), $TValue, true);
		
		//Eventuellement si sa ce chevauche, voir pour terminer l'emprunt et créer le suivant ?
		if ($forceEmprunt || empty($fk_emprunt))
		{
			dol_include_once('/ressource/class/evenement.class.php');
			
			if (!empty($fk_emprunt))
			{
				
				// on termine l'emprunt précédent pour commencer le nouveau
				$old_emprunt = new TRH_Evenement;
				$old_emprunt->load($PDOdb, $fk_emprunt);
				$old_emprunt->date_fin = date('Y-m-d', strtotime($date_debut.' -1 day'));
				$old_emprunt->save($PDOdb);
			}
		
			$emprunt = new TRH_Evenement;
			$emprunt->set_values($TValue);
			
			$emprunt->save($PDOdb);
			
			return $emprunt->getId();
		}
		
		return false;
	}
	
	function addContrat(&$PDOdb, $TValue)
	{
		$contrat_ressource = new TRH_Contrat_Ressource;
		$contrat_ressource->set_values($TValue);
		$contrat_ressource->save($PDOdb);
	}
	
	/**
	 * retourne le timestamp d'une chaine au format jj/mm/aaaa
	 * Utile pour la comparaison.
	 */
	function strToTimestamp($chaine){
		$a = strptime ($chaine, "%d/%m/%Y"); // AA snif je viens d'apprendre une fonction et c'est pas tout les jours ;)
		$timestamp = mktime(0,0,0,substr($chaine, 3,2),substr($chaine,0,2), substr($chaine, 6,4));
		//$timestamp = mktime(0, 0, 0, $a['tm_mon']+1, $a['tm_mday'], $a['tm_year']+1900);
		return $timestamp;
	}
	
	
	/**
	 * La fonction renvoie vrai si les nouvelles date proposé pour un emprunt se chevauchent avec d'autres.
	 */
	function nouvelEmpruntSeChevauche(&$PDOdb,  $idRessource, $newEmprunt, $returnId=false){
		global $conf;
		$sqlReq="SELECT rowid, date_debut,date_fin FROM ".MAIN_DB_PREFIX."rh_evenement WHERE fk_rh_ressource=".$idRessource."
		AND type='emprunt' AND rowid != ".$newEmprunt['idEven']; 
		$PDOdb->Execute($sqlReq);
		while($PDOdb->Get_line()) {
			if ($this->dateSeChevauchent($this->strToTimestamp($newEmprunt['date_debut'])
										,$this->strToTimestamp($newEmprunt['date_fin'])
										,$this->strToTimestamp(date("d/m/Y",strtotime($PDOdb->Get_field('date_debut'))))
										,$this->strToTimestamp(date("d/m/Y",strtotime($PDOdb->Get_field('date_fin'))))))
			{
				return $returnId ? $PDOdb->Get_field('rowid') : true;
			}
		}
		return false;
	}
	
	/**
	 * les dates demandés sont au format timeStamp
	 * @return true si chevauchement; false sinon.
	 */
	function dateSeChevauchent($d1d, $d1f, $d2d, $d2f){
		if (  ( ($d1d>=$d2d) && ($d1d<=$d2f) ) || ( ($d1f>=$d2d)  && ($d1f<=$d2f) )  ) 
			{return true;}
		return false;	
	}

	function load_ressource_type(&$PDOdb) {
		//on prend le type de ressource associé
		$Tab = TRequeteCore::get_id_from_what_you_want($PDOdb, MAIN_DB_PREFIX.'rh_ressource_type', array('rowid'=>$this->fk_rh_ressource_type));
		$this->ressourceType->load($PDOdb, $Tab[0]);
		$this->fk_rh_ressource_type = $this->ressourceType->getId();
		
		//on charge les champs associés au type.
		$this->init_variables($PDOdb);
		
	}
	
	function init_variables(&$PDOdb) {
		foreach($this->ressourceType->TField as $field) {
			$this->add_champs($field->code, array('type'=>'string'));
		}
		$this->init_db_by_vars($PDOdb);
		parent::load($PDOdb, $this->getId());
	}
	
	function save(&$db) {
		global $conf;
		$this->entity = $conf->entity;
		//$this->setStatut($db, date("Y-m-d"));
		if($this->bailvoit == 'Immo') //TODO remove custom customer
		{
			$this->date_vente = null;
		}
		//on transforme les champs sensés être entier en int
		foreach($this->ressourceType->TField as $k=>$field) {
			//var_dump($field->code,$field->type);
			if ($field->type=='entier'){
				$this->{$field->code} = (int) ($this->{$field->code});
			}
		}
		
		parent::save($db);
	}
	
	function delete(&$PDOdb){
		global $conf;
		
		//avant de supprimer le contrat, on supprime les liaisons contrat-ressource associés.
		$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_contrat_ressource WHERE fk_rh_ressource=".$this->getId();
		$Tab = array();
		$temp = new TRH_Contrat_Ressource;
		$PDOdb->Execute($sql);
		while($PDOdb->Get_line()) {
			$Tab[] = $PDOdb->Get_field('rowid');
			}
		foreach ($Tab as $key => $id) {
			$temp->load($PDOdb, $id);
			$temp->delete($PDOdb);
		}
		
		//on supprime aussi les évenements associés
		$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_evenement WHERE fk_rh_ressource=".$this->getId();
		$Tab = array();
		$temp = new TRH_Evenement;
		$PDOdb->Execute($sql);
		while($PDOdb->Get_line()) {
			$Tab[] = $PDOdb->Get_field('rowid');
			}
		foreach ($Tab as $key => $id) {
			$temp->load($PDOdb, $id);
			$temp->delete($PDOdb);
		}
		
		
		parent::delete($PDOdb);
		
		
	}
}



	

class TRH_Ressource_type extends TObjetStd {
	function __construct() { /* declaration */
		parent::set_table(MAIN_DB_PREFIX.'rh_ressource_type');
		parent::add_champs('libelle,code','type=chaine;');
		parent::add_champs('entity','type=entier;index;');
		parent::add_champs('supprimable','type=entier;');
				
		parent::_init_vars();
		parent::start();
		$this->TField=array();
		$this->TType=array('chaine'=>'Texte','entier'=>'Entier','float'=>'Float',"liste"=>'Liste','date'=>'Date', "checkbox"=>'Case à cocher');
	}
	
	
	function load_by_code(&$PDOdb, $code){
		$sqlReq="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_ressource_type WHERE code='".$code."'";
		$PDOdb->Execute($sqlReq);
		
		if ($PDOdb->Get_line()) {
			$this->load($PDOdb, $PDOdb->Get_field('rowid'));
			return true;
		}
		return false;
	}
	
	/**
	 * Attribut les champs directement, pour créer les types par défauts par exemple. 
	 */
	function chargement(&$db, $libelle, $code, $supprimable){
		$this->load_by_code($db, $code);
		$this->libelle = $libelle;
		$this->code = $code;
		$this->supprimable = $supprimable;
		$this->save($db);
	}
	
	function load(&$PDOdb, $id,$loadChild=true) {
		parent::load($PDOdb, $id,$loadChild);
		$this->load_field($PDOdb);
	}
	
	/**
	 * Renvoie true si ce type est utilisé par une des ressources.
	 */
	function isUsedByRessource(&$PDOdb){
		$Tab = TRequeteCore::get_id_from_what_you_want($PDOdb, MAIN_DB_PREFIX.'rh_ressource', array('fk_rh_ressource_type'=>$this->getId()));
		if (count($Tab)>0) return true;
		return false;

	}
	
	function load_field(&$PDOdb) {
		global $conf;
		$sqlReq="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_ressource_field WHERE fk_rh_ressource_type=".$this->getId()." ORDER BY ordre ASC;";
		$PDOdb->Execute($sqlReq);
		
		$Tab = array();
		while($PDOdb->Get_line()) {
			$Tab[]= $PDOdb->Get_field('rowid');
		}
		
		$this->TField=array();
		foreach($Tab as $k=>$id) {
			$this->TField[$k]=new TRH_Ressource_field;
			$this->TField[$k]->load($PDOdb, $id);
		}
	}
	
	function addField(&$PDOdb, $TNField) {
		$k=count($this->TField);
		$this->TField[$k]=new TRH_Ressource_field;
		$this->TField[$k]->set_values($TNField);
		
		$p=new TRH_Ressource;				
		$p->add_champs($TNField['code'] ,'type=chaine' );
		$p->init_db_by_vars($PDOdb);
					
		return $k;
	}
	
	function delField(&$PDOdb, $id){
		$toDel = new TRH_Ressource_field;
		$toDel->load($PDOdb,$id);
		return $toDel->delete($PDOdb);
	}
	
	function delete(&$PDOdb) {
		global $conf;
		if ($this->supprimable){
			//on supprime les champs associés à ce type
			$sqlReq="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_ressource_field WHERE fk_rh_ressource_type=".$this->getId();
			$PDOdb->Execute($sqlReq);
			$Tab = array();
			while($PDOdb->Get_line()) {
				$Tab[]= $PDOdb->Get_field('rowid');
			}
			$temp = new TRH_Ressource_field;
			foreach ($Tab as $k => $id) {
				$temp->load($PDOdb, $id);
				$temp->delete($PDOdb);
			}
			//puis on supprime le type
			parent::delete($PDOdb);
			return true;
		}
		else {return false;}
		
	}
	function save(&$db) {
		global $conf;
		
		$this->entity = $conf->entity;
		$this->code = TRH_Ressource_type::code_format(empty($this->code) ? $this->libelle : $this->code);
		
		$this->code = TRH_Ressource_type::code_format(empty($this->code) ? $this->libelle : $this->code);
		
		parent::save($db);
		
		foreach($this->TField as $field) {
			$field->fk_rh_ressource_type = $this->getId();
			$field->save($db);
		}
		
	}	
	
	static function code_format($s){
		$r=""; $s = strtolower($s);
		$nb=strlen($s);
		for($i = 0; $i < $nb; $i++){
			if(ctype_alnum($s[$i])){
				$r.=$s[$i];			
			}
		} // for
		return $r;
	}
		
}

class TRH_Ressource_field extends TObjetStd {
	function __construct() { /* declaration */
		parent::set_table(MAIN_DB_PREFIX.'rh_ressource_field');
		parent::add_champs('code,libelle','type=chaine;');
		parent::add_champs('type','type=chaine;');
		parent::add_champs('obligatoire','type=entier;');
		parent::add_champs('ordre','type=entier;');
		parent::add_champs('options','type=chaine;');
		parent::add_champs('supprimable','type=entier;');
		parent::add_champs('inliste,inlibelle','type=chaine;'); //varchar booléen : oui/non si le champs sera dans la liste de Ressource.
		parent::add_champs('fk_rh_ressource_type,entity','type=entier;index;');
		
		$this->TListe = array();
		parent::_init_vars();
		parent::start();
		
	}
	
	function load_by_code(&$db, $code){
		$sqlReq="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_ressource_field WHERE code='".$code."'";
		$db->Execute($sqlReq);
		
		if ($db->Get_line()) {
			$this->load($db, $db->Get_field('rowid'));
			return true;
		}
		return false;
	}
	
	
	function chargement(&$db, $libelle, $code, $type, $obligatoire, $ordre, $options, $supprimable, $fk_rh_ressource_type, $inliste = "non", $inlibelle = "non"){
		$this->load_by_code($db, $code);	
		$this->libelle = $libelle;
		$this->code = $code;
		$this->type = $type;
		$this->obligatoire = $obligatoire;
		$this->ordre = $ordre;
		$this->options = $options;
		$this->supprimable = $supprimable;
		$this->inliste = $inliste;
		$this->inlibelle = $inlibelle;
		$this->fk_rh_ressource_type = $fk_rh_ressource_type;
		
		
		$this->save($db);
	}
	
	function load(&$PDOdb, $id, $loadChild = true){
		parent::load($PDOdb, $id, $loadChild);
		$this->TListe = array();
		foreach (explode(";",$this->options) as $key => $value) {
			$this->TListe[$value] = $value;
		}
	}
	
	function save(&$db) {
		global $conf;
		
		$this->code = TRH_Ressource_type::code_format(empty($this->code) ? $this->libelle : $this->code);
		
		$this->entity = $conf->entity;
		if (empty($this->supprimable)){$this->supprimable = 0;}
		parent::save($db);
	}

	function delete(&$PDOdb) {
		global $conf;
		
		//on supprime le champs que si il est par défault.
		if (! $this->supprimable){
			parent::delete($PDOdb);	
			return true;
		}
		else {return false;}
		
		
	}

}
	
