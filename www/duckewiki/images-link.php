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
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />",
"<link rel='stylesheet' href='javascript/magiczoomplus/magiczoomplus/magiczoomplus.css' type='text/css' media='screen' />"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>",
"<script src='javascript/magiczoomplus/magiczoomplus/magiczoomplus.js' type='text/javascript'></script>"
);
$title = 'Checar link de imagens';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (empty($enviado)) {
	$qq = "SELECT * FROM Imagens WHERE checkunlinkedimgs(ImageID)=0";
	$res = mysql_query($qq,$conn);
	$nres = mysql_numrows($res);
   if ($nres>0) {
	unset($_SESSION['unlkimgs']);
	$unlinkedimgs = array();
	while ($row = mysql_fetch_assoc($res)) {
		$unlinkedimgs[] = $row['ImageID'];
		echo '&nbsp;';
		flush();
	}
	$_SESSION['unlkimgs'] = serialize($unlinkedimgs);
	echo "
<br />
<form action='images-link.php' method='post'>
<input type='hidden' name='ispopup' value='".$ispopup."' /> 

  <table cellpadding=\"5\" align='center' class='myformtable'>
    <thead>
      <tr><td colspan='100%'>$nres imagens que não tem vínculo!</td></tr>
    </thead>
    <tbody>
      <tr class='trsubhead'><td class='tdsmallbold'>Selecione uma das opções para facilitar a busca de ligações</td></tr>
      <tr>
        <td colspan='100%'>
          <table>
            <tr><td><input type='radio' name='bydate' value='1' /></td><td>Filtrar dados que tenham a mesma data</td></tr>
            <tr><td><input type='radio' name='bydate' value='2' /></td><td>Mostrar todas as possiveis ligações</td></tr>
          </table>
        </td>
      </tr>
        <input type=hidden name='enviado' value='1' />
        <tr><td colspan='100%' align='center'><input type=submit class='bsubmit' value='".GetLangVar('namecontinuar')."' /></td><tr>
    </tbody>
  </table>
</form>
<br />";
	} 
	else {
		echo "
<br />
<form >
<table cellpadding=\"5\" align='center' class='success'>
  <tr><td class='tdsmallbold' align='center'>Todas as imagens já tem vínculo</td></tr>
  <tr><td><input type='button' class='bsubmit' value='".GetLangVar('namefechar')."' onclick='javascript:window.close();'/></td><tr>
</table>
</form>
<br />";
	}
} 
else {
	//o loop ja comecou ligacoes podem ser feitas
	if ($enviado==2) {
		$erro=0;
		if (empty($traitid)) {
			$erro++;
			echo "
<br />
<form action='images-link.php' method='post'>
  <input type=hidden name='bydate' value='".$bydate."' />
  <input type=hidden name='enviado' value='1' />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>Precisa dizer a variável onde a imagem deve ser relacionada!</td></tr>
<tr><td><input type=submit class='bsubmit' value='".GetLangVar('namevoltar')."' /></td><tr>
</table>
</form>
<br />";
		} 
		else {
			if (empty($specimenid) && empty($plantaid) && empty($habitatid))  {
					$erro++;
					echo "
<br />
<form action='images-link.php' method='post'>
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>Você não indicou com o que a imagem deve ser relacionada</td></tr>
  <input type=hidden name='traitid' value='".$traitid."' />
  <input type=hidden name='bydate' value='".$bydate."' />
  <tr><td><input type=submit class='bsubmit' value='".GetLangVar('namevoltar')."' /></td><tr>
</table>
</form>
<br />";
			} 
			else {
				$er2=0;
				$sumids = $specimenid+$plantaid+$habitatid;
				//se foi indicado uma amostra e apenas isso para relacionar com a imagem, faz o cadastro
				if (!empty($specimenid) && $specimenid==$sumids) {
					$qq = "SELECT * FROM Traits_variation WHERE EspecimenID='".$specimenid."' AND TraitID='".$traitid."'";
					$rws = mysql_query($qq,$conn);
					$nrws = mysql_numrows($rws);
					if ($nrws==1) {
						$rww = mysql_fetch_assoc($rws);
						$trvarid = $rww['TraitVariationID'];
						$imgarr = explode(";",$rww['TraitVariation']);
						$imgarr = array_merge((array)$imgarr,(array)$imgid);
						$imagens = implode(";",$imgarr);
						$arrayofvalues = array('TraitVariation' => $imagens,
												'TraitID' => $traitid,
												'EspecimenID' => $specimenid);
						CreateorUpdateTableofChanges($trvarid,'TraitVariationID','Traits_variation',$conn);
						$newimgvar = UpdateTable($trvarid,$arrayofvalues,'TraitVariationID','Traits_variation',$conn);
					} elseif ($nrws==0) {
						$arrayofvalues = array('TraitVariation' => $imgid,
												'TraitID' => $traitid,
												'EspecimenID' => $specimenid);
						$newimgvar =  InsertIntoTable($arrayofvalues,'TraitVariationID','Traits_variation',$conn);
					}	
				} else {
					$er2++;
				}
				//se foi indicado uma planta marcada e apenas isso para relacionar com a imagem, faz o cadastro
				if (!empty($plantaid) && $plantaid==$sumids) {
					$qq = "SELECT * FROM Traits_variation WHERE PlantaID='".$plantaid."' AND TraitID='".$traitid."'";
					$rws = mysql_query($qq,$conn);
					$nrws = mysql_numrows($rws);
					if ($nrws==1) { //se ja existe variacao para essa variavel e planta, atualiza
						$rww = mysql_fetch_assoc($rws);
						$trvarid = $rww['TraitVariationID'];
						//pega os valores velhor
						$imgarr = explode(";",$rww['TraitVariation']);

						//junta com o novo
						$imgarr = array_merge((array)$imgarr,(array)$imgid);
						$imagens = implode(";",$imgarr);
						$arrayofvalues = array('TraitVariation' => $imagens,
												'TraitID' => $traitid,
												'PlantaID' => $plantaid);
						CreateorUpdateTableofChanges($trvarid,'TraitVariationID','Traits_variation',$conn);
						$newimgvar = UpdateTable($trvarid,$arrayofvalues,'TraitVariationID','Traits_variation',$conn);
					} elseif ($nrws==0) { //se ainda nao existe variacao para essa variavel e planta, entao insere
						$arrayofvalues = array('TraitVariation' => $imgid,
												'TraitID' => $traitid,
												'PlantaID' => $plantaid);
						$newimgvar =  InsertIntoTable($arrayofvalues,'TraitVariationID','Traits_variation',$conn);
					}		
				} elseif (!$newimgvar) {
					$er2++;
				}
				if (!empty($habitatid) && $habitatid==$sumids) {
					$qq = "SELECT * FROM Habitat_Variation WHERE HabitatID='".$habitatid."' AND TraitID='".$traitid."'";
					$rws = mysql_query($qq,$conn);
					$nrws = mysql_numrows($rws);
					if ($nrws==1) { //se ja existe variacao para essa variavel e habitat, atualiza
						$rww = mysql_fetch_assoc($rws);
						$trvarid = $rww['TraitVariationID'];
						//pega os valores velhor
						$imgarr = explode(";",$rww['TraitVariation']);

						//junta com o novo
						$imgarr = array_merge((array)$imgarr,(array)$imgid);
						$imagens = implode(";",$imgarr);
						$arrayofvalues = array('TraitVariation' => $imagens,
												'TraitID' => $traitid,
												'HabitatID' => $habitatid);
						CreateorUpdateTableofChanges($trvarid,'TraitVariationID','Traits_variation',$conn);
						$newimgvar = UpdateTable($trvarid,$arrayofvalues,'TraitVariationID','Traits_variation',$conn);
					} elseif ($nrws==0) { //se ainda nao existe variacao para essa variavel e habitat, entao insere
						$arrayofvalues = array('TraitVariation' => $imgid,
												'TraitID' => $traitid,
												'HabitatID' => $habitatid);
						$newimgvar =  InsertIntoTable($arrayofvalues,'TraitVariationID','Traits_variation',$conn);
					}	
				} elseif (!$newimgvar) {
					$er2++;
				}
				if ($er2>0) {
					$erro++;
					echo "
<br />
<table cellpadding=\"7\" align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>Você pediu para ligar a imagem com mais de uma coisa. Selecionar apenas 1 relação</td></tr>
<form action='images-link.php' method='post' name='setp2imglnkform'>
  <input type='hidden' name='enviado' value='' />
  <input type='hidden' name='traitid' value='".$traitid."' />
  <input type='hidden' name='bydate' value='".$bydate."' />
<tr>
  <td align='center' ><input type='submit' value='Pular e ir para a próxima' class='bsubmit' onclick=\"javascript:document.setp2imglnkform.enviado.value=3\" /></td>
  <td align='left'><input type='submit' value='Voltar' class='borange' onclick=\"javascript:document.setp2imglnkform.enviado.value=1\" /></td>
</tr>
</form>
<form action='index.php' method=post>
  <tr><td><input type=submit class='bsubmit' value='Terminar' /></td><tr>
</form>
</table>
<br />";
				}
				if ($newimgvar) { //se relacionou a imagem com sucesso
					echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='success'>
  <tr><td class='tdsmallbold' align='center'>A imagem $ff foi relacionada com sucesso!</td></tr>";
					$unlinkedimgs = unserialize($_SESSION['unlkimgs']);
					unset($unlinkedimgs[0]);
					$unlinkedimgs = array_values($unlinkedimgs);
					$nleft = count($unlinkedimgs);
					//se ainda faltam imagens
					if ($nleft>0) {
						$_SESSION['unlkimgs'] = serialize($unlinkedimgs);
echo "
<form action='images-link.php' method=post>
  <input type=hidden name='enviado' value='1' />
  <input type=hidden name='traitid' value='".$traitid."' />
  <input type=hidden name='bydate' value='".$bydate."' />
<tr><td><input type=submit class='bsubmit' value='Ir para a próxima' /></td><tr>
<script language=\"JavaScript\">setTimeout(function() {this.form.submit();},2000);</script>
</form>";
					} else {
						echo "
  <tr><td class='tdsmallbold' align='center'>Já não há mais imagens para relacionar!</td></tr>
<form action='index.php' method=post>
  <tr><td><input type=submit class='bsubmit' value='Terminar' /></td><tr>
</form>";
					}
					echo "
</table><br />";
				}
			}
		}
	} 
	else { 
		if ($enviado==3) {
			$unlinkedimgs = unserialize($_SESSION['unlkimgs']);
			unset($unlinkedimgs[0]);
			$unlinkedimgs = array_values($unlinkedimgs);
			$nleft = count($unlinkedimgs);
			if ($nleft>0) {
				$_SESSION['unlkimgs'] = serialize($unlinkedimgs);
			} 
			else {
				echo "
<br />
<table cellpadding=\"7\" align='center' class='success'>
  <tr><td class='tdsmallbold' align='center'>Já não há mais imagens para relacionar!</td></tr>
<form action='index.php' method=post>
  <tr><td><input type=submit class='bsubmit' value='Terminar' /></td><tr>
</form>
</table>
<br />";
				$erro++;
			}
		}
		$unlinkedimgs = unserialize($_SESSION['unlkimgs']);
		$imgid = $unlinkedimgs[0];
		$qq = "SELECT * FROM Imagens WHERE ImageID='".$imgid."'";
		$res = mysql_query($qq,$conn);
		$row = mysql_fetch_assoc($res);
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
		echo "
<br /><table align='center'>
<tr>
  <td>
    <div class='imagecontainer'>
       <a href=\"".$dirlarge.$filename."\" class='MagicZoomPlus'  rel=\"zoom-position:right;zoom-height:200px; zoom-fade:true; smoothing-speed:17;opacity-reverse:true;\" >
       <img style='border: thin solid gray;'  height=\"60\" src=\"".$dirthumb.$filename."\" alt='Imagem' /></a>
      </a>
      <br />
    </div>
  </td>
  <td>
    <table class='myformtable' cellpadding='7'>
      <thead><tr><td colspan='100%'>Vinculando a imagem $ff...</td></tr></thead>
      <tbody>
      <form name='imglinkform' action='images-link.php' method=post >
        <input type=hidden name='ff' value='".$ff."' />
        <input type=hidden name='imgid' value='".$imgid."' />
        <input type=hidden name='bydate' value='".$bydate."' />";
	if ($bgi % 2 == 0) { $bgcolor = $linecolor2;} else { $bgcolor = $linecolor1 ;} $bgi++;
	echo "
        <tr bgcolor = '".$bgcolor."'>
          <td colspan='100%'>
            <table>
              <tr>
                <td class='tdsmallbold'>".GetLangVar('traittolinkto')."&nbsp;<img height=14 src=\"icons/icon_question.gif\"";
				$help = 'Variável do tipo imagem para colocar esta imagem';
				echo " onclick=\"javascript:alert('$help');\" /></td>
                <td>
                  <select name='traitid'>
                    <option value=''>".GetLangVar('nameselect')."</option>";
					$filtro ="SELECT * FROM Traits WHERE TraitTipo='Variavel|Imagem' ORDER BY TraitName";
					$resaa = mysql_query($filtro,$conn);
					while ($aa = mysql_fetch_assoc($resaa)){
						if (!empty($aa['TraitName'])) {
							echo "
                    <option ";
							if (!empty($traitid) && $traitid==$aa['TraitID']) {
								echo "selected";
							}
							echo " value='".$aa['TraitID']."'>".$aa['TraitName']."</option>";
						}
					}
			echo "
                  </select>
                </td>
              </tr>";
				if ($bgi % 2 == 0) { $bgcolor = $linecolor2;} else { $bgcolor = $linecolor1 ;} $bgi++;
	echo "
              <tr bgcolor = '".$bgcolor."'>
                <td class='tdsmallbold'>Relacionar com uma ".mb_strtolower(GetLangVar('nameamostra'))."&nbsp;<img height=14 src=\"icons/icon_question.gif\"";
				$help = 'Especímenes coletados na mesma data que a imagem foi tirada';
				echo " onclick=\"javascript:alert('$help');\" /></td>
                <td>
                  <select name='specimenid'>
                    <option value=''>".GetLangVar('nameselect')."</option>";
					$qq = "SELECT pltb.EspecimenID AS EspecID, CONCAT(colpessoa.SobreNome,' ',IF(pltb.Prefixo='','',CONCAT(pltb.Prefixo,'-')),pltb.Number,IF(pltb.Sufix='','',CONCAT('-',pltb.Sufix))) as Tag, IF(iddet.InfraEspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie,' ',infsptb.InfraEspecie),IF(iddet.EspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie),IF(iddet.GeneroID>0,gentb.Genero,famtb.Familia))) as Nome";
					$qq = $qq." FROM Especimenes as pltb LEFT JOIN Pessoas as colpessoa ON pltb.ColetorID=colpessoa.PessoaID";
					$qq .= " LEFT JOIN Identidade as iddet USING(DetID) LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID LEFT JOIN Pessoas as detpessoa ON detpessoa.PessoaID=iddet.DetbyID ";
					if ($bydate==1) {
						$qq .=  "WHERE CONCAT(pltb.Ano,'-',pltb.Mes,'-',pltb.Day)='".$imgdate."'";
					}
					$resaa = mysql_query($qq,$conn);
					while ($aa = mysql_fetch_assoc($resaa)){
						$nn = $aa['Tag']." [".$aa['Nome']."]";
						echo "
                    <option value='".$aa['EspecID']."'>".$nn."</option>";
				}
	echo "
                  </select>
                </td>
            </tr>";
		if ($bgi % 2 == 0) { $bgcolor = $linecolor2;} else { $bgcolor = $linecolor1 ;} $bgi++;
			echo "
            <tr bgcolor = '".$bgcolor."'>
              <td class='tdsmallbold'>Relacionar com uma ".mb_strtolower(GetLangVar('nametaggedplant'))."&nbsp;<img height=14 src=\"icons/icon_question.gif\"";
						$help = 'Plantas marcadas na mesma data em que a imagem foi tirada!';
						echo " onclick=\"javascript:alert('$help');\" /></td>
              <td>
                <select name='plantaid'>
                  <option value=''>".GetLangVar('nameselect')."</option>";
					$qq = " SELECT pltb.PlantaID AS PlID, IF(pltb.InSituExSitu='',pltb.PlantaTag,IF(pltb.InSituExSitu LIKE 'Insitu',CONCAT('JB-X-',pltb.PlantaTag),CONCAT('JB-N-',pltb.PlantaTag))) as Tag, IF(iddet.InfraEspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie,' ',infsptb.InfraEspecie),IF(iddet.EspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie),IF(iddet.GeneroID>0,gentb.Genero,famtb.Familia))) as Nome";
					$qq = $qq." FROM Plantas as pltb LEFT JOIN Identidade as iddet USING(DetID) LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID LEFT JOIN Pessoas as detpessoa ON detpessoa.PessoaID=iddet.DetbyID ";
					if ($bydate==1) {
						$qq .=  "TaggedDate='".$imgdate."'";
					}
					$resaa = mysql_query($qq,$conn);
					while ($aa = mysql_fetch_assoc($resaa)){
						$nn = $aa['Tag']." [".$aa['Nome']."]";
						echo "
                  <option value='".$aa['PlID']."'>".$nn."</option>";
				}
	echo "
                </select>
              </td>
            </tr>";
			if ($bgi % 2 == 0) { $bgcolor = $linecolor2;} else { $bgcolor = $linecolor1 ;} $bgi++;
	echo "
            <tr bgcolor = '".$bgcolor."'>
              <td class='tdsmallbold' >Relacionar com um ".mb_strtolower(GetLangVar('namehabitat'))."&nbsp;<img height=14 src=\"icons/icon_question.gif\"";
					$help = 'Uma classe de habitat ou um habitat local ligado a um ponto de GPS que tem a mesma data da imagem';
						echo  " onclick=\"javascript:alert('$help');\" /></td>
              <td >
                <select name='habitatid'>
                  <option value=''>".GetLangVar('nameselect')."</option>";
					$qq = "SELECT hab.HabitatID,hab.PathName as habitat,gazgps.PathName as gazeta,gps.Name as gpspt as local FROM Habitat as hab LEFT JOIN GPS_DATA as gps ON hab.GPSPointID=gps.PointID LEFT JOIN Gazetteer as gazgps ON gps.GazetteerID=gazgps.GazetteerID  WHERE hab.HabitatTipo='Class'";
					if ($bydate==1) {
					  $qq .= " OR gps.DateOriginal='".$imgdate."'";
					}
					$qq .= " ORDER BY hab.PathName,gazgps.PathName";
					$wr = mysql_query($qq,$conn);
					$nw = mysql_numrows($wr);
					if ($nw>0) {
						while ($aa = mysql_fetch_assoc($wr)){
							echo "
                  <option value='".$aa['HabitatID']."'>".$aa['habitat']." [".$aa['gpspt']." - ".$aa['gazeta']."]</option>";
		}
	}
	echo  "
                </select>
              </td>
            </tr>
            <tr><td colspan='100%' align='center'>&nbsp;</td></tr>
            ";
			if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
            <tr bgcolor = '".$bgcolor."'>
              <td colspan='100%' align='center'>
                <table align='center' >
                  <tr>
                    <td align='center' >
                      <input type='hidden' name='enviado' value='' />
                      <input type='submit' value='Salvar e ir para a próxima' class='bsubmit' onclick=\"javascript:document.imglinkform.enviado.value=2\" /></td>
                    <td align='left'><input type='submit' value='Pular esta!' class='borange' onclick=\"javascript:document.imglinkform.enviado.value=3\" /></td>
                  </tr>
                </table>
              </td>
            </tr>
        </form>
        </td>
      </tr>
    </table>
  </tr>
</table>
</td>
</tr>
</tbody>
</table>";
	
	}

}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>