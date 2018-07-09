<?
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2016 Aim4me N.V.  (http://www.aim4me.info)        |
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
	if(!isset($_SESSION))
    session_start();
  require_once("displayelements/menulistelement.php");
	require_once("teacher.php");
	
	if(!isset($pngsource))
		$pngsource="PNG";

	if(isset($_GET['context']) || isset($_POST['helpitem']))
	{
		// Connect to the database
		require_once("schooladminconstants.php");
		inputclassbase::dblogon($databaseserver,$datausername,$datapassword,$databasename);
		echo('<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><link rel="stylesheet" type="text/css" href="layout.css" /> </head><body><DIV style="text-align: left;">');
		// See if we need to delete an item
		if(isset($_POST['delete']) && $_POST['delete'] == 1)
		{
			mysql_query("DELETE FROM helpcontent WHERE hid=". $_POST['helpitem']);
			echo(mysql_error());
			mysql_query("DELETE FROM helproles WHERE hid=". $_POST['helpitem']);
			echo(mysql_error());
			echo("<SCRIPT> window.close(); </SCRIPT>");
			exit;
		}
		$helpscreen = new helppage;
		if(isset($_POST['edit']) && $_POST['edit'] == 1)
			$helpscreen->edit_content();
		else
			$helpscreen->show_content();
	}
	

	class helpenabledmenulistelement extends menulistelement
	{
		public function add_helpitem($itemname)
		{
			$this->add_element(new helpmenuelement(NULL, $this->itemstyle,$itemname,"helppage",$this->paramname,$this->itemactivestyle));
		}
	}
	
	class helpmenuelement extends menuelement
	{
		protected function show_contents()
		{
			//echo("<a href=\"". $_SERVER['PHP_SELF']. "?". $this->paramname. "=". $this->menuvalue. "\">". $this->menutext. "</a>");
			//echo("<a href='javascript:setTimeout(window.location.href=\"". $_SERVER['PHP_SELF']. "?". $this->paramname. "=". $this->menuvalue. "\",5000);'>". $this->menutext. "</a>");
			echo("<a href='javascript:openhelp(\"helppage.php?context=". (isset($_GET['Page']) ? $_GET['Page'] : ""). "\")'>". $this->menutext. "</a>");	
			echo("<SCRIPT> function openhelp(aurl) { window.open(aurl,'_blank'); } </SCRIPT>");
		}
	}
	
	class helppage
	{
		public function show_content()
		{
			global $currentuser,$defaultlanguage,$commonhelpdbprefix,$pngsource;
			
			$currentuser = new teacher();
			$currentuser->load_current();
			
			// Find out which hid has to be displayed
			if(!isset($_POST['helpitem']))
			{
				// First priority: Page belonging to the context, in the user language
				$hpq = "SELECT DISTINCT hid FROM helpcontent LEFT JOIN helproles USING(hid) LEFT JOIN teacherroles USING(role) WHERE lang='". $_SESSION['currentlanguage']. "' AND (role IS NULL OR tid=". $currentuser->get_id(). ") AND pageref='". $_GET['context']. "'";
				if(isset($commonhelpdbprefix))
					$hpq .= " UNION SELECT DISTINCT hid FROM ". $commonhelpdbprefix. "helpcontent LEFT JOIN ". $commonhelpdbprefix. "helproles USING(hid) LEFT JOIN teacherroles USING(role) WHERE lang='". $_SESSION['currentlanguage']. "' AND (role IS NULL OR tid=". $currentuser->get_id(). ") AND pageref='". $_GET['context']. "'";
				$hpqr = inputclassbase::load_query($hpq);
				if(!isset($hpqr['hid']))
				{ // Second priority: Page belonging to context, in the default language
					$hpq = "SELECT DISTINCT hid FROM helpcontent LEFT JOIN helproles USING(hid) LEFT JOIN teacherroles USING(role) WHERE lang='". $defaultlanguage. "' AND (role IS NULL OR tid=". $currentuser->get_id(). ") AND pageref='". $_GET['context']. "'";
					if(isset($commonhelpdbprefix))
						$hpq .= " UNION SELECT DISTINCT hid FROM ". $commonhelpdbprefix. "helpcontent LEFT JOIN ". $commonhelpdbprefix. "helproles USING(hid) LEFT JOIN teacherroles USING(role) WHERE lang='". $defaultlanguage. "' AND (role IS NULL OR tid=". $currentuser->get_id(). ") AND pageref='". $_GET['context']. "'";
					$hpqr = inputclassbase::load_query($hpq);					
				}
				if(!isset($hpqr['hid']))
				{ // Third priority: Page contextless, in the the user language
					$hpq = "SELECT DISTINCT hid FROM helpcontent LEFT JOIN helproles USING(hid) LEFT JOIN teacherroles USING(role) WHERE lang='". $_SESSION['currentlanguage']. "' AND (role IS NULL OR tid=". $currentuser->get_id(). ") AND pageref IS NULL AND parentid IS NULL";
					if(isset($commonhelpdbprefix))
						$hpq .= " UNION SELECT DISTINCT hid FROM ". $commonhelpdbprefix. "helpcontent LEFT JOIN ". $commonhelpdbprefix. "helproles USING(hid) LEFT JOIN teacherroles USING(role) WHERE lang='". $_SESSION['currentlanguage']. "' AND (role IS NULL OR tid=". $currentuser->get_id(). ") AND pageref IS NULL AND parentid IS NULL";
					$hpqr = inputclassbase::load_query($hpq);			
				}
				if(!isset($hpqr['hid']))
				{ // Forth priority: Page contextless, in the default language
					$hpq = "SELECT DISTINCT hid FROM helpcontent LEFT JOIN helproles USING(hid) LEFT JOIN teacherroles USING(role) WHERE lang='". $defaultlanguage. "' AND (role IS NULL OR tid=". $currentuser->get_id(). ") AND pageref IS NULL AND parentid IS NULL";
					if(isset($commonhelpdbprefix))
						$hpq .= " UNION SELECT DISTINCT hid FROM ". $commonhelpdbprefix. "helpcontent LEFT JOIN ". $commonhelpdbprefix. "helproles USING(hid) LEFT JOIN teacherroles USING(role) WHERE lang='". $defaultlanguage. "' AND (role IS NULL OR tid=". $currentuser->get_id(). ") AND pageref IS NULL AND parentid IS NULL";
					$hpqr = inputclassbase::load_query($hpq);					
				}
				if(isset($hpqr['hid']))
					$hid=$hpqr['hid'][0];
				else
					$hid=0;
			}
			else
				$hid = $_POST['helpitem'];
			// IF multiple items are found we list them, else we show the content of a single page.
			if(isset($hpqr['hid']) && count($hpqr['hid']) > 1)
			{
				//echo("Count helpitems = ". count($hpqr['hid']). "<BR>");
				foreach($hpqr['hid'] AS $ahid)
				{
					$subhq = "SELECT helptitle,hid FROM helpcontent LEFT JOIN helproles USING(hid) LEFT JOIN teacherroles USING(role) WHERE (role IS NULL OR tid=". $currentuser->get_id(). ") AND hid=". $ahid;
					if(isset($commonhelpdbprefix))
						$subhq .= " UNION SELECT helptitle,hid FROM ". $commonhelpdbprefix. "helpcontent LEFT JOIN ". $commonhelpdbprefix. "helproles USING(hid) LEFT JOIN teacherroles USING(role) WHERE (role IS NULL OR tid=". $currentuser->get_id(). ") AND hid=". $ahid;
					$subhqr = inputclassbase::load_query($subhq);
					if(isset($subhqr['hid']))
					{
						foreach($subhqr['hid'] AS $hix => $shid)
							echo("<a href=# onClick=helplink(". $shid. ");>". $subhqr['helptitle'][$hix]. "</a><BR>");
					}
				}
			}
			else
			{ // Single item
				if($currentuser->has_role("admin"))
				{ // Depending on which item is to be displayed, we show icons to edit, create new and delete.
					if($hid != 0 && $hid<10000)
					{ // Show the edit icon
						echo(" <IMG onClick=editlink(". $hid. "); SRC='". $pngsource. "/reply.png'>");
					}
					// Show the icon to create a new help item
				 echo(" <IMG onClick=editlink(0); SRC='". $pngsource. "/action_add.png'>");
					
					if($hid < 10000)
					{ // Show the icon to delete an item
						echo(" <IMG onClick=deletelink(". $hid. "); SRC='". $pngsource. "/action_delete.png'>");					
					}
					echo("<FORM METHOD=POST ID=editlinkform><INPUT TYPE=hidden NAME=edit VALUE=1><INPUT TYPE=hidden NAME=helpitem VALUE=0 ID=edithid></FORM>");
					echo("<FORM METHOD=POST ID=dellinkform><INPUT TYPE=hidden NAME=delete VALUE=1><INPUT TYPE=hidden NAME=helpitem VALUE=0 ID=delhid></FORM>");
					echo("<SCRIPT> function editlink(ehid) { document.getElementById('edithid').value=ehid; document.getElementById('editlinkform').submit(); } </SCRIPT>");
					echo("<SCRIPT> function deletelink(ehid) { document.getElementById('delhid').value=ehid; document.getElementById('dellinkform').submit(); } </SCRIPT>");
				}
				
				// Show the help content
				$helpcontentq = "SELECT * FROM helpcontent WHERE hid=". $hid;
				if(isset($commonhelpdbprefix))
					$helpcontentq .= " UNION SELECT * FROM ". $commonhelpdbprefix. "helpcontent WHERE hid=". $hid;
				$hcqr = inputclassbase::load_query($helpcontentq);
				if(isset($hcqr['content']))
				{
					echo($hcqr['content'][0]);
				}
				// Now get the related items
				$subhq = "SELECT helptitle,hid FROM helpcontent LEFT JOIN helproles USING(hid) LEFT JOIN teacherroles USING(role) WHERE (role IS NULL OR tid=". $currentuser->get_id(). ") AND parentid=". $hid;
				if(isset($commonhelpdbprefix))
					$subhq .= " UNION SELECT helptitle,hid FROM ". $commonhelpdbprefix. "helpcontent LEFT JOIN ". $commonhelpdbprefix. "helproles USING(hid) LEFT JOIN teacherroles USING(role) WHERE (role IS NULL OR tid=". $currentuser->get_id(). ") AND parentid=". $hid;
				$subhqr = inputclassbase::load_query($subhq);
				if(isset($subhqr['hid']))
				{
					echo("<BR>". $_SESSION['dtext']['RelatedHelp']. " : <BR>");
					foreach($subhqr['hid'] AS $hix => $shid)
						echo("<a href=# onClick=helplink(". $shid. "); style='margin-left: 100px;'>". $subhqr['helptitle'][$hix]. "</a><BR>");
				}
				// Now get the parent items
				if($hcqr['parentid'][0] > 0)
				{
					$subhq = "SELECT helptitle,hid FROM helpcontent LEFT JOIN helproles USING(hid) LEFT JOIN teacherroles USING(role) WHERE (role IS NULL OR tid=". $currentuser->get_id(). ") AND hid=". $hcqr['parentid'][0];
					if(isset($commonhelpdbprefix))
						$subhq .= " UNION SELECT helptitle,hid FROM ". $commonhelpdbprefix. "helpcontent LEFT JOIN ". $commonhelpdbprefix. "helproles USING(hid) LEFT JOIN teacherroles USING(role) WHERE (role IS NULL OR tid=". $currentuser->get_id(). ") AND hid=". $hcqr['parentid'][0];
					$subhqr = inputclassbase::load_query($subhq);
					if(isset($subhqr['hid']))
					{
						echo("<BR>". $_SESSION['dtext']['BackHelp']. " : <BR>");
						foreach($subhqr['hid'] AS $hix => $shid)
							echo("<a href=# onClick=helplink(". $shid. ");>". $subhqr['helptitle'][$hix]. "</a><BR>");
					}
				}
			}
			echo("<FORM METHOD=POST ID=hlink><INPUT TYPE=HIDDEN NAME=helpitem ID=hlinkitem VALUE=0></FORM>");
			echo("<SCRIPT> function helplink(hitem) { document.getElementById('hlinkitem').value=hitem; document.getElementById('hlink').submit(); } </SCRIPT>");
			echo("</div></body></html>");
			
			/* echo("Contents of the help page");
			foreach($_SESSION AS $skey => $sval)
			  echo("<BR>". $skey. " : ". $sval); */
		}
		public function edit_content()
		{
			global $defaultlanguage,$commonhelpdbprefix;
			
			// If the item comes from our own database, we just edit it. If it comes from another database, we first make a copy and work with that.
			$hpqr = inputclassbase::load_query("SELECT hid FROM helpcontent WHERE hid=". $_POST['helpitem']);
			if(isset($hpqr['hid']) || $_POST['helpitem'] == 0)
			{ // It is in our own database or a new item
				$hid = $_POST['helpitem'];
			}
			else
			{ // The item comes from the common help database, we make a copy first
				mysql_query("INSERT INTO helpcontent SELECT NULL,parentid,lang,helptitle,pageref,content FROM ". $commonhelpdbprefix. "helpcontent WHERE hid=". $_POST['helpitem']);
				$hid = mysql_insert_id();
				// Now we need to copy the roles too
				mysql_query("INSERT INTO helproles SELECT ". $hid. ",role FROM ". $commonhelpdbprefix. "helproles WHERE hid=". $_POST['helpitem']);
			}
			
			// Now we create fields for the help items
			// Parentid
			$selq = "SELECT '' AS id, '' AS tekst UNION SELECT hid,helptitle FROM helpcontent WHERE lang='". $_SESSION['currentlanguage']. "'";
			if(isset($commonhelpdbprefix))
				$selq .= " UNION SELECT hid,helptitle FROM ". $commonhelpdbprefix. "helpcontent WHERE lang='". $_SESSION['currentlanguage']. "'";
			$fieldPI = new inputclass_listfield("helpeditPI",$selq,NULL,"parentid","helpcontent",$hid,"hid",NULL,"datahandler.php");
			$fieldPI->set_extrafield("lang",$_SESSION['currentlanguage']);
			echo($_SESSION['dtext']['ParentHelpItem']. " : ");
			$fieldPI->echo_html();
			// Title
			$fieldT = new inputclass_textfield("helpeditT",80,NULL,"helptitle","helpcontent",$hid,"hid",NULL,"datahandler.php");
			$fieldT->set_extrafield("lang",$_SESSION['currentlanguage']);
			echo("<BR>". $_SESSION['dtext']['Title']. " : ");
			$fieldT->echo_html();
			// Page reference
			$fieldPR = new inputclass_textfield("helpeditPR",80,NULL,"pageref","helpcontent",$hid,"hid",NULL,"datahandler.php");
			$fieldPR->set_extrafield("lang",$_SESSION['currentlanguage']);
			echo("<BR>". $_SESSION['dtext']['PageReference']. " : ");
			$fieldPR->echo_html();
			// Content
			$fieldC = new inputclass_ckeditor("helpeditC","80,*",NULL,"content","helpcontent",$hid,"hid",NULL,"datahandler.php");
			$fieldC->set_extrafield("lang",$_SESSION['currentlanguage']);
			echo("<BR>");
			$fieldC->echo_html();
			// roles
			foreach(teacher::$definedroles AS $trid => $trtxt)
			{
				if(isset($rselq))
					$rselq .= " UNION SELECT ". $trid. ",'". $trtxt. "'";
				else
					$rselq = "SELECT ". $trid. " AS id,'". $trtxt. "' AS tekst";
			}
			$fieldR = new inputclass_multiselect("helpeditR",$rselq,NULL,"role","helproles",$hid,"hid",NULL,"datahandler.php");
			echo("<BR>". $_SESSION['dtext']['Role']. " : ");
			$fieldR->echo_html();			
			echo("<INPUT TYEP=SUBMIT onClick='setTimeout(window.close,1000);' VALUE='". $_SESSION['dtext']['submit_chng']. "'>");
			echo("</div></body></html>");
		}
		public function conditional_add($menu)
		{
			global $currentuser,$defaultlanguage,$commonhelpdbprefix;
			if($currentuser->has_role("admin"))
				$haveitems = true;
			else
			{ // Only show the help icon if there is at least one item to show
				$hpcq = "SELECT DISTINCT hid FROM helpcontent LEFT JOIN helproles USING(hid) LEFT JOIN teacherroles USING(role) WHERE (lang='". $_SESSION['currentlanguage']. "' OR lang='". $defaultlanguage. "') AND (role IS NULL OR tid=". $currentuser->get_id(). ")";
				if(isset($_GET['Page']))
					$hpcq .= " AND ((pageref IS NULL AND parentid IS NULL) OR pageref='". $_GET['Page']. "')";
				else
					$hpcq .= " AND pageref IS NULL AND parentid IS NULL";
				if(isset($commonhelpdbprefix))
				{
					$hpcq .= " UNION SELECT DISTINCT hid FROM ". $commonhelpdbprefix. "helpcontent LEFT JOIN ". $commonhelpdbprefix. "helproles USING(hid) LEFT JOIN teacherroles USING(role) WHERE (lang='". $_SESSION['currentlanguage']. "' OR lang='". $defaultlanguage. "') AND (role IS NULL OR tid=". $currentuser->get_id(). ")";
					if(isset($_GET['Page']))
						$hpcq .= " AND ((pageref IS NULL AND parentid IS NULL) OR pageref='". $_GET['Page']. "')";
					else
						$hpcq .= " AND pageref IS NULL AND parentid IS NULL";					
				}
				$hpcqr = inputclassbase::load_query($hpcq);
				$haveitems = count($hpcqr['hid']) > 0;				
			}
			if($haveitems)
				$menu->add_helpitem($_SESSION['dtext']['Help']);
		}
	}
