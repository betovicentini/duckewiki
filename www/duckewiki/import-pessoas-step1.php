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
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' />"
);
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>"
);
$title = 'Importar Pessoas Passo 01';
$body = '';

$fname = @$_FILES['uploadfile']['name'];
if ($imported=='3' || (!isset($imported) && empty($fname) ) ) {
	header("location: import-pessoas-form.php");
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
		$newfilename = $fn."_Importado_".$_SESSION['userid']."_".$importdate.".".$extens;
		
		$vv = implode("",$ext);
		$vv = str_replace(" ", "", $vv);
		$vv = str_replace("-", "", $vv);
		$vv = str_replace("&", "", $vv);
		$vv = str_replace("/", " ", $vv);
		$vv = str_replace("\\", " ", $vv);	
		$tbname = "temp_".$vv."_".$_SESSION['userid'];
		@mkdir("uploads/pessoas_files", 0755);
		if (!file_exists("uploads/pessoas_files/".$newfilename)) {
			$ok = TRUE;
			move_uploaded_file($_FILES["uploadfile"]["tmp_name"],"uploads/pessoas_files/".$newfilename);
		} else {
			move_uploaded_file($_FILES["uploadfile"]["tmp_name"],"uploads/".$newfilename);
echo "
<br />
<form action='import-pessoas-step1.php' method='post' name='impprepform'>
  <input type='hidden' name='tbname' value='".$tbname."' />
  <input type='hidden' name='newfilename' value='".$newfilename."' />
  <table cellpadding=\"5\" align='center' class='erro' />
   <tr>
      <td align='center' colspan='2' >O arquivo ".$fname." já foi importado por você anteriormente!</td>
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
	} 
	elseif ($imported=='2') {
		unlink("uploads/pessoas_files/".$newfilename);
		copy("uploads/".$newfilename,"uploads/pessoas_files/".$newfilename);
		unlink("uploads/".$newfilename);
		$ok=TRUE;
	}
	if ($ok) {
	$fileuploaded = "uploads/pessoas_files/".$newfilename;
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
						$tt = $tt."`".$val."` CHAR(255), ";
					} else {
						$tt = $tt."`".$val."` CHAR(255))";
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
		$imported=1;
	} 
	else {
echo "
<table align='left' class='myformtable' cellpadding=\"5\">
<thead>
  <tr><td >Houve um problema!</td></tr>
</thead>
<tbody>
    <tr><td>Verificar o nome das colunas, a terminação das quebras de linha e o encoding do arquivo</td></tr>
    <tr><td>Não pode ter simbolos estranhos nos nomes das colunas, a quebra precisa ser em formato unix e o encoding UTF-8, as colunas separadas por Tabulação</td></tr>
  <form action='import-pessoas-form.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />  
    <tr><td align='center'><input  style='cursor: pointer'  type='submit' value='".GetLangVar('namevoltar')."' class='bsubmit' /></td></tr>
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
$sql = "CREATE TABLE IF NOT EXISTS iterationTB (id INT(10))";
@mysql_query($sql,$conn);
$sql = "SELECT * FROM iterationTB";
$rsql = @mysql_query($sql,$conn);
$nrsql = mysql_numrows($rsql);
if ($nrsql<100) {
	$n=1;
	while($n<=100) {
		$sql2 = "INSERT INTO iterationTB (id) VALUES (".$n.")";
		@mysql_query($sql2,$conn);
		$n = $n+1;
	}
}
echo "
<form action='import-pessoas-step2form.php' method='post'  >
  <input type='hidden' name='imported' value='1' /> 
  <input type='hidden' name='tbname' value='".$tbname."' />
  <input type='hidden' name='tbprefix' value='".$tbprefix."' />
  <table cellpadding='5' class='myformtable' align='left'>
<thead>
<tr><td class='tdsmallbold' colspan='2'>Definir opção selecionada</td></tr>
</thead>
<tbody>";
//se o arquivo contem colunas administrativas
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
    <tr bgcolor = '".$bgcolor."'>
        <td class='tdformnotes'>Prenome**</td>
        <td class='tdformnotes'>
            <select name='prenome'>
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
							$qq = "SELECT * FROM Import_Fields WHERE LOWER(NamesToMatch) LIKE '%".mb_strtolower($fieldname)."%' AND FieldsToPut='GazetteerID'";
							$rqq = mysql_query($qq,$conn);
							$nrqq = mysql_numrows($qq,$conn);
							if ($nrqq>0) {
								$ch = 'selected'; 
							} else {$ch =''; }
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
    <td class='tdformnotes'>Último sobrenome**</td>
          <td class='tdformnotes'>
            <select name='sobrenome'>
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
							$qq = "SELECT * FROM Import_Fields WHERE LOWER(NamesToMatch) LIKE '%".mb_strtolower($fieldname)."%' AND FieldsToPut='GazetteerID'";
							$rqq = mysql_query($qq,$conn);
							$nrqq = mysql_numrows($qq,$conn);
							if ($nrqq>0) {
								$ch = 'selected'; 
							} else {$ch =''; }
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
        <td class='tdformnotes'>Outros nomes</td>
          <td class='tdformnotes'>
            <select name='segundonome'>
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
							$qq = "SELECT * FROM Import_Fields WHERE LOWER(NamesToMatch) LIKE '%".mb_strtolower($fieldname)."%' AND FieldsToPut='GazetteerID'";
							$rqq = mysql_query($qq,$conn);
							$nrqq = mysql_numrows($qq,$conn);
							if ($nrqq>0) {
								$ch = 'selected'; 
							} else {$ch =''; }
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
        <td class='tdformnotes'>Abreviacao**</td>
          <td class='tdformnotes'>
            <select name='abreviacao'>
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
							$qq = "SELECT * FROM Import_Fields WHERE LOWER(NamesToMatch) LIKE '%".mb_strtolower($fieldname)."%' AND FieldsToPut='GazetteerID'";
							$rqq = mysql_query($qq,$conn);
							$nrqq = mysql_numrows($qq,$conn);
							if ($nrqq>0) {
								$ch = 'selected'; 
							} else {$ch =''; }
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
        <td colspan='2' align='center'>
            <input style='cursor: pointer'  type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit'  />
        </td>
    </tr>
</tbody>
</table>
</form>";
}


$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>
