<?php


/********************
 *    LOGIN + CFG	*
 ********************/


/** cria uma sessão "segura" */
function sec_session_start() {
	$session_name = 'sec_session_id';   // Set a custom session name
	$secure = SECURE;
	//$secure = "";
	// This stops JavaScript being able to access the session id.
	$httponly = true;
	// Forces sessions to only use cookies.
	if (ini_set('session.use_only_cookies', 1) === FALSE) {
		header("Location: ../html/plantas/error.php?err=Could not initiate a safe session (ini_set)");
		exit();
	}
	// Gets current cookies params.
	$cookieParams = session_get_cookie_params();
	session_set_cookie_params($cookieParams["lifetime"],
		$cookieParams["path"],
		$cookieParams["domain"],
		$secure,
		$httponly);
	// Sets the session name to the one set above.
	session_name($session_name);

	session_start(); // Start the PHP session
}
/** testa password e email para fazer o login */
function login($email, $pwd, $conn) {
    // Using prepared statements means that SQL injection is not possible. 
    $q = "select id, username, pwd, salt 
        from usr where email = $1
        limit 1";
    /*$res = pg_prepare($conn,'login',$q);
    // pq não usar pg_query_params?
    if ($res) {
		pg_execute($conn,'login',[$email]); // E POR QUE NÃO SE FECHA MAIS O PHP COM ?> ? */
	
	$res = pg_query_params($conn,$q,[$email]);
	if ($res) {
        // get variables from result.
        //syslog(LOG_INFO,'chega 1');
		$row = pg_fetch_array($res,NULL,PGSQL_NUM);
		$user_id = $row[0];
		$username = $row[1];
		$db_pwd = $row[2];
		$salt = $row[3];
        // hash the password with the unique salt.
        $pwd = hash('sha512', $pwd.$salt);
        if (pg_num_rows($res) == 1) {
            // If the user exists we check if the account is locked
            // from too many login attempts 
            if (checkbrute($user_id, $conn) == true) {
                // Account is locked 
                // Send an email to user saying their account is locked - AINDA NÃO IMPLEMENTADO
                return 'brute';
            } else {
                // Check if the password in the database matches
                // the password the user submitted.
                if ($db_pwd == $pwd) { // === OU == ?
                    // Password is correct!
                    // Get the user-agent string of the user.
                    $user_browser = $_SERVER['HTTP_USER_AGENT'];
                    // XSS protection as we might print this value
                    $user_id = preg_replace("/[^0-9]+/", "", $user_id);
                    $_SESSION['user_id'] = $user_id;
                    // XSS protection as we might print this value
                    $username = preg_replace("/[^a-zA-Z0-9_\-]+/","",$username);
                    $_SESSION['username'] = $username;
                    $_SESSION['login_string'] = hash('sha512',$pwd.$user_browser);
                    // Login successful.
                    return 'ok';
                } else {
                    // Password is not correct
                    // We record this attempt in the database
                    $now = time();
					$q = "insert into login_attempts (user_id, hora) values ('$user_id','$now')";
					$res = pg_query($conn,$q); // PARAMS NÃO ERA MELHOR?
					if ($res) {
						// deu certo
					}
                    return 'pwderror';
                }
            }
        } else {
            // No user exists.
            return 'nouser';
        }
    } else {
		return 'noemail';
	}
}
/** limita tentativas de conexão a apenas 6 (seis) */
function checkbrute($user_id, $conn) {
	// Get timestamp of current time 
	$now = time();

	// All login attempts are counted from the past 2 hours. 
	$valid_attempts = $now - (2 * 60 * 60);

	$q = "select hora from login_attempts where usr = $1 and hora > '$valid_attempts' limit 6"; // BOM USAR LIMIT 6?
	// Execute the prepared query. 
	$res = pg_query_params($conn,$q,[$user_id]);
	if ($res) {
		return pg_num_rows($res) > 5; // If there have been more than 5 failed logins 
	} else {
		// algum erro
	}
}
/** retorna código de erro de conexão, ou 0 (zero) se conectou normalmente */
function login_error($conn) {
	// Check if all session variables are set 
	if (isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['login_string'])) {
		$user_id = $_SESSION['user_id'];
		$login_string = $_SESSION['login_string'];
		$username = $_SESSION['username'];

		// Get the user-agent string of the user
		$user_browser = $_SERVER['HTTP_USER_AGENT'];

		$q = "select pwd from usr where id = $1 limit 1";
		// Execute the query
		$res = pg_query_params($conn,$q,[$user_id]);
		if ($res) {
			if (pg_num_rows($res) == 1) {
				// If the user exists get variables from result.
				$row = pg_fetch_array($res,NULL,PGSQL_NUM);
				$pwd = $row[0];
				$login_check = hash('sha512', $pwd.$user_browser);
				if ($login_check == $login_string) {
					return 0; // Logged In!!!!
				} else {
					return 1; // Not logged in
				}
			} else {
				return 2; // Not logged in
			}
		} else {
			return 3; // Not logged in
		}
	} else {
		if (!isset($_SESSION['user_id'])) {
			return 4; // Not logged in
		} else
		if (!isset($_SESSION['username'])) {
			return 5; // Not logged in
		} else
		if (!isset($_SESSION['login_string'])) {
			return 6; // Not logged in
		}
	}
}

/** tenta ler as configurações do banco de dados */
function readConfig() {
	if (isset($_SESSION['user_id'])) {
		global $conn;
		$q = "select * from cfg where usr = $1";
		$res = pg_query_params($conn,$q,[$_SESSION['user_id']]);
		$erro = false;
		if ($res) {
			if ($row = pg_fetch_array($res,null,PGSQL_ASSOC)) {
				$_SESSION['cfg.expfs'] = $row['expfs'];				// expandir fieldsets?
				$_SESSION['cfg.frmdest'] = $row['frmdest'];			// destino dos formulários (self|window|tab)
				if (!empty($row['corschema'])) {					// colors
					$q = "select * from cor where id = $1";
					$res = pg_query_params($conn,$q,[$row['corschema']]);
					if ($res) {
						if ($row = pg_fetch_array($res,null,PGSQL_ASSOC)) { // colors from schema
							$_SESSION['cfg.corbg'] = ($row['bg'] == '') ? '90B090' : $row['bg'];
							$_SESSION['cfg.corbut'] = ($row['but'] == '') ? 'B0DAB0' : $row['but'];
							$_SESSION['cfg.cortit'] = ($row['tit'] == '') ? 'A07060' : $row['tit'];
							$_SESSION['cfg.corbutbar'] = ($row['butbar'] == '') ? 'C0C0A0' : $row['butbar'];
							$_SESSION['cfg.cortbh'] = ($row['tbh'] == '') ? 'AACCAA' : $row['tbh'];
							$_SESSION['cfg.cortbo'] = ($row['tbo'] == '') ? 'FFFFFF' : $row['tbo'];
							$_SESSION['cfg.cortbe'] = ($row['tbe'] == '') ? 'CCFFCC' : $row['tbe'];
							$_SESSION['cfg.corfrm'] = ($row['frm'] == '') ? 'DCC964' : $row['frm'];
							return true;
						} else {
							$erro = true;
						}
					} else {
						$erro = true;
					}
				} else { // colors from user config
					$_SESSION['cfg.corbg'] = ($row['corbg'] == '') ? '90B090' : $row['corbg'];
					$_SESSION['cfg.corbut'] = ($row['corbut'] == '') ? 'B0DAB0' : $row['corbut'];
					$_SESSION['cfg.cortit'] = ($row['cortit'] == '') ? 'A07060' : $row['cortit'];
					$_SESSION['cfg.corbutbar'] = ($row['corbutbar'] == '') ? 'C0C0A0' : $row['corbutbar'];
					$_SESSION['cfg.cortbh'] = ($row['cortbh'] == '') ? 'AACCAA' : $row['cortbh'];
					$_SESSION['cfg.cortbo'] = ($row['cortbo'] == '') ? 'FFFFFF' : $row['cortbo'];
					$_SESSION['cfg.cortbe'] = ($row['cortbe'] == '') ? 'CCFFCC' : $row['cortbe'];
					$_SESSION['cfg.corfrm'] = ($row['corfrm'] == '') ? 'DCC964' : $row['corfrm'];
					return true;
				}
			} else {
				$erro = true;
			}
		} else {
			$erro = true;
		}
		if ($erro) { // no database, all config from here:
			$_SESSION['cfg.expfs'] = 'N';
			$_SESSION['cfg.frmdest'] = 'S';
			$_SESSION['cfg.corbg'] = '90B090';
			$_SESSION['cfg.corbut'] = 'B0DAB0';
			$_SESSION['cfg.cortit'] = 'A07060';
			$_SESSION['cfg.corbutbar'] = 'C0C0A0';
			$_SESSION['cfg.cortbh'] = 'AACCAA';
			$_SESSION['cfg.cortbo'] = 'FFFFFF';
			$_SESSION['cfg.cortbe'] = 'CCFFCC';
			$_SESSION['cfg.corfrm'] = 'DCC964';
			return false;
		}
	}
}
/** cria um <style> css a partir do que leu em cfg */
function pullCfg() {
	if (!empty($_SESSION['cfg.corbg'])) {
		echo "<style>
		body {
			background-color: #".$_SESSION['cfg.corbg'].";
		}
		.mnuTable {
			background-color: #".$_SESSION['cfg.corbg'].";
		}
		tr:nth-child(odd) {
			background-color: #".$_SESSION['cfg.cortbo'].";
		}
		tr:nth-child(even) {
			background-color: #".$_SESSION['cfg.cortbe'].";
		}
		button {
			background: #".$_SESSION['cfg.corbut'].";
		}
		</style>";
	}
}


/************
 *   TELA	*
 ************/


/** lê da sessão php as strings no idioma atual */
function txt($line) {
	if (isset($_SESSION[$line])) {
		return $_SESSION[$line];
	} else {
		return "($line)";
	}
}
/** adiciona algumas tags de rótulo ao valor retornado em txt() */
function dtlab($who,$obrig=false) {
	$tip = txt("$who.tip");
	if (substr($tip,0,1) != '(') { // se tem conteúdo
		//$tip = " <img src='icon/question.png' title='$tip'>";
		$tip = " <img src='icon/question.png'><div class='tooltip'>$tip</div>";
	} else {
		$tip = '';
	}
	if ($obrig) {
		return "<dt><label>".txt($who)."<span style='color:red'>*</span></label>$tip</dt>";
	} else {
		return "<dt><label>".txt($who)."</label>$tip</dt>";
	}
}
/** desenha uma tabela para o usuário digitar coordenadas (usado em addPl e addEsp) */
function echoCoords() {
	echo "<div id='divCoordLocA'><dl><dt><a href='javascript:popCoords(1,\"divCoordLoc\")'>".txt('coordloc')."</a></dt></dl></div>
	<div id='divCoordLocB' style='display:none'>
	<dl>
	<dt><label><a href='javascript:popCoords(0,\"divCoordLoc\")'>".txt('coordloc')."</a></label></dt>
	<dd>
		<table>
	<tr>
		<td><label>Lat</label></td>
		<td colspan=3 style='text-align:left'>
		<input name='txtLatG' type='text' class='short' oninput='store(this)' />°
		<input name='txtLatM' type='text' value=0 class='short' oninput='store(this)' />'
		<input name='txtLatS' type='text' value=0 class='short' oninput='store(this)' />\"
		<input type='radio' id='radLatHN' name='radLatH' value='N' onclick='store(this)'>N
		<input type='radio' id='radLatHS' name='radLatH' value='S' onclick='store(this)' checked>S</td>
	</tr>
	<tr>
		<td><label>Lon</label></td>
		<td colspan=3 style='text-align:left'>
		<input name='txtLonG' type='text' class='short' oninput='store(this)' />°
		<input name='txtLonM' type='text' value=0 class='short' oninput='store(this)' />'
		<input name='txtLonS' type='text' value=0 class='short' oninput='store(this)' />\"
		<input type='radio' id='radLonHE' name='radLonH' value='E' onclick='store(this)'>E
		<input type='radio' id='radLonHW' name='radLonH' value='W' onclick='store(this)' checked>W</td>
	</tr>
	<tr>
	<td colspan=4 style='text-align:left'>
		<label>UTM N </label><input name='txtUTMn' type='text' value='' class='short' oninput='store(this)' />
		<label>UTM E </label><input name='txtUTMe' type='text' value='' class='short' oninput='store(this)' />
		<label>UTM Z </label><input name='txtUTMz' type='text' value='' class='short' oninput='store(this)' /></td>
	</tr>
	<tr>
	<td colspan=4 style='text-align:right'>
		<label>Alt (m) </label><input name='txtAlt' type='text' value='' class='short' oninput='store(this)' />
		<label>Datum </label><input name='txtDatum' type='text' value='' class='short' oninput='store(this)' /></td>
	</tr>
		</table>
	</dd>
	</dl>";
	echo "</div>";
}
/** botões HTML: salvar, reset, cancelar, etc - em várias janelas (addX.php e outras) */
function echoButtons() {
	echo "<div class='wrapper'>
<button id='btnSave' type='button' onclick='btnSaveClick(this)'>".txt('addSave')."</button>
<button id='btnReset' type='button' onclick='btnResetClick(this)'>".txt('addClear')."</button>
<button id='btnCancel' type='button' onclick='btnCancelClick()'>".txt('addCancel')."</button>
<button id='btnStore' type='button' onclick='seeStore()'>".txt('addLookSt')."</button>
<button id='btnClear' type='button' onclick='clearStore()'>".txt('addClearSt')."</button>
</div>";
}
function echoHeader() {
	global $tabela;
	global $divRes;
	global $title;
	$frmName = "frm".ucfirst($tabela);
	echo "<button type='button' onclick='micClick()' title='Comunicar bug, dúvida ou sugestão'><img src='icon/mic.png'></button>
	<h1 style='text-align:center'>$title</h1>
	$divRes
	<form id='$frmName' autocomplete='off' method='post' action=''>";
}
/*
	<button type='button' onclick='micClick()' title='Comunicar bug, dúvida ou sugestão'><img src='icon/mic.png'></button>
	<h1 style='text-align:center'><?=$title?></h1>
	<?= $divRes ?> 
	<form id='frmEsp' autocomplete='off' method='post' action=''>
*/

/** a partir do id de det, retorna o nome do táxon (Gen sp. ou Fam...) */
function getTaxFromDet($det) {
	$q = "select gettax(t.id)
	from det d
	left join tax t on d.tax = t.id
	where d.id = $1";
	global $conn;
	$res = pg_query_params($conn,$q,[$det]);
	if ($res) {
		if ($row = pg_fetch_array($res,NULL,PGSQL_NUM)) {
			return $row[0];
		}
	}
}


/****************
 *   POST/SQL	*
 ****************/


/** começa a montar a query, e avisa se algum campo foi modificado */
function algumaMudou(&$q) {
	global $arrCol;
	global $arrPar; // dados do post
	$i = 0;
	$c = 0;
	foreach ($arrCol as $coluna) {
		global $$coluna; // dados do BD
		//echo "<BR>`$coluna`: ".$$coluna." ($arrPar[$i])";
		if ($coluna != 'addby' && $coluna != 'adddate') {
			if ($$coluna != $arrPar[$i]) { // diferente -> inclui no update
				//echo " [$arrPar[$i]]";
				$c++;
				$q.="$coluna,";
			}
		} else {
			$q.="$coluna,";
		}
		$i++;
	}
	return ($c > 0);
}
/** termina de montar a query e retorna 2 arrays, com colunas e dados para o dwh (Data WareHouse) */
function montaQuery(&$q) {
	$q = substr($q,0,-1).") = (";
	$i = 0;
	global $arrCol; // $arrCol[] ?
	global $arrPar; // $arrPar[] ?
	$colDWH = []; // colunas para Data WareHouse
	$valDWH = []; // valores para Data WareHouse
	foreach ($arrCol as $coluna) {
		global $$coluna;
		if ($coluna != 'addby' && $coluna != 'adddate') {
			if ($$coluna != $arrPar[$i]) { // diferente -> inclui no update
				$colDWH[] = $coluna;
				$valDWH[] = $$coluna;
				if ($arrPar[$i] == '') {
					$q.="NULL,";
				} else {
					$q.="'$arrPar[$i]',";
				}
			}
		} else {
			$q.="'$arrPar[$i]',";
			$colDWH[] = $coluna; // addby & adddate antigos
			$valDWH[] = $$coluna;
		}
		$i++;
	}
	$q = substr($q,0,-1).") where id = $1";
	//echo "<BR><BR>$q<BR><BR>";
	return [$colDWH,$valDWH]; // array com 2 arrays
}
/** vê se os dados do post já existiam na tabela */
function registroExiste($tab) {
	global $arrCol;
	global $arrPar;
	global $conn;
	$q = "select exists(select 1 from $tab where (addby=$1 or addby<>$1) and (adddate=$2 or adddate<>$2) and ";
	for ($i=2; $i<sizeof($arrCol); $i++) {
		$q.="($arrCol[$i]=$".($i+1)." or $".($i+1)." is null and $arrCol[$i] is null) and ";
	}
	$q = substr($q,0,-5).")";
	echo "<BR><BR>$q<BR><BR>arrPar: ";
	print_r($arrPar);
	echo "<BR><BR>arrCol: ";
	print_r($arrCol);
	$res = pg_query_params($conn,$q,$arrPar);
	return (pg_fetch_array($res,NULL,PGSQL_NUM)[0]);
}
/** tenta inserir dados do post -- chamada apenas em insereUm; juntar? */
function tentaInserir($tab,&$q) {
	global $cols;
	$q = "insert into $tab ($cols) values (";
	global $arrPar;
	for ($i=1; $i<count($arrPar)+1; $i++) {
		$q.="$$i,";
	}
	$q = substr($q,0,-1).") returning id";
	echo "<BR><BR>SQL: $q<BR><BR>";
	global $conn;
	return (pg_query_params($conn,$q,$arrPar));
}
/** insere um item de cada vez -- Trazer versão múltipla de addEsp.php e addPl.php pra cá ?? */
function insereUm($tabela,$close,&$divRes,&$body,$texto='') {
	global $conn;
	global $arrPar;
	global $post;
	global $v1;
	global $v2;
	global $txtfrmdia;
	global $selfrmmes;
	global $txtfrmano;
	$fromfield = getGet('from');
	$datafrm = "$txtfrmdia/$selfrmmes/$txtfrmano";
	$res = tentaInserir($tabela,$q);
	echo "$q<BR><BR>";
	if ($res) {
		$divRes = "<div id='divSucesso' style='background-color:#00FF00'>Registro inserido com sucesso!</div>";
		$newID = pg_fetch_array($res,NULL,PGSQL_NUM)[0];
		echo "newID = $newID<BR><BR>";
		if (insereSubTabelas(get('hidmmData'),$newID,$divRes1)) {
			$divRes.=$divRes1;
			// depois inserir também variáveis de formulário, se houver
			foreach ($post as $key => $value) {
				if (is_array($value)) {
					$value = implode(';',$value);
					$coluna = 'valt';
				} else {
					$coluna = 'val';
				}
				echo "[$key: $value]<BR>";
				$inputTipo = substr($key,0,3);
				$key = substr($key,3);
				if (is_numeric($key) && $value != '' && $inputTipo != 'und') {
					//$q = "insert into var (esp,key,val,addby,adddate) values ($edit,$key,$value,$v1,$v2);";
					$q = "insert into var ($tabela,key,$coluna,datavar,addby,adddate) values ($1,$2,$3,$4,$5,$6)";
					$res = pg_query_params($conn,$q,[$newID,$key,$value,$datafrm,$v1,$v2]);
					if ($res) {
						echo "(key)$key: (value)$value OK<BR>";
					} else {
						echo "(key)$key: (value)$value ERRO!<BR>";
					}
				}
			}
			/*if ($close) { // só fecha se não der erro nas sub-tabelas
				if (!empty($texto)) {
					//$body = "<body onload='fechaLogo($newID,\"$tabela\",\"$texto\")'>";
					$body = "<body onload='fechaLogo($newID,\"$fromfield\",\"$texto\")'>";
				} else {
					//$body = "<body onload='fechaLogo($newID,\"$tabela\")'>";
					$body = "<body onload='fechaLogo($newID,\"$fromfield\")'>";
				}
			}*/
		}
	} else {
		pg_send_query_params($conn,$q,$arrPar);
		$res = pg_get_result($conn);
		$resErr = pg_result_error($res);
		$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Erro ao inserir registro: $resErr</div>";
	}
}
/** tenta inserir dados múltiplos por um único post -- chamada apenas em insereMuitos (ainda inexistente); juntar? */
function tentaInserirM($tab,$min,$max,&$q) {
	global $cols;
	global $arrPar;
	$q .= "insert into $tab ($cols) values ";
	for ($k=$min; $k<=$max; $k++) {
		$q .= '(';
		for ($i=1; $i<count($arrPar)+1; $i++) {
			if ($i == 4) {
				$q.="$k,";
			} else {
				$q.="$$i,";
			}
		}
		$q = substr($q,0,-1)."),\n"; // returning id
	}
	$q = substr($q,0,-2).");"; // returning id
	//$q .= "select curval('$tab"."_id_seq');";
	echo "<BR><BR>SQL-múltiplos: $q<BR><BR>";
	global $conn;
	return (pg_query_params($conn,$q,$arrPar));
}
/** lê linha do banco de dados e mantém em variáveis PHP (nomes idênticos às colunas) */
function updateRow($tab,$val) {
	global $conn;
	//global $edit;
	global $row; // precisa ??
	$q = "select * from $tab where id = $1";
	//echo $q;
	$res = pg_query_params($conn,$q,[$val]);
	if ($res) {
		$row = pg_fetch_array($res,NULL,PGSQL_ASSOC); // pega os valores antigos daquele id
		foreach ($row as $key => $value) {
			global $$key;
			$$key = $value;
			//echo "$key: $value; ";
		}
	}
}
/** esvazia as variáveis PHP (com nomes idênticos às colunas) */
function emptyRow($tab) {
	global $conn;
	global $edit;
	global $row;
	$q = "select * from $tab limit 1";
	$res = pg_query($conn,$q);
	if ($res) {
		$row = pg_fetch_array($res,NULL,PGSQL_ASSOC); // pega os valores antigos daquele id
		foreach ($row as $key => $value) {
			global $$key;
			$$key = '';
		}
	}
}
/** quando editar ou excluir, salva dado anterior na tabela dwh (Data WareHouse) */
function add2dwh($arr2,&$divRes) {
	global $conn;
	global $tabela;
	global $q; // só para aparecer na div, talvez seja removido depois
	$colDWH = $arr2[0];
	$valDWH = $arr2[1];
	$addbyDWH = $valDWH[0];
	$adddateDWH = $valDWH[1];
	$qDWH = "insert into dwh (tab,col,val,addby,adddate) values ";
	for ($iDWH=2; $iDWH<count($colDWH); $iDWH++) {
		if ($valDWH[$iDWH] == '') {
			$qDWH.="('$tabela','$colDWH[$iDWH]',NULL,'$addbyDWH','$adddateDWH'),";
		} else {
			$qDWH.="('$tabela','$colDWH[$iDWH]','$valDWH[$iDWH]','$addbyDWH','$adddateDWH'),";
		}
	}
	$qDWH = substr($qDWH,0,-1).";";
	$resDWH = pg_query($conn,$qDWH);
	if ($resDWH) {
		$divRes = "<div id='divSucesso' style='background-color:#00FF00'>Registro atualizado com sucesso (e backup feito)!<BR>$q<BR>$qDWH</div>";
	} else {
		$divRes = "<div id='divSucesso' style='background-color:#00FF00'>Registro atualizado com sucesso (mas erro no backup)!<BR>$q<BR>$qDWH</div>";
	}
}
/** insere os dados das tabelas auxiliares (muitos-pra-muitos <- build_mm.php) */
function insereSubTabelas($hidData,$edit,&$divRes) {
	if (empty($hidData)) {
		return true;
	}
	global $conn;
	//global $edit;
	global $tabela;
	global $v1;
	global $v2;
	$MMs = explode('|',$hidData);
	$erro = false;
	foreach ($MMs as $MM) {
		echo "MM: $MM<BR><BR>";
		$M = explode(';',$MM);
		$tabelaAux = $M[0];
		$campoCompara = $M[1];
		$campoOutros = $M[2];
		$campoOrder = $M[3];
		$mmStd = "mmVal".$M[4]."Std";
		if (count($M) > 5) {
			$campoOutros .= ",$M[5]";
		}
		// insere os novos
		$mmValStd = get($mmStd);
		echo "novos: [$mmValStd]<BR>";
		if ($mmValStd != '') {
			$itens = explode(';',$mmValStd);
			$q1 = "insert into $tabelaAux (addby,adddate,$campoCompara,$campoOutros) values ";
			$ordem = 1;
			foreach ($itens as $item) {
				$pos = strpos($item,'号');
				if ($pos > 0) {
					$item = substr($item,0,$pos).",'".mb_substr($item,$pos+1,null,'UTF-8')."'";
				}
				if ($campoOrder != '') {
					$q1.="($v1,'$v2',$edit,$item,$ordem),";
				} else {
					$q1.="($v1,'$v2',$edit,$item),";
				}
				$ordem++;
			}
			$q1 = substr($q1,0,-1).";";
			echo "q1: $q1<BR><BR>";
			$res1 = pg_query($conn,$q1);
			if ($res1) {
				$divRes = "<div id='divSucesso' style='background-color:#00FF00'>Registro alterado com sucesso (e backup feito)!</div>";
			} else {
				// erro ao inserir itens
				$erro = true;
				pg_send_query($conn,$q1);
				$res1 = pg_get_result($conn);
				$resErr = pg_result_error($res1);
				$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Erro ao reinserir subitens ($q1): $resErr</div>";
			}
		} else {
			$divRes = "<div id='divSucesso' style='background-color:#00FF00'>Registro alterado com sucesso (e backup feito)!</div>";
		}
	}
	return !$erro;
}
/** atualiza dados das tabelas auxiliares (muitos-pra-muitos <- build_mm.php)
tabL (Left) é a tabela sendo editada (janela addX.php aberta)
tabR (Right) é a tabela que fornece os dados que enchem o componente build_mm (via getLike.php)
*/
function atualizaSubTabela($M,&$divRes) {
	global $conn;
	global $edit;
	global $tabela;
	global $v1;
	global $v2;
	/*echo "<BR><BR>M: ";
	print_r($M);
	echo "<BR><BR>";*/
	$igual = true;
	$tabR = $M[0]; //$tabelaAux = $M[0];
	$tabLid = $M[1]; //$campoCompara = $M[1];
	$tabRid = $M[2]; //$campoOutros = $M[2];
	$campoOrder = $M[3];
	$mmStd = "mmVal".$M[4]."Std"; // nome do hidden com os valores
	if (count($M) > 5) {
		$qSub = "select $tabRid||'号'||$M[5] from $tabR where $tabLid = $1";
		//$tabRid = $tabRid."".$M[5]; // M[5] = $mmExtraField
		//$tabRid .= ",$M[5]";
		$extras = ",$M[5]";
	} else {
		$qSub = "select $tabRid from $tabR where $tabLid = $1";
		$extras = '';
	}
	
	if ($campoOrder) {
		$qSub.=" order by $campoOrder";
	}
	echo "<BR>qSub = $qSub<BR>";
	$resSub = pg_query_params($conn,$qSub,[$edit]);
	// primeiro vê se houve alguma mudança nas subtabelas
	if ($resSub) {
		$itens = explode(';',get($mmStd)); // $mmStd vazio retorna array com 1...
		if (count($itens) == 1 && $itens[0] == '') { // ...nesse caso retornar array vazio
			$itens = [];
		}
		echo "<BR><BR>";
		print_r($itens);
		$i = 0;
		while ($rowSub = pg_fetch_array($resSub,NULL,PGSQL_NUM)) {
			//echo "$rowSub[0]<BR>";
			// se >= algum elemento foi apagado || se != algum foi trocado (de posição ou substituído)
			if ($i >= count($itens) || $rowSub[0] != $itens[$i]) {
				if ($i < count($itens)) {
					echo "-- while ($i;".count($itens).";$rowSub[0];$itens[$i])<BR><BR>";
				} else {
					echo "-- while ($i;".count($itens).";$rowSub[0];itens_ESTOUROU)<BR><BR>";
				}
				$igual = false;
				break;
			}
			$i++;
		}
		if ($i < count($itens)) { // algum elemento foi adicionado
			echo "-- if (antes tinha $i; agora tem ".count($itens).": [".get($mmStd)."])<BR><BR>";
			$igual = false;
		}
	} else {
		echo "Erro ao conferir sub-tabelas<BR>";
	}
	if ($igual) {
		echo "<strong>Sub-tabelas ($tabR) NÃO mudaram</strong><BR><BR>";
	} else { // algo mudou nas sub-tabelas
		echo "<strong>Sub-tabelas ($tabR) mudaram!</strong><BR><BR>";
		// mais fácil apagar tudo e inserir de novo
		// primeiro faz o backup
		echo "tabRid: $tabRid; tabR: $tabR; tabLid: $tabLid; campoOrder: $campoOrder; extras: $extras<BR>";
		$qSub = "select addby,adddate,$tabRid from $tabR where $tabLid = $1";
		if ($campoOrder) {
			$qSub.=" order by $campoOrder";
		}
		echo "qSub: $qSub<BR>";
		$resSub = pg_query_params($conn,$qSub,[$edit]);
		if ($resSub) {
			$qDWH = "insert into dwh (tab,col,val,addby,adddate) values ";
			$outrosCampos = explode(',',$tabRid);
			// ERRADO, deve salvar separado tabRid, ordem e extras !!!
			$linhas = 0;
			while ($rowSub = pg_fetch_array($resSub,NULL,PGSQL_ASSOC)) {
				$linhas++;
				foreach($outrosCampos as $campo) {
					$addby = "'$rowSub[addby]'";
					if ($addby = "''") {
						$addby = 'null';
					}
					$adddate = "'$rowSub[adddate]'";
					if ($adddate = "''") {
						$adddate = 'null';
					}
					$qDWH.="('$tabR','$campo','$rowSub[$campo]',$addby,$adddate),";
				}
			}
			if ($linhas > 0) { // só faz backup se tinha alguma linha para ser salva
				$qDWH = substr($qDWH,0,-1).";";
				echo "qDWH: $qDWH<BR>";
				$resDWH = pg_query($conn,$qDWH);
			} else {
				$resDWH = 1; // para entrar no próximo if
			}
			if ($resDWH) { // sucesso ao fazer o backup
				// apaga tudo
				$qDel = "delete from $tabR where $tabLid = $1";
				echo "qDel: $qDel<BR>";
				$resDel = pg_query_params($conn,$qDel,[$edit]);
				if ($resDel) {
					// apagou com sucesso, agora insere os novos
					$mmValStd = get($mmStd);
					echo "novos: [$mmValStd]<BR>";
					if ($mmValStd != '') {
						$itens = explode(';',$mmValStd);
						/*$espherbtomb = '';
						if ($tabR == 'espherb') {
							$espherbtomb = ',tomb';
						}*/
						if ($campoOrder != '') {
							$ordem = ",$campoOrder";
						} else {
							$ordem = '';
						}
						// $ordem e $extras são opcionais
						$q1 = "insert into $tabR (addby,adddate,$tabLid,$tabRid".$ordem.$extras.") values ";
						if ($campoOrder != '') {
							$ordem = 1;
						} else {
							$ordem = '';
						}
						foreach ($itens as $item) {
							$pos = strpos($item,'号');
							if ($ordem != '') {
								$ordemVirg = ",$ordem";
							} else {
								$ordemVirg = '';
							}
							if ($pos > 0) {
								$item = substr($item,0,$pos)."$ordemVirg,'".mb_substr($item,$pos+1,null,'UTF-8')."'";
							} else {
								if ($extras != '') {
									$item = $item.$ordemVirg.",''";
								} else {
									$item = $item.$ordemVirg;
								}
							}
							$q1.="($v1,'$v2',$edit,$item),";
							if ($campoOrder != '') {
								$ordem++;
							}
						}
						$q1 = substr($q1,0,-1).";";
						echo "q1: $q1<BR><BR>";
						$res1 = pg_query($conn,$q1);
						if ($res1) {
							$divRes = "<div id='divSucesso' style='background-color:#00FF00'>Registro alterado com sucesso (e backup feito)!</div>";
						} else {
							// erro ao inserir itens
							pg_send_query($conn,$q1);
							$res1 = pg_get_result($conn);
							$resErr = pg_result_error($res1);
							$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Erro ao reinserir subitens ($q1): $resErr</div>";
						}
					} else {
						$divRes = "<div id='divSucesso' style='background-color:#00FF00'>Registro alterado com sucesso (e backup feito)!</div>";
					}
				} else {
					// erro ao apagar os subitens
					pg_send_query($conn,$qDel);
					$res1 = pg_get_result($conn);
					$resErr = pg_result_error($res1);
					$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Erro ao apagar subitens antigos ($qDel): $resErr</div>";
				}
			} else {
				// falha ao fazer o backup
				pg_send_query($conn,$qDWH);
				$res1 = pg_get_result($conn);
				$resErr = pg_result_error($res1);
				$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Erro ao fazer backup dos subitens ($qDWH): $resErr</div>";
			}
		} else {
			// erro ao selecionar os itens que deveriam ser apagados
			pg_send_query($conn,$qSub);
			$res1 = pg_get_result($conn);
			$resErr = pg_result_error($res1);
			$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Erro ao selecionar subitens ($qSub): $resErr</div>";
		}
	}
	return !$igual;
}
/** usada apenas em showVar.php */
function getMultiVal($val) {
	if (strpos($val,';') !== false) {
		$vals = explode(';',$val);
	} else {
		$vals = [$val];
	}
	$val = implode(',',$vals);
	$q = "select valname from val where id in ($val) order by valname";
	global $conn;
	$res = pg_query($conn,$q);
	if ($res) {
		$texto = '';
		while ($row = pg_fetch_array($res,NULL,PGSQL_NUM)) {
			$texto .= "$row[0], ";
		}
		$texto = substr($texto,0,-2);
		return $texto;
	} else {
		return "ERRO!";
	}
}
/** usada aqui -> showVarId.php */
function getVarColRead($row) {
	//$row deve ter os itens 'tipo', 'multiselect', 'val', 'valname', 'valf' e 'valt'
	$tipo = $row['tipo'];
	$multi = $row['multiselect'] == 'S';
	$val = $row['val'];
	$valf = $row['valf'];
	$valt = $row['valt'];
	$valname = $row['valname'];
	$texto = '';
	$col = '';
	if ($tipo == 2) {
		if (!$multi) {
			$col = 'valname';
		} else {
			if ($valt != '') {
				$col = 'valt';
				$texto = getMultiVal($row['valt']);
			} else {
				$col = 'valname';
			}
		}
	} else
	if ($tipo == 3) {
		if ($valt != '') {
			$col = 'valt';
			$texto = getMultiVal($row['valt']);
		} else {
			$col = 'val';
		}
	} else
	if ($tipo == 4) {
		if (!$multi) {
			if ($valf != '') {
				$col = 'valf';
			} else
			if ($valt != '') {
				$col = 'valt';
			} else {
				$col = 'val';
			}
		} else {
			$col = 'valt'; // apenas para key = 1358
		}
	} else
	if ($tipo == 5) {
		$col = 'valt';
	}
	if ($texto == '') {
		$texto = $row[$col];
	}
	return $texto;
}
/** usada em showVarId.php */
function getVarColWrite($row) {
	//$row deve ter os itens 'tipo', 'multiselect', 'val', 'valname', 'valf' e 'valt'
	$tipo = $row['tipo'];
	$multi = $row['multiselect'] == 'S';
	$val = $row['val'];
	$valf = $row['valf'];
	$valt = $row['valt'];
	$valname = $row['valname'];
	$col = '';
	if ($tipo == 2) {
		if (!$multi) {
			$col = 'val';
		} else {
			$col = 'valt';
		}
	} else
	if ($tipo == 3) {
		$col = 'valt';
	} else
	if ($tipo == 4) {
		if (!$multi) {
			if ($valf != '') {
				$col = 'valf';
			} else
			if ($valt != '') {
				$col = 'valt';
			} else {
				$col = 'val';
			}
		} else {
			$col = 'valt';
		}
	} else
	if ($tipo == 5) {
		$col = 'valt';
	}
	return $col;
}
/** usada em showVarId.php */
function getVar($id) {
	// retorna o texto apropriado para aquela variável
	$q = "select v.id,v.val,l.valname,v.valf,v.valt,v.key,k.tipo,k.multiselect,k.unit u1,v.unit u2
	from var v
	left join key k on k.id = v.key
	left join val l on l.id = v.val
	where v.id = $1";
	global $conn;
	$res = pg_query_params($conn,$q,[$id]);
	if ($res) {
		if ($row = pg_fetch_array($res,NULL,PGSQL_ASSOC)) {
			return getVarColRead($row); // texto
		}
	}
}
/** usada em getTable, markTable e saveFilter.php, lê usr/usr_id/mark/*.txt -> linhas da tabela * selecionadas */
function readMarks($table) {
	if (file_exists('usr/'.$_SESSION['user_id']."/mark/$table.txt")) {
		$marks = file('usr/'.$_SESSION['user_id']."/mark/$table.txt");
		if (!empty($marks)) {
			$marks = explode(',',$marks[0]);
		} else {
			$marks = [];
		}
	} else {
		$marks = [];
	}
	return $marks;
}
/** limpeza de variáveis para post -- NECESSÁRIO? USANDO QUERIES PARAMETRIZADAS pg_query_params() */
function limpa($p) {
	/* procurar por: 
	 * cláusulas SQL
	 * apóstrofes '
	 * aspas "
	 * <script 
	 */
	return($p);
}
// array copiado sem slice no PHP? Ou só a referência?
$post = limpa($_POST);
$get = limpa($_GET);
/** pega uma variável $_POST, admitindo um valor default */
function getPost($w,$default=null) {
	global $post;
	if (!empty($post[$w])) {
		$res = $post[$w];
	} else {
		$res = $default;
	}
	return($res);
}
/** pega uma variável $_GET, admitindo um valor default */
function getGet($w,$default=null) {
	global $get;
	if (!empty($get[$w])) {
		$res = $get[$w];
	} else {
		$res = $default;
	}
	return($res);
}
/** tentativa de um get universal, para ser usado no lugar de getGet/getPost */
function get($k) {
	// à medida que são digitadas, variáveis vão sendo salvas no cliente/javascript -> localStorage
	// se apertar F5 ou der boot, deve poder recuperar do disco
	// ...
	// após a primeira tentativa de envio, variáveis passam para $_POST
	// variáveis em $_POST devem ser inseridas, e ainda continuar na tela
	// caso esteja editando, variáveis estarão em $row
	global $row;
	global $post;
	if (!empty($post[$k])) {
		//echo "[$k] not empty<BR>";
		return($post[$k]);
	} else
	//if (!empty($row[$k])) { // se for edit
	if (!empty($edit)) { // se for edit
		//echo "[$k] editing<BR>";
		return($row[$k]); // tem que ver a ordem na qual procurar: POST, GET, row?
	} else {
		//echo "[$k] empty<BR>";
		return null;
	}
}
