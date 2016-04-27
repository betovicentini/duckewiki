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
} 

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;
$gget = cleangetpost($_GET,$conn);
@extract($gget);

//CABECALHO
$ispopup=1;
$menu = FALSE;
$title = '';
$which_css = array(
"<link rel='stylesheet' type='text/css' href='css/geral.css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' />"
);
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>"
);
$title = 'Herbarios';
$body= '';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "<script type='text/javascript' src='javascript/wz_tooltip.js'></script>";


$qq = "CREATE TABLE IF NOT EXISTS Herbaria (
 HerbariaID INT(10) unsigned NOT NULL auto_increment,
 Sigla CHAR(50),
 IdxHerbIrn INT(10),
 AddedBy INT(10),
 AddedDate DATE,
 PRIMARY KEY (HerbariaID)) CHARACTER SET utf8";
 @mysql_query($qq,$conn);

$qq = "CREATE TABLE IF NOT EXISTS HerbariaEspecs (
 HerbariaEspecsID INT(10) unsigned NOT NULL auto_increment,
 HerbariaID INT(10),
 EspecimenID INT(10),
 Type CHAR(100),
 Tombamento CHAR(100),
 AddedBy INT(10),
 AddedDate DATE,
 PRIMARY KEY (HerbariaEspecsID)) CHARACTER SET utf8";
 @mysql_query($qq,$conn);

if (!isset($acao)) {
echo "
<br />
<form name='iniform' action=herbaria.php method='post'>
<input type='hidden' name='acao' value='' />
<table class='myformtable' align='center' cellpadding='5'>
<thead>
  <tr><td colspan=2>Editar ou Adicionar Herbario</td></tr>
</thead>
<tbody>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan=2>
    <select name='herbariumid' >
    <option value='' >Selecione</option>";
	$sql = "SELECT Sigla, HerbariaID FROM Herbaria ORDER BY Sigla";
	$rsql = mysql_query($sql,$conn);
	while($row = mysql_fetch_assoc($rsql)) {
		echo "
<option value='".$row['HerbariaID']."' >".$row['Sigla']."</option>";
	}
	echo "    
    </select>
   </td>
</tr>  
<tr>
      <td align='center' >
        <input type='submit' value='Editar selecionado' class='bsubmit' onclick=\"javascript:document.iniform.acao.value=1\" />
      </td>
      <td align='left'>
        <input type='submit' value='Cadastrar herbario' class='bblue' onclick=\"javascript:document.iniform.acao.value=2\" />
      </td>
    </tr>
  </table>
  </td>
</tr>
</tbody>
</table>
</form>
";
} else {
	//echopre($ppost);
	$idxtxt = "";
	//CRIA NOVO REGISTRO SE FOR OPCAO
	if (!isset($saveit)) {
		if ($acao==1 && $herbariumid>0) {
			$sql = "SELECT * FROM Herbaria WHERE HerbariaID=".$herbariumid;
			$rsql = mysql_query($sql,$conn);
			$rsqlr = mysql_fetch_assoc($rsql);
			$sigla = $rsqlr['Sigla'];
			$IdxHerbIrn = $rsqlr['IdxHerbIrn'];
		    if (($IdxHerbIrn+0)>0) {
		    	$idxtxt = "<br /><a href=\"http://sweetgum.nybg.org/science/ih/herbarium_details.php?irn=".$IdxHerbIrn."\">Link para Index Herbariorum</a>";
	    	} 
		}
		echo "
<br />
<form name='fillform' action=herbaria.php method='post'>
<input type='hidden' name='acao' value='".$acao."' />
<input type='hidden' name='saveit' value='1' />
<input type='hidden' name='herbariumid' value='".$herbariumid."' />
<table class='myformtable' align='center' cellpadding='5'>
<thead>
  <tr><td colspan=2>Adicionar Herbario</td></tr>
</thead>
<tbody>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>Sigla do herbário*</td>
  <td><input style='height: 20px; width: 200px; font-weight: bold; font-size: 1.1em;' name='sigla' value='".$sigla."' ></td>
</tr>";  
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>Index herbariorum ID*&nbsp;<img height='12' src=\"icons/icon_question.gif\" ";
	$help = "O valor da variável IRN do registro do herbario no index herbariorum. Procure pela sua sigla e encontre o caminho do registro. Por exemplo, para o INPA o irn é 124921, que você encontra no final da url endereço http://sweetgum.nybg.org/science/ih/herbarium_details.php?irn=124921";
	echo "onclick=\"javascript:alert('$help');\" /></td>
  <td><input style='height: 20px; width: 200px;' name='IdxHerbIrn' value='".$IdxHerbIrn."' >$idxtxt</td>
</tr>
<tr>
  <td align='center' colspan=2 ><input type='submit' value='Salvar' class='bsubmit' /></td>
</tr>
</tbody>
</table>
</form>
";
	
	} else {
		//se editando
		if ($herbariumid>0 && $acao==1) {
		
		
		} else {

		
		
		}
	}

}


$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>