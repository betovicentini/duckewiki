<?php
//este script definir as variaveis de monitoramento checando por compatibilidade
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
$which_java = array("<script type='text/javascript' src='javascript/ajax_framework.js'></script>");
$title = 'Importar Dados Passo 10';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

//echopre($ppost);
$fsdefs = unserialize($_SESSION['firstdefinitions']);
@extract($fsdefs);
$fields = unserialize($_SESSION['fieldsign']);
if (in_array('MONI_VAR',$fields) && !isset($var_moni_ok)) {
	$nused=0;
	if (!isset($varmonitors)) {
		$monitorvars = array_keys($fields, "MONI_VAR");
	} else {
		//faz o update das variaveis definidas e checa por estados se for o caso
		$monitorvars = unserialize($varmonitors);
		if (count($monitorvars)>0) {
		$oldmonitorvars = $monitorvars;
		$butidx =1;
		foreach ($monitorvars as $monikk => $vv) {
			$kk = "moni_vars_".$monikk;
			$traitid = $_POST[$kk]+0;
			$kk = "monivarnovo_".$monikk;
			$ttid = $_POST[$kk]+0;
			if ($ttid>0 && $traitid==0) { $traitid=$ttid;}
			$kk = "datamonitorfields_".$monikk;
			if (!empty($_POST[$kk])) {
			$date1 = $tbprefix.$_POST[$kk];
			} else {
				$date1='';
			}
			$kk = "dataobs_".$monikk;
			$date2 = trim($_POST[$kk]);

			//pega referencia bibliográfica
			$kk = "bibkeymonitorfields_".$monikk;
			if (!empty($_POST[$kk])) {
				$bibref = $tbprefix.$_POST[$kk];
			} 
			
			$kk = "varunitmonitorfields_".$monikk;
			$unit1 = $_POST[$kk];
			$kk = "varunit_".$monikk;
			$unit2 = TRIM($_POST[$kk]);
			if ($traitid>0) {
				$qq = "SELECT * FROM Traits WHERE TraitID='".$traitid."'";
				$rr = mysql_query($qq,$conn);
				$row = mysql_fetch_assoc($rr);
				$tname = $row['TraitName'];
				$tpathname = $row['PathName'];
				$tunidade = $row['TraitUnit'];
				if (empty($unit1) && empty($unit2)) { $unit1 = $tunidade;}
				$tt = explode("|",$row['TraitTipo']);
				$ttipo = $tt[1];
				$qtt = '';
				if ($ttipo=='Quantitativo' && (!empty($unit1) || !empty($unit2)) ) {
					$colname3 = $tbprefix."MONITORAMENTO_".$vv."_traitunit_".$traitid;
					$qtt = ", ADD COLUMN ".$colname3." CHAR(10) DEFAULT ''";
				} elseif ($ttipo=='Quantitativo') {
					$unit2 = $row['TraitUnit'];
				}
			} 
			//echo "date1:".$date1."   date2:".$date2."<br>";
			if ((empty($date1) || $date1=='') && (empty($date2) || $date2=='')) {
				$thereisnodate = 1;
			} else {
				$thereisnodate =0 ; 
			}
			if ($traitid>0 && $thereisnodate==0) {
				$colname = $tbprefix."MONITORAMENTO_".$vv."_traitvar_".$traitid;
				$colname2 = $tbprefix."MONITORAMENTO_".$vv."_dataobs_".$traitid;
				if (!empty($bibref)) {
					$colname4 = $tbprefix."MONITORAMENTO_".$vv."_bibkey_".$traitid;
				}
				$qn = "SELECT LENGTH(".$vv.") AS TEXTFieldSize FROM ".$tbname." ORDER BY LENGTH(".$vv.")  DESC LIMIT 0,1";
				$qnz = mysql_query($qn,$conn);
				$qnw = mysql_fetch_assoc($qnz);
				$charlen = $qnw['TEXTFieldSize']+50;
				if ($charlen<255) {
					$tpvar = "CHAR(".$charlen.")";
				} else {
					if ($charlen<10000) {
						$tpvar = "VARCHAR(".$charlen.")";
					} else {
						$tpvar = "TEXT";
					}
				} 
				$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$colname." ".$tpvar." DEFAULT '', ADD COLUMN ".$colname2." DATE DEFAULT NULL".$qtt;
				@mysql_query($qq,$conn);
				if (!empty($bibref)) {
					$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$colname4." INT(10) DEFAULT NULL";
					//echo $qq."<br>";
					@mysql_query($qq,$conn);
				} 
				if ($ttipo=='Categoria') {
						//faz o update da coluna, se tiver ok entao finaliza, se deu erro pergunte para corrigir os dados
						$qq = "UPDATE `".$tbname."` SET `".$colname."`=checkcategories(".$vv.",".$traitid.")";
						if (empty($date1)) {
							$qq .= ", `".$colname2."`='".$date2."'";
						} else {
							$qq .= ", `".$colname2."`=`".$date1."`";
						}
						if (!empty($bibref)) {
							$qq .= ", `".$colname4."`=`".$bibref."`";
						}
						$qq .= " WHERE `".$vv."`<>'' AND `".$vv."` IS NOT NULL AND (`".$colname."` LIKE 'ERRO' OR `".$colname."` LIKE '')";
						mysql_query($qq,$conn);
						$qq = "SELECT DISTINCT `".$vv."` FROM `".$tbname."`  WHERE `".$vv."`<>'' AND `".$vv."` IS NOT NULL AND (`".$colname."` LIKE 'ERRO' OR `".$colname."` LIKE '')";
						$res = mysql_query($qq,$conn);
						$nres = mysql_numrows($res);
						if ($nres>0) {
if ($nused==0) {
echo "
<br />
<table align='left' class='myformtable' cellpadding='5'>
<thead>
  <tr><td colspan='3'>ATENÇÃO! Sobre as variáveis de monitoramento definidas</td></tr>
  <tr class='subhead'>
    <td>Nome da variável [Caminho]</td>
    <td>Problema encontrado</td>
    <td>O que fazer?</td>
  </tr>
</thead>
<tbody>";
$nused=1;
}
//cadastrar novos estados e atualizar tbname
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr>
  <td>".$tname." [".$tpathname."]</td>
  <td>".$nres." registros tem categorias que não estão cadastradas</td>
  <td align='center'><input id='idx_".$butidx."' type='button' class='bblue' value='Checar e cadastrar' ";
	$myurl ="traitscategoria-popup.php?parentid=".$traitid."&tname=".$tname."&colname=".$colname."&orgcol=".$vv."&tbname=".$tbname."&buttonidx=idx_".$butidx; 
echo " onclick = \"javascript:small_window('$myurl',800,400,'Novas categorias de variável categórica');\" /></td>
</tr>";
						} 
						else {
							//ja completou
							unset($oldmonitorvars[$monikk]);
						}
				}
				if ($ttipo=='Quantitativo') {
					//checa se os valores sao validos e numericos
					$qq = "UPDATE `".$tbname."` SET `".$colname."`=checkquantitativecolumn(`".$vv."`)";
					if (empty($date1)) {
						$qq .= ", `".$colname2."`='".$date2."'";
					} else {
						$qq .= ", `".$colname2."`=`".$date1."`";
					}
					if (empty($unit1)) {
						$qq .= ", `".$colname3."`='".$unit2."'";
					} else {
						$qq .= ", `".$colname3."`='".$unit1."'";
					}
					if (!empty($bibref)) {
							$qq .= ", `".$colname4."`=`".$bibref."`";
					}
					$qq .= "  WHERE `".$vv."`<>'' AND `".$vv."` IS NOT NULL AND (`".$colname."` LIKE 'ERRO' OR `".$colname."` LIKE '')";
					//echo $qq."<br>";
					mysql_query($qq,$conn);
					$qq = "SELECT DISTINCT `".$vv."` FROM `".$tbname."`  WHERE `".$vv."`<>'' AND `".$vv."` IS NOT NULL AND (`".$colname."` LIKE 'ERRO' OR `".$colname."` LIKE '')";
					//echo $qq."<br>";
					$res = mysql_query($qq,$conn);
					
					$nres = mysql_numrows($res);
					if ($nres>0) {
if ($nused==0) {
echo "
<br />
<table align='left' class='myformtable' cellpadding='3'>
<thead>
  <tr><td colspan='3'>ATENÇÃO! Sobre as variáveis de monitoramento definidas</td></tr>
  <tr class='subhead'>
    <td>Nome da variável [Caminho]</td>
    <td>Problema encontrado</td>
    <td>O que fazer?</td>
  </tr>
</thead>
<tbody>";
$nused=1;
}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td>".$tname." [".$tpathname."]</td>
  <td>".$nres." linhas na coluna ".$vv." tem valores não numéricos</td>
  <td align='center'><input idx_".$butidx." type='button' class='bblue' value='Corrigir' ";
$myurl ="traitsquantitativo-popup.php?parentid=".$traitid."&tname=".$tname."&colname=".$colname."&orgcol=".$vv."&tbname=".$tbname."&buttonidx=idx_".$butidx;
echo " onclick = \"javascript:small_window('$myurl',500,350,'Corrigir valores de variável quantitativa');\" /></td>
</tr>";
					} 
					else {
						unset($oldmonitorvars[$monikk]);
					}
				}
				if ($ttipo=='Texto') {
					$qq = "UPDATE `".$tbname."` SET `".$colname."`=".$vv."";
						if (empty($date1)) {
							$qq .= ", `".$colname2."`='".$date2."'";
						} else {
							$qq .= ", `".$colname2."`=`".$date1."`";
						}
						if (!empty($bibref)) {
							$qq .= ", `".$colname4."`=`".$bibref."`";
						}
						$qq .= "  WHERE `".$vv."`<>'' AND `".$vv."` IS NOT NULL";
					//echo $qq."<br />";
					$upr = mysql_query($qq,$conn);
					if ($upr) {
						unset($oldmonitorvars[$monikk]);
						if ($nused==0) {
echo "
<br />
<table align='left' class='myformtable' cellpadding='3'>
<thead>
  <tr><td colspan='2'>ATENÇÃO! Sobre as variáveis de monitoramento definidas</td></tr>
  <tr class='subhead'>
    <td>Nome da variável [Caminho]</td>
    <td>Problema encontrado</td>
    <td>O que fazer?</td>
  </tr>
</thead>
<tbody>";
$nused=1;
}
					}
				}
			} elseif ($traitid>0) {
echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr class='tdsmallbold' ><td align='center'>Não foi informada a data para a coluna $vv</td></tr>
</table><br />";
			}
			$butidx++;
		}
		if ($nused>0) {
			if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
			if (count($oldmonitorvars)>0) {
    echo "<form action='import-data-step10.php' method='post'>";
    unset($_POST['varmonitors']);
foreach ($_POST as $kfs => $vfs) {
	if (!empty($vfs)) {
		echo "
  <input type='hidden' name='".$kfs."' value='".$vfs."' />"; 
		}
	}
      echo "<input name='varmonitors' value='".serialize($oldmonitorvars)."' type='hidden' />";
echo "
<input name='tbname' value='".$tbname."' type='hidden' >
  <tr bgcolor = '".$bgcolor."'><td align='center' colspan='3'><input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td></tr>
</form>";
			} else {
				echo "
<form name='lastform' action='import-data-step10.php' method='post'>";
    unset($_POST['varmonitors']);
foreach ($_POST as $kfs => $vfs) {
		if (!empty($vfs)) {
		echo "
  <input type='hidden' name='".$kfs."' value='".$vfs."' />"; 
		}
	}
echo "
  <input name='var_moni_ok' value='1' type='hidden' />
  <script language=\"JavaScript\">setTimeout('document.lastform.submit()',0.0001);</script>
</form>";
			}
echo "
</tbody>
</table>";
		}
		$monitorvars = $oldmonitorvars;
	}
}

//se variaveis de monitoramento nao foram indicadas, entao solicita
if (count($monitorvars)>0 && $nused==0 && !isset($var_moni_ok)) {
echo "
<br /><table cellpadding='5' class='myformtable' align='left'>
<thead>
<tr><td colspan='8'>Definir as variáveis de monitoramento</td></tr>
<tr class='subhead'>
  <td>Coluna</td>
  <td>Valor&nbsp;mínimo</td>
  <td>Valor&nbsp;máximo</td>
  <td>É&nbsp;uma&nbsp;variável&nbsp;cadastrada?</td>
  <td>Cadastre&nbsp;como&nbsp;nova&nbsp;</td>
  <td>Data&nbsp;da&nbsp;medição</td>
  <td>Bibref&nbsp;da&nbsp;medição</td>
  <td>Unidade&nbsp;de&nbsp;medida&nbsp;<img height=13 src='icons/icon_question.gif' ";
	$help = " Se não informado para variáveis quantitativas o programa assume a unidade de medida padrão da variável";
	echo " onclick=\"javascript:alert('$help');\" /></td>
  </tr>
</thead>
<tbody>
<form name='monitorform' action='import-data-step10.php' method='post'>";
foreach ($fsdefs as $kfs => $vfs) {
	if (!empty($vfs)) {
		echo "
  <input type='hidden' name='".$kfs."' value='".$vfs."' />"; 
		}
	}
echo "
  <input name='varmonitors' value='".serialize($monitorvars)."' type='hidden' />";
			foreach ($monitorvars as $moni => $fieldname) {
				$qq = "SELECT `".$fieldname."` FROM `".$tbname."` PROCEDURE ANALYSE ()";
				$rq = mysql_query($qq,$conn);
				$rw = mysql_fetch_assoc($rq);
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>".$fieldname."</td>
  <td align='center' >".$rw['Min_value']."</td>
  <td align='center'>".$rw['Max_value']."</td>
  <td>
    <table>
      <tr>
        <td>"; autosuggestfieldvalwithunit("search-traits-only.php","trname_".$moni,"","traitres_".$moni,"moni_vars_".$moni,true,"varunit_".$moni);
echo "<td>
      </tr>
      <tr><td class='tdformnotes'>*&nbsp;autosugere&nbsp;selecione&nbsp;da&nbsp;lista</td></tr>
     </table>
  </td>";

//cadastre uma nova variavel
echo "
  <td align='center'>
    <table>
      <tr>
        <td><input type='hidden' name='monivarnovo_".$moni."' id='monivarnovo_".$moni."' value=''><input type='text' id='monitornome_".$moni."' value='' size='30' readonly /></td>
        <td><img style='cursor:pointer;' src='icons/list-add.png' height=15 ";
$myurl ="traitsvar-exec.php?ispopup=1&traitid=&traitkind=Variavel&traitname_val=monitornome_".$moni."&traitid_val=monivarnovo_".$moni;
echo " onclick = \"javascript:small_window('".$myurl."',700,400,'Nova variável');\"></td>
      </tr>
    </table>
  </td>
  <td>
    <table >
      <tr>";
		$data_moni = array_keys($fields,"DATA_MONI");
	echo "
        <td>
          <select name='datamonitorfields_".$moni."'>
            <option selected value=''>Colunas com datas</option>";
			foreach ($data_moni as $fd) {
				echo "
            <option value='".$fd."'>".$fd."</option>";
  			}
	echo "
          </select>
        </td>
        <td class='tdformnotes'>ou&nbsp;então</td>
        <td style='border: 0px'>";
		$ff = "dataobs_".$fieldname;
	echo "<input name=\"dataobs_".$moni."\" value=\"".$$ff."\" size=\"11\" readonly /></td>
        <td style='border: 0px'>
          <a onclick=\"if(self.gfPop)gfPop.fPopCalendar(document.forms['monitorform'].dataobs_".$moni.");return false;\" >
          <img name=\"popcal\" align=\"absmiddle\" src=\"calendar/calbtn.gif\" width=\"34\" height=\"22\" border=\"0\" alt=\"\"></a>
        </td>
      </tr>
    </table>
  </td>
  <td>
    <table>
      <tr>";
	$unit_monit = array_keys($fields, "UNIT_VAR");
	if (!empty($unit_monit)) {
	echo "
        <td>
          <select name='varunitmonitorfields_".$moni."'>
            <option selected value=''>Colunas com unidades de medidas</option>";
			foreach ($data_moni as $fd) {
				echo "
            <option value='".$fd."'>".$fd."</option>";
  			}
	echo "
          </select>
        </td>
        <td class='tdformnotes'>ou&nbsp;então</td>";
	}
echo "
        <td><input id='varunit_".$moni."' type='text' name=\"varunit_".$moni."\" value='' size=\3\" /></td>
      </tr>
    </table>
  </td>
  <td>
    <table >
      <tr>";
		$bib_moni = array_keys($fields,"BIBKEY");
		if (!empty($unit_monit)) {
	echo "
        <td>
          <select name='bibkeymonitorfields_".$moni."'>
            <option selected value=''>Colunas com bibkey</option>";
			foreach ($bib_moni as $fd) {
				echo "
            <option value='".$fd."'>".$fd."</option>";
  			}
	echo "
          </select>
        </td>";
    } else {
 	echo "
        <td>Ausente</tb>";     
    }
echo "
      </tr>
    </table>
  </td>
</tr>";
			}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'><td align='center' colspan='8'><input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' /></td></tr>
</form>
</tbody>
</table>";
		} elseif (count($monitorvars)==0) { 
			$var_moni_ok=1;
		}
} else { 
		$var_moni_ok=1;
}

if ($var_moni_ok==1) {
	$steps = unserialize($_SESSION['importacaostep']);
	unset($steps[0]);
	$stt = array_values($steps);
	$_SESSION['importacaostep'] = serialize($stt);
echo "
  <form name='myform' action='import-data-hub.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />      
    <script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
  </form>";

}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=TRUE,$footer=$menu);
?>