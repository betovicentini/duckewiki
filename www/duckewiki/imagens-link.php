<?php
//este script checa images importadas ao banco de dados mas que nao foram relacionadas com nada e permite criar uma relacao, buscando relacoes que tem a mesma data
//permite ligar com uma amostra coletada, com uma planta marcada ou com um habitat
//precisa modificar o script para fazer outros tipos de relacao que nao foram ainda implementados
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

//CHECA SE O USUARIO TEM PERMISSAO
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) || 
	(trim($uuid)=='')) {
		header("location: access-denied.php");
	exit();
} else {
	$acclevel = $_SESSION['accesslevel'];
}

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
@extract($gget);

//CABECALHO
$menu = FALSE;

    

$which_css = array(
"<link rel=\"stylesheet\" type=\"text/css\" href=\"javascript/jquery.spzoom.css\"/>",
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<style>
div.tabela > div:nth-of-type(odd) {
    background: #e0e0e0;
}
</style>"
);
$which_java = array(
"<script src=\"http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js\" type=\"text/javascript\" ></script>",
"<script type=\"text/javascript\" src=\"javascript/jquery.spzoom.js\"></script>",
"<script type=\"text/javascript\">
 $(function() {
    $('[data-spzoom]').spzoom();
 });
 function fazzoom() {
 	$('[data-spzoom]').spzoom();
 }
 
</script>",
"<script>
  function mudaset(imgidx) {
  		var lx = document.getElementById('setselect').selectedIndex;
		var nameset = document.getElementById('setselect')[lx].value;
		var nametxt = document.getElementById('setselect')[lx].innerHTML;
  		//alert(nametxt);
  		var pgxhttp = new XMLHttpRequest();
		pgxhttp.onreadystatechange = function() {
			if (pgxhttp.readyState == 4 && pgxhttp.status == 200) {
				document.getElementById(\"grupoimgs\").innerHTML = pgxhttp.responseText;	
				fazzoom();
				var lxx = document.getElementById('thespecid').selectedIndex;
				var specid = document.getElementById('thespecid')[lxx];	
				var aimgid = document.getElementById('curimageid').value;
				if (aimgid>0 && specid.value>0) {
					mudasample('showex_'+aimgid,specid,aimgid);
				}	
			}
		};
		if (imgidx) {
		var lxx = document.getElementById('thespecid').selectedIndex;
		var specid = document.getElementById('thespecid')[lxx].value;
	   } else { specid=0;}
		var days = document.getElementById('daysid').value;		
		if (imgidx>=0) {
			var url = 'imagens-linksets.php?days='+days+'&especimenid='+specid+'&ids='+nameset+'&imageindex='+imgidx;
		} else {
			var url = 'imagens-linksets.php?days='+days+'&especimenid='+specid+'&ids='+nameset;	   
	   }
		pgxhttp.open(\"GET\", url, true);    			
		pgxhttp.send(); 
  }  
  function mudasample(theid,specid,theimgid) {
  		var lx = document.getElementById('traitsel').selectedIndex;
		var thetrid = document.getElementById('traitsel')[lx].value;
  		var xhttp = new XMLHttpRequest();
  		xhttp.onreadystatechange = function() {
			if (xhttp.readyState == 4 && xhttp.status == 200) {
				document.getElementById(theid).innerHTML = xhttp.responseText;
				fazzoom();         	      
	       }
		};
		var url = 'imagens-link-mudaspec.php?imgid='+theimgid+'&thetraitid='+thetrid+'&especimenid='+specid.value+'&specname='+specid.innerHTML;
		xhttp.open(\"GET\", url, true);    			
		xhttp.send(); 
  } 
  function salvarelacao(theimgid,thespecid) {
  		var lx = document.getElementById('traitsel').selectedIndex;
		var thetrid = document.getElementById('traitsel')[lx].value;
  		var xhttpp = new XMLHttpRequest();
  		xhttpp.onreadystatechange = function() {
			if (xhttpp.readyState == 4 && xhttpp.status == 200) {
				document.getElementById('linkref').innerHTML = xhttpp.responseText;
	       }
		};
		var url = 'imagens-link-relaciona.php?imgid='+theimgid+'&thetraitid='+thetrid+'&especimenid='+thespecid;
		xhttpp.open(\"GET\", url, true);    			
		xhttpp.send(); 
  }    
  </script>"
);
$title = 'Checar link de imagens';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);


$qq = "SELECT img.AddedBy, uu.LastName,img.AddedDate,count(*) as NImgs FROM `Imagens` as img JOIN Users as uu ON uu.UserID=img.AddedBy WHERE UnLinked=1 GROUP BY img.AddedBy,img.AddedDate";
$res = mysql_query($qq,$conn);
$nres = mysql_numrows($res);
if ($nres) {
	$txt = "
<strong>Importações</strong>:<br/>	
	<select id='setselect' name='imgset' onchange='javascript: mudaset();' >
<option value=''>Selecione um grupo de imagens</option>";	
	while($row = mysql_fetch_assoc($res)) {
	$txt .= "
	<option value=".$row['AddedBy']."_".$row['AddedDate']." >".$row['LastName']." ".$row['AddedDate']." [".$row['NImgs']."]</option>";

	}
	$txt .= "</select>";
	
	$txt .= "&nbsp;&nbsp;<input type='number' value=0 id='daysid' size=2 />&nbsp;<span style='font-size: 0.8em;'>dias de tolerância</span>";

echo "
<div >".$txt."</div>
<div id='grupoimgs'></div>
";

}


$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?> 