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
$title = 'Grupos de Espécies';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
if ($groupid>0 && $final!='1') {
	$query = "SELECT * FROM  Tax_SpeciesGroups WHERE GroupID='$groupid'";
	$res = mysql_query($query,$conn);	
	$row = mysql_fetch_assoc($res);
	$gruponame = $row['GroupName'];
	$grupotipo = $row['FormalGroup'];
	$grupoautor = $row['PesoaID'];
	$sp_membros = $row['Membros'];
	$formaref = $row['Referencias'];
	unset($_SESSION['variation']);
	$tempids='';
	$oldvals = storeoriginaldatatopost($groupid,'GrupoSppID',$formid,$conn,$tempids);
	$traitarray = $oldvals;
	//echopre($traitarray);
	if (count($traitarray)>0) {
		$_SESSION['variation'] = serialize($oldvals);
		$traitids = describetraits($traitarray,$img=TRUE,$conn);
	}
	if (empty($specieslist)) {
			$arraylist = explode(";",$sp_membros);
			$resultado = array();
			foreach ($arraylist as $key => $value) {
				$dado = explode("|",$value);
				if (trim($dado[0])=='familia') {
					$rr = getfamilies($dado[1],$conn,$showinvalid);
					$row = $rr[2];
					$resultado[] = $row['Familia'];
				}
				if (trim($dado[0])=='genero') {
					$rr = getgenera($dado[1],$famid,$conn,$showinvalid);
					$row = mysql_fetch_assoc($rr);
					$resultado[] = $row['Genero'];
				}
				if (trim($dado[0])=='especie') {
					$rr = getspecies($dado[1],$genusid,$conn,$showinvalid);
					$row = mysql_fetch_assoc($rr);
					$resultado[] =  $row['Genero']." ".$row['Especie'];			
				}
				if (trim($dado[0])=='infraspecies') {
					$rr = getinfraspecies($dado[1],$speciesid,$conn,$showinvalid);
					$row = mysql_fetch_assoc($rr);
						$resultado[] = $row['Genero']." ".$row['Especie']." ".$row['InfraEspecieNivel']." ".$row['InfraEspecie'];
				}
			}
			$specieslist = implode("; ",$resultado);
	}
	
	$qq = "SELECT COUNT(*) as numspecimens FROM Especimenes WHERE GruposSppIDs LIKE '%grupoid_".$groupid."%'";
	$res = mysql_query($qq,$conn);
	$rw = mysql_fetch_assoc($res);
	$numspecimens = $rw['numspecimens'];
	$qq = "SELECT COUNT(*) as numplantas FROM Plantas WHERE GruposSppIDs LIKE '%grupoid_".$groupid."%'";
	$res = mysql_query($qq,$conn);
	$rw = mysql_fetch_assoc($res);
	$numplantas = $rw['numplantas'];
	
	////////
}
if ($final==1) {
	$updated=0;
	$erro =0;
	$salvo=0;
	$arrayofvalues = array(
	    	'GroupName' => $gruponame,
	    	'GroupAutor' => $grupoautor,
	    	'Membros' => $sp_membros,
	    	'FormalGroup' => $grupotipo,
	    	'Referencias' => $formaref);
	 //entao esta editando   	
	if ($groupid>0) { 
	    $upp = CompareOldWithNewValues('Tax_SpeciesGroups','GroupID',$groupid,$arrayofvalues,$conn);
		if (!empty($upp) && $upp>0) { //if new values differ from old, then update	
			CreateorUpdateTableofChanges($groupid,'GroupID','Tax_SpeciesGroups',$conn);
			$updatespecid = UpdateTable($groupid,$arrayofvalues,'GroupID','Tax_SpeciesGroups',$conn);
			if (!$updatespecid) {
				$erro++;
			} else {
	  			 $updated++;
			}
		} 
	} else { //senao insere
		 $groupid = InsertIntoTable($arrayofvalues,'GroupID','Tax_SpeciesGroups',$conn);
		 if (!$groupid) {
				$erro++;
			} else {
	  			 $updated++;
			}
	}
	//faz um update do filtro no grupo
	if ($filtro>0 && $groupid>0) {
		$members = explode(";",$sp_membros);
		if (count($members)>0) {
			$qq='';
			$ii=0;
			foreach ($members as $mm) {
				$mem = explode("|",$mm);
				//echopre($mem);
				$tipo = trim($mem[0]);
				$id = $mem[1]+0;
				if ($ii>0) { $qq = $qq." OR ";}
				if ($tipo=='infraspecies') {
					$qq = $qq."Identidade.InfraEspecieID='".$id."'";
					}
				if ($tipo=='especie') {
					$qq = $qq."Identidade.EspecieID='".$id."'";}
				if ($tipo=='genero') {
					$qq = $qq."Identidade.GeneroID='".$id."'";}
				if ($tipo=='familia') {
					$qq = $qq."Identidade.FamiliaID='".$id."'";}
				if (!empty($qq)) { $ii++;}	
					
			}
			$filtertag = 'filtroid_'.$filtro;
			$grupotag = 'grupoid_'.$groupid;
			$sql = "UPDATE Plantas INNER JOIN Identidade USING (DetID) SET Plantas.GruposSppIDs=IF(Plantas.GruposSppIDs<>'',CONCAT(Plantas.GruposSppIDs,';','".$grupotag."'),'".$grupotag."') WHERE (".$qq.") AND Plantas.FiltrosIDS LIKE '%".$filtertag."%' AND Plantas.GruposSppIDs NOT LIKE '%".$grupotag."%'";
			$updatedpls = mysql_query($sql,$conn);
			if ($updatedpls) {
				$salvo++;
			} else {
				$erro++;
			}
			$sql = "UPDATE Especimenes INNER JOIN Identidade USING (DetID) SET Especimenes.GruposSppIDs=IF(Especimenes.GruposSppIDs<>'',CONCAT(Especimenes.GruposSppIDs,';','".$grupotag."'),'".$grupotag."') WHERE (".$qq.") AND Especimenes.FiltrosIDS LIKE '%".$filtertag."%' AND Especimenes.GruposSppIDs NOT LIKE '%".$grupotag."%'";
			$updatedpls = mysql_query($sql,$conn);
			if ($updatedpls) {
				$salvo++;
			} else {
				$erro++;
			}
			//echo "<br />".$sql;
		}
	} 
	$traitarray = unserialize($_SESSION['variation']);
	//echopre($traitarray);
	if (count($traitarray)>0) {
			$traitupdate = updatetraits($traitarray,$groupid,'GrupoSppID',$bibtex_id,$conn);
			if ($traitupdate) {
				$updated++;
			} else {
				$erro++;
			}
	}
	if ($updated>0 || $salvo>0) {
		echo "
<br />
<table cellpadding=\"5\" align='center' class='success'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('sucesso1')."</td></tr>
</table>";
	} elseif ($erro>0) {
		echo "
<br />
<table cellpadding=\"5\" align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('erro1')."</td></tr>
</table>";
	}
	
} 
else {
echo "
<br />
<form name='finalform' method='post' action='grupospp-exec.php'>
 <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input type='hidden' name='groupid' value='$groupid' />
  <input type='hidden' name='final' value='1' />
<table class='myformtable' align='center' cellpadding=\"5\">
<thead>
<tr>
  <td colspan='2'>"; 
if ($groupid>0) {
	echo GetLangVar('nameeditando')." ";
} else {
	echo GetLangVar('namenovo')." ";
}
	echo strtolower(GetLangVar('namespeciesgroups'))." 
  </td>
</tr>
</thead>
<tbody>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>".GetLangVar('namenome')."</td>
  <td colspan='2'><input type='text' name='gruponame' value='$gruponame' /></td>
</tr>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>".GetLangVar('nametipo')."</td>
  <td>
    <table >
      <tr>
        <td>
          <input type='radio' name='grupotipo' ";
			if ($grupotipo=='formalgrp') { echo " checked ";}
			echo " value='formalgrp' />
        </td>
        <td>".GetLangVar('nameformal')."&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
			$help = strip_tags(GetLangVar('grupoformal_help'));
			echo " onclick=\"javascript:alert('$help');\" /></td>
        <td><input type='radio' name='grupotipo' ";
				if ($grupotipo=='informalgrp') { echo " checked ";}
				echo " value='informalgrp' /></td>
        <td>".GetLangVar('nameinformal')."&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
			$help = strip_tags(GetLangVar('grupoinformal_help'));
			echo " onclick=\"javascript:alert('$help');\" /></td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>".GetLangVar('namemembros')."</td>
  <td colspan='2'>
    <table>
      <tr>
        <td>
          <input type='hidden' id='specieslistids' name='sp_membros' value='$sp_membros'>
          <textarea rows='2' cols='50' rows='3' id='specieslist' name='specieslist' readonly>".trim($specieslist)."</textarea
        </td>
        <td>
          <input type='button' value='<<' class='bsubmit' ";
			$myurl ="selectspeciespopup.php?formname=finalform&elementname=specieslistids&destlistlist=".$sp_membros;
			if (!empty($genusid) && empty($speciesid)) {$myurl = $myurl."&famid=".$famid;} elseif (!empty($speciesid)) {$myurl = $myurl."&famid=".$famid."&genusid=".$genusid;} 
		echo " onclick = \"javascript:small_window('$myurl',500,400,'SelectSpecies');\" />
        </td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>".GetLangVar('namereference')."</td>
  <td colspan='2'><textarea name='formaref' cols='50' rows='5'>$formaref</textarea></td>
</tr>";

if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table>";
	if ($numplantas>0) {
		echo "
      <tr><td colspan='100%'>&nbsp;</td></tr>
      <tr><td colspan='100%'  class='tdsmallbold'>$numplantas plantas marcadas nesse grupo</td></tr>
      <tr><td colspan='100%'>&nbsp;</td></tr>";
	}
	if ($numspecimens>0) {
		echo "
      <tr><td colspan='100%'>&nbsp;</td></tr>
      <tr><tr><td colspan='100%'  class='tdsmallbold'>$numspecimens espécimes nesse grupo</td></tr>
      <tr><td colspan='100%'>&nbsp;</td></tr>";
	}
	echo "
      <tr>
        <td class='tdsmallbold'>".GetLangVar('namefiltro')."&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
	$help = strip_tags(GetLangVar('grupofiltro_help'));
	echo " onclick=\"javascript:alert('$help');\" /></td>
        <td>
          <select name='filtro'>";
		if ($filtro>0) {
			$qq = "SELECT * FROM Filtros WHERE FiltroID='".$filtroid."'";
			$res = @mysql_query($qq,$conn);
			$rr = @mysql_fetch_assoc($res);
			echo "
            <option selected value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
		}
			echo "
            <option  value=''>".GetLangVar('nameselect')."</option>";
			$qq = "SELECT * FROM Filtros ORDER BY FiltroName";
			$rs = @mysql_query($qq,$conn);
			while ($rr = @mysql_fetch_assoc($rs)) {
				echo "
            <option value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
			}
	echo "
          </select>
        </td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>".GetLangVar('nameobs')."s</td>
  <td >
    <table  align='left' border=0 cellpadding=\"7\" cellspacing=\"0\" class='tdformnotes'>
      <tr>
        <td id='traitids' class='tdformnotes'>".$traitids."</td>
        <td align='left'><input  type='button' value=\"".GetLangVar('nameadicionar')."\" class='bsubmit' onclick = \"javascript:small_window('traits_coletorvariacao.php?ispopup=1&elementid=traitids',800,500,'Variação para Grupos de Espécie');\" /></td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%' align='center'>
    <input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' />
  </td>
</tr>
</tbody>
</table>
</form>";
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>
