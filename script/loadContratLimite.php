<?php

define('INC_FROM_CRON_SCRIPT', true);
require('../config.php');
require('../lib/ressource.lib.php');
global $conf;
$PDOdb=new TPDOdb;

$plagedeb = !empty($_REQUEST['plagedebut']) ? $_REQUEST['plagedebut'] : date("d/m/Y",time());
$plagefin = !empty($_REQUEST['plagefin']) ? $_REQUEST['plagefin'] : date("d/m/Y", time()+31532400);

$deb = dateToInt($plagedeb);
$fin = dateToInt($plagefin);

$TRetour = getContratLimit($PDOdb, $deb, $fin, $conf->entity);

//print_r($TRetour);
//echo json_encode($TRetour);
__out($TRetour);
exit();


