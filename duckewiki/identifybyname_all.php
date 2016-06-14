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
$ispopup==1;
$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' >"
);
$which_java = array("<script type='text/javascript' src='javascript/ajax_framework.js'></script>");
$title = 'Substituir um nome científico';
$body = '';
$menu=FALSE;
FazHeader($title,$body,$which_css,$which_java,$menu);

if (!empty($_POST['detset'])) {
	$detset = $_POST['detset'];
	unset($_POST['detset']);
}
if (!empty($_GET['detset'])) {
	$detset = $_GET['detset'];
	unset($_GET['detset']);
}

if (!isset($detset) && isset($final)) {
	unset($final);
	echo "Precisa dar uma nova identificação, não acha?<br />";
}


if (!isset($final)) {
echo "
<br />
<form method='post' name='finalform' action='identifybyname_all.php'>
<input type='hidden' name='nomesearch' value='".$nomesearch."' />
<input type='hidden' name='speciesid' value='".$speciesid."' />
<input type='hidden' name='infspecid' value='".$infspecid."' />
<input type='hidden' name='tempid' value='".$tempid."' />
<input type='hidden' name='tbname' value='".$tbname."' />

<table class='myformtable' align='center' cellpadding=\"5\">
<thead>
<tr >
<td >
Substitui o nome <b>".$nomesearch."</b>
&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
$help = "TODAS as amostras ou árvores marcadas com este nome receberão a identificação abaixo";
echo " onclick=\"javascript:alert('$help');\" />
</td>
</tr>
</thead>
<tbody>
";
//taxonomia
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td class='tdsmallboldleft'>".GetLangVar('namenova')." ".GetLangVar('nameidentificacao')."</td>
        <td  align='left'>
          <table >
            <tr >
              <td class='tdformnotes' id='dettexto'></td>
              <td>
                <input type='hidden' id='detsetcode' name='detset' value='$detset' />
                <input type='button' value='Novo nome para as amostras e plantas de ".$nomesearch."' class='bsubmit' ";
			$myurl ="taxonomia-popup.php?detsetid=detsetcode&dettextid=dettexto"; 
			echo " onclick = \"javascript:small_window('$myurl',800,450,'TaxonomyPopup');\" /></td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </td>
</tr>
<tr>
  <td >
    <table align='center'>
      <tr>
        <td>
        <input type='hidden' value=1  name='final'>
        <input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' /></td>
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
 //PEDE CONFIRMACAO
 if ($final==1) {
 echo "
<form method='post' action='identifybyname_all.php'>
<input type='hidden' name='nomesearch' value='".$nomesearch."' />
<input type='hidden' name='speciesid' value='".$speciesid."' />
<input type='hidden' name='infspecid' value='".$infspecid."' />
<input type='hidden' name='detset' value='".$detset."' />
<input type='hidden' name='tempid' value='".$tempid."' />
<input type='hidden' name='tbname' value='".$tbname."' />
    <table align='center' style='padding: 5px;' class='erro' width='60%'>
       <tr>
        <td>
        Tem certeza de que deseja mudar o nome de todas as amostras e plantas atualmente chamadas de ".$nomesearch."?
        </td>
      </tr>
      <tr>
        <td>
        <input type='hidden' value=2  name='final'>
        <input type='submit' value='Confirmar' class='bsubmit' /></td>
      </tr>
    </table>
  </td>
</tr>
</tbody>
</table>
</form>";
} 
 else {

    //EXECUTA A MUDANÇA DE NOME
	//checa para plantas e amostras com esse nome
	if ($infspecid>0) {
		$qwhere = " WHERE idd.InfraEspecieID=".$infspecid;
	} 
	else {
		$qwhere = " WHERE idd.EspecieID=".$speciesid;
	}
	$sql = "SELECT EspecimenID FROM Especimenes LEFT JOIN Identidade as idd  USING(DetID) ".$qwhere;
	$sql2 = "SELECT PlantaID FROM Plantas LEFT JOIN Identidade as idd  USING(DetID) ".$qwhere;

	$res1 = mysql_query($sql,$conn);
	$numspecs = mysql_numrows($res1);
	
	$res2 = mysql_query($sql2,$conn);
	$numplantas = mysql_numrows($res2);

	$newdetid = 0;
	//cadastro da nova identificacao se há plantas ou amostras para mudar o nome
	if (($numplantas>0 || $numspecs>0) && !empty($detset)) {
		$detarray = unserialize($detset);
		//echopre($detarray);
		//insere nova determinacao que será usada pelo conjunto de amostras
		///////HABILITAR AQUI PARA EXECUTAR MUDANÇA
		$newdetid = InsertIntoTable($detarray,'DetID','Identidade',$conn);
		
	}
		
$erro=0;
$salvo=0;
//SE O CADASTRO DA IDENTIFICACAO FOI FEITO CORRETAMENTE
//ATUALIZA A IDENTIFICACAO DAS PLANTAS
if ($newdetid>0) {
	$udate = $_SESSION['sessiondate'];  //DATA DA ATUALIZACAO
	$chgby = $_SESSION['userid']; //DATA
	$qq = "DROP TABLE temp_detall_".$chgby; //PAGA TABELA TEMPORARIA SE HOUVER
	mysql_query($qq,$conn);
	//SE HÁ ESPECIMENES MUDA PARA ESSES
	if ($numspecs>0) {
		//CRIA UMA TABELA TEMPORARIA COM OS DADOS ATUAIS
		$qq = "CREATE TABLE temp_detall_".$chgby." (SELECT Especimenes.*,".$chgby." as ChangedBy, '".$udate."' as ChangedDate FROM Especimenes JOIN Identidade AS idd USING(DetID) ".$qwhere.")";
		//echo $qq."<br />";
		mysql_query($qq,$conn);
		//ALGUMAS ALTERACOES
		$Qq = "ALTER TABLE temp_detall_".$chgby." CHANGE EspecimenID EspecimenID INT( 10 ) NOT NULL";
		mysql_query($Qq,$conn);
		$QQ = "ALTER TABLE temp_detall_".$chgby." DROP PRIMARY KEY";
		mysql_query($QQ,$conn);
		
		//PREPARA UM INSERT STATEMENT PARA O LOG DA MUDANCA
		$qcol = "SHOW COLUMNS FROM  temp_detall_".$chgby;
		$rr = mysql_query($qcol,$conn);
		$cols = array();
		while ($row = mysql_fetch_assoc($rr)) {
			$cols[] = $row['Field'];
		}
		$qq = "INSERT INTO ChangeEspecimenes (";
		$i=0;
		$ncols = count($cols)-1;
		foreach ($cols as $colum ) {
			if ($i<$ncols) {
				$qq = $qq.$colum.",";
			} else {
				$qq = $qq.$colum;
			}
			$i++;
		}
		$qq = $qq.") (SELECT * FROM temp_detall_".$chgby.")";
		mysql_query($qq,$conn);
		
		//AGORA MUDA DE FATO OS ESPECIMENES
		$qu = "ALTER TABLE temp_detall_".$chgby."  ADD TempID INT(10) unsigned NOT NULL auto_increment PRIMARY KEY";
		mysql_query($qu,$conn);
		$sql = "UPDATE Especimenes, temp_detall_".$chgby." as llixo SET Especimenes.DetID='".$newdetid."' WHERE Especimenes.EspecimenID=llixo.EspecimenID";
		$updatedpls = mysql_query($sql,$conn);
		//echo $sql."<br />";
		if ($updatedpls) {
			$salvo++;
		} else {
			$erro++;
		}
	}
	if ($numplantas>0) {
		$qq = "CREATE TABLE temp_detall_".$chgby." (SELECT Plantas.*,".$chgby." as ChangedBy, '".$udate."' as ChangedDate FROM Plantas JOIN Identidade as idd USING(DetID) ".$qwhere.")";
		mysql_query($qq,$conn);
		//echo $qq."<br />";
		
		$Qq = "ALTER TABLE temp_detall_".$chgby." CHANGE PlantaID PlantaID INT( 10 ) NOT NULL";
		mysql_query($Qq,$conn);
		$QQ = "ALTER TABLE temp_detall_".$chgby." DROP PRIMARY KEY";
		mysql_query($QQ,$conn);
		$qcol = "SHOW COLUMNS FROM  temp_detall_".$chgby;
		$rr = mysql_query($qcol,$conn);
		$cols = array();
		while ($row = mysql_fetch_assoc($rr)) {
			$cols[] = $row['Field'];
		}
		$qq = "INSERT INTO ChangePlantas (";
		$i=0;
		$ncols = count($cols)-1;
		foreach ($cols as $colum ) {
			if ($i<$ncols) {
				$qq = $qq.$colum.",";
			} else {
				$qq = $qq.$colum;
			}
			$i++;
		}
		$qq = $qq.") (SELECT * FROM temp_detall_".$chgby.")";
		mysql_query($qq,$conn);
		
		$qu = "ALTER TABLE temp_detall_".$chgby."  ADD TempID INT(10) unsigned NOT NULL auto_increment PRIMARY KEY";
		mysql_query($qu,$conn);
		$sql = "UPDATE Plantas, temp_detall_".$chgby." as llixo SET Plantas.DetID='".$newdetid."' WHERE Plantas.PlantaID=llixo.PlantaID";
		//echo $sql."<br >";
		$updatedpls = mysql_query($sql,$conn);
		if ($updatedpls) {
			$salvo++;
		} else {
			$erro++;
		}
	}
	if ($salvo>0 || $erro==0) {
		//APAGA O NOME DO CHECKLIST
		$qdel = "DELETE FROM ".$tbname."  WHERE TempID=".$tempid;
		$rdel = mysql_query($qdel);
		if ($rdel) {
		echo "
<br />
  <table cellpadding=\"1\" width='50%' align='center' class='success'>
    <tr><td class='tdsmallbold' align='center'>A identificação de ".$numspecs."  especímenes e ".$numplantas."  plantas foram alteradas e o nome ".$nomesearch." foi apagado do checklist</td></tr>
  </table>
<br />";
		} else {
		echo "
<br />
  <table cellpadding=\"1\" width='50%' align='center' class='success'>
    <tr><td class='tdsmallbold' align='center'>A identificação de ".$numspecs."  especímenes e ".$numplantas."  plantas foram alteradas, MAS o nome ".$nomesearch." não foi apagado do checklist</td></tr>
  </table>
<br />";
		
		}
	}
	if ($erro>0) {
		echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>Houve erro no cadastro da identificação das amostras ou plantas</td></tr>
</table>
<br />";
	}
} 
else {
	echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>Não encontrei a nova identificação!</td></tr>
</table>
<br />";
}
	}
}


$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>