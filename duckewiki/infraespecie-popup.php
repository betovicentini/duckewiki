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
$menu=FALSE;
$which_css = array(
"<link rel='stylesheet' type='text/css' href='css/geral.css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' />"
);
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>"
);
$title = 'InfraEspecie';
$body= '';
FazHeader($title,$body,$which_css,$which_java,$menu);

//ATUALIZA A TABELA TAX_ESPECIES SE FOR O CASO
$qu = "ALTER TABLE `Tax_InfraEspecies`  ADD `BibID` INT(10) NULL COMMENT 'BibtexID' AFTER `Morfotipo`";
@mysql_query($qu,$conn);
$qu = "ALTER TABLE `ChangeTax_InfraEspecies`  ADD `BibID` INT(10) NULL COMMENT 'BibtexID' AFTER `Morfotipo`";
@mysql_query($qu,$conn);


//SELECIONA VALORES ANTIGOS SE FOR O CASO
if ($infraspid>0 && $final!=1) {
	$qq = "SELECT Tax_InfraEspecies.*,Tax_Especies.Especie,Tax_Especies.GeneroID,Tax_Especies.EspecieID,Tax_Generos.Genero,Tax_Generos.FamiliaID FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) WHERE InfraEspecieID='$infraspid'";
	$query = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($query);
	$famid = $row['FamiliaID'];
	$genusid = $row['GeneroID'];
	$genero = $row['Genero'];
	$especie = $row['Especie'];
	$nomevalido = $row['Valid'];
	$spnome = $row['InfraEspecie'];
	$autor = $row['InfraEspecieAutor'];
	$subvar = $row['InfraEspecieNivel'];
	$qq = "SELECT *  FROM Vernacular WHERE TaxonomyIDS LIKE '%infraespecie|".$infraspid.";%' OR `TaxonomyIDS` LIKE '%infraespecie|".$infraspid."'";
	$rrr = mysql_query($qq,$conn);
	$nrr = mysql_numrows($rrr);
	if ($nrr>0) {
		while ($rrw = mysql_fetch_assoc($rrr)) {
			if (empty($vernacularvalue)) {$vernacularvalue = $rrw['VernacularID'];} else {
				$vernacularvalue = $vernacularvalue.";".$rrw['VernacularID'];
			}

		}
	}
	$qq = "SELECT *  FROM BiblioRefs WHERE BibID=".$row['BibID'];
	$rrr = mysql_query($qq,$conn);
	$rrw = mysql_fetch_assoc($rrr);
	$bibtex_txt = $rrw['BibKey'];
	$bibtex_id = $rrw['BibID'];
	$basionym = $row['Basionym'];
	$basionymautor = $row['BasionymAutor'];
	$pubrevista = $row['PubRevista'];
	$pubvolume = $row['PubVolume'];
	$pubano = $row['PubAno'];
	$sinonimos = $row['Sinonimos'];
	$geodist = $row['GeoDistribution'];
	$notas = $row['Notas'];
	if ($subvar=='morfossp' || $row['Morfotipo']==1) {
		$morfoorpubsp = 2;
	} else {
		$morfoorpubsp=1;
	}
	$specieslist = describetaxacomposition($sinonimos,$conn,$includeheadings=TRUE);

	if (!empty($vernacularvalue)) {
		$vernaculartxt = describevernacular($vernacularvalue,$conn);
	}
	$ttid = GetLangVar('nameeditar')."&nbsp;".strtolower(GetLangVar('nameinfraspecies'));
} //if editing
else {
	if ($speciesid>0) {
		$rr = getspecies($speciesid,$genusid,$conn,$showinvalid=FALSE);
		$row = mysql_fetch_assoc($rr);
		$nn = $row['Genero']." ".$row['Especie'];
	} 
	$ttid = GetLangVar('namenova')."&nbsp;".strtolower(GetLangVar('nameinfraspecies'))."&nbsp;de <i>$nn</i>";
}


$erro=0;
if ($final==1) {
	//CAMPOS OBRIGATORIOS
	if (empty($spnome) || empty($nomevalido) || empty($speciesid) || empty($subvar) || empty($autor)) {
		//entao tem um erro
		$erro++;
		echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
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
  <tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('namespecies')."</td></tr>";
		}
		if (empty($autor)) {
			echo "
  <tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('nameautor')."</td></tr>";
		}
		if (empty($subvar)) {
			echo "
  <tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('nametipo')."</td></tr>";
		}
		echo "
</table>
<br />";
	} 
	//FAZ O CADASTRO
	if ($erro==0) {
			$fieldsaskeyofvaluearray = array(
			'EspecieID' => $speciesid,
			'InfraEspecie' => $spnome,
			'InfraEspecieAutor' => $autor,
			'InfraEspecieNivel' => $subvar,
			'BibID'  => $bibtex_id+0,
			'Basionym' => $basionym,
			'BasionymAutor' => $basionymautor,
			'PubRevista' => $pubrevista,
			'PubVolume' => $pubvolume,
			'PubAno' => $pubano,
			'Sinonimos' => $sinonimos,
			'GeoDistribution' => $geodist,
			'Notas' => $notas,
			'Valid' => $nomevalido
			);
		//PEGA VALORES ANTIGOS
		if ($infraspid>0) {
			$qq = "SELECT EspecieID,InfraEspecie,InfraEspecieAutor,InfraEspecieNivel,BibID, Basionym,BasionymAutor,PubRevista,PubVolume,PubAno,Sinonimos,GeoDistribution,Notas,Valid FROM Tax_InfraEspecies WHERE InfraEspecieID=".$infraspid;
			$qu = mysql_query($qq,$conn);
			$old = mysql_fetch_assoc($qu);
			//O VELHO É DIFERENTE DO NOVO?
			$haschanged = 0;
			foreach ($old as $kk => $vv) {
				if ($vv!=$fieldsaskeyofvaluearray[$kk] || (empty($vv) && !empty($fieldsaskeyofvaluearray[$kk]))) {
					$haschanged++;
				}
			}
			//SE ESTIVER INVALIDANDO O NOME CHECA SE O NOME NAO ESTA SENDO USADO
			$hasdet = 0;
			if ($old['Valid']!=$nomevalido && $nomevalido==0) {
				//check to see if any specimem has this value if so, then do not allow invalidation
				$qq = "SELECT * FROM Identidade WHERE InfraEspecieID=".$infraspid;
				$det = mysql_query($qq,$conn);
				$hasdet = mysql_numrows($det);
			}
			//SE NAO ESTIVER INVALIDANDO E FEZ MODIFICACOES, ENTAO CADASTRA O NOVO REGISTRO
			if ($hasdet==0 && $haschanged>0) {
					//COPIA REGISTRO ATUAL PARA TABELA Change  
					CreateorUpdateTableofChanges($infraspid,'InfraEspecieID','Tax_InfraEspecies',$conn);
					$newrec = UpdateTable($infraspid,$fieldsaskeyofvaluearray,'InfraEspecieID','Tax_InfraEspecies',$conn);
					if (!$newrec) {
						$erro++;
					} else {
						$verupdate = updatevernacular($vernacularvalue,'infraespecie',$infraspid,$conn);
					}
			} else {
			 if ($hasdet>0) {
				$invalidationfailed++;
				$erro++;
				echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
<tr><td class='tdsmallbold' align='center'>Não pode invalidar esse nome porque ele está sendo usado!</td></tr>
</table>
<br />";   } else {

				echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
<tr><td class='tdsmallbold' align='center'>Não foi feita nenhuma mudança</td></tr>
</table>
<br />";
				}
			}
		} 
		else {
			$qq = "SELECT * FROM Tax_InfraEspecies WHERE LOWER(InfraEspecie) LIKE '".strtolower($spnome)."' AND EspecieID='".$speciesid."'";
			$qu = mysql_query($qq,$conn);
			$nold = mysql_numrows($qu);
			if ($nold>0) {
echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>Já existe uma infra-espécie com esse nome para essa espécie e gênero!</td></tr>
</table>
<br />";
				//PEGA O SPECIES ID DA ESPECIE QUE JA EXISTE
				if (!isset($naoeimportacao) || $naoeimportacao==0) {
					$rur = mysql_fetch_assoc($qu);
					$infraspid = $rur['InfraEspecieID'];
					$erro=0;
				} else {
					$erro++;
				}
			} 
			else {
				$newrec = InsertIntoTable($fieldsaskeyofvaluearray,'InfraEspecieID','Tax_InfraEspecies',$conn);
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
	if ($erro==0) {
		if ($newrec>0 && !$infraspid>0) {
			$infraspid = $newrec;
		}
		if ($infraspid>0) {
			TaxonomySimpleInsert($infraspid,"infspid",$conn);
			$qq = "SELECT Genero,Especie,InfraEspecie,InfraEspecieNivel FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) WHERE InfraEspecieID='".$infraspid."'";
			$qu = mysql_query($qq,$conn);
			$rr = mysql_fetch_assoc($qu);
			$spnome = $rr['Genero']."_".$rr['Especie']."_".$rr['InfraEspecie'];
		}
		//SE ESTIVER IMPORTANTO E ESTE FOR UM CADASTRO DURANTE A IMPORTACAO, ENTAO TRANSFERE O RESULTADO E FECHA A JANELA
		if (!isset($naoeimportacao) || $naoeimportacao==0) {
		echo "
<form >
<input type='hidden' id='sppid' value='$infraspid' />
<script language=\"JavaScript\">
setTimeout(
  function() {
    passnewidandtxtoselectfield('".$infraespeciefieldid."','sppid','".$spnome."','');
  }
  ,0.0001);
</script>
</form>";
		} 
		else {
			echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='success'>
  <tr><td class='tdsmallbold' align='center'>O cadastro foi feito com sucesso</td></tr>
</table>
<br />
<form >
  <script language=\"JavaScript\">
    setTimeout(function() {this.window.close();},2000);
  </script>
</form>
";
		}
}
}
if (!isset($speciesid) || empty($speciesid) || $speciesid==0) {
echo "
<form action='infraespecie-popup.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input type='hidden' name='infraespeciefieldid' value='".$infraespeciefieldid."' />
  <input type='hidden' name='infraspid' value='".$infraspid."' />
  <input type='hidden' name='spnome' value='".$spnome."' />
  <input type='hidden' name='autor' value='".$autor."' />
  <input type='hidden' name='genero' value='".$genero."' />
  <input type='hidden' name='especie' value='".$especie."' />
  <input type='hidden' name='naoeimportacao' value='".$naoeimportacao."' />
  <table class='myformtable' align='center' cellpadding='5' cellspacing='3'>
  <thead>
    <tr><td colspan='2'>Informe os dois campos abaixo</td></tr>
  </thead>
  <tbody>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
    <td class= 'tdsmallbold' style='color:#990000' align='right'>Selecione a espécie*</td>
    <td >";
  autosuggestfieldval3('search-species.php','especie',$especie,'speciesnameres','speciesid',$speciesid,true,60);
echo "
    </td>
  </tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
    <td class= 'tdsmallbold' style='color:#990000' align='right'>Digite o nome infra-específico*</td>
    <td><input type='text' name='spnome' size='40' value='".$spnome."' /></td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
    <td colspan='2' align='center'><input type='submit' class='bsubmit' value='".GetLangVar('namecontinuar')."'/></td>
  </tr>
</tbody>
</table>
</form>
";
}
if (!isset($morfoorpubsp) && empty($infraspid) && $speciesid>0 ) {
	if (!empty($spnome)) { $zz = "Selecione a opção que melhor descreve o epíteto infraespecífico \"$spnome\"";} 
	else { $zz = GetLangVar('messageselect1option');}
echo "
<form action='infraespecie-popup.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input type='hidden' name='genusid' value='".$genusid."'>
  <input type='hidden' name='infraespeciefieldid' value='".$infraespeciefieldid."' />
  <input type='hidden' name='infraspid' value='".$infraspid."' />
  <input type='hidden' name='speciesid' value='".$speciesid."' />
  <input type='hidden' name='spnome' value='".$spnome."' />
  <input type='hidden' name='autor' value='".$autor."' />
  <input type='hidden' name='genero' value='".$genero."' />
  <input type='hidden' name='especie' value='".$especie."' />
  <table class='myformtable' align='center' cellpadding='5' />
  <input type='hidden' name='naoeimportacao' value='".$naoeimportacao."' />
  <thead>
    <tr><td >".$zz."</td></tr>
  </thead>
  <tbody>
  <tr>
    <td>
      <input type='radio' name='morfoorpubsp'  value='1' onchange='this.form.submit();' />&nbsp;".GetLangVar('messagenewsppub')."
    </td>
  </tr>
  <tr align='left'>
    <td>
      <input type='radio' name='morfoorpubsp'  value='2' onchange='this.form.submit();' />&nbsp;".GetLangVar('messagenewspmorfo')."
    </td>
  </tr>
  </tbody>
  </table>
  </form>";
} 
if  (($erro>0 || $final!='1') && $speciesid>0 && ($morfoorpubsp+0)>0) {
if ($morfoorpubsp==1 && (!isset($infraspid) || ($infraspid+0)==0) && (!isset($ipnichecked) &&  (empty($pubrevista) || empty($autor)))) {
if ($infraspid>0) {
	$qu = "SELECT InfraEspecie,Especie,GeneroID,Genero, EspecieID FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) WHERE EspecieID='".$speciesid."'";
	$rq = mysql_query($qu,$conn);
	$rqw = mysql_fetch_assoc($rq);
	$genero = $rqw['Genero'];
	$spnome = $rqw['InfraEspecie'];
	$genusid = $rqw['GeneroID'];
	$especie = $rqw['Especie'];
	$speciesid = $rqw['EspecieID'];
}
echo "
<br />
<form name='ipniform' action='infraespecie-popup.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input type='hidden' name='genusid' value='".$genusid."' />
  <input type='hidden' name='infraespeciefieldid' value='".$infraespeciefieldid."' />
  <input type='hidden' name='infraspid' value='".$infraspid."' />
  <input type='hidden' name='speciesid' value='".$speciesid."' />
  <input type='hidden' name='spnome' value='".$spnome."' />
  <input type='hidden' name='autor' value='".$autor."' />
  <input type='hidden' name='genero' value='".$genero."' />
  <input type='hidden' name='especie' value='".$especie."' />
  <input type='hidden' name='morfoorpubsp' value='".$morfoorpubsp."' />
  <input type='hidden' name='naoeimportacao' value='".$naoeimportacao."' />
<table class='myformtable' align='center' cellpadding='5'>
<thead>
  <tr><td >".$ttid."</td></tr>
  <tr class='subhead'><td >Checar dados da infra-espécie $genero $especie $spnome em www.ipni.org?</td></tr>
</thead>
<tbody>";
//if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;}$bgi++;
//echo "<tr bgcolor = '".$bgcolor."'><td class='tdformnotes' colspan='100%'><input type='checkbox' name='proxy'>Selecionar aqui se estiver no INPA (proxy)</td></tr>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table align='center' >
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
		$ipnurl = "http://www.ipni.org/ipni/advPlantNameSearch.do?find_genus=$genero&find_species=$especie&find_infraspecies=$spnome&find_authorAbbrev=&find_includePublicationAuthors=on&find_includePublicationAuthors=off&find_includeBasionymAuthors=on&find_publicationTitle=&find_isAPNIRecord=on&find_isAPNIRecord=false&find_isGCIRecord=on&find_isGCIRecord=false&find_isIKRecord=on&find_isIKRecord=false&find_rankToReturn=infraspec&output_format=delimited-extended&find_sortByFamily=on&find_sortByFamily=off&query_type=by_query&back_page=plantsearch";
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
		$qq = "SELECT * FROM ".$tbn."";
		$resul = mysql_query($qq,$conn);
		$numgen = mysql_numrows($resul);
		if ($numgen>1) {
			$qq = "SELECT * FROM ".$tbn." ORDER BY PubYEAR ASC LIMIT 1";
			$result = mysql_query($qq,$conn);
			$genres = mysql_fetch_assoc($result);
		} else {
			$genres = mysql_fetch_assoc($resul);
		}
		if ($numgen>0) {
		$fieldsaskeyofvaluearray = array(
			'autor' => $genres['PublishingAuthor'],
			'basionym' => $genres['Basionym'],
			'basionymautor' => $genres['BasionymAuthor'],
			'pubrevista' => $genres['Publication'],
			'pubano' => $genres['PubYEAR'],
			'sinonimos' => $genres['Synonym'],
			'pubvolume' => $genres['Collation'],
			'subvar' => $genres['Rank']
			);
		unlink($relativepath."/".$filename);
		extract($fieldsaskeyofvaluearray);
		} else {
			echo "
<br />
  <table cellpadding=\"7\" align='center' class='erro'>
  <tr><td class='tdsmallnotes' align='center'>Não encontrado ou mais de um registro em <a href='http://www.ipni.org' target='_new'>www.ipni.org</a></td></tr>
  </table>
<br />";
		}
	}
	}
echo "<br />
<form name=specieslistform action='infraespecie-popup.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input type='hidden' name='infraspid' value='".$infraspid."' />
  <input type='hidden' name='final' value='1' />
  <input type='hidden' name='infraespeciefieldid' value='".$infraespeciefieldid."' />
  <input type='hidden' name='naoeimportacao' value='".$naoeimportacao."' />
  <input type='hidden' name='morfoorpubsp' value='".$morfoorpubsp."' />
  <table class='myformtable' align='center' cellpadding='5' />
  <thead>
    <tr><td colspan='2'>".$ttid."</td></tr>
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
          <td class='tdsmallbold' style='color:#990000' align='right'>".GetLangVar('nameautor')." *</td>";
					if ($morfoorpubsp==1) {
								$name = $genero." ".$especie." ".$spnome;
								$redirect = "http://www.tropicos.org/NameSearch.aspx?name=".$name; 
						echo "
          <td><input type='text' name='autor' value='".$autor."' size='15' /></td>
                   <td><img style='cursor:pointer;' src='icons/mobot.png' height='18' onclick=\"javascript:small_window('http://www.tropicos.org/NameSearch.aspx?name=".$name."',800,600,'Tropicos');\" onmouseover=\"Tip('Ver registro do nome ".$name." em tropicos.org');\" /></td>";
//          <td><a href='".$redirect."' target='_new' >Tropicos.org</a></td>";        
					} elseif ($morfoorpubsp==2) {
						echo "
          <td>
            <select  id='autorid' name='autor' >";
          if (!empty($autor)) {
echo "<option selected value='".$autor."'>".$autor."</option>";
		} else {          
echo "<option selected value=''>".GetLangVar('nameselect')."</option>";
	}            
						$rrr = getpessoa('',$abb=true,$conn);
						while ($row = mysql_fetch_assoc($rrr)) {
							$rv = trim($row['Abreviacao']);
							if (!empty($rv)) {
								echo "
            <option value='".$row['Abreviacao']."'>".$row['Abreviacao']." (".$row['Prenome'].")</option>";
							}
						}
						echo "
          </select>
        </td>
        <td><img src='icons/list-add.png' height=15 ";
			$myurl ="novapessoa-form-popup.php?pessoaid_val=autorid";
			echo " onclick = \"javascript:small_window('$myurl',500,350,'Nova Pessoa');\"></td>";
					}
echo "
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
  <td class='tdsmallbold' style='color:#990000'  align='right'>".GetLangVar('namespecies')." *</td>
  <td>
    <table>
      <tr>
        <td>
          <select name='speciesid'>";
if ($speciesid>0) {
	$rr = getspecies($speciesid,$genusid,$conn,$showinvalid=FALSE);
	$row = mysql_fetch_assoc($rr);
	echo "
            <option selected value=".$row['EspecieID'].">".$row['Genero']." ".$row['Especie']."</option>
            <option value=''>---</option>";
} else {
	echo "
            <option value=''>".GetLangVar('nameselect')."</option>";
}
$rrr = getspecies('',$genusid,$conn,$showinvalid=FALSE);
while ($row = mysql_fetch_assoc($rrr)) {
	echo "
            <option value=".$row['EspecieID'].">".$row['Genero']." ".$row['Especie']." [".$row['Familia']."]</option>";
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
  <td class='tdsmallbold' style='color:#990000'  align='right'>".GetLangVar('nametipo')." *</td>
  <td>
    <table>
      <tr>
";
if ($morfoorpubsp==1) {
 echo "
         <td>
          <select name='subvar'>";
	if (!empty($subvar)) {
		echo "
            <option selected value='".$subvar."'>".$subvar."</option>
            <option value=''>---</option>";
	} else {
		echo "
            <option value=''>".GetLangVar('nameselect')."</option>";
	}
	$qq = "SELECT DISTINCT InfraEspecieNivel FROM Tax_InfraEspecies ORDER BY InfraEspecieNivel";
	$qqq = mysql_query($qq,$conn);
	while ($rw = mysql_fetch_assoc($qqq)) {
		echo "
            <option value='".$rw['InfraEspecieNivel']."'>".$rw['InfraEspecieNivel']."</option>";
	}
	echo "
          </select>";
} elseif ($morfoorpubsp==2) {
	if (empty($subvar)) {
		$subvar = 'morfossp';
	}
	echo "<td class='tdformnotes'>
          <input type='text' name='subvar' value='".$subvar."' readonly style='background-color: #cccccc;'/>&nbsppadronizado";
}
echo "
        </td>
      </tr>
    </table>
  </td>
</tr>";
if ($morfoorpubsp==1) {
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<td class='tdsmallboldright'>Referência&nbsp;&nbsp;<img height='15' src=\"icons/icon_question.gif\" ";
		$help = "Indique uma referência bibliográfica com a publicação do nome da infraespécie. Essa informação é usada na geração de monografias em formato FitoTaxa";
		echo " onclick=\"javascript:alert('$help');\" />
</td>
  <td >
    <table>
      <tr>
        <td ><span id='bibtex_txt'>".$bibtex_txt."</span><input type='hidden' id='bibtex_id' name='bibtex_id'  value='".$bibtex_id."'></td>
        <td><input type=button style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Bibliografia'  onmouseover=\"Tip('Escolha a referência de publicação do nome');\" ";
		$myurl = "bibtext-gridsave.php?bibtex_txt=bibtex_txt&bibtex_id=bibtex_id&bibids=".$bibtex_id;
		echo " onclick = \"javascript:small_window('".$myurl."',800,600,'Referências Bibliográficas');\" /></td>
      </tr>
    </table>
  </td>
</tr>
";
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
        <input type='hidden' id='specieslistids' name='sinonimos' value='".$sinonimos."' />";
		if (empty($specieslist)) {
			echo "
        <td><textarea rows='2' cols='50' id='specieslist' name='specieslist' readonly>".$specieslist."</textarea></td>";
		} else {
			echo "
        <input type='hidden' id='specieslist' name='specieslist' value='".$specieslist."' />
        <td class='tdsmalldescription'>$specieslist</td>";
		}
	echo "
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
      <tr><td><textarea name='geodist' cols='50' rows='2' >".$geodist."</textarea></td></tr>
    </table>
  </td>
</tr>";
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>".GetLangVar('namevernacular')."</td>
  <td >
    <table>
      <tr>";
		if (empty($vernaculartxt)) {
			echo "
        <td class='tdformnotes' >
          <input type='hidden' id='vernacularvalue' name='vernacularvalue' value='".$vernacularvalue."' />
          <textarea rows=1 cols='50'% id='vernaculartxt' name='vernaculartxt' readonly>$vernaculartxt</textarea>";
		} else {
			echo "
        <td class='tdformnotes' >
          <textarea rows=1 cols='50'% id='vernaculartxt' name='vernaculartxt' readonly>$vernaculartxt</textarea>";
				}
			echo "
        </td>
        <td><input type=button value=\"+\" class='bsubmit' ";
		$myurl ="vernacular_selector.php?formname=specieslistform&tempelement=vernaculartxt&elementname=vernacularvalue&getvernacularids=$vernacularvalue";
		echo " onclick = \"javascript:small_window('$myurl',350,280,'Add_from_Src_to_Dest');\" /></td>
      </tr>
    </table>
  </td>
</tr>";
}
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
  <td colspan='2'>
    <table align='center'>
      <tr><td align='center'><input type = 'submit' class='bsubmit' value='".GetLangVar('nameenviar')."' /></td></tr>
    </table>
  </td>
</tr>
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