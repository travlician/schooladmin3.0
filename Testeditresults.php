<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2015 Aim4me N.V.   (http://www.aim4me.info)       |
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
require_once("displayelements/displayelement.php");
require_once("student.php");
require_once("group.php");
require_once("testdef.php");
require_once("sclass.php");
require_once("studentsorter.php");

class Testeditresults extends displayelement
{
  protected function add_contents()
  {
    echo("<script type='text/javascript' src='entertotab.js'></script>");
		if(isset($_POST['sselectfld']))
		{
			// Find the gid
			$orggidqr = inputclassbase::load_query("SELECT cid,gid,period FROM testdef LEFT JOIN class USING(cid) WHERE tdid=". $_POST['tdresults']);
			if(isset($orggidqr['gid'][0]))
			{
				// Find the cid for the new subject
				$newcidqr = inputclassbase::load_query("SELECT cid FROM class WHERE gid=". $orggidqr['gid'][0]. " AND mid=". $_POST['sselectfld']);
				if(isset($newcidqr['cid'][0]))
				{
					mysql_query("UPDATE testdef SET cid=". $newcidqr['cid'][0]. " WHERE tdid=". $_POST['tdresults'], inputclassbase::$dbconnection);
					// Now since subject changed, recalculate results for both cids.
					SA_calcGradeGroup($orggidqr['cid'][0], $orggidqr['period'][0]);
					SA_calcGradeGroup($newcidqr['cid'][0], $orggidqr['period'][0]);
				}
			}
		}
  }
  
  public function show_contents()
  {
    global $editresultssimple,$currentuser;
		$dtext = $_SESSION['dtext'];
    $tdid = $_POST['tdresults'];
		
		if($currentuser->get_preference(407) == "WithDetails")
			$editresultssimple=false;
		else if($currentuser->get_preference(407) == "WithoutDetails")
			$editresultssimple=true;

    // Create an array with the current test results
		$resultq = inputclassbase::load_query("SELECT sid,result FROM testresult WHERE tdid=". $tdid);
		if(isset($resultq))
			foreach($resultq['sid'] AS $rix => $sid)
				 $result_array[$sid] = $resultq['result'][$rix];

  // Get the test details
  $testdef = new testdef($tdid);
  $sclass = new sclass($testdef->get_cid());
  
  
  // First part of the page
  echo("<font size=+2><center>" . $dtext['testres_title'] . "</font><p>");

  // Show the test details
  echo("<div align=left>" . $dtext['testres_expl_1'] . " <b>". $_SESSION['CurrentGroup']. "</b>, <b>");
  echo($testdef->get_type() . "</b>: <b>" . $testdef->get_desc());
  echo("</b> " . $dtext['for'] . " <b>" . $sclass->get_subject()->get_fullname(). "</b> " . $dtext['on'] . " <b>");
  echo($testdef->get_date() . "</b> " . $dtext['in_per'] . " <b>" . $testdef->get_period() . "</b>.<br>");
  // Show a box for sorting selection
  $ssortbox = new studentsorter();
  $ssortbox->show();
	
  //echo($dtext['testres_expl_2'] . "</dev><br>");

  // See if this testdef is for a group for which filtering based on subject package is active
  // Create an array with the students in the current group
  $isfiltered = inputclassbase::load_query("SELECT * FROM subjectfiltergroups WHERE `group`=". $sclass->get_group()->get_id());
  //$isfiltered = $sclass->get_group()->filter_package();
  if(!isset($_SESSION['ssortertable']) || $_SESSION['ssortertable'] == "")
    $sortseq = "student.lastname, student.firstname";
  else if($_SESSION['ssortertable'] == "-")
    $sortseq = "student.firstname, student.lastname";
  else
    $sortseq = "data, student.lastname, student.firstname";
	
  if(isset($isfiltered))
  {
    $sql_querys = "SELECT student.*,t1.*". (isset($_SESSION['ssortertable']) && $_SESSION['ssortertable'] != "" && $_SESSION['ssortertable'] != "-" ? ",data" : "") ." FROM student LEFT JOIN sgrouplink USING(sid) 
                   LEFT JOIN (select sid 
                   from s_package 
                   left join subjectpackage USING(packagename)";
	if(isset($_SESSION['ssortertable']) && $_SESSION['ssortertable'] != '' && $_SESSION['ssortertable'] != "-")
	  $sql_querys .= " LEFT JOIN `". $_SESSION['ssortertable']. "` USING(sid)";
	$sql_querys .= " WHERE mid=". $sclass->get_subject()->get_id(). " OR extrasubject=". $sclass->get_subject()->get_id(). " OR extrasubject2=". $sclass->get_subject()->get_id(). " OR extrasubject3=". $sclass->get_subject()->get_id(). "
                   group by sid) AS t1 on (`student`.sid=t1.sid)
                   WHERE gid=". $sclass->get_group()->get_id(). " AND t1.sid IS NOT NULL GROUP BY student.sid ORDER BY ". $sortseq;
  }
  else
  {
    $sql_querys = "SELECT student.*". (isset($_SESSION['ssortertable']) && $_SESSION['ssortertable'] != "" && $_SESSION['ssortertable'] != "-" ? ",data" : "") ." FROM student LEFT JOIN sgrouplink USING(sid)";
	if(isset($_SESSION['ssortertable']) && $_SESSION['ssortertable'] != '' && $_SESSION['ssortertable'] != "-")
	  $sql_querys .= " LEFT JOIN `". $_SESSION['ssortertable']. "` USING(sid)";
    $sql_querys .= " WHERE gid=". $sclass->get_group()->get_id(). " GROUP BY student.sid ORDER BY ". $sortseq;
  }
  $students = inputclassbase::load_query($sql_querys);
    // Show for which subject current editing applies and allow change
	$subselbox = new subjectselector();
	echo($dtext['Change']. " ");
	$subselbox->show();

  echo("<form method=post action=updtestres.php ID=updtestres><input type=hidden name=tdid value='$tdid'>");
  // Create the heading row for the table
  echo("<table border=1 cellpadding=0>");
  echo("<tr>");
  if(isset($editresultssimple) && $editresultssimple)
  {
    echo("<TH><center>". $dtext['Firstname']. "</center></th><TH><center>". $dtext['Lastname']. "</center></th>");
  }
  else
  {
    $fields = student::get_list_headers();
    foreach($fields AS $fieldname)
    {
      echo("<th><center>". $fieldname. "</th>");
    }
  }
  echo("<th><center>" . $dtext['Result'] . "</th></tr>");


  // Create a row in the table for every existing student
  $altrow = false;
  if(isset($students['sid']))
	  foreach($students['sid'] AS $six => $sid)
	  {
	    // insert an extra row to separate on primary sort if conditions met
			if(isset($prevdata) && isset($students['data'][$six]) && $prevdata != $students['data'][$six])
				echo("<TR><TD COLSPAN=3>&nbsp;</td></tr>");
			$stud = new student($sid);
			echo("<tr". ($altrow ? ' class=altbg' : ''). ">");
			if(isset($editresultssimple) && $editresultssimple)
			{
				echo("<TD>". $stud->get_lastname(). "</td><TD>". $stud->get_firstname(). "</td>");
			}
			else
			{
				$sdata = $stud->get_list_data();
				foreach($sdata AS $stdata)
					echo("<TD>". $stdata. "</TD>");
			}
			echo("<td>");
			$rfld = new inputclass_textfield("tres". $testdef->get_id(). "-". $sid,4,NULL,"result","testresult",$testdef->get_id(),"tdid",NULL,"datahandler.php");
			$rfld->set_extrakey("sid",$sid);
			$rfld->set_extrafield("tid", $_SESSION['uid']);
			$rfld->echo_html();
			if(!isset($firstentryid))
				$firstentryid="tres". $testdef->get_id(). "-". $sid;
			echo("</td></tr>");
			$altrow = !$altrow;
			if(isset($students['data'][$six]))
				$prevdata = $students['data'][$six];
	  }
  else
    echo("<tr><td colspan=3>". $dtext['stupw_expl_5']. "</td></tr>");
  // close the table
  echo("</table>");
  echo("</form>");
  echo("<script type='text/javascript'> EnterToTab.init( document.getElementById('updtestres'), false ); </script>");
	if(isset($firstentryid))
	{
		echo("<script type='text/javascript'> 
			function handlePaste(e) 
			{    
				e.stopPropagation();
				e.preventDefault();
				// Get pasted data via clipboard API
				clipboardData = e.clipboardData || window.clipboardData;
				pastedData = clipboardData.getData('Text').split ('\\n',". count($students['sid']). ");
				if(pastedData.length < 2)
					pastedData = clipboardData.getData('Text').split ('\\r',". count($students['sid']). ");
				//pastadata = pastedData;
				// Do whatever with pasteddata
				fieldlst = document.getElementsByTagName('INPUT');
				var pdatpos = 0;
				for(var i=0; i<fieldlst.length; i++)
				{
					if(fieldlst[i].name.substring(0,4) == 'tres' && pastedData[pdatpos] !== undefined)
					{
						fieldlst[i].value=pastedData[pdatpos];
						if(pastedData[pdatpos] != '')
							send_xml(fieldlst[i].name,fieldlst[i]);
						pdatpos++;
					}
				}
			} ");
		echo(" document.getElementById('". $firstentryid. "').addEventListener('paste', handlePaste); </script>");
	}

  echo("</html>");
  }
}
?>
