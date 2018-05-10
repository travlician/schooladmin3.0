<?
abstract class displayelement
{
  protected $style;
  protected $divid;
  abstract protected function add_contents();
  abstract protected function show_contents();
  public function __construct($divid = NULL, $style = NULL)
  {
    if(isset($divid))
	  $this->divid = $divid;
	if(isset($style))
	  $this->style = $style;
	$this->add_contents();
  } 
  public function set_divid($divid)
  {
    $this->divid = $divid;
  }
  public function set_style($style)
  {
    $this->style = $style;
  }
  protected function pre_show()
  {
    $retstr = "";
    if(isset($this->divid) || isset($this->style))
	{
      $retstr .= "<div";
	  if(isset($this->divid))
	    $retstr .= " id=". $this->divid;
	  if(isset($this->style))
	  {
	    $retstr .= " ";
	    if(substr($this->style,0,6) != "class=")
	      $retstr .= "style=\"". $this->style. "\"";
		else
  	      $retstr .= $this->style;
	  }
      $retstr .= ">";
	}
	echo($retstr);
  }
  protected function post_show()
  {
    $retstr = "";
    if(isset($this->divid) || isset($this->style))
      $retstr .= "</div>";
	echo($retstr);
  }
  public function show()
  {
    $this->pre_show();
	$this->show_contents();
	$this->post_show();
  }
}
?>
