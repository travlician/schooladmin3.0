 <? 
require_once("schooladminconstants.php");
$targetname = time()."_".$_FILES['upload']['name'];
$url = $livepictures .$targetname;
 //extensive suitability check before doing anything with the file...
    if (($_FILES['upload'] == "none") OR (empty($_FILES['upload']['name'])) )
    {
       $message = "No file uploaded.";
    }
    else if ($_FILES['upload']["size"] == 0)
    {
       $message = "The file is of zero length.";
    }
    else if (($_FILES['upload']["type"] != "image/pjpeg") AND ($_FILES['upload']["type"] != "image/jpeg") AND ($_FILES['upload']["type"] != "image/png") AND ($_FILES['upload']["type"] != "application/pdf"))
    {
       $message = "The image must be in either JPG, PNG or PDF format. Please upload a JPG, PNG or PDF instead. (detected type: ". $_FILES['upload']["type"];
    }
    else if (!is_uploaded_file($_FILES['upload']["tmp_name"]))
    {
       $message = "You may be attempting to hack our server. We're on to you; expect a knock on the door sometime soon.";
    }
    else {
      $message = "";
      $move = move_uploaded_file($_FILES['upload']['tmp_name'], $picturespath. $targetname);
      if(!$move)
      {
	     $err = error_get_last();
		 if(isset($err['message']))
           $message = "Error moving uploaded file. Check the script is granted Read/Write/Modify permissions. (". addslashes($err['message']). ")";
		 else
           $message = "Error moving uploaded file. Check the script is granted Read/Write/Modify permissions. ()";
      }
    }
 
$funcNum = $_GET['CKEditorFuncNum'] ;
echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '". $livepictures. $targetname. "', '$message');</script>";
?>