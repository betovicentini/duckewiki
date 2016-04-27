<?php
//MUDA O VINCULO OU DESVINCULA UMA IMAGEM
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


echopre($ppost);
//CABECALHO
$menu = FALSE;

$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' >"
);
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>"
);
$title = 'Mudar o link da imagem';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

//SE SUBMETEU COM INDICACAO DUPLA MOSTRA UM ERRO E DESMARCA
if ($newplantaid>0 && $newespecimenid>0) {
	echo "<br /><span width='80%' style='font-size: 1em; color: red; font-weight: bold; background-color: yellow; padding: 15px; border: 1px solid black;' >Não pode indicar uma planta e um especimene. Apenas 1 opção!</span><br /><br />
";
	unset($newplantaid, $specnum);
	unset($newespecimenid, $plantnum);
} 
//SE E A PRIMEIRA VEZ QUE ENTRA NO FORMULARIO
if (!isset($oquefazer)) {
		  echo "
<form name='lastform' action='image_change.php' method='post'>
  <input type='hidden' name='especimenid' value='".$especimenid."' />
  <input type='hidden' name='plantaid' value='".$plantaid."' />
  <input type='hidden' name='imgid'  value='".$imgid."' />
  <input type='hidden' name='otraitid'  value='".$otraitid."' />
<table >
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td class='tdsmallbold'>Selecione uma das opções</td></tr>
        <tr>
        <td><input type='radio' name='oquefazer'  value='1' />Não é uma imagem da espécie, mas não sei se não é a amostra que está mal identificada</td>
         </tr><tr>
        <td><input type='radio' name='oquefazer'  value='2' />Eu sei que a imagem é de outra amostra ou planta marcada</td>
         </tr>
    </table>
  </td>
</tr>
<tr>
<td><input type='submit' value='Enviar' style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\" />
</tr>
</table>
</form>";
} 
else {
	if ($oquefazer==1) {
		//desligar a amostra da planta ou especímene e deixa sem relação
		if ($confirmado==1) {
			$erro=0;
			$succ=0;
			if ($plantaid>0) {
				$getv0 = "SELECT * FROM `Traits_variation` as tv WHERE  tv.PlantaID=".$plantaid."  AND tv.TraitID=".$otraitid;
				$sql1 = "UPDATE `Traits_variation` as tv SET tv.TraitVariation=removeimagelink(tv.TraitVariation,".$imgid.")  WHERE tv.PlantaID=".$plantaid."  AND tv.TraitID=".$otraitid;
				$sql2 = "UPDATE `Monitoramento` as tv SET tv.TraitVariation=removeimagelink(tv.TraitVariation,".$imgid.")  WHERE tv.PlantaID=".$plantaid."  AND tv.TraitID=".$otraitid;
				$rr = mysql_query($sql2,$conn);
				if (!$rr) {
					$erro++;
				} else {
					$succ++;
				}
			} else {
				$getv0 = "SELECT * FROM `Traits_variation` as tv WHERE  tv.EspecimenID=".$especimenid."  AND tv.TraitID=".$otraitid;
				$sql1 = "UPDATE `Traits_variation` as tv  SET tv.TraitVariation=removeimagelink(tv.TraitVariation,".$imgid.")  WHERE tv.EspecimenID=".$especimenid."  AND tv.TraitID=".$otraitid;
			}
			$gtrr0 = mysql_query($getv0,$conn);
			$getrow0 = mysql_fetch_assoc($gtrr0);
			//FAZ O LOG
			CreateorUpdateTableofChanges($getrow0['TraitVariationID'],'TraitVariationID','Traits_variation',$conn);
			
			$rr = mysql_query($sql1,$conn);
			if (!$rr) {
				$erro++;
			} else {
				$succ++;
			}
			if ($erro==0) {
				echo "<br /><span width='80%' style='font-size: 1em; color: white; font-weight: bold; background-color: blue; padding: 15px; border: 1px solid black;' >OK! A imagem continua na base mas foi desvinculada da amostra!</span><br /><br />
";
			} else {
				echo "<br /><span width='80%' style='font-size: 1em; color: red; font-weight: bold; background-color: yellow; padding: 15px; border: 1px solid black;' >Houve ".$erro." erros em 2 mudanças tentadas</span><br /><br />
";
			}
		} 
		else {
echo "
<br />
<span style='font-size: 1em; color: white; font-weight: bold; background-color: blue; padding: 15px; border: 1px solid black;' >
A imagem será desvinculada do registro e ficará sem link a um material testemunho!</span><br /><br />
<form action='image_change.php' method='post'>
  <input type='hidden' name='especimenid' value='".$especimenid."' />
  <input type='hidden' name='plantaid' value='".$plantaid."' />
  <input type='hidden' name='imgid'  value='".$imgid."' />
  <input type='hidden' name='oquefazer'  value='".$oquefazer."' />
  <input type='hidden' name='otraitid'  value='".$otraitid."' />  
  <input type='hidden' name='confirmado'  value='1' />
<input type='submit' value='Confirmar' style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\" />
</form>
";
		}
		
	}
	if ($oquefazer==2) {
		if (!isset($newespecimenid) && !isset($newplantaid)) {
		//perguntar qual a amostra ou planta marcada
		echo "
<form action='image_change.php' method='post'>
  <input type='hidden' name='especimenid' value='".$especimenid."' />
  <input type='hidden' name='plantaid' value='".$plantaid."' />
  <input type='hidden' name='otraitid'  value='".$otraitid."' />  
  <input type='hidden' name='imgid'  value='".$imgid."' />
  <input type='hidden' name='oquefazer'  value='".$oquefazer."' />
<table >
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td colspan=2 class='tdsmallbold'>Selecione uma opção para transferir a image:</td></tr>
        <tr>
          <td class='tdsmallboldright'>".GetLangVar('nametaggedplant')."</td>
          <td>"; 
          autosuggestfieldval5('search-plantas-simple.php','plantnum',$plantnum,'plantres','newplantaid',$newplantaid,true,60, 'Selecione uma planta da lista'); 
		echo "
          </td>
        </tr>
        <tr>
          <td class='tdsmallboldright'>Amostra coletada</td>
          <td>"; 
autosuggestfieldval5('search-specimen.php','specnum',$specnum,'specres','newespecimenid',$newespecimenid,true,60, 'Selecione uma coleção da lista'); 
		echo "
          </td>
        </tr>
    </table>
  </td>
</tr>
<tr>
<td><input type='submit' value='Enviar' style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\" />
</tr>
</table>
</form>";
		}
		else {
			if ($confirmado==1)  {
////////////DESVINCULA DO LINK ATUAL E PEGA O TRAIT
			$erro=0;
			$succ=0;
			if ($plantaid>0) {
				$getv1 = "SELECT * FROM `Traits_variation` as tv WHERE  tv.PlantaID=".$plantaid."  AND tv.TraitID=".$otraitid;
				$sql1 = "UPDATE `Traits_variation` as tv SET tv.TraitVariation=removeimagelink(tv.TraitVariation,".$imgid.")  WHERE tv.PlantaID=".$plantaid."  AND tv.TraitID=".$otraitid;
				$sql2 = "UPDATE `Monitoramento` as tv SET tv.TraitVariation=removeimagelink(tv.TraitVariation,".$imgid.")  WHERE  tv.PlantaID=".$plantaid."  AND tv.TraitID=".$otraitid;
				$rr = mysql_query($sql2,$conn);
				if (!$rr) {
					$erro++;
				} else {
					$succ++;
				}
			} else {
				$getv1 = "SELECT * FROM `Traits_variation` as tv WHERE  tv.EspecimenID=".$especimenid."  AND tv.TraitID=".$otraitid;
				$sql1 = "UPDATE `Traits_variation` as tv SET tv.TraitVariation=removeimagelink(tv.TraitVariation,".$imgid.")  WHERE tv.EspecimenID=".$especimenid."  AND tv.TraitID=".$otraitid;
			}
			$gtrr1 = mysql_query($getv1,$conn);
			$getrow1 = mysql_fetch_assoc($gtrr1);
			//FAZ O LOG
			CreateorUpdateTableofChanges($getrow1['TraitVariationID'],'TraitVariationID','Traits_variation',$conn);
			$rr = mysql_query($sql1,$conn);
			if (!$rr) {
				$erro++;
			} else {
				$succ++;
			}
			///CRIA O NOVO LINK
				if ($newplantaid>0) {
					$getv = "SELECT * FROM `Traits_variation` as tv WHERE  tv.PlantaID=".$newplantaid."  AND tv.TraitID=".$otraitid;
					$sql3 = "UPDATE `Traits_variation` as tv SET tv.TraitVariation=updateimagelink(tv.TraitVariation,".$imgid.")  WHERE  tv.PlantaID=".$newplantaid."  AND tv.TraitID=".$otraitid;
					$sql4 = "INSERT INTO `Traits_variation` (`TraitID`, `TraitVariation`,`PlantaID`, `AddedBy`, `AddedDate`) VALUES (".$otraitid.",  ".$imgid.", ".$newplantaid.", ".$uuid.", ".$_SESSION['sessiondate'].")";
				} else {
					$getv = "SELECT * FROM `Traits_variation` as tv WHERE  tv.EspecimenID=".$newespecimenid."  AND tv.TraitID=".$otraitid;
					$sql3 = "UPDATE `Traits_variation` as tv SET tv.TraitVariation=updateimagelink(tv.TraitVariation,".$imgid.")  WHERE  tv.EspecimenID=".$newespecimenid."  AND tv.TraitID=".$otraitid;
					$sql4 = "INSERT INTO `Traits_variation` (`TraitID`, `TraitVariation`,`EspecimenID`, `AddedBy`, `AddedDate`) VALUES (".$otraitid.",  ".$imgid.", ".$newespecimenid.", ".$uuid.", ".$_SESSION['sessiondate'].")";
				}
				$gtrr = mysql_query($getv,$conn);
				$nrr = mysql_numrows($gtrr);
				if ($nrr>0) {
					//ATUALIZA JA EXISTE
					$gtrow = mysql_fetch_assoc($gtrr);
					//FAZ O LOG
					CreateorUpdateTableofChanges($gtrow['TraitVariationID'],'TraitVariationID','Traits_variation',$conn);
					//ATUALIZA
					$rr = mysql_query($sql3,$conn);
				} else {
					//INSERE REGISTRO
					$rr = mysql_query($sql4,$conn);
				}
				if (!$rr) {
					$erro++;
				} else {
					$succ++;
				}
				if ($erro==0) {
				echo "<br /><span width='80%' style='font-size: 1em; color: white; font-weight: bold; background-color: blue; padding: 15px; border: 1px solid black;' >OK! A imagem continua na base mas foi desvinculada da amostra!</span><br /><br />
";
			} else {
				echo "<br /><span width='80%' style='font-size: 1em; color: red; font-weight: bold; background-color: yellow; padding: 15px; border: 1px solid black;' >Houve ".$erro." erros em 2 mudanças tentadas</span><br /><br />
";
				}
			} else {
				 $oestilotb = "style='font-size: 1em; color: black; background-color: gray; padding: 15px; border: 1px solid black;' ";
				///PEGA INFO DO LINK ATUAL
				if ($plantaid>0) {
					$sql = "SELECT TAG,NOME,FAMILIA,LOCALSIMPLES FROM checklist_pllist WHERE PlantaID=".$plantaid;
				} else {
					$sql = "SELECT COLETOR,NUMERO,NOME,FAMILIA,LOCALSIMPLES FROM checklist_speclist WHERE EspecimenID=".$especimenid;
				}
				$rts = mysql_query($sql,$conn);
				if ($rts) {
					$rwo = mysql_fetch_assoc($rts);
					$otxt = implode("</td><td>",array_keys($rwo));
					$otxt = "<table ".$oestilotb."><tr><td>".$otxt."</td></tr>";
					$otxt2 = implode("</td><td>",$rwo);
					$otxt2 = "<tr><td>".$otxt2."</td></tr></table>";
					$otxt = $otxt.$otxt2;
				}
				//PEGA INFO DO NOVO LINK
				if ($newplantaid>0) {
					$sql = "SELECT TAG,NOME,FAMILIA,LOCALSIMPLES FROM checklist_pllist WHERE PlantaID=".$newplantaid;
				} else {
					$sql = "SELECT COLETOR,NUMERO,NOME,FAMILIA,LOCALSIMPLES FROM checklist_speclist WHERE EspecimenID=".$newespecimenid;
				}
				$rts = mysql_query($sql,$conn);
				if ($rts) {
					$rwo = mysql_fetch_assoc($rts);
					$ntxt = implode("</td><td>",array_keys($rwo));
					$ntxt = "<table ".$oestilotb."><tr><td>".$ntxt."</td></tr>";
					$ntxt2 = implode("</td><td>",$rwo);
					$ntxt2 = "<tr><td>".$ntxt2."</td></tr></table>";
					$ntxt = $ntxt.$ntxt2;
				}
echo "
<br />
O link atual da imagem é:<br />".$otxt;
echo "
<hr>
O novo link da imagem será:<br />".$ntxt."<br />
<br />
<form action='image_change.php' method='post'>
  <input type='hidden' name='especimenid' value='".$especimenid."' />
  <input type='hidden' name='plantaid' value='".$plantaid."' />
  <input type='hidden' name='imgid'  value='".$imgid."' />
  <input type='hidden' name='otraitid'  value='".$otraitid."' />  
  <input type='hidden' name='oquefazer'  value='".$oquefazer."' />
  <input type='hidden' name='newespecimenid'  value='".$newespecimenid."' />
  <input type='hidden' name='newplantaid'  value='".$newplantaid."' />
  <input type='hidden' name='confirmado'  value='1' />
<input type='submit' value='Confirmar' style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\" />
</form>
";
			}
		}
	}
}

$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>