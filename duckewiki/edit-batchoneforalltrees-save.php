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

if ($final==1) {
	$erro=0;
	$otherfields = array(
'addcolvalue',
'detset',
'gazetteerid',
'gpspointid',
'habitatid',
'projetoid',
'vernacularvalue');
	if (!empty($_SESSION['variation'])) {
		$of = 1;
	} else {
		$of=0;
	}
	foreach ($otherfields as $vv) {
		$tz = $ppost[$vv];
		if (!empty($tz) || $tz>0) {
			$of++;
		}
	}
	if ($filtro>0 && !isset($autorizado) && $of>0) {
		$qq = '';
		$qq  = "SELECT * FROM Plantas WHERE FiltrosIDS LIKE '%filtroid_".$filtro.";%' OR FiltrosIDS LIKE '%filtroid_".$filtro."'";
		$qr = mysql_query($qq,$conn);
		$nrss = mysql_numrows($qr);
		$specarr = explode(";",$tagnumbers);
		$nofound = array();
		if (count($specarr)>0 && ($specarr[0]+0)>0) {
			$ii=0;
			foreach ($specarr as $tag) {
				$tag= trim($tag);
				$sql  = "SELECT PlantaID From Plantas WHERE PlantaTag='".$tag."' AND (FiltrosIDS LIKE '%filtroid_".$filtro.";%' OR FiltrosIDS LIKE '%filtroid_".$filtro."')";
				$qr = mysql_query($sql,$conn);
				if ($qr) {
					$qrw = mysql_fetch_assoc($qr);
					$plid = $qrw['PlantaID'];
					if ($ii==0) {
						$qq .= "AND (";
					} else {
						$qq .= " OR ";
					}
					$qq .= " PlantaID='".$plid."'";
					$ii++;
			  	} else {
					$nofound[] = $tag;
			  	}
			}
			$qq .= ")";
		} 
		else {
			$txt = "Você indicou para atualizar os registros de todas as <b>".$nrss."</b> árvores que o filtro contém.<br />Tem certeza que quer continuar?";
			$erro++;
		}
		if (count($nofound)==count($specarr) && count($specarr)>0) {
			$txt = "O filtro contém <b>".$nrss."</b> árvores e os TAGs listados por você não estão no filtro.";
			$erro++;
		}
		if (count($nofound)>0 && count($specarr)>0) {
			$nf = count($nofound);
			$ntxt = implode(";",$notfound);
			$txt = $nf." TAGs informados por você não estão no filtro:<br /><textarea readonly>".$ntxt."</textarea>";
			$erro++;
		}
		$_SESSION['treesql']  = $qq;
		if ($erro>0) {
$title = 'Um valor para várias plantas salvando';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
			echo "
<br />
<table cellpadding=\"3\" width='60%' align='center' class='erro'>
<tr><td colspan='2' class='tdsmallbold' align='center'>".$txt."</td></tr>
<tr>
  <td>
    <form action='edit-batchoneforalltrees-save.php' method='post'>";
    foreach ($ppost as $kk => $vv) {
echo "
      <input type='hidden' value='".$vv."' name='".$kk."' />";
    }
echo "
      <input type='hidden' value='1' name='autorizado' />
      <input type='submit' value='Continuar e atualizar registros' class='bsubmit'/>
    </form>
  </td>
  <td>
    <form action='edit-batchoneforalltrees-exec.php' method='post'>
      <input type='hidden' value='".$ispopup."' name='ispopup' />
      <input type='submit' value='Voltar' class='bblue'/>
    </form>
  </td>
</tr>
</table>
<br />";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
		} 
		else {
			$autorizado=1;
			$stepsize = 5000; 
			$nsteps = ceil($nrss/$stepsize);
			$recsupdated = 0;
			$step=0;
		}
	} 
	elseif ($of==0) {
$title = 'Um valor para várias plantas salvando';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
			echo "
<br />
<table cellpadding=\"3\" width='60%' align='center' class='erro'>
<tr><td colspan='2' class='tdsmallbold' align='center'>Nenhuma mudança foi informada!</td></tr>
<tr>
  <td>
    <form action='edit-batchoneforalltrees-exec.php' method='post'>";
    unset($ppost['final']);
    foreach ($ppost as $kk => $vv) {
echo "
      <input type='hidden' value='".$vv."' name='".$kk."' />";
    }
echo "
      <input type='submit' value='Voltar' class='bblue'/>
    </form>
  </td>
</tr>
</table>
<br />";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
	}
	if ($autorizado==1 && $step<=$nsteps) {
		$qq = $_SESSION['treesql'];
		if ($step==0) {
			$st1 = 0;
		} 
		else {
			$st1 = $st1+$stepsize+1;
		}
		$qqq = $qq." LIMIT $st1,$stepsize";
		echo $qqq."<br />";
		$rs = mysql_query($qqq,$conn);
		$nrss = mysql_numrows($rs);
		if ($nrss>0) {
			while ($rw = mysql_fetch_assoc($rs)) {
			$plantaid = $rw['PlantaID'];
			$olddetid = $rw['DetID'];
			$changedtraits = 0;
			$detchanged =0;
			//checar variaveis de formulários
			if (!empty($_SESSION['variation'])) {
				$tempids='';
				$formid = 0;
				$oldtraitids = storeoriginaldatatopost($plantaid,'PlantaID',$formid,$conn,$tempids);
				$newtraitids = unserialize($_SESSION['variation']);
				//compare arrays
				foreach ($newtraitids as $key => $val) {
					$oldval = trim($oldtraitids[$key]);
					$vv = trim($val);
					if ($vv!='imagem' && $vv!='none' && !empty($vv) && ($vv!=$oldval || empty($oldval))) {
						$changedtraits++;
					}
				}
				//if ($changedtraits==0 && !empty($_SESSION['variation'])) {$changedtraits++;}
				if ($changedtraits>0) {
					$traitarray = unserialize($_SESSION['variation']);
					if (count($traitarray)>0) {
						$resultado = updatetraits($traitarray,$plantaid,'PlantaID',$bibtex_id,$conn);
						//echo "mudei".$plantaid."<br />";
					}
				}
			}
			$arrayofvalues = array();
			if (!empty($detset)) {
				$arrayofdet = unserialize($detset);
				$detchanged = CompareOldWithNewValues('Identidade','DetID',$olddetid,$arrayofdet,$conn);
			}
			if ($detchanged>0) {
				$arrayofdet = unserialize($detset);
				$newdetid = InsertIntoTable($arrayofdet,'DetID','Identidade',$conn);
				$arrayofvalues = array('DetID' => $newdetid);
			}	
			$oldgpsptid = $rw['GPSPointID'];
			if ($gpspointid>0 && $oldgpsptid<>$gpspointid) { 
					unset($gazetteerid);
					$arv = array(
						'GazetteerID' => 0,
						'MunicipioID' => 0,
						'ProvinceID' => 0,
						'CountryID' => 0,
						'GPSPointID' => $gpspointid);
						$arrayofvalues = array_merge((array)$arrayofvalues,(array)$arv);
			} 
			elseif (($gazetteerid+0)>0) {
				$arv = array(
						'GazetteerID' => $gazetteerid,
						'MunicipioID' => 0,
						'ProvinceID' => 0,
						'CountryID' => 0,
						'GPSPointID' => 0);
						$arrayofvalues = array_merge((array)$arrayofvalues,(array)$arv);
			}
			$oldhabitatid = $rw['HabitatID'];
			if ($oldhabitatid<>$habitatid && $habitatid>0) {
				$arv = array('HabitatID' => $habitatid);
				$arrayofvalues = array_merge((array)$arrayofvalues,(array)$arv);
			}
			//$oldaddcolvalue = $rw['AddColIDS'];
			//if ($addcolvalue<>$oldaddcolvalue && !empty($addcolvalue)) {
				//$arv = array('AddColIDS' => $addcolvalue);
				//$arrayofvalues = array_merge((array)$arrayofvalues,(array)$arv);
			//}
		//$oldvernacularvalue = $rw['VernacularIDS'];
		//if ($vernacularvalue<>$oldvernacularvalue && !empty($vernacularvalue)) {
			//$arv = array('VernacularIDS' => $vernacularvalue);
			//$arrayofvalues = array_merge((array)$arrayofvalues,(array)$arv);
		//}
			$oldprojetoid = $rw['ProjetoID'];
			if ($oldprojetoid<>$projetoid && $projetoid>0) {
				$arv = array('ProjetoID' => $projetoid);
				$arrayofvalues = array_merge((array)$arrayofvalues,(array)$arv);
			}
			if (count($arrayofvalues)>0) {
				CreateorUpdateTableofChanges($plantaid,'PlantaID','Plantas',$conn);
				$newupdate = UpdateTable($plantaid,$arrayofvalues,'PlantaID','Plantas',$conn);
				$recsupdated++;
				
				//ATUALIZA A TABELA CHECKLIST SPECIMENS
				if ($_SESSION['editando']==1) {
					$sql = " DELETE FROM checklist_pllist WHERE PlantaID='".$plantaid."'";
					mysql_query($sql,$conn);
				} 
				$sql = "INSERT INTO checklist_pllist (SELECT  pltb.PlantaID,  pltb.DetID, plantatag(pltb.PlantaID) as TAG, famtb.Familia as FAMILIA, IF(iddet.InfraEspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie,' ',infsptb.InfraEspecie),IF(iddet.EspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie),IF(iddet.GeneroID>0,gentb.Genero,famtb.Familia))) as NOME, acentosPorHTML(IF(pltb.GPSPointID>0,countrygps.Country,IF(pltb.GazetteerID>0,countrygaz.Country,' '))) as PAIS,  acentosPorHTML(IF(pltb.GPSPointID>0,provigps.Province,IF(pltb.GazetteerID>0,provgaz.Province,' '))) as ESTADO,  acentosPorHTML(IF(pltb.GPSPointID>0,munigps.Municipio,IF(pltb.GazetteerID>0,muni.Municipio,' '))) as MUNICIPIO,  acentosPorHTML(IF(pltb.GPSPointID>0,gazgps.PathName,IF(pltb.GazetteerID>0,gaz.PathName,' '))) as LOCAL,  IF(ABS(pltb.Longitude)>0,pltb.Longitude+0,IF(pltb.GPSPointID>0,gpspt.Longitude+0,IF(ABS(gaz.Longitude)>0,gaz.Longitude+0,NULL))) as LONGITUDE,  IF(ABS(pltb.Longitude)>0,pltb.Latitude+0,IF(pltb.GPSPointID>0,gpspt.Latitude+0,IF(ABS(gaz.Longitude)>0,gaz.Latitude+0,NULL))) as LATITUDE,  IF(ABS(pltb.Longitude)>0,pltb.Altitude+0,IF(pltb.GPSPointID>0,gpspt.Altitude+0,IF(ABS(gaz.Longitude)>0,gaz.Altitude+0,NULL))) as ALTITUDE, 'edit-icon.png' AS EDIT, 'mapping.png' AS MAP, IF(labeldescricao(0,pltb.PlantaID,".$formnotes.",1,0)<>'','edit-notes.png','question-red.png') as OBS, IF(pltb.HabitatID>0,'environment_icon.png','question-red.png') as HABT, IF(projetologo(pltb.ProjetoID)<>'',projetologo(pltb.ProjetoID),'icons/image_missing.png') as PRJ, acentosPorHTML(IF(projetostring(pltb.ProjetoID,0,0)<>'',projetostring(pltb.ProjetoID,0,0),'NÃO FOI DEFINIDO')) as PROJETOstr, if (checkimgs(0, pltb.PlantaID)>0,'camera.png','question-red.png') as IMG, nduplicates(".$duplicatesTraitID.",pltb.PlantaID,'Plantas')+0 as DUPS, checktrait(0, pltb.PlantaID,".$exsicatatrait.") as EXSICATA_IMG FROM Plantas as pltb LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID  LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID  LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID  LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID   LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID  LEFT JOIN Gazetteer as gaz ON gaz.GazetteerID=pltb.GazetteerID   LEFT JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID   LEFT JOIN Province as provgaz ON muni.ProvinceID=provgaz.ProvinceID   LEFT JOIN Country  as countrygaz ON provgaz.CountryID=countrygaz.CountryID   LEFT JOIN GPS_DATA as gpspt ON gpspt.PointID=pltb.GPSPointID   LEFT JOIN Gazetteer as gazgps ON gpspt.GazetteerID=gazgps.GazetteerID   LEFT JOIN Municipio as munigps ON gazgps.MunicipioID=munigps.MunicipioID  LEFT JOIN Province as provigps ON munigps.ProvinceID=provigps.ProvinceID   LEFT JOIN Country  as countrygps ON provigps.CountryID=countrygps.CountryID
WHERE pltb.PlantaID='".$plantaid."')";
				mysql_query($sql,$conn);
			}
			if ($recsupdated==0 && $changedtraits>0) {
				$recsupdated++;
			}
			
  	  	}
$title = 'Editando vários registros de plantas';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<form action='edit-batchoneforalltrees-save.php' name='myform' method='post'>
  <input type='hidden' name='autorizado' value='".$autorizado."'>
  <input type='hidden' name='ispopup' value='".$ispopup."'>
  <input type='hidden' name='nsteps' value='".$nsteps."'>
  <input type='hidden' name='final' value='".$final."'>
  <input type='hidden' name='st1' value='".($st1-1)."'>
  <input type='hidden' name='step' value='".($step+1)."'>
  <input type='hidden' name='stepsize' value='".$stepsize."'>
  <input type='hidden' name='recsupdated' value='".$recsupdated."'>";  
	$otherfields = array(
'detset',
'gazetteerid',
'gpspointid',
'habitatid',
'projetoid');
	foreach ($otherfields as $vv) {
		$tz = $ppost[$vv];
		if (!empty($tz) || $tz>0) {
echo "<input type='hidden' name='".$vv."' value='".$tz."'>";
		}
	}  
echo "<br />
<table align='center' cellpadding='5' class='erro'>
  <tr><td class='tdformnotes'>Processando passo ".($step+1)." de ".($nsteps+1)."  AGUARDE!</td></tr>
</table>
</form>
<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.00001);</script>";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
		}
	}
} 
else {
	header("location: edit-batchoneforalltrees-exec.php");
} 

if ($recsupdated>0 && $step>$nsteps) {
$title = 'Um valor para várias amostras salvando';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='success'>
  <tr><td class='tdsmallbold' align='center'>Os registros foram atualizados com sucesso!</td></tr>
</table>
<br />";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
}
?>