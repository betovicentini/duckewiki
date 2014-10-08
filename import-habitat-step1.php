<?php
//Este script importa o arquivo CSV ou TXT selecionado para uma tabela temporaria mysql
//Depois sao perguntados quais colunas contém os identificadores das colunas
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
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' >"
);

$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>",
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>"
);
$title = 'Importar Habitat 01';
$body = '';



$fname = @$_FILES['uploadfile']['name'];
if ($foiimportado=='3' || (!isset($foiimportado) && empty($fname))) {
	header("location: import-habitat-form.php");
	exit();
} 

FazHeader($title,$body,$which_css,$which_java,$menu);

//prefixo para reconhecer as colunas a serem importadas (essas novas colunas serao criadas para os dados checados e limpos)
$tbprefix = "WikiCol_";

if ($foiimportado!='1') {
	$finitook=FALSE;
	if (!isset($foiimportado)) {
		unset($_SESSION['fieldsign'],$_SESSION['firstdefinitions'],$_SESSION['importacaostep']);
		$fname = $_FILES['uploadfile']['name'];
		$fileuploaded = $_FILES['uploadfile']['tmp_name'];
		//salva o arquivo importando no permanentemente no servidor
		$ext = explode(".",$fname);
		$ll = count($ext)-1;
		$extens = $ext[$ll];
		unset($ext[$ll]);
		$fn = implode(".",$ext);
		$importdate = date("Y-m-d");
		$newfilename = $fn."_Importado_".$_SESSION['userid']."_".$importdate.".".$extens;

		$vv = implode("",$ext);
		$vv = str_replace(" ", "", $vv);
		$vv = str_replace("-", "", $vv);
		$vv = str_replace("&", "", $vv);
		$vv = str_replace("/", " ", $vv);
		$vv = str_replace("\\", " ", $vv);
		$tbname = "temp_".$vv."_".$_SESSION['userid'];

		if (!file_exists("uploads/data_files/".$newfilename)) {
			$finitook = TRUE;
			move_uploaded_file($_FILES["uploadfile"]["tmp_name"],"uploads/data_files/".$newfilename);
		} else {
			move_uploaded_file($_FILES["uploadfile"]["tmp_name"],"uploads/".$newfilename);
echo "
<br />
<form action='import-habitat-step1.php' method='post' name='impprepform'>
  <input type='hidden' name='tbname' value='".$tbname."' />
  <input type='hidden' name='newfilename' value='".$newfilename."' />
  <input type='hidden' name='ispopup' value='".$ispopup."' />

  <table cellpadding=\"5\" align='center' class='erro'>
   <tr>
      <td align='center' colspan='100%' >O arquivo $fname já foi importado por você anteriormente!</td>
    </tr>
    <tr>
      <input type='hidden' name='foiimportado' value='' />
      <td align='center' ><input type='submit' value='Importar novamente' class='bsubmit' onclick=\"javascript:document.impprepform.foiimportado.value=2\" /></td>
      <td align='left'><input type='submit' value='Cancelar' class='bblue' onclick=\"javascript:document.impprepform.foiimportado.value=3\" /></td>
    </tr>
  </table>
</form>
";
		$finitook = FALSE;
		}
	} 
	elseif ($foiimportado=='2') {
		unlink("uploads/data_files/".$newfilename);
		copy("uploads/".$newfilename,"uploads/data_files/".$newfilename);
		unlink("uploads/".$newfilename);
		$finitook=TRUE;
	}
	if ($finitook) {
	$fileuploaded = "uploads/data_files/".$newfilename;
	//faz a importacao do arquivo numa tabela no mysql
	$fop = @fopen($fileuploaded, 'r');
	$i=0;
	while (($data = fgetcsv($fop, 10000, "\t")) !== FALSE) {
			if ($i==0) {
				$header = $data;
				//Cria tabela vazia para armazenar dados
				$qq = "DROP TABLE ".$tbname;
				mysql_query($qq,$conn);
				$tt = "CREATE TABLE IF NOT EXISTS ".$tbname." (";
				$j=0;
				$nn = count($header);
				$hh = array();
				foreach ($header as $kk => $val) {
					$val =  RemoveAcentos($val);
					$symb = array(" ", ".",'/',"-","_",")","(");
					$val = str_replace($symb, "", $val);
					$val = str_replace(" ", "", $val);
					if ($j!==($nn-1)) {
						$tt = $tt.$val." TEXT, ";
					} else {
						$tt = $tt.$val." TEXT)";
					}
					$j++;
					$hh[] = $val;
				}
				$tt  = $tt." CHARACTER SET utf8";
				mysql_query($tt,$conn);
				//echo $tt;
				flush();
			} 
			elseif ($i>0) {
				$nn = count($header);
				$j=0;
				$spinsert = "INSERT INTO ".$tbname." (";
				foreach ($hh as $kk => $val) {
					$val = trim($val);
					if ($j!==($nn-1)) {
						$spinsert = $spinsert.$val.", ";
					} else {
						$spinsert = $spinsert.$val.")";
					}
					$j++;
				}

				$j=0;
				$spinsert = $spinsert." VALUES (";
				foreach ($data as $key => $val) {
					$val = trim($val);
					if ($val=='NA') { $val =''; }
					if ($j!==($nn-1)) {
						$spinsert = $spinsert."'".$val."', ";
					} else {
						$spinsert = $spinsert."'".$val."')";
					}
					$j++;
				}
				mysql_query($spinsert,$conn);
			}
			$i++;
			echo '&nbsp;';
			flush();
	}
	$qq = "ALTER TABLE `".$tbname."` ADD `ImportID` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
	mysql_query($qq,$conn);
	$qq = "SELECT * FROM `".$tbname."`";
	$rr = @mysql_query($qq,$conn);
	$nrr = @mysql_numrows($rr);

	if ($nrr>0) {
		$foiimportado=1;
	} else {
echo "
<table align='center' class='myformtable' cellpadding=\"5\">
<thead>
  <tr><td colspan='100%'>Houve um problema!</td></tr>
</thead>
<tbody>
    <tr><td>Verificar o nome das colunas, a terminação das quebras de linha e o encoding do arquivo</td></tr>
    <tr><td>Não pode ter simbolos estranhos nos nomes das colunas, a quebra precisa ser em formato unix e o encoding UTF-8, as colunas separadas por Tabulação</td></tr>
  <form action='import-habitat-form.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
    <tr><td colspan='100%' align='center'><input type='submit' value='".GetLangVar('namevoltar')."' class='bsubmit' /></td></tr>
  </form>
</tbody>
</table>
";
	}
	unset($_SESSION['fieldsign']);
	unset($_SESSION['firstdefinitions']);
	unset($_SESSION['importacaostep']);
	} 
}
if ($foiimportado=='1') {
echo "
<form name='coletaform' action='import-habitat-step1.php' method='post' name='coletaform'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input type='hidden' name='foiimportado' value='1' /> 
  <input type='hidden' name='tbname' value='".$tbname."' />
  <input type='hidden' name='tbprefix' value='".$tbprefix."' />
  <input type='hidden' name='locidadeall' value='' />
</form>  
<form action='import-habitat-step1b.php' method='post' name='impprepform'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input type='hidden' name='foiimportado' value='1' /> 
  <input type='hidden' name='tbname' value='".$tbname."' />
  <input type='hidden' name='refdefined' value='' />
  <input type='hidden' name='tbprefix' value='".$tbprefix."' />
<table cellpadding='7' class='myformtable' align='center' />
<thead>
  <tr><td class='tdsmallbold' colspan='100%'>Selecione identificadores para as linhas do seu arquivo</td></tr>
</thead>
<tbody>";
	//identify fields
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>OPÇÃO&nbsp;1&nbsp;<img height=13 src=\"icons/icon_question.gif\" ";
$help = "Essa opção permite relacionar os dados a serem importados a uma localidade tendo como referência os valores de GAZETTEERID de localidades já inseridas neste banco de dados.\n
Isso é útil para importar dados a partir de uma tabela de localidades exportada desta base de dados";
		echo " onclick=\"javascript:alert('".$help."');\" /></td>
  <td class='selectedval'>Coluna com GazetteerID do Wiki*</td>
  <td align='left'>
    <select name='gazetteeridfield'>
      <option value=''>".GetLangVar('nameselect')."</option>";
		$qq = "SELECT * FROM `".$tbname."` PROCEDURE ANALYSE ()";
		$rq = mysql_query($qq,$conn);
		$i=0;
		while ($rw = mysql_fetch_assoc($rq)) {
			$fin = $rw['Field_name'];
			$zz = explode(".",$fin);
			$xt = count($zz)-1;
			$fieldname = trim($zz[$xt]);
		if ($fieldname!="ImportID") {
			$brh = array('gazetteerid','gazetteeridfield');
			$zf = strtolower($fieldname);
			if (in_array($zf,$brh)) { 
				$ch = 'selected'; } else {$ch =''; }
				echo "
      <option  $ch value='".$fieldname."'>$fieldname</option>";
			}
		}
echo "
    </select>
  </td>
</tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>OPÇÃO&nbsp;2&nbsp;<img height=13 src=\"icons/icon_question.gif\" ";
$help = "Essa opção permite relacionar os dados a serem importados a uma localidade, como por exemplo, uma parcela ou subparcela,\n
um transecto, uma reserva, ou outra unidade amostral definida como localidade. Neste caso, você DEVE informar a coluna que contem o nome das localidades. Se estas não estiverem cadastradas no banco de dados, você será avisado para proceder com esse cadastro. Para facilitar a busca por localidades você DEVE informar uma localidade de nível superior, ou seja, uma localidade geral que inclua as localidades do arquivo.";
		echo " onclick=\"javascript:alert(\"".$help."\");\" /></td>
  <td colspan='2'>
    <table>
      <tr>
        <td class='selectedval'>Coluna com NOME da localidade*</td>
        <td>
          <select name='gazetteerfield'>
          <option value=''>".GetLangVar('nameselect')."</option>";
			$qq = "SELECT * FROM `".$tbname."` PROCEDURE ANALYSE ()";
			$rq = mysql_query($qq,$conn);
			$i=0;
			while ($rw = mysql_fetch_assoc($rq)) {
				$fin = $rw['Field_name'];
				$zz = explode(".",$fin);
				$xt = count($zz)-1;
				$fieldname = $zz[$xt];
			if ($fieldname!="ImportID") {
				$brh = array('localidade','gazetteer','parcela','plot');
				$zf = strtolower($fieldname);
			if (in_array($zf,$brh)) { 
				$ch = 'selected'; } else {$ch =''; }
				echo "
          <option  $ch value='".$fieldname."'>$fieldname</option>";
			}
			}
		echo "
        </select>
      </td>
    </tr>
    <tr>
        <td class='selectedval'>Coluna com LOCALIDADE GERAL*</td>
        <td class='tdformnotes'>";
			autosuggestfieldval3('search-gazetteer.php','locality',$locality,'gazres','parentgazetteerid',$parentgazetteerid,true,60); 
echo "</td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%' align='center'>
    <input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' onclick=\"javascript:document.impprepform.refdefined.value=1\" />
  </td>
</tr>
</tbody>
</table>
</form>";

} //if foiimportado
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>