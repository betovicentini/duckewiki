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
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='javascript/tabber/tabber.css' >"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>",
"<script type='text/javascript' src='javascript/filterlist.js'></script>",
"<script type='text/javascript'> $(document).ready(function(){ $('.toggle_container').hide(); $('h2.trigger').click(function(){ $(this).toggleClass('active').next().slideToggle('slow'); }); });</script>"
);
$title = 'Calcular Traits';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<br />
<form action='traits-calculate-exec.php' method='post' name='calculateform'>
<table class='myformtable' cellpadding=\"5\" align='center' width='80%'>
<thead>";
//estado ou caractere ou grupo de caracteres
echo "
<tr>
  <td colspan='100%'>".GetLangVar('calculatevariables')."&nbsp;<img size=12 src=\"icons/icon_question.gif\" ";
		$help = strip_tags(GetLangVar('calculatevariables_help'));
		echo " onclick=\"javascript:alert('$help');\" />
</td></tr>
</thead>
<tbody>
<tr>
<td colspan='100%'>
  <table>
  <tr>
    <td>
      <table>
        <tr>
          <td class='tdsmallboldleft'>Variável para armazenar o resultado:</td>
        </tr>
        <tr>
          <td class='tdsmallbold'>
            <select name='traitid' >";
			if (empty($traitid)) {
				echo "
              <option value=''>".GetLangVar('nameselect')."</option>";
			} else {
				$qq = "SELECT * FROM Traits WHERE TraitID='$traitid'";
				$rr = mysql_query($qq,$conn);
				$row = mysql_fetch_assoc($rr);
				echo "
              <option selected value=".$row['TraitID'].">".$row['TraitName']."</option>";
			}
			//echo "<option>----</option>";
			$qq ="SELECT * FROM Traits WHERE TraitTipo='Variavel|Quantitativo' OR TraitTipo='Classe' ORDER BY PathName,TraitName";
			$res = mysql_query($qq,$conn);
			//$res = listtraits($filtro,$conn);
			while ($aa = mysql_fetch_assoc($res)){
					$PathName = $aa['PathName'];
					$level = $aa['MenuLevel'];
					$tipo = $aa['TraitTipo'];
					$estado = GetLangVar('traitkind3');
					if (!empty($aa['TraitName'])) {
						if ($level==1) {
							$espaco='';
						} else {
							$espaco = str_repeat('&nbsp;&nbsp;&nbsp;',$level);
						}
						if ($tipo=='Classe') { //if is a class or a state does not allow selection
							echo "
              <option class='optselectdowlight' value=''>$espaco<i>".$aa['TraitName']."</i></option>";
						} else { 
							$espaco = $espaco.str_repeat('- ',$level-1);
							if ($tipo!='Estado') {
								echo "
              <option value='".$aa['TraitID']."'>$espaco".$aa['TraitName']."</option>";
							} else {
								echo "
              <option class='optselectdowlight3' value='".$aa['TraitID']."'>$espaco".$aa['TraitName']."</option>";
							}
						}
					}
			}
echo "
            </select>
          </td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <tr><td class='tdsmallboldleft'>".GetLangVar('nameselect')." ".mb_strtolower(GetLangVar('namefiltro'))."&nbsp;<img size='12' src=\"icons/icon_question.gif\" ";
		$help = 'Filtro com amostras para calcular a nova variável';
		echo " onclick=\"javascript:alert('$help');\" /></td></tr>
        <tr>
          <td class='tdsmallbold'>
            <select name='filtro'>";
		if (!empty($filtro)) {
			$qq = "SELECT * FROM Filtros WHERE FiltroID='".$filtro."'";
			$res = @mysql_query($qq,$conn);
			$rr = @mysql_fetch_assoc($res);
			echo "
              <option selected value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
			mysql_free_result($res);
		} 
			echo "
              <option  value=''>".GetLangVar('nameselect')."</option>";
			$qq = "SELECT * FROM Filtros ORDER BY FiltroName";
			$res = @mysql_query($qq,$conn);
			while ($rr = @mysql_fetch_assoc($res)) {
				echo "
              <option value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
			}
			mysql_free_result($res);
	echo "
            </select>
          </td>
        </tr>
      </table>
    </td>
    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
    <td>
      <table>
        <tr><td class='tdsmallboldleft' >".GetLangVar('listacodigosvariaveis')."</td></tr>
        <tr>
          <td class='tdsmallbold'>
            <select name='traitlist' size='6' readonly>";
			$filtro ="SELECT * FROM Traits WHERE TraitTipo='Variavel|Quantitativo' ORDER BY PathName,TraitName";
			$rs = mysql_query($filtro,$conn);
			//$res = listtraits($filtro,$conn);
			while ($aaa = mysql_fetch_assoc($rs)){
					$PathName = $aaa['PathName'];
					$level = $aaa['MenuLevel'];
					$tipo = $aaa['TraitTipo'];
					$estado = GetLangVar('traitkind3');
					if (!empty($aaa['TraitName'])) {
						if ($level==1) {
							//$espaco='';
						} else {
							//$espaco = str_repeat('&nbsp;&nbsp;&nbsp;',$level);
						}
						if ($tipo=='Classe') { //if is a class or a state does not allow selection
							//echo "              <option class='optselectdowlight' value=''>$espaco<i>".$aaa['TraitName']."</i></option>";
						} else { 
							//$espaco = $espaco.str_repeat('- ',$level-1);
							if ($tipo!='Estado') {
								echo "
              <option value='".$aaa['TraitID']."'>".$aaa['PathName']." CODE= &".$aaa['TraitID']."&</option>";
							} else {
								//echo "<option class='optselectdowlight3' value='".$aaa['TraitID']."'>$espaco".$aaa['PathName']."</option>";
							}
						}
					}
			}
echo "</select>
            </td>
          </tr>
<script type=\"text/javascript\">
<!--
var myfilter = new filterlist(document.calculateform.traitlist);
//-->
</script>    
    <tr>
      <td colspan='100%'>
        <table>
          <tr>
            <td>Filtrar:</td>
            <td><input name='regexp' onKeyUp=\"myfilter.set(this.value);\" /></td>
            <td><input type='button' onclick=\"myfilter.set(this.form.regexp.value)\" value=\"Filtrar\" /></td>
            <td><input type='button' onclick=\"myfilter.reset();this.form.regexp.value=''\" value=\"Limpar\" /></td>
          </tr>
          <tr><td colspan='100%'><input type='checkbox' name=\"toLowerCase\" onclick=\"myfilter.set_ignore_case(!this.checked);\" />&nbsp;Case sensitive</td></tr>
         </table>
     </td>
    </tr>
          
        </table>
      </td>
    </tr>
  </table>
</td>
</tr>
<tr><td colspan='100%' class='tabsubhead'>".GetLangVar('nameformula')."</td></tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor = '".$bgcolor."'>
<td colspan='100%'>
  <table>
    <tr>
      <td class='tdformnotes'>
        <ul>
          <li>( ) parênteses para separar os argumentos</li>
          <li>* multiplica</li>
          <li>/ divide</li>
          <li>- subtrai</li>
          <li>+ soma</li>
          <li> use o código das variáveis =  número entre os símbolo '&' (e.g. &643&)</li>
        </ul>
      </td>
      <td align='center'>
        <textarea cols='40%' rows='6' name='formula' >".$formula."</textarea>
      </td>
    </tr>
  </table>
</td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor = '".$bgcolor."'>
<td colspan='100%' class='tdformnotes' align='center'><input type='submit' value='".GetLangVar('namecalcular')."' class='bsubmit' /></td></tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%' >
    <table align='center'>
      <tr><td colspan='100%' class='tabsubhead'>".GetLangVar('nameexemplo')."</td></tr>
      <tr>
        <td colspan='100%' class='tdformnotes' >
          <br />Você pode descrever a forma das folhas tirando as proporções entre as partes e medidas.  Suponha que você tenha medido as seguintes variáveis para um conjunto de amostras (filtro):
          <ul><li>Comprimento da Folha (incluindo pecíolo) - COMPFOL</li>
          <li>Largura da Lâmina no ponto de máxima largura - LARGMAX</li>
          <li>Altura da medição da Largura (da base do pecíolo ao ponto de máxima largura) - ALTLARG
          <li>Comprimento do Pecíolo - COMPPEC</li>
          </ul>
          Você pode calcular as seguintes variáveis que descrevem forma:
          <ul>
          <li><i>Folha_Forma1</i> = ((LARGMAX/(COMPFOL-COMPPEC)) - largura da lâmina divido pela comprimento da folha (vai produzir um número geralmente menor que 0, mais próximo de 1 para folhas mais redondas e proximo de 0 para folhas muito estreitas e longas).</li>
          <li><i>Folha_Forma2</i> = (COMPPEC/COMPFOL) - proporção da folha que é pecíolo, ou seja o comprimento do pecíolo dividido pelo comprimento total da folha.</li>
          <li><i>Folha_Forma3</i> = ((ALTLARG-COMPPEC)/(COMPFOL-COMPPEC))  ou simplesmente (ALTLARG/COMPFOL) - se essa proporção for 0.5 a largura máxima é no centro da lâmina, se for maior que 0.5 a folha é mais obovada, se for menor que 0.5 a folha é mais ovada.</li>
          </ul>
          Para isso, você os usaria fórmulas do tipo:
          <ul>
          <li><i>Folha_Forma1</i> = ((&632&)/(&630&-&148&))</li>
          <li><i>Folha&Forma2</i> = (&148&/&630&)</li>
          <li><i>Folha&Forma3</i> = (&631&/&630&)</li>
          </ul>
        </td>
      </tr>
    </table>
  </td>
</tr>
</tbody>
</table>
</form>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>