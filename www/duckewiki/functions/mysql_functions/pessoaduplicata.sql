CREATE FUNCTION pessoaduplicata(campo CHAR(255), valtocheck INT(10), newval INT(10) ) RETURNS CHAR(255) CHARSET utf8
BEGIN
DECLARE resultado CHAR(255) DEFAULT '';
DECLARE res CHAR(255) DEFAULT '';
DECLARE res1 CHAR(255) DEFAULT '';
DECLARE pess CHAR(255) DEFAULT '';
DECLARE pessid INT(10) DEFAULT 0;

DECLARE pespalavra CHAR(255) DEFAULT '';
DECLARE pespalavra2 CHAR(255) DEFAULT '';
DECLARE sobnome CHAR(255) DEFAULT '';
DECLARE lastname CHAR(255) DEFAULT '';
DECLARE ncat INT(10) DEFAULT 0;
DECLARE newcat INT(10) DEFAULT 0;
DECLARE np1 INT(10) DEFAULT 0;
DECLARE np2 INT(10) DEFAULT 0;
DECLARE nmatches INT(10) DEFAULT 0;
DECLARE matchabr INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
SELECT substrCount(campo,';')+1 INTO ncat;
WHILE ncatstep <= ncat DO
SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(campo,';',ncatstep),';',-1)) INTO pess;
SET pessid = pess+0;
IF (pessid<>valtocheck) THEN
	SET res = pessid;
ELSE 
	SET res = newval;
END IF;
IF (ncatstep=1) THEN
		SET resultado = res;
ELSE
		SET resultado = CONCAT(resultado,';',res);
END IF;
SET ncatstep = ncatstep+1;
END WHILE;
RETURN resultado;
END
