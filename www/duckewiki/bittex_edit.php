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
echo "
<br />
<form  action='bibtex_edit.php' method='post'>
<input type='hidden' name='MAX_FILE_SIZE' value='100000000000' />
<input type='hidden' name='imported' value='1'>
<input type='hidden' name='ispopup' value='".$ispopup."'>
<table align='left' class='myformtable' cellpadding=\"7\" width='90%' />
<thead>
<tr>
<td colspan='2' class='tabhead' >".GetLangVar('nameimportar')." ".GetLangVar('namefile')." Bibliografia BibTex</td>
</tr>
</thead>
<tbody>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td style='color: #990000; font-weight:bold' >".GetLangVar('namefile')."*&nbsp;&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
		$help = "O arquivo para importar deve conter registros BibTex, ou seja, ser um arquivo em formato *.bib, ter quebra de linha em formato UNIX, e código de fonte UTF-8 (no (Ru)Windows use Notepad++, no Mac TextWrangler para essas opções";
		echo " onclick=\"javascript:alert('".$help."');\" /></td>
  <td>
    <input name='uploadfile' type='file' width='20' />
  </td>
</tr>
";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
//O bibtexkey é a índice único da referência bibliográfica. Se já houver na base irá atualizar se for a mesma revista, ano e páginas. Caso contrário, irá adicionar a referência e mudar o bibtexkey adicionando uma letra ao final. Certifique-se que todas as referências tem um bibtexkey!
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdformnotes' colspan='2'>".$help."</td>
</tr>
";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='2' align='center'>
    <input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' />
  </td>
</tr>
</tbody>
</table>
</form>
";
} 
else {
		$fname = $_FILES['uploadfile']['name'];
		$fileuploaded = $_FILES['uploadfile']['tmp_name'];
		
		//echopre($_FILES);
		//salva o arquivo importando no permanentemente no servidor
		$ext = explode(".",$fname);
		$ll = count($ext)-1;
		$extens = $ext[$ll];
		unset($ext[$ll]);
		$fn = implode(".",$ext);
		$importdate = date("Y-m-d");
		$newfilename = $fn."_bibtex_".$_SESSION['userid']."_".$importdate.".".$extens;
		move_uploaded_file($_FILES["uploadfile"]["tmp_name"],"uploads/bibtex/".$newfilename);


		//CREATE TABLE IF NOT EXISTS TO SAVE KEY, TITLE, YEAR, PAGES AND FIRST AUTHOR
$qc = "CREATE TABLE IF NOT EXISTS `BiblioRefs` (  `BibID` int(10) unsigned NOT NULL AUTO_INCREMENT,  `BibKey` char(100) NOT NULL,  `Type` char(100) DEFAULT NULL,  `Year` int(4) DEFAULT NULL,  `FirstAuthor` char(100) DEFAULT NULL, `Authors` varchar(5000) DEFAULT NULL, `Journal` char(100) DEFAULT NULL,  `Title` char(255) DEFAULT NULL,  `BookTitle` CHAR(255) DEFAULT NULL,  `Pages` char(100) DEFAULT NULL,  `Volume` char(100) DEFAULT NULL,  `BibRecord` text, `OrgFileName` CHAR(100), `AddedBy` int(10) unsigned DEFAULT NULL,  `AddedDate` date DEFAULT NULL,  PRIMARY KEY (`BibID`),  UNIQUE KEY `BibKey` (`BibKey`),  KEY `Title` (`Title`,`Year`,`FirstAuthor`,`Type`,`Journal`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
mysql_query($qc, $conn);


		$fileuploaded = "uploads/bibtex/".$newfilename;
        //chmod ($fileuploaded, 0755);
		//$bibtex_db = init_bibtexbrowser($fileuploaded);
		//$field_list = get_field_list($bibtex_db);
		//init_db($field_list,  $conn);
		//feed_database($bibtex_db,  $conn);
		$bibtex = new Structures_BibTex();
		$ret    = $bibtex->loadFile($fileuploaded);
		$bibtex->parse();
		$inserted=0;
		$erros =0;
		foreach ($bibtex->data  as $vv) {
			//@echopre($vv);
			$erro=0;
			$errotxt = '';
			$nrecs =0;
			$nbk =0;
			$inserido=0;
			$txtbiborg = $bibtex->bibTexEntry($vv);

			//PEGA VALORES
			//$key = trim($vv['cite']);
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
					$curaut = RemoveAcentos($va);
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
					if ($curaut!=$lastn) {
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
				//CHECA SE O RESGISTRO IMPORTADO JÁ EXISTE
				$qch = "SELECT * FROM BiblioRefs WHERE LOWER(Type)='".mb_strtolower($vv['entryType'])."' AND year=".$yy." AND 
				FirstAuthor='".$lastn."'  ";
				if (!empty($pg)) {
				 	$qch .= " AND LOWER(Pages)='".mb_strtolower($pg)."'";
				 }
				if (!empty($vl)) {
				 	$qch .= " AND (Volume)='".$vl."'";
				 } else {
				 	if (!empty($bk)) {
					 	$qch .= " AND LOWER(BookTitle)='".mb_strtolower($bk)."'";
				 	} elseif (!empty($titu)) {
				 		$qch .= " AND LOWER(title)='".mb_strtolower($titu)."' ";
				 	}
				 }
				$rch1 = mysql_query($qch, $conn);
				$nrecs = mysql_numrows($rch1);
				if ($nrecs>0) {
					$erro++;
					$errotxt .= ' Já existe um registro desse tipo, ano, autor, titulo e paginas!<br >';
				}
				else {
				//CHECA SE O BIBKEY JA EXISTE
				$qchh = "SELECT * FROM BiblioRefs WHERE LOWER(BibKey)='".mb_strtolower($bibkey)."'";
				$rch = mysql_query($qchh, $conn);
				$nbk = mysql_numrows($rch);
				//SE JA EXISTE, ADICIONA UMA LETRA SEQUENCIAL AO FINAL DO KEY
				if ($nbk>0) {
					$letters = range('a', 'z');
					$letra = $letters[($nbk-1)];
					$bibkey = $bibkey.$letra;
				}
				//APAGA O ARGUMENTO FILE SE HOUVER
				if (!empty($vv['file'])) {
					$vv['file'] = '';
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
				//INSERE O REGISTRO
				$newbib = InsertIntoTable($arrayofvalues,'BibID','BiblioRefs',$conn);
				if ($newbib) {
					$inserido++;
				} 
				else {
					$erro++;
					$errotxt .= ' Houve um erro na insercao do registro na tabela BiblioRefs!<br >';
				}
			}
			}
			
			if ($erro==0) {
				$inserted++;
				echo "<br />////////////////////////////////////// REGISTRO".$vv['cite']."<br />";
				echo $qch."<br />";
				//echo "Bibkey inserida: ".$bibkey."  <br /><textarea>".$txtbib."</textarea><br />";
			} else {
				$erros++;
				echo "<br />////////////////////////////////////// REGISTRO".$vv['cite']."<br />";
				echo "<font style='color: red'>ERRO: ".$errotxt."</font><br />Registro não importado:<br /><textarea>".$txtbiborg."</textarea><br />";
				//echopre($arrayofvalues);
				if ($nrecs) {
					echo "<b >Aparentemente este registro já estava inserido e pode ser 1 desses??</b><br />";
					$ii=1;
					while($row = mysql_fetch_assoc($rch1)) {
						echo $ii."   bibkey:".$vv['cite']."<br /><textarea>".$row['BibRecord']."</textarea><br />";
					}
				}
			}
		//echopre($bibtex->data);
	}
	//SE HOUVE INSERCOES
	if ($inserted>0) {
				echo "
<br />
<table cellpadding=\"7\"  align='center' class='success'>
<tr><td class='tdsmallbold' align='center'>".$inserted." registros BibTex foram importados com sucesso!</td></tr>";
if ($erro>0) {
				echo "
<tr><td class='tdsmallbold' align='center'>".$erros." registros tiveram erros e não foram importados!</td></tr>";
}
echo "
<tr><td class='tdsmallbold' align='center'>
<input style='cursor: pointer' type='button' value='Fechar a janela' onclick=\"javascript:window.close();\" />
</td></tr>
</table>
<br />";
	} else {
				echo "
<br />
<table cellpadding=\"7\" align='center' class='erro'>
<tr><td class='tdsmallbold' align='center'>".$erros." registros BibTex deram erro! Nada foi importado</td></tr>
<tr><td class='tdsmallbold' align='center'>
<input style='cursor: pointer' type='button' value='Fechar a janela' onclick=\"javascript:window.close();\" />
</td></tr>
</table>
<br />";
	
	
	}

}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>
