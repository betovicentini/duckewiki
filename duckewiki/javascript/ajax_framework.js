/* ---------------------------- */
/* XMLHTTPRequest Enable 		*/
/* ---------------------------- */
function createObject() {
	var request_type;
	var browser = navigator.appName;
	if(browser == "Microsoft Internet Explorer"){
	request_type = new ActiveXObject("Microsoft.XMLHTTP");
	}else{
		request_type = new XMLHttpRequest();
	}
		return request_type;
}

var http = createObject();

/* -------------------------- */
/* SEARCH					 */
/* -------------------------- */
function autosuggest(idtag,idres,file,nomeid,all) {
gElem = idres;
idElem = nomeid;
q = document.getElementById(idtag).value;
q = escape(q);
// Set te random number to add to URL request
nocache = Math.random();
http.open('get', 'javascript/'+file+'?idtag='+idtag+'&idres='+idres+'&nomeid='+nomeid+'&q='+q+'&nocache = '+nocache+'&all ='+all);
http.onreadystatechange = autosuggestReply;
http.send(null);
}

function autosuggestmuni(idtag,idres,file,nomeid,all, municipio) {
gElem = idres;
idElem = nomeid;
q = document.getElementById(idtag).value;
q = escape(q);
// Set te random number to add to URL request
nocache = Math.random();
http.open('get', 'javascript/'+file+'?idtag='+idtag+'&idres='+idres+'&nomeid='+nomeid+'&q='+q+'&nocache = '+nocache+'&all ='+all+'&municipioid='+municipio);
http.onreadystatechange = autosuggestReply;
http.send(null);
}

function autosuggestwithunit(idtag,idres,file,nomeid,all,tagunitid) {
gElem = idres;
idElem = nomeid;
q = document.getElementById(idtag).value;
q = escape(q);
// Set te random number to add to URL request
nocache = Math.random();
http.open('get', 'javascript/'+file+'?idtag='+idtag+'&idres='+idres+'&nomeid='+nomeid+'&q='+q+'&nocache = '+nocache+'&all ='+all+'&tagunitid='+tagunitid);
http.onreadystatechange = autosuggestReply;
http.send(null);
}

function autosuggestReply() {
if(http.readyState == 4){
	var response = http.responseText;
	e = document.getElementById(gElem);
	//val = document.getElementById('res');
	if(response!=""){
		var rres = unescape(response);
		e.innerHTML=rres;
		e.style.display="block";
	} else {
		e.style.display="none";
	}
}
}

function substituiid(valor,idtag) {
	val = document.getElementById(idtag);
	val.value=valor;
}


function substitui(valor,idtag,idres,id,nomeid) {
	val = document.getElementById(idtag);
	val.value=valor;
	vv = document.getElementById(idElem);
	vv.value=id;
	e = document.getElementById(idres);
	e.style.display="none";
}

function substituiwithunit(valor,idtag,idres,id,nomeid,tagunitid,tagunitval) {
	val = document.getElementById(idtag);
	val.value=valor;
	valun = document.getElementById(tagunitid);
	valun.value=tagunitval;
	vv = document.getElementById(idElem);
	vv.value=id;
	e = document.getElementById(idres);
	e.style.display="none";
}

