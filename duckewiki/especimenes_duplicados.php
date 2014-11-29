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
	$uuid = $_SESSION['userid'];
	$lastname = $_SESSION['userlastname'];
	$acclevel = $_SESSION['accesslevel'];
}

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
@extract($gget);

//CABECALHO
$ispopup=1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"
);
$which_java = array(
);
$title = 'Especimenes duplicados';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

//echopre($ppost);
$apagado=0;
$lixo=10;
if ($lixo==10) {
if (count($paraapagar)>0) {
	foreach ($originais as $sppids) {
		$zz = explode(";",$sppids);
		$za =  array_diff($zz,$paraapagar);
		$za = array_values($za);
		$validid = 0;
		if (count($za)==1) {
			$validid = $za[0];
		}
		if ($validid>0) {
		foreach ($zz as $specid) {
			if (in_array($specid,$paraapagar)) {
				//echo $validid."  ".$specid." ";
				$qu = "SELECT * FROM Traits_variation JOIN Traits USING(TraitID) WHERE EspecimenID='".$specid."'";
				$ru = mysql_query($qu,$conn);
				$nru = mysql_numrows($ru);
				//IF THERE ARE TRAITS FOR THE ESPECIMENE TO DELETE
				if ($nru>0) {
				//FOR EACH TRAIT
				while ($rw = mysql_fetch_assoc($ru)) {
				  //MAKE A COPY OF TRAIT RECORD
CreateorUpdateTableofChanges($rw['TraitVariationID'],'TraitVariationID','Traits_variation',$conn);
				  //SELECT VALUES FOR THE SAME TRAIT FOR THE VALID (KEEPING RECORD)
				   $qz = "SELECT * FROM Traits_variation WHERE EspecimenID='".$validid."' AND TraitID='".$rw['TraitID']."'";
				  $rz = mysql_query($qz,$conn);
				  $nrz = mysql_numrows($rz);
				  if ($nrz>0) {
				  		$update=0;
				  		$novovalor = '';
				  		//COMPARE E UNA SE NECESSÁRIO
				  		$rwz = mysql_fetch_assoc($rz);
				  		if ($rw['TraitTipo']=='Variavel|Categoria') {
				  			if ($rw['MultiSelect']=='Sim') {
				  				$ardel = explode(";", $rw['TraitVariation']);
				  				$arkeep = explode(";", $rwz['TraitVariation']);
				  				$arall = array_merge((array)$ardel,(array)$arkeep);
				  				$arnew  = array_unique($arall);
				  				$newval = implode(";",$arnew);
				  				if ($newval!=$rwz['TraitVariation']) {
				  					///UPDATE MERGED VALUE
				  					$update=1;
				  					echo "Trait ".$rw['TraitName']."  [ ".$rw['TraitTipo']."] transferido para o registro válido. Unindo CATEGORIAS DE VARIACAO<br />";
				  					$novovalor = $newval;
				  				}
				  			} 
				  			else {
								if ($rw['TraitVariation']!= $rwz['TraitVariation']) {
									//variavel categoria de valor unico, nao atualizar, manter o valor original e descartar o valor velho
									echo "Trait ".$rw['TraitName']."  [ ".$rw['TraitTipo']."]  é diferente. Valor ÚNICO DE CATEGORIA IGNORADO<br />";
								} 
				  			}
				  		}
				  		if ($rw['TraitTipo']=='Variavel|Quantitativo') {
				  				$ardel = explode(";", $rw['TraitVariation']);
				  				$arkeep = explode(";", $rwz['TraitVariation']);
				  				$arall = array_merge((array)$ardel,(array)$arkeep);
				  				$arnew  = array_unique($arall);
				  				$newval = implode(";",$arnew);
				  				if ($newval!=$rwz['TraitVariation']) {
				  					$update=1;
				  					echo "Trait ".$rw['TraitName']."  [ ".$rw['TraitTipo']."] transferido para o registro válido. Unindo VALORES DE VARIAVEL QUANTITATIVA<br />";
				  					$novovalor = $newval;
				  					//echo $novovalor."   k:".$rwz['TraitVariation']."   d:".$rw['TraitVariation']."<br /.";
				  				}
				  		
				  		}
				  		if ($rw['TraitTipo']=='Variavel|Imagem') {
				  				$ardel = explode(";", $rw['TraitVariation']);
				  				$arkeep = explode(";", $rwz['TraitVariation']);
				  				$arall = array_merge((array)$ardel,(array)$arkeep);
				  				$arnew  = array_unique($arall);
				  				$newval = implode(";",$arnew);
				  				if ($newval!=$rwz['TraitVariation']) {
				  					$update=1;
				  					echo "Trait ".$rw['TraitName']."  [ ".$rw['TraitTipo']."] transferido para o registro válido. Unindo IMAGENS<br />";
				  					$novovalor = $newval;
				  				}
				  		
				  		
				  		}
				  		if ($rw['TraitTipo']=='Variavel|Texto') {
				  										if ($rw['TraitVariation']!= $rwz['TraitVariation']) {
									//variavel categoria de valor unico, nao atualizar, manter o valor original e descartar o valor velho
									echo "Trait ".$rw['TraitName']."  [ ".$rw['TraitTipo']."]  é diferente. VARIAVEL DE TEXTO DO REGISTRO APAGADO IGNORADA COMPLETAMENTE<br />";
								} 
				  		
				  		}
				  		
				  		if ($update>0) {
				  				$fieldsaskeyofvaluearray = array('TraitVariation' => $novovalor);
				  				//echopre($fieldsaskeyofvaluearray);
UpdateTable($rwz['TraitVariationID'],$fieldsaskeyofvaluearray,'TraitVariationID','Traits_variation',$conn);
				  		}
				  		//APAGA O REGISTRO DO TRAIT PARA O ESPECIMEN QUE SERÁ APAGADO
				  		$qn = "DELETE FROM Traits_variation WHERE TraitVariationID='".$rw['TraitVariationID']."'";
				  		$ap  = mysql_query($qn,$conn);
				  		if ($ap) {
				  		echo "Trait ".$rw['TraitName']."  [ ".$rw['TraitTipo']."]  é diferente. APAGUEI VARIAVEL DA AMOSTRA DELETADA!<br />";
				  		}
				  } 
				  else {
				  	///MUDE O ESPECIMEN ID PARA O VALIDO
				  		$qup = "UPDATE Traits_variation SET EspecimenID='".$validid."'  WHERE TraitVariationID='".$rw['TraitVariationID']."'";
				  		$rup = mysql_query($qup,$conn);
				  		if ($rup) {
				  			echo "Trait ".$rw['TraitName']."  [ ".$rw['TraitTipo']."] transferido para o registro válido <br />";
				  		}
				  }
				  
				
				
				}
				
				
				
				
				
				}

				$quex = "SELECT COUNT(*) as nn FROM MetodoExpeditoPlantas WHERE EspecimenIDs LIKE '".$specid."'";
				$ruex = @mysql_query($quex,$conn);
				$nruex = @mysql_numrows($ruex);
				if ($nruex>0) {
					$rw = mysql_fetch_assoc($ruex);
					$nexpedito = $rw['nn'];
					//echo "ntraits = ".$ntraits."  nexpedito:".$nexpedito."<br />";
					if ($nexpedito>0) {
						$qqu = "UPDATE MetodoExpeditoPlantas SET EspecimenIDs='".$validid."' WHERE EspecimenIDs LIKE '".$specid."'";
						$rru = mysql_query($qqu,$conn);
					}
				}
				CreateorUpdateTableofChanges($specid,'EspecimenID','Especimenes',$conn);
				$qz = "DELETE FROM Especimenes WHERE EspecimenID='".$specid."'";
				//echo $qz."<br />";
				$rzz = mysql_query($qz,$conn);
				if ($rzz) {
					$apagado++;
				}
				
			}
		}
		} else {
			echo "Não pode apagar todos os registros<br />";
		}
	}
}
if ($apagado>0) {
echo "
<br />
<table align='center' class='success'>
  <tr><td>$apagado registros apagados</td></tr>
</table>
<br />";
}
}

$qq = "SELECT GROUP_CONCAT(EspecimenID SEPARATOR ';') as ids,Abreviacao,Number,count(EspecimenID) as cnt FROM Especimenes JOIN Pessoas ON ColetorID=PessoaID ";
if ($acclevel!='admin') {
			$qq .= " WHERE Especimenes.AddedBy='".$uuid."'";
}
$qq .= "  GROUP BY CONCAT(ColetorID,'_',Number,Day,Mes,Ano) HAVING cnt>1";
$rs = mysql_query($qq,$conn);
$nrs = mysql_numrows($rs);
if ($nrs>0) {
$qq .= " LIMIT 0,1";
echo $qq."<br />";
$res = mysql_query($qq,$conn);
$nnres = mysql_numrows($res);

echo "
<div style=\"color:#B22222; width:800px;float:center; font-size: 1.2em; padding: 4px;\">Registro <b>1/".$nrs."</b> de especímenes duplicados editáveis por ".$lastname."</div>
<form action='especimenes_duplicados.php' name='myform' method='post'>
<div style=\"height:380px; width:800px; float:center; overflow: -moz-scrollbars-vertical; overflow: scroll; border: thin solid sylver\">
<table align='center' cellpadding='7' class='myformtable'>
<thead>";
$ii=0;
while($row = mysql_fetch_assoc($res)) {
	$rz = explode(";",$row['ids']);
	$passids = implode("_",$rz);
	echo "
    <input type='hidden' name='originais[]' value='".$row['ids']."' />";
	foreach ($rz as $vv) {
		$vv = $vv+0;
		$qq = "SELECT pltb.EspecimenID, 
colpessoa.Abreviacao as COLETOR,
pltb.Number as NUMERO,
CONCAT(pltb.Ano,'-',pltb.Mes,'-',pltb.Day) as DATA_COLETA,
pltb.INPA_ID as INPA_NUM, 
pltb.Herbaria,
pl.PlantaTag,
labelnotes_nomoni(pltb.EspecimenID+0,0,".$formnotes.",TRUE,FALSE) as NOTAS, 
IF(iddet.InfraEspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie,' ',infsptb.InfraEspecie),IF(iddet.EspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie),IF(iddet.GeneroID>0,gentb.Genero,famtb.Familia))) as NOME,
IF(pltb.GPSPointID>0,countrygps.Country,IF(pltb.GazetteerID>0,countrygaz.Country,' '))as PAIS, IF(pltb.GPSPointID>0,provigps.Province,IF(pltb.GazetteerID>0,provgaz.Province,' ')) as ESTADO, 
IF(pltb.GPSPointID>0,munigps.Municipio,IF(pltb.GazetteerID>0,muni.Municipio,' ')) as MUNICIPIO, 
IF(pltb.GPSPointID>0,gazgps.PathName,IF(pltb.GazetteerID>0,gaz.PathName,' ')) as LOCALIDADE, 
IF(pltb.GPSPointID>0,pltb.GPSPointID,'') as GPSpointID,IF(pltb.GPSPointID>0,gazgps.Gazetteer,IF(pltb.GazetteerID>0,gaz.Gazetteer,'')) as LOCALIDADE_ESPECIFICA,
IF(ABS(pltb.Longitude)>0,pltb.Longitude,IF(pltb.GPSPointID>0,gpspt.Longitude,IF(gaz.Longitude<>0,gaz.Longitude,muni.Longitude))) as LONGITUDE, 
IF(ABS(pltb.Longitude)>0,pltb.Latitude,IF(pltb.GPSPointID>0,gpspt.Latitude,IF(gaz.Longitude<>0,gaz.Latitude,muni.Latitude))) as LATITUDE, 
habitaclasse(pltb.HabitatID) AS  HABITAT_CLASSE,
addcolldescr(pltb.AddColIDS) as ADDCOLL
FROM Especimenes as pltb LEFT JOIN Pessoas as colpessoa ON pltb.ColetorID=colpessoa.PessoaID";
		$qq .= " LEFT JOIN Plantas as pl ON pltb.PlantaID=pl.PlantaID LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID ";
		$qq .= " LEFT JOIN Gazetteer as gaz ON gaz.GazetteerID=pltb.GazetteerID  LEFT JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID  LEFT JOIN Province as provgaz ON muni.ProvinceID=provgaz.ProvinceID  LEFT JOIN Country  as countrygaz ON provgaz.CountryID=countrygaz.CountryID  LEFT JOIN GPS_DATA as gpspt ON gpspt.PointID=pltb.GPSPointID  LEFT JOIN Gazetteer as gazgps ON gpspt.GazetteerID=gazgps.GazetteerID  LEFT JOIN Municipio as munigps ON gazgps.MunicipioID=munigps.MunicipioID LEFT  JOIN Province as provigps ON munigps.ProvinceID=provigps.ProvinceID  LEFT JOIN Country  as countrygps ON provigps.CountryID=countrygps.CountryID";
		$qq .= " WHERE EspecimenID='".$vv."' ";
		if ($acclevel!='admin') {
			$qq .= " AND Especimenes.AddedBy='".$uuid."'";
		}	
		$rr = mysql_query($qq,$conn);
		$rwz = mysql_fetch_assoc($rr);
		
		
		$qu = "SELECT COUNT(*) as nn FROM Traits_variation WHERE EspecimenID='".$vv."'";
		$ru = mysql_query($qu,$conn);
		$rw = mysql_fetch_assoc($ru);
		$ntraits = $rw['nn'];

		$qu = "SELECT COUNT(*) as nn FROM MetodoExpeditoPlantas WHERE EspecimenIDs LIKE '".$vv."'";
		$ru = @mysql_query($qu,$conn);
		$rw = @mysql_fetch_assoc($ru);
		$nexpedito = $rw['nn'];
				
		//echo $qq."<br /><br />";
		if ($ii==0) {
		echo "
<tr class='subhead'>
  <td style='color:#990000; border: thin solid #990000;'>Apagar*</td>
  <td style='border: thin solid #990000;'>NVarAssociadas</td>
  <td align='center' style='color:#990000; border: thin solid #990000;'>NPlantasExpedito**</td>
  ";
			$jj=0;
			foreach ($rwz as $kk => $vari) {
				if ($jj>0) {
					echo "
  <td style='border: thin solid #990000;'>".$kk."</td>";
				}
				$jj++;
			}
			
			
echo "
  </tr>
</thead>
<tbody>
";
			$ii++;
		}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center' style='font-size: 2em;border: thin solid #990000;'><input style='height: 20px; width: 20px;' type='checkbox'  id='selspecid_".$vv."'  name='paraapagar[]' value='".$vv."'  onclick=\"javascript: getvalueandmerge('todeletespecs',".$vv.",'selspecid_".$vv."');\" /></td>
  <td align='center' style='border: thin solid #990000;'>$ntraits</td>
  <td align='center' style='border: thin solid #990000;'>$nexpedito</td>";
			$jj=0;
			foreach ($rwz as $kk => $vari) {
				if ($kk=='NOTAS' && !empty($vari)) {
					//$varia = "&nbsp;&nbsp;<img height=20 src=\"icons/icon_text2.jpg\" ";
					//$myurl ="showlongtext.php?especimenid=".$vv; 	
					//$varia  .= " onclick = \"javascript:small_window('".$myurl."',500,350,'".$kk."_".$vv."');\"></td>";
					
					$varia = "<img style='cursor:pointer;' src='icons/nota-icon.png' height='20' onclick=\"javascript:small_window('traits_coletorvariacao.php?apagavarsess=1&saveit=1&formid=".$formnotes."&especimenid=".$rwz['EspecimenID']."',800,800,'Editando notas');\"  onmouseover=\"Tip('Editar notas dessa amostra');\" ><br /><textarea rows=5 readonly=true class='tdformnotes'>$vari</textarea>";
				} else {
					$varia = $vari; 
				}
				//$varia = $vari; 
				if ($jj>0) {
					echo "
  <td style='border: thin solid #990000;'>".$varia."</td>";
				}
				$jj++;
			}
			
			
echo "
</tr>";
	}
}
echo "
</tbody>
</table>
</div>
<div style=\"width:800px;float:center;\">
<table>
<!--- <tr ><td align='center'><input type='text' value='' id='todeletespecs' /></td></tr> --->

<tr><td align='left' style='color:#990000;font-size:0.8em;'>*O registro selecionado será apagado &nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
$help = "Se deseja manter variáveis relacionadas ao registro a ser apagado, edite manualmente acima antes de processeguir e transfira as informações. Imagens serão transferidas automaticamente do registro apagado para o registro que permanece"; 
echo "onclick=\"javascript:alert('$help');\" />
</td></tr>";
//<tr> <td align='left' ><input  type='button'  style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"   value='Atualiza Vínculos'  onclick = \"javascript:passdupspecs('todeletespecs','".$passids."');\" />&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
	//$help = "Se deseja manter variáveis relacionadas ao registro a ser apagado, clique aqui antes de apagar o registro para que todas as variáveis associadas ao registro a ser apagado sejam transferidas para o registro mantido."; 
	//echo "onclick=\"javascript:alert('$help');\" /></td></tr>
echo "<tr ><td align='center'><input type='submit' value='Continuar' style=\"color:#B22222; font-size: 2em; font-weight:bold; padding: 4px; cursor:pointer;\"  />&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
	$help = "Se tiver selecionado algum registro para apagar, irá, primeiro apagar e depois passar para o próximo para de duplicados, caso exista!";
	echo "onclick=\"javascript:alert('$help');\" /></td></tr></td></tr>
</table>
</div>
</form>
";

} 
else {

echo "
<br />
<table align='center' class='erro' cellpadding='7'>
<tr><td>Não há registros duplicados editáveis por $lastname!</td></tr>
</table>
<br />";

}


$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<script type='text/javascript' src='javascript/myjavascript_teste.js'></script>"
//,"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->","<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>