CREATE FUNCTION pessoainfield(campo CHAR(255), valtocheck INT(10)) RETURNS INT(1)
BEGIN
DECLARE res CHAR(255) DEFAULT '';
DECLARE pess CHAR(255) DEFAULT '';
DECLARE pessid INT(10) DEFAULT 0;
DECLARE resultado INT(1) DEFAULT 0;
DECLARE lastname CHAR(255) DEFAULT '';
DECLARE ncat INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
SELECT substrCount(campo,';')+1 INTO ncat;
WHILE ncatstep <= ncat DO
SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(campo,';',ncatstep),';',-1)) INTO pess;
SET pessid = pess+0;
IF (pessid=valtocheck) THEN
	SET resultado = 1;
END IF;
SET ncatstep = ncatstep+1;
END WHILE;
RETURN resultado;
END
