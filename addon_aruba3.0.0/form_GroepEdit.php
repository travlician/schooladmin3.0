<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2013 Aim4me N.V.   (http://www.aim4me.info)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();
  require_once("inputlib/inputclasses.php");
  require_once("group.php");
  require_once("student.php");

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  // Avoid sorting on students messed up
  unset($_SESSION['ssortertable']);
  inputclassbase::dbconnect($userlink);
  // Setup table for monitoring actions
  mysql_query("CREATE TABLE IF NOT EXISTS `groupedittrace` (
  `tid` INTEGER(11) UNSIGNED NOT NULL,
  `sid` INTEGER(11),
  `action` VARCHAR(10),
  `newgid` INTEGER(11),
  `oldgid` INTEGER(11),
  `modtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  ) ENGINE=InnoDB;", $userlink);
  // END OF
  if(!isset($_SESSION['leftgroup']))
    $_SESSION['leftgroup'] = 0;
  if(!isset($_SESSION['rightgroup']))
  {
    $mygroup = new group();
	$mygroup->load_current();
	$_SESSION['rightgroup'] = $mygroup->get_id();
  }
  if(isset($_POST['leftgr']))
    $_SESSION['leftgroup'] = $_POST['leftgr'];
  if(isset($_POST['rightgr']))
    $_SESSION['rightgroup'] = $_POST['rightgr'];
	
  if(isset($_POST['func']))
  { // List action detected
    if($_POST['func'] == 1)
	{ // Exchange selected items in lists
	  if(isset($_POST['lstudlist']))
	  foreach($_POST['lstudlist'] AS $asid)
	  {
	    if($_SESSION['leftgroup'] != 0 && $_SESSION['rightgroup'] != 0)
		{ // no "zero" lists active
	      mysql_query("UPDATE sgrouplink SET gid=". $_SESSION['rightgroup']. " WHERE sid=". $asid. " AND gid=". $_SESSION['leftgroup'], $userlink);
		  echo(mysql_error($userlink));
		  mysql_query("INSERT INTO groupedittrace VALUES(". $_SESSION['uid']. ",". $asid. ",'MOVE',". $_SESSION['rightgroup']. ",". $_SESSION['leftgroup']. ",NOW())", $userlink);
	    }
	    else if($_SESSION['leftgroup'] == 0 && $_SESSION['rightgroup'] != 0)
	    {
	      mysql_query("INSERT INTO sgrouplink (sid,gid) VALUES(". $asid. ",". $_SESSION['rightgroup']. ")", $userlink);
		  echo(mysql_error($userlink));
		  mysql_query("INSERT INTO groupedittrace VALUES(". $_SESSION['uid']. ",". $asid. ",'ADD',". $_SESSION['rightgroup']. ",NULL,NOW())", $userlink);
	    }
	    else if($_SESSION['leftgroup'] != 0 && $_SESSION['rightgroup'] == 0)
	    {
	      mysql_query("DELETE FROM sgrouplink WHERE sid=". $asid, $userlink);
		  echo(mysql_error($userlink));
		  mysql_query("INSERT INTO groupedittrace VALUES(". $_SESSION['uid']. ",". $asid. ",'DELALL',NULL,NULL,NOW())", $userlink);
	    }
	  }
	  if(isset($_POST['rstudlist']))
	  foreach($_POST['rstudlist'] AS $asid)
	  {
	    if($_SESSION['leftgroup'] != 0 && $_SESSION['rightgroup'] != 0)
		{ // no "zero" lists active
	      mysql_query("UPDATE sgrouplink SET gid=". $_SESSION['leftgroup']. " WHERE sid=". $asid. " AND gid=". $_SESSION['rightgroup'], $userlink);
		  echo(mysql_error($userlink));
		  mysql_query("INSERT INTO groupedittrace VALUES(". $_SESSION['uid']. ",". $asid. ",'MOVE',". $_SESSION['leftgroup']. ",". $_SESSION['rightgroup']. ",NOW())", $userlink);
	    }
	    else if($_SESSION['rightgroup'] == 0 && $_SESSION['leftgroup'] != 0)
	    {
	      mysql_query("INSERT INTO sgrouplink (sid,gid) VALUES(". $asid. ",". $_SESSION['leftgroup']. ")", $userlink);
		  echo(mysql_error($userlink));
		  mysql_query("INSERT INTO groupedittrace VALUES(". $_SESSION['uid']. ",". $asid. ",'ADD',". $_SESSION['leftgroup']. ",NULL,NOW())", $userlink);
	    }
	    else if($_SESSION['rightgroup'] != 0 && $_SESSION['leftgroup'] == 0)
	    {
	      mysql_query("DELETE FROM sgrouplink WHERE sid=". $asid, $userlink);
		  echo(mysql_error($userlink));
		  mysql_query("INSERT INTO groupedittrace VALUES(". $_SESSION['uid']. ",". $asid. ",'DELALL',NULL,NULL,NOW())", $userlink);
	    }
	  }
	}
	else if ($_POST['func'] == 2)
	{ // multiple to single list move
	  if(isset($_POST['lstudlist']))
	  foreach($_POST['lstudlist'] AS $asid)
	  {
	    if($_SESSION['leftgroup'] != 0 && $_SESSION['rightgroup'] != 0)
		{ // no "zero" lists active
	      mysql_query("DELETE FROM sgrouplink WHERE sid=". $asid, $userlink);
		  echo(mysql_error($userlink));		  
		  mysql_query("INSERT INTO groupedittrace VALUES(". $_SESSION['uid']. ",". $asid. ",'DELALL',NULL,NULL,NOW())", $userlink);
	      mysql_query("INSERT INTO sgrouplink (sid,gid) VALUES(". $asid. ",". $_SESSION['rightgroup']. ")", $userlink);
		  echo(mysql_error($userlink));
		  mysql_query("INSERT INTO groupedittrace VALUES(". $_SESSION['uid']. ",". $asid. ",'ADD',". $_SESSION['rightgroup']. ",NULL,NOW())", $userlink);
	    }
	    else if($_SESSION['leftgroup'] == 0 && $_SESSION['rightgroup'] != 0)
	    {
	      mysql_query("INSERT INTO sgrouplink (sid,gid) VALUES(". $asid. ",". $_SESSION['rightgroup']. ")", $userlink);
		  echo(mysql_error($userlink));
		  mysql_query("INSERT INTO groupedittrace VALUES(". $_SESSION['uid']. ",". $asid. ",'ADD',". $_SESSION['rightgroup']. ",NULL,NOW())", $userlink);
	    }
	    else if($_SESSION['leftgroup'] != 0 && $_SESSION['rightgroup'] == 0)
	    {
	      mysql_query("DELETE FROM sgrouplink WHERE sid=". $asid, $userlink);
		  echo(mysql_error($userlink));
		  mysql_query("INSERT INTO groupedittrace VALUES(". $_SESSION['uid']. ",". $asid. ",'DELALL',NULL,NULL,NOW())", $userlink);
	    }
	  }
	
	}
    else if($_POST['func'] == 3)
	{ // copy selected items in lists
	  if(isset($_POST['lstudlist']))
	  foreach($_POST['lstudlist'] AS $asid)
	  {
	    if($_SESSION['leftgroup'] != 0 && $_SESSION['rightgroup'] != 0)
		{ // no "zero" lists active
	      mysql_query("REPLACE INTO sgrouplink (gid,sid) VALUES(". $_SESSION['rightgroup']. ",". $asid. ")", $userlink);
		  echo(mysql_error($userlink));
		  mysql_query("INSERT INTO groupedittrace VALUES(". $_SESSION['uid']. ",". $asid. ",'ADDREP',". $_SESSION['rightgroup']. ",NULL,NOW())", $userlink);
	    }
	  }
	  if(isset($_POST['rstudlist']))
	  foreach($_POST['rstudlist'] AS $asid)
	  {
	    if($_SESSION['leftgroup'] != 0 && $_SESSION['rightgroup'] != 0)
		{ // no "zero" lists active
	      mysql_query("REPLACE INTO sgrouplink (gid,sid) VALUES(". $_SESSION['leftgroup']. ",". $asid. ")", $userlink);
		  echo(mysql_error($userlink));
		  mysql_query("INSERT INTO groupedittrace VALUES(". $_SESSION['uid']. ",". $asid. ",'ADDREP',". $_SESSION['leftgroup']. ",NULL,NOW())", $userlink);
	    }
	  }
	}
 }
  
  echo("<html><head><title>Group editor</title>");
  echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
  echo("</head><body link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style_groupedit.css" title="style1">';
   
  echo '<p class=txtmidden><a href=# onClick="window.close();">';
  echo $dtext['back_teach_page'];
  echo '</a></p>';
  
  echo("<table><tr><th><form name=leftgroupedit id=leftgroupedit method=POST action=". $_SERVER['PHP_SELF']. ">");
  echo("<div style=border: 2px solid blue;>
	<SELECT name=leftgr onChange=leftgroupedit.submit() border: 1px solid #AAA;><OPTION value=0>-</OPTION>");
	$groups = group::group_list();
  foreach($groups AS $lgrp)
  {
    echo("<OPTION value=". $lgrp->get_id(). ($lgrp->get_id() == $_SESSION['leftgroup'] ? " selected" : ""). ">". $lgrp->get_groupname(). "</OPTION>");
  }
  echo("</SELECT></FORM></th><th>&nbsp</th><th><form name=rightgroupedit id=rightgroupedit method=POST action=". $_SERVER['PHP_SELF']. "><SELECT name=rightgr onChange=rightgroupedit.submit()><OPTION value=0>-</OPTION>");
  foreach($groups AS $rgrp)
  {
    echo("<OPTION value=". $rgrp->get_id(). ($rgrp->get_id() == $_SESSION['rightgroup'] ? " selected" : ""). ">". $rgrp->get_groupname(). "</OPTION>");
  }
  echo("</SELECT></div></FORM></th></tr>");
  
  echo("<tr><td><FORM method=POST name=studlist id=studlist action='". $_SERVER['PHP_SELF']. "'><input type=hidden name=func value=0>");
  echo("<SELECT multiple='multiple' name=lstudlist[] size=30>");
  if($_SESSION['leftgroup'] != 0)
  {
    $lstuds = student::student_list(new group($_SESSION['leftgroup']));
	if(isset($lstuds))
	foreach($lstuds AS $lstu)
	{
	  echo("<OPTION value=". $lstu->get_id(). ">". $lstu->get_lastname(). ", ". $lstu->get_firstname(). "</option>");
	}
  }
  else
  {
    $emptylistq = inputclassbase::load_query("SELECT sid FROM student LEFT JOIN sgrouplink USING(sid) WHERE gid IS NULL ORDER BY lastname,firstname");
	if(isset($emptylistq['sid']))
	{
	  foreach($emptylistq['sid'] AS $asid)
	  {
	    $lstu = new student($asid);
	    echo("<OPTION value=". $lstu->get_id(). ">". $lstu->get_lastname(). ", ". $lstu->get_firstname(). "</option>");
	  }
	}
  }
  echo("</SELECT></td><td class=breedtekolom><img src='PNG/listexchange.png' title='Verwissel' width=200px onClick='listexchange()'>");
  if($_SESSION['leftgroup'] != 0 && $_SESSION['rightgroup'] != 0) // Allow copy only if none of the lists is the "unassigned" list
    echo("<BR><BR><img src='PNG/listcopy.png' title='Kopie maken  van linkerlijst naar rechterlijst' width=200px onClick='listcopy()'>");
  echo("<BR><BR><img src='PNG/L2Rlistcopy.png' title='Uit alle groepen verwijderen en plaatsen naar rechterlijst' width=200px onClick='multilistmove()'></td>");
  echo("<td><SELECT multiple='multiple' name=rstudlist[] size=30>");
  if($_SESSION['rightgroup'] != 0)
  {
    $lstuds = student::student_list(new group($_SESSION['rightgroup']));
	if(isset($lstuds))
	foreach($lstuds AS $lstu)
	{
	  echo("<OPTION value=". $lstu->get_id(). ">". $lstu->get_lastname(). ", ". $lstu->get_firstname(). "</option>");
	}
  }
  else
  {
    $emptylistq = inputclassbase::load_query("SELECT sid FROM student LEFT JOIN sgrouplink USING(sid) WHERE gid IS NULL ORDER BY lastname,firstname");
	if(isset($emptylistq['sid']))
	{
	  foreach($emptylistq['sid'] AS $asid)
	  {
	    $lstu = new student($asid);
	    echo("<OPTION value=". $lstu->get_id(). ">". $lstu->get_lastname(). ", ". $lstu->get_firstname(). "</option>");
	  }
	}
  }
  echo("</SELECT></FORM></td></tr>");
  
  
  
  echo("</table>");
  
  // Dummy form for delayed refresh
  echo("<form name=delayedrefresh id=delayedrefresh method=post action=form_Weekplanning.php></form>");
  // And it's JavaScript
  echo("<SCRIPT> function delayrefresh() { setTimeout(\"document.getElementById('delayedrefresh').submit();\",500); } </SCRIPT>");

  // Scripts for functions
?>
<SCRIPT>
  function listexchange()
  {
    var studlist;
    studlist = document.getElementById("studlist");
    studlist.func.value=1;
	studlist.submit();
  }
  function listcopy()
  {
    var studlist;
    studlist = document.getElementById("studlist");
    studlist.func.value=3;
	studlist.submit();
  }
  function multilistmove()
  {
    var studlist;
    studlist = document.getElementById("studlist");
    studlist.func.value=2;
	studlist.submit();
  }
</SCRIPT>
<?
/*
  // Debugging
  foreach($_POST AS $key => $value)
  {
    if($key == 'lstudlist' || $key == 'rstudlist')
	{
	  echo("<BR>POST ". $key. "[");
	  foreach($value AS $entry)
	    echo($entry. ",");
	  echo("]");
	}
	else
      echo("<BR>POST ". $key. " = ". $value);
  } */
  // close the page
  echo("</html>");
?>
