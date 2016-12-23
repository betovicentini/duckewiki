<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
include "functions/DescricaoModelo.php";

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

$erro=0;

 


//CABECALHO
$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<style>
.tools {
	top: 1px;
	width: 99%;
	/*height: 220px;*/
	padding: 5px;
	border:#cccccc 1px solid;
	border-radius:5px; -webkit-border-radius:5px; -moz-border-radius:5px;
}
#modelo {
	border:#cccccc 1px solid;
	font-size: 1.2em;
	border-radiu]/]g\s:5px; -webkit-border-radius:5px; -moz-border-radius:5px;
	position: relative;
	height: 300px;
	width: 99%;
	overflow: scroll;
	padding: 10px;
	line-height: 120%;
}
.subs {
	padding: 3px;
	align: left;
	font: 0.9em ;
	font-weight: bold;
	color: blue;
}
.variaveis {
	padding: 3px;
	margin: 3px;
	float: left;
	width: 60%;
}
</style>"
);
$pgfilename = 'temp_descricao_'.$_SESSION['userid']."_pg.txt";
$which_java = array(
"<script>
    function CheckProgress() {
    	var pgxhttp = new XMLHttpRequest();
		pgxhttp.onreadystatechange = function() {
			if (pgxhttp.readyState == 4 && pgxhttp.status == 200) {
				var progress = pgxhttp.responseText;
				progress = parseInt(progress, 10);
				if (progress<100) {
					document.getElementById(\"modelo\").innerHTML = 'Processando ' + progress + '%';	         	         
	         	setTimeout(function(){ CheckProgress(); }, 1000);
	      	}
			}
		};
		var url = 'import-data-progress.php?filename=".$pgfilename."';
		pgxhttp.open(\"GET\", url, true);    			
		pgxhttp.send();        
	}	
	function fazdescricao() {
		//CheckProgress();
		makedescription();			
	}
  function makedescription() {
		var tax = document.getElementById('taxon').getElementsByTagName('select');
		var lx = document.getElementById('lang').selectedIndex;
		var lang = document.getElementById('lang')[lx].value;
		var falta = document.getElementById('falta').checked;
		if (falta) {
			faltaval = 1;	
		} else { faltaval =0;}
		var valor=0;
		var frmid=0;
		for(v=0;v<tax.length;v++) {
		var x = tax[v].selectedIndex;
		if (x>0) {
			var sel = tax[v].name;
			var opt = tax[v].getElementsByTagName('option');
			var valor = opt[x].value;
			var taxn = opt[x].innerHTML;
   	}
	}
 	var frm = document.getElementById('frm');
 	x = frm.selectedIndex;
 	if (x>0) {
		var frmid = frm.getElementsByTagName('option')[x].value;			
 	}
 	if(frmid>0 & valor>0) {
		document.getElementById('taxn').innerHTML = taxn; 
		document.getElementById(\"modelo\").innerHTML = 'Aguarde...a descrição aparecerá aqui!';
	 	//alert(sel+' '+x+'  valor:'+valor+'  formid='+frmid);
	 	var xhttp = new XMLHttpRequest();
		var url = 'descricao-make.php?pgfilename=".$pgfilename."&falta='+faltaval+'&lang='+lang+'&formid='+frmid+'&oprojetoid=".$projetoid."&taxondetcol='+sel+'&taxonid='+valor;
		//alert(url);
		xhttp.onreadystatechange = function() {
			if (xhttp.readyState == 4 && xhttp.status == 200) {
				document.getElementById(\"modelo\").innerHTML = xhttp.responseText;
				//alert(xhttp.responseText);
			}
		};
		xhttp.ontimeout = function () {
  			alert('houve um erro de time out');
		};
		xhttp.open(\"GET\", url, true);		
		xhttp.send(); 
		CheckProgress();	
 	} else {
		alert('Precisa selecionar 1 taxon e 1 formulário');
 	}
}
</script>"

);
$title = 'Visualizar descrição';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

echo "<span style='font-size: 2em; background-color: yellow;'>EM TESTE NAO USE</span>";
//GERA A TABELA SE ELA NAO EXISTE E PEGA DADOS DA MONOGRAFIA
if ($projetoid>0) {
	$sqlw = "SELECT DISTINCT Familia,Genero,Especie,InfraEspecie,idd.FamiliaID,idd.GeneroID,idd.EspecieID,idd.InfraEspecieID FROM ProjetosEspecs as prj JOIN Especimenes as spec ON spec.EspecimenID=prj.EspecimenID JOIN Identidade as idd ON idd.DetID=spec.DetID LEFT JOIN Tax_Familias as fam ON fam.FamiliaID=idd.FamiliaID LEFT JOIN Tax_Generos as gen ON gen.GeneroID=idd.GeneroID LEFT JOIN Tax_Especies as spp ON spp.EspecieID=idd.EspecieID LEFT JOIN Tax_InfraEspecies as infr ON infr.InfraEspecieID=idd.InfraEspecieID WHERE prj.ProjetoID=".$projetoid;
	$sqlw2 = "SELECT DISTINCT Familia,Genero,Especie,InfraEspecie,idd.FamiliaID,idd.GeneroID,idd.EspecieID,idd.InfraEspecieID FROM ProjetosEspecs as prj JOIN Plantas as spec ON spec.PlantaID=prj.PlantaID JOIN Identidade as idd ON idd.DetID=spec.DetID LEFT JOIN Tax_Familias as fam ON fam.FamiliaID=idd.FamiliaID LEFT JOIN Tax_Generos as gen ON gen.GeneroID=idd.GeneroID LEFT JOIN Tax_Especies as spp ON spp.EspecieID=idd.EspecieID LEFT JOIN Tax_InfraEspecies as infr ON infr.InfraEspecieID=idd.InfraEspecieID WHERE prj.ProjetoID=".$projetoid;
	
	$sqlfam = "SELECT DISTINCT tbb.Familia,IF(tbb.FamiliaID>0,tbb.FamiliaID,NULL) AS FamiliaID
	FROM (".$sqlw." UNION ".$sqlw2.") as tbb WHERE tbb.FamiliaID>0 ORDER BY tbb.Familia"; 
	//echo $sql;
	$sqlgen = "SELECT DISTINCT tbb.Familia,tbb.Genero,IF(tbb.FamiliaID>0,tbb.FamiliaID,NULL) AS FamiliaID,
	IF(tbb.GeneroID>0,tbb.GeneroID,NULL) AS GeneroID FROM (".$sqlw." UNION ".$sqlw2.") as tbb WHERE tbb.GeneroID>0  ORDER BY tbb.Familia,tbb.Genero"; 
	$sqlspp = "SELECT DISTINCT tbb.Familia,tbb.Genero,tbb.Especie,
	IF(tbb.FamiliaID>0,tbb.FamiliaID,NULL) AS FamiliaID,
	IF(tbb.GeneroID>0,tbb.GeneroID,NULL) AS GeneroID,
	IF(tbb.EspecieID>0,tbb.EspecieID,NULL) AS EspecieID FROM (".$sqlw." UNION ".$sqlw2.") as tbb WHERE tbb.EspecieID>0  ORDER BY tbb.Familia,tbb.Genero,tbb.Especie"; 
	$sqlinfr = "SELECT DISTINCT tbb.Familia,tbb.Genero,tbb.Especie,tbb.InfraEspecie,
	IF(tbb.FamiliaID>0,tbb.FamiliaID,NULL) AS FamiliaID,
	IF(tbb.GeneroID>0,tbb.GeneroID,NULL) AS GeneroID,
	IF(tbb.EspecieID>0,tbb.EspecieID,NULL) AS EspecieID,
	IF(tbb.InfraEspecieID>0,tbb.InfraEspecieID,NULL) AS InfraEspecieID FROM (".$sqlw." UNION ".$sqlw2.") as tbb WHERE tbb.InfraEspecieID>0 ORDER BY tbb.Familia,tbb.Genero,tbb.Especie,tbb.InfraEspecie"; 
	
}
echo "
<form action='descricao-view.php'  method='post' >
<input type='hidden' name='tbname'  value='".$tbname."' > 
<input type='hidden' name='monografiaid'  value='".$monografiaid."' >
<input type='hidden' name='oldtaxanome'  value='".$taxanome."' >
<input type='hidden' name='prepared'  value='1' > 
<div id='taxon' >
<select style=\"color:#000000; font-size: 1em; font-weight:bold; padding: 4px; cursor:pointer;\" name='FamiliaID'  >";
	echo "
<option   value=''>Descrição da familia</option>";
	//$sql = "SELECT DISTINCT taxanome FROM ".$tbname;
	$rsql = mysql_query($sqlfam,$conn);
	$resultado = '';
	while($rsqlw = mysql_fetch_assoc($rsql)) {
		echo "
<option value='".$rsqlw['FamiliaID']."'>".$rsqlw['Familia']."</option>";
	}	
echo "
</select>

<select style=\"color:#000000; font-size: 1em; font-weight:bold; padding: 4px; cursor:pointer;\" name='GeneroID'  >";
	echo "
<option  value=''>Descrição do gênero</option>";

	$rsql = mysql_query($sqlgen,$conn);
	$resultado = '';
	while($rsqlw = mysql_fetch_assoc($rsql)) {
		echo "
<option value='".$rsqlw['GeneroID']."'>".$rsqlw['Genero']."[".$rsqlw['Familia']."]</option>";
	}	
echo "
</select>

<select style=\"color:#000000; font-size: 1em; font-weight:bold; padding: 4px; cursor:pointer;\" name='EspecieID' >
<option   value=''>Descrição de espécie</option>";

	$rsql = mysql_query($sqlspp,$conn);
	$resultado = '';
	while($rsqlw = mysql_fetch_assoc($rsql)) {
		echo "
<option value='".$rsqlw['EspecieID']."'>".$rsqlw['Genero']." ".$rsqlw['Especie']." [".$rsqlw['Familia']."]</option>";
	}	
echo "
</select>";

	$rsql = mysql_query($sqlinfr,$conn);
	$nrsql = mysql_numrows($rsql);
	if ($nrsql>0) {
		echo "
<select style=\"color:#000000; font-size: 1em; font-weight:bold; padding: 4px; cursor:pointer;\" name='InfraEspecieID' >
  <option   value=''>Descrição de infraespécie</option>";
	$resultado = '';
	while($rsqlw = mysql_fetch_assoc($rsql)) {
		echo "
  <option value='".$rsqlw['InfraEspecieID']."'>".$rsqlw['Genero']." ".$rsqlw['Especie']." ".$rsqlw['InfraEspecie']."[".$rsqlw['Familia']."]</option>";
	}	
echo "
</select>";
	}
	echo "
</div>
<br />
<div class=\"tools\" >
<span class='subs'>Modelo da descrição</span>
<br />
<select id='frm'  name='formid' >
  <option  value=''>Formulário para descrição</option>";
	$qq = "SELECT * FROM Formularios ORDER BY Formularios.FormName ASC";
	$rr = mysql_query($qq,$conn);
	if (isset($projformidmorfo)) { $projformidmorfo=explode("_",$projformidmorfo);}
	while ($row= mysql_fetch_assoc($rr)) {
		$tem = false;	
	   if (count($projformidmorfo)>0) {
			$tem = in_array($row['FormID'],$projformidmorfo);
		}
		//if ($tem || ($row["AddedBy"]==$uuid && $row["Shared"]==0)) {
			echo "
      <option  value='".$row['FormID']."'>".$row['FormName']." (".$row['AddedDate'].")</option>";
   	//}	
	}
echo "
</select>
<select id='lang'  name='lang' >
  <option  value=''>Idioma</option>
	  <option selected value='BR'>PT-BR</option>
	  <option  value='US'>US-ENG</option>
</select>
<input type='checkbox' id='falta' /><span>Mostrar ausentes?</span>&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
	$help = "Incluir a palavra FALTA+TraitName para os valores faltantes";
	echo "onclick=\"javascript:alert('$help');\" />
</div>
<br />
<div style='position: relative; width: 99%;' >
<input type='button' value='Gerar Descrição' onclick='javascript:  fazdescricao();'  style=\"color:#4E889C; font-size: 1em; font-weight:bold; padding: 4px; cursor:pointer;\" />
&nbsp;&nbsp;<input type='button' value='Fechar Janela' onclick='javascript: window.close();'  style=\"color:#4E889C; font-size: 1em; font-weight:bold; padding: 4px; cursor:pointer;\" />
</div>
<br />
<div style='position: relative; width: 99%;' >
<span id='taxn' class='subs'></span>
<br />
<div id=\"modelo\"></div>
</div>
</div>
</form>
";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>
