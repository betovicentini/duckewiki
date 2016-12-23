CREATE FUNCTION addcolldescr(AddColIDS varchar(100)) RETURNS VARCHAR(1000) CHARSET utf8
BEGIN
DECLARE addcolldesc  VARCHAR(1000)  DEFAULT '';
DECLARE colname  VARCHAR(500)  DEFAULT '';
DECLARE ncat INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
DECLARE pesstxt CHAR(10) DEFAULT NULL;
DECLARE pessid INT(10) DEFAULT 0;
IF (AddColIDS<>'' AND (AddColIDS IS NOT NULL)) THEN
SELECT substrCount(AddColIDS,';')+1 INTO ncat;
WHILE ncatstep <= ncat DO
SET colname = '';
SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(AddColIDS,';',ncatstep),';',-1)) INTO pesstxt;
SET pessid = pesstxt+0;
IF (pessid>0) THEN
SELECT Abreviacao INTO colname FROM Pessoas WHERE PessoaID=pessid;
IF (ncatstep=1) THEN
SET addcolldesc = colname;
ELSE
SET addcolldesc = CONCAT(addcolldesc,'; ',colname);
END IF;
SET ncatstep = ncatstep+1;
END IF;
END WHILE;
END IF;
RETURN addcolldesc;
END
