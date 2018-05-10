<?php
/* vim: set expandtab tabstop=2 shiftwidth=2: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2015 Aim4me N.V.   (http://www.aim4me.com)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
	require_once("teacher.php");
  require_once("inputlib/inputclasses.php");
	require_once("displayelements/extendableelement.php");
	require_once("student.php");
	require_once("group.php");
	
  class OLlist extends extendableelement
  {
		protected $year;
		public function __construct($year, $divid = NULL, $style = NULL, $contents = NULL)
	  {
			$this->year = $year;
			parent::__construct($divid, $style, $contents);
			// A global $OLprefix is used to prefix the design database tables, if not set, assume empty string.
			global $OLprefix;
			if(!isset($OLprefix))
			$OLprefix = "";
			
			// Create tables if do not exist
			$sqlquery = "CREATE TABLE IF NOT EXISTS ". $OLprefix. "OLitems (
			`oldid` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			`subject` TEXT,
			`category` TEXT,
			`itemdescription` TEXT,
			`seqno` INTEGER(11),
			PRIMARY KEY (`oldid`)
				) ENGINE=InnoDB;";
			mysql_query($sqlquery,inputclassbase::$dbconnection);
			echo(mysql_error());
			
			$sqlquery = "CREATE TABLE IF NOT EXISTS `OLresult` (
				`olresid` INTEGER(11) NOT NULL AUTO_INCREMENT,
			`sid` INTEGER(11),
				`oldid` INTEGER(11) UNSIGNED,
			`odate` DATE,
			`year`  VARCHAR(20),
			`result` INTEGER(1) DEFAULT NULL,
			PRIMARY KEY (`olresid`)
				) ENGINE=InnoDB;";
			mysql_query($sqlquery,inputclassbase::$dbconnection);
			echo(mysql_error());
    }
		public function add_contents()
		{
			parent::add_contents();
			$this->set_contents("<H1>Observatielijsten ". $this->year. "</h1>");
			if(!isset($_GET['edit']) && !isset($_GET['design']))
				$this->show_subjecttable();
		}
		public function show_contents()
		{
			parent::show_contents();
			if(isset($_GET['design']))
				$this->edit_design($_GET['design']);
			if(isset($_GET['edit']))
				$this->edit_results($_GET['edit']);
		}
		
		protected function show_subjecttable()
		{ // No list selected, so we show the table with existing subjects and option to add a subject
			$I = new teacher();
			$I->load_current();
			$subjqr = inputclassbase::load_query("SELECT DISTINCT subject FROM OLitems");
			if(isset($subjqr['subject']))
			{
				$contents = "<table><tr><th>Onderwerp</th><th>&nbsp</th>";
				if($I->has_role("admin"))
					$contents .= "<th>&nbsp;</th>";
				$contents .= "</tr>";
				foreach($subjqr['subject'] AS $osubj)
				{
					$contents .= "<tr><td>". $osubj. "</td><td><a href='". $_SERVER['PHP_SELF']. "?edit=". $osubj. "'><img src='PNG/reply.png'></a></td>";
					if($I->has_role("admin"))
						$contents .= "<td><a href='". $_SERVER['PHP_SELF']. "?design=". $osubj. "'><img src='PNG/application.png'></a></td>";
					$contents .= "</tr>";
				}
				$contents .= "</table>";
			}
			else
				$contents = "Geen onderwerpen beschikbaar";
			if($I->has_role("admin"))
			{
				$contents .= "<a href='". $_SERVER['PHP_SELF']. "?design=0'>Nieuw onderwerp aanmaken</a>";
			}
			$this->add_element(new extendableelement(NULL,NULL,$contents));
		}
	  
		protected function edit_design($osubject)
		{
			if($osubject == "0")
			{ // New design
				// Create a record in the database
				mysql_query("INSERT INTO OLitems (subject,category,itemdescription,seqno) VALUES('Nieuw onderwerp','Eerste categorie','Eerste item',1)", inputclassbase::$dbconnection);
				$osubject = "Nieuw onderwerp";
			}
			echo("<a href='". $_SERVER['PHP_SELF']. "'>Terug naar de lijst</a><BR>");
			echo("Onderwerp: ");
			// Now we need to find the first item available
			$catlist = OLcategory::list_category($osubject);
			// Now create a show a field to change the subject name
			$sjnamefld = new inputclass_textfield("olsubjectname",40,NULL,"subject","OLitems",reset($catlist)->get_firstitemid(),"oldid");
			$sjnamefld->echo_html();
			// Now list the categories with items(editable)
			$seqoff = 1;
			foreach($catlist AS $acat)
			{
				$acat->design_cat();
			}
			// Add a field to add a new category
			$newcat = new OLcategory($osubject);
			$newcat->design_cat();
		}	

		protected function edit_results($osubject)
		{
			echo("<a href='". $_SERVER['PHP_SELF']. "'>Terug naar de lijst</a><BR>");
			echo("Onderwerp: ". $osubject);
			// Show the header
			echo("<table class=resulttable><tr><th>Leeling</th>");
			$catlist = OLcategory::list_category($osubject);
			foreach($catlist AS $acat)
			{
				echo("<th>". $acat->get_name(). "</th>");
			}
			echo("</tr>");
			$studlist = student::student_list();
			$odates = $this->get_observationdates($osubject);
			$oresults = $this->get_prevobservationresults($osubject);
			$nresults = $this->get_newobservationresults($osubject);
			$negix = -1;
			foreach($studlist AS $astudent)
			{
				if(isset($astudent))
				{
					echo("<tr><td>". $astudent->get_name(). "</td>");
					foreach($catlist AS $acat)
					{
						echo("<td>");
						$negix = $acat->edit_results($astudent->get_id(),$odates, $oresults, $nresults,$negix,$this->year);
						echo("</td>");
					}					
					echo("</tr>");
				}
			}
			echo("</table>");
			$b1 = new TriStateBox("test1",0,NULL,"result","OLresult",-100000,"olresid", $style = NULL,$_SERVER['REQUEST_URI'],"sid",100);
			$b1->put_script();
		}	
		
		protected function get_observationdates($osubject)
		{
			$grpobj = new group();
			$grpobj->load_current();
			$gid = $grpobj->get_id();
			$datesq = "SELECT DISTINCT odate FROM OLresult LEFT JOIN sgrouplink USING(sid) LEFT JOIN OLitems USING(oldid) WHERE gid=". $gid. " AND subject=\"". $osubject. "\" AND year='". $this->year. "' AND odate < DATE(NOW()) ORDER BY odate DESC";
			$datesqr = inputclassbase::load_query($datesq);
			if(isset($datesqr['odate']))
			{
				foreach($datesqr['odate'] AS $dix => $adate)
				  $odates[$dix]=inputclassbase::mysqldate2nl($adate);
				return($odates);
			}
			else
				return NULL;
		}
		
		protected function get_prevobservationresults($osubject)
		{
			$grpobj = new group();
			$grpobj->load_current();
			$gid = $grpobj->get_id();
			$resq = "SELECT sid,oldid,result,odate FROM OLresult LEFT JOIN sgrouplink USING(sid) LEFT JOIN OLitems USING(oldid) WHERE gid=". $gid. " AND subject=\"". $osubject. "\" AND year='". $this->year. "' AND odate < DATE(NOW())";
			$resqr = inputclassbase::load_query($resq);
			if(isset($resqr['result']))
			{
				foreach($resqr['result'] AS $rix => $res)
				  $resdata[$resqr['sid'][$rix]][$resqr['oldid'][$rix]][inputclassbase::mysqldate2nl($resqr['odate'][$rix])]=$res;
				return($resdata);
			}
			else
				return NULL;
		}
		
		protected function get_newobservationresults($osubject)
		{
			$grpobj = new group();
			$grpobj->load_current();
			$gid = $grpobj->get_id();
			$resq = "SELECT sid,oldid,result,olresid FROM OLresult LEFT JOIN sgrouplink USING(sid) LEFT JOIN OLitems USING(oldid) WHERE gid=". $gid. " AND subject=\"". $osubject. "\" AND year='". $this->year. "' AND odate = DATE(NOW())";
			$resqr = inputclassbase::load_query($resq);
			if(isset($resqr['olresid']))
			{
				foreach($resqr['olresid'] AS $rix => $res)
				  $resdata[$resqr['sid'][$rix]][$resqr['oldid'][$rix]]=$res;
				return($resdata);
			}
			else
				return NULL;
		}
		
		static function handle_input()
		{
			if(substr($_POST['fieldid'],0,9) == "olcatname")
			{ // Must rename all items with the same subject and catname
		    $itemid = substr($_POST['fieldid'],9);
				$itemqr = inputclassbase::load_query("SELECT subject,category FROM OLitems WHERE oldid=". $itemid);
				$catname=$itemqr['category'][0];
				$subjname=$itemqr['subject'][0];
				mysql_query("UPDATE OLitems SET category=\"". $_POST[$_POST['fieldid']]. "\" WHERE subject=\"". $subjname. "\" AND category=\"". $catname. "\" AND oldid<>". $itemid, inputclassbase::$dbconnection);
			}
			if(substr($_POST['fieldid'],0,13) == "olsubjectname")
			{ // Must rename all items with the same subject and catname
		    $itemid = $_SESSION['inputobjects']['olsubjectname']->get_key();
				$itemqr = inputclassbase::load_query("SELECT subject FROM OLitems WHERE oldid=". $itemid);
				$subjname=$itemqr['subject'][0];
				mysql_query("UPDATE OLitems SET subject=\"". $_POST[$_POST['fieldid']]. "\" WHERE subject=\"". $subjname. "\" AND oldid<>". $itemid, inputclassbase::$dbconnection);
			}
		}
  }
	
	class OLcategory
	{
	  protected $catname;
		protected $itemlist;
		protected $subjname;
		protected $firstseqno,$lastseqno;
		
		public function __construct($subjname,$catname = "")
		{
			$this->subjname = $subjname;
			$this->catname = $catname;
			// Get the first and last seqno
			if($catname != "")
			{
				$seqqr = inputclassbase::load_query("SELECT MIN(seqno) AS first, MAX(seqno) AS last FROM OLitems WHERE subject=\"". $this->subjname. "\" AND category=\"". $catname. "\"");
				$this->firstseqno = $seqqr['first'][0];
				$this->lastseqno = $seqqr['last'][0];
			}
			else
			{
        $this->firstseqno = 1000 * count(OLcategory::list_category($this->subjname));
				$this->lastseqno = $this->firstseqno;
			}
		}
		
		public function get_firstitemid()
		{
			$this->list_items();
			if(isset($this->itemlist))
			  return(reset($this->itemlist)->get_id());
			else
				return 0;
		}
		
		public function get_name()
		{
			return $this->catname;
		}
		
		public function design_cat()
		{
			echo("<BR><SPAN class=catclasslabel>". ($this->catname != "" ? "Categorie" : "Nieuwe categorie"). " </SPAN><SPAN class=catclass>");
			$catfld = new inputclass_textfield(($this->catname != "" ? "olcatname". $this->get_firstitemid() : "newolcatname"),40,NULL,"category","OLitems",$this->get_firstitemid(),"oldid");
			if($this->catname == "")
			{
				$catfld->set_extrafield("subject",$this->subjname);
				$catfld->set_extrafield("itemdescription","Nieuw item");
				// The first sequence number for a new category depends on how many categories already exist for the subject
				$seqoff = 1 + $this->lastseqno;
				$catfld->set_extrafield("seqno",$seqoff);				
			}
			$catfld->echo_html();
			echo("</SPAN>");
			if($this->catname == "")
			  echo("<img src='PNG/action_add.png'>");
			else
			{
			  echo("<img src='PNG/action_delete.png' onClick='delete_cat(\"". $this->get_firstitemid(). "\");'>");
				if($this->firstseqno > 1000)
					echo("<img src='PNG/arrow_top.png' onClick='up_cat(\"". $this->get_firstitemid(). "\");'>");
				foreach($this->itemlist AS $anitem)
					$anitem->design_item();	
				$newitem = new OLitem(NULL,$this->subjname,$this->catname,$this->lastseqno);
				$newitem->design_item();
			}
		}
		
		public function edit_results($sid,$odates, $oresults,$nresults,$negix,$year)
		{
			$this->list_items();
			$firstobj = reset($this->itemlist);
			foreach($this->itemlist AS $anitem)
			{
				if($anitem != $firstobj)
					echo("<BR>");
				$negix = $anitem->edit_results($sid,$odates, $oresults,$nresults,$negix,$year);
			}
			return $negix;
		}

		public static function list_category($osubject)
		{
			$catqr = inputclassbase::load_query("SELECT DISTINCT category FROM OLitems WHERE subject=\"". $osubject. "\" ORDER BY seqno");
			if(isset($catqr['category']))
		  {
				foreach($catqr['category'] AS $cix => $acat)
				  $retval[$cix] = new OLcategory($osubject,$acat);
			  return($retval);
			}
			else
				return(NULL);
		}
		
		public function list_items()
		{
			if(!isset($this->itemlist))
			{
				$itemqr = inputclassbase::load_query("SELECT oldid FROM OLitems WHERE subject=\"". $this->subjname. "\" AND category=\"". $this->catname. "\" ORDER BY seqno");
				if(isset($itemqr['oldid']))
				{
					foreach($itemqr['oldid'] AS $aoldid)
						$this->itemlist[$aoldid] = new OLitem($aoldid);
				}
			}
			if(isset($this->itemlist))
				return $this->itemlist;
			else
			{
				return(NULL);	
			}
		}
		
		
	}
  class OLitem
  {
		protected $oldid;
		protected $subjname,$catname,$seqafter;
		
		public function __construct($oldid = NULL,$subjname = NULL, $catname = NULL, $seqafter = NULL)
		{
			if(isset($oldid))
			  $this->oldid = $oldid;
			else
			{
				$this->subjname = $subjname;
				$this->catname = $catname;
				$this->seqafter = $seqafter;
			}
		}
		
		public function get_id()
		{
			return $this->oldid;
		}
		
		public function design_item()
		{
			if(isset($this->oldid))
			  $descfld = new inputclass_textfield("itemdesc". $this->oldid,40,NULL,"itemdescription","OLitems",$this->oldid,"oldid");
			else
			{
			  $descfld = new inputclass_textfield("newitemdesc". ($this->seqafter + 1),40,NULL,"itemdescription","OLitems",-1 - $this->seqafter,"oldid");
				$descfld->set_extrafield("subject", $this->subjname);
				$descfld->set_extrafield("category",$this->catname);
				$descfld->set_extrafield("seqno",$this->seqafter+1);
			}
			echo("<BR><SPAN class=itemclasslabel>&nbsp;</SPAN><SPAN class=itemclass>");
			$descfld->echo_html();
			echo("</SPAN>");
			if(!isset($this->oldid))
			{
				echo("<img src='PNG/action_add.png'>");
			}
			else
			{
				echo("<img src='PNG/action_delete.png' onClick='delete_item(". $this->oldid. ");'>");
				$seqqr = inputclassbase::load_query("SELECT seqno FROM OLitems WHERE oldid=". $this->oldid);
				if($seqqr['seqno'][0] % 1000 != 1)
				  echo("<img src='PNG/arrow_top.png' onClick='up_item(". $this->oldid. ");'>");					
			}
		}
		
		public function edit_results($sid,$odates, $oresults,$nresults,$negix,$year)
		{
			if(isset($nresults[$sid][$this->oldid]))
				$tribut = new TriStateBox("tribut_". $sid. "_". $this->oldid,0,NULL,"result","OLresult",$nresults[$sid][$this->oldid],"olresid", $style = NULL,$_SERVER['REQUEST_URI']);
			
			else
			{
				$tribut = new TriStateBox("tribut_". $sid. "_". $this->oldid,0,NULL,"result","OLresult",$negix--,"olresid", $style = NULL,$_SERVER['REQUEST_URI'],"sid",$sid);
				$tribut->set_extrafield("year",$year);
				$tribut->set_extrafield("odate",date("Y-m-d"));
				$tribut->set_extrafield("oldid",$this->oldid);
			}
			$tribut->echo_html();
			if(isset($odates))
			{
				foreach($odates AS $odat)
				{
					if(isset($oresults[$sid][$this->oldid][$odat]) && $oresults[$sid][$this->oldid][$odat] != "")
					{
						if($oresults[$sid][$this->oldid][$odat] == 0)
							echo("<img src='PNG/action_check.png' title='". $odat. "'>");
						else
							echo("<img src='PNG/action_delete.png' title='". $odat. "'>");					
					}
					else
					  echo("<img src='PNG/action_none.png' title='". $odat. "'>");
				}
			}
			$descfld = new inputclass_textfield("itemdesc". $this->oldid,40,NULL,"itemdescription","OLitems",$this->oldid,"oldid");
			echo($descfld->__toString());
			return $negix;
		}
		
		public function delete_category()
		{ // Delete the entire category this item is in.
		  if($this->oldid == 0)
				return;
		  $itemdetsqr = inputclassbase::load_query("SELECT * FROM OLitems WHERE oldid=". $this->oldid);
			$catname = $itemdetsqr['category'][0];
			$subjname = $itemdetsqr['subject'][0];
			$seqno = $itemdetsqr['seqno'][0];
			mysql_query("DELETE FROM OLitems WHERE subject=\"". $subjname. "\" AND category=\"". $catname. "\"", inputclassbase::$dbconnection);
			echo(mysql_error());
			mysql_query("UPDATE OLitems SET seqno=seqno-1000 WHERE subject=\"". $subjname. "\" AND seqno>". $seqno, inputclassbase::$dbconnection);			
			echo(mysql_error());
		}
		public function up_category()
		{ // Move up the entire category this item is in.
		  if($this->oldid == 0)
				return;
		  $itemdetsqr = inputclassbase::load_query("SELECT * FROM OLitems WHERE oldid=". $this->oldid);
			$catname = $itemdetsqr['category'][0];
			$subjname = $itemdetsqr['subject'][0];
			$seqno = $itemdetsqr['seqno'][0];
			//mysql_query("DELETE FROM OLitems WHERE subject=\"". $subjname. "\" AND category=\"". $catname. "\"", inputclassbase::$dbconnection);
			//echo(mysql_error());
			mysql_query("UPDATE OLitems SET seqno=seqno+1000 WHERE subject=\"". $subjname. "\" AND seqno < ". $seqno. " AND seqno >= ". ($seqno - 1000), inputclassbase::$dbconnection);		
			echo(mysql_error());
			mysql_query("UPDATE OLitems SET seqno=seqno-1000 WHERE subject=\"". $subjname. "\" AND category=\"". $catname. "\"", inputclassbase::$dbconnection);			
			echo(mysql_error());
		}
		public function delete_item()
		{ // Delete the item from the database.
		  if($this->oldid == 0)
				return;
		  $itemdetsqr = inputclassbase::load_query("SELECT * FROM OLitems WHERE oldid=". $this->oldid);
			$catname = $itemdetsqr['category'][0];
			$subjname = $itemdetsqr['subject'][0];
			$seqno = $itemdetsqr['seqno'][0];
			mysql_query("DELETE FROM OLitems WHERE oldid=". $this->oldid, inputclassbase::$dbconnection);
			echo(mysql_error());
			mysql_query("UPDATE OLitems SET seqno=seqno-1 WHERE subject=\"". $subjname. "\" AND category=\"". $catname. "\"AND seqno>". $seqno, inputclassbase::$dbconnection);			
			echo(mysql_error());
		}
		public function up_item()
		{ // Delete the item from the database.
		  if($this->oldid == 0)
				return;
		  $itemdetsqr = inputclassbase::load_query("SELECT * FROM OLitems WHERE oldid=". $this->oldid);
			$catname = $itemdetsqr['category'][0];
			$subjname = $itemdetsqr['subject'][0];
			$seqno = $itemdetsqr['seqno'][0];
			mysql_query("UPDATE OLitems SET seqno=seqno+1 WHERE subject=\"". $subjname. "\" AND category=\"". $catname. "\"AND seqno=". ($seqno-1), inputclassbase::$dbconnection);			
			echo(mysql_error());
			mysql_query("UPDATE OLitems SET seqno=seqno-1 WHERE subject=\"". $subjname. "\" AND category=\"". $catname. "\"AND oldid=". $this->oldid, inputclassbase::$dbconnection);			
			echo(mysql_error());
		}	  
  }
	
	class TriStateInfoBox
	{
		protected $id, $state;
		public function __construct($id,$state=0)
		{
			$this->id = $id;
			$this->state=$state;
		}
		public function set_state($state)
		{
			$this->state=$state;
		}
		public function display()
		{
			echo("<SPAN ID='". $this->id. "' tstate=". $this->state. " onClick='triBoxChangeState(this);'>");
			echo("<img src='PNG/action_none.png'". ($this->state != 0 ? " style='display: none'>" : ">"));
			echo("<img src='PNG/action_check.png'". ($this->state != 1 ? " style='display: none'>" : ">"));
			echo("<img src='PNG/action_delete.png'". ($this->state != 2 ? " style='display: none'>" : ">"));
			echo("</SPAN>");
		}
	}
	
class TriStateBox extends inputclassbase
{
  protected $selval;
	protected $state;
  public function __construct($fieldid,$state=0,$dbconnection = NULL,$dbfield = NULL, $dbtable = NULL, $dbkey = NULL, $dbkeyfield = NULL, $style = NULL, $handler = NULL, $extrafield = NULL, $extravalue = NULL)
  {
    parent::__construct($fieldid,$dbconnection,$dbfield,$dbtable,$dbkey,$dbkeyfield,$style,$handler,$extrafield,$extravalue);
	  $this->state = $state;
  }

  public function handle_input()
  {
    if(isset($_POST[$this->fieldid]))
		{
			if($_POST[$this->fieldid] != $this->get_state())
			{
				$orgval = $this->get_state();
				if($_POST[$this->fieldid] == 0)
					$newval = "NULL";
				else
					$newval = $_POST[$this->fieldid] - 1;
			
				if($this->dbkey <= 0)
				{
					$query = "INSERT INTO ". $this->dbtable. " (". ($this->dbkey > 0 ? "`". $this->dbkeyfield. "`," : ""). "`". $this->dbfield. "`";
					if(isset($this->extrafield))
						foreach($this->extrafield AS $fnm => $fvl)
							$query .= ",`". $fnm. "`";
					if(isset($this->extrakeyfield))
						$query .= ",`". $this->extrakeyfield. "`";
						$query .= ") VALUES(". ($this->dbkey > 0 ? $this->dbkey. "," : "") .$newval;
					if(isset($this->extrafield))
						foreach($this->extrafield AS $fnm => $fvl)
							$query .= ",\"". $fvl. "\"";
					if(isset($this->extrakeyfield))
						$query .= ",\"". $this->extrakey. "\"";
					$query .= ")";
				}
				else
				{
					$query = "UPDATE ". $this->dbtable. " SET `". $this->dbfield. "`=". $newval;
					if(isset($this->extrafield))
						foreach($this->extrafield AS $fnm => $fvl)
							$query .= ",`". $fnm. "`=\"". $fvl. "\"";
					$query .= " WHERE `". $this->dbkeyfield. "`=". $this->dbkey;
							if(isset($this->extrakeyfield))
						$query .= " AND `". $this->extrakeyfield. "`=\"". $this->extrakey. "\"";
				}
				mysql_query($query,parent::$dbconnection);
				if(mysql_error(inputclassbase::$dbconnection))
					echo(mysql_error(parent::$dbconnection));
				else
					echo("OK\r\n");
				
				echo("\r\n". $query. "\r\n");
				if($this->dbkey <= 0)
						$this->dbkey = mysql_insert_id(parent::$dbconnection);
			}
			else
				echo("OK\r\n");
		}
  }

  public function __toString()
  {
		$curstat = $this->get_state();
		if($curstat == 0)
			return("<img src='PNG/action_none.png>");
		if($curstat == 1)
			return("<img src='PNG/action_check.png>");
		if($curstat == 2)
			return("<img src='PNG/action_delete.png>");
		else
			return("");
  }

  public function get_state()
  {
    if($this->dbkey > 0)
		{
			$getval = $this->load_query("SELECT `". $this->dbfield. "` FROM ". $this->dbtable. " WHERE `". $this->dbkeyfield. "`=". $this->dbkey. (isset($this->extrakeyfield) ? " AND `". $this->extrakeyfield. "`=". $this->extrakey : ""));
			if(isset($getval[$this->dbfield][0]) && $getval[$this->dbfield][0] != "")
				return $getval[$this->dbfield][0] + 1;
			else
				return 0;
		}
		else
		{
			return $this->state;
		}
  }

  public function echo_html()
  {
    parent::echo_html();
    $curstat = $this->get_state();
		echo("<SPAN tstate=". $curstat. " NAME=\"". $this->fieldid. "\" ID=\"". $this->fieldid. "\"");
		echo($this->styledata());
		if($this->dbfield != NULL || $this->handlerpage != NULL)
			echo(" onClick='triBoxChangeState(this);'");
		echo(">");
		echo("<img src='PNG/action_none.png'". ($curstat != 0 ? " style='display: none'>" : ">"));
		echo("<img src='PNG/action_check.png'". ($curstat != 1 ? " style='display: none'>" : ">"));
		echo("<img src='PNG/action_delete.png'". ($curstat != 2 ? " style='display: none'>" : ">"));
		echo("</SPAN>");
  }
	public function put_script()
	{
		echo("<SCRIPT> function triBoxChangeState(target)");
		echo(" { target.children[target.getAttribute('tstate')].style.display='none';
						 target.setAttribute('tstate',(target.getAttribute('tstate') + 1) % 3);
						 target.children[target.getAttribute('tstate')].style.display=''; 
						 send_xmlts(target);
					 }
						function send_xmlts(fieldobj)
						{
							myConn[fieldobj.id] = new XHConn(fieldobj);
							if (!myConn[fieldobj.id]) alert(\"XMLHTTP not available. Try a newer/better browser.\");
								myConn[fieldobj.id].connect('". $this->handlerpage. "', \"POST\", \"fieldid=\"+fieldobj.id+\"&\"+fieldobj.id+\"=\"+fieldobj.getAttribute('tstate'), xmlconnDone);
						}
				</SCRIPT>");
	}
}
?>