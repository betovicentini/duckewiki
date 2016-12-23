CREATE FUNCTION theplantatag(plttag CHAR(20),insitex CHAR(20)) RETURNS CHAR(20) CHARACTER SET utf8 
BEGIN
DECLARE pltagtxt CHAR(20) DEFAULT '';
IF (insitex LIKE 'Insitu') THEN
	SET pltagtxt = CONCAT('JB-X-',plttag);
END IF;
IF (insitex LIKE 'Exsitu') THEN
	SET pltagtxt = CONCAT('JB-N-',plttag);
END IF;
IF (pltagtxt LIKE '')  THEN
	SET pltagtxt = plttag;
END IF;
RETURN pltagtxt;
END
