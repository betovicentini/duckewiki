function changeoptionlist(varvalue, gazid)
{
if (varvalue=="")
  {
  document.getElementById("txtHint").innerHTML="";
  return;
  }
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    document.getElementById("txtHint").innerHTML=xmlhttp.responseText;
    }
  }
 xmlhttp.open("GET","changetaxalist.php?gazetteerid="+gazid+"&onme="+varvalue,true);
 xmlhttp.send();
} 

function showUser(str)
{
if (str=="")
  {
  document.getElementById("txtHint").innerHTML="";
  return;
  }
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    document.getElementById("txtHint").innerHTML=xmlhttp.responseText;
    }
  }
xmlhttp.open("GET","getuser.php?q="+str,true);
xmlhttp.send();
}

function reloadImg(id) {
   var obj = document.getElementById(id);
   var src = obj.src;
   var pos = src.indexOf('?');
   if (pos >= 0) {
      src = src.substr(0, pos);
   }
   var date = new Date();
   obj.src = src + '?v=' + date.getTime();
   return false;
}

function changemap(selid, gazid, mapid)
{	
if (selid=="")
  {
  document.getElementById(mapid).innerHTML="";
  return;
  }
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttpmap=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttpmap=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttpmap.onreadystatechange=function()
  {
  if (xmlhttpmap.readyState==4 && xmlhttpmap.status==200)
    {
    document.getElementById(mapid).innerHTML=xmlhttpmap.responseText;
    }
  }
if (selid=='specieslist') {
var selectedArray = '';
var selObj = document.getElementById(selid);
var i;
for (i=0; i<selObj.options.length; i++) {
  if (selObj.options[i].selected) {
      selectedArray = selectedArray+' '+selObj.options[i].value;
  }
 }
}
else {
	var selectedArray = selid;
}
 xmlhttpmap.open("GET","graph_plotmap.php?gazetteerid="+gazid+"&idds="+selectedArray,true);
 xmlhttpmap.send();
 
} 


function changehabitatlist(gazid)
{
if (gazid=="")
  {
  document.getElementById("txtHint").innerHTML="";
  return;
  }
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    document.getElementById("txtHint").innerHTML=xmlhttp.responseText;
    }
  }
 xmlhttp.open("GET","changehabitatlist.php?parentid="+gazid,true);
 xmlhttp.send();
} 

function changehabitatmap(parid, mapid, divid)
{	
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    document.getElementById(mapid).innerHTML=xmlhttp.responseText;
    }
  }
 xmlhttp.open("GET","plothabitat_map.php?parentid="+parid+"&divid="+divid,true);
 xmlhttp.send();
} 

function changemapimage(imgname, mapid)
{	
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttpimg=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttpimg=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttpimg.onreadystatechange=function()
  {
  if (xmlhttpimg.readyState==4 && xmlhttpimg.status==200)
    {
    document.getElementById(mapid).innerHTML=xmlhttpimg.responseText;
    }
  }
 xmlhttpimg.open("GET","graph_plotmap.php?imgfile="+imgname,true);
 xmlhttpimg.send();
} 


function changemapimgform(gazid, formid)
{	
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttpform=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttpform=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttpform.onreadystatechange=function()
  {
  if (xmlhttpform.readyState==4 && xmlhttpform.status==200)
    {
    document.getElementById(formid).innerHTML=xmlhttpform.responseText;
    }
  }
 xmlhttpform.open("GET","graph_plotmap_imgform.php?gazetteerid="+gazid,true);
 xmlhttpform.send();
} 

function addLoadEvent(func) {
  var oldonload = window.onload;
  if (typeof window.onload != 'function') {
    window.onload = func;
  } else {
    window.onload = function() {
      if (oldonload) {
        oldonload();
      }
      func();
    }
  }
}

function changemapdbhs(dbhdivid)
{	
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttpdbhs=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttpdbhs=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttpdbhs.onreadystatechange=function()
  {
  if (xmlhttpdbhs.readyState==4 && xmlhttpdbhs.status==200)
    {
    document.getElementById(dbhdivid).innerHTML=xmlhttpdbhs.responseText;
    }
  }
 xmlhttpdbhs.open("GET","graph_species_plotDBHs.php",true);
 xmlhttpdbhs.send();
}