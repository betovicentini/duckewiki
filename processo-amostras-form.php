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
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
//"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
//"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Processamento de amostras físicas';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "<script type='text/javascript' src='javascript/wz_tooltip.js'></script>";
//echopre($ppost);
//SE PEDIU PARA SALVAR UM PROCESSO
$erro=0;
if ($final==1) {
//CHECA
//&& ($filtroid+0)==0 
//if ($processoid=='criar') {
	//$erro++;
	//echo "<br /><table cellpadding=\"7\" width='50%' align='center' class='erro'>  <tr><td class='tdsmallbold' align='center'>Para iniciar um processo precisa indicar um filtro!</td></tr></table><br />";
//}
if ($erro==0) {
//Create table if not exists
$qq = "CREATE TABLE IF NOT EXISTS ProcessosEspecs (
 ProcessoID INT(10) unsigned NOT NULL auto_increment,
 Name CHAR(200),
 Inicio DATE,
 Herbaria CHAR(200),
 Status INT(10),
 CreatedBy INT(10),
 AddedBy INT(10),
 AddedDate DATE,
 PRIMARY KEY (ProcessoID)) CHARACTER SET utf8";
 @mysql_query($qq,$conn);
 
if (empty($tbname)) {
	 if (empty($nome)) {
 		$nome = "Processo ".$quem." ".$datainicio;
	 }
 	$arrayofvalues = array(
'Name' => $nome,
'Inicio' => $datainicio,
'Herbaria' => $herbaria,
'Status' => $status,
'CreatedBy' => $createdby);
} else {
	$datainicio = date("Y-m-d");
	$dataultimo = date("Y-m-d");
	$createdby = $uuid;
	$herbaria = "PDBFF; INPA; COAH; MO;  NY; IAN; CAY; ECUAMZ; VEN;  MG";
	$qq = "SELECT LastName,FirstName FROM Users WHERE UserID=".$createdby;
	$re = mysql_query($qq,$conn);
	$rwe = mysql_fetch_assoc($re);
	$quem = $rwe['FirstName']."  ".$rwe['LastName'];
	
 	$arrayofvalues = array(
'Name' => "Processo ".$quem." ".$datainicio,
'Inicio' => $datainicio,
'Herbaria' => $herbaria,
'CreatedBy' => $createdby);
}
if (!empty($tbname)) {
	$qu = "SELECT * FROM ProcessosEspecs WHERE Name='Processo ".$quem." ".$datainicio."'";
	$ru = mysql_query($qu,$conn);
	$nru = mysql_numrows($ru);
	if ($nru==1) {
		$upp = 1;
		$ruw = mysql_fetch_assoc($ru);
		$processoid = $ruw['ProcessoID'];
	} else {
		$upp = 0;
	}
}
if ((($processoid+0)==0 || $upp==0) && $editando==0) {
	$processoid = InsertIntoTable($arrayofvalues,'ProcessoID','ProcessosEspecs',$conn);
} 
else {
	$upp = CompareOldWithNewValues('ProcessosEspecs','ProcessoID',$processoid,$arrayofvalues,$conn);
	if (($upp+0)>0) { 
		CreateorUpdateTableofChanges($processoid,'ProcessoID','ProcessosEspecs',$conn);
		UpdateTable($processoid,$arrayofvalues,'ProcessoID','ProcessosEspecs',$conn);
	}
}

 if (empty($herbariumsigla)) {
	$herbariumsigla = 'HERB_NO';
}
$qq = "CREATE TABLE IF NOT EXISTS ProcessosLIST (
 ProcessoID INT(10),
 EspecimenID INT(10),
 EXISTE INT(10),
 Herbaria varchar(200),
  ".$herbariumsigla."  INT(10))
CHARACTER SET utf8";
 @mysql_query($qq,$conn);
}
}
//INSERE OU ATUALIZA REGISTROS AO PROCESSO QUANDO FOR O CASO
if ($erro==0 && $processoid>0) {
	if ($filtroid>0) {
		$qz = "INSERT INTO ProcessosLIST (ProcessoID,EspecimenID,EXISTE, Herbaria, ".$herbariumsigla." ) (SELECT ".$processoid.", pltb.EspecimenID,0 as EXISTE, pltb.Herbaria, pltb.INPA_ID FROM Especimenes as pltb LEFT JOIN ProcessosLIST as proc USING(EspecimenID) WHERE (pltb.FiltrosIDS LIKE '%filtroid_".$filtroid.";%' OR pltb.FiltrosIDS LIKE '%filtroid_".$filtroid."') AND  CONCAT(proc.ProcessoID,'_',proc.EspecimenID)<>CONCAT(".$processoid.",'_',pltb.EspecimenID))";
		
		
		
		
		//echo $qz."<br /><br />";
		@mysql_query($qz,$conn);
	} elseif (!empty($tbname)) {
		$qz = "INSERT INTO ProcessosLIST (ProcessoID,EspecimenID,EXISTE ) (SELECT ".$processoid.", pltb.EspecimenID,tb.EXISTE FROM Especimenes as pltb JOIN ".$tbname." as tb ON tb.PlantaID=pltb.PlantaID WHERE tb.EXISTE=1)";
		@mysql_query($qz,$conn);
	}
}
///NOVO OU EDICAO DE UM PROCESSO
if (!isset($processoid)) {
echo "
<br />
<table class='myformtable' align='center' cellpadding='7' width='50%'>
<thead>
<tr ><td >Executar ou criar um processamento de amostras
&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
	$help = "Esta ferramenta deve ser usada para quando preparar para depositar amostras em coleções. Aqui você irá comparar as amostras físicas com os dados no wiki, checar se as imagens já existem, se já existem espectros NIR, imprimir etiquetas, etc.";
	echo "onclick=\"javascript:alert('$help');\" />
</td></tr>
</thead>
<tbody>
<form action='processo-amostras-form.php' method='post'>
<input type='hidden' name='ispopup' value='$ispopup' >
";
echo "
<tr>
  <td>
    <select name='processoid' onchange='this.form.submit()'>
      <option value=''>".GetLangVar('nameselect')."</option>
      <option value=''>------------</option>
      <option value='criar'>Criar novo processo!</option>
      <option value=''>------------</option>";
      $qq = "SELECT * FROM ProcessosEspecs ORDER BY AddedDate DESC";
      //WHERE CreatedBy='".$uuid."'  OR '".$acclevel ."' LIKE 'admin'  
      $rrr = @mysql_query($qq,$conn);
      while ($row = @mysql_fetch_assoc($rrr)) {
			echo "
      <option value=".$row['ProcessoID'].">".$row['Name']." </option>";
		}
	echo "
    </select>
    </td>
</tr>
</tbody>
</table>
</form>";
} 
else {
if ($processoid=='criar') {
	$tt = 'Novo processo';
	$datainicio = date("Y-m-d");
	$dataultimo = date("Y-m-d");
	$createdby = $uuid;
	$qq = "SELECT LastName,FirstName FROM Users WHERE UserID=".$createdby;
	$re = mysql_query($qq,$conn);
	$rwe = mysql_fetch_assoc($re);
	$quem = $rwe['FirstName']."  ".$rwe['LastName'];
	$editando=0;
} 
else {
	$editando=1;
	$tt = "Editando o processo ";
		$qq = "SELECT pcs.*,us.LastName,us.FirstName FROM ProcessosEspecs as pcs JOIN Users as us ON us.UserID=pcs.CreatedBy WHERE pcs.ProcessoID=".$processoid;
		$re = mysql_query($qq,$conn);
		if ($re) {
			$rwe = mysql_fetch_assoc($re);
			$nome = $rwe['Name'];
			$datainicio = $rwe['Inicio'];
			$status = $rwe['Status'];
			if ($status==1) {
				$txcon = ' [ CONCLUIDO ] ';
			}
			$quem = $rwe['FirstName']."  ".$rwe['LastName'];
			$createdby = $rwe['CreatedBy'];
			$dataultimo = $rwe['AddedDate'];
			//$herbaria = $rwe['Herbaria'];
		}
	$tt .= $nome."  ".$txcon;
}
$herbaria = "INPA; ESPECIALISTA; PDBFF; COAH; CAY; ECUAMZ; VEN;  MG; IAN;  RB; NY; MO";
$bgi=1;
echo "
<br />
<table class='myformtable' cellpadding='7' align='center' >
<thead>
  <tr><td >$tt</td></tr>
</thead>
<tbody>
  <form name='coletaform' action='processo-amostras-form.php' method='post'>
  <input type='hidden' name='ispopup' value='$ispopup' >
  <input type='hidden' name='editando' value='".$editando."' />
  <input type='hidden' name='processoid' value='".$processoid."' />
  <input type='hidden' name='createdby' value='".$createdby."' />
  <input type='hidden' name='datainicio' value='".$datainicio."' />
  <input type='hidden' name='quem' value='".$quem."' />
  ";
    if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
  echo "
  <tr bgcolor = '".$bgcolor."'>
    <td >
      <table cellpadding='3'>
        <tr>
          <td class='tdformright'>Título&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
	$help = "Um nome qualquer para o processo. Assim, você pode interromper e continuar depois um processo até concluílo!";
	echo "onclick=\"javascript:alert('$help');\" /></td>
          <td>
            <input style='height: 2em'  type='text' name=\"nome\" value=\"$nome\" size='50'  />
          </td>
          <td >
            <table class='tdformnotes'>
              <tr>
                <td class='tdformright'>Criado em</td>
                <td>".$datainicio." [por ".$quem."]</td>
              </tr>
              <tr>
                <td class='tdformright'>Modificado em</td>
                <td>".$dataultimo."</td>
            </tr>
          </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>";
$nspecs = 0;  
if ($processoid=='criar') {
	$help = "Indique o filtro que contém as amostras a serem processadas";
	$txt = "Filtro com os especimenes&nbsp;<img height=15 src=\"icons/icon_question.gif\" onclick=\"javascript:alert('$help');\" />";
} 
else {
	$tbname2 = 'ProcessosLIST';
	$sql = "SELECT COUNT(*) AS specs  FROM ".$tbname2." WHERE ProcessoID=".$processoid;
	//echo $sql."<br />";
	$sqlres = @mysql_query($sql,$conn);
	$sqlrow = @mysql_fetch_assoc($sqlres);
	$nspecs = $sqlrow['specs']+0;

	$sql = "SELECT COUNT(*) AS specs  FROM ".$tbname2." WHERE ProcessoID=".$processoid."  AND EXISTE>0";
	//echo $sql."<br />";
	$sqlres = @mysql_query($sql,$conn);
	$sqlrow = @mysql_fetch_assoc($sqlres);
	$nspecexiste = $sqlrow['specs']+0;

	$help = "Indique o filtro que contém novas amostras a serem adicionas ao processo";
	$txt = "Filtro para adicionar amostras&nbsp;<img height=15 src=\"icons/icon_question.gif\" onclick=\"javascript:alert('$help');\" />";
}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
  echo "
  <tr bgcolor = '".$bgcolor."'>
    <td >
      <table cellpadding='3'>
      <tr>
        <td class='tdsmallbold'>$txt</td>
        <td>
          <select name='filtroid'>
            <option selected value=''>".GetLangVar('nameselect')."</option>";
			$qq = "SELECT * FROM Filtros WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ORDER BY FiltroName";
			$res = @mysql_query($qq,$conn);
			while ($rr = @mysql_fetch_assoc($res)) {
				echo "
          <option value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
			}
			mysql_free_result($res);
echo "
          </select>
        </td>
        <td class='tdsmallbold'><input style='cursor:pointer' type='button'  class='bblue'  value='Gerar um filtro'  onclick = \"javascript:small_window('filtros-form.php?ispopup=1',650,300,'Gerar um filtro');\" ></td>
      </tr>      
    </table>
  </td>
</tr>";
if ($nspecs>0) {
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
  echo "
  <tr bgcolor = '".$bgcolor."'>
    <td class='tdsmallbold'  align='center'>ESTE PROCESSO CONTÉM ".$nspecs." REGISTROS, SENDO ".$nspecexiste." MARCADOS COMO EXISTE!</td>
    </td>
  </tr>"; 
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
  echo "
  <tr bgcolor = '".$bgcolor."'>
    <td >
      <table>
      <tr>
        <td class='tdsmallbold'   align='center' >PASSO 01 &nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
	$help = "Neste passo você deve visualizar os registros e marcar como EXISTE os registros que serão de fato processados nos próximos passos. Aqui também pode checar/editar/atualizar dados faltantes como Número de Duplicatas, se tem imagem de exsicata, se tem dado NIR, etc.";
	echo "onclick=\"javascript:alert('$help');\" /></td>
        <td align='center' ><input  type='button'  style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"   value='Visualizar/editar tabela registros'  onmouseover=\"Tip('Mostra a tabela dos registros ou gera ela se não existir');\"    onclick = \"javascript:small_window('processo-amostras-gridform.php?processoid=".$processoid."&ispopup=1',650,300,'Visualizar amostras processamento');\" ></td>
        <td align='center' ><input  type='button'  style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Re-gerar tabela registros'  onmouseover=\"Tip('Atualiza a tabela se existir, extraindo novamente as informações do banco de dados, exceto a coluna EXISTE');\"   onclick = \"javascript:small_window('processo-amostras-gridform.php?update=1&processoid=".$processoid."&ispopup=1',650,300,'Visualizar amostras processamento');\" ></td>        
      <tr>
      </table>
    </td>
  </tr>"; 
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
	$sql = "SELECT COUNT(DISTINCT prc.EspecimenID) AS nir  FROM processo_".$processoid." as prc JOIN NirSpectra as nn ON nn.EspecimenID=prc.EspecimenID WHERE prc.EXISTE=1";
	$sqres = @mysql_query($sql,$conn);
	$sqrow = @mysql_fetch_assoc($sqres);
	$nnir = $sqrow['nir']+0;
	$pernir = @round(($nnir/$nspecexiste)*100);

	$sql = "SELECT SUM(IF ( (".$exsicatatrait."+0)>0, IF(checktrait(pltb.EspecimenID, 0, (".$exsicatatrait."+0))='OK',1,IF(checktrait(pltb.EspecimenID, 0, (351+0))='OK',1,0)),0)) AS imgg  FROM processo_".$processoid." as pltb WHERE EXISTE=1";
	//echo $sql."<br />";
	$sqres = @mysql_query($sql,$conn);
	$sqrow = @mysql_fetch_assoc($sqres);
	$imgg = $sqrow['imgg']+0;
	$nimg = @round(($imgg/$nspecexiste)*100);

	$sql = "SELECT COUNT(*) AS nup  FROM processo_".$processoid." WHERE NDuplic>0 AND EXISTE=1";
	$sqres = @mysql_query($sql,$conn);
	$sqrow = @mysql_fetch_assoc($sqres);
	$ndups = $sqrow['nup'];
	$perdups = @round(($ndups/$nspecexiste)*100);

	$sql = "SELECT COUNT(*) AS nup  FROM processo_".$processoid." WHERE Herbaria IS NOT NULL AND Herbaria<>'' AND EXISTE=1";
	$sqres = @mysql_query($sql,$conn);
	$sqrow = @mysql_fetch_assoc($sqres);
	$ndist = $sqrow['nup'];
	$perdist = @round(($ndist/$nspecexiste)*100);

echo "
  <tr bgcolor = '".$bgcolor."'>
    <td >
      <table>
      <tr>
        <td class='tdsmallbold'   align='center' >PASSO 02 &nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
	$help = "Você deve concluir este passo antes de prosseguir com os demais. Consiste apenas em garantir que as leituras NIR foram realizadas e também que existem imagens de exsicatas para as amostras marcadas como EXISTE no processo";
	echo "onclick=\"javascript:alert('$help');\" /></td>
        <td>
        <table>
        <tr>
        <td align='left'>Imagens exsicatas</td>
        <td colspan='2'  align='left'><progress id='probar' value='".$nimg."' max='100'></progress>    ".$nimg."% </td>
        </tr>
        <tr>
        <td align='left'>Dados NIR</td>
        <td align='left'><progress id='probar' value='".$pernir."' max='100'></progress>    ".$pernir."% </td>
        <td align='left'>";
        if ($pernir<100) {
        echo "<input  type='button'  style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"   value='Exporta NIR'  onmouseover=\"Tip('Exportar planilha para ler dados NIR');\"   onclick = \"javascript:small_window('export-nir-spreadsheet.php?wikiid=1&sampletype=specimens&processoid=".$processoid."&ispopup=1',650,300,'Exporta planilha NIR');\" >";
        }
echo "</td>
        </tr>
      </table>
    </td>
  </tr>
      </table>
    </td>
  </tr>"; 
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
  <tr bgcolor = '".$bgcolor."'>
    <td >
      <table>
        <tr>
        <td class='tdsmallbold'   align='center' >PASSO 03 &nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
	$help = "Distribuir duplicatas para diferentes herbários, seguindo a ordem de preferência e o número de duplicatas informado para cada amostra. A palavra ESPECIALISTA, se usada na lista, será substituida pelo herbário do especialista da familia se houveer, conforme a tabela de especialistas. Antes de distribuir amostras você precisa anotar o número de duplicatas para todas as amostras marcadas como EXISTE, mesmo para unicatas!";
	echo "onclick=\"javascript:alert('$help');\" /></td>
        <td>
        <table>
        <tr>
        <td align='left'>No. duplicatas anotado</td>
        <td align='left'><progress style='color: green' id='probar' value='".$perdups."' max='100'></progress>    ".$perdups."% </td>
        <td align='center'> <input  type='button'  style=\"color:#339933; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"   value='Especialistas'  onmouseover=\"Tip('Ver/editar Especialistas');\" onclick = \"javascript:small_window('especialista-gridsave.php?processoid=".$processoid."&ispopup=1',900,600,'Especialistas');\" /></td>
        </tr>
        <tr>
        <td align='left'>Lista de herbários</td>
        <td align='left'>
        <input type='text' style='height: 2.5em;' size='80'  id='herbariumlista'  name=\"herbaria\"  value='".$herbaria."'  /></td>
        <td align='center' ><input  type='button'  style=\"color:#339933; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"   value='Distribuir amostras'  onmouseover=\"Tip('Distribuir as duplicatas para herbários indicados');\"  ";
       if ($perdups<100) {
echo " onclick = \"javascript:alert('Precisa primeiro anotar as duplicatas das amostras marcadas como EXISTE no processo. Importante marcar APENAS as amostras que de fato serão distribuidas');\" ";
       } else {
echo " onclick = \"javascript:small_window('processo-amostras-distribute.php?processoid=".$processoid."&ispopup=1',800,400,'Distribui amostras');\" ";
}        
echo "
        ></td>
        </tr>
      </table>
    </td>
  </tr>
      </table>
    </td>
  </tr>";       
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
  <tr bgcolor = '".$bgcolor."'>
    <td >
      <table>
      <tr>
        <td class='tdsmallbold'   align='center' >PASSO 04 &nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
$help = "Agora você irá imprimir as etiquetas e colocar nas duplicatas, separando as amostras aos herbários conforme a distribuição das duplicatas que aparece na etiqueta! Apenas o material do ".$herbariumsigla." receberá nova etiqueta após registro no herbário";
echo " onclick=\"javascript:alert('$help');\" /></td>
        <td align='center' ><input  type='button'  style=\"color:#339933; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"   value='Imprimir etiquetas'  onclick = \"javascript:small_window('processo-amostras-labels.php?processoid=".$processoid."&ispopup=1',800,400,'Imprimir etiquetas');\" ></td>
      </tr>
      </table>
    </td>
  </tr>";    
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;

	//if ($perdist<100 || $perdups<100 || $pernir<100 || $nimg<100) {
	  // $txt1 = " onclick = \"javascript:alert('Precisa concluir os passos 02 e 03 para poder exportar os dados e concluir o processo!');\" ";
	   //$txt2 = $txt1;
	//} else {
	   $txt1 = " onclick = \"javascript:small_window('processo-amostras-export.php?processoid=".$processoid."&ispopup=1',800,400,'Exportar dados');\" ";
	   $txt2 = "onclick = \"javascript:small_window('processo-amostras-importarinpa.php?processoid=".$processoid."&ispopup=1',800,400,'Importar No. INPA');\" ";
	//}
  echo "
  <tr bgcolor = '".$bgcolor."'>
    <td >
      <table>
      <tr>
        <td class='tdsmallbold'   align='center' >PASSO 05 &nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
$help = "Exportar os dados para a base de dados do herbário ".$herbariumsigla.", entregar a planilha no herbário e trazer de volta com o Número INPA e BRAHMS. Checar que a planilha contém TODAS e APENAS as amostras para o herbário";
echo " onclick=\"javascript:alert('$help');\" /></td>
        <td align='center' ><input  type='button'  style=\"color:#339933; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"   value='Exportar dados para ".$herbariumsigla."'  ".$txt1."  /></td>
        <td align='center' ><input  type='button'  style=\"color:#339933; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"   value='Importar No. ".$herbariumsigla."'  ".$txt2." /></td>
      </tr>
      </table>
    </td>
  </tr>";    
  //<td align='center' ><input  type='button'  style=\"color:#339933; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"   value='Registrar ".$herbariumsigla."'  onclick = \"javascript:small_window('processo-amostras-ninpa.php?processoid=".$processoid."&ispopup=1',800,400,'Registrar No. Herbario');\" ></td>

} 
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table align='center' >
      <tr>
        <input type='hidden' name='final' value='' />";
if ($nspecs>0) {
echo "        
        <td align='center' ><input style='cursor: pointer'  type='submit' value='Atualizar' class='bblue' onclick=\"javascript:document.coletaform.final.value=2\" /> </td>";
} 
else {
echo "    
        <td align='center' ><input style='cursor: pointer'  type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' onclick=\"javascript:document.coletaform.final.value=1\" /> </td>";
}
echo "
      </tr>
    </table>
  </td>
</tr>
</form>
</tbody>
</table>
";
} 
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
//, "<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=TRUE,$footer=$menu);
?>