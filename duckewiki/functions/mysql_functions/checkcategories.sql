CREATE FUNCTION checkcategories(valoresCol varchar(300),trid int(10)) RETURNS varchar(300) CHARSET utf8
BEGIN
DECLARE valoresres varchar(300)  DEFAULT '';
DECLARE colname varchar(300)  DEFAULT '';
DECLARE ncat INT(10) DEFAULT 0;
DECLARE statetrid INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
DECLARE val DOUBLE DEFAULT 0;
DECLARE erro BOOLEAN DEFAULT FALSE;
SELECT substrCount(valoresCol,';')+1 INTO ncat;
WHILE ncatstep <= ncat DO
SET colname = '';
SELECT TRIM((SUBSTRING_INDEX(SUBSTRING_INDEX(valoresCol,';',ncatstep),';',-1))) INTO colname;
SELECT TraitID INTO statetrid FROM Traits WHERE ParentID=trid AND LOWER(TraitName) LIKE LOWER(colname);
IF (statetrid>0) THEN
IF (ncatstep=1) THEN
SET valoresres = statetrid;
ELSE
SET valoresres = CONCAT(valoresres,';',statetrid);
END IF;
ELSE
SET erro = TRUE;
END IF;
SET ncatstep = ncatstep+1;
END WHILE;
IF erro THEN
RETURN 'ERRO';
ELSE
RETURN valoresres;
END IF;
END
