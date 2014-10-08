function getvalueandmerge(testeid,oval,thisid) {
var curval = document.getElementById(testeid).value;
var ische = document.getElementById(thisid).checked;
if (ische) {
if (curval) {
  var res = curval.concat('|', oval);
} else {
  var res = oval;
}  
} else {
   var vvv = curval.split("|");
   var myarr = new Array();
   var n = 0;
   for(var i = 0; i < vvv.length; i++) {
      var cv = vvv[i];
      if (cv) {
        if (cv!=oval) {
          myarr[n] = cv;
          n++;
       }
     }
  }
  if (myarr.length==1) {
     var res = myarr[0];
  } else {
     var res = myarr.join('|');
  }
} 
document.getElementById(testeid).value=res;
}


function getvaluefrommain(gettagid,placeid) {
  placevalue = document.getElementById(placeid);
  targetidval = window.opener.document.getElementById(gettagid);
  placevalue.value = targetidval.value;
}

function passdupspecs(delspec_tagid,allspecids) {
	var delspecids = document.getElementById(delspec_tagid).value;
    small_window('especimenes_duplicados_vinculos.php?theids='+allspecids+'&todelspecids='+delspecids,650,300,'Unifica variáveis de amostras duplicadas'); 
}
