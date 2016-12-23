<?php
//Este script importa o arquivo CSV ou TXT selecionado para uma tabela temporaria mysql
//Depois sao perguntados quais colunas indicam amostras coletadas ou plantas marcadas
//Ultima atualizacao: 25 jun 2011 - AV
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
"<link href='css/geral.css' rel='stylesheet' type='text/css' />","<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' >"
);
$which_java = array("<script type='text/javascript' src='javascript/ajax_framework.js'></script>");
$title = 'Importar passo 01';
$body = '';

$fname = @$_FILES['uploadfile']['name'];
if (!isset($imported) && empty($fname)) {
	header("location: import-especialista-form.php?ispopup=".$ispopup);
	exit();
} 
FazHeader($title,$body,$which_css,$which_java,$menu);

$tbprefix = "WikiCol_";
if ($imported!='1') {
	$ok=FALSE;
	if (!isset($imported)) {
		$fname = $_FILES['uploadfile']['name'];
		$fileuploaded = $_FILES['uploadfile']['tmp_name'];
		//salva o arquivo importando no permanentemente no servidor
		$ext = explode(".",$fname);
		$ll = count($ext)-1;
		$extens = $ext[$ll];
		unset($ext[$ll]);
		$fn = implode(".",$ext);
		$importdate = date("Y-m-d");
		$newfilename = $fn."_ImportadoEspecialista_".$_SESSION['userid']."_".$importdate.".".$extens;
		
		$vv = implode("",$ext);
		$vv = str_replace(" ", "", $vv);
		$vv = str_replace("-", "", $vv);
		$vv = str_replace("&", "", $vv);
		$vv = str_replace("/", " ", $vv);
		$vv = str_replace("\\", " ", $vv);	
		$tbname = "temp_".$vv."_".$_SESSION['userid'];

		if (!file_exists("uploads/data_files/".$newfilename)) {
			$ok = TRUE;
			move_uploaded_file($_FILES["uploadfile"]["tmp_name"],"uploads/data_files/".$newfilename);
		} else {
			move_uploaded_file($_FILES["uploadfile"]["tmp_name"],"uploads/".$newfilename);
echo "
<br />
<form action='import-especialistas-step1.php' method='post' name='impprepform'>
  <input type='hidden' name='tbname' value='".$tbname."' />
  <input type='hidden' name='newfilename' value='".$newfilename."' />
  <table cellpadding=\"5\" align='center' class='erro' />
   <tr>
      <td align='center' colspan='2' >O arquivo $fname já foi importado por você anteriormente!</td>
    </tr>
    <tr>
      <td align='center' >
        <input type='hidden' name='imported' value='' />
        <input style='cursor: pointer' type='submit' value='Importar novamente' class='bsubmit' onclick=\"javascript:document.impprepform.imported.value=2\" />
      </td>
      <td align='left'><input style='cursor: pointer'  type='submit' value='Cancelar' class='bblue' onclick=\"javascript:document.impprepform.imported.value=3\" /></td>
    </tr>
  </table>
</form>
";
		$ok = FALSE;
		}
	} elseif ($imported=='2') {
		unlink("uploads/data_files/".$newfilename);
		copy("uploads/".$newfilename,"uploads/data_files/".$newfilename);
		unlink("uploads/".$newfilename);
		$ok=TRUE;
	}	
	if ($ok) {
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
				//echo $tt."<br />";
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
				//$spinsert."<br />";
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
echo "
<table align='left' class='myformtable' cellpadding=\"5\">
<thead>
  <tr><td colspan='100%'>Houve um problema!</td></tr>
</thead>
<tbody>
    <tr><td>Verificar o nome das colunas, a terminação das quebras de linha e o encoding do arquivo</td></tr>
    <tr><td>Não pode ter simbolos estranhos nos nomes das colunas, a quebra precisa ser em formato unix e o encoding UTF-8, as colunas separadas por Tabulação</td></tr>
  <form action='import-especialistas-form.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />  
    <tr><td  align='center'><input style='cursor: pointer'  type='submit' value='".GetLangVar('namevoltar')."' class='bsubmit' /></td></tr>
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
if ($imported==1) {
echo " 
<form action='import-especialistas-step2.php' method='post' name='impprepform'>
  <input type='hidden' name='imported' value='1' /> 
  <input type='hidden' name='tbname' value='".$tbname."' />
  <input type='hidden' name='tbprefix' value='".$tbprefix."' />
 </form>   
<script language=\"JavaScript\">setTimeout('document.impprepform.submit()',0.0001);</script>";
} //if imported
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>