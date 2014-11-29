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
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Criando amostras para plantas!';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

//echopre($gget);
//$traitcols = unserialize($_SESSION['traitcolumnsids']);
//echopre($traitcols);

if (!isset($final)) {
echo "
<br />
<table class='myformtable' cellpadding='7' align='center'  cellpadding='7'>
<thead>
  <tr><td colspan='2'>Criando amostras para plantas marcadas!</td></tr>
</thead>
<tbody>
  <form name='coletaform' action='batchenter-plantas-criaamostras.php' method='post'>
  <input type='hidden' name='ispopup' value='$ispopup' >
  <input type='hidden' name='tbname' value='$tbname' >
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
  echo "
  <tr bgcolor = '".$bgcolor."'>
    <td colspan='2'>Esta função irá criar uma AMOSTRA/ESPECIMENE apenas para as PLANTAS MARCADAS como <b>EXISTE</b>, para as quais já foi informado uma data de coleta e a fertilidade do material e que ainda NÃO tem uma amostra (ESPECIMENE) definida!</td>
  </tr>";
  if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
  echo "
  <tr bgcolor = '".$bgcolor."'>
    <td class='tdsmallbold'>Selecione a coletor</td>
    <td>
        <select name='coletorid'>";
			echo "
          <option value='' class='optselectdowlight'>".GetLangVar('nameselect')."</option>";
			$rrr = getpessoa('',$abb=TRUE,$conn);
			while ($row = mysql_fetch_assoc($rrr)) {
				echo "
          <option value=".$row['PessoaID'].">".$row['Abreviacao']." [".$row['Prenome']."]</option>";
			}
			echo "
        </select>
    </td>
  </tr>";
  if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
  echo "
  <tr bgcolor = '".$bgcolor."'>
    <td class='tdsmallbold'>Selecione uma opção para numeração</td>
    <td class='tdformnotes'>
      <table>
        <tr><td><input type='radio' name='numbertype'  value='1'>&nbsp;Continua a numeração do coletor selecionado</td></tr>
        <tr><td><input type='radio' name='numbertype'  value='2'>&nbsp;Usa o número da árvore como número de coleta  + um sufixo numerico incremental</td></tr>
      </table>
    </td>
  </tr>";  
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='2'>
    <table align='center' >
      <tr>
        <input type='hidden' name='final' value='' />
        <td align='center' ><input style='cursor: pointer'  type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' onclick=\"javascript:document.coletaform.final.value=1\" /> </td>
      </tr>
    </table>
  </td>
</tr>
</form>
</tbody>
</table>
";
} elseif ($final==1) {
	$erro=0;
	//CHECAR CAMPOS OBRIGATÓRIOS
	if (($coletorid+0)>0 && $numbertype>0) {
	
	} else {
		$erro++;
		echo "
<br />
<table cellpadding=\"7\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>Opções obrigatórias faltando!</td></tr>
</table>
<br />
";
	}
	
	if ($numbertype==1) {
		$qn = "SELECT Number FROM Especimenes WHERE ColetorID='".$coletorid."' ORDER BY (Number+0) DESC LIMIT 0,1";
		$rn = mysql_query($qn,$conn);
		$nrz = mysql_numrows($rn);
		if ($nrz==0) {
			$erro++;
									echo "
<br />
<table cellpadding=\"7\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>Não existem coletas para o coletor indicado e portanto, não há essa opção de numeração</td></tr>
</table>
<br />
";
		} else {
			$rw = mysql_fetch_assoc($rn);
			$numinicial = $rw['Number']+0;
			if ($numinicial>0) {
				$numinicial = $numinicial+1;
			} else {
				$erro++;
							echo "
<br />
<table cellpadding=\"7\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>A numeração do coletor não é numérica, não tenho como adicionar incrementos ao numero usado automaticamente!</td></tr>
</table>
<br />
";
			}
		}
	} 
	
	
	if ($erro==0) {
	//CADASTRAR AMOSTRAS PARA AS PLANTAS SELECIONADAS
	$qz = "SELECT pl.PlantaTag, pl.PlantaID,pl.DetID,pl.GazetteerID,pl.GPSPointID,pl.HabitatID,pl.ProjetoID,pl.Longitude,pl.Latitude,pl.Altitude,pl.FiltrosIDS,pl.GruposSppIDs,tb.TEMP_DATA_COLETA ,tb.TEMP_FERT,tb.TEMP_NDUPS FROM ".$tbname."  as tb JOIN Plantas  as pl USING(PlantaID) WHERE (tb.ESPECS=0 OR tb.ESPECS IS NULL) AND tb.EXISTE=1 AND tb.TEMP_DATA_COLETA IS NOT NULL AND tb.TEMP_FERT IS NOT NULL AND tb.TEMP_FERT<>''  AND tb.TEMP_NDUPS>0";
		$rz = mysql_query($qz,$conn);
		echo $qz."<br >";
		$nrz = mysql_numrows($rz);
		if ($nrz>0) {
			while ($rww = mysql_fetch_assoc($rz)) {
					$rww['ColetorID']  = $coletorid;
					$dataarr = explode("-",$rww['TEMP_DATA_COLETA']);
					$rww['Ano'] = $dataarr[0];
					$rww['Mes'] = $dataarr[1];
					$rww['Day'] = $dataarr[2];
					unset($rww['TEMP_DATA_COLETA']);
					$fert = $rww['TEMP_FERT'];
					unset($rww['TEMP_FERT']);
					$pltag = $rww['PlantaTag'];
					unset($rww['PlantaTag']);
					$numdups = $rww['TEMP_NDUPS'];
					unset($rww['TEMP_NDUPS']);
					
					
					//CHECA A NUMERACAO DO COLETOR
					if ($numbertype==1) {
							$rww['Number'] = $numinicial;
							$numinicial++;
					} else {
							$step = 999;
							$idx=1;
							$thenum = '';
							while ($step>0) {
								$nrun = $pltag.'-'.$idx;
								$qn = "SELECT Number FROM Especimenes WHERE ColetorID='".$coletorid."' AND Number='".$nrun."'";
								$rn = mysql_query($qn,$conn);
								$nrz = mysql_numrows($rn);
								if ($nrz==0) {
									$thenum = $nrun;
									$step=0;
								}
								$idx++;
							}
							$rww['Number'] = $thenum;
					}
					//echopre($rww);
					//echo $fert;
					///FAZ O CADASTRO DA AMOSTRA
					$newspec = InsertIntoTable($rww,'EspecimenID','Especimenes',$conn);
					if (!$newspec) {
						$erro++;
						break;
						echo "Amostra NÃO registrada para a planta ".$pltag." <br />";
					} else {
						//FAZ O UPDATE DA VARIAVEL FERTILIDADE
						if ($traitfertid>0) {
						 $nd = updatetraits_fromgrid($traitfertid,"'".$fert."'",$newspec,0,'', $conn);
						 }
						 if ($duplicatesTraitID>0) {
						//FAZ O UPDATE DO TRAIT NDUPS
						 $nd = updatetraits_fromgrid($duplicatesTraitID,$numdups,$newspec,0,'', $conn);
						 }
						///FAZ O UPDATE DA TABELA $tbname
						$qup = "UPDATE ".$tbname."  SET ESPECS=(ESPECS+1) WHERE PlantaID='".$rww['PlantaID']."'";
						mysql_query($qup,$conn);
						
						echo "Amostra registrada para a planta ".$pltag." <br />";
					}
			}
		} else {
			$erro++;
												echo "
<br />
<table cellpadding=\"7\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>Não há registros marcados para cadastrar!</td></tr>
</table>
<br />
";
		}
	} 
if ($erro>0) {
	echo "
<br />
<table class='myformtable' cellpadding='7' align='center'  cellpadding='7'>
<thead>
  <tr><td colspan='2'>Criando amostras para plantas marcadas!</td></tr>
</thead>
<tbody>
  <form name='coletaform' action='batchenter-plantas-criaamostras.php' method='post'>
  <input type='hidden' name='ispopup' value='$ispopup' >
  <input type='hidden' name='tbname' value='$tbname' >
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='2'>
    <table align='center' >
      <tr>
        <td align='center' ><input style='cursor: pointer'  type='submit' value='Voltar' class='bsubmit' /> </td>
      </tr>
    </table>
  </td>
</tr>
</form>
</tbody>
</table>
";
	}
}


$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=TRUE,$footer=$menu);
?>