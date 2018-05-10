<?
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 3.0                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2018 Aim4me N.V.  (http://www.aim4me.info)        |
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
require_once("teacher.php");
require_once("student.php");
require_once("group.php");

class message
{
  protected $msid, $dest, $destid, $destgrp;
  protected $groupnamefld,$mentorfld,$messagetxtfld;

  public function __construct($msid = NULL,$dest=NULL,$destid=NULL,$destgrp=NULL)
  {
		/* Destination types: s = student, p = parent, sp=student and parent, gs=students in group, gp=parents in group, gsp = students and parents in group,
			 t=a teacher, a=administrators, o=teacher with office role, m=teachers with mentor role,am=teachers with absence admin role, c=counsellors, gt=teachers in group,at=all teachers
		*/
		$this->dest = $dest;
		$this->destid = $destid;
		$this->destgrp = $destgrp;
    if(isset($msid))
			$this->msid = $msid;
		else
			$this->msid = 0;
  }
  
  public function get_id()
  {
    return $this->msid;
  }
  
  public function get_message()
  {
    if($this->msid == 0)
	  return NULL;
    if(!isset($this->messagetxtfld))
		{
			$this->messagetxtfld = new inputclass_ckeditor("msgtxt". $this->msid,"80,*",NULL,"message","messages",$this->msid,"msid",NULL,"datahandler.php");
		}
		return($this->messagetxtfld->__toString());
  }

  public function edit_message($sender=NULL,$sendtype=NULL)
  {
   if(!isset($this->messagetxtfld))
		{
			$this->messagetxtfld = new inputclass_ckeditor("msgtxt". $this->msid,"80,*",NULL,"message","messages",$this->msid,"msid",NULL,"datahandler.php");
		}
		if(isset($sender))
		{
			$this->messagetxtfld->set_extrafield("senderid",$sender);
			if(isset($sendtype))
				$this->messagetxtfld->set_extrafield("sendertype",$sendtype);
		}
		$this->messagetxtfld->echo_html();
  }
	
	public function get_dest()
	{
		if($this->msid == 0)
			return($this->dest);
		else
		{
			$dstqr = inputclassbase::load_query("SELECT desttype FROM messages WHERE msid=". $this->msid);
			return($dstqr['desttype'][0]);
		}
	}
	
	public function get_destid()
	{
		if($this->msid == 0)
			return($this->destid);
		else
		{
			$dstqr = inputclassbase::load_query("SELECT destid FROM messages WHERE msid=". $this->msid);
			return($dstqr['destid'][0]);
		}
	}
  
	public function get_destgrp()
	{
		if($this->msid == 0)
			return($this->destgrp);
		else
		{
			$dstqr = inputclassbase::load_query("SELECT destid FROM messages WHERE msid=". $this->msid);
			return($dstqr['destid'][0]);
		}
	}
	
	public function edit_destination()
	{
		$dtext = $_SESSION['dtext'];
		// Destination possibilities depend on initial type. s,p,sp,gs,gp,gsp are for students and 't','a','o','m','am','c','gt','at' for teachers
		$d = $this->dest;
		// Allowed destinations depend on rights configured, so we need that data now
		$msgrightsqr = inputclassbase::load_query("SELECT * FROM messagerights");
		if(isset($msgrightsqr) && $_SESSION['usertype'] == "teacher")
		{
			$I = new teacher();
			$I->load_current();
			foreach($msgrightsqr['destination'] AS $mrix => $adest)
			{
				if($I->has_role($msgrightsqr['role'][$mrix]))
					$myrights[$mrix] = $adest;
			}
		}
		else
			$myrights[0] = "none";
		if($d == 's' || $d == 'p' || $d == 'sp' || $d == 'gs' || $d == 'gp' || $d == 'gsp')
		{
			$stnameqr = inputclassbase::load_query("SELECT CONCAT(firstname,' ',lastname) AS sname FROM student WHERE sid=". $this->destid);
			$stname = $stnameqr['sname'][0];
			$grpnameqr = inputclassbase::load_query("SELECT groupname FROM sgroup WHERE gid=". $this->destgrp);
			$grpname = $grpnameqr['groupname'][0];
			if($d == 's')
				$inpopts="SELECT 's' AS id,'". $stname. "' AS tekst";
			else if($d == 'p')
				$inpopts = "SELECT 'p' AS id, '". $dtext['Parent']. " ". $stname. "' AS tekst";
			else if($d == 'sp')
				$inpopts = "SELECT 'sp' AS id, '". $stname. " ". $t=dtext['and']. " ". $dtext['Parent']. "' AS tekst";
			else if($d == 'gs')
				$inpopts = "SELECT 'gs' AS id, '".$t=$dtext['Student']. " ". dtext['in_group']. " ". $grpname. "' AS tekst";
			else if($d == 'gp')
				$inpopts = "SELECT 'gp' AS id, '".$t=$dtext['Parent']. " ". dtext['in_group']. " ". $grpname. "' AS tekst";
			else if($d == 'gsp')
				$inpopts = "SELECT 'gsp' AS id, '".$t=$dtext['Student']. $t=dtext['and']. " ". $dtext['Parent']. " ". dtext['in_group']. " ". $grpname. "' AS tekst";
			else
				$inpopts = "SELECT '' AS id, '' AS tekst";

			if($d != 's' && in_array("singlestudent",$myrights))
				$inpopts .= " UNION SELECT 's','". $stname. "'";
			if($d != 'p' && in_array("singleparent",$myrights))
				$inpopts .= " UNION SELECT 'p', '". $dtext['Parent']. " ". $stname. "'";
			if($d != 'sp' && in_array("singlestudent",$myrights) && in_array("singleparent",$myrights))
				$inpopts .= " UNION SELECT 'sp', '". $stname. " ". $t=$dtext['and']. " ". $dtext['Parent']. "'";
			if($d != 'gs' && in_array("studentsgroup",$myrights))
				$inpopts .= " UNION SELECT 'gs', '".$t=$dtext['Student']. " ". $dtext['in_grp']. " ". $grpname. "'";
			if($d != 'gp' && in_array("parentsgroup",$myrights))
				$inpopts .= " UNION SELECT 'gp', '".$t=$dtext['Parent']. " ". $dtext['in_grp']. " ". $grpname. "'";
			if($d != 'gsp' && in_array("studentsgroup",$myrights) && in_array("parentsgroup",$myrights))
				$inpopts .= " UNION SELECT 'gsp', '". " ". $dtext['Student']. " ". $dtext['and']. " ". $dtext['Parent']. " ". $dtext['in_grp']. " ". $grpname. "'";
		}
		else
		{
			$tnameqr = inputclassbase::load_query("SELECT CONCAT(firstname,' ',lastname) AS tname FROM teacher WHERE tid=". $this->destid);
			$tname = $tnameqr['tname'][0];
			$grpnameqr = inputclassbase::load_query("SELECT groupname FROM sgroup WHERE gid=". $this->destgrp);
			$grpname = $grpnameqr['groupname'][0];
			if($d == "t")
				$inpopts="SELECT 't' AS id,'". $tname. "' AS tekst";
			else if($d == "a")
				$inpopts .= "SELECT 'a' AS id, '". $dtext['mesgdest_administrators']. "' AS tekst";
			else if($d == "o")
				$inpopts .= "SELECT 'o' AS id, '". $dtext['mesgdest_office']. "' AS tekst";
			else if($d == "m")
				$inpopts .= "SELECT 'm' AS id, '". $dtext['mesgdest_mentors']. "' AS tekst";
			else if($d == "am")
				$inpopts .= "SELECT 'am' AS id, '". $dtext['mesgdest_absencemanagers']. "' AS tekst";
			else if($d == "c")
				$inpopts .= "SELECT 'c' AS id, '". $dtext['mesgdest_counselers']. "' AS tekst";
			else if($d == "gt")
				$inpopts .= "SELECT 'gt' AS id, '". $dtext['Teach_4']. " ". $grpname. "' AS tekst";
			else if($d == "at")
				$inpopts .= "SELECT 'at' AS id, '". $dtext['mesgdest_allteachers']. "' AS tekst";			
			else
				$inpopts .= "SELECT '' AS id, '' AS tekst";			
			
			if($d != 't' && in_array("singleteacher",$myrights))
				$inpopts .= " UNION SELECT 't','". $tname. "'";
			if($d != 'a' && in_array("administrators",$myrights))
				$inpopts .= " UNION SELECT 'a', '". $dtext['mesgdest_administrators']. "'";
			if($d != 'o' && in_array("office",$myrights))
				$inpopts .= " UNION SELECT 'o', '". $dtext['mesgdest_office']. "'";
			if($d != 'm' && in_array("mentors",$myrights))
				$inpopts .= " UNION SELECT 'm', '". $dtext['mesgdest_mentors']. "'";
			if($d != 'am' && in_array("absencemanagers",$myrights))
				$inpopts .= " UNION SELECT 'am', '". $dtext['mesgdest_absencemanagers']. "'";
			if($d != 'c' && in_array("counselers",$myrights))
				$inpopts .= " UNION SELECT 'c', '". $dtext['mesgdest_counselers']. "'";
			if($d != 'gt' && in_array("groupteachers",$myrights))
				$inpopts .= " UNION SELECT 'gt', '". $dtext['Teach_4']. " ". $grpname. "'";
			if($d != 'at' && in_array("allteachers",$myrights))
				$inpopts .= " UNION SELECT 'at', '". $dtext['mesgdest_allteachers']. "'";			
		}
		$msgdestfld = new inputclass_listfield("msgdest". $this->msid,$inpopts,NULL,"targets","messages",$this->msid,"msid",NULL,"datahandler.php");
		$msgdestfld->echo_html();
	}
  
  public function set_read()
  {
    if($this->msid != 0)
			mysql_query("UPDATE messages SET `read`=NOW() WHERE msid=". $this->msid);
  }
	
	public function get_timestamp()
	{
		if($this->msid == 0)
			return NULL;
		$tsqr = inputclassbase::load_query("SELECT sent FROM messages WHERE msid=". $this->msid);
		return($tsqr['sent'][0]);
	}
	
	public function get_sender()
	{
		$dtext = $_SESSION['dtext'];
		if($this->msid == 0)
			return(NULL);
		$sdqr = inputclassbase::load_query("SELECT sendertype,senderid FROM messages WHERE msid=". $this->msid);
		if(isset($sdqr['sendertype']))
		{
			if($sdqr['sendertype'][0] == 't')
			{ // Sent by teacher
				$tnameqr = inputclassbase::load_query("SELECT CONCAT(firstname,' ',lastname) AS tname FROM teacher WHERE tid=". $sdqr['senderid'][0]);
				return($tnameqr['tname'][0]);
			}
			else if($sdqr['sendertype'][0] == 's')
			{ // Sent by student
				$snameqr = inputclassbase::load_query("SELECT CONCAT(firstname,' ',lastname) AS sname FROM student WHERE sid=". $sdqr['senderid'][0]);
				return($snameqr['sname'][0]);				
			}
			else if($sdqr['sendertype'][0] == 'p')
			{ // Sent by parent
				$snameqr = inputclassbase::load_query("SELECT CONCAT(firstname,' ',lastname) AS sname FROM student WHERE sid=". $sdqr['senderid'][0]);
				return($snameqr['sname'][0]. " ". $dtext['Parent']);								
			}
			else if($sdqr['sendertype'][0] == 'sys')
			{ // Sent by system, for now it stays without indication!
				return NULL;
			}
			else
				return NULL;
		}
		else
			return NULL;
	}
	
	public function send($orgdesttype,$orgdestid,$destgrp)
	{
		/* Destination types: s = student, p = parent, sp=student and parent, gs=students in group, gp=parents in group, gsp = students and parents in group,
			 t=a teacher, a=administrators, o=teacher with office role, m=teachers with mentor role,am=teachers with absence admin role, c=counsellors, gt=teachers in group,at=all teachers
		*/
		$targetqr = inputclassbase::load_query("SELECT msid,targets FROM messages WHERE msid=". $this->msid);
		if(isset($targetqr['targets']))
		{
			if($targetqr['targets'][0] == "")
				$targets = $orgdesttype;
			else
				$targets = $targetqr['targets'][0];
			// Now $tagets reflects where the message must go.
			
			// For single targets we can now set the params and we are ready
			if($targets == "p" || $targets == "s" || $targets == "t")
				mysql_query("UPDATE messages SET destid=". $orgdestid. ",desttype='". $targets. "' WHERE msid=". $this->msid);
			else
			{ // We have multiple targets, need to create a list of destinations and adapt the first one and duplicate and adapt for the other targets.
				if($targets == "sp")
				{ // Student & parent
					$destt[0] = "s"; $destt[1] = "p";
					$dests[0] = $orgdestid; $dests[1] = $orgdestid;
				}
				else if($targets == "gs")
				{ // students in group
					$gmemqr = inputclassbase::load_query("SELECT sid FROM sgrouplink WHERE gid=". $destgrp);
					if(isset($gmemqr['sid']))
						foreach($gmemqr['sid'] AS $six => $sid)
						{
							$destt[$six] = "s"; $dests[$six] = $sid;
						}					
				}
				else if($targets == "gp")
				{ // parents in group
					$gmemqr = inputclassbase::load_query("SELECT sid FROM sgrouplink WHERE gid=". $destgrp);
					if(isset($gmemqr['sid']))
						foreach($gmemqr['sid'] AS $six => $sid)
						{
							$destt[$six] = "p"; $dests[$six] = $sid;
						}					
				}
				else if($targets == "gsp")
				{ // students and parents in group
					$gmemqr = inputclassbase::load_query("SELECT sid FROM sgrouplink WHERE gid=". $destgrp);
					if(isset($gmemqr['sid']))
						foreach($gmemqr['sid'] AS $six => $sid)
						{
							$destt[2 * $six] = "s"; $dests[2 * $six] = $sid;
							$destt[2 * $six + 1] = "p"; $dests[2 * $six + 1] = $sid;
						}					
				}
				else if($targets == "a")
				{ // administrators
					$gmemqr = inputclassbase::load_query("SELECT tid FROM teacherroles WHERE role=1");
					if(isset($gmemqr['tid']))
						foreach($gmemqr['tid'] AS $tix => $tid)
						{
							$destt[$tix] = "t"; $dests[$tix] = $tid;
						}					
				}
				else if($targets == "o")
				{ // office teachers
					$gmemqr = inputclassbase::load_query("SELECT tid FROM teacherroles WHERE role=4");
					if(isset($gmemqr['tid']))
						foreach($gmemqr['tid'] AS $tix => $tid)
						{
							$destt[$tix] = "t"; $dests[$tix] = $tid;
						}					
				}
				else if($targets == "am")
				{ // absence admins
					$gmemqr = inputclassbase::load_query("SELECT tid FROM teacherroles WHERE role=3");
					if(isset($gmemqr['tid']))
						foreach($gmemqr['tid'] AS $tix => $tid)
						{
							$destt[$tix] = "t"; $dests[$tix] = $tid;
						}					
				}
				else if($targets == "c")
				{ // counsellors
					$gmemqr = inputclassbase::load_query("SELECT tid FROM teacherroles WHERE role=2");
					if(isset($gmemqr['tid']))
						foreach($gmemqr['tid'] AS $tix => $tid)
						{
							$destt[$tix] = "t"; $dests[$tix] = $tid;
						}					
				}
				else if($targets == "m")
				{ // mentors
					$gmemqr = inputclassbase::load_query("SELECT DISTINCT tid_mentor as tid FROM sgroup WHERE active=1 AND tid_mentor IS NOT NULL");
					if(isset($gmemqr['tid']))
						foreach($gmemqr['tid'] AS $tix => $tid)
						{
							$destt[$tix] = "t"; $dests[$tix] = $tid;
						}					
				}
				else if($targets == "gt")
				{ // teachers for the group
					$gmemqr = inputclassbase::load_query("SELECT DISTINCT tid FROM class WHERE tid IS NOT NULL AND gid=". $destgrp);
					if(isset($gmemqr['tid']))
						foreach($gmemqr['tid'] AS $tix => $tid)
						{
							$destt[$tix] = "t"; $dests[$tix] = $tid;
						}					
				}
				else if($targets == "at")
				{ // all (active) teachers
					$gmemqr = inputclassbase::load_query("SELECT tid FROM teacher WHERE is_gone='N'");
					if(isset($gmemqr['tid']))
						foreach($gmemqr['tid'] AS $tix => $tid)
						{
							$destt[$tix] = "t"; $dests[$tix] = $tid;
						}					
				}
				// Now we may have the destination list!
				if(isset($destt))
				{
					foreach($destt AS $mix => $dstt)
					{
						if($mix == 0) // This is the existing message
							mysql_query("UPDATE messages SET desttype='". $dstt. "', destid=". $dests[0]. " WHERE msid=". $this->msid);
						else
						{ // Need to copy the original message an adjust the destination
							mysql_query("INSERT INTO messages SELECT NULL,destid,targets,desttype,message,sendertype,senderid,sent,`read` FROM messages WHERE msid=". $this->msid);
							//echo(mysql_error());
							$nmsid = mysql_insert_id();
							$updq = "UPDATE messages SET desttype='". $dstt. "', destid=". $dests[$mix]. " WHERE msid=". $nmsid;
							mysql_query($updq);
							if(mysql_error())
								echo(mysql_error(). "{". $updq. "}");
						}
					}
					
				}
				else
				{ // No destinations so remove the message
					mysql_query("DELETE FROM messages WHERE msid=". $this->msid);
				}
			}
		}
	}
	
	public static function send_pending_message($senderid, $orgdesttype, $orgdestid, $destgrp, $sendertype="t")
	{
		// Get a list of pending messages. Pending means sendid and type match but destination is NULL
		$pmqr = inputclassbase::load_query("SELECT msid,message FROM messages WHERE sendertype='". $sendertype. "' AND senderid=". $senderid. " AND destid IS NULL");
		if(isset($pmqr['msid']))
			foreach($pmqr['msid'] AS $msix => $amsid)
			{
				if($pmqr['message'][$msix] == "")
				{ // Empty message, delete it!
					mysql_query("DELETE FROM messages WHERE msid=". $amsid);
				}
				else
				{ // Need to process the message, filling in destid and desttype and duplicating as needed.
					foreach($pmqr['msid'] AS $amsid)
					{
						$sendmsg = new message($amsid);
						$sendmsg->send($orgdesttype, $orgdestid, $destgrp,$amsid);
					}
				}
			}
	}
    
  public static function list_messages($desttype,$destid, $unread=true)
  {
		//echo("Checking messages for ". $desttype. "-". $destid. "<BR>");
		$msgq = "SELECT msid FROM messages WHERE desttype='". $desttype. "' AND destid=". $destid. " ". ($unread ? " AND `read` LIKE '0000%'" : ""). " ORDER BY sent";
		echo(mysql_error());
		$msqr = inputclassbase::load_query($msgq);
		if(isset($msqr['msid']))
		{
			foreach($msqr['msid'] AS $amsid)
			{
				$retmsg[$amsid] = new message($amsid);
			}
			return($retmsg);
		}
		else
			return NULL;
  }
	
	public static function new_message_dialog($desttype, $destid, $sender, $destgrp = NULL)
	{
		/* allowed desttypes: as show in constructor. Sender is assumed to be a teacher! */
		$dtext = $_SESSION['dtext'];
		// If no group is given, we get the current group id
		if(!isset($destgrp))
		{
			$dgrp = new group();
			$dgrp->load_current();
			$destgrp = $dgrp->get_id();
		}
		// Create a message object to show the dialog for
		$newmsg = new message(NULL,$desttype,$destid,$destgrp);
		echo("<BR>". $dtext['mesgdest']. " : ");
		$newmsg->edit_destination();
		echo("<BR><BR>");
		$newmsg->edit_message($sender,"t");
		$_SESSION['CurrentMessage'] = $newmsg;
		// Send button is really just a way to close the dialog
		echo("<BR><form method=POST id=sndmsgfrm><input type=hidden name=orgdesttype value='". $desttype. "'><input type=hidden name=orgdestid value=". $destid. "><input type=hidden name=destgrp value=". $destgrp. "><input type=button value='". $dtext['SendMessage']. "' onClick='setTimeout(function(){sndmsgfrm.submit();},500);'></form>");
	}
  
}
?>