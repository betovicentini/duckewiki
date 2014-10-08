CREATE FUNCTION checanumericos(angu VARCHAR(200)) RETURNS FLOAT
BEGIN
DECLARE yyll CHAR(10) DEFAULT '';
DECLARE yyll2 CHAR(10) DEFAULT '';
DECLARE yyan FLOAT DEFAULT 0;
DECLARE isnn INT(10) DEFAULT 0;
DECLARE ncat INT(10) DEFAULT 0;
DECLARE ress FLOAT(4) DEFAULT NULL;
SELECT TRIM(REPLACE(TRIM(angu),',','.')) INTO yyll;
SELECT IsNumeric(yyll) INTO isnn;
IF (isnn) THEN
SELECT TRIM(yyll)+0 INTO ress;
END IF;
RETURN ress;
END
