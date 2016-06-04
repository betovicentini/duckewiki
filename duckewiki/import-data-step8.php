<?php
//Este script checa se alguns campos que se referem a localidades e ve se estas ja estao cadastradas
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

//echopre($_POST);
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
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' >"
);
$which_java = array("<script type='text/javascript' src='javascript/ajax_framework.js'></script>");
$title = 'Importar Dados Passo 08';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$fields = unserialize($_SESSION['fieldsign']);

$clnl = $tbprefix."CountryID";
$clnl2 = $tbprefix."ProvinceID";
$clnl3 = $tbprefix."MunicipioID";
$colname = $tbprefix."GazetteerID";

if (in_array('GAZETTEER',$fields) && !isset($localidadesdone)) {
		$colname = $tbprefix."GazetteerID";
		$colcol = array_search('GAZETTEER',$fields);
		if (count($colcol)>1) {
			$erro = '<br />Tem mais de uma coluna que define localidade??';
		} else {
			if (!isset($gazdone)) {
				$qq = "ALTER TABLE `".$tbname."` ADD COLUMN `".$colname."` INT(10) DEFAULT 0";
				@mysql_query($qq,$conn);
			} else {
				if (count($gazetteerid)>0) {
					foreach ($gazetteerid as $kk => $vv) {
						$vv = $vv+0;
						$kka = explode("_",$kk);
						$nt = count($kka)-1;
						unset($kka[$nt]);
						$kk = implode("_",$kka);
						if ($vv>0) {
							if (in_array('MINORAREA',$fields)) {
								$qq = "UPDATE ".$tbname." as tb, Gazetteer as gaz SET tb.".$colname."= ".$vv." WHERE tb.".$colcol."='".$kk."'  AND gaz.MunicipioID=tb.".$clnl3." AND tb.".$colcol."<>'' AND tb.".$colcol." IS NOT NULL AND (tb.".$colname."=0  OR tb.".$colname." IS NULL)";
							} else {
								$qq = "UPDATE ".$tbname." as tb SET tb.".$colname."= ".$vv." WHERE tb.".$colcol."='".$kk."'  AND tb.".$colcol."<>'' AND tb.".$colcol." IS NOT NULL AND (tb.".$colname."=0  OR tb.".$colname." IS NULL)";
							}
							//echo $qq."<br />";
							mysql_query($qq,$conn);
						}
					}
				} 
			}
			if (empty($localid)) {
				$parid = 0;
				if (in_array('MINORAREA',$fields)) {
					$muid = $tbprefix."MunicipioID";
					$prid = 0;
					$crtid = 0;
				} else {
					$muid = 0;
					if (in_array('MAJORAREA',$fields)) {
						$prid = $tbprefix."ProvinceID";
						$crtid = 0;
					} else {
						$prid = 0;
						if (in_array('COUNTRY',$fields)) {
							$crtid = $tbprefix."CountryID";
						} else {
							$crtid =0;
						}
					}
				}
			} elseif ($localid>0) {
				$parid = $localid;
				$muid=0;
				$prid=0;
				$crtid=0;
			}
			$qq = "UPDATE ".$tbname." SET ".$colname."=checkgazetteer(".$colcol.",".$parid.",".$muid.",".$prid.",".$crtid.") WHERE ".$colcol."<>'' AND ".$colcol." IS NOT NULL AND (".$colname."=0 OR ".$colname." IS NULL)";
			mysql_query($qq,$conn);
			$qq = "SELECT DISTINCT `".$colcol."` as missgen";
			if (in_array('MINORAREA',$fields)) {
				$qq .= ", ".$clnl3;
			}
			if (in_array('MAJORAREA',$fields)) {
				$qq .= ", ".$clnl2;
			}
			if (in_array('COUNTRY',$fields)) {
				$qq .= ", ".$clnl;
			}			
			$qq .= " FROM `".$tbname."`  WHERE `".$colcol."`<>'' AND `".$colcol."` IS NOT NULL AND (`".$colname."`=0 OR `".$colname."` IS NULL)";
			$rr = mysql_query($qq,$conn);
			$nr = mysql_numrows($rr);
			unset($ppost['gazetteerid']);
			if ($nr>0) {
			echo "
<br />
    <table align='center' class='myformtable' cellpadding='7'>
  <thead>
    <tr><td colspan='3'>Localidades não encontrados no Wiki</td>
    </tr>
    <tr class='subhead'>
    <td>Nome</td>
    <td>Pode ser um desses</td>
    <td>Cadastre novo</td>
    </tr>
  </thead>
  <tbody>
    <form action='import-data-step8.php' method='post'>";
					unset($_POST['gazdone']);
					foreach ($_POST as $kk => $vv) {
						if (!empty($vv)) {
							echo "
        <input type='hidden' name='".$kk."' value='".$vv."' />"; 
						}
					}
					echo "
        <input type='hidden' name='gazdone' value='1' />";
					$i=1;
					while ($rw = mysql_fetch_assoc($rr)) {
						$gen = strtolower(trim($rw['missgen']));
						$genorg = trim($rw['missgen']);
						$gk = $rw['missgen'];
						$gggen = explode(" ",$gen);
						$ggen = implode("-",$gggen);
						$coutid = @trim($rw[$clnl3]);
						$provid = @trim($rw[$clnl2]);
						$paisid = @trim($rw[$clnl]);
						$qgaz ='SELECT Municipio, Province, Country FROM Municipio JOIN Province USING(ProvinceID) JOIN Country USING(CountryID) ';
						if ($coutid>0) {
							$qgazw = ' WHERE MunicipioID='.$coutid;
						} else {
							if ($provid>0) {
								$qgazw = ' WHERE Province.ProvinceID='.$provid.' LIMIT 0,1';
							} else {
								if ($paisid>0) {
									$qgazw = ' WHERE Country.CountryID='.$paisid.' LIMIT 0,1';
								}
							}
						}
						//echo $qgaz.$qgazw."<br /><br />";
						$rgaz = mysql_query($qgaz.$qgazw,$conn);
						$rgazw = mysql_fetch_assoc($rgaz);
						$gazlab = trim($rgazw['Country'].' '.$rgazw['Province'].' '.$rgazw['Municipio']);
						$lab = $gk;
						$gkk2 = $gk."_".$i;
						
						if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
						echo "
        <tr>
          <td>".$lab." [ ".$gazlab." ]</td>
          <td>";        autosuggestfieldval3('search-gazetteer.php','locality['.$gk.'_'.$i.']',$locality[$gk.'_'.$i],'localres'.$i,'gazetteerid['.$gkk2.']','',true,60);
	echo "
			</td>
            <td align='center'><img style='cursor:pointer;' src='icons/list-add.png' height=16";
							$myurl = "localidade_dataexec.php?paisid=".$paisid."&municipioid=".$coutid."&provinciaid=".$provid."&gazetteer=".$genorg."&closewin=1&gazetteer_val=gazetteerid[".$gkk2."]&gazetteer_html=localres".$i;
							echo " onclick = \"javascript:small_window('$myurl',500,350,'Novo Pais');\"></td></tr>";
						$i++;
				}
					echo "
            <tr><td align='center' colspan='3'><input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td></tr>
        </form>
    </tbody>
</table>
			";
			
		} 
		else {
			if (in_array('COUNTRY',$fields)) {
				$cll = $tbprefix."CountryID";
				$colname = $tbprefix."GazetteerID";
				$qq = "UPDATE `".$tbname."` SET `".$cll."`=0 WHERE `".$colname."`>0";
			}
			if (in_array('MAJORAREA',$fields)) {
				$cll = $tbprefix."ProvinceID";
				$colname = $tbprefix."GazetteerID";
				$qq = "UPDATE `".$tbname."` SET `".$cll."`=0 WHERE `".$colname."`>0";
			}
			if (in_array('MINORAREA',$fields)) {
				$cll = $tbprefix."MunicipioID";
				$colname = $tbprefix."GazetteerID";
				$qq = "UPDATE `".$tbname."` SET `".$cll."`=0 WHERE `".$colname."`>0";
			}
			$localidadesdone = 1;
		}
	}
} elseif (!in_array('GAZETTEER',$fields)) { 
	$localidadesdone=1;
}

if ($localidadesdone==1) {
	//cria variável de sessao com as definicoes dos campos feitas pelo usuario
	$steps = unserialize($_SESSION['importacaostep']);
	unset($steps[0]);
	$stt = array_values($steps);
	$_SESSION['importacaostep'] = serialize($stt);
echo "
  <form name='myform' action='import-data-hub.php' method='post'>
    <input type='hidden' name='ispopup' value='".$ispopup."' />
    <script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
  </form>";
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>