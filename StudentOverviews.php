<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.   (http://www.aim4me.info)       |
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
require_once("teacher.php");
require_once("group.php");
require_once("studentsorter.php");

if(isset($_POST['stuviewdown']) && isset($_POST['stuvgroupsel']))
{
  require_once("inputlib/inputclasses.php");
  session_start();
  require_once("schooladminconstants.php");
  require_once("schooladminfunctions.php");
  inputclassbase::dblogon($databaseserver,$datausername,$datapassword,$databasename);	
	$viewobj = new StudentOverview($_POST['stuviewdown']);
	header("Content-Disposition: attachment; filename=\"". $viewobj->get_description(). ".xls\"");
	header("Content-Type: application/xls");  
	$viewobj->download();
	exit;
}

class StudentOverviews extends displayelement
{
  protected function add_contents()
  {
 		if(isset($_POST['stuviewrem']))
		{ // Need to remove this item
			$toRem = new StudentOverview($_POST['stuviewrem']);
			$toRem->remove();		
		}
		if(isset($_POST['dupsovitem']))
		{
			$soviobj = new StudentOverviewItem($_POST['stuviewedit'],$_POST['dupsovitem']);
			$soviobj->up();
		}
		if(isset($_POST['ddownsovitem']))
		{
			$soviobj = new StudentOverviewItem($_POST['stuviewedit'],$_POST['ddownsovitem']);
			$soviobj->down();
		}
		if(isset($_POST['ddeletesovitem']))
		{
			$soviobj = new StudentOverviewItem($_POST['stuviewedit'],$_POST['ddeletesovitem']);
			$soviobj->remove();
		}
	}
  
  public function show_contents()
  {
    if(isset($_POST['stuviewedit']))
		{
			$toEdit = new StudentOverview($_POST['stuviewedit']);
			$toEdit->edit();		
		}
    else if(isset($_POST['stuviewview']))
		{
			$toView = new StudentOverview($_POST['stuviewview']);
			$toView->view();		
		}
    else if(isset($_POST['stuviewdown']))
		{
			$toDown = new StudentOverview($_POST['stuviewdown']);
			$toDown->download();		
		}
		else if(isset($_POST['view']))
		{
			$this->view_student($_POST['view']);
		}
		else if(isset($_POST['qfieldedit']))
		{
			QueryField::edit_qfields();
		}
		else
			$this->list_overviews();
  }
  
  private function list_overviews()
  {
    $dtext = $_SESSION['dtext'];
		$I = new teacher();
		$I->load_current();
		$group = new group();
		$group->load_current();
	
		// Forms and Javascript for handling of view, download, remove and edit of views
		echo("<form method=post name=viewstuview id=viewstuview><input type=hidden name=stuviewview id=stuviewview></form>");
		echo("<form method=post name=downstuview id=downstuview><input type=hidden name=stuviewdown id=stuviewdown></form>");
		echo("<form method=post name=remstuview id=remstuview><input type=hidden name=stuviewrem id=stuviewrem></form>");
		echo("<form method=post name=editstuview id=editstuview><input type=hidden name=stuviewedit id=stuviewedit></form>");
		echo("<form method=post name=editqfields id=editqfields><input type=hidden name=qfieldedit id=qfieldedit></form>");
		echo("<SCRIPT>");
		echo(" ");
		echo(" function fviewstuview(viewid) { document.getElementById('stuviewview').value=viewid; document.getElementById('viewstuview').submit(); }");
		echo(" function fdownstuview(viewid) { document.getElementById('stuviewdown').value=viewid; document.getElementById('downstuview').submit(); }");
		echo(" function fremstuview(viewid) { document.getElementById('stuviewrem').value=viewid; document.getElementById('remstuview').submit(); }");
		echo(" function feditstuview(viewid) { document.getElementById('stuviewedit').value=viewid; document.getElementById('editstuview').submit(); }");
		echo(" function fqfields() { document.getElementById('editqfields').submit(); }");
		echo(" </script>");

    echo("<p><font size=+2>" . $dtext['tpage_studentoverviews'] . "</font></p>");
    echo("<p>" . $dtext['studentviewListIntro'] . "</p>");

		// Show a list of student overviews
		echo("<BR><BR>". $dtext['studentoverviews']);
		echo("<table border=1 celpadding=1>");
		$stulists = StudentOverview::list_overviews($I);
		if(isset($stulists))
			foreach($stulists AS $vid => $viobj)
			{
				$viobj->listit();
			}
		// For a new record
		/*$newov = new StudentOverview();
		$newov->listit(); */
		echo("</table>");		
		echo("<a onClick='feditstuview(0);'>". $dtext['NewOverview']. "</a>");
		
		if($I->has_role("admin"))
		{
			echo("<BR><BR><a onClick='fqfields();'>". $dtext['EditQfields']. "</a>");
			
		}
  }
}

class StudentOverview
{
	protected $viewid;
  public function __construct($viewid = NULL)
  {
    if(isset($viewid))
			$this->viewid = $viewid;
		else
			$this->viewid = 0;
  }
  
  public function get_id()
  {
    return $this->viewid;
  }

	public function listit()
	{
		$dtext = $_SESSION['dtext'];
		if($this->viewid != 0)
		{
			echo("<TR><TD>". $this->get_description(). "</td>");
			echo("<td><img src='PNG/search.png' onclick=\"fviewstuview(". $this->viewid. ");\" title='". $dtext['View']. "'> 
			<img src='PNG/save.png' onclick=\"fdownstuview(". $this->viewid. ");\" title='". $dtext['DownloadExcel']. "'>");
			if($this->get_owner() == $_SESSION['uid'])
				echo(" <img src='PNG/action_delete.png' onclick=\"fremstuview(". $this->viewid. ");\" title='". $dtext['Delete']. "'>");
			echo(" <img src='PNG/reply.png' onclick=\"feditstuview(". $this->viewid. ");\" title='". $dtext['Edit']. "'></td></tr>");
		}
		else // Show a new record
		{
			echo("<tr><td>");
			$this->edit_description();
			echo("</td><td><img src='PNG/action_add.png'></td></tr>");
		}
	}
	
	public function edit_description()
	{
		if($this->viewid == 0)
		{
			$ovfld = new inputclass_textfield("newov",40,NULL,"description","studentview",$this->viewid,"viewid",NULL,"datahandler.php");
			$ovfld->set_extrafield("tid",$_SESSION['uid']);	
			$ovfld->set_extrafield("protect","N");
			$ovfld->echo_html();
		}
		else
		{
			$ovfld = new inputclass_textfield("stov". $this->viewid,40,NULL,"description","studentview",$this->viewid,"viewid",NULL,"datahandler.php");
			$ovfld->echo_html();
		}
	}
	
	public function get_description()
	{
		$ovfld = new inputclass_textfield("stov". $this->viewid,40,NULL,"description","studentview",$this->viewid,"viewid",NULL,"datahandler.php");
		return($ovfld->__toString());
	}
	
	public function get_owner()
	{
		$ownqr = inputclassbase::load_query("SELECT tid FROM studentview WHERE viewid=". $this->viewid);
		return $ownqr['tid'][0];
	}
	
	public function view()
	{
		echo("<style>@media screen { table { margin-left: 40px; margin-top: 40px;}}</style>");
		$dtext = $_SESSION['dtext'];
		$I = new teacher($_SESSION['uid']);
		$itemlist = $this->get_items();
		if(isset($itemlist))
		{
			$eligebility = 2; // 0 is none (no field), 1 is own groups only, 2 is all groups
			foreach($itemlist AS $vitem)
			{
				$ie = $vitem->get_eligebility($I);
				if($ie < $eligebility)
					$eligebility = $ie;
			}
		}
		else
			$eligebility = 0;
		if($eligebility == 0)
			echo("<font color=red>". $dtext['FieldError']. "</font>");
		else
		{
			if(isset($_POST['stuvgroupsel']))
				$this->view_execute($I);
			else
			{
				echo("<BR><BR><form id=viewdetform method=post><input type=hidden name=stuviewview value=". $this->viewid. ">");
				echo("<SELECT name=stuvgroupsel>");
				echo("<OPTION value=1>". $dtext['CurrentGroup']. "</option>");
				if($ie == 2)
					echo("<OPTION value=2>". $dtext['AllGroups']. "</option>");
				echo("</SELECT>");
				echo("<BR>". $dtext['StudentsPerPage']. " : <INPUT TYPE=TEXT SIZE=4 NAME=studsperpage value=60 onChange='document.getElementById(\"viewdetform\").submit();'>");
				echo("<BR><INPUT TYPE=SUBMIT></FORM>");
			}
		}
	}
	
	public function view_execute($I)
	{
		$this->view_tableheader();
		$itemlist = $this->get_items();
		$stulistq = "SELECT DISTINCT sid FROM sgrouplink LEFT JOIN sgroup USING(gid) LEFT JOIN student USING(sid)";
		foreach($itemlist AS $vitem)
			$stulistq .= $vitem->link_filter();
		if($_POST['stuvgroupsel'] == 1) // Only current group
			$stulistq .= " WHERE groupname='". $_SESSION['CurrentGroup']. "'";
		else if($I->has_role("admin") || $I->has_role("office") || $I->has_role("counsel")) // All groups, no exception
			$stulistq .= " WHERE gid IS NOT NULL";
		else // Only groups the teacher is teaching!
		{
			$mygrpsqr = inputclassbase::load_query("SELECT DISTINT gid FROM class WHERE tid=". $_SESSION['uid']);
			$mygrps[0] = 0;
			if(isset($mygrpsqr['gid']))
				foreach($mygrpsqr['gid'] AS $agid)
					$mygrps[$agid] = $agid;
			$mygrpr = implode(",",$mygrps);
			$stulistq .= " WHERE gid IN (". $mygrps. ")";
		}
		foreach($itemlist AS $vitem)
			$stulistq .= $vitem->pre_filter();
		
		$stulistq .= " ORDER BY ";
		$sortdqr = inputclassbase::load_query("SELECT seqno FROM studentviewitems WHERE viewid=". $this->viewid. " AND sortseq IS NOT NULL AND sortseq > 0 ORDER BY sortseq");
		if(isset($sortdqr['seqno']))
			foreach($sortdqr['seqno'] AS $sseqno)
				$stulistq .= $itemlist[$sseqno]->pre_order();
		$stulistq .= "sid";
		//echo("Query = ". $stulistq);
		$stulistqr = inputclassbase::load_query($stulistq);
		if(isset($stulistqr['sid']))
			foreach($stulistqr['sid'] AS $asid)
				$stulist[$asid] = new student($asid);
		
		foreach($itemlist AS $vitem)
			$stulist = $vitem->post_filter($stulist);
		
		// Now we got a list of student that needs to be shown in the sequence required
		if(isset($stulist) && count($stulist) > 0)
		{ // And there really are students!
			$rowpos = 0;
			foreach($stulist AS $astud)
			{
				if($rowpos == $_POST['studsperpage'])
				{
					$rowpos = 0;
					echo("</table>");
					$this->view_tableheader();
				}
				echo("<TR>");
				foreach($itemlist AS $vitem)
				{
					echo("<td>". $vitem->get_value($astud). "</td>");
				}
				$rowpos++;
			}
		}
		// Now we may need to add aggregated data
		$needtoagg = false;
		foreach($itemlist AS $vitem)
		{
			if($vitem->get_aggregation() != "")
				$needtoagg=true;
		}
		if($needtoagg)
		{
			echo("<TR>");
			foreach($itemlist AS $vitem)
			{
				$aggtype = $vitem->get_aggregation();
				if($aggtype != "")
				{
					echo("<TD>");
					// Now get the values to aggregate in an array
					unset($aggdata);
					foreach($stulist AS $astu)
					{
						$aggdata[$astu->get_id()] = $vitem->get_value($astu);
						if($aggdata[$astu->get_id()] == "")
							unset($aggdata[$astu->get_id()]);
					}
					if($aggtype == "AVG")
					{
						if(count($aggdata) > 0)
							echo(array_sum($aggdata) / count($aggdata));
						else
							echo("-");
					}
					else if($aggtype == "SUM")
					{
						echo(array_sum($aggdata));
					}
					else if($aggtype == "COUNT")
					{
						echo(count($aggdata));
					}
					else if($aggtype == "MAX")
					{
						echo(max($aggdata));
					}
					else if($aggtype == "MIN")
					{
						echo(min($aggdata));
					}
					else if($aggtype == "MEDIAN")
					{
						echo(array_median($aggdata));
					}
					else if($aggtype == "MODUS")
					{
						echo(array_modus($aggdata));
					}
					else
						echo($aggtype);
					echo("</td>");					
				}
				else
					echo("<td>&nbsp;</td>");
			}
			echo("</TR>");
		}

		echo("</table>");
	}
	
	protected function view_tableheader()
	{
		$itemlist = $this->get_items();
		echo("<table border=1 cellpadding=1 style='page-break-after : always;'><TR>");
		foreach($itemlist AS $vitem)
			echo("<TH>". $vitem->get_label(). "</th>");
		echo("</tr>");		
	}
	
	public function edit()
	{
		$dtext = $_SESSION['dtext'];

		// Forms and Javascript for up, down and remove of items
		echo("<form method=post name=formupsovitem id=formupsovitem><input type=hidden name=dupsovitem id=dupsovitem><input type=hidden name=stuviewedit value=". $this->viewid. "></form>");
		echo("<form method=post name=formdownsovitem id=formdownsovitem><input type=hidden name=ddownsovitem id=ddownsovitem><input type=hidden name=stuviewedit value=". $this->viewid. "></form>");
		echo("<form method=post name=formdeletesovitem id=formdeletesovitem><input type=hidden name=ddeletesovitem id=ddeletesovitem><input type=hidden name=stuviewedit value=". $this->viewid. "></form>");
		echo("<SCRIPT>");
		echo(" ");
		echo(" function upsovitem(seqno) { document.getElementById('dupsovitem').value=seqno; document.getElementById('formupsovitem').submit(); }");
		echo(" function downsovitem(seqno) { document.getElementById('ddownsovitem').value=seqno; document.getElementById('formdownsovitem').submit(); }");
		echo(" function deletesovitem(seqno) { document.getElementById('ddeletesovitem').value=seqno; document.getElementById('formdeletesovitem').submit(); }");
		echo(" </script>");

    echo("<font size=+2>" . $dtext['tpage_studentoverviews'] . "</font><BR>". $dtext['studentviewEditIntro']. "<BR><BR>". $dtext['ViewDescription']. " : ");
		if($this->viewid == 0 && isset($_SESSION['newviewediting']))
		{
			$this->viewid = $_SESSION['newviewediting'];
			$_POST['stuviewedit'] = $this->viewid;
		}
		else
			unset($_SESSION['newviewediting']);

		/*// debug: show all post fields
		foreach($_POST AS $pkey => $pval)
			echo("POST key ". $pkey. "=". $pval. "<BR>");
		if(isset($_SESSION['newviewediting']))
			echo("New view editing=". $_SESSION['newviewediting']); */
		
		$this->edit_description();
		if($this->viewid != 0)
		{
			echo("<BR>". $dtext['R_acc']. " : ");
			$this->edit_protect();
			
			echo("<table border=1 celpadding=1><TR><TH>". $dtext['Label']. "</th><TH>". $dtext['Filter']. "</th><TH>". $dtext['SortSequence']. "</th><TH>". $dtext['Aggregation']. "</th></tr>");
			$itemlist = $this->get_items();
			if(isset($itemlist))
			{
				foreach($itemlist AS $vitem)
				{
					$vitem->edit();
				}
			}
			$newvitem = new StudentOverviewItem($this->viewid);
			$newvitem->edit();
			echo("<BR><BR></table>");	
		}
	}
	
	public function edit_protect()
	{
		$ovfld = new inputclass_listfield("stovp". $this->viewid,self::get_protect_query(),NULL,"protect","studentview",$this->viewid,"viewid",NULL,"datahandler.php");
		$ovfld->echo_html();		
	}
	
	public function remove()
	{
		mysql_query("DELETE FROM studentviewitems WHERE viewid=". $this->viewid);
		mysql_query("DELETE FROM studentview WHERE viewid=". $this->viewid);
	}
	 
	public static function list_overviews($teacher)
	{
		$slq = "SELECT viewid FROM studentview WHERE tid=". $_SESSION['uid']. " OR protect = 'T' OR protect = 'A'";
		if($teacher->has_role("mentor") || $teacher->has_role("counsel"))
			$slq .= " OR protect='M'";
		if($teacher->has_role("counsel"))
			$slq .= " OR protect='C'";
		if($teacher->has_role("office"))
			$slq .= " OR protect='O'";
		if($teacher->has_role("mentor") || $teacher->has_role("office"))
			$slq .= " OR protect='P'";
		$slq .= " ORDER BY description";
		$slqr = inputclassbase::load_query($slq);
		if(isset($slqr['viewid']))
		{ // results found, now convert into list
			foreach($slqr['viewid'] AS $viewid)
				$retlst[$viewid] = new StudentOverview($viewid);
			return($retlst);
		}
		else
			return NULL;
	}

  public static function get_protect_query()
  {
		$q = "";
		$prottab = array("A"=>$_SESSION['dtext']['allow_all_short'],"T"=>$_SESSION['dtext']['allow_teach_short'],"M"=>$_SESSION['dtext']['allow_ment_short'],"C"=>$_SESSION['dtext']['allow_couns_short'],"N"=>$_SESSION['dtext']['allow_none'],"O"=>$_SESSION['dtext']['Office_admin'],"P"=>$_SESSION['dtext']['allow_ment_office']);
		foreach($prottab AS $ptix => $pttxt)
			$q .= " UNION SELECT '". $ptix. "' as id,\"". $pttxt. "\" as tekst";
		return substr($q,7);
  }
	
	protected function get_items()
	{
		$vitemsqr = inputclassbase::load_query("SELECT seqno FROM studentviewitems WHERE viewid=". $this->viewid. " ORDER BY seqno");
		if(isset($vitemsqr['seqno']))
		{
			foreach($vitemsqr['seqno'] AS $vseq)
				$retlst[$vseq] = new StudentOverviewItem($this->viewid,$vseq);
			return($retlst);
		}
		else
			return NULL;			
	}

	 function download()
	{
		$dtext = $_SESSION['dtext'];
		$I = new teacher($_SESSION['uid']);
			if(isset($_POST['stuvgroupsel']))
				$this->download_execute($I);
			
		$itemlist = $this->get_items();
		if(isset($itemlist))
		{
			$eligebility = 2; // 0 is none (no field), 1 is own groups only, 2 is all groups
			foreach($itemlist AS $vitem)
			{
				$ie = $vitem->get_eligebility($I);
				if($ie < $eligebility)
					$eligebility = $ie;
			}
		}
		else
			$eligebility = 0;
		if($eligebility == 0)
			echo("<font color=red>". $dtext['FieldError']. "</font>");
		else if(!isset($_POST['stuvgroupsel']))
		{
			echo("<BR><BR><form id=downdetform method=post ACTION=StudentOverviews.php><input type=hidden name=stuviewdown value=". $this->viewid. ">");
			echo("<SELECT name=stuvgroupsel onChange='document.getElementById(\"downdetform\").submit();'><OPTION value=0> </option>");
			echo("<OPTION value=1>". $dtext['CurrentGroup']. "</option>");
			if($ie == 2)
				echo("<OPTION value=2>". $dtext['AllGroups']. "</option>");
			echo("</SELECT>");
			echo("<BR><INPUT TYPE=SUBMIT></FORM>");
		}
	}
	
	public function download_execute($I)
	{
		$this->download_tableheader();
		$itemlist = $this->get_items();
		$stulistq = "SELECT DISTINCT sid FROM sgrouplink LEFT JOIN sgroup USING(gid) LEFT JOIN student USING(sid)";
		foreach($itemlist AS $vitem)
			$stulistq .= $vitem->link_filter();
		if($_POST['stuvgroupsel'] == 1) // Only current group
			$stulistq .= " WHERE groupname='". $_SESSION['CurrentGroup']. "'";
		else if($I->has_role("admin") || $I->has_role("office") || $I->has_role("counsel")) // All groups, no exception
			$stulistq .= " WHERE gid IS NOT NULL";
		else // Only groups the teacher is teaching!
		{
			$mygrpsqr = inputclassbase::load_query("SELECT DISTINT gid FROM class WHERE tid=". $_SESSION['uid']);
			$mygrps[0] = 0;
			if(isset($mygrpsqr['gid']))
				foreach($mygrpsqr['gid'] AS $agid)
					$mygrps[$agid] = $agid;
			$mygrpr = implode(",",$mygrps);
			$stulistq .= " WHERE gid IN (". $mygrps. ")";
		}
		foreach($itemlist AS $vitem)
			$stulistq .= $vitem->pre_filter();
		
		$stulistq .= " ORDER BY ";
		$sortdqr = inputclassbase::load_query("SELECT seqno FROM studentviewitems WHERE viewid=". $this->viewid. " AND sortseq IS NOT NULL AND sortseq > 0 ORDER BY sortseq");
		if(isset($sortdqr['seqno']))
			foreach($sortdqr['seqno'] AS $sseqno)
				$stulistq .= $itemlist[$sseqno]->pre_order();
		$stulistq .= "sid";
		$stulistqr = inputclassbase::load_query($stulistq);
		if(isset($stulistqr['sid']))
			foreach($stulistqr['sid'] AS $asid)
				$stulist[$asid] = new student($asid);
		
		foreach($itemlist AS $vitem)
			$stulist = $vitem->post_filter($stulist);
		
		// Now we got a list of student that needs to be shown in the sequence required
		if(isset($stulist) && count($stulist) > 0)
		{ // And there really are students!
			foreach($stulist AS $astud)
			{
				foreach($itemlist AS $vitem)
				{
					echo("\"". $this->content_2_csv($vitem->get_value($astud)). "\"\t");
				}
				echo("\r\n");
			}		
		}
	}
		
	protected function content_2_csv($indata)
	{
		$outdata = str_replace("\"","\"\"",$indata);
		$outdata = str_replace("</td><td>", " | ",$outdata);
		$outdata = str_replace("</tr><tr>", "\r\n",$outdata);
		$outdata = strip_tags($outdata);
		$outdata = iconv("UTF-8", "ISO-8859-1//TRANSLIT",$outdata);
		return($outdata);
	}
	
	protected function download_tableheader()
	{
		$itemlist = $this->get_items();
		foreach($itemlist AS $vitem)
			echo("\"". $vitem->get_label(). "\"\t");
		echo("\r\n");		
	}
	

}

class StudentOverviewItem
{
	protected $viewid;
	protected $seqno;
  public function __construct($viewid = NULL,$seqno = NULL)
  {
    if(isset($viewid))
			$this->viewid = $viewid;
		else
			$this->viewid = 0;
		
		if(isset($seqno))
			$this->seqno = $seqno;
  }
  
  public function get_id()
  {
    return $this->viewid;
  }
  public function get_seqno()
  {
    return $this->seqno;
  }
	
	public function edit_field()
	{
		if($this->seqno == 0)
		{
			// Get the highest seq no appearing, new item must have highest + 1 or just 1
			$maxsqqr = inputclassbase::load_query("SELECT MAX(seqno) AS msq FROM studentviewitems WHERE viewid=". $this->viewid);
			if(isset($maxsqqr['msq']))
				$newsq = $maxsqqr['msq'][0] + 1;
			else
				$newsq = 1;
			$itemfld = new inputclass_listfield("stovni",$this->get_itemquery(),NULL,"fieldname","studentviewitems",0,"seqno",NULL,"datahandler.php");
			$itemfld->set_extrakey("viewid",$this->viewid);
			$itemfld->set_extrafield("seqno",$newsq);
			$itemfld->echo_html();
		}
		else
		{
			$itemfld = new inputclass_listfield("stovi". $this->seqno,$this->get_itemquery(),NULL,"fieldname","studentviewitems",$this->seqno,"seqno",NULL,"datahandler.php");
			$itemfld->set_extrakey("viewid",$this->viewid);
			$itemfld->echo_html();
		}
	}
	
	public function edit_filter()
	{
		if($this->seqno == 0)
			echo(" "); // Empty field for new entry!
		else
		{
			$itemfld = new inputclass_textfield("stovf". $this->seqno,20,NULL,"filter","studentviewitems",$this->seqno,"seqno",NULL,"datahandler.php");
			$itemfld->set_extrakey("viewid",$this->viewid);
			$itemfld->echo_html();
		}
	}
	
	public function edit_sort()
	{
		if($this->seqno == 0)
			echo(" "); // Empty field for new entry!
		else
		{
			// Sort sequence depends on what items have already been used...
			$usedssqr = inputclassbase::load_query("SELECT sortseq FROM studentviewitems WHERE viewid=". $this->viewid. " AND sortseq IS NOT NULL AND sortseq > 0 AND seqno <>". $this->seqno);
			if(isset($usedssqr['sortseq']))
				foreach($usedssqr['sortseq'] AS $uss)
					$ussar[$uss] = 1;
			$sortquery = "SELECT 0 AS id, '' AS tekst";
			for($ckss = 1; $ckss < 6; $ckss++)
			{
				if(!isset($ussar[$ckss]))
					$sortquery .= " UNION SELECT ". $ckss. ",". $ckss;
			}
			
			$itemfld = new inputclass_listfield("stovss". $this->seqno,$sortquery,NULL,"sortseq","studentviewitems",$this->seqno,"seqno",NULL,"datahandler.php");
			$itemfld->set_extrakey("viewid",$this->viewid);
			$itemfld->echo_html();	
		}
	}
	
	public function edit_aggregate()
	{
		$dtext = $_SESSION['dtext'];
		if($this->seqno == 0)
			echo(" "); // Empty field for new entry!
		else
		{
			// Sort sequence depends on what items have already been used...
			$usedssqr = inputclassbase::load_query("SELECT sortseq FROM studentviewitems WHERE viewid=". $this->viewid. " AND sortseq IS NOT NULL AND sortseq > 0 AND seqno <>". $this->seqno);
			if(isset($usedssqr['sortseq']))
				foreach($usedssqr['sortseq'] AS $uss)
					$ussar[$uss] = 1;
			$agquery = "SELECT '' AS id, '' AS tekst UNION SELECT 'AVG','". $dtext['Agg_AVG']. "' UNION SELECT 'SUM','". $dtext['Agg_SUM']. "' UNION SELECT 'MAX','". $dtext['Agg_MAX']. "' UNION SELECT 'MIN','". $dtext['Agg_MIN']. "' UNION SELECT 'COUNT','". $dtext['Agg_COUNT']. "' UNION SELECT 'MODUS','". $dtext['Agg_MODUS']. "' UNION SELECT 'MEDIAN','". $dtext['Agg_MEDIAN']. "'";
			
			$itemfld = new inputclass_listfield("stovag". $this->seqno,$agquery,NULL,"aggregate","studentviewitems",$this->seqno,"seqno",NULL,"datahandler.php");
			$itemfld->set_extrakey("viewid",$this->viewid);
			$itemfld->echo_html();	
		}
	}
	
	protected function get_itemquery()
	{ // This is what gives all possible items, meaning:
		// all studentdetails that are not marked as as "Nobody", unless this is the administrator, then all items count
		// If this item currently active is not in the list we add it!
		// Further for all subjects we add all periods and an end periods
		$dtext=$_SESSION['dtext'];
		$retqry = "SELECT '". $this->get_item(). "' AS id, '". $this->get_label(). "' AS tekst";
		$I = new teacher($_SESSION['uid']);
		$detq = "SELECT table_name,label FROM student_details";
		if(!$I->has_role("admin"))
			$detq .= " WHERE raccess <> 'N'";
		$detq .= " ORDER BY label";
		$detqr = inputclassbase::load_query($detq);
		foreach($detqr['table_name'] AS $dix => $detn)
			$retqry .= " UNION SELECT '". $detn. "' AS id, '". $detqr['label'][$dix]. "' AS tekst";
		// Now add the subjects
		$periodsqr = inputclassbase::load_query("SELECT id FROM period ORDER BY id");
		$subsqr = inputclassbase::load_query("SELECT mid,fullname FROM subject ORDER BY fullname");
		foreach($subsqr['mid'] AS $mix => $mid)
		{
			$retqry .= " UNION SELECT '#mid-". $mid. "-0', '". $subsqr['fullname'][$mix]. " ". $dtext['Final']. "'";
			foreach($periodsqr['id'] AS $pid)
				$retqry .= " UNION SELECT '#mid-". $mid. "-". $pid. "', '". $subsqr['fullname'][$mix]. " ". $pid. "'";			
		}
		// Now add the queryfields
		$qflist = QueryField::get_qfields();
		if(isset($qflist))
			foreach($qflist AS $qfid => $qfobj)
			{
				$retqry .= " UNION SELECT '#query-". $qfid. "','". $qfobj->get_fieldname(). "'";
			}
		return $retqry;
	}
	
	public function edit()
	{
		echo("<TR>");
		echo("<td>");
		$this->edit_field();
		echo("</td><td>");
		$this->edit_filter();
		echo("</td><td>");
		$this->edit_sort();
		echo("</td><td>");
		$this->edit_aggregate();
		echo("</td><td>");
		if($this->seqno == 0)
			echo("<img src='PNG/action_add.png'>");
		else
		{
			if($this->seqno != 1) // No way to move the first item up...
				echo("<img src='PNG/arrow_top.png' onclick='upsovitem(". $this->seqno. ");'>");
			// See what's the max seqno occurring
			$maxsqqr = inputclassbase::load_query("SELECT MAX(seqno) AS mxq FROM studentviewitems WHERE viewid=". $this->viewid);
			if(!isset($maxsqqr['mxq']) || $maxsqqr['mxq'][0] != $this->seqno)
				echo("<img src='PNG/arrow_down.png' onclick='downsovitem(". $this->seqno. ");'>");				
			echo("<img src='PNG/action_delete.png' onclick='deletesovitem(". $this->seqno. ");'>");
		}
		echo("</td>");
		echo("</tr>");
	}	
	
	public function get_item()
	{
		if(isset($this->seqno) && $this->seqno > 0)
			$itqr = inputclassbase::load_query("SELECT fieldname FROM studentviewitems WHERE viewid=". $this->viewid. " AND seqno = ". $this->seqno);
		if(isset($itqr['fieldname'][0]))
			return($itqr['fieldname'][0]);
		else
			return "";
	}
	
	public function get_label()
	{
		$dtext= $_SESSION['dtext'];
		$myitem = $this->get_item();
		if(substr($myitem,0,1) == "#")
		{ // structures like #mid-<mid>-<period> and #query-<queryid> need to get converted to label!
			if(substr($myitem,0,4) == "#mid")
			{
				$splitmid = explode("-",$myitem);
				$subjqr = inputclassbase::load_query("SELECT fullname FROM subject WHERE mid=". $splitmid[1]);
				if(!isset($subjqr['fullname']))
					return("");
				else
					return($subjqr['fullname'][0]. " ". ($splitmid[2] == 0 ? $dtext['Final'] : $splitmid[2]));
			}
			else if(substr($myitem,0,7) == "#query-")
			{
				$qitem = new QueryField(substr($myitem,7));
				return($qitem->get_fieldname());
			}
		}
		if(isset($myitem) && $myitem != "")
			$itlqr = inputclassbase::load_query("SELECT label FROM student_details WHERE table_name='". $myitem. "'");
		if(isset($itlqr['label'][0]))
			return($itlqr['label'][0]);
		else
			return "";		
	}
	
	public function get_filter()
	{
		$filtqr = inputclassbase::load_query("SELECT filter FROM studentviewitems WHERE viewid=". $this->viewid. " AND seqno=". $this->seqno);
		if(isset($filtqr['filter']))
			return($filtqr['filter'][0]);
		else
			return("");
	}
	
	public function get_sort()
	{
		$filtqr = inputclassbase::load_query("SELECT sortseq FROM studentviewitems WHERE viewid=". $this->viewid. " AND seqno=". $this->seqno);
		if(isset($filtqr['sortseq']))
			return($filtqr['sortseq'][0]);
		else
			return("");
	}
	
	public function get_aggregation()
	{
		$filtqr = inputclassbase::load_query("SELECT aggregate FROM studentviewitems WHERE viewid=". $this->viewid. " AND seqno=". $this->seqno);
		if(isset($filtqr['aggregate']))
			return($filtqr['aggregate'][0]);
		else
			return("");
	}
	
	public function up()
	{
		mysql_query("UPDATE studentviewitems SET seqno=999999 WHERE viewid=". $this->viewid. " AND seqno=". $this->seqno);
		mysql_query("UPDATE studentviewitems SET seqno=seqno+1 WHERE viewid=". $this->viewid. " AND seqno=". ($this->seqno - 1));
		mysql_query("UPDATE studentviewitems SET seqno=". ($this->seqno - 1). " WHERE viewid=". $this->viewid. " AND seqno=". 999999);		
	}
	
	public function down()
	{
		mysql_query("UPDATE studentviewitems SET seqno=999999 WHERE viewid=". $this->viewid. " AND seqno=". $this->seqno);
		mysql_query("UPDATE studentviewitems SET seqno=seqno-1 WHERE viewid=". $this->viewid. " AND seqno=". ($this->seqno + 1));
		mysql_query("UPDATE studentviewitems SET seqno=". ($this->seqno + 1). " WHERE viewid=". $this->viewid. " AND seqno=". 999999);				
	}
	
	public function remove()
	{
		mysql_query("DELETE FROM studentviewitems WHERE viewid=". $this->viewid. " AND seqno=". $this->seqno);
		mysql_query("UPDATE studentviewitems SET seqno=seqno-1 WHERE viewid=". $this->viewid. " AND seqno > ". $this->seqno);
	}
	
	public function get_eligebility($I)
	{
		$fieldname = $this->get_item();
		if(substr($fieldname,0,1) == "#")
			return 2; // it's a grade so always eligeble
		if(isset($fieldname) && $fieldname != "")
		{
			// Get the read access for this field
			$raqr = inputclassbase::load_query("SELECT raccess FROM student_details WHERE table_name='". $fieldname. "'");
			if(isset($raqr['raccess']))
			{
				$ac = $raqr['raccess'][0];
			if($ac == 'A' || $ac == 'T' || ($ac == 'M' && $I->has_role("counsel")) || ($ac == 'C' && $I->has_role("counsel")) || ($ac == 'O' && $I->has_role("office")) || ($ac == 'P' && $I->has_role("office")) || $I->has_role("admin"))
				return(2); // available for all groups
			else if(($ac == 'M' || $ac == 'P') && $I->has_role("mentor"))
				return(1); // Only available for mentor group
			else
				return(0); // Not available
			}
			else
			  return 0; // Not available
		}
		else return 0; // Not available
	}
	
	public function link_filter()
	{
		global $stuovhasgrades;
		$myitem = $this->get_item();
		if(substr($this->get_item(),0,1) != "*" && substr($this->get_item(),0,1) != "#" && ($this->get_filter() != "" || $this->get_sort() != ""))
			return (" LEFT JOIN `". $myitem. "` USING(sid)");
		else
			return("");		
	}
	
	public function pre_filter()
	{ // Prefilter means filtering done in the query. So we do that for everything that has a filter if the item does not start with * or # with the exception of names
		if($this->get_filter() == "")
			return("");
		$myitem = $this->get_item();
		if(substr($myitem,0,1) == "#" || (substr($myitem,0,1) == "*" && $myitem != "*student.lastname" && $myitem != "*student.firstname"))
			return("");
		// Now it is sure we need to put a filter so we elaborate it
		if($myitem == "*student.lastname")
			$fieldname="lastname";
		else if($myitem == "*student.firstname")
			$fieldname="firstname";
		else
			$fieldname = $myitem. ".data";
		// Now we get the filter and elaborate it
		$myfilter = $this->get_filter();
		$splitfilter = explode(",",$myfilter);
		$firstel = true;
		$retstr = " AND (";
		foreach($splitfilter AS $filtel)
		{
			if(!$firstel)
				$retstr .= " OR ";
			$retstr .= $fieldname. " ". $this->translate_filter($filtel);				
			$firstel = false;
		}
		$retstr .= ")";
		return $retstr;
	}
	
	public function get_value($astud)
	{
		$myitem = $this->get_item();
		if(substr($myitem,0,1) == "#")
		{ // request for special result, subject or query...
			if(substr($myitem,0,4) == "#mid")
			{
				$splitmid = explode("-",$myitem);
				$curyearqr = inputclassbase::load_query("SELECT year FROM period". ($splitmid[2] > 0 ? " WHERE id=". $splitmid[2] : ""));
				if(isset($curyearqr['year']))
					$curyear = $curyearqr['year'][0];
				else
					return("");
				$resqr = inputclassbase::load_query("SELECT result FROM gradestore WHERE sid=". $astud->get_id(). " AND year='". $curyear. "' AND mid=". $splitmid[1]. " AND period=". $splitmid[2]);
				if(isset($resqr['result']))
					return $resqr['result'][0];
				else
					return("");
			}
			else if(substr($myitem,0,7) == "#query-")
			{
				$qfld = new QueryField(substr($myitem,7));
				return $qfld->get_value($astud->get_id());
			}
		}
		else
		{
			$myval = $astud->get_student_detail($myitem);
			// For *absence.* and *grouphistory need to remove table rows by filter
			if(($myitem == "*absence.*" || $myitem == "*grouphistory.*") && $this->get_filter() != "")
			{
				$splitrecs = explode("<tr>",$myval);
				$splitfilt = explode(",",$this->get_filter());
				foreach($splitrecs AS $spkey => $prtval)
				{
					if($spkey > 1) // Skip the heading row
					{
						$passthru = false;
						foreach($splitfilt AS $filtel)
							if($this->tell_postfilter($prtval,$filtel))
								$passthru = true;
						if(!$passthru)
							$splitrecs[$spkey] = substr($prtval,strpos($prtval,"</tr>") + 5);
					}
				}
				$myval = implode("<tr>",$splitrecs);
			}
			// For *sgroup.groupname need to remove rows by filter
			if($myitem == "*sgroup.groupname" && $this->get_filter() != "")
			{
				$splitrecs = explode("<BR>",$myval);
				$splitfilt = explode(",",$this->get_filter());
				foreach($splitrecs AS $spkey => $prtval)
				{
					$passthru = false;
					foreach($splitfilt AS $filtel)
						if($this->tell_postfilter($prtval,$filtel))
							$passthru = true;
					if(!$passthru)
						unset($splitrecs[$spkey]);
				}
				$myval = implode("<BR>",$splitrecs);
			}
			return $myval;
		}
	}
	
	protected function translate_filter($filtstr)
	{ // Here we translate filters for mysql use.
		// if < or > is used, this will be a numeric intrepretation, = will be interpreted as an exact specification, wildcards are also detected and in nothing, wildcards are put before and after
		$fc = substr($filtstr,0,1);
		if($fc == "=")
			return("='". substr($filtstr,1). "'");
		else if($fc == "<")
			return("<'". substr($filtstr,1). "'");			
		else if($fc == ">")
			return(">'". substr($filtstr,1). "'");
		else
		{ // here we intrepret wildcards first, if none put wildcards around it
			if(strpos($filtstr,'_') !== FALSE || strpos($filtstr,'%') !== FALSE)
			{ // String contains wildcards, so use it as is
				return("LIKE '". $filtstr. "'");
			}
			else // Add ildcards before and after
				return("LIKE '%". $filtstr. "%'");
		}		
	}
	
	public function pre_order()
	{
		$myitem = $this->get_item();
		if(substr($myitem,0,1) == "#" || (substr($myitem,0,1) == "*" && $myitem != "*student.lastname" && $myitem != "*student.firstname"))
			return(""); // These items can not be pre sorted
		if($myitem == "*student.lastname")
			return("lastname,");
		if($myitem == "*student.firstname")
			return("firstname,");
		else
			return($myitem. ".data,");
	}
	
	public function post_filter($stulist)
	{
		$myitem = $this->get_item();
		if(substr($myitem,0,1) == "#" || (substr($myitem,0,1) == "*" && $myitem != "*student.lastname" && $myitem != "*student.firstname"))
		{ // Postfiltering might be needed
			if($this->get_filter() != "")
			{ // And a filter is defined
				$splitfilt = explode(",", $this->get_filter());
				foreach($stulist AS $stu)
				{
					$stuval = $this->get_value($stu);
					$passthru = false;
					foreach($splitfilt AS $filtel)
						if($this->tell_postfilter($stuval,$filtel))
							$passthru = true;
					if(!$passthru)
						unset($stulist[$stu->get_id()]);
				}
			}
		}
		return($stulist);
	}
	
	public function tell_postfilter($myval,$filtel)
	{
		if(substr($filtel,0,1) == "<")
			return($myval < substr($filtel,1));
		if(substr($filtel,0,1) == ">")
			return($myval > substr($filtel,1));
		if(substr($filtel,0,1) == "=")
			return($myval == substr($filtel,1));
		if(strpos($filtel,'_') === FALSE && strpos($filtel,'%') === FALSE)
		{ // No wildcards, so be just need to find out if filter is contained in value
			if(strpos($myval,$filtel) !== FALSE)
				return true;
		}
		else
		{ // Wildcard in filterstring, we need to do a regexp check
			/*$regexp = str_replace("_",".",$filtel);
			$regexp = str_replace("%",".*",$regexp); 
			echo($regexp. "<BR>");
			if(preg_match("/". $regexp. "/",$myval) !== FALSE)
				return true; */
			$fnexp = str_replace("_","?",$filtel);
			$fnexp = str_replace("%","*",$fnexp);
			if(fnmatch($fnexp,$myval))
				return true;
			//else
			//	echo($regexp. " no match for ". $myval. "<BR>");
		}		
		return false;
	}
}

class QueryField
{
	protected $fieldid;
  public function __construct($fieldid = NULL)
  {
    if(isset($fieldid))
			$this->fieldid = $fieldid;
		else
			$this->fieldid = 0;
  }
	
	public function get_fieldname()
	{
		if($this->fieldid != NULL)
		{
			$fnq = inputclassbase::load_query("SELECT fieldname FROM queryfield WHERE fieldid=". $this->fieldid);
			return $fnq['fieldname'][0];
		}
		else
			return NULL;
	}
	
	static public function get_qfields()
	{
		$qfieldsqr = inputclassbase::load_query("SELECT fieldid FROM queryfield ORDER BY fieldname");
		if(isset($qfieldsqr['fieldid']))
		{
			foreach($qfieldsqr['fieldid'] AS $qfid)
				$retarr[$qfid] = new QueryField($qfid);
			return $retarr;
		}
		else
			return NULL;
	}
	
	public function edit_fieldname()
	{
			$itemfld = new inputclass_textfield("qffname". $this->fieldid,40,NULL,"fieldname","queryfield",$this->fieldid,"fieldid",NULL,"datahandler.php");
			$itemfld->echo_html();		
	}
	
	public function edit_query()
	{
			$itemfld = new inputclass_textarea("qffq". $this->fieldid,"60,*",NULL,"fquery","queryfield",$this->fieldid,"fieldid",NULL,"datahandler.php");
			$itemfld->echo_html();		
		
	}
	
	public function edit_qfield()
	{
		echo("<TR><TD>");
		$this->edit_fieldname();
		echo("</td><td>");
		$this->edit_query();
		echo("</td></tr>");
	}
	
	static public function edit_qfields()
	{
		$dtext = $_SESSION['dtext'];
    echo("<p><font size=+2>" . $dtext['tpage_studentoverviews'] . "</font></p>");
    echo("<p>" . $dtext['EditQfieldsExpl'] . "</p>");		
		$existfields = self::get_qfields();
		echo("<table><tr><th>". $dtext['Fieldname']. "</th><th>". $dtext['Query']. "</th></tr>");
		if(isset($existfields))
			foreach($existfields AS $qfieldobj)
				$qfieldobj->edit_qfield();
		$newfield = new QueryField(0);
			$newfield->edit_qfield();
		echo("</table>");
	}
	
	public function get_value($sid)
	{
		$qqr = inputclassbase::load_query("SELECT fquery FROM queryfield WHERE fieldid=". $this->fieldid);
		if(isset($qqr['fquery']))
		{
			$rawq = $qqr['fquery'][0];
			$trueq = str_replace("{sid}",$sid,$rawq);
			$qres = inputclassbase::load_query($trueq);
			if(isset($qres['data']))
				return($qres['data'][0]);
			else
				return("No result from query");
		}
		else // No query found???
			return("No query found");
	}
}

function array_median($array) 
{
  // perhaps all non numeric values should filtered out of $array here?
  $iCount = count($array);
  if ($iCount == 0)
	{
    return("-");
  }
  // if we're down here it must mean $array
  // has at least 1 item in the array.
  $middle_index = floor($iCount / 2);
  sort($array, SORT_NUMERIC);
  $median = $array[$middle_index]; // assume an odd # of items
  // Handle the even case by averaging the middle 2 items
  if ($iCount % 2 == 0)
	{
    $median = ($median + $array[$middle_index - 1]) / 2;
  }
  return $median;
}
function array_modus($array) 
{
  // perhaps all non numeric values should filtered out of $array here?
  $iCount = count($array);
  if ($iCount == 0)
	{
    return("-");
  }
  // if we're down here it must mean $array
  // has at least 1 item in the array.
	// Now make a frequency array
	foreach($array AS $aval)
	{
		if(!isset($freqarray[$aval]))
			$freqarray[$aval] = 1;
		else
			$freqarray[$aval]++;
	}
  asort($freqarray, SORT_NUMERIC);
	// Now pass through the array and see if the last item is indeed a unique highest number
	$prevval = 0;
	$isunique = false;
	foreach($freqarray AS $fkey => $fval)
	{
		$retval = $fkey;
		$isunique = $fval == $prevval;
		$prevval = $fval;
	}
	if($isunique)
		return $retval;
	else
		return("-");
}
?>
