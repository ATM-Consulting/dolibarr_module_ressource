<?php
//TODO fullcalendar instead of wdcalendar

	require('config.php');
	require('./class/ressource.class.php');
	require('./lib/ressource.lib.php');
	
	list($langjs,$dummy) =explode('_', $langs->defaultlang);
	llxHeader('', $langs->trans('RessourceCalendar'), '', '', 0,0,
			array('/fullcalendar/lib/moment/min/moment.min.js', '/fullcalendar/lib/fullcalendar/dist/fullcalendar.min.js','/fullcalendar/lib/fullcalendar/dist/lang/'.$langjs.'.js')
			,array('/fullcalendar/lib/fullcalendar/dist/fullcalendar.min.css','/fullcalendar/css/fullcalendar.css')
	);
		
	$PDOdb=new TPDOdb;
	$ressource=new TRH_ressource;
	$ressource->load($PDOdb, $_REQUEST['id']);
	
	$fiche = isset($_REQUEST['fiche']) ? $_REQUEST['fiche'] : false;
	//$idCombo = isset($_REQUEST['idCombo']) ? $_REQUEST['idCombo'] : 0; 
	$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
	$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : $ressource->fk_rh_ressource_type;
	$fk_user = isset($_REQUEST['fk_user']) ? $_REQUEST['fk_user'] : 0;
	$typeEven = isset($_REQUEST['typeEven']) ? $_REQUEST['typeEven'] : 'all' ;
	
	$formCore=new TFormCore($_SERVER['PHP_SELF'],'form2','GET');
	echo $formCore->hidden('type', $type);
	
	echo $formCore->hidden('id', $ressource->getId());
		
	echo $formCore->hidden('action', 'afficher');
	//echo $formCore->hidden('id',$ressource->getId());
	//echo 'Type : '.$type.' id : '.$id.' user : '.$fk_user.' even : '.$typeEven.'<br>';
	$url = ($id>0 ? 'id='.$id : '').($type>0 ? '&type='.$type : '' ).($idCombo>0 ? 'idCombo='.$idCombo : '').($fk_user>0 ? '&fk_user='.$fk_user : '' ).'&typeEven='.($typeEven ? $typeEven : 'all' );
	
	//LISTE DE USERS
	$TUser = array('');
	$sqlReq="SELECT rowid, firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE entity IN (0,".$conf->entity.")";
	$PDOdb->Execute($sqlReq);
	while($PDOdb->Get_line()) {
		$TUser[$PDOdb->Get_field('rowid')] = htmlentities($PDOdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1')." ".htmlentities($PDOdb->Get_field('name'), ENT_COMPAT , 'ISO8859-1');
		}
	
	$TRessource = array('');
	$sqlReq="SELECT rowid,libelle, numId FROM ".MAIN_DB_PREFIX."rh_ressource WHERE entity IN (0,".$conf->entity.")";
	if ($type>0){$sqlReq .= " AND fk_rh_ressource_type = ".$type;}
		$PDOdb->Execute($sqlReq);
		while($PDOdb->Get_line()) {
			$TRessource[$PDOdb->Get_field('rowid')] = $PDOdb->Get_field('libelle').' '.$PDOdb->Get_field('numId');
			}

	$ressource->load_liste_type_ressource($PDOdb);
	$TType = array_merge($ressource->TType);
	if ($fiche) {
		$TTypeEvent = getTypeEvent($ressource->fk_rh_ressource_type,true);
	}
	else {
		$TTypeEvent = getTypeEvent($type, true);
	}

	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/calendrier.tpl.php'
		,array()
		,array(
			'ressource'=>array(
				'id' => $id//$ressource->getId()
				,'entete'=>getLibelle($ressource)
				,'titreAgenda'=>load_fiche_titre("Agenda des ressources",'', 'title.png', 0, '')
				,'idHidden'=>$formCore->hidden('id', $id)
				,'fiche'=> $fiche
				,'ficheHidden'=>$formCore->hidden('fiche', $fiche)
				,'type'=>$formCore->combo('', 'type', $TType, $type)
				,'typeURL'=>$type
				,'idRessource'=>$formCore->combo('', 'id', $TRessource, $id)
				,'fk_user'=>$formCore->combo('', 'fk_user', $TUser, $fk_user)
				,'typeEven'=>$formCore->combo('', 'typeEven', $TTypeEvent, $typeEven)
				,'typeEvenURL'=>$typeEven
				,'URL'=>$url
				,'btValider'=>$formCore->btsubmit('Valider', 'valider')
				,'numId'=>$ressource->numId
				,'libelle'=>$ressource->libelle
			)
			,'view'=>array(
				'mode'=>$mode
				,'userDroitAgenda'=>((int)$user->rights->ressource->agenda->manageAgenda)
				,'head'=>dol_get_fiche_head(ressourcePrepareHead($ressource, 'ressource')  , 'calendrier', 'Ressource')
				,'onglet'=>dol_get_fiche_head(array()  , '', 'Agenda')
			)
			
			
		)	
		
	);
	
	$formCore->end();

	$defaultDay = date('d');
	?>
<script type="text/javascript">


var year = '<?php echo date('Y'); ?>';
var month = '<?php echo date('m'); ?>';
var defaultDate = year+'-'+month+'-<?php echo $defaultDay; ?>';
var defaultView='month';

var currentsource = '<?php echo dol_buildpath('/ressource/ressourceCalendarDataFeed.php',1) ?>'+'?'+$('form[name=form2]').serialize();

$('#fullcalendar').fullCalendar({
	        header:{
	        	left:   'title',
			    right:  'prev,next today'
	        }
	        ,defaultDate:defaultDate
	        ,lang: 'fr'
	        ,weekNumbers:true
			,defaultView:'month'
			,eventSources : [currentsource]
			,eventLimit : <?php echo !empty($conf->global->AGENDA_MAX_EVENTS_DAY_VIEW) ? $conf->global->AGENDA_MAX_EVENTS_DAY_VIEW : 3; ?>
			<?php
				if(!empty($conf->global->FULLCALENDAR_HIDE_DAYS)) {

					?>
					,hiddenDays: [ <?php echo $conf->global->FULLCALENDAR_HIDE_DAYS ?> ]
					<?php

				}
			?>
			,eventRender:function( event, element, view ) {

				var note = "";
				<?php

				if($conf->global->FULLCALENDAR_USE_HUGE_WHITE_BORDER) {
					echo 'element.css({
						"border":""
						,"border-radius":"0"
						,"border":"1px solid #fff"
						,"border-left":"2px solid #fff"
					});';

				}

				?>
				if(event.note) note+=event.note;

				if(event.fk_soc>0){
					 element.append('<div>'+event.societe+'</div>');
					 note += '<div>'+event.societe+'</div>';
				}
				if(event.fk_contact>0){
					 element.append('<div>'+event.contact+'</div>');
					 note += '<div>'+event.contact+'</div>';
				}
				<?php
				if(!empty($conf->global->FULLCALENDAR_SHOW_AFFECTED_USER)) {

					?>
					if(event.fk_user>0){
						 element.append('<div>'+event.user+'</div>');
						 note += '<div>'+event.user+'</div>';
					}
					<?php

				}

				if(!empty($conf->global->FULLCALENDAR_SHOW_PROJECT)) {

					?>
					if(event.fk_project>0){
						 element.append('<div>'+event.project+'</div>');
						 note = '<div>'+event.project+'</div>'+note;
					}
					<?php
				}

				?>
				if(event.more)  {
					 element.append('<div>'+event.more+'</div>');
					 note = note+'<div>'+event.more+'</div>';
				}

				element.prepend('<div style="float:right;">'+event.statut+'</div>');

				element.tipTip({
					maxWidth: "600px", edgeOffset: 10, delay: 50, fadeIn: 50, fadeOut: 50
					,content : '<strong>'+event.title+'</strong><br />'+ note
				});

				element.find(".classfortooltip").tipTip({maxWidth: "600px", edgeOffset: 10, delay: 50, fadeIn: 50, fadeOut: 50});
				element.find(".classforcustomtooltip").tipTip({maxWidth: "600px", edgeOffset: 10, delay: 50, fadeIn: 50, fadeOut: 5000});

			 }
			
	    });   
       
       
   

   
</script> 
	<?php 
	
	llxFooter();
	
