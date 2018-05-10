<?
abstract class inputclassbase
{
  protected $fieldid;
  protected $dbkey, $dbkeyfield,$extrakey,$extravalue;
  public static $dbconnection,$dbname;
  protected $dbfield,$dbtable;
  protected $style;
  protected $extrafield;
  protected $handlerpage;
  abstract public function handle_input();
  abstract public function __toString();
  public function __construct($fieldid,$dbconnection = NULL,$dbfield = NULL, $dbtable = NULL, $dbkey = NULL, $dbkeyfield = NULL, $style = NULL, $handler = NULL, $extrafield = NULL, $extravalue = NULL)
  {
    $this->fieldid = $fieldid;
	if($dbconnection != NULL)
	  self::dbconnect($dbconnection);
	if($dbfield != NULL)
	  $this->fieldconnect($dbfield,$dbtable);
	if($dbkeyfield != NULL)
	{
	  $this->set_key($dbkey,$dbkeyfield);
	}
	$this->style = $style;
	if($handler != NULL)
	  $this->set_handler($handler);
	if($extrafield != NULL)
	  $this->set_extrafield($extrafield,$extravalue);
  } 
  public function get_key()
  {
    return($this->dbkey);
  }
  public function get_table()
  {
    return($this->dbtable);
  } 
  public function get_extrakey()
  {
    return($this->extrakey);
  }
  public function set_key($newkey, $newkeyfield = NULL)
  {
    $this->dbkey = $newkey;
	if($newkeyfield != NULL)
	{
	  $this->dbkeyfield = $newkeyfield;
	}
  }
  public function set_extrakey($extrakeyfield, $extrakey)
  {
    $this->extrakey = $extrakey;
	$this->extrakeyfield = $extrakeyfield;
  }
  public static function dbconnect($dbconnection,$dbname = NULL)
  {
    self::$dbconnection = $dbconnection;
	if($dbname != NULL)
	{
	  mysql_select_db($dbname,$dbconnection);
	  echo(mysql_error($dbconnection));
	}
	  mysql_set_charset("UTF8",$dbconnection);
	  mysql_query("SET NAMES UTF8",$dbconnection);
  }
  public static function dblogon($host,$uname,$pw,$dbname = NULL)
  {
    $dbcon = mysql_connect($host,$uname,$pw);
	self::dbconnect($dbcon,$dbname);
  }
  public function fieldconnect($field,$table)
  {
    $this->dbfield = $field;
	$this->dbtable = $table;
	if(isset($this->fieldid) && $this->fieldid != NULL)
	{
	  $_SESSION['inputobjects'][$this->fieldid] = $this;
	}
  }
  public function set_style($style)
  {
    $this->style = $style;
  }
  public function set_handler($handlerpage)
  {
    $this->handlerpage = $handlerpage;
  }
  public function inserted_key($newkey,$table=NULL,$extrakey = NULL)
  {
    if($this->dbkey == NULL || $this->dbkey <= 0)
	{
	  if($table == NULL || $table == $this->dbtable)
	  {
	    if($this->extrakey == NULL || $this->extrakey == $extrakey || $extrakey == NULL)
		  $this->set_key($newkey);
	  }
	}
  }
  public function set_extrafield($fieldname,$fieldvalue)
  {
    $this->extrafield[$fieldname] = $fieldvalue;
  }
	public function get_extrafield($xfieldname)
	{
		if(isset($this->extrafield[$xfieldname]))
			return $this->extrafield[$xfieldname];
		else
			return NULL;
	}
  public function echo_html()
  {
    require_once("xmlconnscript.php");
  }
  
  public static function load_query($sql_query)
  {
    // Function to do a MySQL query and store the result in an array
    $userlink = self::$dbconnection;
    $sql_result = mysql_query($sql_query,$userlink);
    if(mysql_error($userlink))
	{
      echo("\r\nFout by database query: " .$sql_query. ". Foutmelding: " .mysql_error($userlink));
	}
    $nrows = 0;
    if (mysql_num_rows($sql_result)!=0)
    {
      $nfields = mysql_num_fields($sql_result);
      for($r=0;$r<mysql_num_rows($sql_result);$r++)
      {
        $nrows++;
        $row = mysql_fetch_array($sql_result,MYSQL_NUM);
        for ($i=0;$i<$nfields;$i++)
        {
          $fieldname = mysql_field_name($sql_result,$i);
          $result[$fieldname][$r] = $row[$i];
        } // for $i
      } //for $r
      mysql_free_result($sql_result);
    }//If numrows != 0
    if(isset($result))
      return $result;
    else
      return null;
  }
  // Function to convert MYSQL date format to NL format
  public static function mysqldate2nl($datestring)
  {
    if(strlen($datestring) < 10)
      $returnstring="";
    else
      $returnstring = substr($datestring,-2) . "-" .substr($datestring,5,2) . "-" . substr($datestring,0,4);
    return $returnstring;
  }

  // Function to convert NL date format to Mysql
  public static function nldate2mysql($datestring)
  {
    return substr($datestring,6,4). "-". substr($datestring,3,2). "-". substr($datestring,0,2);
  }
  
  // Function to include style data
  protected function styledata()
  {
    if($this->style == NULL || $this->style == "")
	  return("");
	if(substr($this->style,0,6) == "class=")
	  return (" ". $this->style);
	else
	  return (" style=\"". $this->style. "\"");
  }
}
?>
