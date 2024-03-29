<?php
	require('config.php');
	require('./class/evenement.class.php');
	require('./class/ressource.class.php');
	require('./lib/ressource.lib.php');
	$langs->load('ressource@ressource');

	$PDOdb=new TPDOdb;
	$evenement=new TRH_Evenement;
	$ressource = new TRH_Ressource;
	$mesg = '';
	$error=false;

	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				//$PDOdb->db->debug=true;
				$ressource->load($PDOdb, $_REQUEST['id']);
				$evenement->set_values($_REQUEST);
				_fiche($PDOdb, $evenement,$ressource,'edit');

				break;
			case 'edit'	:
				//$PDOdb->db->debug=true;
				$ressource->load($PDOdb, $_REQUEST['id']);
				$evenement->load($PDOdb, $_REQUEST['idEven']);
				_fiche($PDOdb, $evenement,$ressource,'edit');
				break;

			case 'save':
				//$PDOdb->db->debug=true;
				$evenement->load($PDOdb, $_REQUEST['idEven']);
				$evenement->set_values($_REQUEST);

				$evenement->save($PDOdb);
				if (!isset($_REQUEST['motif'])|| $_REQUEST['motif']==''){
					$mesg = '<div class="error">Le motif doit être renseigné.</div>';
					$mode = 'edit';

				}
				else{
					$mesg = '<div class="ok">Modifications effectuées</div>';
					$mode = 'view';
				}
				$ressource->load($PDOdb, $_REQUEST['id']);
				_fiche($PDOdb, $evenement,$ressource,$mode);
				break;

			case 'view':
				//$PDOdb->db->debug=true;
				$ressource->load($PDOdb, $_REQUEST['id']);
				$evenement->load($PDOdb, $_REQUEST['idEven'],true);

				_fiche($PDOdb, $evenement,$ressource,'view');
				break;

			case 'deleteEvent':
				//$PDOdb->db->debug=true;
				$evenement->load($PDOdb, $_REQUEST['idEven']);
				$evenement->delete($PDOdb);
				?>
				<script language="javascript">
					document.location.href="?id=<?php echo $_REQUEST['id'];?>&delete_ok=1";
				</script>
				<?php
				/*$ressource->load($PDOdb, $_REQUEST['id']);
				$mesg = '<div class="ok">L\'attribution a bien été supprimée.</div>';
				_liste($PDOdb, $evenement, $ressource, $_REQUEST['type']);*/
				break;

			case 'afficherListe':
				$ressource->load($PDOdb, $_REQUEST['id']);
				_liste($PDOdb, $evenement, $ressource, $_REQUEST['type']);
				break;

		}
	}
	elseif(isset($_REQUEST['id'])) {
		$ressource->load($PDOdb, $_REQUEST['id']);
		_liste($PDOdb, $evenement,$ressource);
	}
	else {
		/*
		 * Liste
		 */
		 //$PDOdb->db->debug=true;
		 _liste($PDOdb, $evenement,$ressource);
	}


	$PDOdb->close();

	llxFooter();

function _liste(&$PDOdb, &$evenement, &$ressource, $type = "all") {
	global $conf,$user;
	llxHeader('','Liste des événements');
	$newToken = function_exists('newToken') ? newToken() : $_SESSION['newtoken'];
	dol_fiche_head(ressourcePrepareHead($ressource, 'ressource')  , 'evenement', 'Ressource');

	printLibelle($ressource);

	$form=new TFormCore($_SERVER['PHP_SELF'],'form2','GET');

	echo $form->hidden('action', 'afficherListe');
	echo $form->hidden('id',$ressource->getId());
	$evenement->load_liste_type( $ressource->fk_rh_ressource_type);

	?>
	<table>
		<tr>
			<td> Type d'évenement à afficher : </td>
			<td> <?php echo $form->combo('','type', $evenement->TType ,$type) ?> </td>
			<td> <?php echo $form->btsubmit('Valider','Valider'); ?>	</td>
		</tr>
	</table>
	<?php
	//'onclick=\'document.location.href="?id='.$ressource->getId().'&action=afficherListe "\''

	$r = new TSSRenderControler($evenement);
	switch($type){
		case 'all' :
			$jointureChamps ="DATE(e.date_debut) as 'Date début', DATE(e.date_fin) as 'Date fin', e.type as 'Type',
				e.motif as 'Motif', tr.codecomptable as 'code comptable', e.commentaire as 'Commentaire', CONCAT (CAST(e.coutTTC as DECIMAL(16,2)), ' €') as 'Coût TTC',
				CONCAT (CAST(e.coutEntrepriseTTC as DECIMAL(16,2)), ' €') as 'Coût pour l\'entreprise TTC', t.taux as 'TVA' ";
			$jointureType = " AND e.type<>'emprunt' ";
			break;
		default :
			$jointureChamps ="DATE(e.date_debut) as 'Date début', DATE(e.date_fin) as 'Date fin',
				e.motif as 'Motif', e.commentaire as 'Commentaire', CONCAT (CAST(e.coutTTC as DECIMAL(16,2)), ' €') as 'Coût',
				CONCAT (CAST(e.coutEntrepriseTTC as DECIMAL(16,2)), ' €') as 'Coût pour l\'entreprise TTC', t.taux as 'TVA' ,
				CONCAT (CAST(e.coutEntrepriseHT as DECIMAL(16,2)), ' €') as 'Coût pour l\'entreprise HT'";
			$jointureType = " AND e.type='".$type."'";
		break;
		}

	$sql = "SELECT DISTINCT e.rowid as 'ID', u.rowid as idUser,
			CONCAT(u.firstname,' ',u.lastname) as 'Utilisateur', ".$jointureChamps;
	if($user->rights->ressource->ressource->manageEvents){
		$sql.=",'' as 'Supprimer'";
	}
	$sql.=" FROM ".MAIN_DB_PREFIX."rh_evenement as e
			LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (e.fk_user = u.rowid)
			LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (e.fk_rh_ressource = r.rowid)
			LEFT JOIN ".MAIN_DB_PREFIX."rh_type_evenement as tr ON (e.type=tr.code)
			LEFT JOIN ".MAIN_DB_PREFIX."c_tva as t ON (e.tva = t.rowid)
			WHERE e.fk_rh_ressource=".$ressource->getId().$jointureType;
	if(!$user->rights->ressource->ressource->manageEvents){
		$sql.=" AND e.fk_user=".$user->id;
	}
	if(!$user->rights->ressource->ressource->readEvenementConfidentiel){
		$sql.=" AND e.confidentiel='non' ";
	}

	$TOrder = array('ID'=>'ASC');
	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');

	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
	$r->liste($PDOdb, $sql, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>'30'
		)
		,'link'=>array(
			'Motif'=>'<a href="?id='.$ressource->getId().'&idEven=@ID@&action=view&token='.$newToken.'">@val@</a>'
			,'Supprimer'=>"<a style=\"cursor:pointer;\" onclick=\"if (window.confirm('Voulez vous supprimer l\'élément ?')){document.location.href='?id=".$ressource->getId()."&idEven=@ID@&type=".$type."&action=deleteEvent'};\"><img src=\"./img/delete.png\"></a>"
		)
		,'translate'=>array('Type'=>$evenement->TType)
		,'hide'=>array('ID','idUser')
		,'type'=>array(
			'Date début'=>'date'
			,'Date fin'=>'date'
			,'Date'=>'date'
			,'Traité le'=>'date'

		)
		,'eval'=>array(
			'Utilisateur'=>'htmlentities("@val@", ENT_COMPAT , "ISO8859-1")'
		)

		,'liste'=>array(
			'titre'=>'Liste des événements de type '.$evenement->TType[$type]
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','previous.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>'Il n\'y a aucun événement à afficher'
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			//,'id'=>$ressource->getId()

		)
		,'orderBy'=>$TOrder

	));

	if($user->rights->ressource->ressource->manageEvents){
	?></div><a class="butAction" href="?id=<?php echo $ressource->getId()?>&action=new&token=<?php echo $newToken; ?>">Nouveau</a><?php
	}
	?><div style="clear:both"></div></div><?php
	$form->end();
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

function _fiche(&$PDOdb, &$evenement,&$ressource,  $mode) {
	global $db,$user,$conf,$langs;
	llxHeader('', 'Evénement');

	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $ressource->getId());
	echo $form->hidden('action', 'save');
	echo $form->hidden('idEven',$evenement->getId());

	$evenement->load_liste($PDOdb);
	$evenement->load_liste_type($ressource->fk_rh_ressource_type);
	$idUserCourant =  $ressource->isEmpruntee($PDOdb, date("Y-m-d", time()));

	$tab = $evenement->TType;

	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/evenement.tpl.php'
		,array()
		,array(
			'ressource'=>array(
				'id'=>$ressource->getId()
				,'numId'=>$ressource->numId
				,'libelle'=>$ressource->libelle
				,'entete'=>getLibelle($ressource)
				,'titreEvenement'=>load_fiche_titre("Evénement sur la ressource",'', 'title.png', 0, '')
				,'URLroot'=>'http://'.$_SERVER['SERVER_NAME']. DOL_URL_ROOT
			)
			,'NEvent'=>array(
				'id'=>$evenement->getId()
				,'user'=>$form->combo('','fk_user',$evenement->TUser,$idUserCourant)
				,'fk_user'=>$evenement->fk_user
				,'tiersimplique'=>$form->combo('', 'tiersimplique', array("oui"=>"Oui", "non"=>"Non"), $evenement->tiersimplique)
				,'fk_rh_ressource'=> $form->hidden('fk_rh_ressource', $ressource->getId())
				,'commentaire'=>$form->zonetexte('','commentaire',$evenement->commentaire,100,10,'','','')
				,'numFacture'=>$form->texte('', 'numFacture', $evenement->numFacture, 10,10)
				,'refexterne'=>$form->texte('', 'refexterne', $evenement->refexterne, 30,60)
				,'confidentiel'=>$form->combo('', 'confidentiel', array("oui"=>$langs->trans('Yes'), "non"=>$langs->trans('No')) ,$evenement->confidentiel)
				,'idContrat'=>$evenement->fk_contrat
				,'motif'=>$form->texte('','motif',$evenement->motif, 30,100,'','','-')
				,'date_debut'=> $form->calendrier('', 'date_debut', $evenement->date_debut,12, 12)
				,'date_fin'=> $form->calendrier('', 'date_fin', $evenement->date_fin,12, 12)
				,'type'=>$form->combo('', 'type', $tab, $evenement->type)
				,'responsabilite'=>$form->combo('', 'responsabilite', $evenement->TResponsabilite, $evenement->responsabilite)
				,'coutTTC'=>$form->texte('', 'coutTTC', ($evenement->coutTTC == 0) ? '0': $evenement->coutTTC, 10,10)
				,'coutEntrepriseTTC'=>$form->texte('', 'coutEntrepriseTTC', $evenement->coutEntrepriseTTC, 10,10, '','','0')
				,'TVA'=>$form->combo('','TVA',$evenement->TTVA,$evenement->TVA)
				,'coutEntrepriseHT'=>$form->texte('', 'coutEntrepriseHT', $evenement->coutEntrepriseHT, 10,10, 'disabled' ,'','0')
				,'appels'=>$evenement->appels
				//($evenement->coutEntrepriseHT)*(1-(0.01*$evenement->TTVA[$evenement->TVA]))
			)
			,'view'=>array(
				'mode'=>$mode
				,'userRight'=>((int)$user->rights->ressource->ressource->manageEvents)
				,'head'=>dol_get_fiche_head(ressourcePrepareHead($evenement, 'evenement', $ressource)  , 'fiche', 'Evénement')
				,'onglet'=>dol_get_fiche_head(array()  , '', 'Evénement')
			)
		)

	);

	echo $form->end_form();
	// End of page

	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();

}



