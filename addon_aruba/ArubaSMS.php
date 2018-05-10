<?PHP

function report_hook($id)
{
  global $smstables;
  if(!isset($smstables))
    return; // Only send SMS if tables are defined to extract phone numbers and report is visible for all
  $repdata = inputclassbase::load_query("SELECT * FROM reports WHERE rid=". $id);
  if($repdata['protect'][0] != "A")
    return; // Only send SMS if visible for all
  if($repdata['type'][0] == "X" || $repdata['type'][0] == "C")
  {  // Group report, sid = gid
	$gnameqr = inputclassbase::load_query("SELECT groupname FROM sgroup WHERE active=1 AND gid=". $repdata['sid'][0]);
	$reptxt = "Nieuwe rapportage over klas ". $gnameqr['groupname'][0];
	$smsdestq = "";
	foreach($smstables AS $smstab)
	{
	  $smsdestq .= " UNION SELECT data FROM ". $smstab. " LEFT JOIN sgrouplink USING (sid) WHERE gid=". $repdata['sid'][0];
	}
	$smsdestq = substr($smsdestq,7);
  }
  else
  { // individual report
	$gnameqr = inputclassbase::load_query("SELECT firstname,lastname FROM student WHERE sid=". $repdata['sid'][0]);
	$reptxt = "Nieuwe rapportage over ". $gnameqr['firstname'][0]. " ". $gnameqr['lastname'][0];
	$smsdestq = "";
	foreach($smstables AS $smstab)
	{
	  $smsdestq .= " UNION SELECT data FROM ". $smstab. " WHERE sid=". $repdata['sid'][0];
	}
	$smsdestq = substr($smsdestq,7);
  }
  $destqr = inputclassbase::load_query($smsdestq);
  $smsdests = destquery2smsnumbers($destqr);
  if(isset($smsdests))
    sendsms($smsdests,"Kijk op myschoolresults.com voor details",$reptxt);
}

function absence_hook($id)
{
  global $smstables;
  if(!isset($smstables))
    return;
  $absdata = inputclassbase::load_query("SELECT * FROM absence LEFT JOIN absencereasons USING(aid) WHERE asid=". $id);
  $gnameqr = inputclassbase::load_query("SELECT firstname,lastname FROM student WHERE sid=". $absdata['sid'][0]);
  $reptxt = $gnameqr['firstname'][0]. " ". $gnameqr['lastname'][0]. " was ". $absdata['description'][0]. " op ". inputclassbase::mysqldate2nl($absdata['date'][0]);
  $smsdestq = "";
  foreach($smstables AS $smstab)
  {
	$smsdestq .= " UNION SELECT data FROM ". $smstab. " WHERE sid=". $absdata['sid'][0];
  }
  $smsdestq = substr($smsdestq,7);

  $destqr = inputclassbase::load_query($smsdestq);
  $smsdests = destquery2smsaddresses($destqr);
  if(isset($smsdests))
    sendsms($smsdests,"Kijk op myschoolresults.com voor details",$reptxt);
}

function destquery2smsnumbers($qr)
{
  $resindex = 1;
  foreach($qr['data'] AS $telnrs)
  {
    $evalnr = str_replace(" ","",$telnrs);
    $evalnr = str_replace("-","",$evalnr);
    $evalnr = str_replace(",","/",$evalnr);
	$evalnrs = explode("/",$evalnr);
	foreach($evalnrs AS $anr)
	{
	  if(($anr >= 5600000 && $anr < 5800000) || ($anr > 5900000 && $anr < 7000000) || ($anr > 9000000 && $anr < 9990000))
	  { // It's a SETAR mobile number
	    $retvals[$resindex++] = "297". $anr;
	  }
	  else if($anr > 7000000 && $anr < 8000000)
	  { // It's a DIGICEL number
	    $retvals[$resindex++] = "297". $anr;
	  }
	}
  }
  if(isset($retvals))
    return $retvals;
  else
    return NULL;
}

function sendsms($dests,$subject,$content)
{
  global $smsgatewaypars;
  if(isset($smsgatewaypars))
  { // Send using a gateway
    $headers = "From:lvs@myschoolresults.com\r\nX-mailer: php";
    // Build the message parameters first
    $msgcontent = "";
    foreach($smsgatewaypars AS $gwkey => $gwval)
    {
	  if($gwkey == "gatewayaddress")
	    $destemail = $gwval;
	  else
	    $msgcontent .= $gwkey. ":". $gwval. "\r\n";
	}
	// Add the destinations
	$msgcontent .= "to:";
	$destlist = "";
	foreach($dests AS $madr)
	  $destlist .= ",". $madr;
	$msgcontent .= substr($destlist,1);
	// Add the message content
	$msgcontent .= "\r\ndata:". $content;
    $msgcontent .= "\r\ndata:. ". $subject;
	// Finalize the mailing params
    $content = $msgcontent;	
	$subject = "SendSMS";
  }
  else
  {
    $destemail = "";
    foreach($dests AS $madr)
	{
	  if(substr($madr,0,4) == "2977") // Digicel
	    $destemail .= ",". substr($madr,3). "@digitextaw.com";
	  else // SETAR
      $destemail .= ",". $madr. "@mas.aw";
	}
    $destemail = substr($destemail,1);
    $headers = "From:lvs@myschoolresults.com\r\nX-mailer: php";
  }
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
