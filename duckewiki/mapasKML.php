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
if((!isset($uuid) || 
	(trim($uuid)=='')) && count($listsarepublic)==0) {
		header("location: access-denied.php");
	exit();
} else {
	$acclevel = $_SESSION['accesslevel'];
}

//////PEGA E LIMPA VARIAVEIS
if (!empty($_POST['detset'])) {
	$detset = $_POST['detset'];
	unset($_POST['detset']);
}
if (!empty($_GET['detset'])) {
	$detset = $_GET['detset'];
	unset($_GET['detset']);
}

$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
@extract($gget);

$detid =0;
$detid = $famid+$genid+$specid+$infspecid+$detid;
$especimes =1;
$formnotes = 0;
if (!isset($prepared)) {
	$pp = array('formnotes'=> $formnotes, 'especimes'=> $especimes, 'plantas' => $plantas);
	unset($_SESSION['runinspp']);
	unset($_SESSION['porfamilia']);
	$_SESSION['destvararray'] = serialize($pp);
	unset($_SESSION['qq']);
	unset($qq);
	$qq .= "SELECT pltb.EspecimenID AS WikiEspecimenID, 
	CONCAT(colpessoa.SobreNome,'_',IF(pltb.Prefixo IS NULL OR pltb.Prefixo='','',CONCAT(pltb.Prefixo,'-')), pltb.Number,IF(pltb.Sufix IS NULL OR pltb.Sufix='','',CONCAT('-',pltb.Sufix))) as IDENTIFICADOR, 
	CONCAT(colpessoa.PreNome,' ',colpessoa.SegundoNome,' ',colpessoa.SobreNome) as COLLECTOR, 
	pltb.Number as NUMBER, 
	CONCAT(pltb.Ano,'-',pltb.Mes,'-',pltb.Day) as DATA_COLETA, 
	addcolldescr(AddColIDS) as ADDCOLL,
	IF (pltb.INPA_ID>0,pltb.INPA_ID,0) as INPA_NUM,
	IF(iddet.InfraEspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie,' ',infsptb.InfraEspecie),IF(iddet.EspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie),IF(iddet.GeneroID>0,gentb.Genero,famtb.Familia))) as NOME,
	famtb.Familia as FAMILY,
	IF(iddet.InfraEspecieID>0,CONCAT('<i>',gentb.Genero,' ',sptb.Especie,' </i> ',sptb.EspecieAutor,' <i>',infsptb.InfraEspecieNivel,' ',infsptb.InfraEspecie,'</i> ',infsptb.InfraEspecieAutor), IF(iddet.EspecieID>0,CONCAT('<i>',gentb.Genero,' ',sptb.Especie,'</i> ',sptb.EspecieAutor),IF(gentb.GeneroID>0,CONCAT('<i>',gentb.Genero,'</i>'),''))) as DETERMINACAO,
	CONCAT(detpessoa.Abreviacao,' [',DATE_FORMAT(iddet.DetDate,'%d-%b-%Y'),']') as detdetby,
localidadestring(pltb.GazetteerID,pltb.GPSPointID,pltb.MunicipioID,pltb.ProvinceID,pltb.CountryID,pltb.Latitude,pltb.Longitude,pltb.Altitude) as LOCALIDADE,
IF(pltb.GPSPointID>0,countrygps.Country,IF(pltb.GazetteerID>0,countrygaz.Country,' ')) as COUNTRY,
IF(pltb.GPSPointID>0,provigps.Province,IF(pltb.GazetteerID>0,provgaz.Province,' ')) as MAJORAREA,
IF(pltb.GPSPointID>0,munigps.Municipio,IF(pltb.GazetteerID>0,muni.Municipio,' ')) as MINORAREA,
IF(pltb.GPSPointID>0,gazgps.PathName,IF(pltb.GazetteerID>0,gaz.PathName,' ')) as GAZETTEER,
IF(pltb.GPSPointID>0,pltb.GPSPointID,'') as GPSpointID,
IF(pltb.GPSPointID>0,gazgps.Gazetteer,IF(pltb.GazetteerID>0,gaz.Gazetteer,'')) as GAZETTEER_SPECIFIC,
getlatlongdms(pltb.Latitude, pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID, pltb.MunicipioID, pltb.ProvinceID, pltb.CountryID, 1,5)  as LATITUDE,
getlatlongdms(pltb.Latitude, pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID, pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 0,5)  as LONGITUDE,
	IF(ABS(pltb.Longitude)>0,pltb.Altitude,IF(pltb.GPSPointID>0,gpspt.Altitude,IF(ABS(gaz.Longitude)>0,gaz.Altitude,''))) as ALTITUDE,
	IF(ABS(pltb.Longitude)>0,'GPS-planta',IF(pltb.GPSPointID>0,'GPS',IF(ABS(gaz.Longitude)>0,'Localidade','Municipio'))) as COORD_PRECISION,
	IF(pltb.PlantaID>0,plspectb.PlantaTag,'') as TAG_PlantaMarcada,
	IF(pltb.HabitatID>0,pltb.HabitatID,IF(plspectb.HabitatID>0,plspectb.HabitatID,0)) as HabitatID,
	vernaculars(pltb.VernacularIDS) as NOME_VULGAR,
	projetostring(pltb.ProjetoID,1,0) as PROJETO,
	projetologo(pltb.ProjetoID) as PROJETOlogofile,
	labeldescricao(pltb.EspecimenID+0,pltb.PlantaID+0,".$formnotes.",FALSE,FALSE) as NOTAS";
//	IF(pltb.GPSPointID>0,CONCAT(gazgps.GazetteerTIPOtxt,' ',gazgps.Gazetteer),IF(pltb.GazetteerID>0,CONCAT(gaz.GazetteerTIPOtxt,' ',gaz.Gazetteer),'')) as GAZETTEER_SPECIFIC,
	$qq .= " FROM Especimenes as pltb 
	LEFT JOIN Plantas as plspectb ON pltb.PlantaID=plspectb.PlantaID 
	LEFT JOIN Pessoas as colpessoa ON pltb.ColetorID=colpessoa.PessoaID
	LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID 
	LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID 
	LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID 
	LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  
	LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID 
	LEFT JOIN Pessoas as detpessoa ON detpessoa.PessoaID=iddet.DetbyID
	LEFT JOIN Gazetteer as gaz ON gaz.GazetteerID=pltb.GazetteerID  
	LEFT JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID  
	LEFT JOIN Province as provgaz ON muni.ProvinceID=provgaz.ProvinceID
	LEFT JOIN Country  as countrygaz ON provgaz.CountryID=countrygaz.CountryID  
	LEFT JOIN GPS_DATA as gpspt ON gpspt.PointID=pltb.GPSPointID  
	LEFT JOIN Gazetteer as gazgps ON gpspt.GazetteerID=gazgps.GazetteerID  
	LEFT JOIN Municipio as munigps ON gazgps.MunicipioID=munigps.MunicipioID 
	LEFT JOIN Province as provigps ON munigps.ProvinceID=provigps.ProvinceID  
	LEFT JOIN Country  as countrygps ON provigps.CountryID=countrygps.CountryID
	LEFT JOIN Projetos ON pltb.ProjetoID=Projetos.ProjetoID";

//	IF(ABS(pltb.Longitude)>0,pltb.Longitude,IF(pltb.GPSPointID>0,gpspt.Longitude,IF(ABS(gaz.Longitude)>0,gaz.Longitude,muni.Longitude))) as LONGITUDE,
//IF(ABS(pltb.Longitude)>0,pltb.Latitude,IF(pltb.GPSPointID>0,gpspt.Latitude,IF(ABS(gaz.Longitude)>0,gaz.Latitude,muni.Latitude))) as LATITUDE,	
	if ($filtro>0) {
		$qq .= " WHERE pltb.FiltrosIDS LIKE '%filtroid_".$filtro.";%' OR pltb.FiltrosIDS LIKE '%filtroid_".$filtro."'";
		$qq .= "ORDER BY famtb.Familia,IF(iddet.InfraEspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie,' ',infsptb.InfraEspecie),IF(iddet.EspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie),IF(iddet.GeneroID>0,gentb.Genero,famtb.Familia)))";

		$qz = "SELECT * FROM Especimenes WHERE FiltrosIDS LIKE '%filtroid_".$filtro.";%' OR FiltrosIDS LIKE '%filtroid_".$filtro."'";
		$rz = mysql_query($qz,$conn);
		$nrz = mysql_numrows($rz);
		$stepsize = 1000;
		$nsteps = ceil($nrz/$stepsize);
		$export_filename = "temp_".$filtro.".kml";

	} else {
		if ($specimenid>0) {
			$qq .= " WHERE pltb.EspecimenID=".$specimenid;
			$nrz =1;
			$nsteps =1;
			$stepsize = 1;
			$export_filename = "temp_".$specimenid.".kml";
		} elseif ($detid>0) {
			if ($infspecid>0) {
				$qq .= " WHERE iddet.InfraEspecieID=".$infspecid;
				$qz = "SELECT EspecimenID FROM Especimenes JOIN Identidade as iddet USING(DetID) WHERE iddet.InfraEspecieID=".$infspecid;
				$rz = mysql_query($qz,$conn);
				$nrz = mysql_numrows($rz);
				$stepsize = 1000;
				$nsteps = ceil($nrz/$stepsize);
				$export_filename = "temp_infsp_".$infspecid.".kml";
			} else {
				if ($specid>0) {
					$qq .= " WHERE iddet.EspecieID=".$specid;
					$qz = "SELECT EspecimenID FROM Especimenes JOIN Identidade as iddet USING(DetID) WHERE iddet.EspecieID=".$specid;
					$rz = mysql_query($qz,$conn);
					$nrz = mysql_numrows($rz);
					$stepsize = 1000;
					$nsteps = ceil($nrz/$stepsize);
					$export_filename = "temp_sp_".$specid.".kml";
				} else {
					if ($genid>0) {
						$qq .= " WHERE iddet.GeneroID=".$genid;
						$qz = "SELECT EspecimenID FROM Especimenes JOIN Identidade as iddet USING(DetID) WHERE iddet.GeneroID=".$genid;
						$rz = mysql_query($qz,$conn);
						$nrz = mysql_numrows($rz);
						$stepsize = 1000;
						$nsteps = ceil($nrz/$stepsize);
						$export_filename = "temp_gen_".$genid.".kml";
					} 
					else {
						$qq .= " WHERE iddet.FamiliaID=".$famid;		
						$qz = "SELECT EspecimenID FROM Especimenes JOIN Identidade as iddet USING(DetID) WHERE iddet.FamiliaID=".$famid;
						$rz = mysql_query($qz,$conn);
						$nrz = mysql_numrows($rz);
						$stepsize = 1000;
						$nsteps = ceil($nrz/$stepsize);
						$export_filename = "temp_fam_".$famid.".kml";
					}
				}
			}		
		}
	}
	
	$prepared = 1;
	$_SESSION['exportnresult'] = $nrz;
	$_SESSION['qq'] = $qq;
	$_SESSION['runinspp'] = 'nuncavaiteralgoassim';
	$_SESSION['idxx'] = 0;
	$_SESSION['porfamilia'] = 'tambemnuncavaiteralgoassim';
	$_SESSION['famidx'] = 0;
	$step=0;
	
}
if ($prepared==1 && $step<=$nsteps) {
	if ($step==0) {
		$step=0;
		$st1 = 0;
	} 
	else {
		$qq = $_SESSION['qq'];
		$st1 = $st1+$stepsize+1;
	}
	$qorder = "ORDER BY IF(pltb.GPSPointID>0,countrygps.Country,IF(pltb.GazetteerID>0,countrygaz.Country,' ')),
	IF(pltb.GPSPointID>0,provigps.Province,IF(pltb.GazetteerID>0,provgaz.Province,' ')),
	IF(pltb.GPSPointID>0,munigps.Municipio,IF(pltb.GazetteerID>0,muni.Municipio,' ')),
	IF(pltb.GPSPointID>0,gazgps.PathName,IF(pltb.GazetteerID>0,gaz.PathName,' '))";
	
	$qqq = $qq." ".$qorder." LIMIT $st1,$stepsize";
	
	//echo $qqq."<br>";
	$res = mysql_query($qqq,$conn);
	$starttime = microtime(true);
	$sttime = microtime();
	//$nnres = mysql_numrows($res);
	if ($res) {
		if ($step==0) {
			$url = $_SERVER['HTTP_REFERER'];
			$uu = explode("/",$url);
			$nu = count($uu)-1;
			unset($uu[$nu]);
			$url = implode("/",$uu);
			$fh = fopen("temp/".$export_filename, 'w') or die("nao foi possivel gerar o arquivo");
			$hh = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<kml xmlns=\"http://www.opengis.net/kml/2.2\">
<Document>
  <name>".$_SESSION['userlastname']."_".$_SESSION['sessiondate']."</name>
  <description>INSTITUTO NACIONAL DE PESQUISAS DA AMAZÔNIA (INPA), Manaus, Brasil. \nArquivo gerado por ".$url." ".$_SESSION['userfirstname']." ".$_SESSION['userlastname']." em ".$_SESSION['sessiondate']."</description>
  ";
			fwrite($fh, $hh);
		} 
		else {
			$fh = fopen("temp/".$export_filename, 'a') or die("nao foi possivel abrir o arquivo");
		}
		$rnspp = trim($_SESSION['runinspp']);
		$idzxx = $_SESSION['idxx'];
		$ffidx = $_SESSION['famidx'];
		$pfam = $_SESSION['porfamilia'];
		$nff = 0;
		while ($rsw = mysql_fetch_assoc($res)){
			$quq = "SELECT TraitVariation FROM Traits_variation as trv JOIN Traits as tr USING(TraitID) WHERE tr.TraitTipo='Variavel|Imagem' AND trv.EspecimenID=".$rsw['WikiEspecimenID']." ORDER BY tr.TraitName";
			$ruq = mysql_query($quq,$conn);
			$nruq = mysql_numrows($ruq);

			$ll = $rsw['LONGITUDE']+0;
			if (abs($ll)>0) {

			if (!isset($lati)) {
				$lati = $rsw['LATITUDE'];
			} else {
				$lati = $lati.";".$rsw['LATITUDE'];
			}
			if (!isset($llong)) {
				$llong = $rsw['LONGITUDE'];
			} else {
				$llong = $llong.";".$rsw['LONGITUDE'];
			}
			$onome = trim($rsw['NOME']);
$txt = '';
$newfam = trim($rsw['FAMILY']);
if ($pfam!=$newfam && $ffidx>0) {
		$txt .= "
</Folder>
</Folder>";
$nff = 1;
} else {
$nff = 0;
}
if ($pfam!=$newfam) {
	$txt .= "
<Folder>
<name>".$rsw['FAMILY']."</name>
  <open>0</open>";
	$pfam = $newfam;
}
$ffidx++;

if ($rnspp!=$onome) {
	if ($idzxx>0 && $nff==0) {
		$txt .= "
</Folder>";
	} 
	$txt .= "
<Folder>
<name>".$rsw['NOME']."</name>
  <open>0</open>";
	$idzxx++;
	$rnspp = $onome;
}
if ($nruq>0) {
$url = $_SERVER['HTTP_REFERER'];
$uu = explode("/",$url);
$nu = count($uu)-1;
unset($uu[$nu]);
$url = implode("/",$uu);
$url2 = $url."/icons/images.jpg";
	//$tkt = "<img src=\"".$url2."\" width=20>";
	$tkt = "(".$nruq." imagens)";
} else {
	$tkt = '';
}

$tbwidth = 300;
$titlefont = "1em";
$txtfont = "0.8em";

//$idd = RemoveAcentos(substr($rsw['COLLECTOR'],0,3)."\n".$rsw['NUMBER']);
$txt .= 
"
<Placemark>
  <name>".$rsw['IDENTIFICADOR']."</name>
  <visibility>1</visibility>
  <classe>".$rsw['NOME']."</classe>
  <identif>".$rsw['IDENTIFICADOR']."</identif>
  <country>".$rsw['COUNTRY']."</country>
  <prov>".$rsw['MAJORAREA']."</prov>
  <muni>".$rsw['MINORAREA']."</muni>
  <gazz>".$rsw['GAZETTEER']."</gazz>
  <description>
<![CDATA[
<table width='".$tbwidth."'>
<tr style='font-size: ".$titlefont."'><td>".strtoupper($rsw['FAMILY'])."</td></tr>
<tr style='font-size: ".$titlefont."'><td>".$rsw['DETERMINACAO']."</td></tr>
";
if (!empty($rsw['detdetby'])) {
$txt .= "
<tr style='font-size: ".$txtfont."'><td>Identificado por ".$rsw['detdetby']."</td></tr>";
}
$dethist = returnDEThistoryAStable(0,$rsw['WikiEspecimenID'],$conn);
if (count($dethist)>0) {
$txt .= "
<tr><td><hr></td></tr>
<tr style='font-size: ".$txtfont."'><td><b>Histórico das identificações</b></td></tr>
";
foreach ($dethist as $detvv) {
	$txt .= "<tr style='font-size: ".$txtfont."'><td>".$detvv."</td></tr>";
}
}
if (!empty($rsw['NOME_VULGAR'])) {
$txt .= "
<tr style='font-size: ".$txtfont."'><td>Nome vulgar: ".$rsw['NOME_VULGAR']."</td></tr>";
}
$txt .= "
<tr><td><hr></td></tr>
<tr style='font-size: ".$titlefont."'><td><b>".$rsw['COLLECTOR']." No. ".$rsw['NUMBER']."</b></td></tr>
<tr style='font-size: ".$titlefont."'><td>&nbsp;&nbsp;&amp; ".$rsw['ADDCOLL']."</td></tr>
<tr style='font-size: ".$titlefont."'><td><b>Coletada em ".$rsw['DATA_COLETA']."</b></td></tr>";
if (($rsw['INPA_NUM']+0)>0) {
$txt .= "
<tr style='font-size: ".$txtfont."'><td>Depositado em INPA #".$rsw['INPA_NUM']."</td></tr>";
}
if (!empty($rsw['TAG_PlantaMarcada'])) {
$txt .= "
<tr style='font-size: ".$txtfont."'><b>Planta marcada</b>: #".$rsw['TAG_PlantaMarcada']."</td></tr>";
}
$txt .= "
<tr><td><hr></td></tr>
<tr style='font-size: ".$txtfont."'><td>".$rsw['LOCALIDADE']."</td></tr>";
if ($rsw['HabitatID']>0) {
$habitat = describehabitat($rsw['HabitatID'],$img=FALSE,$conn);
$txt .= "
<tr><td><hr></td></tr>
<tr style='font-size: ".$txtfont."'><td>".$habitat."</td></tr>";
}
if (!empty($rsw['NOTAS'])) {
	$txt .= "
<tr><td><hr></td></tr>
<tr style='font-size: ".$titlefont."'><td><b>Notas</b>:".$rsw['NOTAS']."</td></tr>";
}
$txt .= "
<tr ><td><hr></td></tr>
<tr>
<td>
<table style='border: 0;' align='center' cellpadding='3'>
<tr ><td><img src=\"".$url."/icons/inpa_gov.png\" width='150' /></td></tr>";
if (!empty($rsw['PROJETO'])) {
	$txt .= "
<tr style='font-size: ".$txtfont."'><td>".$rsw['PROJETO']."</td></tr>";
}
$txt .= "
</table>
</td>
</tr>
";

//imagens
$url = $_SERVER['HTTP_REFERER'];
$uu = explode("/",$url);
$nu = count($uu)-1;
unset($uu[$nu]);
$url = implode("/",$uu);
$urlbig = $url."/img/originais/";
$urllowres = $url."/img/lowres/";

if ($nruq>0) {
$txt .= "
<tr ><td><hr></td></tr>
<tr><td>
<table style='border: 0;' align='center' cellpadding='10'>";

while ($ruqw = mysql_fetch_assoc($ruq)) {
	$imgs = explode(";",$ruqw['TraitVariation']);
	foreach ($imgs as $vimg) {
		$vimg = $vimg+0;
		$qusq = "SELECT FileName FROM Imagens WHERE ImageID='".$vimg."'";
		//echo $qusq;
		$rusq = mysql_query($qusq,$conn);
		$rusqw = mysql_fetch_assoc($rusq);
		$tutx = "
<tr><td><a href=\"".$urlbig.$rusqw['FileName']."\"><img src=\"".$urllowres.$rusqw['FileName']."\" width='200'></a><br></td></tr>";
		$txt .= $tutx;
	}
}
$txt .= "
</table>
<td>
</tr>
";
}
$txt .= "
</table>
]]>
  </description>
  <nimgs>".$nruq."</nimgs>
  <Point>
<coordinates>".$rsw['LONGITUDE'].",".$rsw['LATITUDE'].",".$rsw['ALTITUDE']."</coordinates>
  </Point>
</Placemark>";
	fwrite($fh, $txt);
	}
}


		fclose($fh);
		$_SESSION['runinspp'] = $rnspp;
		$_SESSION['idxx'] = $idzxx;
		$_SESSION['porfamilia'] = $pfam;
		$_SESSION['famidx'] = $ffidx;

$estilo = "background-color:#737373; font: bold 2em; color:#ffffff; border-width: thin; border-style: solid; border-color: #ccc #ccc #999 #ccc; -webkit-box-shadow: rgba(64, 64, 64, 0.5) 0 2px 5px; -moz-box-shadow: rgba(64, 64, 64, 0.5) 0 2px 5px;";	
	echo "<table align='center' valign='center' cellpadding='10' style=\"".$estilo."\" ><tr><td>Passo $step de $nsteps</td></tr>
<tr><td id='resultadoid'>....aguarde!</td></tr>
</table>";
	flush();
			
echo "
<form action='mapasKML.php' name='myform' method='post'>
  <input type='hidden' name='prepared' value='".$prepared."'>
  <input type='hidden' name='filtro' value='".$filtro."'>
  <input type='hidden' name='nsteps' value='".$nsteps."'>
  <input type='hidden' name='st1' value='".($st1-1)."'>
  <input type='hidden' name='step' value='".($step+1)."'>
  <input type='hidden' name='lati' value='".($lati)."'>
  <input type='hidden' name='llong' value='".($llong)."'>
  <input type='hidden' name='download' value='".($download)."'>
  
  
  
  <input type='hidden' name='export_filename' value='".$export_filename."'>
  <input type='hidden' name='stepsize' value='".$stepsize."'>
</form>
<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.00001);</script>";
	}
	
} // if is set prepared
elseif ($step>$nsteps) {
//echo $_SESSION['qq'];
		$fh = fopen("temp/".$export_filename, 'a') or die("nao foi possivel abrir o arquivo");
		$txt = "
</Folder>
</Folder>
</Document>
</kml>";
		fwrite($fh,$txt);
		fclose($fh);

	$qq = $_SESSION['qq'];
	$res = mysql_query($qq,$conn);
	$nnres = mysql_numrows($res);
if ($nnres>0) {
$ll = explode(";",$lati);
$llo = explode(";",$llong);

$yy = array_sum($ll)/count($ll);
$xx = array_sum($llo)/count($llo);

//imagens
$url = $_SERVER['HTTP_REFERER'];
$uu = explode("/",$url);
$nu = count($uu)-1;
unset($uu[$nu]);
$url = implode("/",$uu);
$fn = "Location: mapasKML_download.php?export_filename=".$export_filename;
if ($download==1) {
header($fn);
} else {
unset($_SESSION['qq']);
unset($_SESSION['runinspp']);
unset($_SESSION['porfamilia']);
$_SESSION['destvararray'] = serialize($pp);
echo "
<form name='sendform' action='mapasJsonShow.php' method='post'>  
<input type='hidden' value='".$yy."'  name='latcenter'>   
<input type='hidden' value='".$xx."'  name='longcenter'>   
<input type='hidden' value='".$export_filename."'  name='filename'>   
</form>";
echo "<script language=\"JavaScript\">setTimeout('document.sendform.submit()',0.00001);</script>";
}
} else {
echo "<p style='font-size: 1.5em;'>Não há informação geográfica para os dados selecionados</p>";

}

}


?>