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

//echopre($gget);
//echopre($ppost);
//CABECALHO
$ispopup=1;
$menu = FALSE;

$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Baixar dados NIR selecionados!';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
//$uuuuserid = $_SESSION['userid'];
if (!isset($export_filename)) {
	$export_filename = "temp_nir_export".substr(session_id(),0,10).".csv";
}
if (file_exists("temp/".$export_filename)) {
if (!empty($taxnome)) {
	$txt = "de ".$taxnome;
}
echo "
<br />
<table class='myformtable' cellpadding='7' align='left' width='50%'>
<thead>
<tr><td colspan=2>Dados NIR ".$txt."</td></tr>
</thead>
<tbody>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
$adata = date ("F d Y H:i:s.", filemtime("temp/".$export_filename));
$adata = "[Arquivo gerado em: ".$adata."]";
	echo "
<tr bgcolor = '".$bgcolor."'>
    <td colspan=2><a href=\"download.php?file=temp/".$export_filename."\">Baixar dados NIR </a>".$adata."</td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td  colspan=2 class='tdformnotes'>*Os arquivos são separados por TABULAÇÃO, tem quebras de linha em formato Unix, e o encoding de caracteres é UTF-8. O planilha do openoffice é melhor que o Excel para abrir o arquivo porque reconhece automaticamente o encoding dos caracteres (UTF-8). No Excel pode ter erros de grafia.</td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center' ><input class='bsubmit' type='button' style='cursor: pointer'  value='Fechar'  onclick='javascript: window.close();'></td>
";
if (isset($antaris)) {
echo "
  <td>";
if ($antaris==1 || !isset($antaris)) {
	$fnscript = "export-nir-data-script_antaris.php";
} else {
	$fnscript = "export-nir-data-script.php";
}  
$fnscript = "export-nir-data-form.php";
echo "  
  <form action='".$fnscript."' method='get'>
    <input type='hidden' name='updatefile' value=1 >";
foreach ($ppost as $kk => $vv) {
echo "
  <input type='hidden' name='".$kk."' value='".$vv."' >";
}
echo "
<input type='submit' value='Gerar novamente o arquivo' >
</form>
</td>";
}
echo "
</tr>
</tbody>
</table>";
} 
else {
echo "
<br />
<table class='myformtable' cellpadding='7' align='center' width='50%'>
<thead>
<tr><td >Dados NIR exportados</td></tr>
</thead>
<tbody>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
    <td>Não há dados NIR para o filtro selecionado!</td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center' ><input class='bsubmit' type='button' style='cursor: pointer'  value='Fechar'  onclick='javascript: window.close()'></td>
</tr>";
echo "
</tbody>
</table>";
}

$which_java = array();
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>