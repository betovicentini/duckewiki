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
$title = 'Importar Expedito 01';
$body = '';

$fname = @$_FILES['uploadfile']['name'];
if ($imported=='3' || (!isset($imported) && empty($fname))) {
	header("location: import-expedito-form.php");
	exit();
} 

FazHeader($title,$body,$which_css,$which_java,$menu);
//prefixo para reconhecer as colunas a serem importadas (essas novas colunas serao criadas para os dados checados e limpos)
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
		$newfilename = $fn."_Original_".$_SESSION['userid']."_".$importdate.".".$extens;
		$newfilenameimp = $fn."_Importado_".$_SESSION['userid']."_".$importdate.".sql";

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
<form action='import-expedito-step01.php' method='post' name='impprepform'>
  <input type='hidden' name='ispopup'  value='".$ispopup."' />
  <input type='hidden' name='tbname' value='".$tbname."' />
  <input type='hidden' name='newfilename' value='".$newfilename."' />
  <input type='hidden' name='newfilenameimp' value='".$newfilenameimp."' />
  <table cellpadding=\"5\" align='center' class='erro' />
   <tr>
      <td align='center' colspan='100%' >O arquivo $fname já foi importado por você anteriormente!</td>
    </tr>
    <tr>
      <input type='hidden' name='imported' value='' />
      <td align='center' ><input type='submit' value='Importar novamente' class='bsubmit' onclick=\"javascript:document.impprepform.imported.value=2\" /></td>
      <td align='left'><input type='submit' value='Cancelar' class='bblue' onclick=\"javascript:document.impprepform.imported.value=3\" /></td>
    </tr>
  </table>
</form>
";
		$ok = FALSE;
		}
	} 
	elseif ($imported=='2') {
		unlink("uploads/data_files/".$newfilename);
		copy("uploads/".$newfilename,"uploads/data_files/".$newfilename);
		unlink("uploads/".$newfilename);
		$ok=TRUE;
	}
	if ($ok) {
	$fileuploaded = "uploads/data_files/".$newfilename;
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
	$qq = "ALTER TABLE ".$tbname." ADD ImportID INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
	mysql_query($qq,$conn);
	$qq = "SELECT * FROM ".$tbname;
	//echo "  ".$qq."<br />";
	$rr = mysql_query($qq,$conn);
	$nrr = mysql_numrows($rr);

	if ($nrr>0) {
		$imported=1;
	} 
	else {
echo "
<table align='center' class='myformtable' cellpadding=\"5\"  width='60%'>
<thead>
  <tr><td colspan='100%'>Houve um problema!</td></tr>
</thead>
<tbody>
    <tr><td>Verificar o nome das colunas, a terminação das quebras de linha e o encoding do arquivo</td></tr>
    <tr><td>Não pode ter simbolos estranhos nos nomes das colunas, a quebra precisa ser em formato unix e o encoding UTF-8, as colunas separadas por Tabulação</td></tr>
  <form action='import-expedito-form.php' method='post'>
  <input type='hidden' name='ispopup'  value='".$ispopup."' />
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
if ($imported==1) {
	$_SESSION['expeditoimporfile'] = $newfilenameimp;
echo "
<form action='import-expedito-step02.php' method='post' name='myform'>
  <input type='hidden' name='ispopup'  value='".$ispopup."' />
  <input type='hidden' name='imported' value='1' /> 
  <input type='hidden' name='tbname' value='".$tbname."' />
  <input type='hidden' name='tbprefix' value='".$tbprefix."' />";
  foreach ($ppost as $kk => $vv) {
	if (!empty($vv)) {
		echo "
  <input type='hidden' name='".$kk."' value='".$vv."' />"; 
		}
	}

 
echo"
  <script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
  </form>";
} //if imported
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>