<?PHP
//$maildebugplugin=true;
function report_hook($id)
{
  global $maildests;
  $repdata = inputclassbase::load_query("SELECT * FROM reports WHERE rid=". $id);
  if($repdata['protect'][0] != "A" && $repdata['protect'][0] != "T")
    return; // Only send if visible for all or all teachers
	// See if report need to be sent.
	$sendcheckqr = inputclassbase::load_query("SELECT cdata from email_plugin_config WHERE aspect=3 AND cfid=". ($repdata['rcid'][0] > 0 ? $repdata['rcid'][0] : 0). " AND cdata=1");
	if(!isset($sendcheckqr['cdata']))
		return;
	// Create the subject text, message content and link.
	$msgtxtqr = inputclassbase::load_query("SELECT cdata FROM email_plugin_config WHERE aspect=3 AND cfid=9990");
	if(isset($msgtxtqr['cdata'][0]))
		$msgtxt = $msgtxtqr['cdata'][0];
	else
		$msgtxt = "";
	$lnktxtqr = inputclassbase::load_query("SELECT cdata FROM email_plugin_config WHERE aspect=3 AND cfid=9991");
	if(isset($lnktxtqr['cdata'][0]))
		$lnktxt = $lnktxtqr['cdata'][0];
	else
		$lnktxt = "";
  if($repdata['type'][0] == "X" || $repdata['type'][0] == "C")
  {  // Group report, sid = gid
		$sidsqr = inputclassbase::load_query("SELECT DISTINCT sid FROM sgrouplink WHERE gid=". $sid);
		if(isset($sidsqr['sid']))
		{
			$gnameqr = inputclassbase::load_query("SELECT groupname FROM sgroup WHERE active=1 AND gid=". $repdata['sid'][0]);
			$subjtxt = "Nieuwe rapportage over klas ". $gnameqr['groupname'][0];
			foreach($sidsqr['sid'] AS $asid)
			{
				get_mail_destinations($repdata['sid'][0]);
				if(isset($maildests))
					senddata($maildests,$subjtxt,$msgtxt. "<BR>". create_mail_report_link($lnktxt,"showreports.php",$asid));				
			}					
	  }
	}
  else
  { // individual report
		get_mail_destinations($repdata['sid'][0]);
		if(!isset($maildests))
			return; // nothing to do if there are no destinations
		$gnameqr = inputclassbase::load_query("SELECT firstname,lastname FROM student WHERE sid=". $repdata['sid'][0]);
		$subjtxt = "Nieuwe rapportage over ". $gnameqr['firstname'][0]. " ". $gnameqr['lastname'][0];
		senddata($maildests,$subjtxt,$msgtxt. "<BR>". create_mail_report_link($lnktxt,"showreports.php",$repdata['sid'][0]));
  }
}

function absence_hook($id)
{
  global $maildests;
	//echo("Absence hook entry");
  $absdata = inputclassbase::load_query("SELECT * FROM absence LEFT JOIN absencereasons USING(aid) WHERE asid=". $id);
	// See if absence need to be sent.
	$sendcheckqr = inputclassbase::load_query("SELECT cdata from email_plugin_config WHERE aspect=4 AND cfid=". $absdata['acid'][0]. " AND cdata=1");
	if(!isset($sendcheckqr['cdata']))
		return;

  $gnameqr = inputclassbase::load_query("SELECT firstname,lastname FROM student WHERE sid=". $absdata['sid'][0]);
	get_mail_destinations($absdata['sid'][0]);
  if(!isset($maildests))
    return;
	// Create the subject text, message content and link.
	$msgtxtqr = inputclassbase::load_query("SELECT cdata FROM email_plugin_config WHERE aspect=4 AND cfid=9990");
	if(isset($msgtxtqr['cdata'][0]))
		$msgtxt = $msgtxtqr['cdata'][0];
	else
		$msgtxt = "";
	$lnktxtqr = inputclassbase::load_query("SELECT cdata FROM email_plugin_config WHERE aspect=4 AND cfid=9991");
	if(isset($lnktxtqr['cdata'][0]))
		$lnktxt = $lnktxtqr['cdata'][0];
	else
		$lnktxt = "";
  $subjtxt = "Bericht over ". $gnameqr['firstname'][0]. " ". $gnameqr['lastname'][0];
  senddata($maildests,$subjtxt,$msgtxt. "<BR>". create_mail_report_link($lnktxt,"showabsence.php",$absdata['sid'][0]));
}

function result_hook($tdid,$sid)
{
  global $maildests;
	// See if results need to be sent.
	$sendcheckqr = inputclassbase::load_query("SELECT cdata from email_plugin_config WHERE aspect=2 AND cfid=0 AND cdata=1");
	if(!isset($sendcheckqr['cdata']))
		return;
	// See if we got a destination
	get_mail_destinations($sid);
  if(!isset($maildests))
    return;
	//echo("Report hook entry with destination");
	// Create the subject text, message content and link.
	$msgtxtqr = inputclassbase::load_query("SELECT cdata FROM email_plugin_config WHERE aspect=2 AND cfid=9990");
	if(isset($msgtxtqr['cdata'][0]))
		$msgtxt = $msgtxtqr['cdata'][0];
	else
		$msgtxt = "";
	$lnktxtqr = inputclassbase::load_query("SELECT cdata FROM email_plugin_config WHERE aspect=2 AND cfid=9991");
	if(isset($lnktxtqr['cdata'][0]))
		$lnktxt = $lnktxtqr['cdata'][0];
	else
		$lnktxt = "";
  $gnameqr = inputclassbase::load_query("SELECT firstname,lastname FROM student WHERE sid=". $sid);
  $subjtxt = "Bericht over ". $gnameqr['firstname'][0]. " ". $gnameqr['lastname'][0];
  senddata($maildests,$subjtxt,$msgtxt. "<BR>". create_mail_report_link($lnktxt,"showreportcard.php",$sid));
}

function get_mail_destinations($sid)
{
  global $maildests;
	//echo("getting email destinations for ". $sid);
	// First we see if we have email configuration data present
	$cfgresqr = inputclassbase::load_query("SHOW TABLES LIKE 'email_plugin_config'");
	if(sizeof($cfgresqr) > 0)
	{ // So we should have some... 
		$destfldqr = inputclassbase::load_query("SELECT * FROM email_plugin_config WHERE aspect=1 AND cdata IS NOT NULL");
		$destix = 0;
		if(isset($destfldqr))
		{
			foreach($destfldqr['cdata'] AS $dsttabname)
			{
				$dstadrqr = inputclassbase::load_query("SELECT data FROM `". $dsttabname. "` WHERE sid=". $sid. " AND data LIKE '%@%.%'");
				if(isset($dstadrqr['data']))
					foreach($dstadrqr['data'] AS $amailadr)
					{
						$maildests[$destix++] = $amailadr;
						//echo("Added dest ". $amailadr);
					}
			}
		}
		//else
			//echo("No dest fields found");
	}
	//else
		//echo("No config found ". sizeof($cfgresqr));
	return NULL;
}

function create_mail_report_link($linktxt,$pagedest,$sid)
{
	$orguri = $_SERVER['HTTP_REFERER'];
	$lastslash = strrpos($orguri,"/");
	$linkprefix = substr($orguri,0,$lastslash);
	$restxt = "<a href='". $linkprefix. "/generallogin.php?page=". $pagedest. "&sid=". $sid. "&key=";
	$datestr = date("Y-m-d");
	$key = md5($datestr. $pagedest. $sid);
	$restxt .= $key. "'>". $linktxt. "</a>";
	return $restxt;
}


function senddata($dests,$subject,$content)
{
	global $maildebugplugin;
	//echo("senddata entry");
	$destemail = "";
	foreach($dests AS $madr)
	{
		$destemail .= ",". $madr;
	}
	$destemail = substr($destemail,1);
	$headers = "From:lvs@myschoolresults.com\r\nX-mailer: php\r\nContent-Type: text/html; charset=UTF-8\r\n";

  if(isset($maildebugplugin))
  { // We are on a Windows test system so we just dump the mailing params in a file
		echo("Writing email to file");
    $fh = fopen("mailsend.txt","a");
		print_r(error_get_last());
    fwrite($fh,"\r\nTo: ". $destemail);
    fwrite($fh,"\r\nSubject: ". $subject);
    fwrite($fh,"\r\nText: ". $content);
    fwrite($fh,"\r\nHeader: ". $headers);
    fclose($fh); 
		echo("Mail written to file");
  }
  else // Do the send already!
	{
		//echo("Sending real mail");
    mail($destemail,$subject,$content,$headers,"-flvs@myschoolresults.com");
	}
}
?>
