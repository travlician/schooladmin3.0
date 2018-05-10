<SCRIPT>
var elems = document.getElementsByTagName('img');
var telems = [];
var tepos = 0;
var titlediv;

function touchfix()
{
	for(i=0;i<elems.length;i++)
	{
//	  if('title' in elems[i] && elems[i].title != '' && 'ontouchstart' in document.documentElement && elems[i].offsetParent.tagName != 'TD')
	  if('title' in elems[i] && elems[i].title != '' && 'ontouchstart' in document.documentElement && elems[i].offsetParent != null && elems[i].offsetParent.tagName != 'TD')
	  {
		telems[tepos] = elems[i];
		tepos = tepos + 1;
	  }
	  
	}
	
	for(i=0;i<elems.length;i++)
	{
	  if(elems[i].width < 20 && 'ontouchstart' in document.documentElement)
	    elems[i].width = 2 * elems[i].width;
	}
	
	
  if(telems.length > 0)
  {
	titlediv = document.createElement('div');
	titlediv.style.width='auto';
	titlediv.style.display='block';
	titlediv.style.position='absolute';
	titlediv.style.background='#FF0';
	setInterval('cycle_titles()',1500);
  }
}
  
function cycle_titles()
{
  if(typeof(telems[tepos]) != 'undefined')
    telems[tepos].offsetParent.removeChild(titlediv);
  tepos = tepos + 1;
  if(tepos >= telems.length)
    tepos = 0;
  titlediv.style.left=(telems[tepos].offsetLeft + telems[tepos].offsetWidth / 2) + 'px';
  titlediv.style.top =((telems[tepos].offsetTop + telems[tepos].offsetHeight / 2) - 10) + 'px';
  titlediv.innerHTML=telems[tepos].title;
  telems[tepos].offsetParent.appendChild(titlediv);
}
if(window.attachEvent)
  window.attachEvent("onload",touchfix);
else
  window.addEventListener("load",touchfix,false);
</SCRIPT>