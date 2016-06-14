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
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' >"
);
$which_java = array("<script type='text/javascript' src='javascript/ajax_framework.js'></script>");
$title = 'Entrar dados via tabela';
$body = '';
$menu = FALSE;
FazHeader($title,$body,$which_css,$which_java,$menu);

//SE ESTIVER FINALIZANDO
$erro=0;
if ($final==1 && !isset($cadastrar)) {
	if ((empty($pessoaid)  || empty($colnum1) || empty($colnum2) || empty($datacol)) && empty($localidadeid) && empty($search_gps_waypoints)) {
echo "
<br />
<ol style='color: red; font-size: 1.3em; margin-left: 25%' >".GetLangVar('erro1')."";
			if (empty($datacol)) {
				echo "
<li >".GetLangVar('namedata')."</li>";
			}
			if (empty($pessoaid)) {
				echo "
<li >".GetLangVar('namecoletor')."</li>";
			}
			if (empty($colnum1) || empty($colnum2)) {
				echo "
<li >Números para gerar a série</li>";
			}
			if (empty($localidadeid) && empty($search_gps_waypoints)) {
				echo "
<li >Localidade é obrigatória mesmo indicando busca por waypoints de GPS.</li>";
			}
			echo "
</ol>
";
		$erro++;
	} 
	$numerodecols = $colnum2-$colnum1;
	if ($colnum1>$colnum2 || $colnum1==0) {
		$erro++;
		echo "
<br />
<ol style='color: red; font-size: 1.3em; margin-left: 25%' >Valores Incorretos
<li >Nos valores de números da série de coleta</li>
</ol>";
		}
	if ($erro==0) { 
		//PEDE CONFIRMACAO DO CADASTRO
		echo "
<form action='batchenter_especimenes_form.php' method='post'>
<input type='hidden'  name='cadastrar'  value=1  >";
foreach($ppost as $kk => $vv) {
	echo "
<input type='hidden'  name='".$kk."'  value='".$vv."'  >";
}
echo "
<br />
<table align='center'  width='50%'  >
<tr>
<td style='color: red; font-size: 1.3em; ' align='center' >
Confirme entrada em série de dados&nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Ao confirmar os novos registros da série serão adicionados à base de dados. Para adicionar informações de identificação e notas específicas de cada localidade buscar pelos registros na planilha ESPECIMENES e editar de acordo';
echo " onclick=\"javascript:alert('$help');\" />
</td>
</tr>
<tr>
<td align='center'>
<input type='submit'  value=\"Adicionar ".($numerodecols+1)." especimenes\"  style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  >
</td>
</tr>
</table>
</form>";
	} 
}
//SE TIVER CONFIRMADO O CADASTRO
if ($cadastrar==1) {
$jaexiste = array();
$temmaisdeumwaypt = array();
$sucesso = array();
$erro=0;
$warnings=0;
//PARA CADA NUMERO DE COLETA
for($curcolnum=$colnum1;$curcolnum<=$colnum2;$curcolnum++) {
	//CHECA SE JÁ EXISTE UM REGISTRO DESTE COLETOR COM ESTE NUMERO
	$qq = "SELECT * FROM Especimenes WHERE ColetorID='".$pessoaid."' AND UPPER(Number)='".$curcolnum."'";
	$res = mysql_query($qq,$conn);
	$nres = @mysql_numrows($res);
	if ($nres>0) {
		$jaexiste[] = $curcolnum;
		$erro++;
	} 
	//caso contrario se nao houver registro
	if ($erro==0) { 
		$data = explode("-",$datacol);
		$colyear = $data[0];
		$colmonth = $data[1];
		$colday = $data[2];
		$arrayofvalues = array(
			'ColetorID' => $pessoaid,
			'AddColIDS' => $addcolvalue
		);
		$locid = explode("_",$localidadeid);
		if ($locid[0]=='gazetteer') {
				$arvgaz = array('GazetteerID' => $locid[1]);
		} 
		elseif ($locid[0]=='municipio') {
			$arvgaz = array('GazetteerID' => 0, 'MunicipioID' => $locid[1]);
		} 
		elseif ($locid[0]=='province') {
			$arvgaz = array('GazetteerID' => 0, 'MunicipioID' => 0, 'ProvinceID' => $locid[1]);
		} 
		elseif ($locid[0]=='country') {
			$arvgaz = array('GazetteerID' => 0, 'MunicipioID' => 0,  'ProvinceID' => 0, 'CountryID' => $locid[1]);
		}
		$gpspointid=0;
		if ($search_gps_waypoints==1) {
				$qgis = "SELECT PointID FROM GPS_DATA WHERE (cleansppname(Name)+0)=".$curcolnum."  AND DateOriginal='".$datacol."'   AND (Type LIKE 'Waypoint')";
				//echo $qgis."<br />";
				$rgis = mysql_query($qgis,$conn);
				$nrgis = mysql_numrows($rgis);
				if ($nrgis==1) {
						$rowgis = mysql_fetch_assoc($rgis);
						$gpspointid = $rowgis['PointID'];
				} elseif ($nrgis>1) {
					$temmaisdeumwaypt[] = $curcolnum;
					$warnings++;
				}
		}
		if ($gpspointid==0) {
			$arrayofvalues = array_merge((array)$arrayofvalues,(array)$arvgaz);
		}
		$arv = array(
			'Number' => $curcolnum,
			'Day' => $colday,
			'Mes' => $colmonth,
			'Ano' => $colyear,
			'HabitatID' => $habitatid,
			'GPSPointID' => $gpspointid,
			'ProjetoID' => $projetoid);
		$arrayofvalues = array_merge((array)$arrayofvalues,(array)$arv);
		$especimenid = InsertIntoTable($arrayofvalues,'EspecimenID','Especimenes',$conn);
		if ($especimenid>0) {
		$sql = "INSERT INTO checklist_speclist (SELECT  pltb.GazetteerID, pltb.GPSPointID, pltb.EspecimenID,  pltb.PlantaID,  thepl.PlantaTag, pltb.DetID, (colpessoa.Abreviacao) as COLETOR,  pltb.Number as NUMERO, if(CONCAT(pltb.Ano,'-',pltb.Mes,'-',pltb.Day)<>'0000-00-00',CONCAT(pltb.Ano,'-',pltb.Mes,'-',pltb.Day),'FALTA') as DATA, if(pltb.INPA_ID>0,pltb.INPA_ID+0,NULL) as ".$herbariumsigla.", famtb.Familia as FAMILIA, acentosPorHTML(gettaxonname(pltb.DetID,1,0)) as NOME, acentosPorHTML(gettaxonname(pltb.DetID,1,1)) as NOME_AUTOR, emorfotipo(pltb.DetID,0,0) as MORFOTIPO, 
localidadefields(pltb.GazetteerID, pltb.GPSPointID, pltb.MunicipioID, pltb.ProvinceID, pltb.CountryID, 'COUNTRY') as PAIS,  
localidadefields(pltb.GazetteerID, pltb.GPSPointID, pltb.MunicipioID, pltb.ProvinceID, pltb.CountryID, 'MAJORAREA') as ESTADO,
localidadefields(pltb.GazetteerID, pltb.GPSPointID, pltb.MunicipioID, pltb.ProvinceID, pltb.CountryID, 'MINORAREA') as MUNICIPIO,
localidadefields(pltb.GazetteerID, pltb.GPSPointID, pltb.MunicipioID, pltb.ProvinceID, pltb.CountryID, 'GAZETTEER') as LOCAL,
localidadefields(pltb.GazetteerID, pltb.GPSPointID, pltb.MunicipioID, pltb.ProvinceID, pltb.CountryID, 'GAZETTEER_SPEC') as LOCALSIMPLES,  
getlatlong(pltb.Latitude , pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID, 0, 0, 0, 0) as LONGITUDE, getlatlong(pltb.Latitude , pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID, 0, 0, 0, 1) as LATITUDE, IF(ABS(pltb.Longitude)>0,pltb.Altitude+0,IF(pltb.GPSPointID>0,gpspt.Altitude+0,IF(ABS(gaz.Longitude)>0,gaz.Altitude+0,NULL))) as ALTITUDE, 'edit-icon.png' AS EDIT, 'mapping.png' AS MAP,  '' as OBS, IF(pltb.HabitatID>0,'environment_icon.png','') as HABT, habitaclasse(pltb.HabitatID) AS HABT_CLASSE, IF (checkimgs(pltb.EspecimenID, pltb.PlantaID)>0,'camera.png','') as IMG, checknir(pltb.EspecimenID,pltb.PlantaID) as NIRSpectra, "; 
if ($duplicatesTraitID>0) {$sql .= " traitvaluespecs(".$duplicatesTraitID.", pltb.PlantaID, pltb.EspecimenID,'', 0, 0)+0 as DUPS,";}
if ($daptraitid>0) { $sql .= " traitvaluespecs(".$daptraitid.", pltb.PlantaID, pltb.EspecimenID,'mm', 0, 1)+0 as DAPmm,";}
if ($alturatraitid>0) { $sql .= " traitvaluespecs(".$alturatraitid.", pltb.PlantaID, pltb.EspecimenID,'mm', 0, 1)+0 as ALTURA,"; }
if ($habitotraitid>0) { $sql .= " (traitvaluespecs(".$habitotraitid.", pltb.PlantaID, pltb.EspecimenID,'', 0, 1)) as HABITO,"; }
if ($traitfertid>0) { $sql .= " (traitvaluespecs(".$traitfertid.", pltb.PlantaID, pltb.EspecimenID,'', 0, 1)) as FERTILIDADE,"; }
$sql .= " IF(projetologo(pltb.ProjetoID)<>'',projetologo(pltb.ProjetoID),'') as PRJ, acentosPorHTML(IF(projetostring(pltb.ProjetoID,0,0)<>'',projetostring(pltb.ProjetoID,0,0),'NÃO FOI DEFINIDO')) as PROJETOstr";
$sql .= " FROM Especimenes as pltb LEFT JOIN Plantas as thepl ON thepl.PlantaID=pltb.PlantaID LEFT JOIN Pessoas as colpessoa ON pltb.ColetorID=colpessoa.PessoaID LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID  LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID  LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID  LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID   LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID  LEFT JOIN Gazetteer as gaz ON gaz.GazetteerID=pltb.GazetteerID   LEFT JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID   LEFT JOIN Province as provgaz ON muni.ProvinceID=provgaz.ProvinceID   LEFT JOIN Country  as countrygaz ON provgaz.CountryID=countrygaz.CountryID   LEFT JOIN GPS_DATA as gpspt ON gpspt.PointID=pltb.GPSPointID   LEFT JOIN Gazetteer as gazgps ON gpspt.GazetteerID=gazgps.GazetteerID   LEFT JOIN Municipio as munigps ON gazgps.MunicipioID=munigps.MunicipioID  LEFT JOIN Province as provigps ON munigps.ProvinceID=provigps.ProvinceID   LEFT JOIN Country  as countrygps ON provigps.CountryID=countrygps.CountryID";
$sql .= " WHERE pltb.EspecimenID='".$especimenid."')";
		//echo $sql." <br />";
		@mysql_query($sql,$conn);
		$sucesso[] = $curcolnum;
		}  
	}
}
if (count($jaexiste)>0) {
		echo "
<br />
<span style='color: red; font-size: 1.1em; margin-left: 25%' width='50%'  >
Já existem registros para os seguintes números de coleta que não foram cadastrados:
<textarea  style='background-color: #E0E0E0; color: red; font-size: 1.1em; margin-left: 25%'  cols=50 rows=5>";
foreach($jaexiste as $vv) {
	echo "
".$vv;
}
echo "
</textarea>
</span>
";
}
if (count($temmaisdeumwaypt)>0) {
		echo "
<br />
<ol style='color: green; font-size: 1.1em; margin-left: 25%' >
Não foi possível ligar ponto de gps para os seguintes registros porque HÁ MAIS DE UM WAYPOINT COM ESSE  NOME na data indicada:";
foreach($temmaisdeumwaypt as $vv) {
	echo "
<li >".$vv."</li>";
}
echo "
</ol>";
}
$ns = count($sucesso);
if ($ns>0) {
echo "
<br />
<span style='color: blue; font-size: 1.1em; margin-left: 25% width='50%' >
".$ns."  especimenes foram adicionados. Para identificação e notas para cada registro, buscar por eles na planilha ESPECIMENES</span>
<br />";
}
} //FINALIZA O CADASTRO

//////////////////////////////////////////////////////////////////////////////////////////////////////////
//CASO ESTEJA INICIANDO OU TENHA OCORRIDO UM ERRO
//$erro=1;
if (!isset($final) || ($erro>0 & !isset($cadastrar))) {

echo "
<form name='coletaform' action='batchenter_especimenes_form.php' method='post'>
<br />
<table class='myformtable' align='left' cellpadding=\"7\">
<thead>
<tr >
<td colspan='2' >Entrar dados para vários novos especímenes de um coletor</td></tr>
</thead>
<tbody>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor = '".$bgcolor."'>
      <td class='tdsmallboldright'>".GetLangVar('namecoletor')."&nbsp;<img src='icons/list-add.png' height='15' ";
		$myurl ="novapessoa-form-popup.php?pessoaid_val=coletorid"; 
		echo " onclick = \"javascript:small_window('$myurl',500,350,'Nova Pessoa');\"></td>
      <td class='tdsmallnotes'>
        <select id='coletorid' name='pessoaid'>";
			if (empty($pessoaid)) {
				echo "
          <option value='' class='optselectdowlight'>".GetLangVar('nameselect')."</option>";
			} 
			else {
				$rr = getpessoa($pessoaid,$abb=FALSE,$conn);
				$row = mysql_fetch_assoc($rr);
				echo "
          <option selected class='selectedval' value=".$row['PessoaID'].">".$row['Abreviacao']." [".$row['Prenome']."]</option>";
			}
			$rrr = getpessoa('',$abb=TRUE,$conn);
			while ($row = mysql_fetch_assoc($rrr)) {
				echo "
          <option value=".$row['PessoaID'].">".$row['Abreviacao']." [".$row['Prenome']."]</option>";
			}
			echo "
        </select>
      </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>Coletores adicionais</td>
  <td >
    <table>
      <tr>
        <td class='tdformnotes' ><textarea name='addcoltxt' id='addcoltxt'  cols=60 rows=3 readonly>".$addcoltxt."</textarea></td>
        <td>
          <input type='hidden' id='addcolvalue'  name='addcolvalue' value='$addcolvalue' />
          <input type=button value=\"SELECIONAR\" style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"     ";
          $myurl ="addcollpopup.php?valuevar=addcolvalue&addcoltxt=addcoltxt&getaddcollids=".$addcolvalue."&formname=coletaform"; 
		echo " onclick = \"javascript:small_window('$myurl',800,500,'Seleciona Coletores Adicionas');\" /></td>
      </tr>
    </table>
  </td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor = '".$bgcolor."'>
   <td colspan='2'>
        <table>
          <tr>
            <td class='tdsmallboldright'>Número Inicial da série:</td>
            <td><input type='text' name='colnum1' value='".$colnum1."' size='12' /></td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            <td class='tdsmallboldright'>Número Final da série:</td>
            <td><input type='text' name='colnum2' value='".$colnum2."' size='12' /></td>
          </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>Data de coleta</td>
  <td>
    <table>
      <tr>
        <td><input name=\"datacol\" value=\"".$datacol."\" size=\"15\" readonly /></td>
        <td><a onclick=\"if(self.gfPop)gfPop.fPopCalendar(document.forms['coletaform'].datacol,[[1800,01,01],[2020,01,01]]);return false;\" ><img name=\"popcal\" align=\"absmiddle\" src=\"calendar/calbtn.gif\" width=\"34\" height=\"22\" border=\"0\" alt=\"\"></a></td>
        </tr>
    </table>
  </td>
</tr>";
//dados de localidade
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
if (empty($localtxt) & !empty($localidadeid)) {
			$locid = explode("_",$localidadeid);
			if ($locid[0]=='gazetteer') {
				$arv = array('gazetteerid' => $locid[1]);
			} elseif ($locid[0]=='municipio') {
				$arv = array();
				$arv = array('gazetteerid' => 0, 'muniid' => $locid[1]);
			} elseif ($locid[0]=='province') {
				$arv = array('gazetteerid' => 0, 'muniid' => 0, 'provid' => $locid[1]);
			} elseif ($locid[0]=='country') {
				$arv = array('gazetteerid' => 0, 'muniid' => 0,  'provid' => 0, 'countid' => $locid[1]);
			}
			@extract($arv);
	       $qq = "SELECT localidadestring(".($gazetteerid+0).",".($gpspointid+0).",".($muniid+0).",".($provid+0).",".($countid+0).",0, 0, 0) as locality";
		//echo $qq."<br>";
		$riq = mysql_query($qq,$conn);
		$riw = mysql_fetch_assoc($riq);
		$localtxt = $riw['locality'];
}
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>".GetLangVar('namelocalidade')."</td>
  <td>
  <table>
    <tr>
      <td class='tdsmallboldright'>Localidade&nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'ESCOLHER UMA LOCALIDADE - se for uma localidade dentro do município e você for cadastrar como nova, procure cadastrar apenas a informação chave da localidade e deixe detalhes para NOTAS DE LOCALIDADE. Por exemplo, pode cadastrar Comunidade Seringalzinho com sublocalidade de Parque Nacional do Jaú e colocar em NOTAS coisas como, Trilha atrás da comunidade, Campina próxima à comunidade, etc.  Detalhes de localidade podem ser colocados na variável notas de localidade no formulário de edição de cada especímene';
echo " onclick=\"javascript:alert('$help');\" /></td>
      <td>"; 
		autosuggestfieldval5('search-localidadeseadmin.php','locality',$localtxt,'localres','localidadeid',$localidadeid,true,60,'País, provincia , município ou localidade cadastrada');
		 $myurl = "localidade_dataexec.php?ispopup=1";
		echo "
      &nbsp;<span style=' font-size: 0.8em; font-weight: bold; color: red;' >*selecione digitando...ou adicione uma <input type=button style=\"color:#4E889C; font-size: 1em; font-weight:bold; padding: 1px; cursor:pointer;\"    value='".GetLangVar('namenova')."'  onclick =\"javascript:small_window('".$myurl."',900,300,'Cadastrar nova localidade');\" /> localidade</span></td>";
		echo "
		</tr>
		<tr>
      <td colspan='4' >&nbsp;</td>
	</tr>
    <tr>
      <td class='tdsmallboldright'>Busca&nbsp;por&nbsp;GPS_waypoints&nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Neste caso procura no sistema pontos importados de um GPS que contenha o mesmo número que o número de coleta nas série e data indicadas. Se encontrar 1 registro, liga à amostra; senão, usa a localidade indicada';
echo " onclick=\"javascript:alert('$help');\" /></td>
      <td colspan='3' ><input type='checkbox'  name='search_gps_waypoints'  ";
      if ($search_gps_waypoints==1) {
      echo "checked";
      } 
      echo "  value='1'  /></td>
  </tr>
  </table>
</td>
</tr>
";
//habitat descricao
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>Hábitat</td>
  <td >
    <table align='left' cellpadding=\"7\" cellspacing=\"0\" class='tdformnotes'>
      <input type='hidden' id='habitatidfield'  name='habitatid' value='".$habitatid."' />
      <tr>
        <td id='habitatfield' class='tdformnotes'>$habitat</td>
        <td align='center'><input type='button' value='SELECIONAR' style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"    onclick = \"javascript:small_window('habitat-popup.php?ispopup=1&pophabitatid=$habitatid&elementidval=habitatidfield&elementidtxt=habitatfield&opening=1',850,400,'Selecione um habitat');\" /></td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>".GetLangVar('nameprojeto')."</td>
  <td >
 <select style=\"color:#4E889C; font-size: 0.8em; padding: 4px;\" name='projetoid' >";
		if ($projetoid==0 || empty($projetoid)) {
		echo "
      <option value=''>".GetLangVar('nameselect')."</option>";
			} else {
				$qq = "SELECT * FROM Projetos WHERE ProjetoID='".$projetoid."'";
				$prjres = mysql_query($qq,$conn);
				$prjrow = mysql_fetch_assoc($prjres);
				echo "
      <option  selected value='".$prjrow['ProjetoID']."'>".$prjrow['ProjetoNome']."</option>";
			}
			echo "
      <option value=''>----</option>";
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
  <td colspan='2'>
    <table align='center' >
      <tr>
        <input type='hidden' name='final' value='' />
        <td align='center' ><input style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"    type='submit' value='".GetLangVar('namesalvar')."' onclick=\"javascript:document.coletaform.final.value='1'\" /></td>
      </tr>
    </table>
  </td>
</tr>
</form>
</tbody>
</table>
";

}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=TRUE,$footer=$menu);
?>