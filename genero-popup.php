<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
include "functions/ImportData.php";

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
} 

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;
$gget = cleangetpost($_GET,$conn);
@extract($gget);

//CABECALHO
if (!isset($ispopup)) {
	$ispopup=1;
}
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$title = '';
$which_css = array(
"<link rel='stylesheet' type='text/css' href='css/geral.css' />",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' />"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>",
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>"
);
$title = 'Gênero';
$body= '';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "<script type='text/javascript' src='javascript/wz_tooltip.js'></script>";

//SELECIONA VALORES ANTIGOS SE FOR O CASO
if ($genusid>0 && $final!=1) {
	$qq = "SELECT * FROM Tax_Generos WHERE GeneroID='".$genusid."'";
	//echo $qq."<br/>";
	$query = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($query);
	$famid = $row['FamiliaID'];
	$genusid = $row['GeneroID'];
	$spnome = $row['Genero'];
	$nomevalido = $row['Valid'];
	$spnome = $row['Genero'];
	$autor = $row['GeneroAutor'];
	$qq = "SELECT *  FROM Vernacular WHERE TaxonomyIDS LIKE '%genero|".$genusid.";%' OR `TaxonomyIDS` LIKE '%genero|".$genusid."'";
	$rrr = mysql_query($qq,$conn);
	$nrr = mysql_numrows($rrr);
	if ($nrr>0) {
		while ($rrw = mysql_fetch_assoc($rrr)) {	
			if (empty($vernacularvalue)) {$vernacularvalue = $rrw['VernacularID'];} else {
				$vernacularvalue = $vernacularvalue.";".$rrw['VernacularID'];
			}
			
		}
	}		
	$basionym = $row['Basionym'];
	$basionymautor = $row['BasionymAutor'];
	$pubrevista = $row['PubRevista'];
	$pubvolume = $row['PubVolume'];
	$pubano = $row['PubAno'];
	$sinonimos = $row['Sinonimos'];
	$geodist = $row['GeoDistribution'];
	$notas = $row['Notas'];
	//echopre($row);
	$specieslist = describetaxacomposition($sinonimos,$conn,$includeheadings=TRUE);

	if (!empty($vernacularvalue)) {
		$vernaculartxt = describevernacular($vernacularvalue,$conn);
	}
	$ttid = GetLangVar('nameeditar')."&nbsp;".strtolower(GetLangVar('namegenus'))." &nbsp;<i>$spnome</i>";
} //if editing
else {
	$ttid = GetLangVar('namenovo')."&nbsp;".strtolower(GetLangVar('namegenus'));
}
$erro=0;
if ($final==1) {
	if (empty($spnome) || empty($nomevalido) || empty($famid) || empty($autor)) {
		echo "
<br /><table cellpadding=\"1\" width='50%' align='center' class='erro'>
<tr class='tdsmallbold' ><td align='center'>".GetLangVar('erro1')."</td></tr>";
			if (empty($spnome)) {
				echo "
<tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('namenome')."</td></tr>";
			}
			if (empty($nomevalido)) {
				echo "
<tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('namevalid')."</td></tr>";
			}
			if (empty($famid)) {
				echo "
<tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('namefamilia')."</td></tr>";
			}
			if (empty($autor)) {
				echo "
<tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('nameautor')."</td></tr>";
			}
			echo "
</table><br />";
			$erro++;
	} 
	if ($erro==0) {
				$fieldsaskeyofvaluearray = array(
			'FamiliaID' => $famid,
			'Genero' => $spnome,
			'GeneroAutor' => $autor,
			'Basionym' => $basionym,
			'BasionymAutor' => $basionymautor,
			'PubRevista' => $pubrevista,
			'PubVolume' => $pubvolume,
			'PubAno' => $pubano,
			'Sinonimos' => $sinonimos,
			'Notas' => $notas,
			'Valid' => $nomevalido
			);
			
		//get old values
		if ($genusid>0) {
			$qq = "SELECT FamiliaID,Genero,GeneroAutor,Basionym,BasionymAutor,PubRevista,PubVolume,PubAno,Sinonimos,Notas,Valid FROM Tax_Generos WHERE GeneroID='".$genusid."'";
			$qu = mysql_query($qq,$conn);
			$old = mysql_fetch_assoc($qu);
			//O VELHO É DIFERENTE DO NOVO
			$haschanged = 0;
			foreach ($old as $kk => $vv) {
				if ($vv!=$fieldsaskeyofvaluearray[$kk]) {
					$haschanged++;
				}
			}
			//SE ESTIVER INVALIDANDO O NOME CHECA SE O NOME NAO ESTA SENDO USADO
			$hasdet = 0;
			if ($old['Valid']!=$nomevalido && $nomevalido==0) {
				//check to see if any specimem has this value if so, then do not allow invalidation
				$qq = "SELECT * FROM Identidade WHERE GeneroID='".$genusid."'";
				$det = mysql_query($qq,$conn);
				$hasdet = mysql_numrows($det);
			}
			//SE NAO ESTIVER INVALIDANDO E FEZ MODIFICACOES, ENTAO CADASTRA O NOVO REGISTRO
			if ($hasdet==0 && $haschanged>0) {
					//COPIA REGISTRO ATUAL PARA TABELA Change
					CreateorUpdateTableofChanges($genusid,'GeneroID','Tax_Generos',$conn);
					$newrec = UpdateTable($genusid,$fieldsaskeyofvaluearray,'GeneroID','Tax_Generos',$conn);
					if (!$newrec) {
						$erro++;
					} else {
						$verupdate = updatevernacular($vernacularvalue,'genero',$genusid,$conn);	
					}			
			} 
			elseif ($hasdet>0) {
				$invalidationfailed++;
				$erro++;
				echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
<tr><td class='tdsmallbold' align='center'>Não pode invalidar esse nome porque ele está sendo usado!</td></tr>
</table>
<br />";
			}
		} 
		else { //insert new family
			$qq = "SELECT * FROM Tax_Generos WHERE LOWER(Genero) LIKE '".strtolower($spnome)."' AND FamiliaID='".$famid."'";
			$qu = mysql_query($qq,$conn);
			$nold = mysql_numrows($qu);
			if ($nold>0) {
				$ru = mysql_fetch_assoc($qu);
				$ggid = $ru['GeneroID'];
				echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>Já existe um gênero com esse nome para essa família!</td></tr>
</table>
<br />";				
				$erro++;
			} else {
				$newrec = InsertIntoTable($fieldsaskeyofvaluearray,'GeneroID','Tax_Generos',$conn);
				if (!$newrec) {
					echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
<tr><td class='tdsmallbold' align='center'>".GetLangVar('erro2')."</td></tr>
</table>
<br />";
					$erro++;
				} 
			}
		}
	} 
	//if $erro==0
	if ($erro==0  && (!isset($naoeimportacao) || $naoeimportacao==0)) {
		if ($newfam>0 && !$genusid>0) {
			$genusid = $newfam;
			$ggid = $newfam;
		}
		//close popup and send value
		echo "
<form >
<input type='hidden' id='generoidd' value='$ggid' />
  <script language=\"JavaScript\">
    setTimeout(
      function() {
        passnewidandtxtoselectfield('".$generofieldid."','generoidd','".$spnome."','');
      }
      ,0.0001);
  </script>
</form>";
	}
	if ($erro==0 && $naoeimportacao==1) {
			echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='success'>
  <tr><td class='tdsmallbold' align='center'>O cadastro foi feito com sucesso</td></tr>
</table>
<br />";
if ($ispopup==1) {
echo "
<form >
  <script language=\"JavaScript\">
    setTimeout(function() {this.window.close();},2000);
  </script>
</form>
";
}
	}
} 

if ($erro>0 || $final!='1') {
	if (($genusid+0)==0 && (!isset($ipnichecked) && (empty($pubrevista) || empty($autor)))) {
		if (empty($pubrevista) || empty($autor)) {
			$taxmiss = 'Publicação e/ou autor faltando!';
		}
echo "
<br />
<form name='ipniform' action=genero-popup.php method='post'>
<input type='hidden' name='ispopup' value='".$ispopup."' />
<input type='hidden' name='generofieldid' value='".$generofieldid."' />
<input type='hidden' name='famid' value='".$famid."' />
<input type='hidden' name='genusid' value='".$genusid."' />
<input type='hidden' name='naoeimportacao' value='".$naoeimportacao."' />
<table class='myformtable' align='center' cellpadding='5'>
<thead>
  <tr><td colspan='100%'>".$ttid."</td></tr>
  <tr class='subhead'><td colspan='100%'>Checar os dados do gênero $spnome em www.ipni.org?</td></tr>
</thead>
<tbody>";
//if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
//echo "<tr bgcolor = '".$bgcolor."'><td class='tdformnotes' colspan='100%'><input type='checkbox' name='proxy'>Selecionar aqui se estiver no INPA (proxy)</td></tr>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table align='center' >";
if (empty($spnome)) {
echo "
    <tr>
      <td align='center' >
         Digite o no nome do gênero
      </td>
      <td>
        <input type='text' size=24 name='spnome' value='".$spnome."' />
      </td>
    </tr>";
} else {
	echo "<input type='hidden' name='spnome' value='".$spnome."' />";  
}
echo "
    <tr>
      <input type='hidden' name='ipnichecked' value='' />
      <td align='center' >
        <input type='submit' value='Checar IPNI' class='bsubmit' onclick=\"javascript:document.ipniform.ipnichecked.value=1\" />
      </td>
      <td align='left'>
        <input type='submit' value='".GetLangVar('namecontinuar')."' class='bblue' onclick=\"javascript:document.ipniform.ipnichecked.value=2\" />
      </td>
    </tr>
  </table>
  </td>
</tr>
</tbody>
</table>
</form>
";
} 
    else {
	if ($ipnichecked==1) {
	$ipnurl = "http://www.ipni.org/ipni/advPlantNameSearch.do?find_genus=$spnome&find_authorAbbrev=&find_includePublicationAuthors=on&find_includePublicationAuthors=off&find_includeBasionymAuthors=on&find_publicationTitle=&find_isAPNIRecord=on&find_isAPNIRecord=false&find_isGCIRecord=on&find_isGCIRecord=false&find_isIKRecord=on&find_isIKRecord=false&find_rankToReturn=gen&output_format=delimited-extended&find_sortByFamily=on&find_sortByFamily=off&query_type=by_query&back_page=plantsearch";
	$filename = $spnome.".txt";	
	$ipnirulres = curl_get_file_contents($ipnurl,$proxy);
	if ($ipnirulres) {
		$relativepath = 'uploads/';
		$tbn = "tempipnicheck_".$_SESSION['userid'];
		$texttowrite = $ipnirulres;
		WriteToTXTFile($filename,$texttowrite,$relativepath);
		$qq = "DROP TABLE ".$tbn;
		mysql_query($qq,$conn);
		$qq = "SELECT * FROM `IPNIextended` WHERE Need='1'";
		$rr = mysql_query($qq,$conn);
		$nn = mysql_numrows($rr);
		$tt = "CREATE TABLE IF NOT EXISTS ".$tbn." (";
		$i=0;
		$validFields = array();
		$colnomes = array();
		while ($row = mysql_fetch_assoc($rr)) {
			$validFields[$i] = $row['ArrayIndex'];
			$colnomes[$i] = $row['NewColnames'];  
			if ($i!==($nn-1)) {
				$tt = $tt.$row['NewColnames']." ".$row['ColType'].", ";
			} else {
				$tt = $tt.$row['NewColnames']." ".$row['ColType'].")";
			}
			$i++;
		}
		$tbcreated = mysql_query($tt,$conn);
		$fop = fopen($relativepath.$filename, 'r');
		$nn = count($validFields);
		$j=0;
		while (($data = fgetcsv($fop, 0, "%%")) !== FALSE) {
			if ($j>0) {
				$spinsert = "INSERT INTO ".$tbn." (";
				for ($i = 0; $i < $nn; $i++) {
					if ($i!==($nn-1)) {
						$spinsert = $spinsert.$colnomes[$i].", ";
					} else {
						$spinsert = $spinsert.$colnomes[$i].")";
					}
				}
				$spinsert = $spinsert." VALUES (";
				for ($i = 0; $i < $nn; $i++) {
					$dd = str_replace("'","",$data[$validFields[$i]]);
					if ($i!==($nn-1)) {
						$spinsert = $spinsert."'".$dd."',";
					} else {
						$spinsert = $spinsert."'".$dd."')";
					}
				} 
			}
			mysql_query($spinsert,$conn);
			$j++;
		}
		$qq = "SELECT * FROM ".$tbn." WHERE Rank='gen.' ORDER BY PubYEAR ASC";
		$resul = mysql_query($qq,$conn);
		$numgen = mysql_numrows($resul);
		if ($numgen>1) {
			$qq = "SELECT * FROM ".$tbn." WHERE Rank='gen.' AND PubYEAR>0 ORDER BY PubYEAR ASC LIMIT 1";
			$result = mysql_query($qq,$conn);
			$genres = mysql_fetch_assoc($result);
		} else {
			$genres = @mysql_fetch_assoc($result);
		}
		if ($numgen>0) {
		$fieldsaskeyofvaluearray = array(
			'autor' => $genres['PublishingAuthor'],
			'basionym' => $genres['Basionym'],
			'basionymautor' => $genres['BasionymAuthor'],
			'pubrevista' => $genres['Publication'],
			'pubano' => $genres['PubYEAR'],
			'sinonimos' => $genres['Synonym']
			);
		unlink($relativepath."/".$filename);
		extract($fieldsaskeyofvaluearray);
		} else {
			echo "
<br />
  <table cellpadding=\"7\" align='center' class='erro'>
  <tr class='tdsmallbold' >
  <td class='tdsmallnotes' align='center'>Não encontrado ou mais de um registro em <a href='http://www.ipni.org' target='_new'>www.ipni.org</a></td></tr>
  </table>
<br />";
		}
	}
	}
echo "
<br />
<form name=specieslistform action=genero-popup.php method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input type='hidden' name='genusid' value='".$genusid."' />
  <input type='hidden' name='final' value='1' />
  <input type='hidden' name='generofieldid' value='".$generofieldid."' />
  <input type='hidden' name='naoeimportacao' value='".$naoeimportacao."' /> 
  <table class='myformtable' align='center' cellpadding='5'>
  <thead>
    <tr><td colspan='100%'>".$ttid."</td></tr>
  </thead>
  <tbody>";
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>  
    <td class='tdsmallbold' style='color:#990000' align='right'>".GetLangVar('namenome')." *</td>
    <td>
      <table>
        <tr>
          <td><input name='spnome' type='text' value='$spnome' size='30' class='selectedval' /></td>
          <td class='tdsmallbold' align='right' style='color:#990000'>".GetLangVar('nameautor')."* </td>";
			$name = $generoname." ".$spnome;
//			$redirect = "http://www.tropicos.org/NameSearch.aspx?name=".$name;
//          <td><a href='".$redirect."' target='_new' >Tropicos.org</a></td>
		echo "
          <td><input type='text' name='autor' value='".$autor."' size='15' /></td>
         <td><img style='cursor:pointer;' src='icons/mobot.png' height='18' onclick=\"javascript:small_window('http://www.tropicos.org/NameSearch.aspx?name=".$name."',800,600,'Tropicos');\" onmouseover=\"Tip('Ver registro do nome ".$name." em tropicos.org');\" /></td>
        </tr>
      </table>
    </td>
</tr>";
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>	
  <td class='tdsmallbold' style='color:#990000' align='right'>".GetLangVar('messageestenomee')." *</td>
  <td class='tdformnotes'>
    <table>";
				if ($nomevalido==1 || !isset($nomevalido)) { $ch = "checked";} else { $ch ='';}
				if ($nomevalido==0 && isset($nomevalido)) { $ch2 =  "checked";} else { $ch2 ='';}

				echo "
      <tr>
        <td align='right'><input type='radio' name='nomevalido' $ch value='1' /></td><td>".strtolower(GetLangVar('namevalido'))."</td>
        <td align='right'><input type='radio' name='nomevalido' $ch2 value='0' /></td><td>".strtolower(GetLangVar('nameinvalido'))."</td>
      </tr>
    </table>
  </td>
</tr>";
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold' style='color:#990000'  align='right'>".GetLangVar('namefamily')." *</td>
  <td>
    <table>
      <tr>
        <td>
          <select name='famid'>";
						if ($famid>0) {
							$rr = getfamilies($famid,$conn,$showinvalid=FALSE);
							$row = $rr[0];
							$famid = $rr[1];
							echo "
            <option selected value=".$row['FamiliaID'].">".$row['Familia']."</option>
            <option value=''>---</option>";
						} else {
							echo "
            <option value=''>".GetLangVar('nameselect')."</option>";						
						}
						$rrr = getfamilies('',$conn,$showinvalid=FALSE);
						while ($row = mysql_fetch_assoc($rrr)) {
							echo "
            <option value=".$row['FamiliaID'].">".$row['Familia']."</option>";
						}
			echo "
          </select>
        </td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold' align='right'>".GetLangVar('namebasionym')."</td>
  <td>
    <table>
      <tr>
        <td><input name='basionym' type='text' value='$basionym' size='30' /></td>
        <td class='tdsmallbold' align='right'>".GetLangVar('nameautor')."</td>
        <td><input name='basionymautor' type='text' value='$basionymautor' size='15' /></td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold' align='right'>".GetLangVar('namejournal')."</td>
  <td>
    <table>
      <tr>
        <td><input name='pubrevista' type='text' value='$pubrevista' size='58' /></td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold' align='right'>".GetLangVar('namevolume')."</td>
  <td>
    <table>
      <tr>
        <td><input name='pubvolume' type='text' value='$pubvolume' size='30' /></td>
        <td class='tdsmallbold' align='right'>".GetLangVar('nameano')."</td>
        <td><input name='pubano' type='text' value='$pubano' size='15' /></td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold' align='right'>".GetLangVar('namesinonimos')."</td>
  <td>
    <table>
      <tr>
        <input type='hidden' id='specieslistids' name='sinonimos' value='$sinonimos' />";
							if (empty($specieslist)) {
								echo "
        <td><textarea rows='2' cols='50' id='specieslist' name='specieslist' readonly>$specieslist</textarea></td>";
							} else {
								echo "
        <td class='tdsmalldescription'>$specieslist<input type='hidden' id='specieslist' name='specieslist' value='$specieslist' /></td>";
							}
						echo 
						"
        <td><input type='button' value='<<' class='bsubmit' ";
								$myurl ="selectspeciespopup.php?formname=specieslistform&elementname=specieslistids&destlistlist=".$sinonimos;
								echo " onclick = \"javascript:small_window('$myurl',500,400,'SelectSpecies');\" /></td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold' align='right'>".GetLangVar('namegeodistribution')."</td>
  <td>
    <table>
      <tr><td><textarea name='geodist' cols='50' rows='2' >$geodist</textarea></td></tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <input type='hidden' id='vernacularvalue' name='vernacularvalue' value='$vernacularvalue' />
  <td class='tdsmallboldright'>".GetLangVar('namevernacular')."</td>
  <td >
    <table>
      <tr>";
		if (empty($vernaculartxt)) {
					echo "
        <td class='tdformnotes' ><textarea rows='1' cols='50%' id='vernaculartxt' name='vernaculartxt' readonly>$vernaculartxt</textarea></td>";
				} else {
					echo "
        <td class='tdformnotes' ><textarea rows='1' cols='50%' id='vernaculartxt' name='vernaculartxt' readonly>$vernaculartxt</textarea></td>";
				}
			echo "
        <td><input type=button value=\"+\" class='bsubmit' ";
				$myurl ="vernacular_selector.php?formname=specieslistform&tempelement=vernaculartxt&elementname=vernacularvalue&getvernacularids=$vernacularvalue"; 		
				echo " onclick = \"javascript:small_window('$myurl',350,280,'Add_from_Src_to_Dest');\" /></td>
      </tr>
    </table>
  </td>
</tr>"; 
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold' align='right'>".GetLangVar('nameobs')."</td>
  <td>
    <table>
      <tr><td><textarea name='notas' cols='50' rows='3'>$notas</textarea></td></tr>
    </table>
  </td>
</tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
	<td colspan='100%' align='center'><input type = 'submit' class='bsubmit' value='".GetLangVar('nameenviar')."' /></td></tr>
</tbody>
</table>
</form>
";
}
}

$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>