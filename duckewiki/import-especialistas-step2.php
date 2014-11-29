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
$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Importar Especialistas Passo02';
$body = '';

if ($enviado==1) {
	if (empty($especialistacol) || empty($emailcol) || empty($familiacol) || empty($herbariocol)) {
		$erro=1;
		echo "
<br />
  <table cellpadding=\"7\" align='center' class='erro'>
  <tr ><td class='tdformnotes' align='center'>Você não informou todas as colunas obrigatórias!</td></tr>
  <tr ><td class='tdformnotes' align='center'>As colunas NOME, EMAIL, FAMILIA e HERBARIO são obrigatórias!</td></tr>
</table>
<br />";
	} else {
		$txt = ''; 
		$i=0;
		foreach ($ppost as $kk => $vv) {
			if ($i==0) {
				$txt .= $kk."=".$vv;
			} else {
				$txt .= "&".$kk."=".$vv;
			}
			$i++;
		}
		header("location: import-especialistas-step3.php?".$txt);
	}
} else {
	$erro=1;
}
FazHeader($title,$body,$which_css,$which_java,$menu);

if ($erro>0) {
echo $txt."
<form action='import-especialistas-step2.php' method='post' name='impprepform'>";
foreach ($_POST as $kk => $vv) {
echo "
  <input type='hidden' name='".$kk."' value='".$vv."' />"; 
}
echo "
  <input type='hidden' name='enviado' value='1' /> 
    <table cellpadding='7' class='myformtable' align='center'>
        <thead>
            <tr><td colspan='5'>Especificar as colunas necessárias</td></tr>
            <tr class='subhead'>
                <td>Nome da Coluna</td>
                <td class='redtext'>Equivalente na sua planilha</td>
            </tr>
        </thead>
        <tbody>";
		if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
			echo "
            <tr bgcolor = '".$bgcolor."'>
                <td class='tdformnotes'>Coluna com nome do especialista*</td>
                <td class='tdformnotes'>
                  <select name='especialistacol' >
                    <option value=''>Selecione coluna</option>
                  ";
                	$qq = "SELECT * FROM `".$tbname."` PROCEDURE ANALYSE ()";
					$rq = mysql_query($qq,$conn);
					while ($rw = mysql_fetch_assoc($rq)) {
							$fin = $rw['Field_name'];
							$sn = explode(".",$fin);
							echo "<option value='".$sn[2]."'>".$sn[2]."</option>";
					}
				echo "
                  </select>
                  </td>
                </tr>";
		if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
			echo "
            <tr bgcolor = '".$bgcolor."'>
                <td class='tdformnotes'>Coluna com EMAIL do especialista*</td>
                <td class='tdformnotes'>
                  <select name='emailcol' >
                     <option value=''>Selecione coluna</option>
                  ";
                	$qq = "SELECT * FROM `".$tbname."` PROCEDURE ANALYSE ()";
					$rq = mysql_query($qq,$conn);
					while ($rw = mysql_fetch_assoc($rq)) {
							$fin = $rw['Field_name'];
							$sn = explode(".",$fin);
							echo "<option value='".$sn[2]."'>".$sn[2]."</option>";
					}
				echo "
                  </select>
                  </td>
                </tr>";                
		if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
			echo "
            <tr bgcolor = '".$bgcolor."'>
                <td class='tdformnotes'>Coluna com a família do especialista*</td>
                <td class='tdformnotes'>
                  <select name='familiacol' >
                    <option value=''>Selecione coluna</option>
                  ";
                	$qq = "SELECT * FROM `".$tbname."` PROCEDURE ANALYSE ()";
					$rq = mysql_query($qq,$conn);
					while ($rw = mysql_fetch_assoc($rq)) {
							$fin = $rw['Field_name'];
							$sn = explode(".",$fin);
							echo "<option value='".$sn[2]."'>".$sn[2]."</option>";
					}
				echo "
                  </select>
                  </td>
                </tr>";
		if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
			echo "
            <tr bgcolor = '".$bgcolor."'>
                <td class='tdformnotes'>Coluna com GENEROS do especialista</td>
                <td class='tdformnotes'>
                  <select name='generocol' >
                    <option value=''>Selecione coluna</option>
                  ";
                	$qq = "SELECT * FROM `".$tbname."` PROCEDURE ANALYSE ()";
					$rq = mysql_query($qq,$conn);
					while ($rw = mysql_fetch_assoc($rq)) {
							$fin = $rw['Field_name'];
							$sn = explode(".",$fin);
							echo "<option value='".$sn[2]."'>".$sn[2]."</option>";
					}
				echo "
                  </select>
                  </td>
                </tr>";                
		if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
			echo "
            <tr bgcolor = '".$bgcolor."'>
                <td class='tdformnotes'>Coluna com sigla HERBARIO do especialista*</td>
                <td class='tdformnotes'>
                  <select name='herbariocol' >
                    <option value=''>Selecione coluna</option>
                  ";
                	$qq = "SELECT * FROM `".$tbname."` PROCEDURE ANALYSE ()";
					$rq = mysql_query($qq,$conn);
					while ($rw = mysql_fetch_assoc($rq)) {
							$fin = $rw['Field_name'];
							$sn = explode(".",$fin);
							echo "<option value='".$sn[2]."'>".$sn[2]."</option>";
					}
				echo "
                  </select>
                  </td>
                </tr>";

	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
		echo "
            <tr bgcolor = '".$bgcolor."'><td colspan='2' align='center'><input style='cursor: pointer' type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td></tr>
        </tbody>
    </table>
</form>";
}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>
