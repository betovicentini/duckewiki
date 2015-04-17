<?php
//Start session
ini_set("memory_limit","-1");
ini_set("mysql.allow_persistent","-1");
//IMPORTA UMA TABELA QUALQUER AO MYSQL
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
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Importa registros do herbário para a base';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (!isset($imported)) {
echo "
<br />
<form enctype='multipart/form-data' action='processo-amostras-importarinpa.php' method='post'>
<input type='hidden' name='MAX_FILE_SIZE' value='100000000000' />
<input type='hidden' name='imported' value='1'>
<input type='hidden' name='processoid' value='".$processoid."'>
<input type='hidden' name='ispopup' value='".$ispopup."'>
<table align='center' class='myformtable' cellpadding=\"7\">
<thead>
<tr>
<td colspan='2' class='tabhead' >".GetLangVar('nameimportar')." ".GetLangVar('namefile')."</td>
</tr>
</thead>
<tbody>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td style='color: #990000; font-weight:bold' >".GetLangVar('namefile')."*&nbsp;&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
		$help = "O arquivo para importar deve estar em formato TXT ou CSV, separado por TABULAÇÃO, quebra de linha em formato UNIX e código de fonte UTF-8. O LibreOffice/OpenOffice  permite salvar arquivos em formato CSV com essa opções.";
		echo " onclick=\"javascript:alert('$help');\" /></td>
  <td>
    <input name='uploadfile' type='file' size='30' />
  </td>
</tr>
<tr bgcolor = '".$bgcolor."'>
  <td colspan='2'>Precisa ter as colunas <b>WikiEspecimenID, INPA</b>. <br />As demais colunas, se houver, serão ignoradas!</td>
</tr>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='2' align='center'>
    <input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' />
  </td>
</tr>
</tbody>
</table>
</form>
";
} 
else {
//echopre($ppost);
$fname = $_FILES['uploadfile']['name'];
$fileuploaded = $_FILES['uploadfile']['tmp_name'];
$erro = 0;
if (empty($fname ))  {
echo "
<br />
<table align='center' class='myformtable' cellpadding=\"7\">
<thead>
  <tr><td >Houve um problema!</td></tr>
</thead>
<tbody>
<tr><td>Faltou indicar o arquivo para importar</td></tr>
</tbody>
</table>
";
$erro++;
} 
if ($erro==0) {
		//echopre($_FILES);
		//salva o arquivo importando no permanentemente no servidor
		$ext = explode(".",$fname);
		$ll = count($ext)-1;
		$extens = $ext[$ll];
		unset($ext[$ll]);

		$vv = implode("",$ext);
		$vv = str_replace(" ", "", $vv);
		$vv = str_replace("-", "", $vv);
		$vv = str_replace("&", "", $vv);
		$vv = str_replace("/", " ", $vv);
		$vv = str_replace("\\", " ", $vv);
		$vv = str_replace("  ", " ", $vv);
		$vv = str_replace("  ", " ", $vv);
		$vv = str_replace("  ", " ", $vv);
		$vv = str_replace(" ", "-", $vv);
		$vv =  RemoveAcentos($vv);
		$tbname = "temp_INPANUM_".$vv."_".$_SESSION['userid'];
		$importdate = date("Y-m-d");
		$newfilename = "processo_".$processoid."_INPANUM_".$_SESSION['userid']."_".$importdate.".".$extens;

		if (!file_exists("uploads/data_files/".$newfilename)) {
move_uploaded_file($_FILES["uploadfile"]["tmp_name"],"uploads/data_files/".$newfilename);
		} 
		else {
			$erro++;
			echo "
<br />
<table align='center' class='myformtable' cellpadding=\"7\">
<thead>
  <tr><td >Houve um problema!</td></tr>
</thead>
<tbody>
<tr><td>Você não pode importar duas vezes o mesmo arquivo. Consulte o administrador!</td></tr>
</tbody>
</table>
";
		}
		if ($erro==0) {
		$fileuploaded = "uploads/data_files/".$newfilename;
		//echo $fileuploaded."<br />";
		//faz a importacao do arquivo numa tabela no mysql
		$fop = fopen($fileuploaded, 'r');
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
					$val = strtoupper($val);
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
					$val = trim(strtoupper($val));
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
				//echo "<br />".$spinsert."<br />";
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

		$qq = "SHOW COLUMNS FROM `".$tbname."` WHERE UPPER(Field)='WIKIESPECIMENID' ";
		$rr = @mysql_query($qq,$conn);
		$nrr1 = @mysql_numrows($rr);

		$qq = "SHOW COLUMNS FROM `".$tbname."` WHERE UPPER(Field)='INPA'";
		$rr = @mysql_query($qq,$conn);
		$nrr2 = @mysql_numrows($rr);

		//$qq = "SHOW COLUMNS FROM `".$tbname."` WHERE UPPER(Field)='BRAHMS'";
		//$rr = @mysql_query($qq,$conn);
		//$nrr3 = @mysql_numrows($rr);

		$allvals = $nrr1+$nrr2;
		
		//+$nrr3;
		//echo $allvals."<br />";
//if ($lixo==234567) {
		if ($nrr>0 && $allvals==2) {
		/////////////AQUI NOVO SCRIPT OU REDIRECT
				echo "
<form name='iptinpa'  action='processo-amostras-importainpa-form.php'  method='post' >
<input type='hidden' name='tbname'  value='".$tbname."' >
<input type='hidden' name='processoid'  value='".$processoid."' >
<script language=\"JavaScript\">setTimeout('document.iptinpa.submit()',0.00001);</script>
</form>";
		} 
		else {
echo "
<br />
<table align='center' class='myformtable' cellpadding=\"5\">
<thead>
  <tr><td >Houve um problema! 2</td></tr>
</thead>
<tbody>";
if ($allvals<3 && $nrr>0) {
 echo "
<tr><td>O seu arquivo não contém as colunas esperadas</td></tr>";
} 
else {
echo "
<tr><td>Verificar a terminação das quebras de linha e o encoding do arquivo</td></tr>
    <tr><td>Não pode ter simbolos estranhos nos nomes das colunas, a quebra precisa ser em formato unix e o encoding UTF-8, as colunas separadas por Tabulação</td></tr>
  <form action='processo-amostras-importarinpa.php' method='post'>
<input type='hidden' name='ispopup' value='".$ispopup."'>
<input type='hidden' name='processoid' value='".$processoid."'>
    <tr><td align='center'><input type='submit' value='".GetLangVar('namevoltar')."' class='bsubmit' /></td></tr>
  </form>
</tbody>
</table>
";
}
	}
	}
}
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>
