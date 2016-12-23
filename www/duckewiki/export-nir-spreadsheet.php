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
$ispopup=1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"

);
$which_java = array(
);
$title = 'Exportar planilha para leituras NIR';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

//echopre($ppost);
$erro=0;
if (isset($final)) {
	if (($filtro+0)==0 && ($processoid)+0==0 && empty($tbname)) {
		$erro++;
	}
	if (empty($sampletype)) {
		$erro++;
	}
	if (($nfolhas+0)==0 || ($nabax+0)==0 || ($nadax+0)==0) {
		$erro++;
	}
}
if ($erro>0) {
	echo "<table align='center' cellpadding='5' class='erro'><tr><td align='center'>Campos obrigatórios faltando!</td></tr></table><br>";
}

if (!isset($final) || $erro>0) {
echo "
<form method='post' name='finalform' action='export-nir-spreadsheet.php'>
<input type='hidden' name='ispopup' value='".$ispopup."' />";
if (isset($processoid)) { 
echo "
<input type='hidden' name='processoid' value='".$processoid."' /> 
<input type='hidden' name='sampletype' value='".$sampletype."' /> ";
}
if (isset($tbname)) { 
echo "
<input type='hidden' name='tbname' value='".$tbname."' /> 
<input type='hidden' name='sampletype' value='".$sampletype."' /> ";
}

echo "
<input type='hidden' name='final' value='1' /> 
<br />
<table class='myformtable' align='left' cellpadding=\"5\">
<thead>
<tr >
<td colspan='2'>Exporta planilha para coleta de dados NIR</td></tr>
</thead>
<tbody>
";
if (!isset($processoid) && !isset($tbname)) {
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>Filtro com amostras e plantas</td>
  <td>
    <select name='filtro'  >";
		if (!empty($filtro)) {
			$qq = "SELECT * FROM Filtros WHERE FiltroID='".$filtro."'";
			$res = @mysql_query($qq,$conn);
			$rr = @mysql_fetch_assoc($res);
			echo "
      <option selected value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
		}
			echo "
      <option  value=''>".GetLangVar('nameselect')." um filtro</option>";
			$qq = "SELECT * FROM Filtros WHERE (AddedBy=".$_SESSION['userid']." OR Shared=1) ORDER BY FiltroName";
			$res = @mysql_query($qq,$conn);
			while ($rr = @mysql_fetch_assoc($res)) {
				echo "
      <option value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
			}

	echo "
    </select>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
	$txt1 = '';
	$txt2 = '';
if ($sampletype=='specimens') {
	$txt1 = 'checked';
	$txt2 = '';
}
if ($sampletype=='plantas') {
	$txt1 = '';
	$txt2 = 'checked';
}
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>Incluir na planilha</td>
  <td >
  <table>
  <tr><td><input type='radio'  name='sampletype'  $txt1 value='specimens'>&nbsp;Amostra - Especímene</td></tr>
  <tr><td><input type='radio'  name='sampletype'  $txt2 value='plantas'>&nbsp;Planta marcada</td></tr> 
  </table>
  </td>
</tr>
";
}
if (!isset($nfolhas)) {
	$nfolhas=3;
	$nadax = 1;
	$nabax = 1;
}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>Número de folhas por individuo</td>
  <td ><input type='text'  name='nfolhas'  size='30'  value='$nfolhas'></td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>Número de leituras ADAXIAL por folha</td>
  <td ><input type='text'  name='nadax'  size='30' value='$nadax'></td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>Número de leituras ABAXIAL por folha</td>
  <td ><input type='text'  name='nabax'  size='30'  value='$nabax'></td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
//if ($wikiid==1) {
	$txt3 = 'checked';
//}
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>Incluir WikiID </td>
  <td ><input type='checkbox'  name='wikiid'  $txt3 value='1'></td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center' colspan='2'><input style='cursor:pointer;' type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' /></td>
</tr>";
echo "
</form>
</tbody>
</table>";
} else {
	//prepara a query
	if (empty($tbname)) {
		if ($sampletype=='specimens' || $processoid>0) {
			if (($processoid+0)==0) {
				$qq = "SELECT CONCAT('Spec','-',Especimenes.EspecimenID) as WikiID, CONCAT(pess.Sobrenome,'-',Especimenes.Number) as TAG FROM Especimenes JOIN Pessoas as pess ON pess.PessoaID=Especimenes.ColetorID" ;
				$qq .= "  JOIN FiltrosSpecs as fl ON Especimenes.EspecimenID=fl.EspecimenID WHERE fl.FiltroID=".$filtro;
			} else {
				$qq = "SELECT CONCAT('Spec','-',prcc.EspecimenID) as WikiID, CONCAT(pess.Sobrenome,'-',Especimenes.Number) as TAG FROM ProcessosLIST as prcc JOIN Especimenes ON prcc.EspecimenID=Especimenes.EspecimenID JOIN Pessoas as pess ON pess.PessoaID=Especimenes.ColetorID WHERE prcc.EXISTE=1 AND prcc.ProcessoID=".$processoid;
			}
			$qo = " ORDER BY pess.Sobrenome,Especimenes.Number";
			$qq .= $qo;
		} else {
			$qq = "SELECT CONCAT('Planta','-',pl.PlantaID) as WikiID, PlantaTag as TAG, localidadefields(pl.GazetteerID, pl.GPSPointID,0,0,0, 'GAZ_PAR2') as LOCAL FROM Plantas as pl ";
			$qo = "ORDER BY PlantaTag";
			$qq .= "  JOIN FiltrosSpecs as fl ON pl.PlantaID=fl.PlantaID WHERE fl.FiltroID=".$filtro." ".$qo;
		}
	} else {
		$qq = "SELECT CONCAT('Planta','-',pl.PlantaID) as WikiID, pl.PlantaTag as TAG, localidadefields(pl.GazetteerID, pl.GPSPointID,0,0,0, 'GAZ_PAR2') as LOCAL
 FROM `".$tbname."` as tb JOIN Plantas as pl USING(PlantaID)  WHERE tb.EXISTE=1 ORDER BY localidadefields(pl.GazetteerID, pl.GPSPointID,0,0,0, 'GAZ_PAR2'),pl.PlantaTag";
	}
	//echo $qq."<br>";
	$res = mysql_query($qq,$conn);
	$nrecs = mysql_numrows($res);
	$export_filename = "nir_datasheet_".$_SESSION['userlastname']."_".$_SESSION['sessiondate'].".csv";
	$fh = fopen("temp/".$export_filename, 'w') or die("nao foi possivel gerar o arquivo");
	$idd = 1;
	while ($row = mysql_fetch_assoc($res)) {
			$txtbase = '';
			if ($sampletype=='plantas') {
					$local = str_replace("Parcela","",$row['LOCAL']);
					$local = trim($local);
					$thetag = "TAG-".$row['TAG'];
					//."-".$local;
			} else {
					$thetag = $row['TAG'];
			}
			if ($wikiid>0 || $sampletype=='plantas') {
				$txtbase .=  $row['WikiID']."_";
			}
			$txtbase .=  $thetag;
			$txt = array();
			for ($f=1;$f<=$nfolhas;$f++) {
				$fol = 'folha-'.$f;
				$txt[] = $txtbase."_".$fol;
				//echo $f."<br />";
			}
			$resfin = array();
			foreach ($txt as $vv) {
				for ($ab=1;$ab<=$nabax;$ab++) {
						$txtr = $vv."_abaxial_".$ab;
						$resfin[] = $txtr;
				}
				for ($ad=1;$ad<=$nadax;$ad++) {
						$txtr = $vv."_adaxial_".$ad;
						$resfin[] = $txtr;
				}
			}
			foreach ($resfin as $vvt) {
				$top = $idd."\t".$vvt."\n";
				fwrite($fh, $top);
				$idd++;
			}
	}
	fclose($fh);

	if ($nrecs==0) {
		echo "<table align='center' cellpadding='5' class='erro'><tr><td align='center'>Nenhum registro encontrado!</td></tr></table><br>";
	} 
	else {
echo "
<br />
<table class='myformtable' cellpadding='5' align='center' width=70%>
<thead>
<tr><td colspan='2'>Resultados</td></tr>
</thead>
<tbody>";
echo "
<tr>
  <td><b>$nrecs</b> registros  foram preparados</td>
    <td><a href=\"download.php?file=temp/".$export_filename."\">Baixar planilha!</a>
</tr>
<tr>
  <td colspan='2'><hr></td>
</tr>
<tr>
  <td colspan='2' class='tdformnotes'>*Os arquivos são separados por TABULAÇÃO, tem quebras de linha em formato Unix, e o encoding de caracteres é UTF-8. O planilha do openoffice é melhor que o Excel para abrir o arquivo porque reconhece automaticamente o encoding dos caracteres (UTF-8). No Excel pode ter erros de grafia.</td>
</tr>
<tr>
  <td colspan='2' align='center'><input style='cursor:pointer;' type='button' value='Fechar' onclick='javascript: window.close();' class='bsubmit'></td>
</tr>
</tbody>
</table>
";
	}

}



$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>