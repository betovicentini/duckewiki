<?php
ini_set('max_execution_time',400);
ini_set('max_input_time',400);
function ConectaDB($dbname)
{
	///////ALTERAR AQUI PARA SUA CONFIGURACAO
	$host = 'localhost';
	$user = 'root';
	$pws = '';
	///////////////////////////////
	
	//database conexao
	@define ('DB_USER', $user);
	@define ('DB_PASSWORD', $pws);
	@define ('DB_HOST', $host);
	@define ('DB_NAME', $dbname);
	
	$dbc =  @mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) 
	or die('Você não tem acesso. Não foi possível conectar <font color = "red">'.mysql_error().'</font>');
	(mysql_select_db(DB_NAME, $dbc)) or die( 'Precisa Logar! Você não tem acesso.<br /> 
	Sem conexão<font color ="red">'.mysql_error().'</font>');
	@mysql_set_charset('utf8',$dbc);
	return $dbc;
}
function ReturnAcessList($acclevel) {
if ($acclevel=='admin') {
$quais = array( 'inicio', 'especimen' , 'planta' ,'taxonomia', 'local' , 'habitat' , 'pessoas' ,'variaveis'  , 'variaveistable', 'formularios' ,  'editbytable', 'processos', 'definicoes'  , 'ferramentas'  ,'bibtex', 'graficos'  , 'buscas'  , 'filtros' , 'exportar' , 'importar' , 'imprimir' , 'admim' , 'teste' ,'imagens');
} else {
	if ($acclevel=='manager') {
	$quais = array( 'inicio', 'especimen' , 'planta' ,  'taxonomia', 'local' , 'habitat' , 'pessoas' , 'variaveis'  , 'formularios' ,  'editbytable' ,  'processos', 'definicoes'  , 'ferramentas'  , 'graficos'  , 'buscas'  , 'filtros' , 'exportar' , 'importar' , 'imprimir');
	} elseif (!empty($acclevel)) {
	$quais = array( 'inicio', 'especimen' , 'planta' , 'taxonomia', 'local' , 'habitat' , 'pessoas' , 'formularios' ,  'editbytable',   'processos',  'definicoes'  ,  'buscas'  , 'filtros' , 'exportar' , 'importar' , 'imprimir');
	}
}
return($quais);
}

function GetLangVar($var) {
	$dbname = $_SESSION['dbname'];
	$lang = $_SESSION['lang'];
	$conn = ConectaDB($dbname);
	$qq = "SELECT * FROM VarLang WHERE VariableName='$var'";
	$res = mysql_unbuffered_query($qq,$conn);
	$rr = @mysql_fetch_assoc($res);
	return $rr[$lang];
}

function GetLangVarSeries($var) {
	$dbname = $_SESSION['dbname'];
	$lang = $_SESSION['lang'];
	$conn = ConectaDB($dbname);
	$qq = "SELECT * FROM VarLang WHERE VariableName LIKE '%".$var."%' ORDER BY SUBSTRING(VariableName,1,5),$lang ASC";
	$res = mysql_query($qq,$conn);
	return $res;
}

function Menu($title) {
	$dbname = $_SESSION['dbname'];
	$lang = $_SESSION['lang'];
	$accesslevel = $_SESSION['accesslevel'];
	$conn = ConectaDB($dbname);
	//main menus
	//<div id=\"moonmenu\" class=\"halfmoon\">
	echo "
<ul id='qm0' class='qmmc'>";
		$qq = "SELECT * FROM Menu WHERE Link LIKE 'index.php%'";
		$res = mysql_query($qq,$conn);		
		$row = mysql_fetch_assoc($res);
		$link = $row['Link'];
		$name = $row[$lang];
		echo "
 <li><a href='".$link."'>".$name."</a></li>";
		mysql_free_result($res);
		$level = 0;
		$qq = "SELECT * FROM Menu WHERE AccessLevel LIKE '%".$accesslevel."%' AND Link NOT LIKE '%index.php%' AND Link NOT LIKE '%logout%' AND Link NOT LIKE '%login%' AND Level='".$level."' ORDER BY $lang ASC";
		$res = mysql_query($qq,$conn);
		$level++;
		while ($row = mysql_fetch_assoc($res)) {
			$parentid = $row['MenuID'];
			$link = $row['Link'];
			$name = $row[$lang];
			$qq = "SELECT * FROM Menu WHERE ParentID='".$parentid."' AND AccessLevel LIKE '%".$accesslevel."%' AND Level='".$level."' ORDER BY $lang ASC";
			//echo $qq;
			$rr = @mysql_query($qq,$conn);
			$nres = @mysql_numrows($rr);
			if (!empty($link) && $nres==0) {
				echo "
 <li><a class='qmparent' href=\"".$link."\">".$name."</a></li>";
			} else {
				if ($nres>0) {
					echo "
 <li>
   <a class='qmparent' href='javascript:void(0)'>".$name."</a>
   <ul>";
						while ($rw = mysql_fetch_assoc($rr)) {
							$prid = $rw['MenuID'];
							$lnk = $rw['Link'];
							$subnome = $rw[$lang];
							$nlev = $level+1;
							$qu = "SELECT * FROM Menu WHERE ParentID='".$prid."' AND AccessLevel LIKE '%".$accesslevel."%' AND Level='".$nlev."' ORDER BY $lang ASC";
							$rrr = @mysql_query($qu,$conn);
							$nrrr = @mysql_numrows($rrr);
							if (!empty($lnk) && $nrrr==0) {
								echo "
      <li><a class='qmparent' href=\"".$lnk."\">".$subnome."</a></li>";
							} else {
								echo "
      <li>
        <a class='qmparent' href='javascript:void(0)'>".$subnome."</a>
         <ul>";
									while ($rww = mysql_fetch_assoc($rrr)) {
											$pridw = $rww['MenuID'];
											$lnkw = $rww['Link'];
											$subnomew = $rww[$lang];
											$nlevw = $nlev+1;
											$qu = "SELECT * FROM Menu WHERE ParentID='".$pridw."' AND AccessLevel LIKE '%".$accesslevel."%' AND Level='".$nlevw."' ORDER BY $lang ASC";
											$rrre = @mysql_query($qu,$conn);
											$nrrre = @mysql_numrows($rrre);
											if (!empty($lnkw) && $nrrre==0) {
												echo "
           <li><a class='qmparent' href=\"".$lnkw."\">".$subnomew."</a></li>";
											} else {
													echo "
           <li>
             <a class='qmparent' href='javascript:void(0)'>".$subnomew."</a>
              <ul>";
				while ($rwww = mysql_fetch_assoc($rrre)) {
					$pridww = $rwww['MenuID'];
					$lnkww = $rwww['Link'];
					$subnomeww = $rwww[$lang];
					echo "
                <li><a class='qmparent' href='".$lnkww."'>".$subnomeww."</a></li>";
				}
				echo "
              </ul>
            </li>";
											}
									}
									echo "
         </ul>
       </li>";
							}
						}
						echo "
      </ul>
    </li>";
					}
				}
			}
		@mysql_free_result($res);
		@mysql_free_result($rr);
		@mysql_free_result($rrr);

		//login menu
		if (!isset($_SESSION['userid'])) {
			$qq = "SELECT * FROM Menu WHERE Link LIKE 'login%' AND AccessLevel='login'";
			$res = mysql_query($qq,$conn);
			$row = mysql_fetch_assoc($res);
			$link = $row['Link'];
			$name = $row[$lang];
		} else {
			$qq = "SELECT * FROM Menu WHERE Link LIKE '%logout%' AND AccessLevel='login'";
			$res = mysql_query($qq,$conn);
			$row = mysql_fetch_assoc($res);
			$link = $row['Link'];
			$name = $row[$lang];
		}
		echo "
  <li><a href='".$link."'>".$name."</a></li>
</ul>
";
}
?>