<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011 Aim4me N.V.   (http://www.aim4me.info)	      |
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
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();
  require_once("inputlib/inputclasses.php");

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  require_once("teacher.php");
  require_once("group.php");
  inputclassbase::DBconnect($userlink);
  $me = new teacher();
  $me->load_current();
  
  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  
  $daynames = array(1 => "Maandag","Dinsdag","Woensdag","Donderdag","Vrijdag");
  
  // This function is based on tables that is created as needed. So now we create it if it does not exist.
  $sqlquery = "CREATE TABLE IF NOT EXISTS `bo_rooster` (
  `rid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gid` int(11) unsigned DEFAULT NULL,
  `mid` int(11) unsigned DEFAULT NULL,
  `weekday` int(11) unsigned DEFAULT NULL,
  `starttime` time DEFAULT NULL,
  `endtime` time DEFAULT NULL,
  PRIMARY KEY (`rid`),
  UNIQUE KEY `rid` (`rid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());
  // There may be entries that are empty and need to be removes
  mysql_query("DELETE FROM bo_rooster WHERE starttime IS NULL", $userlink);
  
  // First part of the page
  echo("<html><head><title>Rooster instelling</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style_bor.css" title="style1">';
  echo("<p class=heading>Rooster instellingen</p>");
  echo("<p class=returnlink><a href=# onClick='window.close();'>" . $dtext['back_teach_page'] . "</a></p>");
  // Get the groups we need to edit or display
  $grps = group::group_list();
  // remove groups that start with "W" or "P"
  foreach($grps AS $gix => $grp)
  {
    if(substr($grp->get_groupname() ,0,1) == "W" || substr($grp->get_groupname() ,0,1) == "X") // X used to be p to filter out prisma group
	  unset($grps[$gix]);
  }

  // Create the heading row / table
  echo("<table border=1 cellpadding=0>");
  // Create the top row
  echo("<tr><TH class=day ROWSPAN=3>Dag</TH><TH>&nbsp;</TH>");
  foreach($grps AS $grp)
    echo("<TH>Klas: ". $grp->get_groupname(). "</TH><TH>&nbsp</TH>");
  echo("</TR><TR><TH>&nbsp;</TH>");
  foreach($grps AS $grp)
  {
    $gt = $grp->get_mentor();
	echo("<TH>Lkr.: ". substr($gt->get_teacher_detail("*teacher.firstname"),0,1). ".". substr($gt->get_teacher_detail("*teacher.lastname"),0,1). ".</TH><TH class=subhdr ROWSPAN=2>Vak</TH>");
  }
  echo("</TR><TR><TH>Les</TH>");
  foreach($grps AS $grp)
    echo("<TH>Lestijden</TH>");
  echo("</TR>");
  for($wkday = 1; $wkday <= 5; $wkday++)
  {
    echo("<TR class=fatline><TD class=day ROWSPAN=10>". $daynames[$wkday]. "</TD>");
	for($rs = 1; $rs <= 10; $rs++)
	{
	  if($rs != 1)
	    echo("<TR>");
	  echo("<TD class=slotnr>". $rs. "</TD>");
	  foreach($grps AS $grp)
	  {
		$ri = new roosteritem($grp->get_id(),$wkday,$rs);
	    echo("<TD class=ltimes>");
		$ri->put_starttime($me->has_role("admin"));
		$ri->put_endtime($me->has_role("admin"));
		echo("</TD><TD>");
		$ri->put_subject($me->has_role("admin"));
		echo("</TD>");		
	  }
	  echo("</TR>");
	}
  }
  
   echo("</table>");
 
  // close the page
  echo("</html>");
  
  class roosteritem
  {
    static protected $newindex = -1;
	protected $rid; // positive rid indicates existing entry (that can be modified), negative index a new entry and 0 a pauze entry
	protected $gid,$weekday;
    public function __construct($gid,$weekday,$slot)
	{ 
	  $this->gid = $gid;
	  $this->weekday = $weekday;
	  // See if there is an existing entry for this item
	  $rids4me = inputclassbase::load_query("SELECT * FROM bo_rooster WHERE gid=". $gid. " AND weekday=". $weekday. " ORDER BY starttime");
	  if(isset($rids4me['starttime']))
	  {
	    $rslot = 1;
		$prevend = "00:00:00";
	    foreach($rids4me['rid'] AS $rix => $rid)
		{
		  if($prevend != "00:00:00" && $rids4me['starttime'][$rix] != $prevend)
		  { // Insert a pauze slot first
		    $rdata[$rslot++] = 0;
		  }
		  $rdata[$rslot++] = $rid; // Add our entry as a time slot
		  $prevend = $rids4me['endtime'][$rix];
		}
	  }
	  if(isset($rdata[$slot]))
	  {
	    $this->rid = $rdata[$slot];
	  }
	  else // New entry, use negative number
	    $this->rid = roosteritem::$newindex--;
	}
	
	public function put_starttime($mayedit = TRUE)
	{
	  if($this->rid == 0)
	  {
	    echo("P");
		return;
	  }
	  $stfield = new inputclass_textfield("st". $this->rid,5,NULL,"starttime","bo_rooster",$this->rid,"rid",NULL,"datahandler.php");
	  if($this->rid < 0)
	  { // Add fields as defaults
	    $stfield->set_extrafield("gid", $this->gid);
		$stfield->set_extrafield("weekday", $this->weekday);
	  }
	  if($mayedit)
	    $stfield->echo_html();
	  else
	    echo(substr($stfield->__toString(),0,5));
	  echo("-");
	}
	
	public function put_endtime($mayedit = TRUE)
	{
	  if($this->rid == 0)
	  {
		return;
	  }
	  $stfield = new inputclass_textfield("et". $this->rid,5,NULL,"endtime","bo_rooster",$this->rid,"rid",NULL,"datahandler.php");
	  if($mayedit)
	    $stfield->echo_html();
	  else
	    echo(substr($stfield->__toString(),0,5));
	}
	
	public function put_subject($mayedit = TRUE)
	{
	  if($this->rid == 0)
	  {
	    echo("&nbsp");
		return;
	  }
	  $stfield = new inputclass_listfield("sj". $this->rid,"SELECT '' AS id, '' AS tekst UNION SELECT mid,shortname FROM subject ORDER BY tekst",NULL,"mid","bo_rooster",$this->rid,"rid",NULL,"datahandler.php");
	  if($mayedit)
	    $stfield->echo_html();
	  else
	    echo($stfield->__toString());
	}
	
  }
?>
