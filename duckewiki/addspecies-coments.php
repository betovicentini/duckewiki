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

//CABECALHO
$ispopup=1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$title = '';
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' >",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' >"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$body='';
$title = GetLangVar('nameselect')." ".GetLangVar('nameespecimenes');
FazHeader($title,$body,$which_css,$which_java,$menu);

if (!isset($final)) {
	$monospecids = $_SESSION['monospecids'];
	$comentarios = unserialize($_SESSION['comentarios']);
	echopre($comentarios);

if (!empty($monospecids)) {
		$uid = $_SESSION['userid'];
		
		$specarr = explode(";",$monospecids);
		//echopre($specarr );
		$qq = "SELECT Herbaria,AddColIDS,Abreviacao,Number,Day,Mes,Ano,GPSPointID,GazetteerID,EspecimenID,idd.DetID,Familia,Genero,Especie,InfraEspecie FROM Especimenes as spec JOIN Identidade as idd USING(DetID) LEFT JOIN Tax_InfraEspecies as taxisp ON taxisp.InfraEspecieID=idd.InfraEspecieID LEFT JOIN Tax_Especies as taxsp ON taxsp.EspecieID=idd.EspecieID JOIN Tax_Generos as taxgen ON taxgen.GeneroID=idd.GeneroID JOIN Tax_Familias as taxfam ON idd.FamiliaID=taxfam.FamiliaID JOIN Pessoas ON ColetorID=PessoaID WHERE ";
		$nn = count($specarr)-1;
		$ii=0;
		foreach ($specarr as $kk => $vv) {
			if ($ii==$nn) {
				$qq = $qq." EspecimenID='".$vv."'";
			} else {
				$qq = $qq." EspecimenID='".$vv."' OR ";
			}
			$ii++;
		}

		$qq = $qq." ORDER BY Familia,Genero,Especie,InfraEspecie";
		$res = mysql_query($qq,$conn);	
		
		$qu = "DROP TABLE Temp_Coments_".$uid;
		mysql_query($qu,$conn);	
		$qu = "CREATE TABLE Temp_Coments_".$uid." ".$qq;
		//echo $qu;
		mysql_query($qu,$conn);
		$qu = " ALTER TABLE Temp_Coments_".$uid." CHANGE EspecimenID EspecimenID INT( 10 ) NOT NULL ";
		mysql_query($qu,$conn);
		$qu = "ALTER TABLE Temp_Coments_".$uid." DROP PRIMARY KEY";
		mysql_query($qu,$conn);

		$qu = "ALTER TABLE Temp_Coments_".$uid." ADD TempID INT(10) unsigned NOT NULL auto_increment PRIMARY KEY";
		mysql_query($qu,$conn);

		$qu = "ALTER TABLE Temp_Coments_".$uid."  ADD NOME VARCHAR(200), ADD NAMEINDEX VARCHAR(200)";
		mysql_query($qu,$conn);

		//echo $qu."<br />";

		$qu = "SELECT * FROM Temp_Coments_".$uid."";
		$res = mysql_query($qu,$conn);

		$nno = '';
		while ($row = mysql_fetch_assoc($res)) {
			$tid = $row['TempID'];
			$newsp = array($row['EspecimenID']);
			$detid = $row['DetID']+0;
			
			$detno = getdetnoautor($detid,$conn);
			$simplename = explode(" ",$detno);
			$simplename = implode("_",$simplename);
			
			$arrofvals = array('NOME' => $detno, 'NAMEINDEX' => $simplename);
			$qu = "UPDATE Temp_Coments_".$uid." SET ";
			$jj=0;
			$nj = count($arrofvals)-1;
			foreach ($arrofvals as $kkk => $vvv) {
				if ($jj==$nj) {
					$qu = $qu." ".$kkk."='".$vvv."'";
				} else {
					$qu = $qu." ".$kkk."='".$vvv."', ";
				}
				$jj++;
			}
			$qu = $qu." WHERE TempID='".$tid."'";
			mysql_query($qu,$conn);
		}
		
		
		$qu = "SELECT DISTINCT NOME,NAMEINDEX FROM Temp_Coments_".$uid." ORDER BY Familia,Genero,Especie,InfraEspecie";
		$rr = mysql_query($qu,$conn);
		$nsp =1;
		$nno = '';
			echo "
<br />
<form method='post' name='comentform' action='addspecies-coments.php' >
<table class='myformtable' align='center' cellpadding=\"5\">
<thead>
<tr >
  <td >".GetLangVar('namenome')."</td><td>".GetLangVar('namecomentario')."</td>
</tr>
</thead>
<tbody>
";
		while ($rw = mysql_fetch_assoc($rr)) {
			$nome = $rw['NOME'];
			$nameindex = $rw['NAMEINDEX'];
			
			if (isset($comentarios)) { 
				$comm = $comentarios[$nameindex];
				} else {
				$comm='';
			}
			echo "
  <tr>
    <td class='tdsmallboldright'>".$nome."</td>
    <td class='tdsmallbold'><textarea cols=60 rows=5 name='".$nameindex."']'>".$comm."</textarea></td>
  </tr>";
		}
		echo "
  <tr>
    <td align='center' colspan='100%' >
      <input type='hidden' name='final' value='' />
      <input type='hidden' name='tagtoputid' value='$tagtoputid' />
      <input type='hidden' name='tagtoputtxt' value='$tagtoputtxt' />
      <input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' onclick=\"javascript:document.comentform.final.value=1\" />
    </td>
  </tr>
</tbody>
</table>
</form>
";
	} 
} elseif ($final==1) {
	$arofvals = $_POST;
	unset($arofvals['final']);
	unset($arofvals['tagtoputtxt']);
	unset($arofvals['tagtoputid']);
	$arvals = serialize($arofvals);
	$_SESSION['comentarios'] = $arvals;
	
	$nsp = count($arofvals);
	$comm=0;
	foreach ($arofvals as $vv) {
		if (!empty($vv)) {
			$comm++;
		}
	}
	$nometxt = "Comentários adicionados para $comm dos $nsp taxa";
	echo "
<form name='myform' >
  <input type='hidden' id='sendid' value='".$nometxt."' />
  <script language=\"JavaScript\">
    setTimeout(
      function() {
        sendval_innerHTML('sendid','".$tagtoputtxt."');
        sendvalclosewin('".$tagtoputid."','".$arvals."');
      }
      ,0.1);
  </script>
</form>";
}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>