<?
require_once("displayelement.php");

class menufileelement extends displayelement
{
  protected $defaultfile;
  protected $paramname;
  public function __construct($divid = NULL, $style = NULL, $paramname = "Page", $defaultfile = NULL)
  {
	if(isset($defaultfile))
      $this->defaultfile = $defaultfile;
	$this->paramname = $paramname;
	parent::__construct($divid, $style);
  }
  protected function show_contents()
  {
    if(isset($_GET[$this->paramname]))
      include($_GET[$this->paramname]. ".php");
	else
	  include($this->defaultfile. ".php");
  }
  protected function add_contents()
  {
  }
  public function set_file($file)
  {
    $this->file = $file;
  }
  public function set_parameter_name($paramname)
  {
    $this->paramname = $paramname;
  }
}
?>
