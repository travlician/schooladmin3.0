<?
require_once("inputclassbase.php");
class inputclass_listfield extends inputclassbase
{
  protected $sourcequery,$selval,$readonly;
  public function __construct($fieldid,$sourcequery,$dbconnection = NULL,$dbfield = NULL, $dbtable = NULL, $dbkey = NULL, $dbkeyfield = NULL, $style = NULL, $handler = NULL, $extrafield = NULL, $extravalue = NULL)
  {
    parent::__construct($fieldid,$dbconnection,$dbfield,$dbtable,$dbkey,$dbkeyfield,$style,$handler,$extrafield,$extravalue);
	$this->sourcequery = $sourcequery;
  }

  public function handle_input()
  {
    if(isset($_POST[$this->fieldid]))
	{
	  if($_POST[$this->fieldid] != $this->get_state())
	  {
	    $orgval = $this->get_state();
		if($_POST[$this->fieldid] == "")
		  $newval = "NULL";
		else
  	      $newval = "\"". $_POST[$this->fieldid]. "\"";
		
	    if(!$this->record_exists())
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
		mysql_query($query,parent::$dbconnection);
		if(mysql_error(inputclassbase::$dbconnection))
		  echo(mysql_error(parent::$dbconnection));
		else
		  echo("OK\r\n");
		
		echo("\r\n". $query. "\r\n");
		if($this->dbkey <= 0)
  		  $this->dbkey = mysql_insert_id(parent::$dbconnection);
	  }
	  else
	    echo("OK\r\n");
	}
  }

  public function __toString()
  {
    if($this->dbkey > 0)
	{
	  $getval = $this->load_query("SELECT id,tekst FROM ". $this->dbtable. " LEFT JOIN (". $this->sourcequery. ") AS t1 ON(id=". $this->dbfield. ") WHERE `". $this->dbkeyfield. "`=". $this->dbkey. (isset($this->extrakeyfield) ? " AND `". $this->extrakeyfield. "`=". $this->extrakey : ""));
	  if(isset($getval))
	  {
	    $selval = $getval['id'][0];
	    return($getval['tekst'][0]);
	  }
	  else
	    return("");
	}
	else
	  return("");
  }

  public function get_state()
  {
    if($this->dbkey > 0)
	{
	  $getval = $this->load_query("SELECT id,tekst FROM ". $this->dbtable. " LEFT JOIN (". $this->sourcequery. ") AS t1 ON(id=". $this->dbfield. ") WHERE `". $this->dbkeyfield. "`=". $this->dbkey. (isset($this->extrakeyfield) ? " AND `". $this->extrakeyfield. "`=". $this->extrakey : ""));
	  if(isset($getval['id']))
	    return $getval['id'][0];
	  else
	    return NULL;
	}
	else
	{
	  $getval = $this->load_query($this->sourcequery);
	  return $getval['id'][0];
	}
  }

  public function record_exists()
  {
    if($this->dbkey > 0)
	{
	  $getval = $this->load_query("SELECT ". $this->dbfield. " FROM ". $this->dbtable. " WHERE `". $this->dbkeyfield. "`=". $this->dbkey. (isset($this->extrakeyfield) ? " AND `". $this->extrakeyfield. "`=". $this->extrakey : ""));
	  if(isset($getval[$this->dbfield]))
	    return true;
	  else
	    return false;
	}
	else
	{
      return false;
 	}
  }

  public function echo_html()
  {
    parent::echo_html();
    $this->selval = $this->get_state();
	$selitems = $this->load_query($this->sourcequery);
	echo("<SELECT NAME=\"". $this->fieldid. "\" ID=\"". $this->fieldid. "\"");
	echo($this->styledata());
	if($this->readonly)
	  echo(" DISABLED");
	if($this->dbfield != NULL || $this->handlerpage != NULL)
	  echo(" onChange='return(send_xmlsl(\"". $this->fieldid. "\",this));'");
    echo(">");

	foreach($selitems['id'] AS $sk => $si)
	  echo("<OPTION VALUE='". $si. "'". ($si == $this->selval ? " selected" : ""). ">". $selitems['tekst'][$sk]. "</OPTION>");
    echo("</SELECT>");
  }
  
  public function set_readonly()
  {
    $this->readonly = TRUE;
  }
}
?>