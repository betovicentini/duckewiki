CREATE FUNCTION projetostringbrahmsnovo(specid int(10), pltaid int(10)) RETURNS VARCHAR(60) CHARSET utf8
BEGIN
DECLARE projn VARCHAR(60) DEFAULT '';
DECLARE projid INT(10) DEFAULT 0;
DECLARE clprj INT(10) DEFAULT 0;
IF (specid>0) THEN 
SELECT ProjetoID INTO projid FROM ProjetosEspecs WHERE EspecimenID=specid LIMIT 0,1;
END IF;
IF (specid=0 AND pltaid>0) THEN 
SELECT ProjetoID INTO projid FROM ProjetosEspecs WHERE  PlantaID=pltaid LIMIT 0,1;
END IF;
IF (projid>0) THEN
SELECT ProjetoNome INTO projn FROM Projetos WHERE ProjetoID=projid;
END IF;
RETURN projn;
END
