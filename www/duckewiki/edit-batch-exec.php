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

if ($onevalforall=='on') {
	header("location: edit-batchoneforall-exec.php?varsent=".serialize($_POST));
	exit();
}


//if first time the page is shown
if (count($ppost)<=5) {
	//unset session variable that holds the edited data before saving (if it exists)
	unset($_SESSION['specimensbatch']);
	unset($_SESSION['basicvars']);
	if (!isset($basicvariables)) {
		$basicvariables = array();
	} 
	$_SESSION['basicvars'] = serialize($basicvariables);
} else { //else get submitted page data
	$arv = $ppost;
	$page = $arv['page'];
	$largs = $arv['largs'];
	$final = $arv['final'];
	$basvar = $_SESSION['basicvars'] ;
	$basicvariables = unserialize($basvar);
	$formid = $arv['formid'];
	$nspec_perpage = $arv['nspec_perpage'];
	$nomenoautor = $arv['nomenoautor'];
	$especimenesids = $arv['especimenesids'];

	unset($arv['nomenoautor']);
	unset($arv['nspec_perpage']);
	unset($arv['especimenesids']);
	unset($arv['page']);
	unset($arv['largs']);
	unset($arv['basvar']);
	unset($arv['final']);
	unset($arv['formid']);

	//join with data from another page case it exists
	$oldv = unserialize($_SESSION['specimensbatch']);

	//if the page has NOT been already submitted JOIN with data from other pages (if it exists)
	if (!isset($oldv[$page])) {
		$vv = array($page => $arv);
		if (!isset($_SESSION['specimensbatch'])) {
			$news = $vv;
		} else {
			$news = array_merge((array)$oldv,(array)$vv);
		}
	//else update session variable with new page data
	} else {
		$oldv[$page] = $arv;
		$news = $oldv;
	}
	//update session variable
	$_SESSION['specimensbatch'] = serialize($news);
	$variarr = $news;
}

if ($final==1) {
	$news = array_merge((array)array('variable' => $news),(array)array('largespecarr' => $largs));
	$news = array_merge((array)$news,(array)array('formid' => $formid));
	$_SESSION['specimensbatch'] = serialize($news);
	header("location: edit-batch-store.php");
} 
else {
	if ($filtro>0 && !isset($page)) { 
		$specarr = array();
		$qq = '';
		$qq  = "SELECT EspecimenID FROM Especimenes JOIN Pessoas ON ColetorID=PessoaID JOIN FiltrosSpecs as fl ON Especimenes.EspecimenID=fl.EspecimenID WHERE FiltroID=".$filtro;
		$qq = $qq." ORDER BY Abreviacao,Number+0";
		$res = mysql_unbuffered_query($qq,$conn);
		while ($rr = mysql_fetch_assoc($res)) {
			$specarr[] = $rr['EspecimenID'];
		}
		$especimenesids = implode(";",$specarr);

	} elseif (empty($especimenesids) && !isset($page)) {
		header("location: edit-batch.php");
	} else {
		$specarr = explode(";",$especimenesids);
	}
	$nspec = count($specarr);
	$npgs = floor($nspec/$nspec_perpage)+1;
	if (!isset($page)) {
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
				$valor = array($k => $pgarr);
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
		if ($final==3 && $page>0) {
			$namepage = $page;
			$page = $page-1;
		} elseif ($final==3 && $page==0) {
			$namepage = $npgs;
			$page = $npgs-1;
		}
		if ($final==2 && $page<($npgs-1)) {
			$page = $page+1;
			$namepage = $page+1;
		} elseif ($final==2 && $page==($npgs-1)) {
			$namepage = 1;
			$page=0;
		}
		$varia = $variarr[$page];
	}
	$specarr = $largespecarr[$page];
	$linecolor1='#FFCC00';
	$linecolor2='#FFFFFF';



$title = 'Editar várias amostras';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

echo "
<br />
<form action='edit-batch-exec.php' name='finalform' method='post'>
  <table align='center' cellspacing='0' class='scrolltable'>
    <thead>
      <tr>
        <th>&nbsp;</th>";

	if (in_array('coletor',$basicvariables)) {
			echo "<th >".strtoupper(GetLangVar('namecoletor'))."</th>";

	}
	if (in_array('colnum',$basicvariables)) {
			echo "<th >".strtoupper(GetLangVar('namenumber'))."</th>";

	}

	if (in_array('taxonomy',$basicvariables)) {
			echo "<th >".strtoupper(GetLangVar('nametaxonomy'))."</th>";

	}

	if (in_array('datacol',$basicvariables)) {
			echo "<th align='center'>".strtoupper(GetLangVar('namedata'))."</th>";

	}
	if (in_array('localidade',$basicvariables)) {
			echo "<th align='center'>".strtoupper(GetLangVar('namelocalidade'))."</th>";
	}
	if (in_array('habitat',$basicvariables)) {
			echo "<th align='center'>".strtoupper(GetLangVar('namehabitat'))."</th>";
	}
	if (in_array('addcoll',$basicvariables)) {
			echo "<th align='center'>".strtoupper(GetLangVar('nameaddcoll'))."</th>";
	}
	if (in_array('vernacular',$basicvariables)) {
		echo "<th align='center'>".GetLangVar('namevernacular')."</th>";
	}
	if (in_array('projeto',$basicvariables)) {
		echo "<th align='center'>".strtoupper(GetLangVar('nameprojeto'))."</th>";
	}

	if (!empty($formid) || !empty($traitids)) {
		if ($formid>0) {
			$qq = "SELECT * FROM Formularios WHERE FormID='".$formid."'";
			$res = mysql_unbuffered_query($qq,$conn);
			$rr = mysql_fetch_assoc($res);
			$FormFieldsIDS= $rr['FormFieldsIDS'];
			$formcarr = explode(";",$FormFieldsIDS);
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
			echo "<th align='center'>".$pt."</th>";
		}
	}

	//empty last column
	echo "<th>&nbsp;</th>";

	echo "</tr>
	</thead>
	<tbody style='height: 350px; overflow-y: scroll; border-bottom: thin solid gray; overflow-x: hidden'>
	";

	//the first column of the table
	$bgi=0;

	foreach ($specarr as $i) {

		$jj=0;

		unset($nomenoautor);
		unset($dettext);
		unset($detset);
		unset($variaveis);
		if (isset($varia) && count($varia)>0 ) {
			$variaveis = $varia;
			$coletorid = $varia['coletor_'.$i];
			$colnum = $varia['colnum_'.$i];
			$detset = $varia['detset_'.$i];
			if (!empty($detset)) {
				$dettext = describetaxa($detset,$conn);
			}
			$datacol = $varia['datacol_'.$i];
			$gpspointid = $varia['gpspointid_'.$i];
		    $gazetteerid = $varia['gazetteerid_'.$i];
		    $habitatid = $varia['habitatid_'.$i];
		    $addcolvalue = $varia['addcolvalue_'.$i];
		    $vernacularvalue = $varia['vernacularvalue_'.$i];
		    $projetoid = $varia['projetoid_'.$i];
		    $nomenoautor = $varia['nomenoautor_'.$i];

			//$nomenoautor;
		} else {
			$qq = "SELECT * FROM Especimenes WHERE EspecimenID='".$i."'";
			$rs = mysql_unbuffered_query($qq,$conn);
			$row = mysql_fetch_assoc($rs);
			$coletorid = $row['ColetorID'];
			$colnum = $row['Number'];
			$detid = $row['DetID'];
			if ($detid>0) {
				$detarr = getdetsetvar($detid,$conn);
				$detset = serialize($detarr);
				$dettext = describetaxa($detset,$conn);
				$nomenoautor = getdetnoautor($detid,$conn);
			}
			$dd = $row['Day'];
			$mm = $row['Mes'];
			$yy = $row['Ano'];
			$datacol = $yy."-".$mm."-".$dd;
			$gpspointid =$row['GPSPointID'];
		    $gazetteerid =$row['GazetteerID'];
	   		$habitatid = $row['HabitatID']+0;
			$addcolvalue = $row['AddColIDS'];
			$vernacularvalue = $row['VernacularIDS'];
			$projetoid = $row['ProjetoID'];

		}

		//coletor
			$rr = getpessoa($coletorid,$abb=TRUE,$conn);
			$ps = mysql_fetch_assoc($rr);
			$coletor = $ps['Abreviacao'];

		//taxonomy

		$fixedcolumn = $coletor." ".$colnum."<br /> <i>".$nomenoautor."</i>";
		echo "<input type='hidden' name='nomenoautor_".$i."' value='".$nomenoautor."'>";

		$fxcol = $coletor." ".$colnum;

		if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else { $bgcolor=$linecolor1;} $bgi++;

			echo "<tr style='background-color:".$bgcolor."'>
				<td style='width:125px; max-width:150px' title='".strip_tags($fixedcolumn)."'>".$fixedcolumn."</td>";

		if (in_array('coletor',$basicvariables)) {
			echo "<td >
				<select name='coletor_".$i."'>";
				$jj++;
				if ($coletorid>0) {
					echo "<option selected value='".$coletorid."'>$coletor</option>";
				}
				$rr = getpessoa('',$abb=TRUE,$conn);
				while ($ps = mysql_fetch_assoc($rr)) {
					echo "<option value='".$ps['ColetorID']."'>".$ps['Abreviacao']."</option>";
				}
				echo "</select></td>";
			} else {
				$jj++;
				echo "<input type='hidden' name='coletor_".$i."' value='".$coletorid."'>";
			}
		if (in_array('colnum',$basicvariables)) {
			echo "<td ><input type='text' name ='colnum_".$i."' value='".$colnum."' size='6'></td>";
			$jj++;
		} else {
			$jj++;
			echo "<input type='hidden' name='colnum_".$i."' value='".$colnum."'>";
		}

		//taxonomy
		echo "<input type='hidden' name='detid_".$i."' value='$detid'>";
		if (in_array('taxonomy',$basicvariables) || in_array('taxajustname',$basicvariables)) {
			if (in_array('taxonomy',$basicvariables)) {
				 echo "<td title='".$fxcol.": ".strip_tags($dettext)."'>
				<table>
				<tr >
				<td id='dettext_".$i."'  style='text-align: left; border: 0px'>".$dettext."</td>
					<input type='hidden' id='detset_".$i."' name='detset_".$i."' value='$detset' >
				<td style='border: 0px' align='right'>
					<input type=button value='+' class='bsubmit' ";
				$jj++;
				$myurl ="taxonomia-popup-batch.php?detid=$detid&idd=$i"; 
				echo "	onclick = \"javascript:small_window('$myurl',800,200,'Add_from_Src_to_Dest');\">
				</td></tr>
				</table></td>";
			} 
		} else {
			echo "<input type='hidden' name='detset_".$i."' value='".$detset."'>";
		}


		if (in_array('datacol',$basicvariables)) {

		      	echo "<td title='".$fxcol."'>
					<table><tr>
					<td style='border: 0px'><input name=\"datacol_".$i."\" value=\"$datacol\" size=\"11\" ></td>
					<td style='border: 0px'>
						<a onclick=\"if(self.gfPop)gfPop.fPopCalendar(document.forms['finalform'].datacol_".$i.");return false;\" >
						<img name=\"popcal\" align=\"absmiddle\" src=\"calendar/calbtn.gif\" width=\"34\" height=\"22\" border=\"0\" alt=\"\"></a>
					</td>
					</tr></table></td>";
				$jj++;
		} else {
			echo "<input type='hidden' name='datacol_".$i."' value='".$datacol."'>";
		}

		if (in_array('localidade',$basicvariables)) {
		    $locality = '';

	    	if ($gpspointid>0) {
				$locality = getGPSlocality($gpspointid,$name=FALSE,$conn);
			} elseif ($gazetteerid>0) {
				$locality = getlocality($gazetteerid,$name=FALSE,$conn);
			}

			$loc = strip_tags($locality);
	    	echo "<td title='".$fxcol.":  ".$loc."'>
	    		<table>
					<tr><td id='locality_".$i."' style='border: 0px;'>$locality</td></tr>
		<tr>
			<td align='left' style='border: 0px'>
		    <table>
	    		<tr>
			    <td style='border: 0px'><select name='gpspointid_".$i."'>";
				$jj++;
				if (!empty($gpspointid)) {
					$qq = "SELECT * FROM GPS_DATA WHERE PointID='$gpspointid'";
					$resw = mysql_query($qq,$conn);
					$gpsdat = mysql_fetch_assoc($resw);
					echo "<option selected value=".$gpsdat['PointID'].">".$gpsdat['Name']."</option>";
				} else {
					echo "<option selected value=''>Waypoint</option>";
				}
				$qq = "SELECT * FROM GPS_DATA WHERE Type='Waypoint' ORDER BY GPSName,DateOriginal,Name ASC";
				$rse = mysql_query($qq,$conn);
				$gps = "nenhum";
				$date = "1900-10-04";
				while ($rwo = mysql_fetch_assoc($rse)) {
					if ($gps!=$rwo['GPSName']) {
						$gps = $rwo['GPSName'];
						echo "<option class='optselectdowlight' style='font-size:0.8em' value=''>".$rwo['GPSName']."------</option>";
					}
					if ($date!=$rwo['DateOriginal']) {
						$date = $rwo['DateOriginal'];
						echo "<option class='redtext' value='' style='font-size:0.8em'>&nbsp;&nbsp;".$rwo['DateOriginal']."</option>";
					}
					echo "<option value=".$rwo['PointID']." style='font-size:0.8em'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$rwo['Name']."</option>";
				}
				echo "</select></td>
				<td style='border: 0px'>".mb_strtolower(GetLangVar('nameor'))."</td>
				<td align='left' style='border: 0px' >
				<input type='hidden' id='gazetteerid_".$i."'  name='gazetteerid_".$i."' value='$gazetteerid'>
				<input type=button value='Local' class='bsubmit' 
						onclick = \"javascript:small_window('localidade-popup.php?gaztag=gazetteerid_".$i."&localtag=locality_".$i."&gazetteerid=$gazetteerid',850,150,'LocalidadePopUp');\">
					</td>
				</tr></table></td></tr></table></td>";
	    } else {
			echo "<input type='hidden' name='gpspointid_".$i."' value='".$gpspointid."'>";
			echo "<input type='hidden' name='gazetteerid_".$i."' value='".$gazetteerid."'>";
		}

		//habitat
		if (in_array('habitat',$basicvariables)) {
			$habitat = '';
			if ($habitatid>0) { 
				$habitat= describehabitat($habitatid,$img=FALSE,true,$conn);
				$hab = strip_tags($habitat);
			} 
			$nhabt = strlen($habitat);
			if($nhabt>100) { 
				$habitat = substr($habitat,0,100);
				$habitat = $habitat."...";
			}
			echo "<input type='hidden' id='habitatid_".$i."'  name='habitatid_".$i."' value='$habitatid'>
			<td title='".$fxcol.": ".$hab."'><table >
				<tr>
				<td id='habitat_".$i."' style='border: 0px;'>$habitat</td>
				<td style='border: 0px;'>";
			$jj++;
			$myurl = "habitat-popup-batch.php?idd=$i&especimenesids=$especimenesids";
			echo "<input type=button value='+' class='bsubmit' onclick = \"javascript:small_window('$myurl',850,400,'HabitatPopUp');\"></td>
			</tr></table></td>";
		} else {
			echo "<input type='hidden' name='habitatid_".$i."' value='".$habitatid."'>";
		}

		//addcoll
		if (in_array('addcoll',$basicvariables)) {
			$addcolarr = explode(";",$addcolvalue);
			$addcoltxt = '';
			$j=1;
			foreach ($addcolarr as $kk => $val) {
				$val = $val+0;
				if ($val>0) {
					$qq = "SELECT * FROM Pessoas WHERE PessoaID='".$val."'";
					$res = mysql_query($qq,$conn);
					$rrw = mysql_fetch_assoc($res);
					if ($j==1) {
						$addcoltxt = 	$rrw['Abreviacao'];
					} else {
						$addcoltxt = $addcoltxt."; ".$rrw['Abreviacao'];
					}
					$j++;
				}
			}
				echo "
				<td  title='".$fxcol." ".$addcoltxt."'>
				<table>
				<tr>
				<td id='addcoltxt_".$i."' style='border: 0px'>$addcoltxt</td>
				<td style='border: 0px'>
				<input type='hidden' id='addcolvalue_".$i."' name='addcolvalue_".$i."' value='$addcolvalue'>
				<input type=button value=\"+\" class='bsubmit' ";
				$jj++;
				$myurl ="addcollpop-batch.php?getaddcollids=$addcolvalue&formname=finalform&valuevar=addcolvalue_".$i."&valuetxt=addcoltxt_".$i."&especimenesids=$especimenesids"; 
				echo "	onclick = \"javascript:small_window('$myurl',350,280,'Add_from_Src_to_Dest');\">
				</td>
				</tr>
			</table>
			</td>";


		} else {
			echo "<input type='hidden' name='addcolvalue_".$i."' value='".$addcolvalue."'>";
		}

		if (in_array('vernacular',$basicvariables)) {
			$vernarr = explode(";",$vernacularvalue);
			$vernaculartxt = '';
			$j=1;
			foreach ($vernarr as $kk => $val) {
				$qq = "SELECT * FROM Vernacular WHERE VernacularID='$val'";
				$res = mysql_query($qq,$conn);
				$rrw = mysql_fetch_assoc($res);
				if ($j==1) {
					$vernaculartxt = 	$rrw['Vernacular'];
					if (!empty($rrw['Language'])) { $vernaculartxt=$vernaculartxt." (".$rrw['Language'].")";}
				} else {
					if (!empty($rrw['Language'])) { $vtxt= $rrw['Vernacular']." (".$rrw['Language'].")";} else {$vtxt=$rrw['Vernacular'];}
					$vernaculartxt = $vernaculartxt."; ".$vtxt;
				}
				$j++;
			}

			echo "<input type='hidden' name='vernacularvalue_".$i."' value='$vernacularvalue'>
				<td title='".$fxcol.": ".$vernaculartxt."'>
					<table>
						<tr>
						<td style='border: 0px;'><input type='text' name='vernaculartxt_".$i."' value='$vernaculartxt' readonly></td>
						<td style='border: 0px;'><input type=button value=\"+\" class='bsubmit' ";
						$jj++;
						$myurl ="vernacular-pop-bacth.php?getvernacularids=$vernacularvalue&formname=finalform&elementval=vernacularvalue_".$i."&elementtxt=vernaculartxt_".$i; 
						echo "	onclick = \"javascript:small_window('".$myurl."',350,280,'Add_from_Src_to_Dest');\"></td>
						</tr>
					</table>
				</td>"; 
		}	else {
			echo "<input type='hidden' name='vernacularvalue_".$i."' value='".$vernacularvalue."'>";
		}


		if (in_array('projeto',$basicvariables)) {
				$projeto='';
				if ($projetoid>0) {
					$qq = "SELECT * FROM Projetos WHERE ProjetoID='".$projetoid."'";
					$prjres = mysql_query($qq,$conn);
					$prjrow = mysql_fetch_assoc($prjres);
					$projeto = $prjrow['ProjetoNome'];
				}
				echo "<td title='".$fxcol.": ".$projeto."'>
					<table>
						<tr><td style='border: 0px'>
						<select name='projetoid_".$i."'>";
						$jj++;
						if ($projetoid>0) {
							echo "<option selected value='".$prjrow['ProjetoID']."'>$projeto</option>";
						} else {
							echo "<option selected value=''>".GetLangVar('nameselect')."</option>";
						}
						$qq = "SELECT * FROM Projetos ORDER BY ProjetoNome";
						$prjres = mysql_query($qq,$conn);
					while ($prj = mysql_fetch_assoc($prjres)) {
						$projeto = $prj['ProjetoNome'];
						echo "<option value='".$prj['ProjetoID']."'>$projeto</option>";
					}
				echo "</select>
				</td></tr></table>
				</td>";

		} else {
			echo "<input type='hidden' name='projetoid_".$i."' value='".$projetoid."'>";
		}
		//echopre($olarr);
		//variaveis do formulario, se esta e a primeira pagina e a variavel de sessao ainda esta vazia

		if (!isset($variaveis) && $formid>0) {
			if ($formid>0) {
				$traitids = '';
				$variaveis = storeoriginaldatatopost($i,'EspecimenID',$formid,$conn,$traitids);
			} elseif (!empty($traitids)) {
				$traitids = explode(";",$traitids);
				$variaveis = storeoriginaldatatopost($i,'EspecimenID',$formid,$conn,$traitids);
			}
			$pp = TRUE;
			puttraitrow2($variaveis,$formid,$i,$traitids,$conn,$pp,$fxcol);

		} elseif ($formid>0) {
			$pp = FALSE;
			puttraitrow2($variaveis,$formid,$i,$traitids,$conn,$pp,$fxcol);
		}

		//echo "<td><input type='text' name='umteste".$i."' value=''></td>";
		echo "<td>&nbsp;</td>";
		echo "</tr>";


	} //for each specimen
	//echopre($varia);
	for ($k=1;$k<=3;$k++) {
		echo "<tr><td colspan='100%'></td></tr>";
	}
	echo "</tbody></table>";

		echo "<table align='center'><tr>
			<td>
			<input type='hidden' name='especimenesids' value='".$especimenesids."'>
			<input type='hidden' name='formid' value='".$formid."'>
			<input type='hidden' name='page' value='".$page."'>
			<input type='hidden' name='largs' value='".$largs."'>
			<input type='hidden' name='basvar' value='".$basvar."'>
			<input type='hidden' name='nspec_perpage' value='".$nspec_perpage."'>";

			$countpg = $namepage."/".$npgs;

	echo "<table align='center' ><tr>
				<input type='hidden' name='final' value=''>
				<td align='center' >
					<input type='submit' value='<<' class='bblue' onclick=\"javascript:document.finalform.final.value=3\">
				</td>
				<td align='center' >".$countpg." pgs.</td>
				<td align='center' >
					<input type='submit' value='>>' class='bblue' onclick=\"javascript:document.finalform.final.value=2\">
				</td>
				<td align='center' >
					<input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' onclick=\"javascript:document.finalform.final.value=1\">
				</td>
	</form>
		<form action=edit-batch.php method='post'>
			<td align='center'>
				<input type='submit' value='".GetLangVar('namevoltar')."' class='breset' />
			</td>
		</form>
			</tr>
		</table>
	</td>
	</tr></table>";
	}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=TRUE,$footer=$menu);

?>