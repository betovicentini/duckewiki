<?php
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
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");

$pgfilename = 'temp_importplantas_'.substr(session_id(),0,10)."_pg.txt";
$fh = fopen("temp/".$pgfilename, 'w');
fwrite($fh,"0");
fclose($fh);
session_write_close();

$which_java = array(
"<script>
    function CheckProgress() {
    	var pgxhttp = new XMLHttpRequest();
		pgxhttp.onreadystatechange = function() {
			if (pgxhttp.readyState == 4 && pgxhttp.status == 200) {
				var progress = pgxhttp.responseText;
				progress = parseInt(progress, 10);
				document.getElementById(\"probar\").value = progress;
				document.getElementById('probarperc').innerHTML = 'Processando ' + progress + '%';
				if (progress<100) {
		         	CheckProgress();
		      	} else {
					document.getElementById('probarperc').innerHTML = 'Concluido ' + progress + '%';
					document.getElementById('fechabt').display = 'visible';
					document.getElementById('imptbt').display = 'hidden';
	      		}
			}
		};
		var url = 'import-data-progress.php?filename=".$pgfilename."';
		pgxhttp.open(\"GET\", url, true);
		pgxhttp.send();        
	}
	function fazimportacao() {
		CheckProgress();
		importaasplantas();
	}       
  function importaasplantas() {
  		var lx = document.getElementById('filtroid').selectedIndex;
		var ofiltroid = document.getElementById('filtroid')[lx].value;
 		if(ofiltroid>0) {
	 		var xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function() {
				if (xhttp.readyState == 4 && xhttp.status == 200) {
					document.getElementById(\"resultado\").innerHTML = xhttp.responseText;
				}
			};
			var url = 'projeto-fromfiltro-script.php?pgfilename=".$pgfilename."&saoplantas=".$saoplantas."&projetoid=".$projetoid."&filtroid='+ofiltroid;
			xhttp.open(\"GET\", url, true);
			xhttp.send(); 
	 	} else {
			alert('Precisa selecionar um filtro');
 		}
	}
</script>"

);
$title = 'Importar de filtro para projeto';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

echo "
<br />
<table class='myformtable' align='left' cellpadding='7'>
<thead>
<tr>
  <td >Selecione o filtro</td>
</tr>
</thead>
<tbody>";
//<form method='post' name='finalform' action='projeto-fromfiltro.php'>";
foreach($gget as $kk => $vv) {
	echo "
<input type='hidden'  value='".$vv."' name='".$kk."'  >";
}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('namefiltro')."</td>
        <td>
          <select id='filtroid'  name='filtroid' >
            <option value=''>Selecione um filtro</option>";
			$qq = "SELECT * FROM Filtros WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ORDER BY FiltroName";
			$res = @mysql_query($qq,$conn);
			while ($rr = @mysql_fetch_assoc($res)) {
				echo "
            <option value='".$rr['FiltroID']."'>".$rr['FiltroName']." [".$rr['AddedDate'].".]</option>";
			}
			mysql_free_result($res);
echo "
          </select>
        </td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td  align='center'>
    <progress id='probar' value='0' max='100'></progress>
    <span  id='probarperc' ></span>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td  align='center'>
        <span  id='resultado'></span>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td  align='center'>
    <input id='fechabt' style='display: hidden; cursor: pointer' type='button' value='Fechar' onclick='javascript: window.close();' />
    <input id='imptbt' style='display: visible; cursor: pointer' type='button' value='Importar' onclick='javascript: fazimportacao();' />
  </td>
</tr>


</tbody>
</table>
";
//</form>";


$which_java = array(
//"<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>