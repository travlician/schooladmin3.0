<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011 Aim4me N.V.  (http://www.aim4me.info)        |
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
// | Authors: Wilfred van Weert - travlcian@bigfoot.com                   |
// +----------------------------------------------------------------------+
//
  session_start();

  $login_qualify = 'A';
  require_once("schooladminfunctions.php");
  require_once("schooladmingradecalc.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];

  $uid = intval($uid);

  $id = trim($HTTP_POST_VARS['id']);
  $status = trim($HTTP_POST_VARS['status']);
  $year = trim($HTTP_POST_VARS['year']);
	
	if($id != "")
		$perdets = SA_loadquery("SELECT * FROM period WHERE id=". $id);

	

  if ($status == "")
  {
    echo($dtext['missing_params']);
    echo("<br><a href=manperiods.php>" . $dtext['back_perman'] . "</a>");
    SA_closeDB();
    exit;
  }
 
  if($id == "") 
    $sql_query = "INSERT INTO period VALUES(NULL, '$status', '$year', '". $_POST['startdate']. "','". $_POST['enddate']. "')";
  else
    $sql_query = "UPDATE period SET status='$status', year='$year', startdate='". $_POST['startdate']. "', enddate='". $_POST['enddate']. "' WHERE id='$id';";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  // Reset all lockings on test definitions
  mysql_query("UPDATE testdef LEFT JOIN period ON(period=id) SET locked=1 WHERE period.year <> testdef.year OR status <> 'open'", $userlink);	  
  mysql_query("UPDATE testdef LEFT JOIN period ON(period=id) SET locked=0 WHERE period.year = testdef.year AND status = 'open'", $userlink);	  

  // Recalculate all grades for this period
  if($id != "")
	{
		// Now only recalcalc if status had changed or calc button was pressed.
		if((isset($perdets['status']) && $perdets['status'][1] != $status) || $_POST['recalc'] == 1)
		{
			if($_POST['recalc'] == 1) // Forced recalc, so remove gradestore first
				mysql_query("DELETE FROM gradestore WHERE (period=0 OR period=". $id. ") AND year='". $perdets['year'][1]. "'");
			SA_calcGradePeriod($id);
		}
	}

  SA_closeDB();
  if($sql_result == 1)
  {	// operation succeeded, back to the mangroups page!
    header("Location: " . $livesite ."manperiods.php");
    exit;
  }
  else
  {
    echo($dtext['op_fail']);
    echo("<br><a href=manperiods.php>" . $dtext['back_perman'] . "</a>");
  }   

?>