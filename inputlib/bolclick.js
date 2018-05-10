<SCRIPT>
function bolclick(butobj)
{
	bollist = document.getElementsByName(butobj.name);
	for(i=0; i<bollist.length; i++)
	{
		if(bollist[i].multiple)
		{ // this was the active button!
			if(bollist[i].value==butobj.value)
			{ // reactivated the current button, so deactivate sending 0 as value
				orgval = butobj.value;
				butobj.value=0;
				send_xml(butobj.name,butobj);
				butobj.value=orgval;
				butobj.checked=false;
				butobj.multiple=false;
			}
			else
			{ // Deactivate this button
				bollist[i].multiple=false;
			}
		}
		else
		{
			if(bollist[i].value==butobj.value)
			{ // Inactive button, and has been clicked
				butobj.multiple=true;
				send_xml(butobj.name,butobj);
			}
		}
	}
	//alert("Button clicked" + butobj.name + " : " + butobj.value);
}
</SCRIPT>