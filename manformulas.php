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
  require_once("inputlib/inputclasses.php");
  session_start();

  $login_qualify = 'A';
  include ("schooladminfunctions.php");
	
	// Connect input lib to database
	inputclassbase::dbconnect($userlink);

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];

  $uid = intval($uid);

	// Check if need to process AJAX results
  if(isset($_POST['fieldid']))
	{
		//echo("ERR");
		include("inputlib/procinput.php");
		if(substr($_POST['fieldid'],0,5) == "ssubj")
			echo(" REFRESH");
		exit;
	}
	
	// See if need to 'move' selected test items
	if(isset($_POST['formfunc']))
  {
		if($_POST['formfunc'] == "<-")
		{ // Need to move from available part to selected part
	    if(isset($_POST['avtests']))
			  foreach($_POST['avtests'] AS $atst)
				{
					mysql_query("REPLACE INTO specialformula_sources (formulaid,short_desc) VALUES(". $_POST['formulaid']. ",'". $atst. "')", $userlink);
					echo(mysql_error($userlink));
				}			
		}
		else if($_POST['formfunc'] == "->")
		{
	    if(isset($_POST['seltests']))
			  foreach($_POST['seltests'] AS $atst)
				{
					mysql_query("DELETE FROM specialformula_sources WHERE formulaid=". $_POST['formulaid']. " AND short_desc='". $atst. "'", $userlink);
					echo(mysql_error($userlink));
				}			
			
		}
		unset($_POST['avtests']);
		unset($_POST['seltests']);
	}
	
	// See if items need to be removed
	if(isset($_POST['removeformula']))
	{
		$fobj = new SpecialFormula($_POST['removeformula']);
		$fobj->remove();
		unset($_POST['removeformula']);
	}
	// See if items need to be moved up
	if(isset($_POST['moveupformula']))
	{
		$fobj = new SpecialFormula($_POST['moveupformula']);
		$fobj->move_up();
		unset($_POST['moveupformula']);
	}
	// See if items need to be removed
	if(isset($_POST['movedownformula']))
	{
		$fobj = new SpecialFormula($_POST['movedownformula']);
		$fobj->move_down();
		unset($_POST['movedownformula']);
	}
	
  // First part of the page
  echo("<html><head><title>" . $dtext['Specialformulas'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['Specialformulas'] . "</font><p>");
  echo '<a href="admin.php">';
  echo($dtext['back_admin'] . "</a></center><br>");
  echo("<br><div align=left>" . $dtext['Specialformulaexpl']. "</div><br><br>");
	
	// First the existing formulas
	// Get a list of the existing formulas
	$forms = SpecialFormula::list_formulas();
	// Add a new one to edit
	$forms[0] = new SpecialFormula(0);
	// Show all formulas for editing
	foreach($forms AS $aformula)
	{
		echo("<BR><FIELDSET><LEGEND>". $aformula->get_description(). "</LEGEND>");
		$aformula->edit();
		echo("</fieldset>");
	}
	
	// Create forms to remove, move up and move down
	echo("<FORM METHOD=POST ID=removeform><INPUT TYPE=HIDDEN NAME=removeformula ID=removeformula VALUE=0></FORM>");
	echo("<FORM METHOD=POST ID=moveupform><INPUT TYPE=HIDDEN NAME=moveupformula ID=moveupformula VALUE=0></FORM>");
	echo("<FORM METHOD=POST ID=movedownform><INPUT TYPE=HIDDEN NAME=movedownformula ID=movedownformula VALUE=0></FORM>");
	// Add the scripts to remove, move up and move down
?>
<SCRIPT>
function removeformula(formulaid)
{
	document.getElementById('removeformula').value=formulaid;
	document.getElementById('removeform').submit();
}
function moveupformula(formulaid)
{
	document.getElementById('moveupformula').value=formulaid;
	document.getElementById('moveupform').submit();
}
function movedownformula(formulaid)
{
	document.getElementById('movedownformula').value=formulaid;
	document.getElementById('movedownform').submit();
}
</SCRIPT>
<STYLE>
LABEL
{
	width: 200px;
	display: inline-block;
}
</STYLE>
<?
	
  
  // close the page
  echo("</html>");
	
	class SpecialFormula
	{
		protected $formulaid;
    public function __construct($fid = NULL)
		{
			$this->formulaid = $fid;
		}
		
		public function get_description()
		{
			$descfld = new inputclass_textfield("desc". $this->formulaid,40,NULL,"description","specialformulas",$this->formulaid,"formulaid");
			return $descfld->__toString();
		}
		
		public function edit()
		{
			global $userlink;
		  $descfld = new inputclass_textfield("desc". $this->formulaid,40,NULL,"description","specialformulas",$this->formulaid,"formulaid");
			// Add some additional fields if this is a new formula
			$sqr = inputclassbase::load_query("SELECT MAX(seq_no + 1) AS nseq FROM specialformulas");
			if(isset($sqr['nseq']) && $sqr['nseq'][0] > 0)
				$nseq = $sqr['nseq'][0];
			else
				$nseq = 1;
			if($this->formulaid == 0)
				$descfld->set_extrafield("seq_no",$nseq);
				
			
			echo("<LABEL>". $_SESSION['dtext']['Description']. ": </LABEL>");
			$descfld->echo_html();
			// Depending on which this is, show icons for delete and move up
			if($this->formulaid != 0)
			{ // Show remove icon
		    echo("<IMG SRC='PNG/action_delete.png' onClick='removeformula(". $this->formulaid. ");'>");
				$myseqnoqr = inputclassbase::load_query("SELECT seq_no FROM specialformulas WHERE formulaid=". $this->formulaid);
				if(isset($myseqnoqr['seq_no']) && $myseqnoqr['seq_no'][0] > 1)
				// Show Up icon
		      echo("<IMG SRC='PNG/arrow_top.png' onClick='moveupformula(". $this->formulaid. ");'>");
				else // Show down icon
		      echo("<IMG SRC='PNG/arrow_down.png' onClick='movedownformula(". $this->formulaid. ");'>");
			}
			
			
		  $shortfld = new inputclass_textfield("short". $this->formulaid,10,NULL,"short_desc","specialformulas",$this->formulaid,"formulaid");
			// Add some additional fields if this is a new formula
			if($this->formulaid == 0)
				$shortfld->set_extrafield("seq_no",$nseq);
			echo("<BR><LABEL>". $_SESSION['dtext']['Short']. ": </LABEL>");
			$shortfld->echo_html();
		  $formulafld = new inputclass_textarea("formula". $this->formulaid,"120,*",NULL,"formula","specialformulas",$this->formulaid,"formulaid");
			// Add some additional fields if this is a new formula
			if($this->formulaid == 0)
				$formulafld->set_extrafield("seq_no",$nseq);
			echo("<BR><LABEL>". $_SESSION['dtext']['Formula']. ": </LABEL>");
			$formulafld->echo_html();
		  $tsubjfld = new inputclass_listfield("tsubj". $this->formulaid,"SELECT '' AS id, '' AS tekst UNION SELECT mid,fullname FROM subject ORDER BY tekst",NULL,"targetmid","specialformulas",$this->formulaid,"formulaid");
			echo("<BR><LABEL>". $_SESSION['dtext']['Targetsubject']. ": </LABEL>");
			$tsubjfld->echo_html();
		  $tttfld = new inputclass_listfield("ttt". $this->formulaid,"SELECT '' AS id, '' AS tekst UNION SELECT type,translation FROM testtype LEFT JOIN reportcalc ON (testtype=type) WHERE weight > 0 GROUP BY testtype ORDER BY tekst",NULL,"targettesttype","specialformulas",$this->formulaid,"formulaid");
			echo("<BR><LABEL>". $_SESSION['dtext']['Targettesttype']. ": </LABEL>");
			$tttfld->echo_html();
		  $ssubjfld = new inputclass_listfield("ssubj". $this->formulaid,"SELECT '' AS id, '' AS tekst UNION SELECT mid,fullname FROM subject ORDER BY tekst",NULL,"sourcemid","specialformulas",$this->formulaid,"formulaid");
			echo("<BR><LABEL>". $_SESSION['dtext']['Sourcesubject']. ": </LABEL>");
			$ssubjfld->echo_html();
			// Now see if there are any test definitions that apply
			$sourcemid = $ssubjfld->get_state();
			if($sourcemid > 0)
			{ // only do this if a valid source mid has been found
				// Now only use this years test definitions
				$thisyearqr = inputclassbase::load_query("SELECT year FROM period");
				$thisyear = $thisyearqr['year'][0];
				$appdefsq = "SELECT DISTINCT short_desc FROM testdef LEFT JOIN class USING(cid) LEFT JOIN reportcalc ON(type=testtype) WHERE class.mid=". $sourcemid. " AND year='". $thisyear. "' ORDER BY short_desc";
				$appdefsqr = inputclassbase::load_query($appdefsq);
				if(isset($appdefsqr['short_desc']))
				{
					// Now we need to create a form with two multiple select boxes to be able to select/deselected the tests to use
					// First we get which ones are already present
					if($this->formulaid > 0)
					{
						$seltestsqr = inputclassbase::load_query("SELECT DISTINCT short_desc FROM specialformula_sources WHERE formulaid=". $this->formulaid);
						if(isset($seltestsqr['short_desc']))
						{
							foreach($seltestsqr['short_desc'] AS $selssn)
							  $seltests[$selssn] = $selssn;
						}
					}
					echo("<FORM METHOD='POST' ACTION='". $_SERVER['REQUEST_URI']. "'><INPUT TYPE=hidden NAME=formulaid VALUE=". $this->formulaid. ">");
					echo("<LABEL>". $_SESSION['dtext']['Sourcetestdefinitions']. ": </label>");
					echo("<TABLE><TR>");
					echo("<TD><SELECT MULTIPLE SIZE=8 NAME='seltests[]'>");
					if(isset($seltests))
					{
						foreach($seltests AS $seltst)
						  echo("<OPTION VALUE='". $seltst. "'>". $seltst. "</OPTION>");
					}
					echo("</SELECT></TD>");
					echo("<TD><INPUT TYPE=SUBMIT NAME=formfunc VALUE='<-'><BR><INPUT TYPE=SUBMIT NAME=formfunc VALUE='->'></TD>");
					echo("<TD><SELECT MULTIPLE SIZE=8 NAME='avtests[]'>");
					foreach($appdefsqr['short_desc'] AS $ss)
					if(!isset($seltests) || !in_array($ss,$seltests))
					  echo("<OPTION VALUE='". $ss. "'>". $ss. "</OPTION>");
					echo("</SELECT></TD>");
					echo("</TR></TABLE></form>");
				}
				else
				{
					//echo(" No qualifying test definitions found");
					mysql_query("DELETE FROM specialformula_sources WHERE formulaid=". $this->formulaid, $userlink);
					echo(mysql_error($userlink));
				}
				
			}
		}
		
		public function remove()
		{
			global $userlink;
			$myseqnoqr = inputclassbase::load_query("SELECT seq_no FROM specialformulas WHERE formulaid=". $this->formulaid);
			if(isset($myseqnoqr['seq_no']) && $myseqnoqr['seq_no'][0] > 0)
			{
				$myseqno = $myseqnoqr['seq_no'][0];
			  mysql_query("UPDATE specialformulas SET seq_no = seq_no -1 WHERE seq_no>". $myseqno, $userlink);
			  echo(mysql_error($userlink));
			}
			mysql_query("DELETE FROM specialformulas WHERE formulaid=". $this->formulaid, $userlink);
			echo(mysql_error($userlink));
			mysql_query("DELETE FROM specialformula_sources WHERE formulaid=". $this->formulaid, $userlink);
			echo(mysql_error($userlink));
		}
		
		public function move_up()
		{
			global $userlink;
			$myseqnoqr = inputclassbase::load_query("SELECT seq_no FROM specialformulas WHERE formulaid=". $this->formulaid);
			if(isset($myseqnoqr['seq_no']) && $myseqnoqr['seq_no'][0] > 0)
			{
				$myseqno = $myseqnoqr['seq_no'][0];
			  mysql_query("UPDATE specialformulas SET seq_no = seq_no +1 WHERE seq_no=". ($myseqno-1), $userlink);
			  echo(mysql_error($userlink));
			  mysql_query("UPDATE specialformulas SET seq_no = seq_no -1 WHERE formulaid=". $this->formulaid, $userlink);
			  echo(mysql_error($userlink));
			}
		}
		
		public function move_down()
		{
			global $userlink;
			$myseqnoqr = inputclassbase::load_query("SELECT seq_no FROM specialformulas WHERE formulaid=". $this->formulaid);
			if(isset($myseqnoqr['seq_no']) && $myseqnoqr['seq_no'][0] > 0)
			{
				$myseqno = $myseqnoqr['seq_no'][0];
			  mysql_query("UPDATE specialformulas SET seq_no = seq_no -	1 WHERE seq_no=". ($myseqno+1), $userlink);
			  echo(mysql_error($userlink));
			  mysql_query("UPDATE specialformulas SET seq_no = seq_no +1 WHERE formulaid=". $this->formulaid, $userlink);
			  echo(mysql_error($userlink));
			}
		}
		
		public static function list_formulas()
		{
			$lqr = inputclassbase::load_query("SELECT formulaid FROM specialformulas ORDER BY seq_no");
			if(isset($lqr['formulaid']))
			{
				foreach($lqr['formulaid'] AS $fid)
				  $retarray[$fid] = new SpecialFormula($fid);
				return $retarray;
			}
			else
			  return NULL;
		}
		
	}

?>
