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
//$ppost = cleangetpost($_POST,$conn);
//@extract($ppost);
//$arval = $ppost;
//$gget = cleangetpost($_GET,$conn);
//@extract($gget);

$ppost = unserialize($_SESSION['imgspost']);
@extract($ppost);

/*DEFINE PASTA TEMPORARIA ONDE SAO ARMAZENADOS OS ARQUIVOS DURANTE A IMPORTACAO (HA REFERENCIA A ESTA PASTA EM imagesupload-doit.php)*/
$tbn = "uploadDir_". $_SESSION['userid'];
$dir = "uploads/batch_images/".$tbn;

//SE HOUVE INDICACAO DE ESPECIMENES, CHECA SE O COLETOR E NUMERO SAO VALIDOS PARA TODOS OS ARQUIVOS
$checalink=0;
if ($erro==0 && !empty($fnpattern) && !empty($fnpattern_sep) && !isset($especimenesids) ) {
	$imgs_nomes = scandir($dir);
	unset($imgs_nomes[0]);
	unset($imgs_nomes[1]);
	$imgs_nomes = array_values($imgs_nomes);
	//PARA CADA IMAGEM EXTRAI O COLETOR E BUSCA NA BASE E PERMITE USUARIO SELECIONAR
	//IDENTIFICA TODOS OS COLETORES
	$coletoresSIM = array();
	$coletoresPRES = array();
	$identificadores = array();

	$checalink=0;
	foreach ($imgs_nomes as $imgnome) {
			//PEGA O NOME DO COLETOR COMO PRIMEIRO ELEMENTO DO NOME EXPLODIDO PELO SEPARADOR INDICADO PELO USUARIO. REMOVE ACENTOS PARA GARANTIR COMPARACAO
			$colarr = explode($fnpattern_sep,$imgnome);
			//echopre($colarr);
			$clo = $colarr[0];
			$clo = strtoupper($clo);
			$coletoresSIM[] = RemoveAcentos($clo);
			$coletoresPRES[] = strtoupper($clo);
			$coletor = RemoveAcentos($clo);
			//PEGA O NUMERO DE COLETA COMO SENDO O SEGUNDO ELEMENTO E FORÇA NUMERICO NELE
			$numarr = explode(".",$colarr[1]);
			$colnum = trim($numarr[0]);
			if (is_numeric($colnum)) {
				$colnum = $colnum+0;
			}
	//CHECA NA BASE SE HA UMA COLETA COM ESTE NUMERO OU NUMERO PARECIDO
		$nrs =0;
	//SE O PADRAO SAO AS INICIAIS DO SOBRENOME
	if ($fnpattern==1) {
		$qq = "SELECT EspecimenID,Abreviacao,Number,gettaxonname(DetID,1,0) as NOME FROM Especimenes JOIN Pessoas ON ColetorID=PessoaID WHERE UPPER(acentostosemacentos(Abreviacao)) LIKE '".$coletor."%' AND Number='".$colnum."'";
	}
	//SE O PADRAO SAO É A ABREVIACAO DO NOME
	if ($fnpattern==2) {
		$qq = "SELECT EspecimenID,Abreviacao,Number,gettaxonname(DetID,1,0) as NOME FROM Especimenes JOIN Pessoas ON ColetorID=PessoaID WHERE checkiniciais(Prenome,SegundoNome,SobreNome) LIKE '".$coletor."' AND Number='".$colnum."'";
	}
	//FAZ O QUERY NA BASE
	$rs = mysql_query($qq,$conn);
	$nrs = mysql_numrows($rs);
	//SE AINDA NAO ENCONTROU NADA, TESTA DE OUTRO JEITO
	if ($nrs==0) {
		//PEGA UMA PARTE DO NOME DO COLETOR APENAS
		$colector = substr($coletor,0,5);
		$qq = "SELECT EspecimenID,Abreviacao,Number,gettaxonname(DetID,1,0) as NOME FROM Especimenes JOIN Pessoas ON ColetorID=PessoaID WHERE UPPER(acentostosemacentos(Abreviacao)) LIKE '".$coletor."%'  AND Number='".$colnum."'";
		$rs = mysql_query($qq,$conn);
		$nrs = mysql_numrows($rs);
		} 
	
	//echo  $qq."<br >";
	//SE ENCONTROU APENAS 1 COLETOR PARA ESTA IMAGEM ANOTA
	if ($nrs==1) {
		//echo "Encontrou sim!!!";
		$rsw = mysql_fetch_assoc($rs);
		$identificadores[] = $rsw['EspecimenID'];
		$Abreviacao = $rsw['Abreviacao'];
		$Number = $rsw['Number'];
		$NOME = $rsw['NOME'];
	} 
	else {
		//echo "NAO Encontrou NAO!!!";
		$identificadores[] = 0;
		$Abreviacao =   strtoupper($clo);
		$Number =  $colnum;
		$NOME = '';
		$checalink++;
	}
	} // fecha o for de cada imagen

	//SE NAO ENCONTROU ALGUM VÍNCULO, AVISA
	if ($checalink>0) {
echo "
<br>
<form action='imagens-import-batch-exec2.php' method='post' >";
  foreach ($ppost as $pp => $vp) {
	if (!empty($vp)) {
		echo "
  <input type='hidden' name='".$pp."' value='".$vp."' />"; 
		}
	}
echo "
<table align='center' class='myformtable' cellpadding='5'>
<thead>
  <tr><td colspan='2'>Os seguintes nomes de coletores (e ou coleta) não foram encontrados:</td></tr>
  <tr class='subhead'>
    <td>Nome do arquivo</td>
    <td>Referencia da coleta</td>
  </tr>
</thead>
<tbody>";
//echopre($identificadores);
	foreach ($imgs_nomes as $kk => $imgnome) {
	$idd = $identificadores[$kk];
	if ($idd==0) {
		echo "
<tr >
    <td>".$imgnome."</td>
    <td>
      <table>
        <tr>
          <td>".$coletoresPRES[$kk]."</td>
          <td>
            <td class='tdsmallnotes'>
                <select name=\"especimenesids[".$kk."]\">
                <option value='0' style='font-size: 0.8em' >Não importar esta imagem!</option>";
				$rrr = "SELECT DISTINCT EspecimenID,Abreviacao,Number,gettaxonname(DetID,1,0) as NOME FROM Especimenes JOIN Pessoas ON ColetorID=PessoaID ORDER BY Abreviacao,Number";
				$rz = mysql_query($rrr,$conn);
				while ($row = mysql_fetch_assoc($rz)) {
					echo "
                    <option value='".$row['EspecimenID']."' style='font-size: 0.8em' >".$row['Abreviacao']." ".$row['Number']." [".$row['NOME']."]</option>";
				}
			echo "
                </select>
            </td>
        </tr>
    </table>
    </td>
    </tr>
    ";
} else {
echo "<input type='hidden' name=\"especimenesids[".$kk."]\" value=".$identificadores[$kk]."  >";
}
}
echo "
<tr><td colspan='2' align='center'><input type='submit' value='Attribuir correções' </td></tr>
</tbody>
</table>
</form>
";
} else {

		echo "
<br>
<form action='imagens-import-batch-exec2.php' method='post' name='autoform'>";
  foreach ($ppost as $pp => $vp) {
	if (!empty($vp)) {
		echo "
  <input type='hidden' name='".$pp."' value='".$vp."' />"; 
		}
	}
	foreach ($imgs_nomes as $kk => $imgnome) {
		$idd = $identificadores[$kk];
		echo "<input type='hidden' name=\"especimenesids[".$kk."]\" value=".$identificadores[$kk]."  >";
	}
echo "<script language=\"JavaScript\">setTimeout('document.autoform.submit()',0.0001);</script>";
echo "</form>";
	}
		


} 
else {
	//CHECA POR PLANTASIDS SE FOR O CASO
	if ($erro==0 && !empty($fnpattern_pl) && (!empty($filtro) || $fnpattern_pl==2) && !isset($plantasids)) {
		echo "estou entrando corretamente??";
		$imgs_nomes = scandir($dir);
		unset($imgs_nomes[0]);
		unset($imgs_nomes[1]);
		$imgs_nomes = array_values($imgs_nomes);
		$identificadores = array();
		foreach ($imgs_nomes as $kkimg => $imgnome) {
			if (!empty($fnpattern_seppl)) {
				$colarr = explode($fnpattern_seppl,$imgnome);
				$tag = $colarr[0]+0;
			} 
			else {
				$numarr = explode(".",$ff);
				$td = count($numarr)-1;
				unset($numarr[$td]);
				$tag = implode(".",$numarr);
				$tag = trim($numarr[0]);
			}
			//pega o plantaid
		    if ($fnpattern_pl==2) {
		    	$qq = "SELECT * FROM Plantas WHERE PlantaID='".$tag."'";
				$rs = mysql_query($qq,$conn);
				$nrs = mysql_numrows($rs);
				if ($nrs==1) { 
			        $identificadores[$kkimg] = $tag;
			    } else {
			    	//PLANTA NAO ENCONTRADA
					$identificadores[$kkimg] = 0;
					$checalink++;
			    }
		    } 
		    else {
		    	$qq = "SELECT * FROM Plantas WHERE PlantaTag='".$tag."' AND (FiltrosIDS LIKE '%filtroid_".$filtro."' OR FiltrosIDS LIKE '%filtroid_".$filtro.";%')";
				$rs = mysql_query($qq,$conn);
				$nrs = mysql_numrows($rs);
				if ($nrs==1) { 
					$rw = mysql_fetch_assoc($rs);
					$identificadores[$kkimg] = $rw['PlantaID'];
				} else {
					//PLANTA NAO ENCONTRADA
					$identificadores[$kkimg] = 0;
					$checalink++;
				}
		    }
		}
		
		//echopre($identificadores);
		if ($checalink>0) {
		echo "
<br>
<form action='imagens-import-batch-exec2.php' method='post' >";
  foreach ($ppost as $pp => $vp) {
	if (!empty($vp)) {
		echo "
  <input type='hidden' name='".$pp."' value='".$vp."' />"; 
		}
	}
echo "
<table align='center' class='myformtable' cellpadding='5'>
<thead>
  <tr><td colspan='2'>As seguintes plantas marcadas não foram encontradas:</td></tr>
  <tr class='subhead'>
    <td>Nome do arquivo</td>
    <td>Plantas marcadas</td>
  </tr>
</thead>
<tbody>";
			$plantasids = array();
			foreach ($imgs_nomes as $kk => $imgnome) {
				$idd = $identificadores[$kk];
				if ($idd==0) {
				echo "
<tr >
    <td>".$imgnome."</td>
    <td>
      <select name=\"plantasids[".$kk."]\">
            <option value='0' style='font-size: 0.8em' >Não importar esta imagem!</option>";
				$rrr = "SELECT DISTINCT PlantaID,PlantaTag, localidadefields(GazetteerID,GPSPointID, 'GAZETTEER')  as GAZETTEER FROM Plantas ORDER BY PlantaTag";
				$rz = mysql_query($rrr,$conn);
				while ($row = mysql_fetch_assoc($rz)) {
					echo "
                    <option value='".$row['PlantaID']."' style='font-size: 0.8em' >".$row['PlantaTag']." [".$row['GAZETTEER']."]</option>";
				}
			echo "
                </select>
            </td>
        </tr>
    </table>
    </td>
    </tr>
    ";
				} else {
				echo "<input type='hidden' name=\"plantasids[".$kk."]\" value=".$identificadores[$kk]."  >";
				}
			}
echo "
<tr><td colspan='2' align='center'><input type='submit' value='Attribuir correções' </td></tr>
</tbody>
</table>
</form>
";
			
			
			
		} 
		else {
		echo "
<br>
<form action='imagens-import-batch-exec2.php' method='post' name='autoform'>";
  foreach ($ppost as $pp => $vp) {
	if (!empty($vp)) {
		echo "
  <input type='hidden' name='".$pp."' value='".$vp."' />"; 
		}
	}
	foreach ($imgs_nomes as $kk => $imgnome) {
		$idd = $identificadores[$kk];
		echo "<input type='hidden' name=\"plantasids[".$kk."]\" value=".$identificadores[$kk]."  >";
	}
echo "<script language=\"JavaScript\">setTimeout('document.autoform.submit()',0.0001);</script>";
echo "</form>";
		
		}
	} else {
		if ($linkposterior==1) {
			echo "
<br>
<form action='imagens-import-batch-exec2.php' method='post' name='lknform'>";
  foreach ($ppost as $pp => $vp) {
	if (!empty($vp)) {
		echo "
  <input type='hidden' name='".$pp."' value='".$vp."' />"; 
		}
	}
echo "<script language=\"JavaScript\">setTimeout('document.lknform.submit()',0.0001);</script>";
echo "</form>";
	} else {
			echo "
<br>
<form action='imagens-import-batch.php' method='post' name='finform'>";
echo "<script language=\"JavaScript\">setTimeout('document.finform.submit()',0.0001);</script>";
echo "</form>";
	
	}
	}
}



$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>