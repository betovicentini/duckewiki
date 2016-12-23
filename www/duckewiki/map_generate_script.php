<?php
//Start session
ini_set("memory_limit","-1");
ini_set("mysql.allow_persistent","-1");

session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
@extract($gget);

$formnotes = 0;
$pp = array('formnotes'=> $formnotes, 'especimes'=> $especimes, 'plantas' => $plantas);
$basesql = "SELECT pltb.EspecimenID AS WikiEspecimenID, 
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
getlatlongdms(pltb.Latitude+0, pltb.Longitude+0, pltb.GPSPointID, pltb.GazetteerID, pltb.MunicipioID, pltb.ProvinceID, pltb.CountryID, 1,5)  as LATITUDE,
getlatlongdms(pltb.Latitude+0, pltb.Longitude+0, pltb.GPSPointID, pltb.GazetteerID, pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 0,5)  as LONGITUDE,
IF(ABS(pltb.Longitude)>0,pltb.Altitude,IF(pltb.GPSPointID>0,gpspt.Altitude,IF(ABS(gaz.Longitude)>0,gaz.Altitude,''))) as ALTITUDE,
IF(ABS(pltb.Longitude)>0,'GPS-planta',IF(pltb.GPSPointID>0,'GPS',IF(ABS(gaz.Longitude)>0,'Localidade','Municipio'))) as COORD_PRECISION,
IF(pltb.PlantaID>0,plspectb.PlantaTag,'') as TAG_PlantaMarcada,
IF(pltb.HabitatID>0,pltb.HabitatID,IF(plspectb.HabitatID>0,plspectb.HabitatID,0)) as HabitatID,
vernaculars(pltb.VernacularIDS) as NOME_VULGAR,
projetostring(pltb.ProjetoID,1,0) as PROJETO,
projetologo(pltb.ProjetoID) as PROJETOlogofile,
labeldescricao(pltb.EspecimenID+0,pltb.PlantaID+0,".$formnotes.",FALSE,FALSE) as NOTAS";
//	IF(pltb.GPSPointID>0,CONCAT(gazgps.GazetteerTIPOtxt,' ',gazgps.Gazetteer),IF(pltb.GazetteerID>0,CONCAT(gaz.GazetteerTIPOtxt,' ',gaz.Gazetteer),'')) as GAZETTEER_SPECIFIC,
$basesql .= " FROM Especimenes as pltb 
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
";

//FAZ O LOOP PARA TODAS OS ESPECIMENES 
//SELECIONA OS ESPECIMENSIDS
$sql = "SELECT EspecimenID FROM Especimenes ";
if ($projetoid>0) {
$sql .= " JOIN ProjetosEspecs as prj ON prj.EspecimenID=pltb.EspecimenID WHERE prj.ProjetoID=".$projetoid;
}
//$sql .= " ORDER BY Ano,Mes,Day DESC";
$sql .= " ORDER BY Ano,Mes,Day DESC";
$ores = mysql_query($sql,$conn);
$nores = mysql_numrows($ores);
if ($nores>0) {
$step=0;
$idzxx =0;
$rnspp = "onomequalquer"; 
$ffidx = 0;
$pfam = 'tambemnuncavaiteralgoassim';
$export_filename = "checklist_map.kml";
$export_filename_settings = "checklist_map_sets.json";
while($oresw = mysql_fetch_assoc($ores)) {
	$qqq = $basesql."  WHERE pltb.EspecimenID=".$oresw['EspecimenID'];
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
  <description>INSTITUTO NACIONAL DE PESQUISAS DA AMAZÔNIA (INPA), Manaus, Brasil. \nArquivo gerado por ".$url." ".$_SESSION['userfirstname']." ".$_SESSION['userlastname']." em ".$_SESSION['sessiondate']." pelo duckewiki</description>
  ";
			fwrite($fh, $hh);
		} 
		else {
			$fh = fopen("temp/".$export_filename, 'a') or die("nao foi possivel abrir o arquivo");
		}
		$nff = 0;
		//pega os dados da amostra
		$rsw = mysql_fetch_assoc($res);
		
		//pega imagens da amostra
		$quq = "SELECT TraitVariation FROM Traits_variation as trv JOIN Traits as tr USING(TraitID) WHERE tr.TraitTipo='Variavel|Imagem' AND trv.EspecimenID=".$oresw['EspecimenID']." ORDER BY tr.TraitName";
		$ruq = mysql_query($quq,$conn);
		$nruq = mysql_numrows($ruq);

		//checa coordenadas
		$ll = $rsw['LONGITUDE']+0;
		//adiciona o registro se houver  longitude diferente de 0
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
		//pega o nome da especie
		$onome = trim($rsw['NOME']);
		//da familia
		$newfam = trim($rsw['FAMILY']);

		//comeca a escrever o arquivo
		$txt = '';
		
		//se a familia mudou, fecha o folder (vai fazer sentido se estiver ordenado por familia
		if ($pfam!=$newfam && $ffidx>0) {
			$txt .= "
</Folder>
</Folder>";
			$nff = 1;
		} else {
			$nff = 0;
		}
		//se a familia mudou coloca a pasta da familia
		if ($pfam!=$newfam) {
			$txt .= "
<Folder>
<name>".$rsw['FAMILY']."</name>
  <open>0</open>";
  			//ajusta o indice da familia
			$ffidx++;
			$pfam = $newfam;
		}
		//se o nome da especie mudou
		if ($rnspp!=$onome) {
			//e  po indice nao é inicial, entao esta fechando o folder da especie anterior
			if ($idzxx>0 && $nff==0) {
				$txt .= "
</Folder>";
			} 
			//abre a pasta com o nome da especie
			$txt .= "
<Folder>
<name>".$rsw['NOME']."</name>
  <open>0</open>";
			$idzxx++;
			$rnspp = $onome;
		}
		//se tem imagens
		if ($nruq>0) {
			$url = $_SERVER['HTTP_REFERER'];
			$uu = explode("/",$url);
			$nu = count($uu)-1;
			unset($uu[$nu]);
			$url = implode("/",$uu);
			$url2 = $url."/icons/images.jpg";
			$tkt = "(".$nruq." imagens)";
		} else {
			$tkt = '';
		}
		//algumas definicoes do registro da amostra
		$tbwidth = 300;
		$titlefont = "1em";
		$txtfont = "0.8em";

		//$idd = RemoveAcentos(substr($rsw['COLLECTOR'],0,3)."\n".$rsw['NUMBER']);
		//coloca o registro da amostra dentro da pasta da especie
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

//adiciona o link para as imagens se houver imagens
//pega o caminho do servidor da base onde o arquivo foi gerado
$url = $_SERVER['HTTP_REFERER'];
$uu = explode("/",$url);
$nu = count($uu)-1;
unset($uu[$nu]);
$url = implode("/",$uu);
//caminhos dos arquivos originais e baixa resolucao
$urlbig = $url."/img/originais/";
$urllowres = $url."/img/lowres/";
//havendo imagens
if ($nruq>0) {
$txt .= "
<tr ><td><hr></td></tr>
<tr><td>
<table style='border: 0;' align='center' cellpadding='10'>";
//adiciona cada imagem 
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

//fecha a descricao adicionando as coordenadas
$txt .= "
</table>
]]>
  </description>
  <nimgs>".$nruq."</nimgs>
  <Point>
<coordinates>".$rsw['LONGITUDE'].",".$rsw['LATITUDE'].",".$rsw['ALTITUDE']."</coordinates>
  </Point>
</Placemark>";
//escreve no arquivo
fwrite($fh, $txt);
//fecha o arquivo
fclose($fh);
}
}
	if (isset($pgfilename)) {
		$pgfn = fopen("temp/".$pgfilename, 'w');
		$perc = round(($step/$nores)*99,5);
		fwrite($pgfn, $perc);
		fclose($pgfn);
	}
	$step++;
} // para cada amostra

//fecha o arquivo kml
$fh = fopen("temp/".$export_filename, 'a') or die("nao foi possivel abrir o arquivo");
$txt = "
</Folder>
</Folder>
</Document>
</kml>";
fwrite($fh,$txt);
fclose($fh);

//pega o centroid de todas as amostras
$ll = explode(";",$lati);
$llo = explode(";",$llong);
$yy = array_sum($ll)/count($ll);
$xx = array_sum($llo)/count($llo);

//salva isso num arquivo
$fh = fopen("temp/".$export_filename_settings, 'w') or die("nao foi possivel abrir o arquivo");
$tt = array("latcenter" => $xx, "longcenter" => $yy);
$tt = json_encode($tt);
fwrite($fh,$tt);
fclose($fh);
echo "<p style='font-size: 1.5em;'>Os arquivos foram salvos!</p>";
} 
else {
echo "<p style='font-size: 1.5em;'>Não há informação geográfica para os dados selecionados</p>";
}
?>