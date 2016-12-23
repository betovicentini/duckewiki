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
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Equipamentos salvando';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if ($enviado!=1) {

if (!empty($equipamentoid)) {
	$qq = "SELECT * FROM Equipamentos WHERE EquipamentoID='$equipamentoid'";
	$res = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($res);
	$donoid = $row['PessoaID'];
	$marca = $row['Marca'];
	$modelo = $row['Model'];
	$year = $row['Year'];
	$tipo = $row['Type'];
	$notas = $row['Notas'];

}

echo "
<br />
<table align='left' class='myformtable' cellpadding='5' cellspacing='0'>
<thead>
<tr ><td colspan='100%'>".GetLangVar('namecadastro')." ".GetLangVar('namequipamento')."</td></tr>
</thead>
<tbody>
<form action='equipamentos-exec.php' method='post' name='autorform'>
<input type=hidden name='ispopup' value='$ispopup' />
<input type=hidden name='equipamentoid' value='$equipamentoid' />
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>".GetLangVar('namepessoa')." responsável/dono (referência)</td>
  <td >
    <table>
      <tr>
        <td >
          <select name='donoid'>";
			if (!empty($donoid)) {
				$wrr = getpessoa($donoid,$abb=TRUE,$conn);
				$aa = mysql_fetch_assoc($wrr);
				echo "
            <option value='".$aa['PessoaID']."'>".$aa['Abreviacao']."</option>";
			} else {
				echo "
            <option value=''>".GetLangVar('nameselect')."</option>";
			}
			echo "
            <option value=''>----</option>";
			$wrr = getpessoa('',$abb=TRUE,$conn);
			while ($aa = mysql_fetch_assoc($wrr)){
					echo "
            <option value='".$aa['PessoaID']."'>".$aa['Abreviacao']."</option>";
				}
			echo "
          </select>
        </td>
      </tr>
    </table>
  </td>
</tr>
";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>".GetLangVar('nametipo')."</td>
  <td class='tdformnotes'>
    <table>
      <tr>
        <td>  <input type='radio' name='tipo' ";
		if ($tipo=='camera') { echo "checked";}
		echo " value='camera' />Photo Camera</td>
        <td><input type='radio' name='tipo' ";
		if ($tipo=='gps') { echo "checked";}
		echo " value='gps' />GPS</td>
        <td><input type='radio' name='tipo' ";
		if ($tipo=='scanner') { echo "checked";}
		echo " value='scanner' />Scanner</td>
      </tr>
    </table>
  </td>
</tr>
";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>".GetLangVar('namespecificacoes')."</td>
  <td class='small'>
    <table>
      <tr>
        <td>".GetLangVar('namemarca')."</td>
        <td><input type='text' name='marca' value='$marca' size='10' /></td>
        <td>".GetLangVar('namemodelo')."</td>
        <td><input type='text' name='modelo' value='$modelo' size='20' /></td>
        <td>".GetLangVar('nameano')."</td>
        <td><input type='text' name='year' value='$year' size='4' /></td>
      </tr>
    </table>
  </td>
</tr>
";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>".GetLangVar('nameobs')."s</td>
  <td class='tdformnotes' ><textarea name='notas' cols='67' rows='2'  >$notas</textarea></td>
</tr>
<tr>
<td colspan='100%' align='center'>
  <input type='hidden' name='enviado' value='1' />
  <input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' />
</td>
</tr>
</form>
</tbody>
</table>
<br />
";
} else {

	if ($donoid>0 && !empty($tipo) && !empty($marca) && !empty($modelo)) {
	//Create table if not exists
		$qq = "CREATE TABLE IF NOT EXISTS Equipamentos (
				EquipamentoID INT(10) unsigned NOT NULL auto_increment,
				Name VARCHAR(100),
				PessoaID INT(10),
				Type VARCHAR(30),
				Marca VARCHAR(100),
				Model VARCHAR(100),
				Year INT(4),
				Notas VARCHAR(500),
				AddedBy INT(10),
				AddedDate DATE,
				PRIMARY KEY (EquipamentoID))";		
		mysql_query($qq,$conn);
	
		$wrr = getpessoa($donoid,$abb=TRUE,$conn);
		$aa = mysql_fetch_assoc($wrr);
		$name = $marca."-".$modelo."-".$year."-".$aa['Sobrenome'];
		
		$arrayofvalues = array(
					'Name' => $name,
					'Type' => $tipo,
					'PessoaID' => $donoid,
					'Marca' => $marca,
					'Model' => $modelo,
					'Year' => $year,
					'Notas' => $notas);
		
		if ($equipamentoid>0) { //se editando
			$changed = CompareOldWithNewValues('Equipamentos','EquipamentoID',$equipamentoid,$arrayofvalues,$conn);
			if ($changed>0) {
				CreateorUpdateTableofChanges($equipamentoid,'EquipamentoID','Equipamentos',$conn);
				$newupdate = UpdateTable($equipamentoid,$arrayofvalues,'EquipamentoID','Equipamentos',$conn);
				if ($newupdate) {
					$updated++;
				}
			}
		} else {
			$newequipid = InsertIntoTable($arrayofvalues,'EquipamentoID','Equipamentos',$conn);
			if ($newequipid) {
					$inserted++;
				}
		}

		if ($inserted>0 || $updated>0) {
			echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='success'>
<tr><td class='tdsmallbold' align='center'>".GetLangVar('sucesso1')."</td></tr>
</table>
<br />";
		} else {
			echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
<tr><td class='tdsmallbold' align='center'>O cadastro n„o foi feito! Faltou alguma informaÁ„o ou houve um erro do programa</td></tr>
</table>
<br />";
		
		}
	} 
}


$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>