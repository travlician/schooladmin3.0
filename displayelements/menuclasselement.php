<?
require_once("displayelement.php");

class menuclasselement extends displayelement
{
  protected $defaultclass;
  protected $defaultdivid;
  protected $defaultstyle;
  protected $paramname;
  protected $classpath;
  protected $showclass;
  public function __construct($divid = NULL, $style = NULL, $classpath = NULL, $paramname = "Page", $defaultclass = NULL, $defaultdivid = NULL, $defaultstyle = NULL)
  {
    if(isset($classpath))
	  $this->classpath = $classpath;
	if(isset($defaultclass))
      $this->defaultclass = $defaultclass;
	if(isset($defaultdivid))
      $this->defaultdivid = $defaultdivid;
	if(isset($defaultstyle))
      $this->defaultstyle = $defaultstyle;
	$this->paramname = $paramname;
	parent::__construct($divid, $style);
  }
  protected function show_contents()
  {
    if(isset($this->showclass))
	  $this->showclass->show();
  }
  protected function add_contents()
  {
    if(isset($_GET[$this->paramname]))
      $classname = $_GET[$this->paramname];
	else if(isset($this->defaultclass))
	  $classname = $this->defaultclass;
	if(isset($classname))
	{
	  if(isset($this->classpath))
	    require_once($this->classpath. $classname. ".php");
	  $this->showclass = new $classname ($this->defaultdivid, $this->defaultstyle);
	}
  }
  public function set_default_class($classname)
  {
    $this->defaultclass = $classname;
  }
  public function set_default_div_id($divid)
  {
    $this->defaultdivid = $divid;
  }
  public function set_default_style($style)
  {
    $this->defaultstyle = $style;
  }
  public function set_parameter_name($paramname)
  {
    $this->paramname = $paramname;
  }
}
?>
