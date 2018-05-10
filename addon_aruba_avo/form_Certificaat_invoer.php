<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.   (http://www.aim4me.info)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
// MUST load the classes before session_start()!
  require_once("inputlib/inputclasses.php");
  require_once("schooladminconstants.php");
  session_start();
  //error_reporting(E_STRICT);
  inputclassbase::dblogon($databaseserver,$datausername,$datapassword,$databasename);
  $userlink = inputclassbase::$dbconnection;
  if(isset($_POST['fieldid']))
  {
    if($_POST['fieldid'] == "sefield" || $_POST['fieldid'] == "exfield")
	  $_POST[$_POST['fieldid']] = str_replace(',','.',$_POST[$_POST['fieldid']]);
    // Let the library page handle the data
    include("inputlib/procinput.php");
    //echo("OK");
	exit;
  }
  if(isset($_GET['delete']))
  {
    mysql_query("DELETE FROM excertdata WHERE excertid=". $_GET['delete'],$userlink);
  }

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
 
  // Subject translation tables
  $offsubjects = array(1 => "Ne","En","Sp","Wi-A","Wi-B","Na","Sk","Bio","Ec","M&O","Ak","Gs","CKV","Pa","Inf");
  $altsubjects = array("Ne"=>1,"En"=>2,"Sp"=>3,"Wi-A"=>4,"Wi-B"=>5,"Na"=>6,"Sk"=>7,"Bio"=>8,"Ec"=>9,"M&O"=>10,"Ak"=>11,"Gs"=>12,"CKV"=>13,
                       "ne"=>1,"en"=>2,"sp"=>3,"wiA"=>4,"wiB"=>5,"na"=>6,"sk"=>7,"bio"=>8,"ec"=>9,"m&o"=>10,"ak"=>11,"gs"=>12,"ckv"=>13,"pa"=>14,"inf"=>15);


  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];
  if(isset($HTTP_POST_VARS['NewGroup']) && SA_verifyGroupAccess($uid,$HTTP_POST_VARS['NewGroup']))
    $CurrentGroup = $HTTP_POST_VARS['NewGroup'];
  if(isset($HTTP_GET_VARS['NewGroup']) && SA_verifyGroupAccess($uid,$HTTP_GET_VARS['NewGroup']))
    $CurrentGroup = $HTTP_GET_VARS['NewGroup'];
  // Store the new group or future pages
  $_SESSION['CurrentGroup']=$CurrentGroup;
  
  $uid = intval($uid);
  
  // This function is based on tables that is created as needed. So now we create it if it does not exist.
  $sqlquery = "CREATE TABLE IF NOT EXISTS `excertdata` (
    `excertid` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `sid` INTEGER(11) DEFAULT NULL,
    `mid` INTEGER(11) DEFAULT NULL,
	`year` TEXT,
	`seresult` DOUBLE DEFAULT NULL,
	`exresult` DOUBLE DEFAULT NULL,
	`endresult` DOUBLE DEFAULT NULL,
    `lastmodifiedat` TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`excertid`),
    UNIQUE KEY `sidmid` (`sid`, `mid`)
    ) ENGINE=InnoDB;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());
 
  // Get all exisiting records in an array
  $excertdata = SA_loadquery("SELECT excertdata.*,CONCAT(lastname,', ',firstname) AS studname,shortname FROM excertdata LEFT JOIN student USING(sid) 
                               LEFT JOIN subject USING(mid) LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid)
							   WHERE active=1 AND groupname LIKE 'Exam%' GROUP BY sid,mid ORDER BY lastname,firstname,year");

  // First part of the page
  echo("<html><head><title>Certificaat gegevens invoer</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>Certifcaten</font><p>");
  echo("<a href=# onClick='window.close();'>" . $dtext['back_teach_page'] . "</a><br>");

  // Create the heading row for the table
  echo("<table border=1 cellpadding=0>");
  echo("<tr><td><center>Studentnaam</td><td><center>Vak</td><td>Jaar<center></td><td><center>SE cijfer</td>
        <td><center>CSE cijfer</td><td>Eindcijfer<center></td><td>&nbsp;</td></tr>");
  // Query definitions for in fields.
  $studqry = "SELECT '' AS tekst,'' AS id UNION SELECT CONCAT(lastname,', ',firstname) AS tekst, sid AS id FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) WHERE active=1 AND groupname LIKE 'Exam%' ORDER BY tekst";
  $subjqry = "SELECT '' AS tekst,'' AS id UNION SELECT shortname AS tekst,mid AS id FROM subject ORDER BY tekst";
  // Build the year query, last 10 years.
  $yearqry = "SELECT '' AS tekst,'' AS id";
  for($i=0;$i<=10;$i++)
    $yearqry .= " UNION SELECT ". (date("Y") - $i). " AS id, ". (date("Y") - $i). " AS tekst";

  // Create a row for entry of a new record
  echo("<tr><td>");
  $studfield = new inputclass_listfield("studfield",$studqry,$userlink,"sid","excertdata",0,"excertid","","form_Certificaat_invoer.php");
  $studfield->echo_html();
  echo("</td><td>");
  $subjfield = new inputclass_listfield("subjfield",$subjqry,$userlink,"mid","excertdata",0,"excertid","","form_Certificaat_invoer.php");
  $subjfield->echo_html();
  echo("</td><td>");
  $yearfield = new inputclass_listfield("yearfield",$yearqry,$userlink,"year","excertdata",0,"excertid","","form_Certificaat_invoer.php");
  $yearfield->echo_html();
  echo("</td><td>");
  $sefield = new inputclass_textfield("sefield",4,$userlink,"seresult","excertdata",0,"excertid","","form_Certificaat_invoer.php");
  $sefield->echo_html();
  echo("</td><td>");
  $exfield = new inputclass_textfield("exfield",4,$userlink,"exresult","excertdata",0,"excertid","","form_Certificaat_invoer.php");
  $exfield->echo_html();
  echo("</td><td>");
  $endfield = new inputclass_textfield("endfield",1,$userlink,"endresult","excertdata",0,"excertid","","form_Certificaat_invoer.php");
  $endfield->echo_html();
  echo("</td><td><img src='PNG/action_add.png' border=0 onclick=getpage('form_Certificaat_invoer.php');></td></tr>");
  
  
  // Create a row in the table for each record
  if(isset($excertdata['excertid']))
  {
    foreach($excertdata['excertid'] AS $ecix => $ecid)
	{
	  echo("<tr><td>". $excertdata['studname'][$ecix]. "</td><td>". $excertdata['shortname'][$ecix]. "</td><td>". 
	        $excertdata['year'][$ecix]. "</td><td>". $excertdata['seresult'][$ecix]. "</td><td>". 
			$excertdata['exresult'][$ecix]. "</td><td>". $excertdata['endresult'][$ecix]. "</td><td>
			<img src='PNG/action_delete.png' onclick='getpage(\"form_Certificaat_invoer.php?delete=". $ecid. "\");'></td></tr>");
	}
  }
 
  echo("</table>");
  echo '<a href=# onClick="window.close();">';
  echo $dtext['back_teach_page'];
  //echo "</a><BR><BR><a href='" .$_SERVER['PHP_SELF']. "?extractfromprevyear=1'>Haal vrijstellingen en CKV resultaat uit resultaten vorig jaar</a>";
  // Script for delayed reload
?>
<SCRIPT>
  function getpage(url)
  {
    setTimeout(loadpage(url),1000);
  }
  function loadpage(url)
  {
    window.location.href=url;
  }
</SCRIPT>
<?
  // close the page
  echo("</html>");
?>
