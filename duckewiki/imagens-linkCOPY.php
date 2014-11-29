<?php
//este script checa images importadas ao banco de dados mas que nao foram relacionadas com nada e permite criar uma relacao, buscando relacoes que tem a mesma data
//permite ligar com uma amostra coletada, com uma planta marcada ou com um habitat
//precisa modificar o script para fazer outros tipos de relacao que nao foram ainda implementados
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
$menu = FALSE;

$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' href='javascript/magiczoomplus/magiczoomplus/magiczoomplus.css' type='text/css' media='screen' />"
);
$which_java = array(
"<script src='javascript/magiczoomplus/magiczoomplus/magiczoomplus.js' type='text/javascript'></script>"
);
$title = 'Checar link de imagens';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);


$qq = "SELECT * FROM Imagens WHERE checkunlinkedimgs(ImageID)=0 AND AddedBy='".$uuid."' LIMIT 0,1";
$res = mysql_query($qq,$conn);
$nres = mysql_numrows($res);
if ($nres) {
	$rw = mysql_fetch_assoc($res);
	$imgid = $rw['ImageID'];

	$bydate = 1;
	$filename = $row['FileName'];
	$imgdate = $row['DateOriginal'];
		
	$ff = explode("_",$filename);
	unset($ff[0]);
	unset($ff[1]);
	$ff = implode("_",$ff);
	$dirthumb = 'img/thumbnails/';
	$dirmedium = 'img/lowres/';
	$dirlarge = 'img/copias_baixa_resolucao/';
	$diroriginal = 'img/originais/';
}

echo "
<div id=\"imagedisplay\" style=\"background-color:#EEEEEE;height:400px;width:50%;float:left;padding: 10px;\">
    <div class='imagecontainer'>
       <a href=\"".$dirlarge.$filename."\" class='MagicZoomPlus'  rel=\"zoom-position:right;zoom-height:200px; zoom-fade:true; smoothing-speed:17;opacity-reverse:true;\" >
       <img style='border: thin solid gray;'  height=\"60\" src=\"".$dirthumb.$filename."\" alt='Imagem' /></a>
      </a>
      <br />
    </div>
</div>
<div id=\"linkform\" style=\"background-color:#FFA500;height:400px;width:30%;float:left;padding: 10px;\">
<strong>Variável</strong>:<br/>
<select name='traitid'>
<option value=''>".GetLangVar('nameselect')."</option>";
	$filtro ="SELECT * FROM Traits WHERE TraitTipo='Variavel|Imagem' ORDER BY TraitName";
	$resaa = mysql_query($filtro,$conn);
	while ($aa = mysql_fetch_assoc($resaa)){
		   echo "
		   <option value='".$aa['TraitID']."'>".$aa['TraitName']."</option>";
	}
	echo "
</select>
<br /><br/>
<strong>Amostra coletada</strong>:&nbsp;<img height=14 src=\"icons/icon_question.gif\"";
$help = 'Especímenes coletados na mesma data que a imagem foi tirada';
echo " onclick=\"javascript:alert('$help');\" /><br/>
<select name='especimenid'>
  <option value=''>".GetLangVar('nameselect')."</option>";
$qq  = "SELECT 
pltb.EspecimenID, 
CONCAT(colpessoa.SobreNome,' ', pltb.Number) as COLETOR_NO, 
famtb.Familia as FAMILIA,
gettaxonname(pltb.DetID,1,0) as NOME,
(IF(pltb.GPSPointID>0,gazgps.PathName,IF(pltb.GazetteerID>0,gaz.PathName,' '))) as LOCAL
FROM Especimenes as pltb 
LEFT JOIN Pessoas as colpessoa ON pltb.ColetorID=colpessoa.PessoaID 
LEFT JOIN Identidade as iddet USING(DetID) 
LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID 
LEFT JOIN Gazetteer as gaz ON gaz.GazetteerID=pltb.GazetteerID  
LEFT JOIN GPS_DATA as gpspt ON gpspt.PointID=pltb.GPSPointID  
LEFT JOIN Gazetteer as gazgps ON gpspt.GazetteerID=gazgps.GazetteerID  
";
if ($bydate==1) {
		$qq .=  "WHERE CONCAT(pltb.Ano,'-',pltb.Mes,'-',pltb.Day)='".$imgdate."'";
}
$qq .= '  ORDER BY FAMILIA,gettaxonname(pltb.DetID,1,0), colpessoa.SobreNome, pltb.Number';
$resaa = mysql_query($qq,$conn);
while ($aa = mysql_fetch_assoc($resaa)){
		   echo "
  <option value='".$aa['EspecimenID']."'>".$aa['COLETOR_NO']." [".$aa['FAMILIA']." ".$aa['NOME']."] [.".$aa['LOCAL']."]</option>";
	}
	echo "
</select>
<br /><br/>
<strong>Planta marcada</strong>:&nbsp;<img height=14 src=\"icons/icon_question.gif\"";
$help = 'Plantas marcadas na mesma data em que a imagem foi tirada!';
echo " onclick=\"javascript:alert('".$help."');\" /><br/>
<select name='plantaid'>
<option value=''>".GetLangVar('nameselect')."</option>";
	$filtro ="SELECT * FROM Traits WHERE TraitTipo='Variavel|Imagem' ORDER BY TraitName";
	$resaa = mysql_query($filtro,$conn);
	while ($aa = mysql_fetch_assoc($resaa)){
		   echo "
		   <option value='".$aa['TraitID']."'>".$aa['TraitName']."</option>";
	}
	echo "
</select>
<br /><br/>
<strong>Habitat</strong>:&nbsp;<img height=14 src=\"icons/icon_question.gif\"";
$help = 'Uma classe de habitat ou um habitat local ligado a um ponto de GPS que tem a mesma data da imagem';
echo  " onclick=\"javascript:alert('$help');\" /><br/>
<select name='habitatid'>
  <option value=''>".GetLangVar('nameselect')."</option>";
		$qq = "SELECT hab.HabitatID,hab.PathName as habitat,gazgps.PathName as gazeta,gps.Name as gpspt FROM Habitat as hab LEFT JOIN GPS_DATA as gps ON hab.GPSPointID=gps.PointID LEFT JOIN Gazetteer as gazgps ON gps.GazetteerID=gazgps.GazetteerID  WHERE hab.HabitatTipo='Class'";
	if ($bydate==1) {
	  $qq .= " OR gps.DateOriginal='".$imgdate."'";
	}
	$qq .= " ORDER BY hab.PathName,gazgps.PathName";
	$wr = mysql_query($qq,$conn);
	$nw = mysql_numrows($wr);
	if ($nw>0) {
		while ($aa = mysql_fetch_assoc($wr)){
			if (!empty($aa['gpspt']) && !empty($aa['gpspt'])) {
				$ta = " [".$aa['gpspt']." - ".$aa['gazeta']."] ";
			} else {
				$ta = "";
			}
			echo "
  <option value='".$aa['HabitatID']."'>".$aa['habitat'].$ta."</option>";
}
	}
	echo  "
</select>
<br /><br/>
<strong>Georeferência</strong>:&nbsp;<img height=14 src=\"icons/icon_question.gif\"";
$help = 'Extrair de dados de GPS as coordenadas da imagen. Aparece uma lista se houver dados de GPS';
echo  " onclick=\"javascript:alert('$help');\" /><br/>";
$qq = "SELECT Gazetteer, COUNT(*) as NPts FROM GPS_DATA as gps LEFT JOIN Gazetteer as gazgps ON gps.GazetteerID=gazgps.GazetteerID GROUP BY Gazetteer,DateOriginal
";
if ($bydate==1) {
	  $qq .= " WHERE gps.DateOriginal='".$imgdate."'";
}
echo "
</div>
<div id='buttons' ></div>
";


$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>