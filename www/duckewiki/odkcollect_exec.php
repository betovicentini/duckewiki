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

//echopre($ppost);
//CABECALHO
$menu = FALSE;

$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"
);
$which_java = array(
);
$title = 'Opções de Formulario ODK';
$body = '';
//echopre($ppost);

function comparaodk($tbid,$fname,$defarr, $conn) {
	$qz = "SELECT * FROM ODKforms WHERE ODKformid=".$tbid;
	$res = mysql_query($qz,$conn);
	$row = mysql_fetch_assoc($res);
	$changes = 0;	
	if ($row["FormName"]!=$fname) {
		$changes++;
	}
	$defold = unserialize($row["Definitions"]);
	foreach($defold as $kk => $vv) {
		if ($vv!=$defarr[$kk]) {
			$changes++;
		}
	}
	if ($changes>0) { return(1); } else { return(0);}
}

//Create table if not exists
$qq = "CREATE TABLE IF NOT EXISTS ODKforms (
ODKformid INT(10) unsigned NOT NULL auto_increment,
FormName VARCHAR(50),
Definitions TEXT,
AddedBy INT(10),
AddedDate DATE,
PRIMARY KEY (odkformid)) CHARACTER SET utf8";
@mysql_query($qq,$conn);

///SALVANDO 



if ($saving==1) {
	//salvando definicoes de formulario
	//campos obrigatorios:
	$defs = $ppost;
	unset($defs['odkformid']);
	unset($defs['saving']);
	//definicoes
	$definicoes = serialize($defs);
	if (!empty($formname) && (0+$coordenadas_cell+$$coordenadas_gps)>0) {
		$fieldsaskeyofvaluearray = array(
		'FormName' => $formname,
		'Definitions' => $definicoes);
		if ($odkformid>0 && $odkformid!="criar") { //then update
			$upp = comparaodk($odkformid,$formname,$defs, $conn);
			if ($upp>0) {
				CreateorUpdateTableofChanges($odkformid,'ODKformid','ODKforms',$conn);
				$finalizado = UpdateTable($odkformid,$fieldsaskeyofvaluearray,'ODKformid','ODKforms',$conn);
			} else { 
				$aviso = "Nada foi modificado";
				$finalizado = 0;
			}     
        	} else {
			if ($odkformid=="criar") {
				$finalizado = InsertIntoTable($fieldsaskeyofvaluearray,'ODKformid','ODKforms',$conn);
			}
		}
		if (isset($finalizado)) {
			if ($finalizado>0) { 
				if ($odkformid>0) { 
					$aviso="Registro atualizado"; 
				} else { 
					$aviso="Registro Criado";
					$odkformid = $finalizado;
				}
			}
		} else { $aviso = "Houve um erro";}
	} else {
		$aviso = "Cammpos obrigatórios faltando";
	}
	echo "<br /><span style='color: red; font-size: 1.1em; font-weight: bold;' >".$aviso."</span></br>";
}

$versao = $_SESSION["sessiondate"];

///

if (($odkformid+0)>0 && !isset($saving)) {
	$qq = "SELECT * FROM ODKforms WHERE ODKformid='".$odkformid."'";
	$res = mysql_query($qq);
	$rr = mysql_fetch_assoc($res);
	//echopre($rr);
	$definitions= unserialize($rr['Definitions']);
	$odkformid = $rr['ODKformid'];
	$versao = $rr['AddedDate'];
	@extract($definitions);
	//echopre($definitions);
} 

FazHeader($title,$body,$which_css,$which_java,$menu);

echo "
<br />
<form method='post' name='odkformname' action='odkcollect_exec.php'>
  <input type='hidden' name='odkformid' value='".$odkformid."'>
<table class='myformtable' align='left' cellpadding='7'>
<thead><tr ><td colspan='2'>ODK Collect Form - Definições</td></tr></thead>
<tbody>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<td>
  <table>
    <tr>
      <td class='tdsmallbold'>".GetLangVar('nametitle')."*&nbsp;<img height='15' src=\"icons/icon_question.gif\" "; 
      $help = "Nome do formulário que irá aparecer quando executar o aplicativo no seu celular"; echo " onclick=\"javascript:alert('$help');\" /></td>
      <td><input type=\"text\" style=\"font-size: 1em; color: red;\" cols=55 rows=2 name=\"formname\" value=\"".$formname."\"></td>
      <td class='tdformnotes'>Versão ".$versao."</td>
    </tr>
  </table>
</td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td class='tdsmallbold'>Coletores&nbsp;<img height='15' src=\"icons/icon_question.gif\" ";
$help = "Selecione todos os coletores que estarão em campo durante o uso desta versão do formulário";
echo " onclick=\"javascript:alert('$help');\" /></td>
        <td class='tdformnotes' >
        <input type='hidden' id='addcolvalue'  name='addcolvalue' value='".$addcolvalue."' />
        <textarea name='addcoltxt' id='addcoltxt'  rows=2 readonly>".$addcoltxt."</textarea></td>
        <td><input type=button style=\"color:#4E889C; font-size: 0.8em; padding: 4px; cursor:pointer;\"  value='Coletores'  onmouseover=\"Tip('Adiciona ou Edita os Autores do Trabalho');\" ";
		$myurl ="addcollpopupNOVO.php?valuevar=addcolvalue&valuetxt=addcoltxt&formname=odkformname"; 
		echo " onclick = \"javascript:small_window('".$myurl."',900,500,'Coletores');\" /></td>
      </tr>
    </table>
  </td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td class='tdsmallbold'>Tipo de coordenadas*&nbsp;
<img height='15' src=\"icons/icon_question.gif\" ";
$help = "Selecione ambas ou apenas uma das opções para incluir no seu formulário: 1. Coordenadas do celular ou tablet usa o sensor desse equipamento para pegar coordenadas;2. Coordenadas de um GPS externo, neste caso o formulário pedirá que você inique o número do ponto do seu GPS e pode indicar diferentes aparelhos de GPS em equipamentos.";
echo " onclick=\"javascript:alert('".$help."');\" /></td></tr>";
if (($coordenadas_cell+0)==1 || !isset($coordenadas_cell)) {
	$cellsel = "checked";
} else {
  $cellsel = ""; 
}
echo "<tr>
        <td>
<input type='checkbox' name='coordenadas_cell'  ".$cellsel." value='1' >&nbsp;Coordenadas do celular ou tablet</td></tr>";
if (($coordenadas_gps+0)==1 || !isset($coordenadas_gps)) {
	$cellsel = "checked";
} else {
  $cellsel = ""; 
}
echo "<tr>
        <td><input type='checkbox' name='coordenadas_gps'  ".$cellsel." value='1' >&nbsp;Número de ponto de GPS externo</td>
      </tr>
    </table>
  </td>
</tr>
";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td colspan=2 class='tdsmallbold'>Aparelhos de GPS&nbsp;<img height='15' src=\"icons/icon_question.gif\"";
$help = "Caso tenha indicado que você precisará anotar no formulário o número do ponto de GPS de sua coleta registrado num aparelho de GPS, indique aqui qual(is) aparelho(s) estará usando. (cadastrar como Equipamentos se não aparecer na lista";
echo " onclick=\"javascript:alert('$help');\" /></td>";
echo "
      </tr>
      <tr>
        <td class='tdsmallbold'>GPS padrão<img height='15' src=\"icons/icon_question.gif\"";
$help = "Indique o GPS que aparecerá como opção selecionada no formulário ODK.";
echo " onclick=\"javascript:alert('$help');\" /></td>
        <td><select name=\"gpsunit_def\" >";
	$qq = "SELECT * FROM Equipamentos LEFT JOIN Users ON Users.UserID=Equipamentos.AddedBy WHERE Type LIKE 'gps' ORDER BY Equipamentos.Name ASC";
	$res = mysql_query($qq,$conn);
	while ($row =  mysql_fetch_assoc($res)) {
		if (($gpsunit_def+0)==$row["EquipamentoID"] && !empty($gpsunit_def)) {
			$opt = "selected";
		} else {  $opt = ""; }
		echo "
      <option ".$opt." value='".$row['EquipamentoID']."' >".$row['Name']."</option>";
	}
echo "
          </select>
        </td>
      </tr>
      <tr>
        <td class='tdsmallbold'>Outros GPSs<img height='15' src=\"icons/icon_question.gif\"";
$help = "Demais aparelhos de GPS que estarão sendo usados em campo";
echo " onclick=\"javascript:alert('$help');\" /></td>
        <td><select name=\"gpsunits[]\" multiple size=5 >";
	$qq = "SELECT * FROM Equipamentos LEFT JOIN Users ON Users.UserID=Equipamentos.AddedBy WHERE Type LIKE 'gps' ORDER BY Equipamentos.Name ASC";
	$res = mysql_query($qq,$conn);
	while ($row =  mysql_fetch_assoc($res)) {
		if (in_array($row['EquipamentoID'], $gpsunits,TRUE)) {
			$opt = "selected";
		} else { $opt= "";}
		echo "
          <option ".$opt." value='".$row['EquipamentoID']."' >".$row['Name']."</option>";
	}
echo "
          </select>
        </td>
      </tr>
    </table>
  </td>
</tr>";

//CLASSE DE HABITAT
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td class='tdsmallbold'>Inclui classes de habitat?&nbsp;<img height='15' src=\"icons/icon_question.gif\" ";
$help = "Criará no seu formulário uma entrada do tipo select para classes de habitat cadastradas na base.";
if ($addhabitatclass==1) { $habtxt = "checked";} else {$habtxt = "";}

echo " onclick=\"javascript:alert('".$help."');\" />&nbsp;&nbsp;<input type=\"checkbox\" name=\"addhabitatclass\" ".$habtxt." value=\"1\">
        </td>
      </tr>
    </table>
  </td>
</tr>";

//INCLUIR VARIAVEIS DE IMAGENS
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td class='tdsmallbold'>Variáveis de imagens&nbsp;<img height='15' src=\"icons/icon_question.gif\" ";
$help = "Selecione as variáveis de imagem para as quais vai coletar imagens em campo";
echo " onclick=\"javascript:alert('".$help."');\" />
        </td>
        <td><select name=\"varimgsids[]\" multiple size=10 >";
	$qq = "SELECT * FROM Traits WHERE TraitTipo LIKE '%imag%' ORDER BY TraitName ASC";
	$res = mysql_query($qq,$conn);
	while ($row =  mysql_fetch_assoc($res)) {
		if (in_array($row['TraitID'], $varimgsids,TRUE)) {
			$opt = "selected";
		} else { $opt= "";}
		echo "
          <option ".$opt." value='".$row['TraitID']."' >".$row['TraitName']."</option>";
	}
echo "
          </select>
        </td>
      </tr>
    </table>
  </td>
</tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>


      <tr>
        <td class='tdsmallbold'>Variáveis do usuário<img height='15' src=\"icons/icon_question.gif\" ";
$help = "Indique quais formulários contém as variáveis que você quer incluir no formulario ODK. Coloque eles na ordem desejada.";
echo " onclick=\"javascript:alert('$help');\" /></td>
        <td class='tdformnotes' >
        <input type='hidden' id='varvalues'  name='varvalues' value='".$varvalues."' />
        <textarea name='varvaluestxt' id='varvaluestxt' rows=3 readonly>".$varvaluestxt."</textarea>
        </td>
        <td>
         <input type='button' style=\"color:#4E889C; font-size: 0.8em; padding: 4px; cursor:pointer;\"  value='Formulários'  onmouseover=\"Tip('Formulários');\" ";
		$myurl ="formularios_select.php?valuevar=varvalues&valuetxt=varvaluestxt&formname=odkformname&enviado=1"; 
		echo " onclick = \"javascript:small_window('".$myurl."',800,500,'Variáveis de usuário');\" /></td>
      </tr>
    </table>
  </td>
</tr>
";

if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table align='center' >
      <tr>
        <td align='center' >
        <input type='hidden' name='saving' value='1' />
        <input type='submit' style=\"color:#4E889C; font-size: 0.8em; padding: 4px; cursor:pointer;\"  value='".GetLangVar('namesalvar')." ".mb_strtolower(GetLangVar('namedefinicoes'))."' />
      </td>
</form>";
if ($odkformid>0 && $odkformid!="criar") {
echo "
<form action='odkcollect_generate.php' method='post' >
<input type='hidden'  value='".$odkformid."' name='odkformid' >
        <td align='left'>&nbsp;&nbsp;&nbsp;<input type='submit' value='Gera XML' style=\"color:#4E889C; font-size: 0.8em; padding: 4px; cursor:pointer;\"  />&nbsp;&nbsp;&nbsp;</td>
</form>";
}
echo "
<form action='odkcollect_inicio.php' method='post'>
        <td align='left'><input type='submit' value='Início' style=\"color:#4E889C; font-size: 0.8em; padding: 4px; cursor:pointer;\"  /></td>
</form>
      </tr>
    </table>
  </td>
</tr>
</tbody>
</table>";

$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>
