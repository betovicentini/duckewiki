<?php
//Start session
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
//include "bibtex2html.php";
require_once "functions/BibTex.php";


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
$ispopup=1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Edita registro BibTex';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (!isset($enviado)) {
	$qch = "SELECT * FROM BiblioRefs WHERE BibID=".$bibid;
	$res = mysql_query($qch, $conn);
	$row = mysql_fetch_assoc($res);
	$newrec = htmlspecialchars($row['BibRecord']);

echo "
<br />
<form  action='bibtex_edit.php' method='post'>
<input type='submit' value='Salvar'  />&nbsp;&nbsp;&nbsp;<input type='button' value='Fechar' onclick='javascript: window.close();'  />
<br />
<input type='hidden' name='enviado' value='1'>
<input type='hidden' name='bibid' value='$bibid'>
<textarea name='bibrec' rows='20' cols='60' style='background-color: #FFFFCC;' >
".$newrec."
</textarea>
<br />
<input type='submit' value='Salvar'  />&nbsp;&nbsp;&nbsp;<input type='button' value='Fechar' onclick='javascript: window.close();'  />
</form>
";
} 
else {
	$fnn = 'temp_'.$bibid.'_'.$uuid.'bib';
	$fh = fopen("temp/".$fnn, 'w');
	$stringData = $ppost['bibrec'];
	//echo $stringData;
	$stringData = trim(preg_replace('[\\\r\\\n|\\\r|\\\n|\\\t]', '', $stringData));
	//$stringData = htmlspecialchars($stringData);
	//echo "<br /><br /><br />";
	//echo $stringData;
	fwrite($fh, $stringData);
	fclose($fh);
	
	$bibtex = new Structures_BibTex();
	$ret    = $bibtex->loadFile("temp/".$fnn);
	$bibtex->parse();

	$qchh = "SELECT * FROM BiblioRefs WHERE  BibID=".$bibid;
	$rches = mysql_query($qchh, $conn);
	$rchwo = mysql_fetch_assoc($rches);
	$fnnol = 'temp_'.$bibid.'old_'.$uuid.'bib';
	$fh = fopen("temp/".$fnnol, 'w');
	$strold = $rchwo['BibRecord'];
	$strold = trim(preg_replace('[\\\r\\\n|\\\r|\\\n|\\\t]', '', $strold));
	fwrite($fh, $strold);
	fclose($fh);
	
	$bibtex2 = new Structures_BibTex();
	$ret    = $bibtex2->loadFile("temp/".$fnnol);
	$bibtex2->parse();

	$ar1 = $bibtex->data[0];
	$ar2 =  $bibtex2->data[0];
	$upd =0;
	//echopre($ar1);
	//echopre($ar2);
	foreach ($ar1 as $kk => $vr) {
		$vr1 = $ar2[$kk];
		if ($vr1!=$vr) {
			$upd++;
		}
	}
	//echo "here ".$upd;
	if ($upd>0) {
		$inserted=0;
		$erros =0;
		$updated =0 ;
		foreach ($bibtex->data  as $vv) {
			//echopre($vv);
			$erro=0;
			$errotxt = '';
			$nrecs =0;
			$nbk =0;
			$inserido=0;
			$txtbiborg = $bibtex->bibTexEntry($vv);

			//PEGA VALORES
			$yy = ($vv['year']+0);
			$pg = @trim($vv['pages']);
			$titu =  @trim($vv['title']);
			$titu = str_replace("{","",$titu);
			$titu = str_replace("}","",$titu);

			$jrn =  @trim($vv['journal']);
			$bk =  @trim($vv['booktitle']);
			$vl =  @trim($vv['volume']);
			$aut = $vv['author'];
			$lastn = '';
			$autores = '';
			if (is_array($aut)) {
				$aa = $aut[0];
				$lastn = $aa['last'];
				//SANITIZE NAME (REMOVE ACENTOS, PONTOS, ESPACOS, SIMBOLOS, ETC)
				$lastn = RemoveAcentos($lastn);
				$lastn = mb_strtolower($lastn);
				$lastn = str_replace("\\", "", $lastn);
				$lastn = str_replace('#', '', $lastn);
				$lastn = str_replace('$', '', $lastn);
				$lastn = str_replace('%', '', $lastn);
				$lastn = str_replace('^', '', $lastn);
				$lastn = str_replace('&', '', $lastn);
				$lastn = str_replace('"', '', $lastn);
				$lastn = str_replace("'", '', $lastn);
				$lastn = str_replace('_', '', $lastn);
				$lastn = str_replace("  "," ",$lastn);
				$lastn = str_replace("  "," ",$lastn);
				$lastn = str_replace("  "," ",$lastn);
				$lastn = str_replace(" ","",$lastn);
				$lastn = str_replace("{","",$lastn);
				$lastn = str_replace("}","",$lastn);
				$autores = strtoupper($lastn);
				foreach ($aut as $va) {
					$curaut = $va['last'];
					$curaut = RemoveAcentos($curaut);
					$curaut = str_replace("\\", "", $curaut);
					$curaut = str_replace('#', '', $curaut);
					$curaut = str_replace('$', '', $curaut);
					$curaut = str_replace('%', '', $curaut);
					$curaut = str_replace('^', '', $curaut);
					$curaut = str_replace('&', '', $curaut);
					$curaut = str_replace('"', '', $curaut);
					$curaut = str_replace("'", '', $curaut);
					$curaut = str_replace('_', '', $curaut);
					$curaut = str_replace("  "," ",$curaut);
					$curaut = str_replace("  "," ",$curaut);
					$curaut = str_replace("  "," ",$curaut);
					$curaut = str_replace(" ","",$curaut);
					$curaut = str_replace("{","",$curaut);
					$curaut = str_replace("}","",$curaut);
					$curaut = mb_strtolower($curaut);
					if ($curaut!==$lastn) {
						$autores .= '  & '.$curaut;
					}
				}
			} 
			if (count($aut)>1) {
				$bibkey = $lastn.'ETAL'.$yy;
			} else {
				$bibkey = $lastn.$yy;
			}
			//checa por campos obrigatorios
			if ($yy==0 || empty($titu) || (empty($jrn) && !empty($journal)) || empty($lastn)) {
				//nao importar
				$erro++;
				$errotxt .= ' Falta campos obrigatórios<br >';
			} 
			//se importar
			if ($erro==0) {
				//CHECA SE O BIBKEY JA EXISTE
				$qchh = "SELECT * FROM BiblioRefs WHERE LOWER(BibKey)='".mb_strtolower($bibkey)."' AND BibID<>".$bibid;
				$rch = mysql_query($qchh, $conn);
				$nbk = mysql_numrows($rch);
				//SE JA EXISTE, ADICIONA UMA LETRA SEQUENCIAL AO FINAL DO KEY
				if ($nbk>0) {
					$letters = range('a', 'z');
					$letra = $letters[($nbk-1)];
					$bibkey = $bibkey.$letra;
				}
				
				//CRIA UM STRING COM O BIBTEX
				$vv['cite'] = $bibkey;
				$txtbib = $bibtex->bibTexEntry($vv);
				$txtbib = mysql_real_escape_string($txtbib,$conn);
				//CRIA O ARRAY PARA ENTRADA/UPDATE DE DADOS NA TABELA BiblioRefs
				$arrvv = array(
					'BibKey' => $bibkey,
					'Year' => ($vv['year']+0),
					'Type' => mb_strtolower($vv['entryType']),
					'FirstAuthor' => $lastn,
					'Authors' => $autores,
					'Title' => $titu,
					'Journal'  => $jrn,
					'BookTitle'  => $bk,
					'Pages'  => $pg,
					'Volume'  => $vl,
					'BibRecord' => $txtbib,
					'OrgFileName' => $newfilename
				);
				$arrayofvalues = array();
				foreach ($arrvv as $kkk => $vk) {
					if (!empty($vk)) {
						$arrayofvalues[$kkk] = $vk;
					}
				}
					CreateorUpdateTableofChanges($bibid,'BibID','BiblioRefs',$conn);
					$updatebib = UpdateTable($bibid,$arrayofvalues,'BibID','BiblioRefs',$conn);
					if (!$updatebib) {
						$erro++;
						$errotxt .= 'Falhou atualização da tabela';
					} else {
						$updated++;
					}
			}
			
			if ($erro>0) {
				$erros++;
				echo "<br />////////////////////////////////////// REGISTRO".$vv['cite']."<br />";
				echo "<font style='color: red'>ERRO: ".$errotxt."</font><br />Registro não atualizado";
			}
		}
	} 
	else {
		echo "<br >Você não mudou nada!<br /><input type='button' value='Fechar' onclick='javascript: window.close();'  />";
	}
	if ($erros==0 & $updated>0) {
		echo "<br >Atualização Concluida<br /><input type='button' value='Fechar' onclick='javascript: window.close();'  />";
	}
}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>
