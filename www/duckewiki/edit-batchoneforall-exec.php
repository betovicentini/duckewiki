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
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' >"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>",
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>"
);
$title = 'Um valor para várias amostras';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

unset($_SESSION['variation']);
echo "
<br />
<form action='edit-batchoneforall-save.php' name='finalform' method='post'>
<input type='hidden' name='ispopup' value='$ispopup' >
<table align='center' cellspacing='0' cellpadding='7' class='myformtable'>
<thead>
  <tr><td colspan='100%'>Atribuir os mesmos valores para um conjunto de especímenes</td></tr>
</thead>
<tbody>
<tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>OPÇÃO 1</td>
  <td>
    <table>
      <tr>
        <td>
          <select name='filtro'>";
		if (!empty($filtro)) {
			$qq = "SELECT * FROM Filtros WHERE FiltroID='".$filtroid."'";
			$res = @mysql_query($qq,$conn);
			$rr = @mysql_fetch_assoc($res);
			echo "
          <option selected value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
		}
			echo "
          <option selected value=''>".GetLangVar('nameselect')." ".GetLangVar('namefiltro')."</option>";
			$qq = "SELECT * FROM Filtros WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ORDER BY FiltroName";
			$res = @mysql_query($qq,$conn);
			while ($rr = @mysql_fetch_assoc($res)) {
				echo "
          <option value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
			}

	echo "
          </select>
        </td>
      </tr>
    </table>
  </td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>OPÇÃO 2</td>
  <td>
`<table>
    <tr>
      <td class='tdsmallbold'>Selecione Amostras</td>
      <input type='hidden' id='especimenesids' name='especimenesids' value='".$especimenesids."' />
      <td id='especimenestxt'>$especimenestxt</td>";
			if (empty($especimenesids)) {
				$butname = GetLangVar('nameselect');
	      		unset($_SESSION['specsids']);
	      		$specsids = 0;

			} else {
				$butname = GetLangVar('nameeditar');
	      		$_SESSION['especsids'] = $especimenesids;
	      		$specsids = 1;
			}
			if ($cleanssession==1) {
				$kv = 'selec_'.$_SESSION['userid'];
				unset($_SESSION[$kv]);
			}
			$myurl = "selectespecimene-popup.php?elementid=especimenesids&elementtxtid=especimenestxt&specsids=".$specsids;

echo "
      <td><input type=button value='$butname' class='bsubmit' onclick = \"javascript:small_window('$myurl',850,400,'Selecione especimenes');\" /></td>
    </tr>
  </table>
  </td>
</tr>
";

//taxonomia
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>NOVA IDENTIFICAÇÃO</td>
  <td >
          <table >
            <tr >
              <td class='tdformnotes' id='dettexto'>$dettext</td>
              <input type='hidden' id='detsetcode' name='detset' value='$detset' >
";
		$butname = GetLangVar('nameselect');
		echo "
              <td><input type=button value='$butname' class='bblue' ";
			$myurl ="taxonomia-popup.php?ispopup=1&detid=$detid&dettextid=dettexto&detsetid=detsetcode"; 
			echo " onclick = \"javascript:small_window('$myurl',800,450,'TaxonomyPopup');\"></td>
            </tr>
          </table>
        </td>
</tr>";
//dados de localidade
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>".GetLangVar('namelocalidade')."&nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = GetLangVar('localidadetipos2');
	echo " onclick=\"javascript:alert('$help');\" /></td>
  <td>
  <table>
    <tr><td class='tdformnotes' colspan='100%'>$localtxt</td></tr>
    <tr>
      <td class='tdsmallboldright'>OPÇÃO&nbsp;01&nbsp;-&nbsp;Localidade</td>
      <td>"; 
		autosuggestfieldval3('search-gazetteer-new.php','locality',$gaztxt,'localres','gazetteerid',$gazetteerid,true,60);
		echo "
      </td>";
	  $myurl = "localidade_dataexec.php?ispopup=1&municipioid=$municipioid&paisid=$paisid&provinciaid=$provinciaid";
		echo "
      <td><input type=button class='bblue' value='".GetLangVar('namenova')."'  onclick =\"javascript:small_window('$myurl',900,300,'Cadastrar nova localidade');\" /></td>
	</tr>
    <tr>
      <td class='tdsmallboldright'>OPÇÃO&nbsp;02&nbsp;-&nbsp;Ponto&nbsp;de&nbsp;GPS</td>
      <td>"; 
		autosuggestfieldval3('search-gpspoint.php','gpspt',$gpstxt,'gpsres','gpspointid',$gpspointid,true,60); 
		echo "
      </td>
	</tr>
  </table>
  </td>
</tr>";
//habitat descricao
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>".GetLangVar('namehabitat')."</td>
  <td >
    <table align='left' cellpadding=\"7\" cellspacing=\"0\" class='tdformnotes'>
      <input type='hidden' id='habitatidfield'  name='habitatid' value='$habitatid' />
      <tr>
        <td id='habitatfield' class='tdformnotes'>$habitat</td>";
		if (empty($habitatid)) {
			$buthab = GetLangVar('nameselect');
		} else {
			$buthab = GetLangVar('nameeditar');
		} 
		echo "
        <td align='center'><input type='button' value='$buthab' class='bsubmit' onclick = \"javascript:small_window('habitat-popup.php?ispopup=1&pophabitatid=$habitatid&elementidval=habitatidfield&elementidtxt=habitatfield&opening=1',850,400,'Selecione um habitat');\" /></td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <input type='hidden' name='addcolvalue' value='$addcolvalue'>
  <td class='tdsmallboldright'>".strtoupper(GetLangVar('nameaddcoll'))."</td>
  <td >
    <table>
      <tr>
        <td class='tdformnotes' ><textarea name='addcoltxt' cols='60' rows='2' readonly>$addcoltxt</textarea></td>
        <td><input type=button value=\"+\" class='bsubmit' ";
		$myurl ="addcollpopup.php?getaddcollids=$addcolvalue&formname=finalform";
		echo " onclick = \"javascript:small_window('$myurl',500,400,'Coletores adicionais');\"></td>
      </tr>
    </table>
  </td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <input type='hidden' name='vernacularvalue' value='$vernacularvalue'>
  <td class='tdsmallboldright'>NOME VULGAR</td>
  <td >
    <table>
      <tr>
       <td class='tdformnotes' ><textarea name='vernaculartxt' cols='60' rows='2' readonly>$vernaculartxt</textarea></td>
        <td><input type=button value=\"+\" class='bsubmit' ";
		$myurl ="vernacular_selector.php?getvernacularids=$vernacularvalue&formname=finalform";
		echo " onclick = \"javascript:small_window('$myurl',400,300,'Vernacular');\"></td>
      </tr>
    </table>
  </td>
</tr>
";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>".strtoupper(GetLangVar('nameprojeto'))."</td>
  <td >
    <select name='projetoid' >
      <option value=''>".GetLangVar('nameselect')."</option>";
	  $qq = "SELECT * FROM Projetos ORDER BY ProjetoNome";
	  $resss = mysql_query($qq,$conn);
	  while ($rwww = mysql_fetch_assoc($resss)) {
			echo "
      <option   value='".$rwww['ProjetoID']."'>".$rwww['ProjetoNome']."</option>";
	  }
	echo "
    </select>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>VARIÁVEIS DE FORMULÁRIOS</td>
  <td >
    <table  align='left' border=0 cellpadding=\"7\" cellspacing=\"0\" class='tdformnotes'>
      <tr>
        <td id='traitids' class='tdformnotes'>".$traitids."</td>
        <td align='left'><input  type='button' value='$butname' class='bsubmit' onclick = \"javascript:small_window('traits_coletorvariacao.php?cleansession=1&elementid=traitids',800,500,'Entrar Variacao');\" /></td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <input type='hidden' name='final' value='1'>
  <td colspan='100%' align='center'><input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit'></td>
</tr>
</tbody>
</table>
</form>
";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>