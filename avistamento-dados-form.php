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
$ispopup=1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$bgi=1;

$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' >",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' >",
"<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"css/autosuggest.css\" />"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>",
"<script type=\"text/javascript\" src=\"javascript/ajax_framework.js\"></script>",
"<script type=\"text/javascript\" src=\"javascript/mootools.js\"></script>"
);
$body='';
$title = 'Entrando Dados Avistamento';
FazHeader($title,$body,$which_css,$which_java,$menu);




//SE ESTIVER ABRINDO O FORMULARIO, CHECA PARA DADOS EXISTENTES NA TRILHA
if (empty($final) && empty($ns)) {
	$qq = "SELECT avis.GPSPointID,avis.DadosID, avis.TaxonomiaID,trl.Name,trl.Data, avis.Obs, avis.Nindiv, expd.Name as expedicao FROM Expedicoes_Dados as avis LEFT JOIN GPS_DATA as gps ON gps.PointID=avis.GPSPointID LEFT JOIN Expedicao_Trilhas as trl ON trl.TrilhaID=gps.TrilhaID LEFT JOIN Expedicoes as expd ON trl.ViagemID=expd.ViagemID WHERE gps.TrilhaID=".$trilhaid." ORDER BY gps.Name,gps.TimeOriginal";
	$rrr = mysql_query($qq,$conn);
	@$res = mysql_query($qq,$conn);
	@$nres = mysql_numrows($res);
	
	if ($nres>0) {  //se ja existem registros
		$titulo = '';
		$nlinhas = $nres;
		$taxonomias = array();
		$editando = array();
		$observa = array();
		$individuos = array();
		$ptgps = array();
		$j=0;
		while ($rw = mysql_fetch_assoc($res)) {
			if ($j==0) {
				$titulo = $rw['Name']."  [".$rw['Data']." ".$rw['expedicao']."]";
			}
			$taxonomias[$j] = $rw['TaxonomiaID'];
			$editando[$j] = $rw['DadosID'];
			$observa[$j] = $rw['Obs'];
			$individuos[$j] = $rw['Nindiv'];
			$ptgps[$j] = $rw['GPSPointID'];
			$j++;
		}
	}
} 

//INDICES DE LINHA
if (empty($ns) && !isset($nlinhas)) { $nlinhas=5;} 
if ($nlinhas<5) { $nlinhas=5;}
//SE ESTIVER ADICIONANDO LINHAS
if ($final==2) { $nlinhas=$ns+5; }

//SE TIVER ENVIADO PARA  SALVAR
if ($final==1) { 

	//CRIA A TABELA SE ELA AINDA NÃO EXISTE
	$qq = "CREATE TABLE IF NOT EXISTS Expedicoes_Dados (
				DadosID INT(10) unsigned NOT NULL auto_increment,
				GPSPointID INT(10),
				TaxonomiaID CHAR(200),
				Nindiv INT(10),
				Obs VARCHAR(10000),
				AddedBy INT(10),
				AddedDate DATE,
				PRIMARY KEY (DadosID))";
	mysql_query($qq,$conn);

	//CHECA TODAS AS LINHAS E CADASTRA SE FOR O CASO
	$sucesso=0;
	$atualizados=0;
	$semmudanca=0;
	$erro=0;
	$nlinhas = count($linhas);
	$incompletas = 0;
	foreach ($linhas as $ii) {
			$oldid = $editando[$ii]+0;
			
			$tz = "nomesciid_".$ii;
			$taxid = trim($$tz);
			
			$obs = $observa[$ii];
			$nind = $individuos[$ii];
			$gps = $ptgps[$ii];
			$arrayofvalues = array(
				'GPSPointID' => $gps,
				'TaxonomiaID' => $taxid,
				'Nindiv' => $nind,
				'Obs' => $obs
				);
			//SE TEM OS DADOS MÍNIMOS NECESSARIOS
			if (($gps+0)>0 && !empty($taxid) && ($nind+0)>0) {
				//entao editando
				if ($oldid>0) {
					$idd = $editando[$ii]+0;
					$upp = CompareOldWithNewValues('Expedicoes_Dados','DadosID',$idd,$arrayofvalues,$conn);
					if (!empty($upp) && $upp>0) {
						CreateorUpdateTableofChanges($idd,'DadosID','Expedicoes_Dados',$conn);
						$newplanta = UpdateTable($idd,$arrayofvalues,'DadosID','Expedicoes_Dados',$conn);
						if (!$newplanta) {
							$erro++;
						} else {
							$atualizados++;
						}
					} else {
							$semmudanca++;
					}
				} else { //entao inserindo
					$newplanta = InsertIntoTable($arrayofvalues,'DadosID','Expedicoes_Dados',$conn);
					if (!$newplanta) {
						$erro++;
					} else {
						$sucesso++;
					}
				}
			} else {
				$incompletas++;
			}
			$ii++;
		}
	//INFORMA O QUE ACONTECEU QUANDO SALVANDO OS DADOS
	if ($sucesso>0) {
		echo "
<br />
<table class='success' align='center' padding='7'>
  <tr><td >$sucesso registros novos foram cadastrados.</td><tr>
</table>
<br />";
	}
	if ($atualizados>0) {
		echo "
<table class='success' align='center' padding='7'>
  <tr><td>$atualizados registros foram atualizados.</td><tr>
</table>
<br />";
	}
	if ($semmudanca>0) {
		echo "
<table class='success' align='center' padding='7'>
<tr><td>$semmudanca registros existentes n&atilde;o foram alterados.</td><tr>
</table>
<br />";
	}
	if ($incompletas>0) {
		echo "
<table class='success' align='center' padding='7'>
<tr><td>$incompletas linhas estavam incompletas ou sem informação</td><tr>
</table>
<br />";
	}	
	if ($erro>0) {
		echo "
<form action='avistamento-dados-form.php' method='post'>";
$ppost['final'] = '';
foreach ($ppost as $kk => $vv) {
	echo "
<input type='hidden' value='".$vv."'  name='".$kk."'  />";
}
echo "
<table class='erro' align='center' padding='7'>
<tr><td>$erro registros não puderam ser registrados!</td><tr>
<tr><td> <input style='cursor: pointer;'  type='submit' value='Editar os dados'  /></td><tr>
</table>
</form>
<br />";
	}

echo 
"<table align='center'>
<tr><td>
<input style='cursor: pointer;'  type='button' value='Fechar' class='bsubmit' onclick=\"javascript:window.close();\" />
</td></tr>
</table>";

} else {
echo "
<br />
<form name='coletaform' action='avistamento-dados-form.php' method='post'>
<input type=hidden name='trilhaid' value='".$trilhaid."'>
<input type=hidden name='datacol' value='".$datacol."'>
<input type=hidden name='titulo' value='".$titulo."'>
<input type='hidden' name='ispopup' value='$ispopup' />
<table class='myformtable' align='center' cellpadding='7'>
<thead><tr><td colspan='4'>".$titulo."</td></tr></thead>
<tbody>";
//habitat descricao
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
      <td align='center'>PontoGPS</td>
      <td align='center'>Especie</td>
      <td align='center'>N.Ind</td>
      <td align='center'>Obs</td>
</tr>";
for ($i=0;$i<$nlinhas;$i++) {
echo "
<input type=hidden name='linhas[$i]' value='".$i."' />
<input type=hidden name='editando[$i]' value='".$editando[$i]."' />";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>";
//PONTOS DE GPS DA TRILHA
echo "
<td align='center'>
 <select name='ptgps[".$i."]'>
 ";
$qq = "SELECT gps.PointID,gps.Name as ponto, gps.TimeOriginal as time FROM GPS_DATA as gps JOIN Expedicao_Trilhas as trl ON trl.TrilhaID=gps.TrilhaID JOIN Expedicoes as expd ON trl.ViagemID=expd.ViagemID WHERE  trl.TrilhaID=".$trilhaid."  AND gps.Type='Waypoint'  ORDER BY gps.Name,gps.TimeOriginal";
$rrr = mysql_query($qq,$conn);

if (isset($ptgps[$i])) {
	$valz = $ptgps[$i];
	$txt = '';
} else {
	 $valz = -20;
	 $txt = 'selected';
}
	 echo "
	 <option $txt value=''>Selecione</option>";
while ($row = mysql_fetch_assoc($rrr)) {
		$val = $row['PointID'];
	if ($valz==$val) {
		$txt = 'selected';
	} else {
		$txt = '';
	}
	echo "
      <option $txt value='".$val."'>".$row['ponto']." [".$row['time']."]</option>";
}
echo "
</select>
</td>";
$zz  = "nomesciid_".$i;
$nomesci ='';
if (isset($taxonomias[$i])) {
		$nomesci = strip_tags(gettaxatxt($taxonomias[$i],$conn));
		$$zz = $taxonomias[$i];
} elseif (!empty($$zz)) {
		$nomesci = strip_tags(gettaxatxt($$zz,$conn));
}
	echo "
<td style='border: 0px'>";
autosuggestfieldval3("search-name-simple.php","nomesci_".$i, $nomesci,"nomeres_".$i,"nomesciid_".$i,$$zz,true,50); 
echo "</td>";
echo "
<td class='tdformnotes'><input type='text' name='individuos[".$i."]' value='".$individuos[$i]."' /></td>
<td class='tdformnotes'><textarea name='observa[".$i."]'>".$observa[$i]."</textarea></td>
</tr>";
}

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
 <td align='center' colspan='2'>
   <input type='hidden' name='ns' value='$nlinhas' />
   <input type='hidden' name='final' value='' />
   <input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' onclick=\"javascript:document.coletaform.final.value=1\" />
 </td>
 <td align='center' colspan='2'>
   <input type='submit' value='Adicionar linhas' class='bblue' onclick=\"javascript:document.coletaform.final.value=2\" />
 </td>
</tr>
</tbody>
</table>
</form>
";
}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>