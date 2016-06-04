<?php
//este script definir as variaveis estaticas, checando por compatibilidade
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
$title = 'Importar Dados Passo 11';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);


$fsdefs = unserialize($_SESSION['firstdefinitions']);
$fields = unserialize($_SESSION['fieldsign']);

if (in_array('ESTA_VAR',$fields) && !isset($var_estatic_ok)) {
	$nused=0;
	if (!isset($varestatic)) {
		$estaticvars = array_keys($fields, "ESTA_VAR");
	} 
	else {
		//faz o update das variaveis definidas e checa por estados se for o caso
		$estaticvars = unserialize($varestatic);
		if (count($estaticvars)>0) {
		$oldestaticvars = $estaticvars;
		$idx =1;
		foreach ($estaticvars as $monikk => $vv) {
			$kk = "estatic_vars_".$monikk;
			$traitid = $_POST[$kk]+0;
			$kk = "estaticnovo_".$monikk;
			$ttid = $_POST[$kk]+0;
			$kk = "varunitestaticfields_".$monikk;
			$unit1 = $_POST[$kk];
			$kk = "varunitess_".$monikk;
			$unit2 = TRIM($_POST[$kk]);
			
			//pega referencia bibliográfica
			$kk = "bibkeystaticfields_".$monikk;
			if (!empty($_POST[$kk])) {
				$bibref = $tbprefix.$_POST[$kk];
			} 
			
			if ($ttid>0 && $traitid==0) { $traitid=$ttid;}
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
					$colname2 = $tbprefix."ESTATIC_".$vv."_traitunit_".$traitid;
					$qtt = ", ADD COLUMN ".$colname2." CHAR(10) DEFAULT ''";
				} elseif ($ttipo=='Quantitativo') {
					$unit2 = $row['TraitUnit'];
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

				$colname = $tbprefix."ESTATIC_".$vv."_traitvar_".$traitid;

				$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$colname." ".$tpvar." DEFAULT ''".$qtt;
				@mysql_query($qq,$conn);
				if (!empty($bibref)) {
					$colname4 = $tbprefix."ESTATIC_".$vv."_bibkey_".$traitid;
					$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$colname4." INT(10) DEFAULT NULL";
					@mysql_query($qq,$conn);
				} 
				$qq = "SELECT * FROM Traits WHERE TraitID='".$traitid."'";
				$rr = mysql_query($qq,$conn);
				$row = mysql_fetch_assoc($rr);
				$tname = $row['TraitName'];
				$tpathname = $row['PathName'];
				$tt = explode("|",$row['TraitTipo']);
				$ttipo = $tt[1];
				if ($ttipo=='Categoria') {
						//faz o update da coluna, se tiver ok entao finaliza, se deu erro pergunte para corrigir os dados
						$qq = "UPDATE `".$tbname."` SET `".$colname."`=checkcategories(".$vv.",".$traitid.")";
						if (!empty($bibref)) {
							$qq .= ", `".$colname4."`=`".$bibref."`";
						}
						$qq .= "WHERE `".$vv."`<>'' AND (`".$vv."` IS NOT NULL) AND (`".$colname."` LIKE 'ERRO' OR `".$colname."` LIKE '')";
						mysql_query($qq,$conn);
						$qq = "SELECT DISTINCT `".$vv."` FROM `".$tbname."`  WHERE `".$vv."`<>'' AND `".$vv."` IS NOT NULL AND (`".$colname."`='ERRO' OR `".$colname."`='')";
						$res = mysql_query($qq,$conn);
						$nres = @mysql_numrows($res);
						//echo $qq."<br />";
						if ($nres>0) {
if ($nused==0) {
echo "<br />
<table align='center' class='myformtable' cellpadding='3'>
<thead>
  <tr><td colspan='3'>ATENÇÃO! Sobre as variáveis estáticas definidas</td></tr>
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
  <td align='center'><input id='idx_".$idx."' type='button' class='bblue' value='Checar e cadastrar' ";
	$myurl ="traitscategoria-popup.php?parentid=".$traitid."&tname=".$tname."&colname=".$colname."&orgcol=".$vv."&tbname=".$tbname."&buttonidx=idx_".$idx; 
echo " onclick = \"javascript:small_window('$myurl',800,400,'Novas categorias de variável categórica');\" /></td>
</tr>";
						} else {
							//ja completou
							unset($oldestaticvars[$monikk]);
						}
				}
				if ($ttipo=='Quantitativo') {

					//checa se os valores sao validos e numericos
					$qq = "UPDATE `".$tbname."` SET `".$colname."`=checkquantitativecolumn(`".$vv."`)";
						if (empty($unit1)) {
							$qq .= ", `".$colname2."`='".$unit2."'";
						} else {
							$qq .= ", `".$colname2."`='".$unit1."'";
						}
						if (!empty($bibref)) {
							$qq .= ", `".$colname4."`=`".$bibref."`";
						}
					$qq .= "  WHERE `".$vv."`<>'' AND `".$vv."` IS NOT NULL AND (`".$colname."` LIKE 'ERRO' OR `".$colname."` LIKE '')";
					mysql_query($qq,$conn);
					$qq = "SELECT DISTINCT `".$vv."` FROM `".$tbname."`  WHERE `".$vv."`<>'' AND `".$vv."` IS NOT NULL AND (`".$colname."`='ERRO' OR `".$colname."`='')";
					$res = mysql_query($qq,$conn);
					$nres = @mysql_numrows($res);
					//echo $qq."<br />";
					if ($nres>0) {
if ($nused==0) {
echo "<br />
<table align='center' class='myformtable' cellpadding='3'>
<thead>
  <tr><td colspan='3'>ATENÇÃO! Sobre as variáveis estáticas definidas</td></tr>
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
  <td align='center'><input id='idx_".$idx."' type='button' class='bblue' value='Corrigir' ";
	$myurl ="traitsquantitativo-popup.php?parentid=".$traitid."&tname=".$tname."&colname=".$colname."&orgcol=".$vv."&tbname=".$tbname."&buttonidx=idx_".$idx; 
echo " onclick = \"javascript:small_window('$myurl',500,350,'Corrigir valores de variável quantitativa');\" /></td>
</tr>";
					} else {
						unset($oldestaticvars[$monikk]);
					}
				}
				if ($ttipo=='Texto') {
					$qq = "UPDATE `".$tbname."` SET `".$colname."`=".$vv;
					if (!empty($bibref)) {
							$qq .= ", `".$colname4."`=`".$bibref."`";
					}
					$qq .= "WHERE `".$vv."`<>'' AND `".$vv."` IS NOT NULL";
					$upr = mysql_query($qq,$conn);
					//echo $qq."<br />";
					if ($upr) {
						unset($oldestaticvars[$monikk]);
						//$nused=1;
					}
				}
			} 
			$idx++;
		}
		if ($nused>0) {
			if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
			if (count($oldestaticvars)>0) {
echo "
<form action='import-data-step11.php' method='post'>";
unset($_POST['varestatic']);
foreach ($_POST as $kfs => $vfs) {
	if (!empty($vfs)) {
		echo "
  <input type='hidden' name='".$kfs."' value='".$vfs."' />"; 
		}
	}
echo "
  <input name='varestatic' value='".serialize($oldestaticvars)."' type='hidden' />
  <tr bgcolor = '".$bgcolor."'><td align='center' colspan='3'><input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td></tr>
</form>";
			} else {
				echo "
<form action='import-data-step11.php' method='post'>";
unset($_POST['varestatic']);
foreach ($_POST as $kfs => $vfs) {
	if (!empty($vfs)) {
		echo "
  <input type='hidden' name='".$kfs."' value='".$vfs."' />"; 
		}
	}  
echo "
  <input name='var_estatic_ok' value='1' type='hidden' />
<tr bgcolor = '".$bgcolor."'><td align='center' colspan='3'><input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td></tr>
</form>";
			}
echo "
</tbody>
</table>";
		}
		$estaticvars = $oldestaticvars;
	}
}

//se variaveis nao foram indicadas, entao solicita
if (count($estaticvars)>0 && $nused==0 && !isset($var_estatic_ok)) {
echo "
<br />
<table cellpadding='5' class='myformtable' align='center'>
<thead>
<tr><td colspan='7'>Definir as variáveis estáticas</td></tr>
<tr class='subhead'>
  <td>Coluna</td>
  <td>Valor&nbsp;mínimo</td>
  <td>Valor&nbsp;máximo</td>
  <td>É&nbsp;uma&nbsp;variável&nbsp;cadastrada?</td>
  <td>Cadastre&nbsp;como&nbsp;nova&nbsp;</td>
  <td>Unidade&nbsp;de&nbsp;medida&nbsp;<img height=13 src='icons/icon_question.gif' ";
	$help = " Se não informado para variáveis quantitativas o programa assume a unidade de medida padrão da variável";
	echo " onclick=\"javascript:alert('$help');\" /></td>
  <td>Bibref&nbsp;da&nbsp;medição</td>
</tr>
</thead>
<tbody>
<form name='monitorform' action='import-data-step11.php' method='post'>
  <input name='varestatic' value='".serialize($estaticvars)."' type='hidden' />
  <input name='tbname' value='".$tbname."' type='hidden' />";
foreach ($fsdefs as $kfs => $vfs) {
	if (!empty($vfs)) {
		echo "
  <input type='hidden' name='".$kfs."' value='".$vfs."' />"; 
		}
	}
foreach ($estaticvars as $moni => $fieldname) {
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
        <td>"; autosuggestfieldvalwithunit("search-traits-only.php","trname_".$moni,"","traitres_".$moni,"estatic_vars_".$moni,true,"varunitess_".$moni);
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
        <td><input type='hidden' name='estaticnovo_".$moni."' id='estaticnovo_".$moni."' value='' /><input type='text' id='estaticnome_".$moni."' value='' size='20' readonly /></td>
        <td><img style='cursor:pointer;' src='icons/list-add.png' height='15' ";
		$myurl ="traitsvar-exec.php?ispopup=1&traitid=&traitkind=Variavel&traitname_val=estaticnome_".$moni."&traitid_val=estaticnovo_".$moni;
echo " onclick = \"javascript:small_window('".$myurl."',600,350,'Nova variável');\"></td>
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
          <select name='varunitestaticfields_".$moni."'>
            <option selected value=''>Colunas com unidades de medida</option>";
			foreach ($unit_monit as $fd) {
				echo "
            <option value='".$fd."'>".$fd."</option>";
			}
	echo "
          </select>
        </td>
        <td class='tdformnotes'>ou&nbsp;então</td>";
	}
echo "
        <td><input id='varunitess_".$moni."' type='text' name=\"varunitess_".$moni."\" value='' size=\3\" /></td>
 <td>
    <table >
      <tr>";
		$bib_moni = array_keys($fields,"BIBKEY");
		if (!empty($bib_moni)) {
	echo "
        <td>
          <select name='bibkeystaticfields_".$moni."'>
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
      </tr>
    </table>
  </td>
</tr>";
			}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
  <tr bgcolor = '".$bgcolor."'><td align='center' colspan='7'><input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' /></td></tr>
</form>";
echo "
</tbody>
</table>";
		} elseif (count($estaticvars)==0) { 
			$var_estatic_ok=1;
		}
} else { 
		$var_estatic_ok=1;
}

if ($var_estatic_ok==1) {
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
$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>