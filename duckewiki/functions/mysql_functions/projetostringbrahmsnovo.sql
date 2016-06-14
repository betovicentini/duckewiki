CREATE FUNCTION projetostringbrahmsnovo(specid int(10)) RETURNS VARCHAR(60) CHARSET utf8
BEGIN
DECLARE projn VARCHAR(60) DEFAULT '';
DECLARE projid INT(10) DEFAULT 0;
DECLARE clprj INT(10) DEFAULT 0;
SELECT tb.ProjetoID INTO projid FROM (SELECT COUNT(DISTINCT ProjetoID) as clprj, ProjetoID FROM ProjetosEspecs WHERE EspecimenID=specid) AS tb ORDER BY tb.clprj DESC LIMIT 0,1;
IF (projid>0) THEN
SELECT ProjetoNome INTO projn FROM Projetos WHERE ProjetoID=projid;
END IF;
RETURN projn;
END
