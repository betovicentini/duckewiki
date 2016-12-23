<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
include "functions/ImportData.php";

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
} 

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;
$gget = cleangetpost($_GET,$conn);
@extract($gget);

//CABECALHO
$ispopup=1;
$menu = FALSE;
$title = '';
$which_css = array(
"<link rel='stylesheet' type='text/css' href='css/geral.css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' />"
);
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>"
);
$title = 'Família';
$body= '';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "<script type='text/javascript' src='javascript/wz_tooltip.js'></script>";


$qu = "ALTER TABLE `Tax_Familias`  ADD `IpniID` INT(10) NULL AFTER `Used`";
@mysql_query($qu,$conn);
$qu = "ALTER TABLE `ChangeTax_Familias`  ADD `IpniID` INT(10) NULL AFTER `Used`";
@mysql_query($qu,$conn);

$qu = "ALTER TABLE `Tax_Familias`  ADD `MycobankID` INT(10) NULL AFTER `IpniID`";
@mysql_query($qu,$conn);
$qu = "ALTER TABLE `ChangeTax_Familias`  ADD `MycobankID` INT(10) NULL AFTER `IpniID`";
@mysql_query($qu,$conn);


//echopre($ppost);
//SELECIONA VALORES ANTIGOS SE FOR O CASO
if ($famid>0 && $final!=1) {
	$qq = "SELECT * FROM Tax_Familias WHERE FamiliaID='".$famid."'";
	$query = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($query);
	$famid = $row['FamiliaID'];
	$genusid = $row['GeneroID'];
	$genus = $row['Genero'];
	$nomevalido = $row['Valid'];
	$spnome = $row['Familia'];
	$ipniid = $row['IpniID'];
	$mycobankid = $row['MycobankID'];
	$autor = $row['FamiliaAutor'];
	$qq = "SELECT *  FROM Vernacular WHERE TaxonomyIDS LIKE '%familia|".$famid.";%' OR `TaxonomyIDS` LIKE '%familia|".$famid."'";
	$rrr = mysql_query($qq,$conn);
	$nrr = mysql_numrows($rrr);
	if ($nrr>0) {
		while ($rrw = mysql_fetch_assoc($rrr)) {	
			if (empty($vernacularvalue)) {$vernacularvalue = $rrw['VernacularID'];} else {
				$vernacularvalue = $vernacularvalue.";".$rrw['VernacularID'];
			}
			
		}
	}	
	$basionym = $row['Basionym'];
	$basionymautor = $row['BasionymAutor'];
	$pubrevista = $row['PubRevista'];
	$pubvolume = $row['PubVolume'];
	$pubano = $row['PubAno'];
	$sinonimos = $row['Sinonimos'];
	$geodist = $row['GeoDistribution'];
	$notas = $row['Notas'];
	
	$specieslist = describetaxacomposition($sinonimos,$conn,$includeheadings=TRUE);

	if (!empty($vernacularvalue)) {
		$vernaculartxt = describevernacular($vernacularvalue,$conn);
	}
	$ttid = GetLangVar('nameeditar')."&nbsp;".mb_strtolower(GetLangVar('namefamily'))." &nbsp;<i>$spnome</i>";
} 
else {
	$ttid = GetLangVar('namenova')."&nbsp;".mb_strtolower(GetLangVar('namefamily'));
}
$erro=0;
if ($final==1) {
	//CHECA POR CAMPOS OBRIGATORIOS
	if (empty($spnome) || empty($nomevalido)) {
		echo "
<br/>
<table cellpadding=\"1\" width='50%' align='left' class='erro'>
<tr class='tdsmallbold' ><td align='center'>".GetLangVar('erro1')."</td></tr>";
	if (empty($spnome)) {
		echo "
<tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('namenome')."</td></tr>";
			}
			if (empty($nomevalido)) {
				echo "
<tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('namevalid')."</td></tr>";
	}
			echo " 
</table>
<br />";
		$erro++;
	} 
	//FAZ O CADASTRO SE NAO HÁ ERRO
	if ($erro==0) {
		$fieldsaskeyofvaluearray = array(
			'Familia' => $spnome,
			'FamiliaAutor' => $autor,
			'Sinonimos' => $sinonimos,
			'Notas' => $notas,
			'Valid' => $nomevalido,
			'IpniID'  => $ipniid,
			'MycobankID' => $mycobankid
			);
		
		//VALORES ANTIGOS SE EDITANDO
		if ($famid>0) {
			$qq = "SELECT Familia,FamiliaAutor,Sinonimos,Notas,Valid FROM Tax_Familias WHERE FamiliaID='".$famid."'";
			$qu = mysql_query($qq,$conn);
			$old = mysql_fetch_assoc($qu);
			//O VELHO É DIFERENTE DO NOVO
			$haschanged = 0;
			foreach ($old as $kk => $vv) {
				if ($vv!=$fieldsaskeyofvaluearray[$kk] || (empty($vv) && !empty($fieldsaskeyofvaluearray[$kk]))) {
					$haschanged++;
				}
			}
			//SE ESTIVER INVALIDANDO O NOME CHECA SE O NOME NAO ESTA SENDO USADO
			$hasdet = 0;
			//echopre($old);
			if ($old['Valid']!=$nomevalido && $nomevalido==0) {
				$qq = "SELECT * FROM Identidade WHERE FamiliaID='".$famid."'";
				$det = mysql_query($qq,$conn);
				$hasdet = mysql_numrows($det);
			}
			//SE NAO ESTIVER INVALIDANDO E FEZ MODIFICACOES, ENTAO CADASTRA O NOVO REGISTRO
			if ($hasdet==0 && $haschanged>0) {	
				    CreateorUpdateTableofChanges($famid,'FamiliaID','Tax_Familias',$conn);
					$newrec = UpdateTable($famid,$fieldsaskeyofvaluearray,'FamiliaID','Tax_Familias',$conn);
					if (!$newrec) {
						$erro++;
					} else {
						$verupdate = updatevernacular($vernacularvalue,'familia',$famid,$conn);	
					}
			} elseif ($hasdet>0) {
				$invalidationfailed++;
				$erro++;
				echo "
<br />
<table cellpadding=\"1\" width='50%' align='left' class='erro'>
  <tr><td class='tdsmallbold' align='center'>Não pode invalidar esse nome porque ele está sendo usado!</td></tr>
</table>
<br />";
			}
		} 
		else { //insert new family
			$qq = "SELECT FamiliaID,Familia,FamiliaAutor,Sinonimos,Notas FROM Tax_Familias WHERE LOWER(Familia) LIKE '".mb_strtolower($spnome)."'";
			$qu = mysql_query($qq,$conn);
			$nold = mysql_numrows($qu);
			if ($nold>0) {
				$erro++;
				echo "
<br />
<table cellpadding=\"1\" width='50%' align='left' class='erro'>
  <tr><td class='tdsmallbold' align='center'>Já existe uma família com esse nome!</td></tr>
</table>
<br />";
				if (!isset($naoeimportacao) || $naoeimportacao==0) {
					$rur = mysql_fetch_assoc($qu);
					$famid = $rur['FamiliaID'];
					$erro=0;
				} else {
					$erro++;
				}
				
			} else {
				$newrec = InsertIntoTable($fieldsaskeyofvaluearray,'FamiliaID','Tax_Familias',$conn);
				if (!$newrec) {
					echo "
<br />
<table cellpadding=\"1\" width='50%' align='left' class='erro'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('erro2')."</td></tr>
</table>
<br />";
				$erro++;
				} 
			}
		}
	}
	//ATUALIZA TABELA TEMPORARIA
	if ($erro==0 && $newrec>0) {
		TaxonomySimpleInsert($newrec,"famid",$conn);
	}
	if ($erro==0 && (!isset($naoeimportacao) || $naoeimportacao==0)) {
		if ($newrec>0 && !$famid>0) {
			$famid = $newrec;
		}
		//close popup and send value
		echo "
<form >
<input type='hidden' id='famiid' value='".$famid."' />
<script language=\"JavaScript\">
setTimeout(
  function() {
    passnewidandtxtoselectfield('".$familiafieldid."','famiid','".$spnome."','');
    }
    ,2000);
</script>
</form>";
	}
	if ($erro==0 && $naoeimportacao==1) {
			echo "
<br />
<table cellpadding=\"1\" width='50%' align='left' class='success'>
  <tr><td class='tdsmallbold' align='center'>O cadastro foi feito com sucesso</td></tr>
</table>
<br />
<form >
  <script language=\"JavaScript\">
    setTimeout(function() {this.window.close();},2000);
  </script>
</form>
";
	}
} 

if ($erro>0 || $final!='1') {
	if (!isset($ipnichecked) && (!isset($famid) || ($famid+0)==0) && empty($spnome)) {
		echo "
<br />
<form name='ipniform' action=familia-popup.php method='post'>
  <input type='hidden' name='spnome' value='".$spnome."' />
  <input type='hidden' name='familiafieldid' value='".$familiafieldid."' />
  <input type='hidden' name='naoeimportacao' value='".$naoeimportacao."' />
<table class='myformtable' align='left' cellpadding='5'>
<thead>
  <tr><td colspan=2>".$ttid."</td></tr>
  <tr class='subhead'><td colspan=2>Checar dados da familia ".$spnome."?</td></tr>
</thead>
<tbody>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor ='".$bgcolor."'>
  <td class='tdsmallbold' style='color:#990000' align='right'>".GetLangVar('namenome')."*</td>
  <td>
    <table>
      <tr>
        <td><input name='spnome' type='text' value='$spnome' size='30' class='selectedval' /></td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan=2>
    <table align='center' >
    <tr>
      <input type='hidden' name='ipnichecked' value='' />
      <!---<td align='center' >
        <input type='submit' value='Checar IPNI' class='bsubmit' onclick=\"javascript:document.ipniform.ipnichecked.value=1\" />
      </td>
      --->
      <td align='left'>
        <input type='submit' value='".GetLangVar('namecontinuar')." sem checar' class='bsubmit' />
      </td>
      <td align='center' >
        <input type='submit' value='Checar em Mycobank.org' class='bblue' onclick=\"javascript:document.ipniform.ipnichecked.value=3\" />
      </td>
    </tr>
  </table>
  </td>
</tr>
</tbody>
</table>
</form>
";
} else {
	if ($ipnichecked==3 && !empty($spnome)) {
			$resultado = getmycobankdata("","",$spnome,"");
			//echopre($resultado);
			if (is_array($resultado)) {
				extract($resultado["dados"]);
				$txt = $resultado["resposta"];
			}  else {
				$txt = $resultado;
			}
			if ($erro==0 && $naoeimportacao==1) {
			echo "
<br />
<span style='background-color: yellow; color: red; font-size: 1.1em; padding: 5px; align: left;'  >".$txt."</span>
<br />
";
			}
	}
echo "<br />
<form name=specieslistform action=familia-popup.php method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input type='hidden' name='famid' value='".$famid."' />
  <input type='hidden' name='final' value='1' />
  <input type='hidden' name='ipniid' value='".$ipniid."'  />
  <input type='hidden' name='familiafieldid' value='".$familiafieldid."' />
  <input type='hidden' name='naoeimportacao' value='".$naoeimportacao."' />
  <table class='myformtable' align='left' cellpadding='5'>
  <thead>
    <tr><td colspan=2>".$ttid."</td></tr>
  </thead>
  <tbody>";
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor ='".$bgcolor."'>
  <td class='tdsmallbold' style='color:#990000' align='right'>".GetLangVar('namenome')."*</td>
  <td>
    <table>
      <tr>
        <td><input name='spnome' type='text' value='$spnome' size='30' class='selectedval' /></td>
        <td class='tdsmallbold' align='right'>".GetLangVar('nameautor')."</td>";
		 //$name = $genero." ".$especie." ".$spnome;
		 $name = $spnome;
		echo "
          <td><input type='text' name='autor' value='".$autor."' size='15' /></td>
          <td><img style='cursor:pointer;' src='icons/mobot.png' height='18' onclick=\"javascript:small_window('http://www.tropicos.org/NameSearch.aspx?name=".$name."',800,600,'Tropicos');\" onmouseover=\"Tip('Ver registro do nome ".$name." em tropicos.org');\" /></td>
      </tr>";
      if ($mycobankid>0) {
      echo "
      <tr><td colspan=2>
      <input type='hidden' name='mycobankid' value='".$mycobankid."'  />Mycobank No. ".$mycobankid."&nbsp;<img style='cursor:pointer;' src='icons/mycobank.png' height='18' onclick=\"javascript:alert('ainda não implementado');\" onmouseover=\"Tip('Ver registro do nome ".$name." em mycobank.org');\" /></td></tr>";
      }
echo "      
    </table>
  </td>
</tr>";


  

	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor ='".$bgcolor."'>
  <td class='tdsmallbold' style='color:#990000' align='right'>".GetLangVar('messageestenomee')."*</td>
  <td class='tdformnotes'>
    <table>";
	if ($nomevalido==1 || !isset($nomevalido)) { $ch = "checked";} else { $ch ='';}
	if ($nomevalido==0 && isset($nomevalido)) { $ch2 =  "checked";} else { $ch2 ='';}
	echo "
      <tr>
        <td align='right'><input type='radio' name='nomevalido' $ch value='1' /></td><td>".mb_strtolower(GetLangVar('namevalido')) ."</td>
        <td align='right'><input type='radio' name='nomevalido' $ch2 value='0' /></td><td>".mb_strtolower(GetLangVar('nameinvalido'))."</td>
      </tr>
    </table>
  </td>
</tr>";
  if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
  echo "
<tr bgcolor ='".$bgcolor."'>
  <td class='tdsmallbold' align='right'>".GetLangVar('namesinonimos')."</td>
  <td>
    <table>
    <tr>
      <input type='hidden' id='specieslistids' name='sinonimos' value='$sinonimos' />";
		if (empty($specieslist)) {
			echo "
        <td><textarea rows=2 cols=50 id='specieslist' name='specieslist' readonly>$specieslist</textarea></td>";
		} else {
			echo "
        <input type='hidden' id='specieslist' name='specieslist' value='$specieslist' />
        <td class='tdsmalldescription'>$specieslist</td>";
		}
		echo "
        <td>
          <input type='button' value='<<' class='bsubmit' ";
		$myurl ="selectspeciespopup.php?formname=specieslistform&elementname=specieslistids&destlistlist=".$sinonimos;
		echo " onclick = \"javascript:small_window('$myurl',500,400,'SelectSpecies');\" />
        </td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor ='".$bgcolor."'>
  <td class='tdsmallbold' align='right'>".GetLangVar('namegeodistribution')."</td>
  <td>
    <table>
      <tr><td><textarea name='geodist' cols=50 rows=2 >$geodist</textarea></td></tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor ='".$bgcolor."'>
  <input type='hidden' id='vernacularvalue' name='vernacularvalue' value='$vernacularvalue' />
  <td class='tdsmallboldright'>".GetLangVar('namevernacular')."</td>
  <td >
    <table>
      <tr>";
	if (empty($vernaculartxt) || !isset($vernaculartxt)) {
		echo "
        <td class='tdformnotes' >
          <textarea rows=1 cols=50% id='vernaculartxt' name='vernaculartxt' readonly></textarea>";
	} else {
		echo "
        <td class='tdformnotes' >
          <textarea rows=1 cols=50% id='vernaculartxt' name='vernaculartxt' readonly>".$vernaculartxt."</textarea>";
	}
	if (!isset($vernacularvalue)) { $vernacularvalue="";}
	echo "
        </td>
        <td><input type='button' value=\"+\" class='bsubmit' ";
$myurl="vernacular_selector.php?formname=specieslistform&amp;tempelement=vernaculartxt&amp;elementname=vernacularvalue&amp;getvernacularids=".$vernacularvalue;     
echo " onclick = \"javascript:small_window('".$myurl."',350,280,'Vernaculares');\" />
        </td>
      </tr>
    </table>
  </td>
</tr>"; 
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor ='".$bgcolor."'>
  <td class='tdsmallbold' align='right'>".GetLangVar('nameobs')."</td>
  <td>
    <table>
      <tr><td><textarea name='notas' cols=50 rows=3>$notas</textarea></td></tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor ='".$bgcolor."'>
  <td colspan=2>
    <table align='center'>
      <tr>
        <td align='center'><input type = 'submit' class='bsubmit' value='".GetLangVar('nameenviar')."' /></td>
      </tr>
    </table>
  </td>
</tr>
</tbody>
</table>
</form>
";
}
}


$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>