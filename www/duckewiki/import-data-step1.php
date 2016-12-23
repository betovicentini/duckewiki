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
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' >"
);
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>");
$title = 'Importar passo 01';
$body = '';

$fname = @$_FILES['uploadfile']['name'];
if ($imported=='3' || (!isset($imported) && empty($fname)) || (empty($coletas))) {
	header("location: import-data-form.php?ispopup=".$ispopup);
	exit();
} 
FazHeader($title,$body,$which_css,$which_java,$menu);

//echopre($ppost);
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

		if (!file_exists("uploads/data_files/".$newfilename)) {
			$ok = TRUE;
			move_uploaded_file($_FILES["uploadfile"]["tmp_name"],"uploads/data_files/".$newfilename);
		} else {
			move_uploaded_file($_FILES["uploadfile"]["tmp_name"],"uploads/".$newfilename);
echo "
<br />
<form action='import-data-step1.php' method='post' name='impprepform'>
  <input type='hidden' name='coletas' value='".$coletas."' /> 
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
	//echo "<div id='counter2' style=\"position: relative; top: 100px; padding: 1px; color:  red; font-size: 1em;\"></span>";
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
						$spinsert = $spinsert."`".$val."`, ";
					} else {
						$spinsert = $spinsert."`".$val."`)";
					}
					$j++;
				}
				
				$j=0;
				$nt = count($data);
				$spinsert = $spinsert." VALUES (";
				foreach ($data as $key => $val) {
					$val = trim($val);
					if ($val=='NA') { $val =''; }
					if ($j!==($nn-1)) {
						$spinsert = $spinsert."\"".$val."\", ";
					} else {
						$spinsert = $spinsert."\"".$val."\")";
					}
					$j++;
				}
				$rii = mysql_query($spinsert,$conn);
				if (!$rii) {
					//echo $spinsert."<br />";
				}
			}
			$i++;
			echo '&nbsp;';
			//echo "<script type='text/javascript' >importacaoprogress(counter2,".$i.");</script>";
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
<table align='left' class='myformtable' cellpadding=\"7\" width='60%'>
<thead>
  <tr><td >Houve um problema!</td></tr>
</thead>
<tbody>
    <tr><td>Verificar o nome das colunas, a terminação das quebras de linha e o encoding do arquivo</td></tr>
    <tr><td>Não pode ter simbolos estranhos nos nomes das colunas, a quebra precisa ser em formato unix e o encoding UTF-8, as colunas separadas por Tabulação</td></tr>
  <form action='import-data-form.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />  
    <tr><td align='center'><input style='cursor: pointer'  type='submit' value='".GetLangVar('namevoltar')."' class='bsubmit' /></td></tr>
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
<form name='coletaform' action='import-data-step1.php' method='post'>
  <input type='hidden' name='coletas' value='".$coletas."' /> 
  <input type='hidden' name='imported' value='1' /> 
  <input type='hidden' name='tbname' value='".$tbname."' />
  <input type='hidden' name='tbprefix' value='".$tbprefix."' />
  <input type='hidden' name='ispopup' value='".$ispopup."' />  
  <input type='hidden' name='locidadeall' value='' />
</form>  
<form action='import-data-step2.php' method='post' name='impprepform'>
  <input type='hidden' name='coletas' value='".$coletas."' /> 
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input type='hidden' name='imported' value='1' /> 
  <input type='hidden' name='tbname' value='".$tbname."' />
  <input type='hidden' name='refdefined' value='' />
  <input type='hidden' name='tbprefix' value='".$tbprefix."' />
  <table cellpadding='5' class='myformtable' align='left'>";
	if ($coletas==1 || $coletas==3) {
	echo "
<thead>
  <tr><td class='tdsmallbold' colspan='3'>".GetLangVar('nametaggedplant')." - selecione identificadores</td></tr>
</thead>
<tbody>";
//identify fields
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>OPÇÃO&nbsp;1</td>
  <td class='tdsmallbold'>Coluna com PlantaID do wiki</td>
  <td>
    <select name='plantaidfield'>
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
				$brh = array('plantaid','wikiplantaid');
				$zf = mb_strtolower($fieldname);
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
  <td class='tdsmallbold'>OPÇÃO&nbsp;2</td>
  <td class='tdsmallbold'>Coluna com número da placa</td>
  <td>
    <select name='tagnumfield'>
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
				$qq = "SELECT * FROM Import_Fields WHERE LOWER(NamesToMatch) LIKE '%".mb_strtolower($fieldname)."%' AND FieldsToPut='PlantaTag'";
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
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>&nbsp;</td>
  <td class='tdsmallboldleft'>Localidade das plantas&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
		$help = "Como número de árvores podem repetir entre localidades, você precisa informar também uma das opções da localidade: (A) a localidade onde estão todas as plantas sendo importadas; ou (A+B), plantas em sublocalidade (B) de uma localidade geral (A); ou (C), coluna na sua tabela que tem o ID (GazetteerID) de localidades cadastradas da base";
		$localfile = 'search-gazetteer.php';
		echo " onclick=\"javascript:alert('$help');\" /></td>
  <td>
    <table>
      <tr>
        <td>Opção&nbsp;A: </td>
        <td>
          <table>
            <tr>
              <td class='tdformnotes'>Localidade para todas as plantas ou localidade geral</td>
              <td class='tdformnotes'>";
              autosuggestfieldval3($localfile,'localnome',$localnome,'localres','localid',$localid,true,60);
				$myurl = "localidade_dataexec.php?ispopup=1";
			echo "
              </td>
              <td><input type=button class='bblue' value='".GetLangVar('namenova')."'  onclick =\"javascript:small_window('$myurl',900,300,'Cadastrar nova localidade');\" /></td>
            </tr>
          </table>
        </td>
    </tr>
    <tr>
        <td>Opção&nbsp;B: </td>
        <td>
          <table>
            <tr>
                <td class='tdformnotes'>Coluna com localidade ou sub-localidade de localidade geral</td>
                <td class='tdformnotes'>
                  <select name='plantagazfield'>
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
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td>Opção&nbsp;C:</td>
          <td>
            <table>
            <tr>
                <td class='tdformnotes'>Coluna com um GazetteerID</td>
                <td class='tdformnotes'>
                  <select name='plantagazidfield'>
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
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
</tr>
</tbody>";
	}
	if ($coletas>1) {
		echo "
<thead>
  <tr><td class='tdsmallbold' colspan='3'>".GetLangVar('nameamostra')." ".GetLangVar('namecoletada')." - selecione identificadores</td></tr>
</thead>
<tbody>";
		if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
			echo "
    <tr bgcolor = '".$bgcolor."'>
        <td class='tdsmallbold'>OPÇÃO&nbsp;1</td>
        <td class='tdsmallbold'>Coluna com EspecimenID do wiki</td>
        <td>
          <select name='specimenidfield'>";
			echo "
            <option value=''>".GetLangVar('nameselect')."</option>";
			$qq = "SELECT * FROM `".$tbname."` PROCEDURE ANALYSE ()";
			$rq = mysql_query($qq,$conn);
			while ($rw = mysql_fetch_assoc($rq)) {
				$fin = $rw['Field_name'];
				$zz = explode(".",$fin);
				$xt = count($zz)-1;
				$fieldname = trim($zz[$xt]);
			if ($fieldname!="ImportID") {	
				$brh = array('especimenid','wikiespecimenid');
				$zf = mb_strtolower($fieldname);
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
		
		

	
		if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
		echo "
    <tr bgcolor = '".$bgcolor."'>
      <td class='tdsmallbold'>OPÇÃO&nbsp;2</td>
      <td colspan='2'>
      <table><tr>
        <td class='tdsmallbold'>Coluna com nome do coletor</td>
        <td>
          <select name='coletorfield'>
            <option value=''>".GetLangVar('nameselect')."</option>";			
			$qq = "SELECT * FROM `".$tbname."` PROCEDURE ANALYSE ()";
			$rq = mysql_query($qq,$conn);
			while ($rw = mysql_fetch_assoc($rq)) {
				$fin = $rw['Field_name'];
				$zz = explode(".",$fin);
				$xt = count($zz)-1;
				$fieldname = $zz[$xt];
			if ($fieldname!="ImportID") {
				$qq = "SELECT * FROM Import_Fields WHERE LOWER(NamesToMatch) LIKE '%".mb_strtolower($fieldname)."%' AND FieldsToPut='ColetorID'";
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
        <td class='tdsmallbold'>&nbsp;número de coleta</td>
        <td>
          <select name='numcolfield'>
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
				$qq = "SELECT * FROM Import_Fields WHERE LOWER(NamesToMatch) LIKE '%".mb_strtolower($fieldname)."%' AND FieldsToPut='Number'";
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
      </tr></table>
      </td>
    </tr>
";
	}
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
    <tr bgcolor = '".$bgcolor."'>
        <td colspan='3' align='center'>
            <input style='cursor: pointer'  type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' onclick=\"javascript:document.impprepform.refdefined.value=1\" />
        </td>
    </tr>
</tbody>
</table>
</form>";
} //if imported
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js?lixo=1233'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>