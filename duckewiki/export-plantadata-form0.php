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
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"
);
$which_java = array();
$title = 'Exportar dados de plantas marcadas';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

unset($_SESSION['metadados']);
unset($_SESSION['destvararray']);
unset($_SESSION['qq']);
unset($_SESSION['exportnresult']);

echo "
<br />
<table class='myformtable' align='center' cellpadding=\"5\">
<thead>
<tr >
<td >".GetLangVar('namexportar')." ".GetLangVar('nametaggedplant')."</td></tr>
</thead>
<tbody>
";
if (!isset($filtro)) {

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<form method='post' name='finalform' action='export-plantadata-form0.php'>
<input type='hidden' name='ispopup' value='".$ispopup."' /> 
<tr bgcolor = '".$bgcolor."'>
<td colspan='100%'>
<table>
<tr>
  <td class='tdsmallbold'>Árvores à exportar</td>
  <td>
    <select name='filtro' onchange='javascript: this.form.submit();'>";
		if (!empty($filtro)) {
			$qq = "SELECT * FROM Filtros WHERE FiltroID='".$filtroid."'";
			$res = @mysql_query($qq,$conn);
			$rr = @mysql_fetch_assoc($res);
			echo "
      <option selected value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
		}
			echo "
      <option selected value=''>".GetLangVar('nameselect')." um filtro</option>";
			$qq = "SELECT * FROM Filtros WHERE (AddedBy=".$_SESSION['userid']." OR Shared=1) ORDER BY FiltroName";
			$res = @mysql_query($qq,$conn);
			while ($rr = @mysql_fetch_assoc($res)) {
				echo "
      <option value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
			}

	echo "
    </select>
  </td>
<!---
  <td class='tdsmallbold'> ou por uma lista de placas</td>
  <td><textarea name='listadearvores'></textarea></td>
--->
</tr>
</table>
</td>
</tr>";

} 
elseif (($filtro+0)>0) {
$qz = "SELECT COUNT(*) as npl FROM Plantas AS pl WHERE pl.FiltrosIDS LIKE '%filtroid_".$filtro."%'";
$rz = mysql_query($qz,$conn);
$rwz = mysql_fetch_assoc($rz);
$narv = $rwz['npl'];
if ($narv>0) {
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdformnotes'>O filtro selecionado contém $narv árvores!</td>
</tr>
";
}
else {
echo "
<tr bgcolor = '".$bgcolor."'>
  <td style='color: red; font-size: 1.2em;'>O filtro selecionado não tem árvores! Atualizar o filtro ou selecionar outro!</td>
</tr>
";
}
if ($narv>0) {
//já tem censos cadastrados
	$qz = "SELECT DISTINCT CensoID FROM Monitoramento LEFT JOIN Plantas AS pl USING(PlantaID) WHERE pl.FiltrosIDS LIKE '%filtroid_".$filtro."%' AND CensoID>0";
	$rz = @mysql_query($qz,$conn);
	$ncensos = @mysql_numrows($rz);
	if ($ncensos>0) {
		if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
		echo "
<form method='post' name='finalform' action='export-plantadata-form.php'>
<input type='hidden' name='filtro' value='".$filtro."' /> 
<tr bgcolor = '".$bgcolor."'>
  <td>
  <table>
  <tr>
    <td class='tdsmallbold'>Dados de Monitoramento.<br />Selecione 1 ou mais censos:</td>
    <td>
      <select name='censos[]' multiple size='5'>";
			while ($rr = @mysql_fetch_assoc($rz)) {
				$qk = "SELECT * FROM Censos WHERE CensoID='".$rr['CensoID']."'";
				$rk = @mysql_query($qk,$conn);
				$rwk = @mysql_fetch_assoc($rk);
								$qz = "SELECT COUNT(DISTINCT TraitID) as trs FROM Monitoramento LEFT JOIN Plantas AS pl USING(PlantaID) WHERE pl.FiltrosIDS LIKE '%filtroid_".$filtro."%' AND CensoID='".$rr['CensoID']."'";
				$rzk = @mysql_query($qz,$conn);
				$rwzk = @mysql_fetch_assoc($rzk);
				$trs = $rwzk['trs'];
				$qz = "SELECT COUNT(DISTINCT PlantaID) as pls FROM Monitoramento LEFT JOIN Plantas AS pl USING(PlantaID) WHERE pl.FiltrosIDS LIKE '%filtroid_".$filtro."%' AND CensoID='".$rr['CensoID']."'";
				$rzk = @mysql_query($qz,$conn);
				$rwzk = @mysql_fetch_assoc($rzk);
				$pls = $rwzk['pls'];
				echo "
          <option value='".$rwk['CensoID']."'>".$rwk['CensoNome']." [".$rwk['DataInicio']." à ".$rwk['DataFim']."] - inclui ".$pls." árvores e ".$trs." variáveis</option>";
			}
	echo "
      </select>
    </td>
    <td>
      <table>
        <tr>
          <td><input type='button' class='bsubmit' value='Editar/atualizar um censo'  onclick =\"javascript:small_window('censo-edit.php?ispopup=1&filtro=".$filtro."',900,400,'Editar censo');\" /></td>
        </tr>
        <tr>
          <td><input type='button' class='bblue' value='Criar um censo'  onclick =\"javascript:small_window('censos.php?ispopup=1&filtro=".$filtro."',900,400,'Editar censo');\" /></td>
        </tr>
      </table>
    </td>
  </tr>
  </table>
  </td>
</tr>
";
	} 
	else {
		echo "
<tr bgcolor = '".$bgcolor."'>
  <td>
    <table>
      <tr>  <td class='tdsmallbold'>Não há censos cadastrados para árvores desse filtro.</td></tr>
      <tr><td><input type='button' class='bsubmit' value='Criar um censo'  onclick =\"javascript:small_window('censos.php?ispopup=1&filtro=".$filtro."',900,400,'Editar censo');\" /></td></tr>
    </table>
  </td>
</tr>
";
	}


if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<td >
<table>
  <tr>
    <td class='tdsmallbold'>".GetLangVar('nameformulario')." ".strtolower(GetLangVar('nameobs')."s");
echo "&nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Selecione aqui as variáveis ESTÁTICAS que deseja utilizar para gerar uma descrição que será armazenada numa única coluna no arquivo gerado';
	echo " onclick=\"javascript:alert('$help');\" />
  </td>
  <td class='tdformnotes'>
    <select name='formnotes' >";
		if (!empty($formid)) {
			$qq = "SELECT * FROM Formularios WHERE FormID='$formid'";
			$rr = mysql_query($qq,$conn);
			$row= mysql_fetch_assoc($rr);
			echo "
      <option selected value='".$row['FormID']."'>".$row['FormName']." (".$row['AddedDate'].")</option>";
		} else {
			echo "
      <option value=''>".GetLangVar('nameselect')."</option>";
		}
	//formularios usuario
	$qq = "SELECT * FROM Formularios WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ORDER BY FormName,Formularios.AddedDate ASC";
	$rr = mysql_query($qq,$conn);
	while ($row= mysql_fetch_assoc($rr)) {
		echo "
      <option value='".$row['FormID']."'>".$row['FormName']."</option>";
	}
	echo "
    </select>
  </td>
</tr>
</table>
</td>
</tr>";
//formulario variaveis

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<td >
<table>
<tr>
  <td class='tdsmallbold'>".GetLangVar('nameformulario')." ".strtolower(GetLangVar('namevariaveis')." estáticas");
echo "&nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Selecione o formulário que contém as variáveis ESTÁTICAS que deseja incluir como colunas na planilha produzida';
	echo " onclick=\"javascript:alert('$help');\" />
  </td>
  <td class='tdformnotes'>
    <select name='formvariables' >";
		if (!empty($formid)) {
			$qq = "SELECT * FROM Formularios WHERE FormID='$formid'";
			$rr = mysql_query($qq,$conn);
			$row= mysql_fetch_assoc($rr);
			echo "
      <option selected value='".$row['FormID']."'>".$row['FormName']." (".$row['AddedDate'].")</option>";
		} else {
			echo "
      <option value=''>".GetLangVar('nameselect')."</option>";
		}
	//formularios usuario
	$qq = "SELECT * FROM Formularios WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ORDER BY FormName,Formularios.AddedDate ASC";
	$rr = mysql_query($qq,$conn);
	while ($row= mysql_fetch_assoc($rr)) {
		echo "
      <option value='".$row['FormID']."'>".$row['FormName']."</option>";
	}
	echo "
    </select>
  </td>
  <td class='tdformnotes'><input type='checkbox' value='1' name='meanvalues' />".GetLangVar('usemeanvalues')."&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
	$help = strip_tags(GetLangVar('usemeanvalues_help'));
	echo " onclick=\"javascript:alert('$help');\" /></td>
</tr>
</table>
</td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('nameformulario')." ".strtolower(GetLangVar('namevariaveis')." ".GetLangVar('namehabitat'));
		echo "&nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
		$help = 'Selecione aqui as variáveis de HABITAT (associadas à localidade da árvore) para adicioná-las como colunas no arquivo exportado';
	echo " onclick=\"javascript:alert('$help');\" /></td>
        <td class='tdformnotes'>
          <select name='formhabitat' >";
			if (!empty($formhabitat)) {
			$qq = "SELECT * FROM Formularios WHERE FormID='$formhabitat'";
			$rr = mysql_query($qq,$conn);
			$row= mysql_fetch_assoc($rr);
			echo "
            <option selected value='".$row['FormID']."'>".$row['FormName']."</option>";
		} else {
			echo "
            <option value=''>".GetLangVar('nameselect')."</option>";
		}
		//formularios usuario
		$qq = "SELECT * FROM Formularios WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ORDER BY FormName,Formularios.AddedDate ASC";
		$rr = mysql_query($qq,$conn);
		while ($row= mysql_fetch_assoc($rr)) {
		echo "
            <option value='".$row['FormID']."'>".$row['FormName']."</option>";
	}
	echo "
          </select>
        </td>
        <td class='tdformnotes' ><input type='checkbox' value='1' name='habmean' />".GetLangVar('usemeanvalues')."&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
	$help = strip_tags(GetLangVar('usemeanvalues_help'));
	echo " onclick=\"javascript:alert('$help');\" /></td>
      </tr>
    </table>
  </td>
</tr>";

//variaveis basicas
$vararr = 
array(
//		'Amostras Coletadas',
		'Genero+especie+infraespecifico (sem autor)',
		'Genero+especie+infraespecifico (com autor)',
		'Taxonomia (campos separados)',
		'Localidade',
		'Geo-coordenadas',
		'X Y e/ou Dist e Angulo',
		'Classe de habitat',
		'Data da marcacao',
		'Projeto'); 
//'Habitat','Marcado por', 'Vernacular',

$variablesarr = array(
//		'coletas',
		'nomenoautor',
		'nomeautor',
		'taxacompleto',
		'localidade',
		'gps',
		'xylocal',
		'habitat',
		'datacol',
		'projeto'); 

//'habitat','taggedby', 'vernacular',
$nvars = count($vararr);
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td>
    <table>
      <tr>
        <td class='tdsmallbold'>Selecione variáveis planta à incluir:</td>
        <td>
          <select name='basicvariables[]' multiple size='11'>";
	$i=0;
	foreach ($vararr as $kk => $vv) {
		$value = $variablesarr[$kk];
		echo "
            <option value='".$value."'>".$vv."</option>";
		$i++;
	}
	echo "
          </select>
        </td>
      </tr>
    </table>
  </td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center'><input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' /></td>
</tr>";
  } 
}
echo "
</form>
</tbody>
</table>";
$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>