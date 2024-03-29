[onshow;block=begin;when [view.mode]=='view']
	[view.head;strconv=no]
[onshow;block=end]

[onshow;block=begin;when [view.mode]!='view']
	[view.onglet;strconv=no]
[onshow;block=end]

<link rel="stylesheet" type="text/css" href="./css/jquery.jOrgChart.css" />
<script>
    jQuery(document).ready(function() {
    	$("#JQorganigramme").jOrgChart({
            chartElement : '#chart',
            dragAndDrop : false
        });
    });
</script>


<div class="fichecenter">

	<div class="fichehalfleft">

		<!-- entête du tableau -->
		<table class="border tableforfield" style="width:100%">
			<tbody>
			[onshow;block=begin;when [view.mode]=='new']
				<tr>
					<td style="width:20%">Type</td>
					<td>[ressourceNew.typeCombo;strconv=no;protect=no]</td>
					<td>[ressourceNew.validerType;strconv=no;protect=no]</td>
				</tr>
			[onshow;block=end]

			[onshow;block=begin;when [view.mode]!='new']
				<tr >
					<td style="width:20%">Type</td>
					<td>[ressource.type;strconv=no;protect=no]</td>[ressource.typehidden;strconv=no;protect=no]
				</tr>

				<tr>
					<td>Numéro Id</td>
					<td>[ressource.numId;strconv=no;protect=no] </td>
				</tr>
				<tr>
					<td>Libellé</td>
					<td>[ressource.libelle;strconv=no;protect=no] </td>
				</tr>

				[onshow;block=begin;when [view.mode]!='new']
					[onshow;block=begin;when [ressource.bailvoit_value]!='']
						[onshow;block=begin;when [ressource.bailvoit_value]=='Immo']
						<tr>
							<td id="datedeb">Date d'achat</td>
							<td>[ressource.date_achat;strconv=no;protect=no]</td>
						</tr>
						<tr id="trdatefin" style="display:none;">
							<td>Date fin location</td>
							<td>[ressource.date_vente;strconv=no;protect=no]</td>
						</tr>
						[onshow;block=end]
						[onshow;block=begin;when [ressource.bailvoit_value]!='Immo']
						<tr>
							<td id="datedeb">Date début location</td>
							<td>[ressource.date_achat;strconv=no;protect=no]</td>
						</tr>
						<tr id="trdatefin">
							<td>Date fin location</td>
							<td>[ressource.date_vente;strconv=no;protect=no]</td>
						</tr>
						[onshow;block=end]
					[onshow;block=end]
					[onshow;block=begin;when [ressource.bailvoit_value]=='']
						<tr>
							<td id="datedeb">Date début location</td>
							<td>[ressource.date_achat;strconv=no;protect=no]</td>
						</tr>
						<tr id="trdatefin">
							<td>Date fin location</td>
							<td>[ressource.date_vente;strconv=no;protect=no]</td>
						</tr>
					[onshow;block=end]
					<script type="text/javascript">
						$(document).ready( function(){

							$("#bailvoit").change(function(){
								if($("#bailvoit").val() == 'Immo')
								{
									$('#datedeb').html('Date d\'achat');
									$('#trdatefin').hide();
								}else{
									$('#datedeb').html('Date début location');
									$('#trdatefin').show();
								}
							});

						});
					</script>
				[onshow;block=end]
				<tr>
					<td>Mode d'acquisition</td>
					<td>[ressource.bailvoit;strconv=no;protect=no]</td>
				</tr>

				<tr>
					<td>Entité Propriétaire</td>
					<td>[ressource.fk_proprietaire;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>Fournisseur</td>
					<td>[ressource.fk_loueur;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>Entité Utilisatrice</td>
					<td colspan=3>[ressource.fk_entity_utilisatrice;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>Agence Utilisatrice</td>
					<td colspan=3>[ressource.fk_utilisatrice;strconv=no;protect=no]</td>
				</tr>
			[onshow;block=end]
			</tbody>
		</table>
		<div style="clear:both"></div>
	</div>

	[onshow;block=begin;when [view.mode]!='new']
		<div class="fichehalfright">
			<div class="ficheaddleft">

				<table class="border tableforfield" style="width:100%">
					<tbody>
					<tr>
						<td class="titlefield [ressourceField.obligatoire;strconv=no;protect=no]">
							[ressourceField.libelle;block=tr;strconv=no;protect=no]
						</td>
						<td> [ressourceField.valeur;strconv=no;protect=no] </td>

					</tr>
					</tbody>
				</table>

			</div>
			<div style="clear:both"></div>
		</div>

		<div style="clear:both"></div>
		[onshow;block=begin;when [view.mode]=='view']
			[view.end;strconv=no]
		[onshow;block=end]
	[onshow;block=end]
</div> <!-- fin fichecenter -->
<br>

[onshow;block=begin;when [view.mode]=='edit']
	[ressource.titreRessourceAssocie;strconv=no;protect=no]
	<div>
		[fk_ressource.liste_fk_rh_ressource;strconv=no;protect=no]
	</div>
[onshow;block=end]


[onshow;block=begin;when [view.mode]=='edit']
	<br><br>
	[ressource.titreAttribution;strconv=no;protect=no]

	<p> Attribuer directement cette ressource à un utilisateur :
		<INPUT type=radio name="fieldChoice" value="O" id="ouiChecked"><label for="ouiChecked"> Oui</label>
		<INPUT type=radio name="fieldChoice" value="N" id="nonChecked" checked="checked"><label for="nonChecked"> Non</label>
	</p>

	<table id="tableAttribution" class="border" style="width:100%">
		[NEmprunt.fk_rh_ressource;strconv=no;protect=no]
		[NEmprunt.type;strconv=no;protect=no]
		[NEmprunt.idEven;strconv=no;protect=no]
		<tr>
			<td>Utilisateur</td>
			<td>[NEmprunt.fk_user;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td>Date début</td>
			<td>[NEmprunt.date_debut;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td>Date fin</td>
			<td>[NEmprunt.date_fin;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td>Commentaire</td>
			<td>[NEmprunt.commentaire;strconv=no;protect=no]</td>
		</tr>
	</table>

	<br><br>
	[ressource.titreContrat;strconv=no;protect=no]

	<p> Attribuer directement cette ressource à un contrat :
		<INPUT type=radio name="fieldChoiceContrat" value="O" id="ouiCheckedContrat"><label for="ouiCheckedContrat"> Oui</label>
		<INPUT type=radio name="fieldChoiceContrat" value="N" id="nonCheckedContrat" checked="checked"><label for="nonCheckedContrat"> Non</label>
	</p>

	<table id="tableContrat" class="border" style="width:100%">
		[contrat.fk_rh_ressource;strconv=no;protect=no]
		<tr>
				<td style="width:20%">Libellé du contrat</td>
				<td>[contrat.libelle;strconv=no;protect=no]</td>
			</tr>
			<tr>
				<td>Numéro du contrat</td>
				<td>[contrat.numContrat;strconv=no;protect=no]</td>
			</tr>
			<tr>
				<td>Fournisseur concerné</td>
				<td>[contrat.tiersFournisseur;strconv=no;protect=no]</td>
			</tr>
			<tr>
				<td>Date de début</td>
				<td>[contrat.date_debut;strconv=no;protect=no]</td>
			</tr>
			<tr>
				<td>Date de fin</td>
				<td>[contrat.date_fin;strconv=no;protect=no]</td>
			</tr>
			<tr id="km" >
				<td>Kilomètrage</td>
				<td>[contrat.kilometre;strconv=no;protect=no] km</td>
			</tr>
			<tr id="dureeeenmois" >
				<td>Durée mois</td>
				<td>[contrat.dureemois;strconv=no;protect=no] mois</td>
			</tr>
			<script>
				$('#fk_tier_fournisseur').change(function()
					{actuKm();})
				$(document).ready(function()
					{actuKm();});

				function actuKm(){
					if ($('#fk_tier_fournisseur option:selected').html()=='Parcours'){
						$('#km').show();
						$('#dureeeenmois').show();}
					else{
						$('#km').hide();
						$('#dureeeenmois').hide();
					}
				}
			</script>
			<tr>
				<td>Montant Entretien</td>
				<td>[contrat.entretien;strconv=no;protect=no] €</td>
			</tr><tr>
				<td>Montant Assurance</td>
				<td>[contrat.assurance;strconv=no;protect=no] €</td>
			</tr><tr>
				<td>Loyer mensuel TTC</td>
				<td>[contrat.loyer_TTC;strconv=no;protect=no] €</td>
			</tr><tr>
				<td>TVA </td>
				<td>[contrat.TVA;strconv=no;protect=no] %</td>
			</tr>
			<tr>
				<td>Loyer mensuel HT</td>
				<td>[contrat.loyer_HT;strconv=no;protect=no] €</td>
			</tr>
	</table>
[onshow;block=end]


<script>
	$(document).ready( function(){
		$('#tableAttribution').hide();
		$('#ouiChecked').click(function(){
			$('#tableAttribution').show();
		});
		$('#nonChecked').click(function(){
			$('#tableAttribution').hide();
		})

		$('#tableContrat').hide();
		$('#ouiCheckedContrat').click(function(){
			$('#tableContrat').show();
		});
		$('#nonCheckedContrat').click(function(){
			$('#tableContrat').hide();
		})

		//on empêche que la date de début dépasse pas celle de fin
		function comparerDates(){

			dd = $("#date_debut").val().split("/");
			df = $("#date_fin").val().split("/");

			var dDebut = new Date(dd[2], dd[1]-1, dd[0], 0,0,0,0);
			var dFin = new Date(df[2], df[1]-1, df[0], 0,0,0,0);

			if(dFin.getTime() < dDebut.getTime()) {
				$("#date_fin").val($("#date_debut").val());
			}

		}

		$("#date_debut").change(comparerDates);
		$("#date_fin").change(comparerDates);

	});
</script>

[onshow;block=begin;when [view.mode]!='new']
	[onshow;block=begin;when [view.userRight]==1]
		[onshow;block=begin;when [view.mode]=='edit']
			<div align="center">
				<input type="submit" value="Enregistrer" name="save" class="button">
				[onshow;block=begin;when [ressource.id]!=0]
					<input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[ressource.id]'">
				[onshow;block=end]
				[onshow;block=begin;when [ressource.id]==0]
					<input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href=''">
				[onshow;block=end]
			</div>
		[onshow;block=end]

		[onshow;block=begin;when [view.mode]!='edit']
			<div class="tabsAction">
				<a class="butAction"  href="?id=[ressource.id]&action=edit">Modifier</a>
				<a class="butAction"  href="?id=[ressource.id]&action=clone">Cloner</a>
				<a class="butActionDelete"  onclick="if (window.confirm('Voulez vous supprimer l\'élément ?')){document.location.href='?id=[ressource.id]&action=delete'};">Supprimer</a>
			</div>
		[onshow;block=end]
	[onshow;block=end]
[onshow;block=end]

[onshow;block=begin;when [view.mode]=='view']
	[listeContrat.liste;strconv=no;protect=no]
[onshow;block=end]<br>



<!--  href='ressource.php?id=[fk_ressource.id]'-->

		[onshow;block=begin;when [fk_ressource.fk_rh_ressource]=='aucune ressource']
			[onshow;block=begin;when [fk_ressource.reqExiste]=='1']
				[onshow;block=begin;when [view.mode]=='view']
				</br>
				[ressource.titreOrganigramme;strconv=no;protect=no]
					<div id="organigrammePrincipal" style="margin-left:70px;text-align:center;">
						<br/>
						<div id="chart" class="orgChart" ></div>
							<ul id="JQorganigramme" style="display:none;">
								<li> [ressource.libelle;strconv=no;protect=no]
									(Ressource courante)
									<ul>
											<li>
												[sous_ressource.libelle;block=li;strconv=no;protect=no]
												<ul>

												</ul>
											</li>
									</ul>
								</li>
							</ul>
					</div>
				[onshow;block=end]
			[onshow;block=end]
		[onshow;block=end]


		[onshow;block=begin;when [fk_ressource.fk_rh_ressource]!='aucune ressource']
			[onshow;block=begin;when [view.mode]=='view']
				</br>
				[ressource.titreOrganigramme;strconv=no;protect=no]
					<div id="organigrammePrincipal" style="margin-left:70px;text-align:center;">
					<br/>
					<div id="chart" class="orgChart" ></div>
						<ul id="JQorganigramme" style="display:none;">
							<li> <a href="?id=[fk_ressource.id]">[fk_ressource.fk_rh_ressource;strconv=no;protect=no]</a>
								<ul>
										<li>
											 [ressource.libelle;strconv=no;protect=no]</a>
											 (Ressource courante)
											<ul>

											</ul>
										</li>
								</ul>
							</li>
						</ul>
				</div>
			[onshow;block=end]
		[onshow;block=end]



<div style="clear:both"></div>

