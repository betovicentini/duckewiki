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

//echopre($ppost);
//CABECALHO
$ispopup=1;
$menu = FALSE;
$title = '';
$which_css = array(
"<link rel='stylesheet' type='text/css' href='css/geral.css' />","<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' />");
$which_java = array("<script type='text/javascript' src='javascript/ajax_framework.js'></script>");
$title = 'Editando Especie';
$body= '';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "<script type='text/javascript' src='javascript/wz_tooltip.js'></script>";

//ATUALIZA A TABELA TAX_ESPECIES SE FOR O CASO
$qu = "ALTER TABLE `Tax_Especies`  ADD `BibID` INT(10) NULL COMMENT 'BibtexID' AFTER `Morfotipo`";
@mysql_query($qu,$conn);
$qu = "ALTER TABLE `ChangeTax_Especies`  ADD `BibID` INT(10) NULL COMMENT 'BibtexID' AFTER `Morfotipo`";
@mysql_query($qu,$conn);

$qu = "ALTER TABLE `Tax_Especies`  ADD `MycobankID` INT(10) NULL AFTER `IpniID`";
@mysql_query($qu,$conn);
$qu = "ALTER TABLE `ChangeTax_Especies`  ADD `MycobankID` INT(10) NULL AFTER `IpniID`";
@mysql_query($qu,$conn);

//SELECIONA VALORES ANTIGOS SE FOR O CASO
if ($speciesid>0 && $final!=1) {
	$qq = "SELECT Tax_Especies.*,Tax_Generos.Genero,Tax_Generos.FamiliaID FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) WHERE EspecieID='$speciesid'";
	$query = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($query);
	$famid = $row['FamiliaID'];
	$genusid = $row['GeneroID'];
	$genus = $row['Genero'];
	$nomevalido = $row['Valid'];
	$spnome = $row['Especie'];
	$autor = $row['EspecieAutor'];
	$qq = "SELECT *  FROM Vernacular WHERE TaxonomyIDS LIKE '%especie|".$speciesid.";%' OR `TaxonomyIDS` LIKE '%especie|".$speciesid."'";
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
	$ipniid = $row['IpniID'];
	$mycobankid = $row['MycobankID'];
	$basionym = $row['Basionym'];
	$basionymautor = $row['BasionymAutor'];
	$pubrevista = $row['PubRevista'];
	$pubvolume = $row['PubVolume'];
	$pubano = $row['PubAno'];
	$sinonimos = $row['Sinonimos'];
	$geodist = $row['GeoDistribution'];
	$notas = $row['Notas'];
	$mftp = $row['Morfotipo'];
	if ($mftp==1) {
		$morfoorpubsp=2;
	} else {
		$morfoorpubsp=1;
	}
	$specieslist = describetaxacomposition($sinonimos,$conn,$includeheadings=TRUE);

	if (!empty($vernacularvalue)) {
		$vernaculartxt = describevernacular($vernacularvalue,$conn);
	}
	$ttid = GetLangVar('nameeditar')."&nbsp;".mb_strtolower(GetLangVar('namespecies'))." &nbsp;<i>$spnome</i>";
} //if editing
else {
	$ttid = GetLangVar('namenova')."&nbsp;".mb_strtolower(GetLangVar('namespecies'));
}

//FAZ O CADASTRO OU MODIFICACAO SE O FORMULARIO FOI ENVIADO
$erro=0;
if ($final==1) {
	//CAMPOS OBRIGATORIOS
	if (empty($spnome) || (empty($nomevalido) && $nomevalido!=0) || empty($genusid) || empty($autor)) {
		//entao tem um erro
		$erro++;
		echo "
<br />
<table cellpadding=\"6\"  align='left' class='erro'>
  <tr class='tdsmallbold' ><td align='center'>".GetLangVar('erro1')."</td></tr>";
		if (empty($spnome)) {
			echo "
  <tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('namenome')."</td></tr>";
		}
		if (empty($nomevalido)) {
			echo "
  <tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('namevalid')."</td></tr>";
		}
		if (empty($genusid)) {
			echo "
  <tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('namegenus')."</td></tr>";
		}
		if (empty($autor)) {
			echo "
  <tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('nameautor')."</td></tr>";
		}
		echo "
</table>
<br />";
	} 
	//FAZ O CADASTRO
	if ($erro==0) {
			$fieldsaskeyofvaluearray = array(
			'GeneroID' => $genusid,
			'Especie' => $spnome,
			'EspecieAutor' => $autor,
			'BibID'  => $bibtex_id+0,
			'IpniID'  => $ipniid,
			'MycobankID'  => $mycobankid,
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
			//echopre($fieldsaskeyofvaluearray);
		//PEGA OS VALORES ANTIGOS SE ESTIVER EDITANDO
		if ($speciesid>0) {
			$qq = "SELECT GeneroID,Especie,EspecieAutor,BibID,Basionym,BasionymAutor,PubRevista,PubVolume,PubAno,Sinonimos,GeoDistribution,Notas,IpniID,MycobankID FROM Tax_Especies WHERE EspecieID=".$speciesid;
			$qu = mysql_query($qq,$conn);
			$old = mysql_fetch_assoc($qu);
			//O VELHO É DIFERENTE DO NOVO
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
				$qq = "SELECT * FROM Identidade WHERE EspecieID=".$speciesid;
				$det = mysql_query($qq,$conn);
				$hasdet = mysql_numrows($det);
			}
			//SE NAO ESTIVER INVALIDANDO E FEZ MODIFICACOES, ENTAO CADASTRA O NOVO REGISTRO
			if ($hasdet==0 && $haschanged>0) {
					//COPIA REGISTRO ATUAL PARA TABELA Change  
					CreateorUpdateTableofChanges($speciesid,'EspecieID','Tax_Especies',$conn);
					//FAZ O UPDATE DO REGISTRO
					$newrec = UpdateTable($speciesid,$fieldsaskeyofvaluearray,'EspecieID','Tax_Especies',$conn);
					if (!$newrec) {
						$erro++;
					} else {
						//SE ATUALIZOU, ATUALIZA O REGISTRO VERNACULAR TAMBEM
						$verupdate = updatevernacular($vernacularvalue,'especie',$speciesid,$conn);
					}
			}  //SE O NOME ESTÁ SENDO USADO ENTAO
			else {
			 if ($hasdet>0) {
				$invalidationfailed++;
				$erro++;
				echo "
<br />
<table cellpadding=\"1\" width='50%' align='left' class='erro'>
<tr><td class='tdsmallbold' align='center'>Não pode invalidar esse nome porque ele está sendo usado!</td></tr>
</table>
<br />";   } else {

				echo "
<br />
<table cellpadding=\"1\" width='50%' align='left' class='erro'>
<tr><td class='tdsmallbold' align='center'>Não foi feita nenhuma mudança</td></tr>
</table>
<br />";
				}
			}

		} 
		//CASO NAO ESTEJA EDITANDO, ETNAO É NOVO CADASTRO
		else { 
			//CHECA SE NAO EXISTE UMA ESPÉCIE COM MESMO NOME (GRAFIA IDENTICA) PARA O GENERO EM QUESTAO
			$qq = "SELECT * FROM Tax_Especies WHERE LOWER(Especie) LIKE '".mb_strtolower($spnome)."' AND GeneroID='".$genusid."'";
			$qu = mysql_query($qq,$conn);
			$nold = mysql_numrows($qu);
			//SE HA OUTRA ESPECIE AVISA
			if ($nold>0) {
				echo "
<br />
<table cellpadding=\"1\" width='50%' align='left' class='erro'>
  <tr><td class='tdsmallbold' align='center'>Já existe uma espécies com esse nome para esse gênero!</td></tr>
</table>
<br />";
				//PEGA O SPECIES ID DA ESPECIE QUE JA EXISTE
				if (!isset($naoeimportacao) || $naoeimportacao==0) {
					$rur = mysql_fetch_assoc($qu);
					$speciesid = $rur['EspecieID'];
					$erro=0;
				} else {
					$erro++;
				}
			} 
			//CASO CONTRÁRIO INSERE O NOVO REGISTRO
			else {
				//se for um morfotipo anota no campo morfotipo
				if ($morfoorpubsp==2) {
					//altera tabela se for o caso
					$qq = "ALTER TABLE `Tax_Especies`  ADD `Morfotipo` INT(10) NOT NULL AFTER `Valid`";
					@mysql_query($qq,$conn);
					$qq = "ALTER TABLE `ChangeTax_Especies`  ADD `Morfotipo` INT(10) NOT NULL AFTER `Valid`";
					@mysql_query($qq,$conn);
					$fieldsaskeyofvaluearray = array_merge((array)$fieldsaskeyofvaluearray,(array)array('Morfotipo'=> 1));
				}
				$newrec = InsertIntoTable($fieldsaskeyofvaluearray,'EspecieID','Tax_Especies',$conn);
				if (!$newrec) {
					echo "
<br />
<table cellpadding=\"1\" width='50%' align='left' class='erro'>
<tr><td class='tdsmallbold' align='center'>".GetLangVar('erro2')."</td></tr>
</table>
<br />";
				$erro++;
				} 
			}
		}
	} 
	//SE FEZ O CADASTRO E NAO HOUVE ERRO, FECHA A JANELA
	if ($erro==0) {
		if ($newrec>0 && !$speciesid>0) {
			$speciesid = $newrec;
		}
		if ($speciesid>0) {
			TaxonomySimpleInsert($speciesid,"speciesid",$conn);
			$qq = "SELECT * FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) WHERE EspecieID='".$speciesid."'";
			$qu = mysql_query($qq,$conn);
			$rr = mysql_fetch_assoc($qu);
			$spnome = $rr['Genero']." ".$rr['Especie'];
		}
		//SE ESTIVER IMPORTANTO E ESTE FOR UM CADASTRO DURANTE A IMPORTACAO, ENTAO TRANSFERE O RESULTADO E FECHA A JANELA
		if (!isset($naoeimportacao) || $naoeimportacao==0) {
		echo "
<form >
<input type='hidden' id='sppid' value='".$speciesid."' >
<script language=\"JavaScript\">
setTimeout(
  function() {
    passnewidandtxtoselectfield('".$especiefieldid."','sppid','".$spnome."','');
  }
  ,0.0001);
</script>
</form>";
		} 
		else {
			echo "
<br />
<table cellpadding=\"1\" width='50%' align='left' class='success'>
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

if (!isset($genusid) || empty($genusid)) {
echo "
<form action='especie-popup.php' method='post'>
  <input type='hidden' name='speciesid' value='".$speciesid."' />
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input type='hidden' name='especiefieldid' value='".$especiefieldid."' />
  <input type='hidden' name='autor' value='".$autor."' />
  <input type='hidden' name='naoeimportacao' value='".$naoeimportacao."' />
  <table class='myformtable' align='left' cellpadding='5' cellspacing='3'>
  <thead>
    <tr><td colspan='2'>Informe os dois campos abaixo</td></tr>
  </thead>
  <tbody>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
    <td class= 'tdsmallbold' style='color:#990000' align='right'>Selecione o gênero*</td>
    <td >";
  autosuggestfieldval3('search-famgen.php','genero',$genero,'genusres','genusid',$genusid,true,60);
echo "
    </td>
  </tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
    <td class= 'tdsmallbold' style='color:#990000' align='right'>Digite o nome da espécie*</td>
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
if (!isset($morfoorpubsp) && empty($speciesid) && $genusid>0) {
	if (!empty($spnome)) { 
		$zz = "Selecione a opção que melhor descreve o epíteto \"$spnome\"";
	} 
	else { 
		$zz = GetLangVar('messageselect1option');
	}
echo "
<form action='especie-popup.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input type='hidden' name='genusid' value='".$genusid."' />
  <input type='hidden' name='speciesid' value='".$speciesid."' />
  <input type='hidden' name='especiefieldid' value='".$especiefieldid."' />
  <input type='hidden' name='spnome' value='".$spnome."' />
  <input type='hidden' name='autor' value='".$autor."' />
  <input type='hidden' name='genero' value='".$genero."' />
  <input type='hidden' name='naoeimportacao' value='".$naoeimportacao."' />
  <table class='myformtable' align='left' cellpadding='5' cellspacing='3'>
  <thead>
    <tr><td >".$zz."</td></tr>
  </thead>
  <tbody>
  <tr>
    <td>
      <input type='radio' name='morfoorpubsp' value='1' onchange=\"this.form.submit();\" />&nbsp;".GetLangVar('messagenewsppub')."
    </td>
  </tr>
  <tr>
    <td>
      <input type='radio' name='morfoorpubsp'  value='2' onchange=\"this.form.submit();\" />&nbsp;".GetLangVar('messagenewspmorfo')."
    </td>
  </tr>
</tbody>
</table>
</form>

";
} 
if (($erro>0 || $final!='1') && $genusid>0 && ($morfoorpubsp+0)>0) {
	if ($morfoorpubsp==1 && (!isset($speciesid) || $speciesid==0) && (!isset($ipnichecked) &&  (empty($pubrevista) || empty($autor)))) {
		if ($genusid>0) {
			$qu = "SELECT Genero FROM Tax_Generos WHERE GeneroID='".$genusid."'";
			$rq = mysql_query($qu,$conn);
			$rqw = mysql_fetch_assoc($rq);
			$genero = $rqw['Genero'];
		}
echo "
<br />
<form name='ipniform' action=especie-popup.php method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input type='hidden' name='genusid' value='".$genusid."' />
  <input type='hidden' name='speciesid' value='".$speciesid."' />
  <input type='hidden' name='especiefieldid' value='".$especiefieldid."' />
  <input type='hidden' name='spnome' value='".$spnome."' />
  <input type='hidden' name='genero' value='".$genero."' />
  <input type='hidden' name='morfoorpubsp' value='".$morfoorpubsp."' />
  <input type='hidden' name='naoeimportacao' value='".$naoeimportacao."' />
<table class='myformtable' align='left' cellpadding='5'>
<thead>
  <tr><td >".$ttid."</td></tr>
  <tr class='subhead'><td>Checar dados da espécie ".$genero." ". $spnome."?</td></tr>
</thead>
<tbody>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table align='center' >
    <tr>
      <input type='hidden' name='ipnichecked' value='' />
      <td align='center' >
        <input type='submit' value='Checar IPNI' class='bsubmit' onclick=\"javascript:document.ipniform.ipnichecked.value=1\" />
      </td>
      <td align='center'>
        <input type='submit' value='".GetLangVar('namecontinuar')."' class='bblue' onclick=\"javascript:document.ipniform.ipnichecked.value=2\" />
      </td>
      <td align='center' >
        <input type='submit' value='Mycobank' class='borange' onclick=\"javascript:document.ipniform.ipnichecked.value=3\" />
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
	$ipnurl = "http://www.ipni.org/ipni/advPlantNameSearch.do?find_genus=$genero&find_species=$spnome&find_authorAbbrev=&find_includePublicationAuthors=on&find_includePublicationAuthors=off&find_includeBasionymAuthors=on&find_publicationTitle=&find_isAPNIRecord=on&find_isAPNIRecord=false&find_isGCIRecord=on&find_isGCIRecord=false&find_isIKRecord=on&find_isIKRecord=false&find_rankToReturn=spec&output_format=delimited-extended&find_sortByFamily=on&find_sortByFamily=off&query_type=by_query&back_page=plantsearch";
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
		} 
		else {
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
			'ipniid' => $genres['IpniID']
			);
		unlink($relativepath."/".$filename);
		extract($fieldsaskeyofvaluearray);
		} else {
			echo "
<br />
  <table cellpadding=\"7\" align='left' class='erro'>
  <tr class='tdsmallbold' >
  <td class='tdsmallnotes' align='center'>Não encontrado ou mais de um registro em <a href='http://www.ipni.org' target='_new'>www.ipni.org</a></td></tr>
  </table>
<br />";
		}
	}
}

	if ($ipnichecked==3 && !empty($spnome)) {
			$resultado = getmycobankdata($genero,$spnome,"","");
			//echopre($resultado);
			if (is_array($resultado)) {
				extract($resultado["dados"]);
				$txt = $resultado["resposta"];
			}  else {
				$txt = $resultado;
			}
			if ($erro==0 && $naoeimportacao==1) {
			echo "
<br />
<span style='background-color: yellow; color: red; font-size: 1.1em; padding: 5px; align: left;'  >".$txt."</span>
<br /><br /><br />
";
			}
	}
echo "
<form name=specieslistform action=especie-popup.php method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input type='hidden' name='speciesid' value='".$speciesid."' />
  <input type='hidden' name='morfoorpubsp' value='".$morfoorpubsp."' />
  <input type='hidden' name='especiefieldid' value='".$especiefieldid."' />
  <input type='hidden' name='naoeimportacao' value='".$naoeimportacao."' />
  <input type='hidden' name='final' value='1' />
<table class='myformtable' align='left' cellpadding='7'>
<thead>
  <tr><td colspan='2'>".$ttid."</td></tr>
</thead>
<tbody>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold' style='color:#990000'  align='right'>".GetLangVar('namegenus')." *</td>
  <td>
    <table>
      <tr>
        <td>
          <select name='genusid'>";
	if ($genusid>0) {
		$rr = getgenera($genusid,$famid,$conn,$showinvalid=TRUE);
		$row = mysql_fetch_assoc($rr);
		$generoname = $row['Genero'];
		echo "
            <option selected value=".$row['GeneroID'].">".$row['Genero']."</option>
            <option value=''>---</option>";
	} 
	else {
		echo "
            <option value=''>".GetLangVar('nameselect')."</option>";
	}
		$rrr = getgenera('',$famid,$conn,$showinvalid=TRUE);
		while ($row = mysql_fetch_assoc($rrr)) {
			echo "
            <option value=".$row['GeneroID'].">".$row['Genero']."</option>";
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
  <td class='tdsmallbold' style='color:#990000' align='right'>".GetLangVar('namenome')." *</td>
  <td>
    <table>
      <tr>
        <td><input name='spnome' type='text' value='$spnome' size='30' class='selectedval' /></td>
        <td class='tdsmallbold' style='color:#990000' align='right'>".GetLangVar('nameautor')." *</td>";
	if ($morfoorpubsp==1) {
		$name = $generoname." ".$spnome;
		echo "
        <td><input type='text' value='".$autor."' name='autor' size='15' /></td>";
			if ($mycobankid>0) {
				echo "
      <td >
      <input type='hidden' name='mycobankid' value='".$mycobankid."'  />Mycobank No. ".$mycobankid."&nbsp;<img style='cursor:pointer;' src='icons/mycobank.png' height='18' onclick=\"javascript:alert('ainda não implementado');\" onmouseover=\"Tip('Ver registro do nome ".$name." em mycobank.org');\" /></td>";
			}
			if ($ipniid>0) {
				echo "
         <td><input type='hidden' name='ipniid' value='".$ipniid."'  />IPNI No. ".$ipniid."&nbsp;<img style='cursor:pointer;' src='icons/mobot.png' height='18' onclick=\"javascript:small_window('http://www.tropicos.org/NameSearch.aspx?name=".$name."',800,600,'Tropicos');\" onmouseover=\"Tip('Ver registro do nome ".$name." em tropicos.org');\" /></td>
      ";
      
	      }
	} 
	elseif ($morfoorpubsp==2) {
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
        <td>
          <img src='icons/list-add.png' height=15 ";
			$myurl ="novapessoa-form-popup.php?pessoaid_val=autorid";
			echo " onclick = \"javascript:small_window('$myurl',500,350,'Nova Pessoa');\">
        </td>";
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
        <td align='right'><input type='radio' name='nomevalido' $ch value='1' /></td><td>".mb_strtolower(GetLangVar('namevalido'))."</td>
        <td align='right'><input type='radio' name='nomevalido' $ch2 value='0' /></td><td>".mb_strtolower(GetLangVar('nameinvalido'))."</td>
      </tr>
    </table>
  </td>
</tr>";
if ($morfoorpubsp==1) {
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<td class='tdsmallboldright'>Referência&nbsp;&nbsp;<img height='15' src=\"icons/icon_question.gif\" ";
		$help = "Indique uma referência bibliográfica com a publicação do nome da espécie. Essa informação é usada na geração de monografias em formato FitoTaxa";
		echo " onclick=\"javascript:alert('$help');\" />
</td>
  <td >
    <table>
      <tr>
        <td ><span id='bibtex_txt'>".$bibtex_txt."</span><input type='hidden' id='bibtex_id' name='bibtex_id'  value='".$bibtex_id."'></td>
        <td><input type=button style=\"cursor:pointer;\"  value='Bibliografia'  onmouseover=\"Tip('Escolha a referência de publicação do nome');\" ";
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
        <td><input name='basionym' type='text' value='".$basionym."' size='30' /></td>
        <td class='tdsmallbold' align='right'>".GetLangVar('nameautor')."</td>
        <td><input name='basionymautor' type='text' value='".$basionymautor."' size='15' /></td>
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
        <td><textarea rows=2 cols=50 id='specieslist' name='specieslist' readonly>$specieslist</textarea></td>";
		}
		else {
			echo "
        <input type='hidden' id='specieslist' name='specieslist' value='$specieslist' />
        <td class='tdsmalldescription'>$specieslist </td>";
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
      <tr><td><textarea name='geodist' cols='50' rows='2' >$geodist</textarea></td></tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>".GetLangVar('namevernacular')."
  <input type='hidden' id='vernacularvalue' name='vernacularvalue' value='$vernacularvalue' /></td>
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
        </td>
        <td>
          <input type=button value=\"+\" class='bsubmit' ";
			$myurl ="vernacular_selector.php?formname=specieslistform&tempelement=vernaculartxt&elementname=vernacularvalue&getvernacularids=$vernacularvalue"; 
			echo " onclick = \"javascript:small_window('$myurl',350,280,'Add_from_Src_to_Dest');\" />
        </td>
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
      <tr>
        <td align='center'><input type = 'submit' class='bsubmit' value='".GetLangVar('nameenviar')."' /></td>
      </tr>
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
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>