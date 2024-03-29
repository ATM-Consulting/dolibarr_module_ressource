<?php

	require('config.php');
	require('./class/contrat.class.php');
	require('./class/ressource.class.php');
	require('./lib/ressource.lib.php');
	$langs->load('ressource@ressource');

	//if (!$user->rights->financement->affaire->read)	{ accessforbidden(); }
	$PDOdb=new TPDOdb;
	$association = new TRH_Contrat_Ressource;
	$ressource = new TRH_Ressource;
	$mesg = '';
	$error=false;

	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				$ressource->load($PDOdb, $_REQUEST['id']);
				_fiche($PDOdb, $association,$ressource,'edit');

				break;
			case 'edit'	:
				//$PDOdb->db->debug=true;
				$ressource->load($PDOdb, $_REQUEST['id']);
				$association->load($PDOdb, $_REQUEST['idAssoc']);
				_fiche($PDOdb, $association, $ressource,'edit');
				break;

			case 'save':
				//$PDOdb->db->debug=true;
				$association->load($PDOdb, $_REQUEST['idAssoc']);
				$mesg = '<div class="ok">Modifications effectuées</div>';
				$mode = 'view';

				$association->set_values($_REQUEST);
				$association->save($PDOdb);

				$ressource->load($PDOdb, $_REQUEST['id']);
				$association->load($PDOdb, $_REQUEST['idAssoc']);
				_fiche($PDOdb, $association, $ressource, $mode);
				break;

			case 'deleteAssoc':
				//$PDOdb->db->debug=true;
				$association->load($PDOdb, $_REQUEST['idAssoc']);
				$association->delete($PDOdb);
				$ressource->load($PDOdb, $_REQUEST['id']);
				$mesg = '<div class="ok">Le lien avec le contrat a été supprimée.</div>';
				_liste($PDOdb, $association, $ressource,'view');
				break;
		}
	}
	elseif(isset($_REQUEST['id']) && isset($_REQUEST['idAssoc'])) {
		$ressource->load($PDOdb, $_REQUEST['id']);
		$association->load($PDOdb, $_REQUEST['idAssoc']);
		_fiche($PDOdb, $association, $ressource,'view');
	}
	elseif(isset($_REQUEST['id'])) {
		$ressource->load($PDOdb, $_REQUEST['id']);
		_liste($PDOdb, $association, $ressource,'view');
	}
	else {
		/*
		 * Liste
		 */
		 //$PDOdb->db->debug=true;
		 _liste($PDOdb, $evenement);
	}


	$PDOdb->close();

	llxFooter();

function _liste(&$PDOdb, &$association, &$ressource,  $mode) {
	global $langs,$conf,$user;
	llxHeader('','Liste des contrats');
	getStandartJS();
	dol_fiche_head(ressourcePrepareHead($ressource, 'ressource')  , 'contrats', 'Ressource');
	$newToken = function_exists('newToken') ? newToken() : $_SESSION['newtoken'];
	printLibelle($ressource);

	$r = new TSSRenderControler($association);
	$sql="SELECT DISTINCT a.rowid as 'ID',  c.rowid as 'IDContrat' , c.libelle as 'Libellé',
		DATE(c.date_debut) as 'Date début', DATE(c.date_fin) as 'Date fin', a.commentaire as 'Commentaire'";
	if($user->rights->ressource->contrat->createContract){
		$sql.=", '' as 'Supprimer'";
	}
	$sql.=" FROM ".MAIN_DB_PREFIX."rh_contrat_ressource as a
		LEFT JOIN ".MAIN_DB_PREFIX."rh_contrat as c ON (a.fk_rh_contrat = c.rowid)
		LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (a.fk_rh_ressource = r.rowid)";
	$sql.=" WHERE 1
		AND a.fk_rh_ressource=".$ressource->getId();

	$TOrder = array('Date début'=>'ASC');
	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');

	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
	//print $page;
	$r->liste($PDOdb, $sql, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>'30'
		)
		,'link'=>array(
			'ID'=>'<a href="?id='.$ressource->getId().'&idAssoc=@ID@">@val@</a>'
			,'Libellé'=>'<a href="contrat.php?id=@IDContrat@&token='.$newToken.'">@val@</a>'
			,'Commentaire'=>'<a href="?id='.$ressource->getId().'&idAssoc=@ID@&token='.$newToken.'">@val@</a>'
			,'Supprimer'=>'<a href="?id='.$ressource->getId().'&idAssoc=@ID@&action=deleteAssoc&token='.$newToken.'"><img src="./img/delete.png"></a>'
		)
		,'translate'=>array()
		,'hide'=>array('DateCre', 'IDContrat')
		,'type'=>array(
			'Date début'=>'date'
			,'Date fin'=>'date'
			)
		,'liste'=>array(
			'titre'=>'Liste des contrats'
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

	if($user->rights->ressource->contrat->createContract){
	?></div><a class="butAction" href="?id=<?=$ressource->getId()?>&action=newatoken=<?php echo $newToken; ?>">Nouveau</a><?
	}
	?><div style="clear:both"></div></div><?php
	llxFooter();
}


function _fiche(&$PDOdb,  &$association, &$ressource,  $mode) {
	global $db,$user;
	llxHeader('', 'Contrats');

	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $ressource->getId());
	echo $form->hidden('action', 'save');
	echo $form->hidden('idAssoc',$association->getId());


	$ressource->load_contrat($PDOdb);
	$ressource->load_liste_type_ressource($PDOdb);
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/contratRessource.tpl.php'
		,array(
			//'associations'=>$TContrats
		)
		,array(
			'ressource'=>array(
				'id'=>$ressource->getId()
				,'entete'=>getLibelle($ressource)
				,'titreContratRessource'=>load_fiche_titre("Contrat associé à la ressource",'', 'title.png', 0, '')
			)
			,'NAssociation'=>array(
				'id'=>$association->getId()
				,'fk_rh_ressource'=> $form->hidden('fk_rh_ressource', $ressource->getId())
				,'fk_rh_contrat'=>(count($ressource->TListeContrat) > 0) ? $form->combo('', 'fk_rh_contrat', $ressource->TListeContrat, $association->fk_rh_contrat) : 'Pas de contrats associés au type '.$ressource->TType[$ressource->fk_rh_ressource_type]
				,'commentaire'=>$form->texte('','commentaire',$association->commentaire, 30,100,'','','-')

			)
			,'view'=>array(
				'mode'=>$mode
				,'userRight'=>((int)$user->rights->ressource->contrat->createContract)
				,'head'=>dol_get_fiche_head(ressourcePrepareHead($ressource, 'ressource')  , 'contrats', 'Ressource')
				,'onglet'=>dol_get_fiche_head(array()  , '', 'Contrat associé')
			)


		)

	);

	echo $form->end_form();
	// End of page

	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();

}



