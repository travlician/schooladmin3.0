<SCRIPT>
<?
  require_once("xhconn.js");
?>
var AjaxPending = 0;
var myConn = [];
function send_xml(fieldid,fieldobj)
{
  fieldobj.style.backgroundColor='red';
  myConn[fieldid] = new XHConn(fieldobj);
  if (!myConn[fieldid]) alert("XMLHTTP not available. Try a newer/better browser.");
  myConn[fieldid].connect("<? echo($this->handlerpage); ?>", "POST", "fieldid="+fieldid+"&"+fieldobj.name+"="+encodeURIComponent(fieldobj.value), xmlconnDone);
}
function send_xmlsl(fieldid,fieldobj)
{
  myConn[fieldid] = new XHConn(fieldobj);
  if (!myConn[fieldid]) alert("XMLHTTP not available. Try a newer/better browser.");
  myConn[fieldid].connect("<? echo($this->handlerpage); ?>", "POST", "fieldid="+fieldid+"&"+fieldobj.name+"="+fieldobj[fieldobj.selectedIndex].value, xmlconnDone);
}
function send_xmlcb(fieldid,fieldobj)
{
  myConn[fieldid] = new XHConn(fieldobj);
  if (!myConn[fieldid]) alert("XMLHTTP not available. Try a newer/better browser.");
  if(fieldobj.checked == false)
    cbstat = 0;
  else
    cbstat = 1;
  myConn[fieldid].connect("<? echo($this->handlerpage); ?>", "POST", "fieldid="+fieldid+"&"+fieldobj.name+"="+cbstat, xmlconnDone);
}
function xmlconnDone(oXML,fieldobj)
{
<?
  $xmlpostdata = "{";
	if(isset($_POST))
		foreach($_POST AS $pkey => $pval)
		{
			$xmlpostdata .= $pkey. ":'". $pval. "',";
		}
  $xmlpostdata = substr($xmlpostdata,0,-1). "}";
?>
  fieldobj.style.backgroundColor='white';
  if(oXML.responseText.substring(0,2) != "OK" && typeof oXML.responseText != "undefined")
    alert(oXML.responseText);
  if(oXML.responseText.substr(oXML.responseText.length - 7) == "REFRESH")
<?
  if(isset($_POST) && count($_POST) > 0)
    echo(" postwith(window.location," .$xmlpostdata. "); ");
  else
    echo(" document.location.reload(true); ");
?>
  
}
function postwith (to,p) {
  var myForm = document.createElement("form");
  myForm.method="post" ;
  myForm.action = to ;
  for (var k in p) {
    var myInput = document.createElement("input") ;
    myInput.setAttribute("name", k) ;
    myInput.setAttribute("value", p[k]);
    myForm.appendChild(myInput) ;
  }
  document.body.appendChild(myForm) ;
  myForm.submit() ;
  document.body.removeChild(myForm) ;
}</SCRIPT>

