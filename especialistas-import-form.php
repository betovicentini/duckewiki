<?php
//Start session
ini_set("memory_limit","-1");
ini_set("mysql.allow_persistent","-1");

session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
//include "bibtex2html.php";
require_once "functions/BibTex.php";


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
$which_java = array();
$title = 'Importa lista de especialistas';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (!isset($imported)) {
echo "
<br />
<form enctype='multipart/form-data' action='especialista-import-form.php' method='post'>
<input type='hidden' name='MAX_FILE_SIZE' value='100000000000' />
<input type='hidden' name='imported' value='1'>
<table align='center' class='myformtable' cellpadding=\"7\" width='90%' />
<thead>
<tr>
<td colspan='2' class='tabhead' >".GetLangVar('nameimportar')." ".GetLangVar('namefile')." Especialistas</td>
</tr>
</thead>
<tbody>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td style='color: #990000; font-weight:bold' >".GetLangVar('namefile')."*&nbsp;&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
		$help = "O arquivo para importar deve conter registros BibTex, ou seja, ser um arquivo em formato *.bib, ter quebra de linha em formato UNIX, e código de fonte UTF-8 (no (Ru)Windows use Notepad++, no Mac TextWrangler para essas opções";
		echo " onclick=\"javascript:alert('".$help."');\" /></td>
  <td>
    <input name='uploadfile' type='file' width='20' />
  </td>
</tr>
";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
$txt = "Precisa ter as  colunas <b>Prenome, SegundoNome, Sobrenome, Email, Abreviacao,
Familia,  Generos, Herbarium</b>.";
echo "
<tr bgcolor = '".$bgcolor."'><td class='tdformnotes' colspan='2'>".$txt."</td></tr>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
//O bibtexkey é a índice único da referência bibliográfica. Se já houver na base irá atualizar se for a mesma revista, ano e páginas. Caso contrário, irá adicionar a referência e mudar o bibtexkey adicionando uma letra ao final. Certifique-se que todas as referências tem um bibtexkey!
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdformnotes' colspan='2'>".$help."</td>
</tr>
";
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
		$fname = $_FILES['uploadfile']['name'];
		$fileuploaded = $_FILES['uploadfile']['tmp_name'];
		$file_size = $_FILES['uploadfile']['size'];
		$file_type = $_FILES['uploadfile']['type'];
		if ($file_size > 1097152){      
			$message = 'Arquivo muito grande. Precisa ser menor que 1MB'; 
			echo $message; 
		} 
		else  {
		//echopre($_FILES);
		//salva o arquivo importando no permanentemente no servidor
		$ext = explode(".",$fname);
		$ll = count($ext)-1;
		$extens = $ext[$ll];
		unset($ext[$ll]);
		$fn = implode(".",$ext);
		$importdate = date("Y-m-d");
		$newfilename = $fn."_especialista_".$_SESSION['userid']."_".$importdate.".".$extens;
		move_uploaded_file($_FILES["uploadfile"]["tmp_name"],"uploads/especialistas/".$newfilename);
		
		$fileuploaded = "uploads/especialistas/".$newfilename;
		
	//faz a importacao do arquivo numa tabela no mysql
	$tbname = 'temp_especialistas_'.$_SESSION['userid']."_".$importdate;
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
	//echo $qq."<br >";
	$rr = @mysql_query($qq,$conn);
	$nrr = @mysql_numrows($rr);

	if ($nrr>0) {
		$imported=1;
	} else {
		echo "<br />Houve um erro. Tabela ".$tbname."  não foi criada<br />";
	}

	}
}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>
