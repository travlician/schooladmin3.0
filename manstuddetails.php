<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2013 Aim4me N.V.   (http://www.aim4me.info) 	  |
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

  $login_qualify = 'A';
  include ("schooladminfunctions.php");
  require_once("inputlib/inputclasses.php");
  // Connect the library to the current database connection
  inputclassbase::dbconnect($userlink);

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];

  $uid = intval($uid);
  
  // If table needs to be expanded to enable setring of touch index and initial setup extra fields, do it now!
  $toverviewfields = SA_loadquery("SHOW COLUMNS FROM student_details LIKE 'toverview'");
  if(!isset($toverviewfields['Field'][1]))
  { // Need to add fields overview tochscreens, at initial entry and texts and set names as initial entry fields
    mysql_query("ALTER TABLE student_details ADD toverview int(1) AFTER overview");
    mysql_query("ALTER TABLE student_details ADD initialentry int(1) AFTER toverview");
	mysql_query("INSERT INTO tt_english (short,full) VALUES('TSOverview','TS Overview')");
	mysql_query("INSERT INTO tt_nederlands (short,full) VALUES('TSOverview','TS Overzicht')");
	mysql_query("INSERT INTO tt_english (short,full) VALUES('InitialFields','Entry')");
	mysql_query("INSERT INTO tt_nederlands (short,full) VALUES('InitialFields','Entry')");
	mysql_query("UPDATE student_details SET initialentry=1 WHERE table_name = '*student.lastname' OR table_name='*student.firstname' OR table_name='*sid'");
	mysql_query("UPDATE student_details SET toverview=1 WHERE table_name = '*student.lastname' OR table_name='*student.firstname'");
  }
  

  // First we get all the data from existing student_details in an array.
  $sql_query = "SELECT * FROM student_details ORDER BY seq_no";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
  $nrows = 0;
  if (mysql_num_rows($sql_result)!=0)
  {
    $nfields = mysql_num_fields($sql_result);
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
     $nrows++;
     for ($i=0;$i<$nfields;$i++){
       $fieldname = mysql_field_name($sql_result,$i);
       $fieldvalu = mysql_result($sql_result,$r,mysql_field_name($sql_result,$i));
       $grade_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  SA_closeDB();
  $row_n = $nrows;

  // First part of the page
  echo("<html><head><title>" . $dtext['studetman_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['studetman_title'] . "</font><p>");
  echo '<a href="admin.php">';
  echo($dtext['back_admin'] . "</a><br>");
  echo("<br><div align=left>" . $dtext['studetman_expl_1']);
  echo(" " . $dtext['studetman_expl_2']);
  echo(" " . $dtext['studetman_expl_3'] . "</dev><br>");
  echo("<table border=1 cellpadding=0>");
  
  // Create the heading row for the table
  echo("<tr><td><center><font size=-1>" . $dtext['Fieldname'] . "</td>");
  echo("<td><center><font size=-1>" . $dtext['Label'] . "</td>");
  echo("<td><center><font size=-1>" . $dtext['Type'] . "</td>");
  echo("<td><center><font size=-1>" . $dtext['Size'] . "</td>");
  echo("<td><center><font size=-1>" . $dtext['params'] . "</td>");  
  echo("<td><center><font size=-1>" . $dtext['Records'] . "</td>");
  echo("<td><center><font size=-1>" . $dtext['R_acc'] . "</td>");
  echo("<td><center><font size=-1>" . $dtext['W_acc'] . "</td>");
  echo("<td><center><font size=-1>" . $dtext['numb_token'] . "</td>");
  echo("<td><center><font size=-1>" . $dtext['Overview']. "</td>");
  echo("<td><center><font size=-1>" . $dtext['TSOverview']. "</td>");
  echo("<td><center><font size=-1>" . $dtext['InitialFields']. "</td>");
  echo("<td><center></td>");
  echo("<td></td></font></tr>");

  // Create a row in the table for every existing detail
  for($r=1;$r<=$row_n;$r++)
  {
    echo("<tr><form method=post action=updstuddetail.php name=us". $r. ">");
    echo("<input type=hidden name=table_name value='" . $grade_array['table_name'][$r] . "'>");
    // Field name, label and type
    echo("<td><font size=-1>". $grade_array['table_name'][$r] . "</td>");
    echo("<td><center><input type=text size=15 name=label value=\"" . $grade_array['label'][$r] ."\"></td>");
    echo("<td><center><font size=-1>");
    if($grade_array['type'][$r] == "text")
      echo($dtext['Text']);
    else if($grade_array['type'][$r] == "choice")
	  echo($dtext['choice']);
	else
      echo($dtext['Picture']);
    echo("</td>");
    // Size, parameters and multiple or single records
    echo("<td><center><input type=text size=3 name=size value=\"". $grade_array['size'][$r]. "\"></td>");
    echo("<td><center><input type=text size=15 name=params value=\"" . $grade_array['params'][$r] ."\"></td>");
    if($grade_array['multi'][$r] == "Y")
      echo("<td><font size=-1>" . $dtext['Multi'] . "</td>");
    else
      echo("<td><font size=-1>" . $dtext['Single'] . "</td>");
    // Read access
    echo("<td><center><select name=raccess>");
    echo("<option value='A' ". (($grade_array['raccess'][$r]=="A") ? " selected" : "") . ">" . $dtext['allow_all_short'] . "</option>");
    echo("<option value='T' ". (($grade_array['raccess'][$r]=="T") ? " selected" : "") . ">" . $dtext['allow_teach_short'] . "</option>");
    echo("<option value='M' ". (($grade_array['raccess'][$r]=="M") ? " selected" : "") . ">" . $dtext['allow_ment_short'] . "</option>");
    echo("<option value='C' ". (($grade_array['raccess'][$r]=="C") ? " selected" : "") . ">" . $dtext['allow_couns_short'] . "</option>");
    echo("<option value='O' ". (($grade_array['raccess'][$r]=="O") ? " selected" : "") . ">" . $dtext['Office_admin'] . "</option>");
    echo("<option value='P' ". (($grade_array['raccess'][$r]=="P") ? " selected" : "") . ">" . $dtext['allow_ment_office'] . "</option>");
    echo("<option value='N' ". (($grade_array['raccess'][$r]=="N") ? " selected" : "") . ">" . $dtext['allow_none'] . "</option>");
    echo("</select></td>");
    // Write access
    echo("<td><center><select name=waccess>");
    echo("<option value='A' ". (($grade_array['waccess'][$r]=="A") ? " selected" : "") . ">" . $dtext['allow_all_short'] . "</option>");
    echo("<option value='T' ". (($grade_array['waccess'][$r]=="T") ? " selected" : "") . ">" . $dtext['allow_teach_short'] . "</option>");
    echo("<option value='M' ". (($grade_array['waccess'][$r]=="M") ? " selected" : "") . ">" . $dtext['allow_ment_short'] . "</option>");
    echo("<option value='C' ". (($grade_array['waccess'][$r]=="C") ? " selected" : "") . ">" . $dtext['allow_couns_short'] . "</option>");
    echo("<option value='O' ". (($grade_array['waccess'][$r]=="O") ? " selected" : "") . ">" . $dtext['Office_admin'] . "</option>");
    echo("<option value='P' ". (($grade_array['waccess'][$r]=="P") ? " selected" : "") . ">" . $dtext['allow_ment_office'] . "</option>");
    echo("<option value='N' ". (($grade_array['waccess'][$r]=="N") ? " selected" : "") . ">" . $dtext['allow_none'] . "</option>");
    echo("</select></td>");
    // sequence number, here we make a drop-down /w all the available numbers
    echo("<td><select name=seq_no>");
    for($s=1;$s<=$row_n;$s++)
      echo("<option value=". $s . (($grade_array['seq_no'][$r] == $s) ? " selected" : "") . ">" . $s . "</option>");
    echo("</select></td>");
	// Flag (checkbox) to show item in overview or not, ONLY if not a multiple record!
	if($grade_array['multi'][$r] == "N")
      echo("<td><center><input type=checkbox name=overview". ($grade_array['overview'][$r] == 1 ? " checked" : ""). "></td>");
	else
	  echo("<td><center>-</td>");
	if($grade_array['multi'][$r] == "N")
      echo("<td><center><input type=checkbox name=toverview". ($grade_array['toverview'][$r] == 1 ? " checked" : ""). "></td>");
	else
	  echo("<td><center>-</td>");
	if($grade_array['multi'][$r] == "N")
      echo("<td><center><input type=checkbox name=initialentry". ($grade_array['initialentry'][$r] == 1 ? " checked" : ""). "></td>");
	else
	  echo("<td><center>-</td>");

    // DO button and ends the form
    //echo("<td><center><input type=submit value=" . $dtext['DO_CAP'] . "></td></form>");
    echo("<td><center><img src='PNG/action_check.png' title='". $dtext['DO_CAP']. "' onclick='document.us". $r. ".submit();'></td></form>");
    // Delete button (only if not fixed!)
    if($grade_array['fixed'][$r] == "Y")
      echo("<td><center><font size=-1>-</td></tr>");
    else
    {
      echo("<form method=post action=delstuddetail.php name=ds". $r. "><input type=hidden name=table_name value=");
      echo($grade_array['table_name'][$r]);
      //echo("><td><input type=submit value=" . $dtext['Delete'] . "></td></form></tr>");
      echo("><td><center><img src='PNG/action_delete.png' title='". $dtext['Delete']. "' onclick='if(confirm(\"". $dtext['confirm_delete']. "\")) { document.ds". $r. ".submit(); }'></td></form></tr>");
    }
  }


  // Insert the row for a new student detail field
  echo("<tr><form method=post action=updstuddetail.php name=newsd><input type=hidden name=new value=Y>");
  // Field name, label and type
  echo("<td><input type=text size=17 name=table_name></td>");
  echo("<td><center><input type=text size=15 name=label></td>");
  echo("<td><center><select name=type><option value=text selected>" . $dtext['Text'] . "</option>");
  echo("<option value=picture>" . $dtext['Picture'] . "</option><option value=choice>" . $dtext['choice'] . "</option></select></td>");
  // size and parameters
  echo("<td><center><input type=text size=3 name=size></td>");
  echo("<td><center><input type=text size=15 name=params></td>");
  // Single or multiple records
  echo("<td><center><select name=multi><option value=Y>" . $dtext['Multi'] . "</option>");
  echo("<option value=N selected>" . $dtext['Single'] . "</option></select></td>");
  // Read access
  echo("<td><center><select name=raccess>");
  echo("<option value='A'>" . $dtext['allow_all_short'] . "</option>");
  echo("<option value='T'>" . $dtext['allow_teach_short'] . "</option>");
  echo("<option value='M'>" . $dtext['allow_ment_short'] . "</option>");
  echo("<option value='C'>" . $dtext['allow_couns_short'] . "</option>");
  echo("<option value='O'>" . $dtext['Office_admin'] . "</option>");
  echo("<option value='P'>" . $dtext['allow_ment_office'] . "</option>");
  echo("<option value='N'>" . $dtext['allow_none'] . "</option>");
  echo("</select></td>");
  // Write access
  echo("<td><center><select name=waccess>");
  echo("<option value='A'>" . $dtext['allow_all_short'] . "</option>");
  echo("<option value='T'>" . $dtext['allow_teach_short'] . "</option>");
  echo("<option value='M'>" . $dtext['allow_ment_short'] . "</option>");
  echo("<option value='C'>" . $dtext['allow_couns_short'] . "</option>");
  echo("<option value='O'>" . $dtext['Office_admin'] . "</option>");
  echo("<option value='P'>" . $dtext['allow_ment_office'] . "</option>");
  echo("<option value='N'>" . $dtext['allow_none'] . "</option>");
  echo("</select></td>");
  // sequence number, here we make a drop-down /w all the available numbers
  echo("<td><select name=seq_no>");
  for($s=1;$s<=($row_n);$s++)
    echo("<option value=". $s. ">" . $s . "</option>");
  echo("<option value=" . ($row_n+1) . " selected>" . ($row_n + 1) . "</option>");
  echo("</select></td>");
  echo("<td><center><input type=checkbox name=overview></td>");
  echo("<td><center><input type=checkbox name=toverview></td>");
  echo("<td><center><input type=checkbox name=initialentry></td>");

  // ADD button and ends the form
  //echo("<td><center><input type=submit value=" . $dtext['ADD_CAP'] . "></td></form>");
  echo("<td><center><img src='PNG/action_add.png' title='". $dtext['ADD_CAP']. "' onclick='document.newsd.submit();'></td></form>");
  // No delete button!
  echo("<td></td></tr>");
  
  // close the table
  echo("</table>");
  // Before we show the table for choices, see if we need to delete an antry
  if(isset($_POST['delstabi']))
  {
    mysql_query("DELETE FROM ". $_POST['stableselect']. " WHERE id=". $_POST['delstabi'], $userlink);
  }
  // Show a drop down list of tables for choices
  $ctablenameq = inputclassbase::load_query("SELECT params FROM student_details WHERE type='choice' AND params LIKE '*%'");
  if(isset($ctablenameq['params']))
  {
    echo("<BR><BR><FORM METHOD=POST NAME=stablename ID=stablename action=". $_SERVER['PHP_SELF']. "><SELECT NAME=stableselect onChange='document.stablename.submit();'><OPTION VALUE=''> </OPTION>");
	foreach($ctablenameq['params'] AS $ctabopt)
	  echo("<OPTION VALUE='". substr($ctabopt,1). "'". ((isset($_POST['stableselect']) && $_POST['stableselect'] == substr($ctabopt,1)) ? ' selected' : ''). ">". substr($ctabopt,1). "</OPTION>");
	echo("</SELECT></FORM>");
	if(isset($_POST['stableselect']))
	{ // allow editing of the table
	  echo("<table><tr><th>". $_POST['stableselect']. "</th></tr>");
	  $stabids = inputclassbase::load_query("SELECT id FROM ". $_POST['stableselect']. " ORDER BY tekst");
	  if(isset($stabids['id']))
	  {
	    foreach($stabids['id'] AS $stabid)
		{
		  echo("<tr><td>");
		  $newfield = new inputclass_textfield("staentry". $stabid,80,$userlink,"tekst",$_POST['stableselect'],$stabid,"id","","datahandler.php");
		  $newfield->echo_html();
		  echo("</td><td><IMG SRC='PNG/action_delete.png' BORDER=0 TITLE='". $dtext["Delete"]. "' onClick='if(confirm(\"". $dtext['confirm_delete']. "\")) { delstableitem(". $stabid."); }'>");		  
		  echo("</td></tr>");
		}
	  }
      // Add a new entry field
	  echo("<tr><td>");
	  $newfield = new inputclass_textfield("staentry0",80,$userlink,"tekst",$_POST['stableselect'],0,"id","","datahandler.php");
	  $newfield->echo_html();
	  echo("</td><td><IMG SRC='PNG/action_add.png' BORDER=0 TITLE='". $dtext["ADD_CAP"]. "' onClick='setTimeout(\"delstableitem(0);\",1000)'>");		  
	  echo("</td></tr>");
	}
  }
  if(isset($_POST['stableselect']))
  echo("<FORM NAME=delete_stabitem ID=delete_stabitem METHOD=POST ACTION=". $_SERVER['REQUEST_URI']. ">
        <input type=hidden name=delstabi value=0>
		<input type=hidden name=stableselect value='". $_POST['stableselect']. "'></FORM>");
?>
<SCRIPT>
  function delstableitem(asid)
  {
	document.getElementById("delete_stabitem").delstabi.value=asid;
	document.getElementById("delete_stabitem").submit();
  }
</SCRIPT>
<?
  echo("</html>");
?>
