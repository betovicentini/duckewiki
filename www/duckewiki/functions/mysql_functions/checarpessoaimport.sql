CREATE FUNCTION checarpessoaimport(abrev char(40), firstname char(40), lastname char(40)) RETURNS CHAR(40) 
BEGIN
DECLARE foundid CHAR(40) DEFAULT 0;
DECLARE idofrun CHAR(40) DEFAULT 0;
IF (abrev IS NOT NULL) THEN
	SET abrev = UPPER(acentostosemacentos(abrev));
	SELECT COUNT(PessoaID) INTO idofrun FROM Pessoas WHERE UPPER(acentostosemacentos(Abreviacao)) LIKE abrev;
	IF (idofrun=1) THEN
		SELECT PessoaID INTO idofrun FROM Pessoas WHERE UPPER(acentostosemacentos(Abreviacao)) LIKE abrev;
		SET foundid = idofrun;
	END IF;
END IF;
IF (foundid=0) THEN
	SET lastname = UPPER(TRIM(acentostosemacentos(lastname)));
	SET firstname = UPPER(TRIM(acentostosemacentos(firstname)));
	SELECT COUNT(PessoaID) INTO idofrun FROM Pessoas WHERE UPPER(acentostosemacentos(Sobrenome)) LIKE lastname AND LEFT(UPPER(acentostosemacentos(Prenome)),1) LIKE LEFT(firstname,1);
	IF (idofrun>0) THEN
			SET foundid = 'ERRO';
	END IF;
END IF;
RETURN foundid;
END
