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
$title = 'Exportar KML';
$body = '';


if (!isset($filtro)) {
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<br />
<table class='myformtable' align='center' border=0 cellpadding=\"5\" cellspacing=\"0\" >
<thead>
<tr><td colspan='100%'>Exportar dados para um arquivo KML
&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
	$help = "falta colocar ajuda aqui";
	echo " onclick=\"javascript:alert('$help');\"></td>
</tr>
</thead>
<tbody>
<form action='exportAsKML.php' method='post'>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<td colspan='100%'>
<table>
<tr><td class='tdsmallbold'>".GetLangVar('namefiltro')."</td>
<td>
  <select name='filtro'>
    <option selected value=''>".GetLangVar('nameselect')."</option>";
	$qq = "SELECT * FROM Filtros WHERE (AddedBy=".$_SESSION['userid']." OR Shared=1) ORDER BY FiltroName";
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
</tr>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<td colspan='100%'><table><tr>
	<td class='tdsmallbold'>".GetLangVar('nameformulario')." ".strtolower(GetLangVar('nameobs')."s");
	echo "&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
	$help = "selecione formulário com variáveis para colocar na info dos pontos";
	echo " onclick=\"javascript:alert('$help');\" />
	</td>
	<td class='tdformnotes'>
		<select name='formnotes' >";
		if (!empty($formid)) {
			$qq = "SELECT * FROM Formularios WHERE FormID=".$formid;
			$rr = mysql_query($qq,$conn);
			$row= mysql_fetch_assoc($rr);
			echo "<option selected value='".$row['FormID']."'>".$row['FormName']." (".$row['AddedDate'].")</option>";
		} else {
			echo "<option value=''>".GetLangVar('nameselect')."</option>";
		}
	//formularios usuario
	$qq = "SELECT * FROM Formularios WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ORDER BY FormName,Formularios.AddedDate ASC";
	$rr = mysql_query($qq,$conn);
	while ($row= mysql_fetch_assoc($rr)) {
		echo "<option value='".$row['FormID']."'>".$row['FormName']."</option>";
	}
	echo "
	</select>
	</td></tr></table>
</td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "<!---
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table align='left'>
        <tr>
          <td><input type='checkbox' name='especimes' value='1' /></td>
          <td class='tdsmallbold'>".GetLangVar('nameamostra')."s</td>
          <td><input type='checkbox' name='plantas' value='1' /></td>
          <td class='tdsmallbold'>".GetLangVar('nameplanta')."s ".strtolower(GetLangVar('namemarcada'))."s</td>
        </tr>
      </table>
  </td>
</tr>
--->";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%' align='center'><input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' /></td>
</tr>
</form>
</tbody>
</table>
</form>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
} 
else {
if (!isset($prepared)) {
	unset($_SESSION['runinspp']);
	unset($_SESSION['porfamilia']);
	$_SESSION['destvararray'] = serialize($ppost);
	unset($_SESSION['qq']);
	unset($qq);
	$qq .= " SELECT pltb.EspecimenID AS WikiEspecimenID, 
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
	IF(pltb.GPSPointID>0,pltb.GPSPointID,'') as GPSpointID,";
	//IF(pltb.GPSPointID>0,CONCAT(gazgps.GazetteerTIPOtxt,' ',gazgps.Gazetteer),IF(pltb.GazetteerID>0,CONCAT(gaz.GazetteerTIPOtxt,' ',gaz.Gazetteer),'')) as GAZETTEER_SPECIFIC,
	$qq .= "IF(pltb.GPSPointID>0,gazgps.Gazetteer,IF(pltb.GazetteerID>0,gaz.Gazetteer,'')) as GAZETTEER_SPECIFIC,
	IF(ABS(pltb.Longitude)>0,pltb.Longitude,IF(pltb.GPSPointID>0,gpspt.Longitude,IF(ABS(gaz.Longitude)>0,gaz.Longitude,muni.Longitude))) as LONGITUDE,
	IF(ABS(pltb.Longitude)>0,pltb.Latitude,IF(pltb.GPSPointID>0,gpspt.Latitude,IF(ABS(gaz.Longitude)>0,gaz.Latitude,muni.Latitude))) as LATITUDE,
	IF(ABS(pltb.Longitude)>0,pltb.Altitude,IF(pltb.GPSPointID>0,gpspt.Altitude,IF(ABS(gaz.Longitude)>0,gaz.Altitude,''))) as ALTITUDE,
	IF(ABS(pltb.Longitude)>0,'GPS-planta',IF(pltb.GPSPointID>0,'GPS',IF(ABS(gaz.Longitude)>0,'Localidade','Municipio'))) as COORD_PRECISION,
	IF(pltb.PlantaID>0,plspectb.PlantaTag,'') as TAG_PlantaMarcada,
	IF(pltb.HabitatID>0,pltb.HabitatID,IF(plspectb.HabitatID>0,plspectb.HabitatID,0)) as HabitatID,
	vernaculars(pltb.VernacularIDS) as NOME_VULGAR,
	projetostring(pltb.ProjetoID,1,0) as PROJETO,
	projetologo(pltb.ProjetoID) as PROJETOlogofile,
	labeldescricao(pltb.EspecimenID+0,pltb.PlantaID+0,".$formnotes.",FALSE,FALSE) as NOTAS";
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
	$qq .= " WHERE pltb.FiltrosIDS LIKE '%filtroid_".$filtro.";%' OR pltb.FiltrosIDS LIKE '%filtroid_".$filtro."'";
	$qq .= "ORDER BY famtb.Familia,IF(iddet.InfraEspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie,' ',infsptb.InfraEspecie),IF(iddet.EspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie),IF(iddet.GeneroID>0,gentb.Genero,famtb.Familia)))";
	$prepared = 1;
	$qz = "SELECT * FROM Especimenes WHERE FiltrosIDS LIKE '%filtroid_".$filtro.";%' OR FiltrosIDS LIKE '%filtroid_".$filtro."'";
	$rz = mysql_query($qz,$conn);
	$nrz = mysql_numrows($rz);
	$_SESSION['exportnresult'] = $nrz;
	$stepsize = 1000;
	$nsteps = ceil($nrz/$stepsize);
	$_SESSION['qq'] = $qq;
	$_SESSION['runinspp'] = 'nuncavaiteralgoassim';
	$_SESSION['idxx'] = 0;
	$_SESSION['porfamilia'] = 'tambemnuncavaiteralgoassim';
	$_SESSION['famidx'] = 0;
	$step=0;
	$export_filename = "especimenes_exportados_".$filtro.".kml";
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
	$qqq = $qq." LIMIT $st1,$stepsize";
	//echo $qqq."<br />";
	$res = mysql_query($qqq,$conn);
	$starttime = microtime(true);
	$sttime = microtime();
	if ($res) {
		if ($step==0) {
		
			$qqu = "SELECT * FROM Filtros WHERE FiltroID='".$filtro."'";
			$rqes = @mysql_query($qqu,$conn);
			$rqesw = @mysql_fetch_assoc($rqes);
			$filtronome= $rqesw['FiltroName'];
	
	
			$url = $_SERVER['HTTP_REFERER'];
			$uu = explode("/",$url);
			$nu = count($uu)-1;
			unset($uu[$nu]);
			$url = implode("/",$uu);
			$fh = fopen("temp/".$export_filename, 'w') or die("nao foi possivel gerar o arquivo");
			$hh = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<kml xmlns=\"http://www.opengis.net/kml/2.2\">
<Document>
  <name>".$filtronome."_".$_SESSION['userlastname']."_".$_SESSION['sessiondate']."</name>
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
$url1 = implode("/",$uu);
$url2 = $url1."/icons/images.jpg";
	//$tkt = "<img src=\"".$url2."\" width=20>";
	$tkt = "(".$nruq." imagens)";
} else {
	$tkt = '';
}
$txt .= 
"
<Placemark>
  <name>".$rsw['IDENTIFICADOR']."</name>
  <visibility>1</visibility>
  <description>
<![CDATA[
<font size=3>".strtoupper($rsw['FAMILY'])."</font>
<br /><font size=3>".$rsw['DETERMINACAO']." ".$tkt."</font>
";
if (!empty($rsw['detdetby'])) {
$txt .= "<br />
<font size=2>Identificado por ".$rsw['detdetby']."</font>";
}
$dethist = returnDEThistoryAStable(0,$rsw['WikiEspecimenID'],$conn);
if (count($dethist)>0) {
$txt .= "
<hr>
<font size=2><b>Histórico das identificações</b>";
foreach ($dethist as $detvv) {
	$txt .= "<br />".$detvv;
}
$txt .= "</font>";
}
if (!empty($rsw['NOME_VULGAR'])) {
$txt .= "<br />
<font size=3>Nome vulgar: ".$rsw['NOME_VULGAR']."</font><br />";
}
$txt .= "<hr>
<font size=3><b>".$rsw['COLLECTOR']." No. ".$rsw['NUMBER']."</b>
<br />&amp; ".$rsw['ADDCOLL']." em ".$rsw['DATA_COLETA']."</font><br />";
if (($rsw['INPA_NUM']+0)>0) {
$txt .= "
<font size=3>Depositado em INPA #".$rsw['INPA_NUM']."</font><br />";
}
if (!empty($rsw['TAG_PlantaMarcada'])) {
$txt .= "
<font size=2><b>Planta marcada</b>: #".$rsw['TAG_PlantaMarcada']."</font><br />";
}
$txt .= "
<hr>
<font size=2>".$rsw['LOCALIDADE']."</font>";
if ($rsw['HabitatID']>0) {
$habitat = describehabitat($rsw['HabitatID'],$img=FALSE,$conn);
$txt .= "
<hr>
<font size=2>".$habitat."</font>";
}
if (!empty($rsw['NOTAS'])) {
	$txt .= "
<hr>	
<font size=2><b>Notas</b>:".$rsw['NOTAS']."</font>";
}
$txt .= "
<hr>
<table style='border: 0;' align='center' cellpadding='3'>
<tr><td>
<img src=\"".$url1."/icons/inpa_gov.png\" width=150></td>";
if (!empty($rsw['PROJETO'])) {
	$txt .= "
<td><font size=2>".$rsw['PROJETO']."</font></td>";
}
$txt .= "
</tr></table><br />";

//imagens
$url = $_SERVER['HTTP_REFERER'];
$uu = explode("/",$url);
$nu = count($uu)-1;
unset($uu[$nu]);
$url = implode("/",$uu);
$urlbig = $url."/img/originais/";
$url = $url."/img/lowres/";
if ($nruq>0) {
$txt .= "
<hr>
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
<tr><td><a href=\"".$urlbig.$rusqw['FileName']."\"><img src=\"".$url.$rusqw['FileName']."\" width=300></a><br /></td></tr>";
		$txt .= $tutx;
	}
}
$txt .= "
</table>
<hr>";
}
$txt .= "
]]>
  </description>
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
		$endtime = microtime(true); 
		$exectime = $endtime-$starttime;
		$exectime = round(($exectime*100)/60,4);
		if ($step==0) {
			$tfalta = ceil($exectime*$nsteps);
		} else {
			$tfalta = $tfalta-$exectime;
		}
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<form action='exportAsKML.php' name='myform' method='post' />
  <input type='hidden' name='prepared' value='".$prepared."' />
  <input type='hidden' name='filtro' value='".$filtro."' />
  <input type='hidden' name='nsteps' value='".$nsteps."' />
  <input type='hidden' name='st1' value='".($st1-1)."' />
  <input type='hidden' name='step' value='".($step+1)."' />
  <input type='hidden' name='export_filename' value='".$export_filename."' />
  <input type='hidden' name='stepsize' value='".$stepsize."' />
  <input type='hidden' name='tfalta' value='".$tfalta."' />
<br />
<table align='center' cellpadding='5' width='50%' class='erro'>
  <tr><td>Processando passo ".($step+1)." de ".($nsteps+1)."  AGUARDE!</td></tr>
  <tr><td>Faltam aproximadamente ".$tfalta."  minutos para terminar</td></tr>
</table>
</form>
<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.00001);</script>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
	}
} // if is set prepared
elseif ($step>$nsteps) {
		$fh = fopen("temp/".$export_filename, 'a') or die("nao foi possivel abrir o arquivo");
		$txt = "
</Folder>
</Folder>
</Document>
</kml>";
		fwrite($fh,$txt);
		fclose($fh);
		if (file_exists("temp/".$export_filename)) {
			header("location: exportAsKML_result.php?export_filename=$export_filename");
		} else {
			header("location: exportAsKML.php");
		}
}
}

?>