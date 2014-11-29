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

//echopre($ppost);
//CABECALHO
$ispopup=1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />",
"<link rel='stylesheet' type='text/css' href='css/colorbuttons.css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' />"
);
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>",
"<script type='text/javascript' >
function openuser(){
	var usid = document.getElementById('responsaveltag').value;
	if (usid!='') {
		//alert(usid);
         small_window('usuario-form.php?usuarioid='+usid+'&ispopup=1&submitted=editando',600,500,'Editando usuário');
	} else {
		alert('Precisa selecionar um responsável');
	}
}
</script>"

);
$title = 'Editar Censos';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
//echopre($ppost);
echo "
<br />
<form action='censo-edit.php' name='finalform' method='post'>
<input type='hidden' name='ispopup' value='".$ispopup."' />
<table align='center' cellspacing='0' cellpadding='5' class='myformtable' width='99%'>
<thead>
  <tr><td colspan='4'>Editar Censos</td></tr>
</thead>
<tbody>
<tr>";
if (($filtro+0)>0 && !isset($censoid)) {
//já tem censos cadastrados
	$qz = "SELECT DISTINCT CensoID FROM Monitoramento LEFT JOIN Plantas AS pl USING(PlantaID) WHERE pl.FiltrosIDS LIKE '%filtroid_".$filtro."%' AND CensoID>0";
	$rz = @mysql_query($qz,$conn);
	$ncensos = @mysql_numrows($rz);
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
		echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>Selecione um censo para editar:</td>
    <td colspan='3'>
          <input type='hidden' name='filtro' value='".$filtro."' />
          <select name='censoid' onchange='javascript: this.form.submit();'>
            <option value=''>Selecione o censo para editar</option>";
			while ($rr = @mysql_fetch_assoc($rz)) {
				$qk = "SELECT * FROM Censos WHERE CensoID='".$rr['CensoID']."'";
				$rk = @mysql_query($qk,$conn);
				$rwk = @mysql_fetch_assoc($rk);
				echo "
          <option value='".$rwk['CensoID']."'>".$rwk['CensoNome']." [".$rwk['DataInicio']." à ".$rwk['DataFim']."]</option>";
			}
	echo "
          </select>
  </td>
</tr>
";
}
elseif ($censoid>0 && !isset($final)) {
	$qz = "SELECT COUNT(DISTINCT PlantaID) as npl FROM Monitoramento LEFT JOIN Plantas AS pl USING(PlantaID) WHERE pl.FiltrosIDS LIKE '%filtroid_".$filtro."%'  AND Monitoramento.CensoID='".$censoid."'";
	$rz = mysql_query($qz,$conn);
	$rwz = mysql_fetch_assoc($rz);
	$narv = $rwz['npl'];
	
	$qz = "SELECT COUNT(DISTINCT TraitID) as trs FROM Monitoramento LEFT JOIN Plantas AS pl USING(PlantaID) WHERE pl.FiltrosIDS LIKE '%filtroid_".$filtro."%'  AND Monitoramento.CensoID='".$censoid."'";
	$rzk = @mysql_query($qz,$conn);
	$rwzk = @mysql_fetch_assoc($rzk);
	$trs = $rwzk['trs'];

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdformnotes' colspan='4' >Para o filtro escolhido, o censo inclui $narv árvores marcadas e $trs variáveis</td>
</tr>
";
	$qq = "SELECT * FROM Censos WHERE CensoID='".$censoid."'";
	$rq = mysql_query($qq,$conn);
	$rwq = mysql_fetch_assoc($rq);
	//echopre($rwq);
	$censonome = $rwq['CensoNome'];
	$datainicio_nova = $rwq['DataInicio'];
	$responsavelid = $rwq['ResponsavelID'];
	$datafim_nova = $rwq['DataFim'];
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
		echo "
<tr bgcolor = '".$bgcolor."'>
    <td colspan='4'  align='center'  class='tdsmallbold'>Editando censo $censonome</td>
 </tr>"; 
 if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'> 
        <td class='tdsmallboldright'>Este censo inclui as seguintes variáveis:</td>
        <td colspan='3'>
          <table style='border: thin;' >
            ";
		$qq = "SELECT DISTINCT TraitID FROM Monitoramento LEFT JOIN Plantas AS pl USING(PlantaID) WHERE pl.FiltrosIDS LIKE '%filtroid_".$filtro."%' AND CensoID='".$censoid."'";
		$rr = mysql_query($qq,$conn);
		$trids = array();
		while ($row= mysql_fetch_assoc($rr)) {
			$trid = $row['TraitID'];
			if (($trid+0)>0) {
				$trids[] = $trid;
				$qu = "SELECT TraitName, REPLACE(PathName,CONCAT(' - ',TraitName),'') as ptn FROM Traits WHERE TraitID='".$trid."'";
				$rwr = mysql_query($qu,$conn);
				$rww= mysql_fetch_assoc($rwr);
				echo "<tr><td style='font-size: 0.8em;'><i>".$rww['TraitName']."</i> [".$rww['ptn']."]</td></tr>";
			}
		}
		$tridss = implode(";",$trids);    
        echo "
          </table>
        </td>
</tr>";
 if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'> 
        <td class='tdsmallboldright'>Formulário para adicionar variáveis</td>
        <td class='tdformnotes' colspan='3'>
          <select name='formularioid' >";
			echo "
            <option value=''>Selecione</option>";
			//formularios usuario
			$qq = "SELECT * FROM Formularios WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ORDER BY FormName,Formularios.AddedDate ASC";
			$rr = mysql_query($qq,$conn);
			while ($row= mysql_fetch_assoc($rr)) {
				echo "
            <option value='".$row['FormID']."'>".$row['FormName']."</option>";
			}
	echo "
          </select>
        </td>
</tr>";
 if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'> 
        <td class='tdsmallboldright'>Nome para o censo</td>
        <td colspan='3'>
          <input type='hidden'  name='traitids'  value='".$tridss."' />
          <input type='hidden' name='censoid' value='".$censoid."' />
          <input type='hidden' name='final' value='1' />
          <input type='hidden' name='filtro' value='".$filtro."' />
          <input type='text' value='".$censonome."' name='censonome' size='60'/>
        </td>
</tr>";
 if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'> 
        <td class='tdsmallboldright'>Data início</td>
        <td>
          <select name='datainicio'>";
			echo "
          <option selected value=''>Selecione das medições</option>";
			$qzz = "SELECT DISTINCT DataOBS FROM Monitoramento LEFT JOIN Plantas AS pl USING(PlantaID) WHERE pl.FiltrosIDS LIKE '%filtroid_".$filtro."%' AND (CensoID=0 OR CensoID='".$censoid."')  ORDER BY DataOBS ASC";
			$rzz = mysql_query($qzz,$conn);
			while ($rrr = @mysql_fetch_assoc($rzz)) {
				echo "
          <option value='".$rrr['DataOBS']."'>".$rrr['DataOBS']."</option>";
			}
	echo "
          </select>
        </td>
        <td align='right'> ou escolha:</td>
        <td><input class=\"plain\" name=\"datainicio_nova\" value=\"$datainicio_nova\" size=\"11\"  readonly><a onclick=\"if(self.gfPop)gfPop.fPopCalendar(document.forms['finalform'].datainicio_nova);return false;\" ><img name=\"popcal\" align=\"absmiddle\" src=\"calendar/calbtn.gif\" width=\"34\" height=\"22\" border=\"0\" alt=\"\" /></a>
        </td>
</tr>
</tr>";
 if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'> 
        <td class='tdsmallboldright'>Data fim:</td>
        <td>
          <select name='datafim'>";
			echo "
          <option selected value=''>Selecione das medições</option>";
			$qzz = "SELECT DISTINCT DataOBS FROM Monitoramento LEFT JOIN Plantas AS pl USING(PlantaID) WHERE pl.FiltrosIDS LIKE '%filtroid_".$filtro."%' AND (CensoID=0 OR CensoID='".$censoid."') ORDER BY DataOBS DESC";
			$rzz = mysql_query($qzz,$conn);
			while ($rrr = @mysql_fetch_assoc($rzz)) {
				echo "
          <option value='".$rrr['DataOBS']."'>".$rrr['DataOBS']."</option>";
			}
	echo "
          </select>
        </td>
        <td align='right'> ou escolha:</td>
        <td><input class=\"plain\" name=\"datafim_nova\" value=\"$datafim_nova\" size=\"11\"  readonly><a onclick=\"if(self.gfPop)gfPop.fPopCalendar(document.forms['finalform'].datafim_nova);return false;\" ><img name=\"popcal\" align=\"absmiddle\" src=\"calendar/calbtn.gif\" width=\"34\" height=\"22\" border=\"0\" alt=\"\" /></a></td>
</tr>";
 if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'> 
        <td class='tdsmallboldright'>Responsável&nbsp;<img style='cursor: pointer;' height=12 src=\"icons/icon_question.gif\" ";
				$help = "O email dessa pessoa será usado para contato de usuários solicitando dados";
				echo " onclick=\"javascript:alert('$help');\" /></td>";
		if (($responsavelid+0)==0 || $responsavelid==$uuid) {
echo  "
        <td>
          <select id='responsaveltag'  name='responsavelid' >";
		 	if (!isset($responsavelid)) {
				echo "
        <option value=''>".GetLangVar('nameselect')." ".strtolower(GetLangVar('nameeditar'))."</option>";
			} else {
				$wr = mysql_query("SELECT * FROM Users WHERE UserID=".$responsavelid,$conn);
				$ww = mysql_fetch_assoc($wr);
				echo "
        <option  selected value='".$ww['UserID']."'>".$ww['FirstName']." ".$ww['LastName']."</option>";
			}
			echo "
        <option value=''>----</option>";
			$wrr = mysql_query("SELECT * FROM Users ORDER BY FirstName,LastName",$conn);
			while ($aa = mysql_fetch_assoc($wrr)){
				echo "
        <option value='".$aa['UserID']."'>".$aa['FirstName']." ".$aa['LastName']."</option>";
			}
	echo "
    </select>
        <td colspan='2'>
            <input type='button'  onclick='javascript: openuser();'  value='Ver registro'>
        </td>
";
    } elseif ($responsavelid>0) {
				$wr = mysql_query("SELECT * FROM Users WHERE UserID=".$responsavelid,$conn);
				$ww = mysql_fetch_assoc($wr);
				echo "<td>".$ww['FirstName']." ".$ww['LastName']."  você não tem autorização para mudar isso</td>";
    }
 	echo "   
        </td>
      </tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center' colspan='2'><input type='submit' value='Salvar' class='bsubmit' /></td>
  <td align='center' colspan='2'><input type='submit' value='Atualizar medições desse censo' class='bblue' /></td>
</tr>";
}
//echopre($ppost);
if ((!empty($datainicio) || !empty($datainicio_nova)) && (!empty($datafim) || !empty($datafim_nova)) && ($filtro+0)>0 && $final==1 && $censoid>0) {
	if (empty($datainicio)) {
		$datainicio = $datainicio_nova;
	}
	if (empty($datafim)) {
		$datafim = $datafim_nova;
	}
	$arrayofvalues = array(
				'CensoNome' => $censonome,
				'DataInicio' => $datainicio,
				'DataFim' => $datafim,
				'ResponsavelID' => $responsavelid
	);
	$censoset=0;
	$upp = 0;
	$traitarr = explode(";",$traitids);
	if ($censoid>0) {
		$upp = CompareOldWithNewValues('Censos','CensoID',$censoid,$arrayofvalues,$conn);
		if (($upp+0)>0) { //if new values differ from old, then update
			$updated++;
			//faz o log do registro atual
			CreateorUpdateTableofChanges($censoid,'CensoID','Censos',$conn);
			//atualiza o registro
			$censoset = UpdateTable($censoid,$arrayofvalues,'CensoID','Censos',$conn);
		} 
		$censoset = $censoid;
		foreach ($traitarr as $tr) {
			$qzz = "UPDATE Monitoramento as moni, Plantas as pl SET moni.CensoID='0' WHERE  pl.PlantaID=moni.PlantaID AND pl.FiltrosIDS LIKE '%filtroid_".$filtro."%' AND moni.CensoID='".$censoid."' AND TraitID='".$tr."'";
			$rzz = @mysql_query($qzz,$conn);
			//echo $qzz."<br />";
		}
	} 
	else {
		$censoset = InsertIntoTable($arrayofvalues,'CensoID','Censos',$conn);
	}
	if ($censoset>0) {
		if ($formularioid>0) {
			$qq = "SELECT FormFieldsIDS FROM Formularios WHERE FormID='".$formularioid."'";
			$rq = mysql_query($qq,$conn);
			$rqw = mysql_fetch_assoc($rq);
			$trits = explode(";",$rqw['FormFieldsIDS']);
			$traits = array_merge((array)$traitarr,(array)$trits);
			$trids = array_unique($traits);
		} 
		else {
			$trids  = $traitarr;
		}
		$ddid=0;
		foreach ($trids as $tr) {
			$qzz = "UPDATE Monitoramento as moni, Plantas as pl SET moni.CensoID='".$censoset."' WHERE pl.PlantaID=moni.PlantaID AND pl.FiltrosIDS LIKE '%filtroid_".$filtro."%' AND moni.DataOBS>='".$datainicio."' AND  moni.DataOBS<='".$datafim."' AND moni.TraitID='".$tr."'";
			//echo $qzz."<br />";
			$rzz = @mysql_query($qzz,$conn);
			if ($rzz) {
				$ddid++;
			}
		}
		if ($ddid>0) {
			if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
			echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='4' align='center' style='font-size: 1.2em; color: red;'>O censo foi atualizado com sucesso!</td>
</tr>
";
		}
	}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='4' align='center'><input type='button' value='Fechar' class='bblue' onclick='javascript: window.close();'/></td>
</tr>";
}
echo "
</tbody>
</table>
</form>
";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=TRUE,$footer=$menu);

?>