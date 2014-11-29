CREATE FUNCTION checkgazetteerchilds(gazid INT(10), fieldname CHAR(50)) RETURNS TEXT CHARSET utf8
BEGIN
DECLARE valoresres TEXT DEFAULT '';
DECLARE done INT DEFAULT 0;
DECLARE pltid INT DEFAULT 0;
DECLARE cur1 CURSOR FOR SELECT GazetteerID FROM Gazetteer WHERE ParentID=gazid;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
OPEN cur1;
loop1: LOOP
FETCH cur1 INTO pltid;
IF done=1 THEN
CLOSE cur1;
LEAVE loop1;
END IF;
IF pltid>0 THEN
SET valoresres = CONCAT(valoresres," OR ",fieldname,"=",pltid);
END IF;
END LOOP loop1;
RETURN valoresres;
END
