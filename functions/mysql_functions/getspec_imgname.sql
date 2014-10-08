CREATE FUNCTION getspec_imgname(arqnome char(255), separador char(100), padrao INT(2)) RETURNS INT(10)
BEGIN
DECLARE resultado INT DEFAULT NULL;
DECLARE nn INT DEFAULT 0;
DECLARE specid INT DEFAULT 0;
DECLARE coletor CHAR(255) DEFAULT '';
DECLARE numero CHAR(255)  DEFAULT '';
IF padrao>=1 THEN
	SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(arqnome,separador,1),separador,-1)) INTO coletor;
	SET coletor = UPPER(acentostosemacentos(coletor));
	SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(arqnome,separador,2),separador,-1)) INTO numero;
	IF padrao=1 THEN
		SELECT EspecimenID,count(*) into resultado,nn FROM Especimenes JOIN Pessoas ON ColetorID=PessoaID WHERE UPPER(acentostosemacentos(SobreNome)) LIKE CONCAT(coletor,'%') AND Number=numero;
	END IF;
	IF padrao=2 THEN
		SELECT EspecimenID,count(*) into resultado,nn  FROM Especimenes JOIN Pessoas ON ColetorID=PessoaID WHERE checkiniciais(Prenome,SegundoNome,SobreNome) LIKE coletor AND Number=numero;
		IF resultado=0 THEN
			SELECT EspecimenID,count(*) into resultado,nn FROM Especimenes JOIN Pessoas ON ColetorID=PessoaID WHERE UPPER(acentostosemacentos(Abreviacao)) LIKE CONCAT(coletor,'%') AND Number=numero;
		END IF;
	END IF;
	IF padrao=3 THEN
		SET specid = (coletor+0);
		SELECT EspecimenID,count(*) into resultado,nn FROM Especimenes WHERE EspecimenID=specid;
	END IF;
	IF (nn>1 OR nn=0) THEN
		SET resultado = NULL;
	END IF;
END IF;
RETURN resultado;
END
