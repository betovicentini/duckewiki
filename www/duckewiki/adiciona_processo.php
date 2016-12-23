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


$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
);
$which_java = array(
);
$title = 'Adiciona Amostra ao Processo';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (!isset($processoid) || ($processoid+0)==0) {
	//checa se já está num processo!
	$qz = "SELECT Name,Inicio,Status,ProcessosEspecs.ProcessoID,CreatedBy FROM ProcessosLIST LEFT JOIN ProcessosEspecs USING(ProcessoID) WHERE EspecimenID=".$especimenid;
	//echo $qz."<br >";
	$rz = mysql_query($qz,$conn);
	$nrz = mysql_numrows($rz);
	$txts = "";
	$concluido=0;
	$prcid=0;
	$createdby = 0;
	if ($nrz>0) {
		while ($rzw = mysql_fetch_assoc($rz)) {
			$txt = "<tr><td>".$rzw['Name']."</td><td>".$rzw['Inicio']."</td>";
			if ($rzw['Status']==0) {
				$txt .= "<td>EM ANDAMENTO</td></tr>";
					$prcid = $rzw['ProcessoID'];
					$createdby = $rzw['CreatedBy'];
			} else {
				$txt .= "<td>CONCLUIDO</td></tr>";
				$concluido=1;
			}
			$txts .= "
".$txt;
		}
	}
	if ($concluido==1) {
echo "
<br />
<table class='myformtable' align='left' cellpadding='7' >
<thead><tr ><td colspan='3'>Essa amostra já foi processada</td></tr></thead>
<tbody>".$txts."</tobdy></table><br />";
	}
	else {
		$nooption=0;
		if ($prcid==0) {
			$th = "Adiciona Amostra a Processo";
		} else {
			if ($createdby==$uuid || $acclevel=='admin') {
				$th = "Muda a Amostra de Processo";
			} else {
				$th = "Você não tem autorização para mudar esta amostra de processo";
				$nooption=1;
			}
		}
		
echo "
<br />
<form action='adiciona_processo.php' method='post'>
<table class='myformtable' align='left' cellpadding='7' >
<thead><tr ><td >".$th."</td></tr></thead>";
if ($nooption==1) {
echo "<tbody>".$txts."</tobdy></table><br />";
} else {
echo "
<tbody>
";
if ($txts!="") {
echo ";
<tr><td><table class='tdformnotes' ><tr><td colspan='4'>A amostra já está nos processos</td></tr>
".$txts."</table></td></tr>";
}
echo "
<tr>

  <td>
    <table>
    <tr>  <td>".$onome." </td><td>
    <input type='hidden' name='especimenid' value='".$especimenid."' >
    <input type='hidden' name='oldprocessoid' value='".$prcid."' >
    <input type='hidden' name='onome' value='".$onome."' >

    <select name='processoid' onchange='this.form.submit()'>";  
      if ($prcid==0) {
echo "
      <option value=''>adiciona ao processo</option>"; 
      }
echo"
      <option value=''>------------</option>";
      $qq = "SELECT * FROM ProcessosEspecs WHERE Status=0";
      if ($acclevel!="admin") {
         $qq .= " WHERE AddedBy=".$uuid;
      } 
      $qq .=  " ORDER BY AddedDate DESC";
      $rrr = @mysql_query($qq,$conn);
      while ($row = @mysql_fetch_assoc($rrr)) {
            $sel = '';
            if ($prcid==$row['ProcessoID']) {
            	$sel = "selected";
            }
			echo "
      <option ". $sel." value=".$row['ProcessoID'].">".$row['Name']." </option>";
		}
	echo "
    </select>
    </td>
    </tr>
    </table>
    </td>
</tr>
</tbody>
</table>
</form>";
		}
	}
} else {
	//echopre($ppost);
	if ($oldprocessoid>0 && $processoid!=$oldprocessoid) {
		$qn = "DELETE * FROM ProcessosLIST WHERE ProcessoID=".$oldprocessoid." AND EspecimenID=".$especimenid;
		@mysql_query($qn,$conn);
	}
	$qn = "SELECT * FROM ProcessosLIST WHERE ProcessoID=".$processoid." AND EspecimenID=".$especimenid;
	$tem = mysql_query($qn,$conn);
	$ntem = mysql_numrows($tem);
	if ($ntem>0) {
			echo "<b>O registro já estava no processo</b>";
	} else {
		$qz = "INSERT INTO ProcessosLIST (ProcessoID,EspecimenID,EXISTE, Herbaria, ".$herbariumsigla." ) (SELECT ".$processoid.", pltb.EspecimenID,1 as EXISTE, pltb.Herbaria, pltb.INPA_ID FROM Especimenes as pltb WHERE pltb.EspecimenID=".$especimenid.")";
		$inseriu = mysql_query($qz,$conn);
		if ($inseriu) {
			echo "<b>Inseriu o registro ".$onome." corretamente no processo!<br >Para visualizar regerar a tabela no Processo!<br >O registro foi marcado como existe</b>";
		} else {
				echo $qz."<br >";
				echo "houve um erro";
		}
	}
	echo "<br ><input type='button' onclick='javascript: window.close();'  value='Fechar' >";
}
$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
);
FazFooter($which_java,$calendar=TRUE,$footer=$menu);
?>