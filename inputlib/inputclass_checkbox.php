<?
require_once("inputclassbase.php");
class inputclass_checkbox extends inputclassbase
{
  protected $defaultvalue;
  public function __construct($fieldid,$defaultvalue,$dbconnection = NULL,$dbfield = NULL, $dbtable = NULL, $dbkey = NULL, $dbkeyfield = NULL, $style = NULL, $handler = NULL, $extrafield = NULL, $extravalue = NULL)
  {
    parent::__construct($fieldid,$dbconnection,$dbfield,$dbtable,$dbkey,$dbkeyfield,$style,$handler,$extrafield,$extravalue);
	$this->defaultvalue = $defaultvalue;
  }
  public function handle_input()
  {
    if(isset($_POST[$this->fieldid]) && $_POST[$this->fieldid] != 0)
	  $newval = 1;
	else
	  $newval = 0;
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
	mysql_query($query,inputclassbase::$dbconnection);
	if(mysql_error(inputclassbase::$dbconnection))
      echo(mysql_error(inputclassbase::$dbconnection));
	else
	  echo("OK\r\n");
    echo("\r\n". $query. "\r\n");
	if($this->dbkey <= 0)
  	  $this->dbkey = mysql_insert_id(inputclassbase::$dbconnection);
  }
  public function __toString()
  {
    if($this->dbkey > 0)
	{
	  $getval = $this->load_query("SELECT `". $this->dbfield. "`,`". $this->dbkeyfield. "` FROM ". $this->dbtable. " WHERE `". $this->dbkeyfield. "`=". $this->dbkey. (isset($this->extrakey) ? " AND `". $this->extrakeyfield. "`=". $this->extrakey : ""));
	  if(isset($getval))
	    if(isset($getval[$this->dbfield][0]))
    	  return($getval[$this->dbfield][0]);
		else
		  return($this->defaultvalue);
	  else
	    return($this->defaultvalue);
	}
	else
	  return($this->defaultvalue);
  }
  public function echo_html()
  {
    // Put a checkbox 
    parent::echo_html();
    $val= $this->__toString();
    echo("<INPUT TYPE=CHECKBOX NAME=\"". $this->fieldid. "\"". ($val == 1 ? " CHECKED" : ""));
	echo($this->styledata());
	if($this->dbfield != NULL || $this->handlerpage != NULL)
	  echo(" onChange='return(send_xmlcb(\"". $this->fieldid. "\",this));'");
    echo(">");
  }
}
?>