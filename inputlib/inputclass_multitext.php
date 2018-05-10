<?
require_once("inputclassbase.php");
class inputclass_multitext extends inputclassbase
{
  protected $separator,$size;
  protected $values;
  public function __construct($fieldid,$size,$dbconnection = NULL,$dbfield = NULL, $dbtable = NULL, $dbkey = NULL, $dbkeyfield = NULL, $style = NULL, $handler = NULL, $extrafield = NULL, $extravalue = NULL)
  {
    parent::__construct($fieldid,$dbconnection,$dbfield,$dbtable,$dbkey,$dbkeyfield,$style,$handler,$extrafield,$extravalue);
	$this->size = $size;
	// Get the values for each existing entry
	$vals = inputclassbase::load_query("SELECT `". $dbfield. "` FROM ". $dbtable. " WHERE `". $dbkeyfield. "`=". $dbkey);
	if(isset($vals))
	{
	  foreach($vals[$dbfield] AS $vix => $val)
	    $this->values[$vix+1] = $val;
	}
	$this->separator = ", ";
  }

  public function handle_input()
  {
   if($_POST['fieldid'] == $this->fieldid)
	{
	  if(isset($this->values))
	    foreach($this->values AS $vk => $dummy)
	    {
	      if(isset($_POST[$this->fieldid. "-". $vk]))
		    $ix = $vk;
	    }
	  else
	    $ix=0;
	  if(isset($_POST[$this->fieldid. "-0"]))
	    $ix = 0;
	  if(isset($this->values[$ix]))
	    $orgval = $this->values[$ix];
	  if(!isset($orgval) || $_POST[$this->fieldid. "-". $ix] != $orgval)
	  {
		if($_POST[$this->fieldid. "-". $ix] == "")
		  $newval = "NULL";
		else
  	      $newval = "\"". $_POST[$this->fieldid. "-". $ix]. "\"";
		
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
		  $query .= ") ON DUPLICATE KEY UPDATE `". $this->dbfield. "`=". $newval;
		  if(isset($this->extrafield))
		    foreach($this->extrafield AS $fnm => $fvl)
		      $query .= ",`". $fnm. "`=\"". $fvl. "\"";
	      $this->values[$ix] = $newval;
		}
		else if($newval == "NULL")
		{
		  $query = "DELETE FROM ". $this->dbtable;
		  $query .= " WHERE `". $this->dbkeyfield. "`=". $this->dbkey;
		    $query .= " AND `". $this->dbfield. "`=". $orgval;		  
          if(isset($this->extrakeyfield))
		    $query .= " AND `". $this->extrakeyfield. "`=\"". $this->extrakey. "\"";
          unset($this->values[$ix]);
		}
		else
		{
		  $query = "UPDATE ". $this->dbtable. " SET `". $this->dbfield. "`=". $newval;
		  if(isset($this->extrafield))
		    foreach($this->extrafield AS $fnm => $fvl)
		      $query .= ",`". $fnm. "`=\"". $fvl. "\"";
		  $query .= " WHERE `". $this->dbkeyfield. "`=". $this->dbkey;
		    $query .= " AND `". $this->dbfield. "`=\"". $orgval. "\"";		  
          if(isset($this->extrakeyfield))
		    $query .= " AND `". $this->extrakeyfield. "`=\"". $this->extrakey. "\"";
		  $this->values[$ix] = $newval;
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
	}
  }

  public function set_separator($val)
  {
    $this->separator = $val;
  }

  public function __toString()
  {
    if($this->dbkey > 0)
    {
	  if(isset($this->values))
	  {
	    $retval = "";
	    foreach($this->values AS $rval)
		  $retval .= $this->separator. $rval;
		return substr($retval,strlen($this->separator));  
	  }
      else
        return(NULL);
    }
    else
    {
       return(NULL);
    }
  }

  public function echo_html()
  {
    parent::echo_html();
	$firstsep = true;
	if(isset($this->values))
	{
	  foreach($this->values AS $ix => $val)
	  {
	    if($firstsep)
		  $firstsep = false;
		else
		  echo($this->separator);
        echo("<INPUT TYPE=TEXT NAME=\"". $this->fieldid. "-". $ix. "\" VALUE=\"". htmlspecialchars($val). "\"");
	    if($this->size != NULL)
	      echo(" SIZE=". $this->size);
	    echo($this->styledata());
	    if($this->dbfield != NULL || $this->handlerpage != NULL)
	      echo(" onChange='return(send_xml(\"". $this->fieldid. "\",this));'");
        echo(">");
      }
	}
	if($firstsep)
	  $firstsep = false;
    else
	  echo($this->separator);
    echo("<INPUT TYPE=TEXT NAME=\"". $this->fieldid. "-0\"");
	if($this->size != NULL)
	  echo(" SIZE=". $this->size);
    echo($this->styledata());
    if($this->dbfield != NULL || $this->handlerpage != NULL)
      echo(" onChange='return(send_xml(\"". $this->fieldid. "\",this));'");
    echo(">");
  }
}
?>