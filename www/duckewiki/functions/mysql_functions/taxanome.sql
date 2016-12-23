CREATE FUNCTION taxanome(famid INT(10),genusid INT(10),spid INT(10),infspid INT(10), qual CHAR(50)) RETURNS CHAR(100) CHARSET utf8
BEGIN
DECLARE famtxt CHAR(100) DEFAULT "";
DECLARE genustxt CHAR(100) DEFAULT "";
DECLARE especietxt CHAR(100) DEFAULT "";
DECLARE infraespecietxt CHAR(100) DEFAULT "";
DECLARE onome CHAR(100) DEFAULT "";
IF (infspid>0) THEN
	SELECT Familia,Genero,Especie,InfraEspecie INTO famtxt,genustxt,especietxt,infraespecietxt From Tax_InfraEspecies AS infsptb JOIN Tax_Especies as sptb ON sptb.EspecieID=infsptb.EspecieID  JOIN Tax_Generos as gentb ON gentb.GeneroID=sptb.GeneroID JOIN Tax_Familias as famtb ON famtb.FamiliaID=gentb.FamiliaID WHERE infsptb.InfraEspecieID=infspid;
	SET onome = CONCAT(genustxt," ",especietxt," ",$infraespecietxt);
ELSE 
	IF (spid>0) THEN
		SELECT Familia,Genero,Especie INTO famtxt,genustxt,especietxt  From Tax_Especies as sptb JOIN Tax_Generos as gentb ON gentb.GeneroID=sptb.GeneroID JOIN Tax_Familias as famtb ON famtb.FamiliaID=gentb.FamiliaID WHERE sptb.EspecieID=spid;
		SET onome = CONCAT(genustxt," ",especietxt);
	ELSE 
		IF (genusid>0) THEN
			SELECT Familia,Genero INTO famtxt,genustxt   From Tax_Generos as gentb JOIN Tax_Familias as famtb ON famtb.FamiliaID=gentb.FamiliaID WHERE gentb.GeneroID=genusid;
			SET onome = CONCAT(genustxt);
		ELSE 
			IF (famid>0) THEN
				SELECT Familia INTO famtxt From Tax_Familias as famtb WHERE famtb.FamiliaID=famid;
				SET onome = CONCAT(famtxt);
			END IF;
		END IF;
	END IF;
END IF;
IF (qual='familia') THEN
	RETURN famtxt;
END IF;
IF (qual='genero') THEN
	RETURN genustxt;
END IF;
IF (qual='especie') THEN
	RETURN especietxt;
END IF;
IF (qual='infraespecie') THEN
	RETURN infraespecietxt;
END IF;
IF (qual='onome') THEN
	RETURN onome;
END IF;


END
