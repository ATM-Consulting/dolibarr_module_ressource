<?php
/* Copyright (C) 2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * or see http://www.gnu.org/
 */

/**
 *      \file       test/phpunit/UserTest.php
 *		\ingroup    test
 *      \brief      PHPUnit test
 *		\remarks	To run this script as CLI:  phpunit filename.php
 */




/*global $conf,$user,$langs,$db;
//inclusion de config des tests.
require('./config.php');
require('../lib/ressource.lib.php');
require('../class/ressource.class.php');
require('../class/evenement.class.php');
require('../class/contrat.class.php');*/


$ress = new TRH_Ressource;
$PDOdb = new TPDOdb;


/**
 * Class for PHPUnit tests
 *
 * @backupGlobals disabled
 * @backupStaticAttributes enabled
 * @remarks	backupGlobals must be disabled to have db,conf,user and lang not erased.
 */
class LibRessourceTest extends PHPUnit_Framework_TestCase
{
		
		
	public static function setUpBeforeClass()
    {
        global $PDOdb;	
        print "Début du test de Ressource.\n";
		
    }
 
    public static function tearDownAfterClass()
    {
    	global $PDOdb;
		print "\nFin du test de Ressource.\n";
    }
	
	
	
	
	
	
	public function testRessourcePrepareHead()
    {
    	global $ress, $PDOdb;
		$ress->code = 'voiture';
		$ret = ressourcePrepareHead($ress, 'type-ressource');
		$this->assertCount(4, $ret);
		$this->assertNull($ret[2]);
		
		$ress->code = 'telephone';
		$ret = ressourcePrepareHead($ress, 'type-ressource');
		$this->assertCount(4, $ret);
		
		$ret = ressourcePrepareHead($ress, 'contrat');
		$this->assertCount(2, $ret);
		
		$ret = ressourcePrepareHead($ress, 'evenement', $ress);
		$this->assertCount(2, $ret);
		
		$ret = ressourcePrepareHead($ress, 'import');
		$this->assertCount(1, $ret);
		
		$ret = ressourcePrepareHead($ress, 'nimportequoi');
		$this->assertCount(0, $ret);
		
		
		print __METHOD__."\n";
    }
	
	public function testGetLibelle()
    {
    	global $ress, $PDOdb;
		$ret = getLibelle($ress);
		$this->assertNotNull($ret);
		print __METHOD__."\n";
    }
	
	public function testgetTypeEvent()
    {
    	$events = array();
		
    	$events = getTypeEvent(3);
        $this->assertNotEmpty($events);
		print __METHOD__."\n";
    }
	
	public function testgetRessource()
    {
    	global $ress, $PDOdb, $conf ;
		$idVoit = getIdType('voiture');
		
    	$sqlReq="SELECT COUNT(rowid) AS 'nb' FROM ".MAIN_DB_PREFIX."rh_ressource 
    	WHERE entity IN (0,".$conf->entity.")";
		$PDOdb->Execute($sqlReq);
		if ($row = $PDOdb->Get_line()) {$nb = intval($row->nb);}
		$Tab = getRessource();
		$this->assertCount($nb+1 , $Tab);
		
		$sqlReq="SELECT COUNT(rowid) AS 'nb' FROM ".MAIN_DB_PREFIX."rh_ressource 
    	WHERE entity IN (0,".$conf->entity.") AND fk_rh_ressource_type=".$idVoit;
		$PDOdb->Execute($sqlReq);
		if ($row = $PDOdb->Get_line()) {$nb = intval($row->nb);}
		
		$Tab = getRessource($idVoit);
		$this->assertCount($nb+1 , $Tab);
		
		
		print __METHOD__."\n";
	}
	
	public function testgetIdType()
    {
		$id = getIdType('voiture');
		$this->assertGreaterThanOrEqual(0 , $id);
		
		$id = getIdType('inexistant');
		$this->assertFalse($id);
		print __METHOD__."\n";
	}
	
	public function testgetIDRessource()
    {
    	global $ress, $PDOdb, $conf ;
		$idVoit = getIdType('voiture');	
		$sqlReq="SELECT COUNT(rowid) AS 'nb' FROM ".MAIN_DB_PREFIX."rh_ressource 
    	WHERE entity IN (0,".$conf->entity.") AND fk_rh_ressource_type=".$idVoit;
		$PDOdb->Execute($sqlReq);
		if ($row = $PDOdb->Get_line()) {$nb = intval($row->nb);}
		$Tab = getIDRessource($PDOdb, $idVoit);
		$this->assertCount($nb , $Tab);
		
		print __METHOD__."\n";
	}
	
	public function testgetUsers()
    {
    	$Tab = getUsers(true, false);
		$this->assertArrayHasKey(0, $Tab);
		
		$Tab = getUsers(false, true);
		$this->assertArrayNotHasKey(0, $Tab);
		
		print __METHOD__."\n";
	}
	
	public function testgetGroups()
    {
    	$Tab = getGroups();
		$this->assertNotEmpty($Tab);
		
		print __METHOD__."\n";
	}
	
	public function testafficheOuPas()
    {
    	$ret = afficheOuPas('10', 'lol', 'iuhihi');
		$this->assertEquals('', $ret);
		
		$ret = afficheOuPas('10', 'lol', 'lol');
		$this->assertEquals('00:10', $ret);
		
		print __METHOD__."\n";
	}
	
	public function teststringTous()
    {
    	$ret = stringTous('ok', 'notall');
		$this->assertEquals('ok', $ret);
		
		$ret = stringTous('ok', 'all');
		$this->assertEquals('Tous', $ret);
		
		print __METHOD__."\n";
	}
	
	public function testintToString()
    {
    	$ret = intToString();
		$this->assertEquals('00:00', $ret);
		
		$ret = intToString(5);
		$this->assertEquals('00:05', $ret);
		
		$ret = intToString(650);
		$this->assertEquals('10:50', $ret);
		
		$ret = intToString(0);
		$this->assertEquals('00:00', $ret);
		
		print __METHOD__."\n";
	}
	
	public function testintToHour()
    {
		$ret = intToHour(5);
		$this->assertEquals('00', $ret);
		
		$ret = intToHour(650);
		$this->assertEquals('10', $ret);
		
		$ret = intToHour(65);
		$this->assertEquals('01', $ret);
		
		print __METHOD__."\n";
	}

	public function testintToMinute()
    {
    	global $ress, $PDOdb, $conf ;
		
		$ret = intToMinute(65);
		$this->assertEquals('05', $ret);
		
		$ret = intToMinute(0);
		$this->assertEquals('00', $ret);
		
		$ret = intToMinute(5);
		$this->assertEquals('05', $ret);
		
		print __METHOD__."\n";
	}


	public function testtimeToInt()
    {
		$ret = timeToInt(5,5);
		$this->assertEquals(305, $ret);
		
		print __METHOD__."\n";
	}
	
	public function testLoad_limites_telephone()
    {
    	global $ress, $PDOdb, $conf ;
		$TGroups = getGroups();
		$TRowidUser = array();
		$sql="SELECT rowid, lastname, firstname, login FROM ".MAIN_DB_PREFIX."user WHERE entity IN (0,".$conf->entity.")";
		$PDOdb->Execute($sql);
		while($PDOdb->Get_line()) {
			$TRowidUser[] = $PDOdb->Get_field('rowid');
		}
		$ret = load_limites_telephone($PDOdb, $TGroups, $TRowidUser);
		
		print __METHOD__."\n";
	}
	
	public function testsend_mail_resources()
    {
    	global $ress, $PDOdb, $conf ;
		
		$ret = send_mail_resources('titre', 'message');
		$this->assertEquals(1, $ret);
		
		print __METHOD__."\n";
	}
	
	
	public function testressourceIsEmpruntee()
    {
    	global $ress, $PDOdb, $conf ;
		$jour = date("Y-m-d", time() );
		$sql="SELECT rowid, fk_user, fk_rh_ressource FROM ".MAIN_DB_PREFIX."rh_evenement 
		WHERE type='emprunt' 
		AND date_debut<='".$jour."' AND date_fin >= '".$jour."' ";
		$PDOdb->Execute($sql);
		while($PDOdb->Get_line()) {
			$idRessource = $PDOdb->Get_field('fk_rh_ressource');
			$expectedUser = $PDOdb->Get_field('fk_user');
		}
		$ret = ressourceIsEmpruntee($PDOdb, $idRessource, $jour);
		$this->assertEquals($expectedUser, $ret);
		
		$ret = ressourceIsEmpruntee($PDOdb, 0, 0);
		$this->assertEquals(0, $ret);
		
		print __METHOD__."\n";
	}
	
	
	
}
