CREATE FUNCTION checkiniciais(Prenome char(200),SegundoNome char(200),Sobrenome char(200)) RETURNS char(20)
BEGIN
DECLARE addcolldesc TEXT DEFAULT '';
DECLARE colname TEXT DEFAULT '';
DECLARE partn TEXT DEFAULT '';
DECLARE ncat INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
SELECT substrCount(Prenome,' ')+1 INTO ncat;
WHILE ncatstep <= ncat DO
SET colname = '';
SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(Prenome,' ',ncatstep),' ',-1)) INTO partn;
SELECT UPPER(SUBSTRING(partn,1,1)) INTO colname;
SET addcolldesc = CONCAT(addcolldesc,colname);
SET ncatstep = ncatstep+1;
END WHILE;
SET ncatstep=1;
SELECT substrCount(SegundoNome,' ')+1 INTO ncat;
WHILE ncatstep <= ncat DO
SET colname = '';
SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(SegundoNome,' ',ncatstep),' ',-1)) INTO partn;
SELECT UPPER(SUBSTRING(partn,1,1)) INTO colname;
SET addcolldesc = CONCAT(addcolldesc,colname);
SET ncatstep = ncatstep+1;
END WHILE;
SET ncatstep=1;
SELECT substrCount(Sobrenome,' ')+1 INTO ncat;
WHILE ncatstep <= ncat DO
SET colname = '';
SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(Sobrenome,' ',ncatstep),' ',-1)) INTO partn;
SELECT UPPER(SUBSTRING(partn,1,1)) INTO colname;
SET addcolldesc = CONCAT(addcolldesc,colname);
SET ncatstep = ncatstep+1;
END WHILE;
RETURN addcolldesc;
END



