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
$ispopup=1;
$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='javascript/tabber/tabber.css' >"
);



$which_java = array(
"<script type='text/javascript' src='javascript/jquery-latest.js'></script>",
"<script type='text/javascript'> $(document).ready(function(){ $('.toggle_container').hide(); $('h2.trigger').click(function(){ $(this).toggleClass('active').next().slideToggle('slow'); }); });</script>"
);
$title = 'Imprimir Etiquetas';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);


mysql_set_charset('utf8');


if (!empty($especimenesids) && empty($especimenestxt)) {
	$aa = explode(";",$especimenesids);
	$naa = count($aa);
	$especimenestxt = $naa." ".mb_strtolower(GetLangVar('nameregistro'))."s ".mb_strtolower(GetLangVar('nameselecionado'))."s";
}

unset($_SESSION['qq']);
unset($_SESSION['exportnresult']);
unset($_SESSION['etiqueta_sql']);
unset($_SESSION['qqcolumns']);
unset($_SESSION['qqcolumns']);

echo "
<br />
<form method='post' name='finalform' action='label-exec.php'>
<input type='hidden' name='ispopup' value='".$ispopup."' />
<h2 class='trigger'><a href='#' >Tipo de etiqueta: </a></h2>
<div class='toggle_container'>
  <div class='block'>
    <table cellpadding='7'>
      <tr>
        <td align='right'><input type='checkbox' checked value='1' name='spec_label' /></td><td align='left' class='tdsmallbold' style='color: #990000;'>Exsicata</td>
        <td align='right' ><input type='checkbox' value='1' name='mini_label' /></td><td align='left' class='tdsmallbold' style='color: #990000;'>Código de Barras</td>
        <td align='right' ><input type='checkbox' value='1' name='det_label' /></td><td align='left' class='tdsmallbold' style='color: #990000;'>Determinação</td>
      </tr>
    </table>
  </div>
</div>
<h2 class='trigger'><a href='#'>Amostras:</a></h2>
<div class='toggle_container'>
  <div class='block'>
    <table width='99%'>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('namefiltro')."</td>
        <td>
          <select name='filtro'>";
		if (!empty($filtro)) {
			$qq = "SELECT * FROM Filtros WHERE FiltroID='".$filtroid."'";
			$res = @mysql_query($qq,$conn);
			$rr = @mysql_fetch_assoc($res);
			echo "
            <option selected value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
			mysql_free_result($res);
		}
			echo "
            <option selected value=''>".GetLangVar('nameselect')."</option>";
			$qq = "SELECT * FROM Filtros WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ORDER BY FiltroName";
			$res = @mysql_query($qq,$conn);
			while ($rr = @mysql_fetch_assoc($res)) {
				echo "
            <option value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
			}
			mysql_free_result($res);
	echo "
          </select>
        </td>
      </tr>
      <tr>
	    <td class='tdsmallbold'>Para:</td>
        <td class='tdformnotes'>
          <table>
            <tr>
              
              <td align='right' class='tdformnotes' ><input type='radio' name='etitype' checked value='EspecimenesIDS' /></td>
              <td align='left' class='tdformnotes'>".mb_strtolower(GetLangVar('nameamostra'))."s</td>
              <td align='right' class='tdformnotes' ><input type='radio' name='etitype' value='PlantasIDS' /></td>
              <td align='left' class='tdformnotes'>plantas&nbsp;marcadas</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('namecoleta')."s</td>
        <input type='hidden' id='especimenesids' name='especimenesids' value='$especimenesids' />
        <td id='especimenestxt'>$especimenestxt</td>";
		$myurl = "selectespecimene-popup.php?elementid=especimenesids&elementtxtid=especimenestxt";
		if (empty($especimenesids)) {
			$butname = GetLangVar('nameselect');
		} else {
			$butname = GetLangVar('nameeditar');
		} 
		echo "
        <td><input type=button value='$butname' class='bsubmit' onclick = \"javascript:small_window('$myurl',850,400,'Select specimens');\" /></td>
      </tr>
    </table>
  </td>
</tr>
</table>
</div>
</div>";
echo "
<h2 class='trigger'><a href='#'>Opções:</a></h2>
<div class='toggle_container'>
  <div class='block'>
        <table width='99%'>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table align='left'>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('nameformulario')."  ".GetLangVar('namenota')."&nbsp;&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
		$help = strip_tags(GetLangVar('formulariolabel'));
		echo " onclick=\"javascript:alert('$help');\" /></td>
        <td class='tdformnotes'>
          <select name='formid' >";
		if (!empty($formid)) {
			$qq = "SELECT * FROM Formularios WHERE FormID='$formid'";
			$rr = mysql_query($qq,$conn);
			$row= mysql_fetch_assoc($rr);
			echo "
            <option selected value='".$row['FormID']."'>".$row['FormName']." (".$row['AddedDate'].")</option>";
		} else {
			echo "
            <option value=0>".GetLangVar('nameselect')."</option>";
		}
	//formularios usuario
	$qq = "SELECT * FROM Formularios WHERE (AddedBy=".$_SESSION['userid']." OR Shared=1) AND HabitatForm<>1 ORDER BY FormName,Formularios.AddedDate ASC";
	$rr = mysql_query($qq,$conn);
	while ($row= mysql_fetch_assoc($rr)) {
		echo "
            <option value='".$row['FormID']."'>".$row['FormName']."</option>";
	}
	echo "
          </select>
        </td>
      </tr>
      <tr><td class='tdsmallbold'>Usar modelo de descrição&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
		$help = "Neste caso a descrição gerada será feita utilizando o modelo descritivo do formulário. O formulário precisa ter um modelo para usar esta opção";
		echo " onclick=\"javascript:alert('$help');\" /></td><td><input type='checkbox' value='1' name='usarmodelo' /></td>
	</tr>      
    </table>
  </td>
</tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table align='left'>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('nameformulario')." ".GetLangVar('namehabitat')." &nbsp;&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
		$help = "Selecione um formulário que contém variáveis usadas em descrições de hábitat. O HabitatID da amostra será usado como referência para a variação nessas variáveis, e uma descrição será produzida seguindo a ordem das variáveis no formulário. O formato da descrição é definido na função habitatstring.sql";
		echo " onclick=\"javascript:alert('$help');\" /></td>
        <td class='tdformnotes'>
          <select name='formidhabitat' >
            <option value=''>".GetLangVar('nameselect')."</option>";
	//formularios usuario
	$qq = "SELECT * FROM Formularios WHERE (AddedBy=".$_SESSION['userid']." OR Shared=1) AND HabitatForm=1  ORDER BY FormName,Formularios.AddedDate ASC";
	$rr = mysql_query($qq,$conn);
	while ($row= mysql_fetch_assoc($rr)) {
		if (($formidhabitat+0)==$row['FormID']) {
			$slt = 'selected';
		} else {
			$slt ='';
		}
		echo "
            <option $slt value='".$row['FormID']."'>".$row['FormName']."</option>";
	}
	echo "
          </select>
        </td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('namelogo')."</td>
        <td><input type=radio name='useprojectlog' value='1' />&nbsp;".GetLangVar('nameprojeto')."s</td>
        <td><input type=radio name='useprojectlog' checked value='0' />&nbsp;INPA</td>
      </tr>
    </table>
  </td>
</tr>";

//campo com numero de duplicatas
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table>
      <tr>
        <td >
          <table>
            <tr><td class='tdsmallbold'>".GetLangVar('campocomnumerodeduplicatas')."&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
				$help = GetLangVar('campocomnumerodeduplicatas_help');
			echo " onclick=\"javascript:alert('$help');\" /></td></tr>
          </table>
        </td>
        <td>
          <table>
            <tr>
            <td>
              <select name='duplisTraitID'>
                <option value=''>".GetLangVar('nameselect')." ".mb_strtolower(GetLangVar('namevariavel'))."</option>";
				$filtro = "SELECT * FROM Traits WHERE TraitTipo  LIKE '%Quantita%' ORDER BY TraitName ASC";
				$nnn = mysql_query($filtro,$conn);
				while ($aa = mysql_fetch_assoc($nnn)){
					if (!empty($aa['TraitName'])) {
						$tnma = explode("-",$aa['PathName']);
						$nt = count($tnma)-1;
						unset($tnma[$nt]);
						$ptna = implode("-",$tnma);
						$level = $aa['MenuLevel'];
						$tipo = $aa['TraitTipo'];
				echo "
                <option value='".$aa['TraitID']."'>".$aa['TraitName']." [".$ptna."]</option>";
					}
				}
			echo "
              </select>
              </td>
            </tr>
            <tr><td class='tdformnotes'><input type='text' name='duplicatesTraitID2' size='4' />&nbsp;*ou indique aqui!</td></tr>
          </table>
        </td>
      </tr>
    </table>
  </td>
</tr>
</table>
</div>
</div>
<table style='position: relative' width='50%' align='center'>
<tr>
    <td><input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' /></td>
</form>
<form method='post' action='label-form.php'>
<input type='hidden' name='ispopup' value='".$ispopup."' />
    <td><input type='submit' value='".GetLangVar('namereset')."' class='breset' /></td
</form>
</tr>
</table>";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>