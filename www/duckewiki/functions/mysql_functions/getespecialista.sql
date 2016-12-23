CREATE FUNCTION getespecialista(famid INT(10), curherbario CHAR(100)) RETURNS VARCHAR(500)  CHARSET utf8
BEGIN
DECLARE ttt VARCHAR(1000) DEFAULT '';
IF (curherbario<>'') THEN
SELECT CONCAT(Prenome,' ',SegundoNome,' ',Sobrenome) into ttt FROM Especialistas JOIN Pessoas ON PessoaID=Especialista WHERE Herbarium LIKE curherbario AND FamiliaID=famid;
ELSE 
SELECT CONCAT(Prenome,' ',SegundoNome,' ',Sobrenome) into ttt FROM Especialistas JOIN Pessoas ON PessoaID=Especialista WHERE FamiliaID=famid;
END IF;
SET ttt = TRIM(ttt);
RETURN ttt;
END
