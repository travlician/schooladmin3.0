<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 3.0                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014  Aim4Me N.V.  (http://www.aim4me.info)       |
// +----------------------------------------------------------------------+
// | This program is free software.  You can redistribute in and/or       |
// | modify it under the terms of the GNU General Public License Version  |
// | 2 as published by the Free Software Foundation.                      |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY, without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program;  If not, write to the Free Software         |
// | Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.            |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert  -  travlician@bigfoot.com                |
// +----------------------------------------------------------------------+
//
session_start();

//$login_qualify = 'A';
require_once("schooladminfunctions.php");
require_once("inputlib/inputclasses.php");

	// Link inputlib with database connection
	inputclassbase::dbconnect($userlink);

  // This function is based on tables that is created as needed. So now we create it if it does not exist.
  $sqlquery = "CREATE TABLE IF NOT EXISTS `email_plugin_config` (
    `aspect` VARCHAR(20),
    `cfid` INTEGER(11) DEFAULT NULL,
    `cdata` TEXT DEFAULT NULL,
    UNIQUE KEY `aspectid` (`cfid`, `aspect`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());
	
	
  // First part of the page
  echo("<html><head><title>Ouders en lln email configuratie</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>E-mail configuratie ouders en leerlingen</font><p>");
  echo("<a href=# onClick='window.close();'>" . $dtext['back_teach_page'] . "</a><br><div style='text-align: left;'>");
	
	//Find out which fields contain e-mail addresses
	$stfieldsqr = inputclassbase::load_query("SELECT table_name,label from student_details ORDER BY seq_no");
	if(isset($stfieldsqr['table_name']))
		foreach($stfieldsqr['table_name'] AS $sdix => $tbname)
		{
			if(substr($tbname,0,1) != "*")
			{
				$dataqr = inputclassbase::load_query("SELECT count(sid) AS fcnt FROM `". $tbname. "` WHERE data LIKE '%@%.%'");
				if(isset($dataqr['fcnt']) && $dataqr['fcnt'][0] > 0)
					$tablecandidates[$tbname] = $stfieldsqr['label'][$sdix];
			}
		}

	if(isset($tablecandidates))
	{
		echo("Velden met bestemming e-mail adres: ");
		$tbid = 1;
		$tbselq = "SELECT '' AS id, '' AS tekst";
		foreach($tablecandidates AS $tbname => $tblabel)
			$tbselq .= " UNION SELECT '". $tbname. "','". $tblabel. "'";
		foreach($tablecandidates AS $tbname => $tblabel)
		{
			$mailselfld[$tbid] = new inputclass_listfield("mailfldsel". $tbid,$tbselq,$userlink,"cdata","email_plugin_config",1,"aspect","","datahandler.php");
			$mailselfld[$tbid]->set_extrakey("cfid",$tbid);
			$mailselfld[$tbid]->echo_html();
			echo(" ");
			$tbid++;
		}
		// Now determine if results should be reported
		mysql_query("INSERT IGNORE INTO email_plugin_config (aspect,cfid,cdata) VALUES(2,0,0)",$userlink);
		echo("<BR><BR>Stuur bericht bij toevoegen of veranderen cijfers: ");
		$sendresultfld = new inputclass_checkbox("maildfldresult",NULL,$userlink,"cdata","email_plugin_config",2,"aspect","","datahandler.php");
		$sendresultfld->set_extrakey('cfid',0);
		$sendresultfld->echo_html();
		echo("<BR>Tekst voor resultaten bericht:");
		$txtresultfld = new inputclass_textarea("mailtfldresult","80,*",$userlink,"cdata","email_plugin_config",2,"aspect","","datahandler.php");
		$txtresultfld->set_extrakey('cfid',9990);
		$txtresultfld->echo_html();
		echo("<BR>Tekst voor resultaten link in bericht:");
		$linkresultfld = new inputclass_textfield("maillfldresult",80,$userlink,"cdata","email_plugin_config",2,"aspect","","datahandler.php");
		$linkresultfld->set_extrakey('cfid',9991);
		$linkresultfld->echo_html();
		
		// Now the report categories for which messages are to be sent
		mysql_query("INSERT IGNORE INTO email_plugin_config (aspect,cfid,cdata) VALUES(3,0,0)",$userlink);
		echo("<BR><BR>Stuur bericht bij toevoegen of veranderen rapportage in categorie: ");
		echo("<BR><SPAN style='width: 200px; display: inline-block;'>Zonder categorie</span>");
		$sendresultfld = new inputclass_checkbox("maildfldrcat0",NULL,$userlink,"cdata","email_plugin_config",3,"aspect","","datahandler.php");
		$sendresultfld->set_extrakey('cfid',0);
		$sendresultfld->echo_html();
		// Now the other categories
		$repcatqr = inputclassbase::load_query("SELECT * FROM reportcats");
		if(isset($repcatqr['rcid']))
			foreach($repcatqr['rcid'] AS $rcix => $rcid)
			{
				mysql_query("INSERT IGNORE INTO email_plugin_config (aspect,cfid,cdata) VALUES(3,". $rcid. ",0)",$userlink);
				echo("<BR><SPAN style='width: 200px; display: inline-block;'>". $repcatqr['name'][$rcix]. "</span>");
				$sendresultfld = new inputclass_checkbox("maildfldrcat". $rcid,NULL,$userlink,"cdata","email_plugin_config",3,"aspect","","datahandler.php");
				$sendresultfld->set_extrakey('cfid',$rcid);
				$sendresultfld->echo_html();				
			}
		echo("<BR>Tekst voor rapportage bericht:");
		$txtresultfld = new inputclass_textarea("mailrtfldresult","80,*",$userlink,"cdata","email_plugin_config",3,"aspect","","datahandler.php");
		$txtresultfld->set_extrakey('cfid',9990);
		$txtresultfld->echo_html();
		echo("<BR>Tekst voor rapportage link in bericht:");
		$linkresultfld = new inputclass_textfield("mailrlfldresult",80,$userlink,"cdata","email_plugin_config",3,"aspect","","datahandler.php");
		$linkresultfld->set_extrakey('cfid',9991);
		$linkresultfld->echo_html();
		// Now the absense categories for which messages are to be sent
		echo("<BR><BR>Stuur bericht bij toevoegen of veranderen absentie in categorie: ");
		$abscatqr = inputclassbase::load_query("SELECT * FROM absencecats");
		if(isset($abscatqr['acid']))
			foreach($abscatqr['acid'] AS $acix => $acid)
			{
				mysql_query("INSERT IGNORE INTO email_plugin_config (aspect,cfid,cdata) VALUES(4,". $acid. ",0)",$userlink);
				echo("<BR><SPAN style='width: 200px; display: inline-block;'>". $abscatqr['name'][$acix]. "</span>");
				$sendresultfld = new inputclass_checkbox("maildfldacat". $acid,NULL,$userlink,"cdata","email_plugin_config",4,"aspect","","datahandler.php");
				$sendresultfld->set_extrakey('cfid',$acid);
				$sendresultfld->echo_html();				
			}
		echo("<BR>Tekst voor absentie bericht:");
		$txtresultfld = new inputclass_textarea("mailatfldresult","80,*",$userlink,"cdata","email_plugin_config",4,"aspect","","datahandler.php");
		$txtresultfld->set_extrakey('cfid',9990);
		$txtresultfld->echo_html();
		echo("<BR>Tekst voor absentie link in bericht:");
		$linkresultfld = new inputclass_textfield("mailalfldresult",80,$userlink,"cdata","email_plugin_config",4,"aspect","","datahandler.php");
		$linkresultfld->set_extrakey('cfid',9991);
		$linkresultfld->echo_html();
		
	}
	else
		echo("Er zijn geen velden gevonden met e-mail adressen.");
	
	echo("</div></body></html>");
?>


