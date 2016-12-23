CREATE FUNCTION especimenInprojeto(specid INT(10), projid INT(10)) RETURNS INT(1)
BEGIN
DECLARE res INT(10) DEFAULT 0;
SELECT count(*) INTO res FROM ProjetosEspecs WHERE EspecimenID=specid AND ProjetoID=projid;
RETURN res;
END
