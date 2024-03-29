<?php
	require('config.php');
	require('./class/ressource.class.php');
	require('./class/evenement.class.php');
	require('./lib/ressource.lib.php');

	$langs->load('ressource@ressource');

	//if (!$user->rights->financement->affaire->read)	{ accessforbidden(); }
	$PDOdb=new TPDOdb;
	$ressourceType=new TRH_ressource_type;
	$typeEven = new TRH_Type_Evenement;
	$mesg = '';
	$error=false;
	//llxHeader('','Type d\'événement sur le type', '', '', 0, 0);



	if(isset($_REQUEST['id'])){
		$ressourceType->load($PDOdb, $_REQUEST['id']);
		if (isset($_REQUEST['action'])){
			switch($_REQUEST['action']){
				case 'add':
				case 'new':
					//$typeEven->load($PDOdb, $_REQUEST['idTypeEvent']);
					_fiche($PDOdb, $typeEven, $ressourceType, 'edit');
					break;
				case 'edit' :
					$typeEven->load($PDOdb, $_REQUEST['idTypeEvent']);
					_fiche($PDOdb, $typeEven, $ressourceType, 'edit');
					break;
				case 'save':
					$typeEven->load($PDOdb, $_REQUEST['idTypeEvent']);
					$mode = 'edit';
					if (empty($_REQUEST['libelle'])){
						$mesg = '<div class="error">Veuillez remplir le libellé.</div>';
					}
					else if (empty($_REQUEST['code'])){
						$mesg = '<div class="error">Veuillez remplir le code.</div>';
					}
					else if (empty($_REQUEST['codecomptable'])){
						$mesg = '<div class="error">Veuillez remplir le code analytique.</div>';
					}
					else {
						$mesg = '<div class="ok">Modifications effectuées</div>';
						$mode = 'view';
					}
					$typeEven->set_values($_REQUEST);
					$typeEven->save($PDOdb);
					_fiche($PDOdb, $typeEven, $ressourceType, $mode);
					break;
				case 'view':
					//$PDOdb->db->debug=true;
					$typeEven->load($PDOdb, $_REQUEST['idTypeEvent']);
					_fiche($PDOdb, $typeEven, $ressourceType, 'view');
					break;
				case 'delete' :
					$typeEven->load($PDOdb, $_REQUEST['idTypeEvent']);
					if ($typeEven->supprimable == 'vrai'){
						$typeEven->delete($PDOdb);
						$ressourceType->load($PDOdb, $_REQUEST['id']);
						?>
						<script language="javascript">
							document.location.href="?id=<?php echo $_REQUEST['id'];?>&delete_ok=1";
						</script>
						<?php
					}
					else {
						$mesg = '<div class="ok">Impossible de supprimer ce type d\événement.</div>';
						_fiche($PDOdb, $typeEven, $ressourceType, 'view');

					}
					break;

				default :
					break;
			}
		}
		else {
			_liste($PDOdb, $ressourceType, $typeEven);
		}
	}

	$PDOdb->close();


function _liste(&$PDOdb, &$ressourceType, &$even) {
	global $langs,$conf, $db;
	$newToken = function_exists('newToken') ? newToken() : $_SESSION['newtoken'];
	llxHeader('','Règles sur les Ressources');
	dol_fiche_head(ressourcePrepareHead($ressourceType, 'type-ressource')  , 'event', 'Type de ressource');

		echo '<table width="100%" class="border">
			<tr><td width="20%">Libellé</td><td>'.$ressourceType->libelle.'</td></tr>
			<tr><td width="20%">Code</td><td>'.$ressourceType->code.'</td></tr>
		</table><br>';

	$r = new TSSRenderControler($ressourceType);
	$sql="SELECT rowid as ID, libelle, code, codecomptable, fk_rh_ressource_type, supprimable
		FROM ".MAIN_DB_PREFIX."rh_type_evenement as r
		WHERE (fk_rh_ressource_type=0 OR fk_rh_ressource_type=".$ressourceType->getId().")";


	$TOrder = array('fk_rh_ressource_type'=>'ASC');
	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');

	$TOuiRien = array('vrai'=>'Oui', 'faux'=>'');
	$TOuiNon = array('vrai'=>'Oui', 'faux'=>'Non');
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
	$form=new TFormCore($_SERVER['PHP_SELF'].'?id='.$ressourceType->getId(),'formtranslateList','GET');
	echo $form->hidden('id',$ressourceType->getId());

	$r->liste($PDOdb, $sql, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>'30'
		)
		,'link'=>array(
			'libelle'=>'<a href="?id='.$ressourceType->getId().'&idTypeEvent=@ID@&action=view&token='. $newToken .'">@val@</a>'
		)
		,'translate'=>array(
			'supprimable'=>array('vrai'=>'Non', 'faux'=>'Oui')
		)
		,'title'=>array(
			'libelle'=>'Libellé'
			,'code'=>'Code'
			,'codecomptable'=>'Code Comptable'
			,'supprimable'=>'Type par défaut'
		)
		,'hide'=>array('ID', 'fk_rh_ressource_type')
		,'liste'=>array(
			'titre'=>'Liste des types d\'événements'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','previous.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['ID'])
			,'messageNothing'=>"Il n'y a aucun type d'événement à afficher"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			,'picto_search'=>'<img src="../../theme/rh/img/search.png">'
		)
		,'orderBy'=>$TOrder

	));

	?></div><a class="butAction" href="?id=<?php echo $ressourceType->getId()?>&action=new&token=<?php echo $newToken; ?>">Nouveau</a>
	<div style="clear:both"></div><?php
	$form->end();
	llxFooter();
}

function _fiche(&$PDOdb, &$typeEven, &$ressourceType, $mode) {
	llxHeader('','Règle sur les Ressources', '', '', 0, 0);



	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $ressourceType->getId());
	echo $form->hidden('idTypeEvent', $typeEven->getId());
	echo $form->hidden('action', 'save');
	echo $form->hidden('fk_rh_ressource_type', $ressourceType->getId());

	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/ressource.type.evenement.tpl.php'
		,array()
		,array(
			'ressourceType'=>array(
				'id'=>$ressourceType->getId()
				,'code'=> $ressourceType->code
				,'libelle'=> $ressourceType->libelle
				,'titreEvenement'=>load_fiche_titre('Type d\'événement','', 'title.png', 0, '')
			)
			,'newEvent'=>array(
				'id'=>$typeEven->getId()
				,'libelle'=>$form->texte('', 'libelle', $typeEven->libelle, 20,30,'','','')
				,'code'=>$form->texte('', 'code', $typeEven->code, 20,30,'','','')
				,'codecomptable'=>$form->texte('', 'codecomptable', $typeEven->codecomptable, 20,30,'','','')
				,'supprimable'=>$typeEven->supprimable

			)
			,'view'=>array(
				'mode'=>$mode
				,'head'=>dol_get_fiche_head(ressourcePrepareHead($ressourceType)  , 'event', 'Type de ressource')
				,'onglet'=>dol_get_fiche_head(array()  , '', 'Type d\'événement')
			)

		)

	);

	echo $form->end_form();
	// End of page

	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}
