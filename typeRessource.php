<?php
	require('config.php');
	require('./class/ressource.class.php');
	require('./lib/ressource.lib.php');
	
	$langs->load('ressource@ressource');
	
	//if (!$user->rights->financement->affaire->read)	{ accessforbidden(); }
	$PDOdb=new TPDOdb;
	$ressource=new TRH_ressource_type;
	
	$mesg = '';
	$error=false;
	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				//$PDOdb->db->debug=true;
				$ressource->set_values($_REQUEST);
				//$ressource->save($PDOdb);
				_fiche($PDOdb, $ressource,'edit');
				
				break;	
			case 'edit'	:
				//$PDOdb->db->debug=true;
				$ressource->load($PDOdb, $_REQUEST['id']);
				
				_fiche($PDOdb, $ressource,'edit');
				break;
				
			case 'save':
				//$PDOdb->db->debug=true;
				$ressource->load($PDOdb, $_REQUEST['id']);
				$ressource->set_values($_REQUEST);
				$mesg = '<div class="ok">Modifications effectuées.</div>';
				$mode = 'view';
				if(isset($_REQUEST['TField'])){
				
					foreach($_REQUEST['TField'] as $k=>$field) {
						$ressource->TField[$k]->set_values($field);					
					}
				}
				

				if(isset($_REQUEST['newField']) ) {				
					//ajout de ce champs à la classe ressource
					$ressource->addField($PDOdb, $_REQUEST['TNField']);
					
					$mesg = '<div class="ok">Le champs a bien été créé.</div>';
					$mode = 'edit';
				}

				
				$ressource->save($PDOdb);
				$ressource->load($PDOdb, $_REQUEST['id']);
				_fiche($PDOdb, $ressource,$mode);
				break;
			
			case 'view':
				$ressource->load($PDOdb, $_REQUEST['id']);
				_fiche($PDOdb, $ressource,'view');
				break;
		
			case 'delete':
				$ressource->load($PDOdb, $_REQUEST['id']);
				//$PDOdb->db->debug=true;
				
				//avant de supprimer, on vérifie qu'aucune ressource n'est de ce type. Sinon on ne le supprime pas.
				if (!$ressource->isUsedByRessource($PDOdb)){
					if ($ressource->delete($PDOdb)){
						?>
						<script language="javascript">
							document.location.href="?delete_ok=1";					
						</script>
						<?php	
					}
					else{
						$mesg = '<div class="error">Ce type de ressource ne peut pas être supprimé.</div>';
						_liste($PDOdb, $ressource);
					}
				}
				else{
					$mesg = '<div class="error">Le type de ressource est utilisé par une ressource. Il ne peut pas être supprimé.</div>';
					_liste($PDOdb, $ressource);
				} 
				
				
				break;
		}
	}
	elseif(isset($_REQUEST['id'])) {
		$ressource->load($PDOdb, $_REQUEST['id']);
		
		_fiche($PDOdb, $ressource, 'view');
		
	}
	else {
		/*
		 * Liste
		 */
		 _liste($PDOdb, $ressource);
	}
	
	
	$PDOdb->close();
	
	
function _liste(&$PDOdb, &$ressource) {
	global $langs,$conf, $db;	
	
	llxHeader('','Type Ressource');
	print dol_get_fiche_head(array()  , '', 'Liste types de ressources');
	getStandartJS();
	
	$r = new TSSRenderControler($ressource);
	$sql="SELECT rowid as 'ID', libelle as 'Libellé', code as 'Code', '' as 'Supprimer'
		FROM ".MAIN_DB_PREFIX."rh_ressource_type
		WHERE 1 ";
	
	$TOrder = array('Code'=>'ASC');
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
			'Libellé'=>'<a href="?id=@ID@&action=view">@val@</a>'
			,'Supprimer'=>"<a style=\"cursor:pointer;\" onclick=\"if (window.confirm('Voulez vous supprimer l\'élément ?')){document.location.href='?id=@ID@&action=delete'};\"><img src=\"./img/delete.png\"></a>"
		)
		,'translate'=>array()
		,'hide'=>array()
		,'type'=>array()
		,'liste'=>array(
			'titre'=>'Liste des types de ressources'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','previous.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Il n'y a aucun type de ressource à afficher"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			
		)
		,'orderBy'=>$TOrder
		
	));
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}	
	
function _fiche(&$PDOdb, &$ressource, $mode) {
	global $db,$user;


	llxHeader('','Type de ressource', '', '', 0, 0);
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $ressource->getId());
	echo $form->hidden('action', 'save');
	
	
	
	
	
	$TBS=new TTemplateTBS();
	
	print $TBS->render('./tpl/ressource.type.tpl.php'
		,array()
		,array(
			'ressourceType'=>array(
				'id'=>$ressource->getId()
				,'code'=>$form->texte('', 'code', $ressource->code, 20,255,'','','à saisir')
				,'libelle'=>$form->texte('', 'libelle', $ressource->libelle, 20,255,'','','à saisir') 
				,'supprimable'=>$form->hidden('supprimable', 1)
			)
			,'view'=>array(
				'mode'=>$mode
				,'nbChamps'=>count($ressource->TField)
				,'head'=>dol_get_fiche_head(ressourcePrepareHead($ressource)  , 'fiche', 'Type de ressource')
				,'onglet'=>dol_get_fiche_head(array()  , '', 'Création type de ressource')
			)
			
		)	
		
	);
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

	
	
