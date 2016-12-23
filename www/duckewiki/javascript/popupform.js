//Code downloaded from http://javascript.internet.com/forms/items-popup-list.html
//Date 2009-oct-28
// Add the selected items in the parent by calling method of parent



function addSelectedItemsToParent(formName,formParentName) {
//self.opener.addToParentList(window.document.forms[formName].destList,formParentName);
self.opener.MyArray(formName,formName);
window.close();
}




function sendvalclosewin(targetid,targetvalue){
	var destination = window.opener.document.getElementById(targetid);
	destination.value = targetvalue;
	window.close();
}

function sendval_innerHTML(sourceid,targetid){
	var valor = document.getElementById(sourceid).value;
	var element = window.opener.document.getElementById(targetid);
	element.innerHTML = valor;
	window.close();
}

function sendval_closewin(sourceid,targetid){
	var valor = document.getElementById(sourceid).value;
	var destination = window.opener.document.getElementById(targetid);
	destination.value = valor;
	window.close();
}


function sendvalclosewinTD(targetid,targetvalue){
	var element = window.opener.document.getElementById(targetid);
	element.value = targetvalue;
	window.close();
}


function sendval_innerHTMLnotclose(sourceid,targetid){
	var valor = document.getElementById(sourceid).value;
	var element = self.opener.document.getElementById(targetid);
	element.innerHTML = valor;
}
function sendvalclosewinnotclose(targetid,targetvalue){
	var destination = self.opener.document.getElementById(targetid);
	destination.value = targetvalue;
}

function changebutton(buttonid,buttonvalue){
	var element = window.opener.document.getElementById(buttonid);
	element.value = buttonvalue;	
	window.close();
}

function changeclass(idd,oldclass,newclass){
	document.getElementById(idd).className = document.getElementById(idd).className.replace(oldclass,newclass)
}

function SelectAll(id)
{
    document.getElementById(id).focus();
    document.getElementById(id).select();
}

function deletimage(sourceid,targetid,tagimgtodel,delval){
	var valor = document.getElementById(sourceid).value;
	var element = document.getElementById(targetid);
	element.innerHTML = valor;
	var todelete = document.getElementById(tagimgtodel);
	todelete.value = delval;
}

function showimage(sourceid,targetid,tagimgtodel,delval){
	var imgval = document.getElementById(sourceid).value;
	var imgelem = document.getElementById(targetid);
	imgelem.innerHTML = imgval;
	var imgtodel = document.getElementById(tagimgtodel);
	imgtodel.value = delval;
}

function sendval_toselectoption(sourceid,targetid,targetvalue){
	var valor = document.getElementById(sourceid).value;
	var element = window.opener.document.getElementById(targetid).options;
	element[0].value = valor;
	element[0].text = targetvalue;
	window.close();
}


function sendval_toselexec(sourceid,targetid){
	var valor = document.getElementById(sourceid).value;
	var habt = document.getElementById(hab).value;
	document.getElementById(targetid).innerHTML = habt;
}

function getandsendval(getid,targetid){
	var targetvalue = window.document.getElementById(getid).value;
	window.opener.document.getElementById(targetid).value = targetvalue;
	window.close();
}

function changevaluebyid(valor,targetid){
	var newvalor = document.getElementById(targetid).value;
	document.getElementById(targetid).value = valor+newvalor;
}

function getselectoptionsendtoinput(sourceid,targetid){
	var element = document.getElementById(sourceid);
	var valor = element.options[element.selectedIndex].text
	//var valor = element[0].innerHTML;
	document.getElementById(targetid).value = valor;
}


// Fill the selcted item list with the items already present in parent.
function fillInitialDestList(formName) {
var destList = window.document.forms[formName].destList; 
var srcList = self.opener.window.document.forms[formName].parentList;
for (var count = destList.options.length - 1; count >= 0; count--) {
destList.options[count] = null;
}
for(var i = 0; i < srcList.options.length; i++) { 
if (srcList.options[i] != null)
	destList.options[i] = new Option(srcList.options[i].text, srcList.options[i].value);
   }
}

// Add the selected items from the source to destination list
function addSrcToDestList(formName) {
destList = window.document.forms[formName].destList;
srcList = window.document.forms[formName].srcList; 
var len = destList.length;
for(var i = 0; i < srcList.length; i++) {
	if ((srcList.options[i] != null) && (srcList.options[i].selected)) {
	//Check if this value already exist in the destList or not
	//if not then add it otherwise do not add it.
		var found = false;
		for(var count = 0; count < len; count++) {
			if (destList.options[count] != null) {
				if (srcList.options[i].text == destList.options[count].text) {
					found = true;
					break;
		    	}
		   }
		}
		if (found != true) {
			destList.options[len] = new Option(srcList.options[i].text, srcList.options[i].value); 
		  //destList.options[len].value = new Option(srcList.options[i].value); 
		len++;
	    }
	}
}
} //end of function


function addSrcToDestListTraits(srcformName) {
destList = window.document.forms[srcformName].destList;
srcList = window.document.forms[srcformName].srcList; 
var len = destList.length;
for(var i = 0; i < srcList.length; i++) {
	if ((srcList.options[i] != null) && (srcList.options[i].selected)) {
	//Check if this value already exist in the destList or not
	//if not then add it otherwise do not add it.
		var found = false;
		for(var count = 0; count < len; count++) {
			if (destList.options[count] != null) {
				if (srcList.options[i].value == destList.options[count].value) {
					found = true;
					break;
		    	}
		   }
		}
		if (found != true) {
			var vv = srcList.options[i].value;
			var vvv = vv.split("|");
			var texto = vvv[1];
			if (texto!=null) {
				//destList.options[len] = new Option(srcList.options[i].text, srcList.options[i].value); 
			destList.options[len] = new Option(texto, srcList.options[i].value); 
			  //destList.options[len].value = new Option(srcList.options[i].value); 
			len++;
			}
	    }
	}
}
} //end of function

// Deletes from the destination list.
function deleteFromDestList(destformName) {
var destList  = window.document.forms[destformName].destList;
var len = destList.options.length;
for(var i = (len-1); i >= 0; i--) {
if ((destList.options[i] != null) && (destList.options[i].selected == true)) {
destList.options[i] = null;
      }
   }
}

//Original:  Pankaj Mittal (pankajm@writeme.com) /
//Web Site:  http://www.fortunecity.com/lavendar/lavender/21/
//This script and many more are available free online at /
//The JavaScript Source!! http://javascript.internet.com /

function small_window(myurl,mywidth,myheight,mywintitle) {
var newWindow;
var prop = "left=300,top=100,scrollBars=yes,resizable=yes,toolbar=no,menubar=no,location=no,directories=no,width=";
var prop2 = ", height=";
var teste = prop + mywidth + prop2 + myheight;
var props = teste;
newWindow = window.open(myurl, mywintitle, props);
newWindow.focus(); 
}


// Adds the list of selected items selected in the child
// window to its list. It is called by child window to do so.  
function MyArray2(popupformName,formName,tagvalue,tagtxt) {
	sourceList = window.document.forms[popupformName].destList;
	destinationListvalue = self.opener.window.document.forms[formName].elements[tagvalue];
	destinationListtxt = self.opener.window.document.getElementById[tagtxt];
	resval = Array(sourceList.options.length);
	restxt = Array(sourceList.options.length);
	for (var i = 0; i < sourceList.options.length; i++) {
	    if (sourceList.options[i] != null) {
		   resval[i] = sourceList.options[i].value;
		   restxt[i] = sourceList.options[i].text;
		}
   	}
   	resvv = resval.join("; ");
   	restext = restxt.join("; ");
   	destinationListtxt.innerHTML = restext;
   	destinationListvalue.value = resvv;
	window.close();
}

//converte lista para array
function MyArray(popupformName,formName,tagvalue,tagtxt) {
	sourceList = window.document.forms[popupformName].destList;
	destinationListvalue = self.opener.window.document.forms[formName].elements[tagvalue];
	destinationListtxt = self.opener.window.document.forms[formName].elements[tagtxt];
	resval = Array(sourceList.options.length);
	restxt = Array(sourceList.options.length);
	for (var i = 0; i < sourceList.options.length; i++) {
	    if (sourceList.options[i] != null) {
		   resval[i] = sourceList.options[i].value;
		   restxt[i] = sourceList.options[i].text;
		}
   	}
   	resvv = resval.join("; ");
   	restext = restxt.join("; ");
   	destinationListtxt.value = restext;
   	destinationListvalue.value = resvv;
   	//self.opener.window.document.forms[formName].submit();
  	window.close();
}



// Marks all the items as selected for the submit button.  
function selectList(formName) {
sourceList = window.document.forms[formName].parentList;
for(var i = 0; i < sourceList.options.length; i++) {
	if (sourceList.options[i] != null)
	sourceList.options[i].selected = true;
}
return true;
}

function passnewidandtxtoinputfield(valueid,textid,openervalid_id,openertxtid_id) {
	var valor = document.getElementById(valueid).value;
	targetidval = window.opener.document.getElementById(openervalid_id);
	targetidval.value = valor;
	var txt = document.getElementById(textid).value;
	targetidtxt = window.opener.document.getElementById(openertxtid_id);
	targetidtxt.value = txt;
	window.close();
}


function passnewidandtxtoselectfield(destlist,pessoaid,destpessoatxt,secondid) {
	var valor = document.getElementById(pessoaid).value;
	targedlist = window.opener.document.getElementById(destlist);
	for(var count = targedlist.options.length - 1; count >= 0; count--) {
		if (count==1) {
			targedlist.options[count].value = valor;
			targedlist.options[count].text = destpessoatxt;
			targedlist.options[count].selected = true;
			
		} else {
			targedlist.options[count].selected = false;
		}
	}
	if (secondid) {
		targedlist2 = window.opener.document.getElementById(secondid);
		targedlist2.options[1] = new Option(destpessoatxt,valor); 
	}
	window.close();
}

// Deletes the selected items of supplied list.
function deleteSelectedItemsFromList(sourceList) {
var maxCnt = sourceList.options.length;
for(var i = maxCnt - 1; i >= 0; i--) {
if ((sourceList.options[i] != null) && (sourceList.options[i].selected == true)) {
sourceList.options[i] = null;
      }
   }
}

function limitText(limitField, limitCount, limitNum) {
	if (limitField.value.length > limitNum) {
		limitField.value = limitField.value.substring(0, limitNum);
	} else {
		limitCount.value = limitNum - limitField.value.length;
	}
}

function closepopupwindow() {
  	window.close();
}

function refreshparent(parentform){	
	sourceList = self.opener.window.document.forms[parentform].destList;
	resval = Array(sourceList.options.length);
	for (var i = 0; i < sourceList.options.length; i++) {
		    if (sourceList.options[i] != null) {
			   resval[i] = sourceList.options[i].value;
			}
   	}
	resvv = resval.join("; ");	
	destinationList = self.opener.window.document.detailedform.details;
	destinationList.value = resvv;
	self.opener.window.document.detailedform.submit();  	
	window.close();
}


function sendValue(popupid,targetform,targetid,closewin){
	var selvalue = window.document.getElementById(popupid).value;
	window.opener.document.getElementById(targetid).value = selvalue;
   	//window.opener.document.forms[targetform].submit();
	destination = self.opener.window.document.forms[targetform].teste2;
	destination.value = 'Foi selecionado';
	if (closewin.value="TRUE") { 
		window.close();
	}
}

function sendvalandrefreshparent(popupid,targetform,targetid,closewin){
	var selvalue = window.document.getElementById(popupid).value;
	window.opener.document.getElementById(targetid).value = selvalue;
   	window.opener.document.forms[targetform].submit();
	if (closewin.value="TRUE") { 
		window.close();
	}
}


function passid(popupid,targetform,targetid,closewin){
	var selvalue = popupid.value;
	window.opener.document.getElementById(targetid).value = selvalue;
   	window.opener.document.forms[targetform].submit();
	if (closewin.value="TRUE") { 
		window.close();
	}
}

function passsinglefield(popupform,popupfield,targetform,targetfield,closewin) {
	sourcelst = window.document.forms[popupform].elements[popupfield];
	destlist = self.opener.window.document.forms[targetform].elements[targetfield];
	destlist.value = sourcelst.value;
   	self.opener.window.document.forms[targetform].submit();
	if (closewin.value="TRUE") { 
		window.close();
	}
}

function getvarfromform(popupform,popupvarid,varid) {
	var sourcevar = self.window.document.getElementById(varid).value;
	self.window.document.getElementById(popupvarid).value = sourcevar;
   	window.document.forms[popupform].submit();
}


function selectaxa(listformName,sourceformName,destformName,desttag,sourcethertag,destothertag,destlevel,destleveltag) {
	sourceList = window.document.forms[listformName].destList;
	resval = Array(sourceList.options.length);
	for (var i = 0; i < sourceList.options.length; i++) {
		    if (sourceList.options[i] != null) {
			   resval[i] = sourceList.options[i].value;
			}
   	}
	resvv = resval.join("; ");
	sourcevariable = window.document.forms[sourceformName].elements[sourcethertag];
	sourcevariable2 = window.document.forms[sourceformName].elements[destlevel];

	window.document.forms[sourceformName].submit();  	
	destinationListvalue = window.document.forms[destformName].elements[desttag];
	destinationvariable = window.document.forms[destformName].elements[destothertag];
	destinationvariable2 = window.document.forms[destformName].elements[destleveltag];
   	destinationListvalue.value = resvv;
   	destinationvariable.value = sourcevariable.value;
   	destinationvariable2.value = sourcevariable2.value;
  	window.document.forms[destformName].submit();
}

function gethrefvalue(atagid){
	var selvalue = window.document.getElementById(atagid).href;
	var vvv = selvalue.split("/");
	vvv[5] = 'originais';
	var vf = vvv.join("/");
	window.open(vf);
}


//sourceList = window.document.forms[popupformName].destList;
//	destinationListvalue = self.opener.window.document.forms[formName].elements[tagvalue];
//	destinationListtxt = self.opener.window.document.forms[formName].elements[tagtxt];
	
function sendarraytoparent(sourceformName,sourceelement,targetform,targetelement,tempelement) {
	sourceList = window.document.forms[sourceformName].elements[sourceelement];
	resval = Array(sourceList.options.length);
	for (var i = 0; i < sourceList.options.length; i++) {
		    if (sourceList.options[i] != null) {
			   resval[i] = sourceList.options[i].value;
			}
   	}
	resvv = resval.join("; ");	
	destinationList = self.opener.window.document.forms[targetform].elements[targetelement];
   	destinationList.value = resvv;
	destselect = self.opener.window.document.forms[targetform].elements[tempelement];
	destselect.value = 'Foram selecionados';
  	//self.opener.window.document.forms[targetform].submit();
  	window.close();
}


function sendarrayandvaluetoself(sourceformName,sourceelement,targetform,targetelement,elemval) {
	var selvalue = window.document.forms[sourceformName].elements[elemval].value;
	sourceList = window.document.forms[sourceformName].elements[sourceelement];
	resval = Array(sourceList.options.length);
	for (var i = 0; i < sourceList.options.length; i++) {
		    if (sourceList.options[i] != null) {
			   resval[i] = sourceList.options[i].value;
			}
   	}
	resvv = resval.join("; ");
	destinationList = window.document.forms[targetform].elements[targetelement];
   	destinationList.value = resvv;
  	window.document.forms[targetform].elements[elemval].value = selvalue;
  	window.document.forms[targetform].submit();
  	window.close();
}

function sendarrayatoself(sourceformName,sourceelement,targetform,targetelement) {
	sourceList = window.document.forms[sourceformName].elements[sourceelement];
	resval = Array(sourceList.options.length);
	for (var i = 0; i < sourceList.options.length; i++) {
		    if (sourceList.options[i] != null) {
			   resval[i] = sourceList.options[i].value;
			}
   	}
	resvv = resval.join("; ");
	destinationList = window.document.forms[targetform].elements[targetelement];
   	destinationList.value = resvv;
  	window.document.forms[targetform].submit();
}


function sendarrayatoself(sourceformName,sourceelement,targetform,targetelement) {
	sourceList = window.document.forms[sourceformName].elements[sourceelement];
	resval = Array(sourceList.options.length);
	for (var i = 0; i < sourceList.options.length; i++) {
		    if (sourceList.options[i] != null) {
			   resval[i] = sourceList.options[i].value;
			}
   	}
	resvv = resval.join("; ");
	destinationList = window.document.forms[targetform].elements[targetelement];
   	destinationList.value = resvv;
  	window.document.forms[targetform].submit();
}

function target_popup(form) {
    window.open('', 'formpopup', 'width=900,height=700,resizeable,scrollbars');
    form.target = 'formpopup';
}


