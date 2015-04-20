CREATE FUNCTION projetostringbrahms(projid int(10)) RETURNS VARCHAR(200) CHARSET utf8
BEGIN
DECLARE projn VARCHAR(200) DEFAULT '';
SELECT ProjetoNome INTO projn FROM Projetos WHERE ProjetoID=projid;
RETURN projn;
END
