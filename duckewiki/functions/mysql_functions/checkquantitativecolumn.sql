CREATE FUNCTION checkquantitativecolumn(valoresCol varchar(100)) RETURNS text CHARSET utf8
BEGIN
DECLARE valoresres TEXT DEFAULT '';
DECLARE colname TEXT DEFAULT '';
DECLARE ncat INT(10) DEFAULT 0;
DECLARE isit INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
DECLARE val DOUBLE DEFAULT 0;
DECLARE erro BOOLEAN DEFAULT FALSE;
SELECT substrCount(valoresCol,';')+1 INTO ncat;
WHILE ncatstep <= ncat DO
SET colname = '';
SELECT REPLACE(TRIM((SUBSTRING_INDEX(SUBSTRING_INDEX(valoresCol,';',ncatstep),';',-1))),',','.') INTO colname;
SELECT IsNumeric(colname) INTO isit;
IF (isit=0) THEN
SET erro = TRUE;
ELSE
IF (ncatstep=1) THEN
SET valoresres = colname;
ELSE
SET valoresres = CONCAT(valoresres,'; ',colname);
END IF;
END IF;
SET ncatstep = ncatstep+1;
END WHILE;
IF erro THEN
RETURN 'ERRO';
ELSE
RETURN valoresres;
END IF;
END
