CREATE FUNCTION projetologo(projid int(10)) RETURNS text CHARSET utf8
BEGIN
DECLARE resultado VARCHAR(200) DEFAULT '';
SELECT LogoFile INTO resultado FROM Projetos WHERE ProjetoID=projid;
RETURN resultado;
END
