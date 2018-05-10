<?
require_once("inputclassbase.php");
class inputclass_datefield extends inputclassbase
{
  protected $params,$defaultdate;
  public function __construct($fieldid,$defaultdate,$dbconnection = NULL,$dbfield = NULL, $dbtable = NULL, $dbkey = NULL, $dbkeyfield = NULL, $style = NULL, $handler = NULL, $extrafield = NULL, $extravalue = NULL)
  {
    parent::__construct($fieldid,$dbconnection,$dbfield,$dbtable,$dbkey,$dbkeyfield,$style,$handler,$extrafield,$extravalue);
	$this->defaultdate = $defaultdate;
  }
  public function set_parameter($pname,$pvalue)
  {
    $this->params[$pname] = $pvalue;
  }
  public function set_initial_value($val)
  {
    $this->defaultdate = $val;
  }

  public function handle_input()
  {
    global $userlink;
    if(isset($_POST[$this->fieldid]))
	{
	  $orgval = $this->__toString();
	  if($_POST[$this->fieldid] != $orgval)
	  {
		if($_POST[$this->fieldid] == "")
		  $newval = "NULL";
		else
  	      $newval = "'". inputclassbase::nldate2mysql($_POST[$this->fieldid]). "'";
		
	    if($this->dbkey <= 0 || !isset($orgval))
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
		mysql_query($query,inputclassbase::$dbconnection);
		if(mysql_error(inputclassbase::$dbconnection))
    	  echo(mysql_error(inputclassbase::$dbconnection));
		else
		  echo("OK\r\n");
		echo("\r\n". $query. "\r\n");
		if($this->dbkey <= 0)
  		  $this->dbkey = mysql_insert_id(inputclassbase::$dbconnection);
	  }
	  else // no value change, just echo OK
		echo("OK\r\n");	  
	}
  }


  public function __toString()
  {
    if($this->dbkey > 0)
	{
	  $getval = $this->load_query("SELECT `". $this->dbfield. "` FROM ". $this->dbtable. " WHERE `". $this->dbkeyfield. "`=". $this->dbkey. (isset($this->extrakeyfield) ? " AND `". $this->extrakeyfield. "`=". $this->extrakey : ""));
	  if(isset($getval))
	    return(inputclassbase::mysqldate2nl($getval[$this->dbfield][0]));
	  else
	    return($this->defaultdate);
	}
	else
	  return($this->defaultdate);
  }
  public function echo_html()
  {
    parent::echo_html();
    $this->echo_script();
    $val= $this->__toString();
    echo("<input  type=text size=11 readonly name=". $this->fieldid. " id=". $this->fieldid. " value='". $this->__toString(). "'");
	if(isset($this->style) && substr($this->style,0,6) != "class=")
	  echo($this->styledata());
	if($this->dbfield != NULL || $this->handlerpage != NULL)
	  echo(" onChange='return(send_xml(\"". $this->fieldid. "\",this));' dbfield='". $this->dbfield. "'");
	if(isset($this->style) && substr($this->style,0,6) == "class=")
	  echo(" class=\"". substr($this->style,6). " ");
	else 
	  echo(" class=\"datechooser ");
	if(isset($this->params))
	  foreach($this->params AS $pname => $pvalue)
	    echo(" ". $pname. "='". $pvalue. "'");
	echo(" dc-dateformat='d-m-Y' dc-iconlink='/inputlib/datechooser.png' dc-onupdate='dsfunc". $this->fieldid. "'\"");
    echo(">");
  }

  protected function echo_script()
  {
	echo('<SCRIPT LANGUAGE="JavaScript">');
	require_once("dateselectnl.js");
	echo("function dsfunc". $this->fieldid. "() { return (send_xml(\"". $this->fieldid. "\",document.getElementById('". $this->fieldid. "'))); }");
    echo(" </SCRIPT>");
  }
}
?>