CREATE FUNCTION check_coletor_and_numcol(coletorfield char(200),numcol char(20)) RETURNS int(11)
BEGIN
DECLARE done INT DEFAULT 0;
DECLARE resultado INT DEFAULT 0;
DECLARE contador INT DEFAULT 0;
DECLARE pltid INT DEFAULT 0;
DECLARE cur1 CURSOR FOR SELECT EspecimenID FROM Especimenes JOIN Pessoas ON ColetorID=PessoaID WHERE LOWER(Abreviacao) LIKE LOWER(coletorfield) AND LOWER(Number) LIKE LOWER(numcol);
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
OPEN cur1;
loop1: LOOP
FETCH cur1 INTO pltid;
IF done=1 THEN
CLOSE cur1;
LEAVE loop1;
END IF;
SET resultado = pltid;
END LOOP loop1;
RETURN resultado;
END
