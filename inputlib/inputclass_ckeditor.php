<?
require_once("inputclass_textfield.php");
class inputclass_ckeditor extends inputclass_textarea
{
  protected $stylefile,$language;
  
  public function set_stylefile($stylefile)
  {
    $this->stylefile = $stylefile;
  }
  
  public function set_language($language)
  {
    $this->language = $language;
  }
  
  public function echo_html()
  {
    global $livesite;
    echo('<script src="inputlib/ckeditor/ckeditor.js"></script>');
	parent::echo_html();
	if(isset($this->stylefile) && isset($this->language))
	  echo("<script>  CKEDITOR.replace( '". $this->fieldid. "',{contentsCss : '". $this->stylefile. "', language : '". $this->language. "'}); </script>");
	else if(isset($this->stylefile))
	  echo("<script>  CKEDITOR.replace( '". $this->fieldid. "',{contentsCss : '". $this->stylefile. "'}); </script>");
	else if(isset($this->language))
	  echo("<script>  CKEDITOR.replace( '". $this->fieldid. "',{language : '". $this->language. "'}); </script>");
	else
	  echo("<script>  CKEDITOR.replace( '". $this->fieldid. "'); </script>");
    echo("<script>  CKEDITOR.instances['". $this->fieldid. "'].on('blur', function(e) {
          if (e.editor.checkDirty()) {
		        fobj = document.getElementsByName('". $this->fieldid. "')[0];
		        //alert(document.getElementsByName('". $this->fieldid. "')[0]);
				fobj.value = CKEDITOR.instances['". $this->fieldid. "'].getData();
                send_xml('". $this->fieldid. "',fobj);
				 } } ); </script>");
  }
}
?>