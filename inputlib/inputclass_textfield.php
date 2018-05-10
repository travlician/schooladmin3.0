<?
require_once("inputclassbase.php");
class inputclass_textfield extends inputclassbase
{
  protected $size;
  public $initial_value;
  public function __construct($fieldid,$size,$dbconnection = NULL,$dbfield = NULL, $dbtable = NULL, $dbkey = NULL, $dbkeyfield = NULL, $style = NULL, $handler = NULL, $extrafield = NULL, $extravalue = NULL)
  {
    parent::__construct($fieldid,$dbconnection,$dbfield,$dbtable,$dbkey,$dbkeyfield,$style,$handler,$extrafield,$extravalue);
	$this->size = $size;
  }

  public function handle_input()
  {
   if(isset($_POST[$this->fieldid]))
	{
	  $orgval = $this->__toString();
	  if(strcmp($_POST[$this->fieldid],$orgval) != 0)
	  {
		if($_POST[$this->fieldid] == "")
		  $newval = "NULL";
		else
		{
		  if(!get_magic_quotes_gpc())
  	        $newval = "\"". addslashes($_POST[$this->fieldid]). "\"";
		  else
		    $newval = "\"". $_POST[$this->fieldid]. "\"";
		}
		
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
		if(mysql_error(parent::$dbconnection))
		  echo(mysql_error(parent::$dbconnection));
		else
		  echo("OK (". mysql_affected_rows(parent::$dbconnection). ")\r\n");
		echo("\r\n". $query. "\r\n");
		if($this->dbkey <= 0)
  		  $this->dbkey = mysql_insert_id(parent::$dbconnection);
	  }
	  else
	    echo("Identical value (". (string)$_POST[$this->fieldid]. "=". (string)$orgval. ")");
	}
  }

  public function set_initial_value($val,$onExisting=false)
  {
    $this->initial_value = $val;
		if($onExisting)
			$this->existing_value = $val;
  }

  public function __toString()
  {
    if($this->dbkey > 0)
    {
      $getval = $this->load_query("SELECT `". $this->dbfield. "`,`". $this->dbkeyfield. "` FROM ". $this->dbtable. " WHERE `". $this->dbkeyfield. "`=". $this->dbkey. (isset($this->extrakey) ? " AND `". $this->extrakeyfield. "`=\"". $this->extrakey. "\"" : ""));
      if(isset($getval))
        if(isset($getval[$this->dbfield][0]))
					return($getval[$this->dbfield][0]);
				else
				{
					if(isset($this->existing_value))
						return($this->existing_value);
					else
						return("");
				}
			else
        return(NULL);
    }
    else
    {
      if(isset($this->initial_value))
        return($this->initial_value);
      else
        return("");
    }
  }

  public function echo_html()
  {
    parent::echo_html();
    $val= $this->__toString();
    echo("<INPUT TYPE=TEXT NAME=\"". $this->fieldid. "\" ID=\"". $this->fieldid. "\" VALUE=\"". htmlspecialchars($val). "\"");
		if($this->size != NULL)
			echo(" SIZE=". $this->size);
		echo($this->styledata());
		if($this->dbfield != NULL || $this->handlerpage != NULL)
			echo(" onChange='return(send_xml(\"". $this->fieldid. "\",this));'");
    echo(">");
  }
}
?>