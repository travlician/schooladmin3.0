<?PHP

function report_hook($id)
{
  global $maildests;
  if(!isset($maildests))
    return; // Only send mail if destination(s) set
  $repdata = inputclassbase::load_query("SELECT * FROM reports WHERE rid=". $id);
  if($repdata['protect'][0] != "A" && $repdata['protect'][0] != "T")
    return; // Only send if visible for teachers
  if($repdata['type'][0] == "X" || $repdata['type'][0] == "C")
  {  // Group report, sid = gid
	$gnameqr = inputclassbase::load_query("SELECT groupname FROM sgroup WHERE active=1 AND gid=". $repdata['sid'][0]);
	$reptxt = "Nieuwe rapportage over klas ". $gnameqr['groupname'][0];
  }
  else
  { // individual report
	$gnameqr = inputclassbase::load_query("SELECT firstname,lastname FROM student WHERE sid=". $repdata['sid'][0]);
	$reptxt = "Nieuwe rapportage over ". $gnameqr['firstname'][0]. " ". $gnameqr['lastname'][0];
  }
  senddata($maildests,"Kijk op myschoolresults.com voor details",$reptxt);
}

function absence_hook($id)
{
  global $maildests;
  if(!isset($maildests))
    return;
  $absdata = inputclassbase::load_query("SELECT * FROM absence LEFT JOIN absencereasons USING(aid) WHERE asid=". $id);
  $gnameqr = inputclassbase::load_query("SELECT firstname,lastname FROM student WHERE sid=". $absdata['sid'][0]);
  $reptxt = $gnameqr['firstname'][0]. " ". $gnameqr['lastname'][0]. " was ". $absdata['description'][0]. " op ". inputclassbase::mysqldate2nl($absdata['date'][0]);
  senddata($maildests,"Kijk op myschoolresults.com voor details",$reptxt);
}


function senddata($dests,$subject,$content)
{
    $destemail = "";
    foreach($dests AS $madr)
	{
      $destemail .= ",". $madr;
	}
    $destemail = substr($destemail,1);
    $headers = "From:lvs@myschoolresults.com\r\nX-mailer: php";

  if(PHP_OS == "WINNT")
  { // We are on a Windows test system so we just dump the mailing params in a file
    $fh = fopen("smssend.txt","a");
    fwrite($fh,"\r\nTo: ". $destemail);
    fwrite($fh,"\r\nSubject: ". $subject);
    fwrite($fh,"\r\nText: ". $content);
    fwrite($fh,"\r\nHeader: ". $headers);
    fclose($fh); 
  }
  else // Do the send already!
    mail($destemail,$subject,$content,$headers,"-flvs@myschoolresults.com");
}
?>
