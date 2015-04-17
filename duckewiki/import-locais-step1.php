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
$ispopup=1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' />"
//, "<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>"
//"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Importar locais passo 01';
$body = '';

$fname = @$_FILES['uploadfile']['name'];
if ($imported=='3' || (!isset($imported) && empty($fname) ) ) {
	header("location: import-locais-form.php?ispopup=".$ispopup);
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
		@mkdir("uploads/gazetteer_files", 0755);
		if (!file_exists("uploads/gazetteer_files/".$newfilename)) {
			$ok = TRUE;
			move_uploaded_file($_FILES["uploadfile"]["tmp_name"],"uploads/gazetteer_files/".$newfilename);
		} else {
			move_uploaded_file($_FILES["uploadfile"]["tmp_name"],"uploads/".$newfilename);
echo "
<br />
<form action='import-locais-step1.php' method='post' name='impprepform'>
  <input type='hidden' name='tbname' value='".$tbname."' />
  <input type='hidden' name='newfilename' value='".$newfilename."' />
  <input type='hidden' name='ispopup' value='".$ispopup."' />  
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
	} 
	elseif ($imported=='2') {
		unlink("uploads/gazetteer_files/".$newfilename);
		copy("uploads/".$newfilename,"uploads/gazetteer_files/".$newfilename);
		unlink("uploads/".$newfilename);
		$ok=TRUE;
	}
	if ($ok) {
	$fileuploaded = "uploads/gazetteer_files/".$newfilename;
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
						$tt = $tt."`".$val."` TEXT, ";
					} else {
						$tt = $tt."`".$val."` TEXT)";
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
<table align='center' class='myformtable' cellpadding=\"5\">
<thead>
  <tr><td >Houve um problema!</td></tr>
</thead>
<tbody>
    <tr><td>Verificar o nome das colunas, a terminação das quebras de linha e o encoding do arquivo</td></tr>
    <tr><td>Não pode ter simbolos estranhos nos nomes das colunas, a quebra precisa ser em formato unix e o encoding UTF-8, as colunas separadas por Tabulação</td></tr>
  <form action='import-locais-form.php' method='post'>
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
if ($imported==1 && (!isset($opcao) || empty($opcao))) {
//echo "<form name='coletaform' action='import-locais-step1.php' method='post'>    <input type='hidden' name='imported' value='1' />     <input type='hidden' name='tbname' value='".$tbname."' />    <input type='hidden' name='tbprefix' value='".$tbprefix."' />    <input type='hidden' name='ispopup' value='".$ispopup."' />    </form>  ";
echo "
<form action='import-locais-step1.php' method='post'  >
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input type='hidden' name='imported' value='1' /> 
  <input type='hidden' name='tbname' value='".$tbname."' />
  <input type='hidden' name='tbprefix' value='".$tbprefix."' />
  <table cellpadding='5' class='myformtable' align='center'>";
	echo "
<thead>
  <tr><td class='tdsmallbold' colspan='3'>Selecione uma das opções</td></tr>
</thead>
<tbody>";
//identify fields
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'><input type='radio' name='opcao' value=1 /></td>
  <td class='tdsmallbold'>OPÇÃO&nbsp;1</td>
  <td class='tdformnotes'>O arquivo contém pelo menos uma coluna político administrativas, Municipio ou MinorArea (pode conter também Pais e Estado ou MajorArea)</td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'><input type='radio' name='opcao' value=2 /></td>
  <td class='tdsmallbold'>OPÇÃO&nbsp;2</td>
  <td class='tdformnotes'>O arquivo contém apenas nomes de localidades que devem ser ligadas a um município</td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'><input type='radio' name='opcao' value=3 /></td>
  <td class='tdsmallbold'>OPÇÃO&nbsp;3</td>
  <td class='tdformnotes'>O arquivo contém apenas nomes de localidades que devem ser ligadas a uma outra localidade (e.g. parcelas numa reserva)</td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
    <tr bgcolor = '".$bgcolor."'>
        <td colspan='3' align='center'>
            <input  style='cursor: pointer'  type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit'  />
        </td>
    </tr>
</tbody>
</table>
</form>";
} 
elseif ($opcao>0) {

echo "
<form action='import-locais-step2.php' method='post'  >
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input type='hidden' name='imported' value='1' /> 
  <input type='hidden' name='tbname' value='".$tbname."' />
  <input type='hidden' name='tbprefix' value='".$tbprefix."' />
  <input type='hidden' name='opcao' value='".$opcao."' />
  <table cellpadding='5' class='myformtable' align='center'>
<thead>
<tr><td class='tdsmallbold' colspan='2'>Definir opção selecionada</td></tr>
</thead>
<tbody>";

//se o arquivo contem colunas administrativas
if ($opcao==1) {
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldleft'>Opção 1 - Informar colunas político administrativas</td>
  <td>
    <table>
      <tr>
        <td class='tdformnotes'>Pais</td>
          <td class='tdformnotes'>
            <select name='pais'>
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
							$qq = "SELECT * FROM Import_Fields WHERE LOWER(NamesToMatch) LIKE '%".strtolower($fieldname)."%' AND FieldsToPut='GazetteerID'";
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
          </tr>
      <tr>
        <td class='tdformnotes'>Estado (MajorArea)</td>
          <td class='tdformnotes'>
            <select name='provincia'>
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
							$qq = "SELECT * FROM Import_Fields WHERE LOWER(NamesToMatch) LIKE '%".strtolower($fieldname)."%' AND FieldsToPut='GazetteerID'";
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
          </tr>
      <tr>
        <td class='tdformnotes'>Municipio (MinorArea)</td>
          <td class='tdformnotes'>
            <select name='municipio'>
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
							$qq = "SELECT * FROM Import_Fields WHERE LOWER(NamesToMatch) LIKE '%".strtolower($fieldname)."%' AND FieldsToPut='GazetteerID'";
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
          </tr>
        </table>
      </td>
    </tr>";
}


//seleciona o municipio
if ($opcao==2) {
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
    <tr bgcolor = '".$bgcolor."'>
      <td class='tdsmallbold'>Municipio para as localidades (MinorArea)</td>
      <td>"; autosuggestfieldval3('search-municipio.php','municipio',$municipio,'munires','municipioid',$municipioid, true, 80);      
echo "</td>
</tr>"; 
}
//caso se liguem à uma localidade
if ($opcao==3) {
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
    <tr bgcolor = '".$bgcolor."'>
      <td class='tdsmallbold'>Localidade (e.g. Parque Nacional) geral <br /> das (sub)localidades à importar</td>
      <td>"; autosuggestfieldval3('search-gazetteer.php','gazetteer',$gazetteer,'gazres','pparentid',$pparentid, true, 80);
echo "</td>
</tr>"; 
}
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
//,"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>
