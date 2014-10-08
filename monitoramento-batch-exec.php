<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
include_once("functions/class.Numerical.php") ;

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
$title = 'Monitoramento Série de Plantas';
$body = '';


//if first time the page is shown
if ($ppost['submitted']>0) {
	//unset session variable that holds the edited data before saving (if it exists)
	unset($_SESSION['monitoramento']);
} else { //else get submitted page data
	$arv = $ppost;
	//get script variables
	$page = $arv['page'];
	$largs = $arv['largs'];
	$final = $arv['final'];
	$formid = $arv['formid'];
	$nspec_perpage = $arv['nspec_perpage'];
	$censo = $arv['censo'];
	$plantasids = $arv['plantasids'];
	$datanew = $arv['datanew'];
	$includetaxonomia = $arv['includetaxonomia'];
	$censo = $arv['censo'];
	$traitids = $arv['traitids'];
	$oldpage = $arv['oldpage'];
	unset($arv['datanew']);
	unset($arv['nomenoautor']);
	unset($arv['nspec_perpage']);
	unset($arv['plantasids']);
	unset($arv['page']);
	unset($arv['largs']);
	unset($arv['final']);
	unset($arv['formid']);
	unset($arv['includetaxonomia']);
	unset($arv['censo']);
	unset($arv['traitids']);
	unset($arv['oldpage']);

	//join with data from another page case it exists
	$oldv = unserialize($_SESSION['monitoramento']);


	//if the page has NOT been already submitted JOIN with data from other pages (if it exists)
	if (!isset($oldv["pg_".$oldpage])) {
		$vv = array("pg_".$oldpage => $arv);
		if (!isset($_SESSION['monitoramento'])) {
			$news = $vv;
		} else {
			$news = array_merge((array)$oldv,(array)$vv);
		}
	//else update session variable with new page data
	} else {
		$oldv["pg_".$oldpage] = $arv;
		$news = $oldv;
	}
	//update session variable
	$_SESSION['monitoramento'] = serialize($news);
	$variarr = $news;
}

if (!formid>0 && !filtro>0) {
	header("location: monitoramento-batch-form.php");
}

if ($final==1) {
	$_SESSION['largespecarr'] = $largs;
	header("location: monitoramento-batch-store.php?formid=".$formid."&censo=".$censo."&includetaxonomia=".$includetaxonomia);
} 
else {
	if ($filtro>0 && !isset($page)) { 
		//order by plant tag
		$qq  = "SELECT PlantaID FROM Plantas WHERE FiltrosIDS LIKE '%filtroid_".$filtro."%' ORDER BY PlantaTag+0";
		$rs = mysql_query($qq,$conn);
		$specarr = array();
		while ($rr = mysql_fetch_assoc($rs)) {
			$specarr[] = $rr['PlantaID'];
		}
		$plantasids = implode(";",$specarr);
	} elseif (empty($plantasids) && !isset($page)) {
		header("location: monitoramento-batch-form.php");
	} else {
		$specarr = explode(";",$plantasids);
	}
	
	$nspec = count($specarr);
	$npgs = floor($nspec/$nspec_perpage)+1;
	
	if ($nspec==0) {
		//header("location: monitoramento-batch-form.php");
	}
	
	if (!isset($page)) {
		//echopre($specarr);
		$namepage = 1;
		$page =0;
		$largespecarr = array();
		$spid = 0;
		for ($k=0; $k<$npgs; $k++) {
			$pgarr = array();
			$spto  = $spid+$nspec_perpage;
			for ($kk=$spid;$kk<=$spto;$kk++) {
				$sid = $specarr[$kk];
				if ($sid>0) {
					$pgarr[] = $sid;
				}
			}
			if (count($pgarr)>0) {
				$valor = array("pg_".$k => $pgarr);
				$largespecarr = array_merge((array)$largespecarr,(array)$valor);
			}
				$spid = $spto+1;
		}
		$largs = serialize($largespecarr);
		$npgs = count($largespecarr);
		unset($varia);
	} else {
		$largespecarr = unserialize($largs);
		$npgs = count($largespecarr);
		if ($final==3 && $oldpage>0) {
			$namepage = $oldpage;
			$page = $oldpage-1;
		} elseif ($final==3 && $oldpage==0) {
			$namepage = $npgs;
			$page = $npgs-1;
		}
		if ($final==2 && $oldpage<($npgs-1)) {
			$page = $oldpage+1;
			$namepage = $page+1;
		} elseif ($final==2 && $oldpage==($npgs-1)) {
			$namepage = 1;
			$page=0;
		}
		if (empty($namepage)) {
			$namepage = $page+1;
			//if ($pg>$npgs) { $namepage=0;} else { $namepage= $pg;}
		}
		//echo "page:".$page." namepage:".$namepage;
		$varia = $variarr["pg_".$page];
	}
	$specarr = $largespecarr["pg_".$page];
	$linecolor1='#FFCC00';
	$linecolor2='#FFFFFF';
	
	//echopre(array($datanew,$censo));
	//echopre($varia);
	
	FazHeader($title,$body,$which_css,$which_java,$menu);
	echo "
<br />
<form action='monitoramento-batch-exec.php' name='finalform' method='post'>
<table align='center' cellspacing='0' cellpadding='3' class='scrolltable'>
<thead>
<tr>
  <th align='center'>".strtoupper('tag')."</th>";
	if (!empty($includetaxonomia)) {
		echo "
  <th align='center'>".GetLangVar('nametaxonomy')."</th>";
	}
	
	if (!empty($formid) || !empty($traitids)) {
		if ($formid>0) {
			$qq = "SELECT * FROM Formularios WHERE FormID='".$formid."'";
			$res = mysql_unbuffered_query($qq,$conn);
			$rr = mysql_fetch_assoc($res);
			$traitids= $rr['FormFieldsIDS'];
			$formcarr = explode(";",$traitids);
		} else {
			$formcarr = explode(";",$traitids);
		}
	
		$qq = "SELECT * FROM Traits WHERE ";
		$j=0;
		foreach ($formcarr as $key => $value) {
				if ($j==0) {
					$qq = $qq." TraitID='".$value."'";
				} else {
					$qq = $qq." OR TraitID='".$value."'";
				}
				$j++;
		}
		$qq = $qq." ORDER BY PathName";
		$rr = mysql_query($qq);
		while ($row= mysql_fetch_assoc($rr)) {
			$pt = strtoupper($row['TraitName']);
			//echo "<th align='center'>".$pt."_Data</th>";
			echo "<th align='center'>".$pt."</th>";
		}
	}
	
	//empty last column
	echo "
  <th>&nbsp;&nbsp;&nbsp;&nbsp;</th>
</tr>
</thead>
<tbody style='height: 350px; overflow-y: scroll; border-bottom: thin solid gray; overflow-x: hidden'>
";
	$bgi=0;
	foreach ($specarr as $i) {
		$jj=0;
		unset($nomenoautor);
		unset($variaveis);
		if (isset($varia) && count($varia)>0 ) {
			$variaveis = $varia;
			$tagnum = $variaveis['tagnum_'.$i];
			$detid = $variaveis['detid_'.$i];
		    $nomesci = trim($variaveis["nomesci_".$i]);
		    if (!empty($nomesci)) {
		   		$nomenoautor = $nomesci;
			} else {
			 	$nomenoautor = $varia['nomenoautor_'.$i];
			}    
		} else {
			$qq = "SELECT * FROM Plantas WHERE PlantaID='".$i."'";
			$rs = mysql_unbuffered_query($qq,$conn);
			$row = mysql_fetch_assoc($rs);
			$tagnum = $row['PlantaTag'];
			$tagnum = sprintf("%05s",$tagnum+0);
			$inexsitu = $row['InSituExSitu'];
				if ($inexsitu=='Insitu') { $tagnum = "JB-N-".$tagnum;}
				if ($inexsitu=='Exsitu') { $tagnum = "JB-X-".$tagnum;}
	
			$detid = $row['DetID'];
			if ($detid>0) {
				$nomenoautor = getdetnoautor($detid,$conn);
				$nomesci = $nomenoautor;
			}
	
		}
	
		//taxonomy
		$fixedcolumn = "Tag: ".$tagnum."&nbsp;";
		if (!empty($nomenoautor) ) {
			$fixedcolumn = $fixedcolumn."<br /> <i>".$nomenoautor."</i>";
		}
		echo "
<input type='hidden' name='nomenoautor_".$i."' value='".$nomenoautor."' />
<input type='hidden' name='tagnum_".$i."' value='".$tagnum."' />
<input type='hidden' name='detid_".$i."' value='".$detid."' />";
	
		$fxcol = $tagnum;
	
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else { $bgcolor=$linecolor1;} $bgi++;
	echo "
<tr style='background-color:".$bgcolor."'>
  <td  style='min-width:125px; font-weight:bold'  class='redtext' title='".strip_tags($fixedcolumn)."'>".$fixedcolumn."</td>";
		if (!empty($includetaxonomia)) {
			$detarr = getdetsetvar($detid,$conn);
			$detset = serialize($detarr);
			$dettext = describetaxa($detset,$conn);
			$detnotes = $detarr[ 'DetNotes' ];
			$determinador = $detarr[ 'DetbyID' ]+0;
	
			if ($includetaxonomia==1) {
				//$nomesci = $nomenoautor;
				echo "
  <td style='min-width:200px' >
    <table>
      <tr><td style='border: 0px'>".$dettext."</td></tr>
      <tr>
        <td style='border: 0px' class='tdformnotes' colspan='2'>
          <input type='checkbox' ";
				if ($detnotes=='Não coletada, det de campo' || $determinador==0) { echo "checked";}
				echo " name='detnota_".$i."' value='1' />&nbsp;Não coletada, det de campo</td>
      </tr>
      <tr>
        <td style='border: 0px'>"; 
		autosuggestfieldval("search-name-simple.php","nomesci_".$i, $nomesci,"nomeres_".$i,"nomesciid_".$i,true); 
		echo "</td>
      </tr>
    </table>
  </td>";
			} elseif ($includetaxonomia==2) {
				echo "
  <td style='min-width:200px' >
    <table>
      <tr>
        <td id='dettext_".$i."'  style='text-align: left; border: 0px'>
          <input type='hidden' id='detset_".$i."' name='detset_".$i."' value='$detset' />".$dettext."
        </td>
        <td style='border: 0px' align='right'>
          <input type=button value='+' class='bsubmit' ";
				$myurl ="taxonomia-popup-batch.php?detid=$detid&idd=$i"; 
				echo " onclick = \"javascript:small_window('$myurl',800,200,'Add_from_Src_to_Dest');\" />
        </td>
      </tr>
      <tr>
        <td style='border: 0px' class='tdformnotes' colspan='2'>
          <input type='checkbox' ";
				if ($detnotes=='Não coletada, det de campo' || $determinador==0) { echo "checked";}
				echo " name='detnota_".$i."' value='1' />&nbsp;Não coletada, det de campo
        </td>
      </tr>
    </table>
  </td>";
	
			}
		}
	
		//echopre($olarr);
		//variaveis do formulario, se esta e a primeira pagina e a variavel de sessao ainda esta vazia
	
		if (!isset($variaveis) && $formid>0 && empty($datanew) && !empty($censo)) {
				//echo $datanew."<br />";
				$variaveis = GetMonitoringData_batch($i,$censo,$formid,$conn);
				$pp = TRUE;
				puttraitrow_monitor($variaveis,$formid,$i,$traitids,$conn,$pp,$fxcol,$datanew);
		} elseif ($formid>0) {
			//echo "formid".$formid." i:".$i." fx:".$fxcol." datanew".$datanew;
			//echopre($variaveis);
			//echo "___________________________<br />";
			$pp = FALSE;
			puttraitrow_monitor($variaveis,$formid,$i,$traitids,$conn,$pp,$fxcol,$datanew);
		}
		//echo "<td><input type='text' name='umteste".$i."' value=''></td>";
		echo "
  <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
</tr>";
	
	} //for each specimen
	//echopre($varia);
	for ($k=1;$k<=3;$k++) {
		echo "
<tr><td colspan='100%'></td></tr>";
	}
	$countpg = $namepage."/".$npgs;
	echo "
</tbody>
</table>
<table align='center'>
  <tr>
    <td>
      <input type='hidden' name='plantasids' value='".$plantasids."' />
      <input type='hidden' name='includetaxonomia' value='".$includetaxonomia."' />
      <input type='hidden' name='formid' value='".$formid."' />
      <input type='hidden' name='largs' value='".$largs."' />
      <input type='hidden' name='nspec_perpage' value='".$nspec_perpage."' />
      <input type='hidden' name='censo' value='".$censo."' />
      <input type='hidden' name='datanew' value='".$datanew."' />
      <input type='hidden' name='final' value='' />
      <input type='hidden' name='traitids' value='".$traitids."' />
      <input type='hidden' name='oldpage' value='".$page."' />
      <table align='center' >
        <tr>
          <td align='center'>
            <input type='submit' value='<<' class='bblue' onclick=\"javascript:document.finalform.final.value=3\" />
          </td>
          <td align='center' >
            <select name='page' onchange='this.form.submit();'>";
						foreach (range(0, $npgs-1, 1) as $pg) {
							$pgnumber = $pg+1;
							if ($page==$pg) {
								echo "
              <option selected value='".$page."'>".$namepage."/".$npgs." pgs.</option>";
							} else {
								echo "
              <option value='".$pg."'>".$pgnumber."/".$npgs." pgs</option>";
							}
						}
					echo "
            </select>
          </td>
          <td align='center' >
            <input type='submit' value='>>' class='bblue' onclick=\"javascript:document.finalform.final.value=2\" />
          </td>
          <td align='center' >
            <input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' onclick=\"javascript:document.finalform.final.value=1\" />
          </td>
        </form>
        <form action=edit-batch-tree.php method='post'>
          <td align='center'>
            <input type='submit' value='".GetLangVar('namevoltar')."' class='breset' />
          </td>
        </form>
        </tr>
      </table>
    </td>
  </tr>
</table>
";
	}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>