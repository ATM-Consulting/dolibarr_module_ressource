<?php
	require('config.php');
	require('./class/ressource.class.php');
	require('./class/contrat.class.php');
	require('./class/evenement.class.php');
	require('./lib/ressource.lib.php');
	$langs->load('ressource@ressource');

	//if (!$user->rights->financement->affaire->read)	{ accessforbidden(); }
	$PDOdb=new TPDOdb;
	$emprunt=new TRH_Evenement;
	$ressource=new TRH_ressource;
	$contrat=new TRH_Contrat;
	$contrat_ressource=new TRH_Contrat_Ressource;

	$mesg = '';
	$error=false;

	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				$ressource->set_values($_REQUEST);
				_fiche($PDOdb, $emprunt, $ressource, $contrat,'new');
				break;
			case 'clone':
				$ressource->load($PDOdb, $_REQUEST['id']);
				$ressource->load_ressource_type($PDOdb);

				$clone = $ressource->getClone();
				//print_r($clone);exit;
				_fiche($PDOdb, $emprunt, $clone, $contrat,'edit');

				break;
			case 'edit'	:
				//$PDOdb->db->debug=true;
				//print_r($_REQUEST);

				//$ressource->set_values($_REQUEST['fk_rh_ressource_type']);
				$ressource->fk_rh_ressource_type = $_REQUEST['fk_rh_ressource_type'];
				$ressource->load_ressource_type($PDOdb);
				$ressource->load($PDOdb, $_REQUEST['id']);
				_fiche($PDOdb, $emprunt, $ressource, $contrat,'edit');
				break;

			case 'save':
			//	$PDOdb->db->debug=true;
				$ressource->fk_rh_ressource_type = $_REQUEST['fk_rh_ressource_type'];
				$ressource->load($PDOdb, $_REQUEST['id']);
				//on vérifie que le libellé est renseigné
				if  ( empty($_REQUEST['numId']) ){
					$mesg .= '<div class="error">Le numéro Id doit être renseigné.</div>';
				}

				if  ( empty($_REQUEST['libelle']) ){
					$mesg .= '<div class="error">Le libellé doit être renseigné.</div>';
				}


				//on vérifie que les champs obligatoires sont renseignés
				foreach($ressource->ressourceType->TField as $k=>$field) {
					if (! $field->obligatoire){
						if  ( empty($_REQUEST[$field->code]) ){
							$mesg .= '<div class="error">Le champs '.$field->libelle.' doit être renseigné.</div>';
						}
					}
				}

				//ensuite on vérifie ici que les champs (OBLIGATOIRE OU REMPLIS) sont bien du type attendu
				if ($mesg == ''){
					foreach($ressource->ressourceType->TField as $k=>$field) {
						if (! $field->obligatoire || ! empty($_REQUEST[$field->code])){
							switch ($field->type){
								case 'float':
								case 'entier':
									//la conversion en entier se fera lors de la sauvegarde dans l'objet.
									if (! is_numeric($_REQUEST[$field->code]) ){
										$mesg .= '<div class="error">Le champ '.$field->libelle.' doit être un nombre.</div>';
										}
									break;
								default :
									break;
							}
						}
					}
				}

				$ressource->set_values($_REQUEST);
				$ressource->save($PDOdb);

				////////
				if($_REQUEST["fieldChoice"]=="O"){
					//print_r($_REQUEST['evenement']);
					if ($ressource->nouvelEmpruntSeChevauche($PDOdb, $_REQUEST['id'], $_REQUEST['evenement']) ){
						$mesg = '<div class="error">Impossible d\'attributer la ressource. Les dates choisies se superposent avec d\'autres attributions.</div>';
					}
					else {
						$emprunt->load($PDOdb, $_REQUEST['idEven']);
						$emprunt->set_values($_REQUEST['evenement']);
						$emprunt->fk_rh_ressource = $ressource->getId();
						$emprunt->save($PDOdb);
					}


				}
				////////

				////////
				if($_REQUEST["fieldChoiceContrat"]=="O"){
					$contrat->set_values($_REQUEST['contrat']);
					$contrat->fk_tier_fournisseur=$_REQUEST['fk_tier_fournisseur'];
					$contrat->fk_rh_ressource_type=$_REQUEST['fk_rh_ressource_type'];
					$contrat->save($PDOdb);
					$contrat_ressource->fk_rh_ressource = $ressource->getId();
					$contrat_ressource->fk_rh_contrat = $contrat->getId();
					$contrat_ressource->save($PDOdb);
				}
				////////

				if ($mesg==''){
					$mesg = '<div class="ok">Modifications effectuées</div>';
					$mode = 'view';
					if(isset($_REQUEST['validerType']) ) {
						$mode = 'edit';
					}
				}
				else {$mode = 'edit';}

				$ressource->load($PDOdb, $_REQUEST['id']);
				_fiche($PDOdb, $emprunt, $ressource, $contrat, $mode);
				break;

			case 'view':
				//$PDOdb->db->debug=true;
				$ressource->load($PDOdb, $_REQUEST['id']);
				_fiche($PDOdb, $emprunt, $ressource, $contrat, 'view');
				break;


			case 'delete':
				$ressource->load($PDOdb, $_REQUEST['id']);
				//$PDOdb->db->debug=true;
				$ressource->delete($PDOdb);

				?>
				<script language="javascript">
					document.location.href="?delete_ok=1";
				</script>
				<?php


				break;
		}
	}
	elseif(isset($_REQUEST['id'])) {
		$ressource->load($PDOdb, $_REQUEST['id']);
		_fiche($PDOdb, $emprunt, $ressource, $contrat, 'view');
	}
	else {
		/*
		 * Liste
		 */
		 //$PDOdb->db->debug=true;
		 _liste($PDOdb, $ressource);
	}


	$PDOdb->close();
	llxFooter();


function _liste(&$PDOdb, &$ressource) {
	global $langs,$conf,$db,$user;
	llxHeader('','Liste des ressources');
	print dol_get_fiche_head(array()  , '', 'Liste ressources');

	//récupération des champs spéciaux à afficher.
	$sqlReq="SELECT code, libelle, type, options FROM ".MAIN_DB_PREFIX."rh_ressource_field WHERE inliste='oui' ";
	$PDOdb->Execute($sqlReq);
	$TSpeciaux = array();

	$TSearch=array();
	while($PDOdb->Get_line()) {
		$TSpeciaux[$PDOdb->Get_field('code')]= $PDOdb->Get_field('libelle');
		if ($PDOdb->Get_field('type')=='liste'){
			$TSearch[$PDOdb->Get_field('code')] = array_combine(explode(';', $PDOdb->Get_field('options')), explode(';', $PDOdb->Get_field('options')));
		}
		else {
			$TSearch[$PDOdb->Get_field('code')] = true;}
	}

	$listname = 'list_'.$ressource->get_table();
	$render = new TListviewTBS($listname);

	$sql_select="SELECT r.rowid as 'ID', r.date_cre as 'DateCre', r.libelle, r.fk_rh_ressource_type,
		r.numId , u.rowid as 'Statut', firstname, lastname ";

	if(!empty($conf->valideur->enabled)) {
		$sql_select.=" ,GROUP_CONCAT(CONCAT(ua.code,'(',ua.pourcentage,'%)') SEPARATOR ', ' ) as 'Codes analytiques' ";
	}

	if(!empty($_REQUEST['TListTBS']['list_llx_rh_ressource']['search'])) {
		$sql_select.=", CONCAT(DATE_FORMAT(e.date_debut,'%d/%m/%Y') ,' ', DATE_FORMAT(e.date_fin,'%d/%m/%Y')) as 'dates'";
	}

	//rajout des champs spéciaux parametré par les types de ressources
	foreach ($TSpeciaux as $key=>$value) {
		$sql_select .= ','.$key.' ';
	}
	if($user->rights->ressource->ressource->createRessource){
		$sql_select.=", '' as 'Supprimer'";
	}

	$now = date('Y-m-d H:i:s');
	$sql_left_1 = " FROM ".MAIN_DB_PREFIX."rh_ressource as r
			LEFT JOIN (SELECT fk_rh_ressource, date_debut,date_fin,fk_user FROM ".MAIN_DB_PREFIX."rh_evenement WHERE type='emprunt' AND date_fin>='".$now."' AND date_debut<='".$now."') as e ON (e.fk_rh_ressource=r.rowid)
	 LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (e.fk_user = u.rowid ) ";

	$sql_left_2 = " FROM ".MAIN_DB_PREFIX."rh_ressource as r
			LEFT JOIN (SELECT fk_rh_ressource, date_debut,date_fin,fk_user FROM ".MAIN_DB_PREFIX."rh_evenement WHERE type='emprunt' AND date_fin>='".$now."' AND date_debut<='".$now."') as e ON (e.fk_rh_ressource=r.fk_rh_ressource)
	 LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (e.fk_user = u.rowid ) ";


	if(!empty($conf->valideur->enabled)) {
	    $sql_left_1.= " LEFT JOIN ".MAIN_DB_PREFIX."rh_analytique_user as ua ON (e.fk_user = ua.fk_user) ";
		$sql_left_2.= " LEFT JOIN ".MAIN_DB_PREFIX."rh_analytique_user as ua ON (e.fk_user = ua.fk_user) ";
	}

	$sql_where = " WHERE 1 ";


	if(!$user->rights->ressource->ressource->viewRessource){
		$sql_where.=" AND e.fk_user=".$user->id;
	}


	$TListTBS = GETPOST('TListTBS');
	if (empty($TListTBS)) $TListTBS = array();
	if (!empty($TListTBS['list_llx_rh_ressource']['search']['libelle'])) $sql_where.= ' AND r.libelle LIKE "%'.$db->escape($TListTBS['list_llx_rh_ressource']['search']['libelle']).'%"';
	if (!empty($TListTBS['list_llx_rh_ressource']['search']['fk_rh_ressource_type'])) $sql_where.= ' AND r.fk_rh_ressource_type = '.$db->escape($TListTBS['list_llx_rh_ressource']['search']['fk_rh_ressource_type']);
	if (!empty($TListTBS['list_llx_rh_ressource']['search']['numId'])) $sql_where.= ' AND r.numId LIKE "%'.$db->escape($TListTBS['list_llx_rh_ressource']['search']['numId']).'%"';
	if (!empty($TListTBS['list_llx_rh_ressource']['search']['firstname'])) $sql_where.= ' AND u.firstname LIKE "%'.$db->escape($TListTBS['list_llx_rh_ressource']['search']['firstname']).'%"';
	if (!empty($TListTBS['list_llx_rh_ressource']['search']['lastname'])) $sql_where.= ' AND u.lastname LIKE "%'.$db->escape($TListTBS['list_llx_rh_ressource']['search']['lastname']).'%"';
	foreach ($TSpeciaux as $key=>$value) {
		if (!empty($TListTBS['list_llx_rh_ressource']['search'][$key]))
        {
            if (is_array($TSearch[$key])) $sql_where.= ' AND '.$key.' = "'.$db->escape($TListTBS['list_llx_rh_ressource']['search'][$key]).'"';
            else $sql_where.= ' AND '.$key.' LIKE "%'.$db->escape($TListTBS['list_llx_rh_ressource']['search'][$key]).'%"';
		}
	}


	$sql_group = " GROUP BY r.rowid ";
	$ressource->load_liste_type_ressource($PDOdb);

//	$TOrder = array('ID'=>'ASC');
//	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
//	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
	$TOrder = array();
	if (!empty($TListTBS['list_llx_rh_ressource']['orderBy']['ID'])) $TOrder[] = 'ID '.$TListTBS['list_llx_rh_ressource']['orderBy']['ID'];
	if (!empty($TListTBS['list_llx_rh_ressource']['orderBy']['libelle'])) $TOrder[] = 'r.libelle '.$TListTBS['list_llx_rh_ressource']['orderBy']['libelle'];
	if (!empty($TListTBS['list_llx_rh_ressource']['orderBy']['fk_rh_ressource_type'])) $TOrder[] = 'r.fk_rh_ressource_type '.$TListTBS['list_llx_rh_ressource']['orderBy']['fk_rh_ressource_type'];
	if (!empty($TListTBS['list_llx_rh_ressource']['orderBy']['numId'])) $TOrder[] = 'r.numId '.$TListTBS['list_llx_rh_ressource']['orderBy']['numId'];
	if (!empty($TListTBS['list_llx_rh_ressource']['orderBy']['lastname'])) $TOrder[] = 'u.lastname '.$TListTBS['list_llx_rh_ressource']['orderBy']['lastname'];
	if (!empty($TListTBS['list_llx_rh_ressource']['orderBy']['firstname'])) $TOrder[] = 'u.lastname '.$TListTBS['list_llx_rh_ressource']['orderBy']['firstname'];

	$sql_order_by = '';
	if (!empty($TOrder))
	{
		$sql_order_by.= ' ORDER BY '.implode(', ', $TOrder);
	}

	$TRessource = array();
	$PDOdb->Execute($sql_select.$sql_left_1.$sql_where.$sql_group.$sql_order_by);
	while ($line = $PDOdb->Get_line(PDO::FETCH_ASSOC))
    {
		$TRessource[$line['ID']] = $line;
    }
	$PDOdb->Execute($sql_select.$sql_left_2.$sql_where.$sql_order_by.$sql_group);
	while ($line = $PDOdb->Get_line(PDO::FETCH_ASSOC))
    {
        if (!empty($TRessource[$line['ID']])) {
            array_walk($TRessource[$line['ID']], function(&$item, $key) use (&$line) {
                if (empty($item)) $item = $line[$key];
			});
		}
        else $TRessource[$line['ID']] = $line;
    }

	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
	$formCore=new TFormCore($_SERVER['PHP_SELF'],'formtranslateList','GET');
	$nbLine = ! empty($user->conf->MAIN_SIZE_LISTE_LIMIT) ? $user->conf->MAIN_SIZE_LISTE_LIMIT : $conf->global->MAIN_SIZE_LISTE_LIMIT;

	print $render->renderArray($PDOdb, $TRessource, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>$nbLine
		)
		,'link'=>array(
			'libelle'=>'<a href="?id=@ID@&action=view">@val@</a>'
			,'Supprimer'=>"<a style=\"cursor:pointer;\"  onclick=\"if (window.confirm('Voulez vous supprimer l\'élément ?')){document.location.href='?id=@ID@&action=delete'};\"><img src=\"./img/delete.png\"></a>"
		)
		,'eval'=>array(
			'Statut'=>'getStatut("@val@")'
			,'lastname'=>'toUtf8("@val@")'
			,'firstname'=>'toUtf8("@val@")'
		)
		,'translate'=>array(
			'fk_rh_ressource_type'=>$ressource->TType
			)
		,'hide'=>array('DateCre')
		,'type'=>array('libelle'=>'string')
		,'liste'=>array(
			'titre'=>'Liste des ressources'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','previous.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Il n'y a aucune ressource à afficher"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			,'picto_search'=>'<img src="../../theme/eldy/img/search.png">'
		)
		,'title'=>array_merge(array(
		        'ID' => 'ID <span class="nowrap"><a href="javascript:TListTBS_OrderDown(\'list_llx_rh_ressource\',\'ID\')">'.img_down().'</a><a href="javascript:TListTBS_OrderUp(\'list_llx_rh_ressource\', \'ID\')">'.img_up().'</a></span>'
                ,'libelle'=>'Libellé <span class="nowrap"><a href="javascript:TListTBS_OrderDown(\'list_llx_rh_ressource\',\'libelle\')">'.img_down().'</a><a href="javascript:TListTBS_OrderUp(\'list_llx_rh_ressource\', \'libelle\')">'.img_up().'</a></span>'
                ,'numId'=>'Numéro Id <span class="nowrap"><a href="javascript:TListTBS_OrderDown(\'list_llx_rh_ressource\',\'numId\')">'.img_down().'</a><a href="javascript:TListTBS_OrderUp(\'list_llx_rh_ressource\', \'numId\')">'.img_up().'</a></span>'
                ,'fk_rh_ressource_type'=> 'Type <span class="nowrap"><a href="javascript:TListTBS_OrderDown(\'list_llx_rh_ressource\',\'fk_rh_ressource_type\')">'.img_down().'</a><a href="javascript:TListTBS_OrderUp(\'list_llx_rh_ressource\', \'fk_rh_ressource_type\')">'.img_up().'</a></span>'
                ,'lastname'=>'Nom <span class="nowrap"><a href="javascript:TListTBS_OrderDown(\'list_llx_rh_ressource\',\'lastname\')">'.img_down().'</a><a href="javascript:TListTBS_OrderUp(\'list_llx_rh_ressource\', \'lastname\')">'.img_up().'</a></span>'
                ,'firstname'=>'Prénom <span class="nowrap"><a href="javascript:TListTBS_OrderDown(\'list_llx_rh_ressource\',\'firstname\')">'.img_down().'</a><a href="javascript:TListTBS_OrderUp(\'list_llx_rh_ressource\', \'firstname\')">'.img_up().'</a></span>'
            )
            , $TSpeciaux
		)
		,'search'=>($user->rights->ressource->ressource->searchRessource) ?
			array_merge(array(
				'fk_rh_ressource_type'=>array('recherche'=>$ressource->TType)
				,'numId'=>true
				,'libelle'=>true
				,'lastname'=>true
				,'firstname'=>true
			), $TSearch)
			: array()
//		,'orderBy'=>$TOrder

	));

	//si on est en mode utilisateur : on voit la liste des règles le concernant
	if(! $user->rights->ressource->ressource->hideRegle){
		echo '<br>';
		$r = new TSSRenderControler($ressource);
		$sql="SELECT DISTINCT r.rowid as 'ID', r.choixLimite as 'CL', r.choixApplication as 'CA', u.firstname ,u.lastname, g.nom as 'Groupe',
		duree, dureeInt,dureeExt, natureRefac,  CONCAT (CAST(montantRefac as DECIMAL(16,2)), ' €') as 'Montant à déduire'
		FROM ".MAIN_DB_PREFIX."rh_ressource_regle as r
		LEFT OUTER JOIN ".MAIN_DB_PREFIX."user as u ON (r.fk_user = u.rowid)
		LEFT OUTER JOIN ".MAIN_DB_PREFIX."usergroup as g ON (r.fk_usergroup = g.rowid)
		WHERE 1
		AND (r.fk_user=".$user->id."
			OR r.choixApplication = 'all'
			OR g.rowid IS NOT NULL)";

		$idTelephone=getIdType('telephone');
		$TOrder = array('ID'=>'ASC');
		if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
		if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');

		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
		$formCore=new TFormCore($_SERVER['PHP_SELF'].'','formtranslateList','GET');

		$r->liste($PDOdb, $sql, array(
			'link'=>array(
				'ID'=>'<a href="typeRessourceRegle.php?id='.$idTelephone.'&idRegle=@ID@&action=view">@val@</a>'
			)
			,'eval'=>array(
				'dureeInt'=>'afficheOuPas(@val@, @CL@, "extint")'
				,'dureeExt'=>'afficheOuPas(@val@, @CL@, "extint")'
				,'duree'=>'afficheOuPas(@val@, @CL@, "gen")'
				,'Groupe'=>'TousOuPas(@CA@,"@val@")'
				,'firstname'=>'TousOuPas(@CA@,"@val@")'
				,'name'=>'TousOuPas(@CA@,"@val@")'
			)
			,'title'=>array(
				'name'=>'Nom'
				,'firstname'=>'Prénom'
				,'duree'=>'Lim. générale'
				,'dureeInt'=>'Lim. interne'
				,'dureeExt'=>'Lim. externe'
				,'natureRefac' => 'Nature à déduire'
			)
			,'hide'=>array('CA', 'CL')
			,'type'=>array()
			,'liste'=>array(
				'titre'=>'Liste des règles téléphoniques'
				,'image'=>img_picto('','title.png', '', 0)
				,'picto_precedent'=>img_picto('','previous.png', '', 0)
				,'picto_suivant'=>img_picto('','next.png', '', 0)
				,'noheader'=> (int)isset($_REQUEST['ID'])
				,'messageNothing'=>"Il n'y a aucune règle à afficher"
				,'order_down'=>img_picto('','1downarrow.png', '', 0)
				,'order_up'=>img_picto('','1uparrow.png', '', 0)
				,'picto_search'=>'<img src="../../theme/eldy/img/search.png">'
			)
			,'orderBy'=>$TOrder
		));
	}

	$formCore->end();
	llxFooter();
}
function toUtf8($val){
	return iconv(mb_detect_encoding($val, "auto"), "UTF-8", $val);
}
function TousOuPas($choix, $val){
	if ($choix=='all'){
		return 'Tous';}
	return htmlentities($val, ENT_COMPAT , "ISO8859-1");
}


function getStatut($val){
	if (empty($val)){return "Libre";}
	return "Attribué";
}



function _fiche(&$PDOdb, &$emprunt, &$ressource, &$contrat, $mode) {
	global $db,$user,$conf,$mc,$mysoc;
	llxHeader('', 'Ressource', '', '', 0, 0, array('/hierarchie/js/jquery.jOrgChart.js'));

	$formCore=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$formCore->Set_typeaff($mode);
	echo $formCore->hidden('id', $ressource->getId());
	if ($mode=='new'){
		echo $formCore->hidden('action', 'edit');
	}
	else {echo $formCore->hidden('action', 'save');}
	//Ressources
	$TFields=array();

	?>
	<script type="text/javascript">
		$(document).ready(function(){

		<?php
		foreach($ressource->ressourceType->TField as $k=>$field) {
			switch($field->type){
				case 'liste':
					$temp = $formCore->combo('',$field->code,$field->TListe,$ressource->{$field->code});
					break;
				case 'checkbox':
					$temp = $formCore->combo('',$field->code,array('oui'=>'Oui', 'non'=>'Non'),$ressource->{$field->code});
					break;
				case 'date':
					$temp = $formCore->calendrier('', $field->code, $ressource->{$field->code});
					break;
				default:
					$temp = $formCore->texte('', $field->code, $ressource->{$field->code}, 50,255,'','','-');
					break;
			}

			$TFields[$k]=array(
					'libelle'=>$field->libelle
					,'valeur'=>$temp
					//champs obligatoire : 0 = obligatoire ; 1 = non obligatoire
					,'obligatoire'=>$field->obligatoire ? 'class="field"': 'class="fieldrequired"'
				);

			//Autocompletion
			if($field->type != combo && $field->type != liste){
				?>
				$("#<?php echo $field->code; ?>").autocomplete({
					source: "script/interface.php?get=autocomplete&json=1&fieldcode=<?php echo $field->code; ?>",
					minLength : 1
				});

				<?php
			}
		}

		//Concaténation des champs dans le libelle ressource
		foreach($ressource->ressourceType->TField as $k=>$field) {

			if($field->inlibelle == "oui"){
				$chaineid .= "#".$field->code.", ";
				$chaineval .= "$('#".$field->code."').val().toUpperCase()+' '+";
			}

		}
		$chaineval = substr($chaineval, 0,-5);
		$chaineid = substr($chaineid, 0,-2);
		?>
			$('<?php echo $chaineid; ?>').bind("keyup change", function(e) {
				$('#libelle').val(<?php echo $chaineval; ?>);
			});
		});
	</script>
	<?php

	//requete pour avoir toutes les ressources associées à la ressource concernées
	$k=0;
	$sqlReq="SELECT rowid, libelle FROM ".MAIN_DB_PREFIX."rh_ressource WHERE fk_rh_ressource=".$ressource->rowid;
	$PDOdb->Execute($sqlReq);
	$Tab=array();
	$Tab_sous_ressource=array();
	$reqVide=0;	//variable permettant de savoir si la requete existe, et donc au final si on affichera l'organigramme
	while($PDOdb->Get_line()) {
		//récupère les id des différents nom des  groupes de l'utilisateur
		$Tab_sous_ressource[$k]=array('libelle'=>'<a href="?id='.$PDOdb->Get_field('rowid').'">'.$PDOdb->Get_field('libelle').'</a>');
		$k++;
		$reqVide=1;
	}

	$contrat->load_liste($PDOdb);
	$emprunt->load_liste($PDOdb);
	$ressource->load_liste_entity($PDOdb);
	$ressource->load_agence($PDOdb);
	$ressource->load_liste_type_ressource($PDOdb);
	$listeContrat = $ressource->liste_contrat($PDOdb);

	$combo_entite_utilisatrice = ( !empty($conf->multicompany->enabled) ? $mc->select_entities($ressource->fk_entity_utilisatrice,'fk_entity_utilisatrice', $mode != 'edit' ? 'disabled="true"' : ''  ) : $mysoc->name ) ;

	if(defined('AUTOMATIC_ATTRIBUTION_USER_ENTITY_ON_RESSOURCE') && AUTOMATIC_ATTRIBUTION_USER_ENTITY_ON_RESSOURCE ) $combo_entite_utilisatrice = $ressource->TEntity[$ressource->fk_entity_utilisatrice];

	$form=new Form($db);

	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/ressource.tpl.php'
		,array(
			'ressourceField'=>$TFields
			,'sous_ressource'=>$Tab_sous_ressource
		)
		,array(
			'ressource'=>array(
				'id'=>$ressource->getId()
				,'numId'=>$formCore->texte('', 'numId', $ressource->numId, 50,255,'','','-')
				,'libelle'=>$formCore->texte('', 'libelle', $ressource->libelle, 50,255,'','','-')

				,'titreChamps'=>load_fiche_titre("Champs",'', 'title.png', 0, '')
				,'titreOrganigramme'=>load_fiche_titre("Organigramme des ressources associées",'', 'title.png', 0, '')
				,'titreRessourceAssocie'=>load_fiche_titre("Organigramme des ressources associées",'', 'title.png', 0, '')
				,'titreAttribution'=>load_fiche_titre("Attribution de la ressource",'', 'title.png', 0, '')
				,'titreContrat'=>load_fiche_titre("Création d'un contrat directement lié",'', 'title.png', 0, '')

				,'typehidden'=>$formCore->hidden('fk_rh_ressource_type', $ressource->fk_rh_ressource_type)
				,'type'=>$ressource->TType[$ressource->fk_rh_ressource_type]
				,'bailvoit_value'=>$ressource->bailvoit
				,'bailvoit'=>$formCore->combo('','bailvoit',$ressource->TBail,$ressource->bailvoit)
				,'date_achat'=>$formCore->calendrier('', 'date_achat', $ressource->date_achat,12, 12)
				,'date_vente'=>(empty($ressource->date_vente) || ($ressource->date_vente<=0) || ($mode=='new')) ? $formCore->calendrier('', 'date_vente', '' ,12, 12) : $formCore->calendrier('', 'date_vente', $ressource->date_vente,12 , 12)
				//,'date_garantie'=>(empty($ressource->date_garantie) || ($ressource->date_garantie<=0) || ($mode=='new')) ? $formCore->calendrier('', 'date_garantie', '' , 10) : $formCore->calendrier('', 'date_garantie', $ressource->date_garantie, 12)
					,'fk_proprietaire'=>( !empty($conf->multicompany->enabled) ? $mc->select_entities($ressource->fk_proprietaire,'fk_proprietaire', $mode != 'edit' ? 'disabled="true"' : '' ) : $mysoc->name )   //$formCore->combo('','fk_proprietaire',$ressource->TEntity,$ressource->fk_proprietaire)
				,'fk_utilisatrice'=>$form->select_dolgroups($ressource->fk_utilisatrice,'fk_utilisatrice',0,'', $mode!='edit')  //$formCore->combo('','fk_utilisatrice',$ressource->TAgence,$ressource->fk_utilisatrice)
				,'fk_entity_utilisatrice'=>$combo_entite_utilisatrice
				,'fk_loueur'=> ($mode != 'edit' ?_getNomUrl($ressource->fk_loueur) : $form->select_company($ressource->fk_loueur, 'fk_loueur' ,'fournisseur=1'))//$formCore->combo('','fk_loueur',$ressource->TFournisseur,$ressource->fk_loueur)
			)
			,'ressourceNew' =>array(
				'typeCombo'=> count($ressource->TType) ? $formCore->combo('','fk_rh_ressource_type',$ressource->TType,$ressource->fk_rh_ressource_type): "Aucun type"
				,'validerType'=>$formCore->btsubmit('Valider', 'validerType')

			)
			,'fk_ressource'=>array(
				'liste_fk_rh_ressource'=>$formCore->combo('','fk_rh_ressource',$ressource->TRessource,$ressource->fk_rh_ressource)
				,'fk_rh_ressource'=>$ressource->fk_rh_ressource ? $ressource->TRessource[$ressource->fk_rh_ressource] : "aucune ressource"
				,'id'=>$ressource->fk_rh_ressource
				,'reqExiste'=>$reqVide
			)
			,'NEmprunt'=>array(
				'id'=>$emprunt->getId()
				,'type'=>$formCore->hidden('evenement[type]', 'emprunt')
				,'idEven'=>$formCore->hidden('evenement[idEven]', $emprunt->getId())
				,'fk_user'=> $form->select_dolusers($emprunt->fk_user,'evenement[fk_user]') //$formCore->combo('','evenement[fk_user]',$emprunt->TUser,$emprunt->fk_user)
				,'fk_rh_ressource'=> $formCore->hidden('evenement[fk_rh_ressource]', $ressource->getId())
				,'commentaire'=>$formCore->texte('','evenement[commentaire]',$emprunt->commentaire, 30,100,'','','-')
				,'date_debut'=> $formCore->calendrier('', 'evenement[date_debut]', $emprunt->date_debut, 12)
				,'date_fin'=> $formCore->calendrier('', 'evenement[date_fin]', $emprunt->date_fin, 12)
			)
			,'listeContrat'=>array(
				'liste' => $listeContrat
			)
			,'contrat'=>array(
				'id'=>$contrat->getId()
				,'libelle'=>$formCore->texte('', 'contrat[libelle]', $contrat->libelle, 50,255,'','','-')
				,'numContrat'=>$formCore->texte('', 'contrat[numContrat]', $contrat->numContrat, 50,255,'','','-')
				,'fk_rh_ressource'=> $formCore->hidden('contrat[fk_rh_ressource]', $ressource->getId())
				,'tiersFournisseur'=> $form->select_company($contrat->fk_tier_fournisseur, 'fk_tier_fournisseur' ,'fournisseur=1') //  $formCore->combo('','fk_tier_fournisseur',$contrat->TFournisseur,)
				,'tiersAgence'=> $formCore->combo('','contrat[fk_tier_utilisateur]',$contrat->TAgence,$contrat->fk_tier_utilisateur)
				,'date_debut'=> $formCore->calendrier('', 'contrat[date_debut]', $contrat->date_debut, 12)
				,'date_fin'=> $formCore->calendrier('', 'contrat[date_fin]', $contrat->date_fin, 12)
				,'entretien'=>$formCore->texte('', 'contrat[entretien]', $contrat->entretien, 10,20,'','','')
				,'assurance'=>$formCore->texte('', 'contrat[assurance]', $contrat->assurance, 10,20,'','','')
				,'kilometre'=>$formCore->texte('', 'contrat[kilometre]', $contrat->kilometre, 8,8,'','','')
				,'dureemois'=>$formCore->texte('', 'dureemois', $contrat->dureeMois, 8,8,'','','')
				,'loyer_TTC'=>$formCore->texte('', 'contrat[loyer_TTC]', $contrat->loyer_TTC, 10,20,'','','')
				,'TVA'=>$formCore->combo('','contrat[TVA]',$contrat->TTVA,$contrat->TVA)
				,'loyer_HT'=>($contrat->loyer_TTC)*(1-(0.01*$contrat->TTVA[$contrat->TVA]))

			)
			,'view'=>array(
				'mode'=>$mode
				,'userRight'=>((int)$user->rights->ressource->ressource->createRessource)
				,'head'=>dol_get_fiche_head(ressourcePrepareHead($ressource, 'ressource')  , 'fiche', 'Ressource')
				,'onglet'=>dol_get_fiche_head(array()  , '', 'Création ressource')
			)


		)

	);

	echo $formCore->end_form();
	// End of page

	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}
function _getNomUrl($fk_soc) {
	global $db, $conf,$langs;

	$o=new Societe($db);
	$o->fetch($fk_soc);

	return $o->getNomUrl(1);

}


