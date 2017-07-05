

[onshow;block=begin;when [ressource.fiche]==true]
	[view.head;strconv=no;protect=no]
	[ressource.entete;strconv=no;protect=no]
[onshow;block=end] 
 
[onshow;block=begin;when [ressource.fiche]!=true]
	[view.onglet;strconv=no;protect=no]
[onshow;block=end] 


[ressource.titreAgenda;strconv=no;protect=no]
[ressource.ficheHidden;strconv=no;protect=no]

[onshow;block=begin;when [ressource.fiche]==true]
	[ressource.idHidden;strconv=no;protect=no]
	Filtre sur le type d'événément : 
	[ressource.typeEven;strconv=no;protect=no]
	[ressource.btValider;strconv=no;protect=no]
	<br><br>
[onshow;block=end]


[onshow;block=begin;when [ressource.fiche]!=true] 
[onshow;block=begin;when [view.userDroitAgenda]==1] 

 


		<table class="border" style="width:100%">
			<tr>
				<td style="width:10%">Type</td>
				<td style="width:30%">Ressource</td>
				<td style="width:30%">Utilisateur</td>
				<td style="width:20%">Evénement</td>
				<td rowspan="2" style="width:10%;text-align:center">[ressource.btValider;strconv=no;protect=no]</td>
			</tr>
			<tr>
				<td>[ressource.type;strconv=no;protect=no]</td>
				<td>[ressource.idRessource;strconv=no;protect=no]</td>
				<td>[ressource.fk_user;strconv=no;protect=no]</td>
				<td>[ressource.typeEven;strconv=no;protect=no]</td>
			</tr>
			
		</table>			
         <br><br>
[onshow;block=end]
[onshow;block=end] 	

<div id="fullcalendar"></div>
<style style="text/css">
a.fc-day-grid-event,a.fc-time-grid-event  {
	color:#000;
	font-weight:normal;
}
</style>
		

<script type="text/javascript">
ajaxLoadType = function(){
	$.ajax({
			url: 'script/loadTypeEvent.php?type='+$('#type option:selected').val()
			,dataType:'json'
		}).done(function(liste) {
			$("#typeEven").empty(); // remove old options
			$.each(liste, function(key, value) {
			  $("#typeEven").append($("<option></option>")
			     .attr("value", key).text(value));
			});	
		});
}

$('#type').change(function(){
		$.ajax({
			url: 'script/loadRessources.php?type='+$('#type option:selected').val()
			,dataType:'json'
		}).done(function(liste) {
			$("#id").empty(); // remove old options
			$.each(liste, function(key, value) {
			  $("#id").append($("<option></option>")
			     .attr("value", key).text(value));
			});	
		});
		ajaxLoadType();
		
		
});


</script>
		