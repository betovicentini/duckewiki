CREATE FUNCTION checktaxa(fam char(100), gen char(100), spp char(100), infspp char(100)) RETURNS text CHARSET utf8
BEGIN
DECLARE contador INT DEFAULT 0;
DECLARE contador2 INT DEFAULT 0;
DECLARE famid INT DEFAULT 0;
DECLARE famid2 INT DEFAULT 0;
DECLARE genid INT DEFAULT 0;
DECLARE sppid INT DEFAULT 0;
DECLARE infspid INT DEFAULT 0;
DECLARE done INT DEFAULT 0;
DECLARE done2 INT DEFAULT 0;
DECLARE done3 INT DEFAULT 0;
DECLARE done4 INT DEFAULT 0;
DECLARE resultado TEXT DEFAULT '';
DECLARE toprint TEXT DEFAULT '';
DECLARE cur1 CURSOR FOR SELECT FamiliaID FROM Tax_Familias WHERE Familia LIKE fam;
DECLARE cur2 CURSOR FOR SELECT GeneroID,FamiliaID FROM Tax_Generos WHERE Genero LIKE gen;
DECLARE cur3 CURSOR FOR SELECT EspecieID FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) WHERE GeneroID=genid AND Especie LIKE spp;
DECLARE cur4 CURSOR FOR SELECT InfraEspecieID FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) WHERE GeneroID=genid AND Especieid=sppid AND InfraEspecie LIKE infspp;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
IF fam<>'' THEN
SET done = 0;
OPEN cur1;
loop1: LOOP
FETCH cur1 INTO famid;
IF done=1 THEN
CLOSE cur1;
LEAVE loop1;
END IF;
END LOOP loop1;
SET contador2=contador2+1;
END IF;
IF gen<>'' THEN
SET done = 0;
OPEN cur2;
loop2: LOOP
FETCH cur2 INTO genid,famid2;
IF done=1 THEN
CLOSE cur2;
LEAVE loop2;
END IF;
IF famid<>famid2 THEN
SET famid=famid2;
END IF;
END LOOP loop2;
SET contador2=contador2+1;
END IF;
IF spp<>'' AND genid>0 THEN
SET done = 0;
OPEN cur3;
loop3: LOOP
FETCH cur3 INTO sppid;
IF done=1 THEN
CLOSE cur3;
LEAVE loop3;
END IF;
END LOOP loop3;
SET contador2=contador2+1;
END IF;
IF infspp<>'' AND genid>0 AND sppid>0 THEN
SET done = 0;
OPEN cur4;
loop4: LOOP
FETCH cur4 INTO infspid;
IF done=1 THEN
CLOSE cur4;
LEAVE loop4;
END IF;
END LOOP loop4;
SET contador2=contador2+1;
END IF;
IF famid>0 THEN
SET resultado = CONCAT('familiaid_',famid);
SET contador=contador+1;
END IF;
IF genid>0 THEN
IF contador=0 THEN
SET resultado = CONCAT('generoid_',genid);
ELSE
SET toprint = CONCAT('generoid_',genid);
SET resultado = CONCAT(resultado,';',toprint);
END IF;
SET contador=contador+1;
END IF;
IF sppid>0 THEN
SET toprint = CONCAT('especieid_',sppid);
SET resultado = CONCAT(resultado,';',toprint);
SET contador=contador+1;
END IF;
IF infspid>0 THEN
SET toprint = CONCAT('infraespecieid_',infspid);
SET resultado = CONCAT(resultado,';',toprint);
SET contador=contador+1;
END IF;
IF contador=contador2 THEN
SET resultado = CONCAT('OK | ',resultado);
ELSE
IF contador2=4 AND contador=3 THEN
SET resultado = CONCAT('miss_InfraEspecie | ',resultado);
ELSE
IF contador=2 AND contador2>2 THEN
SET resultado = CONCAT('miss_Especie | ',resultado);
ELSE
IF contador=1 AND contador2>1 THEN
SET resultado = CONCAT('miss_Genero | ',resultado);
ELSE
IF contador=0 AND contador2=1 THEN
SET resultado = CONCAT('miss_Familia | ',resultado);
END IF;
END IF;
END IF;
END IF;
END IF;
RETURN resultado;
END
