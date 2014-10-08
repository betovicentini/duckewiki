CREATE FUNCTION gettaxonlevel(identid INT(10), morftp BOOLEAN ) RETURNS text CHARSET utf8
BEGIN
DECLARE famid INT(10) DEFAULT 0;
DECLARE genid INT(10) DEFAULT 0;
DECLARE specid INT(10) DEFAULT 0;
DECLARE infspid INT(10) DEFAULT 0;
DECLARE infbasiotest INT(10) DEFAULT 0;
DECLARE spbasiotest INT(10) DEFAULT 0;
DECLARE fam VARCHAR(150) DEFAULT '';
DECLARE gen VARCHAR(100) DEFAULT '';
DECLARE sp VARCHAR(150) DEFAULT '';
DECLARE spaut VARCHAR(150) DEFAULT '';
DECLARE spbasio VARCHAR(150) DEFAULT '';
DECLARE infrni VARCHAR(150) DEFAULT '';
DECLARE infsp VARCHAR(150) DEFAULT '';
DECLARE infbasio VARCHAR(150) DEFAULT '';
DECLARE infaut VARCHAR(150) DEFAULT '';
DECLARE nome VARCHAR(1000) DEFAULT '';
DECLARE nnsp VARCHAR(100) DEFAULT '';
DECLARE npess INT(10) DEFAULT 0;
SELECT FamiliaID,GeneroID,EspecieID,InfraEspecieID INTO famid,genid,specid,infspid FROM Identidade WHERE DetID=identid;
IF infspid>0 THEN
	SELECT TRIM(Tax_Familias.Familia),TRIM(Tax_Generos.Genero),TRIM(Tax_Especies.Especie),TRIM(Tax_Especies.EspecieAutor),TRIM(Tax_Especies.BasionymAutor),TRIM(Tax_InfraEspecies.InfraEspecieNivel),TRIM(Tax_InfraEspecies.InfraEspecie),TRIM(Tax_InfraEspecies.BasionymAutor),TRIM(Tax_InfraEspecies.InfraEspecieAutor) INTO fam, gen, sp, spaut, spbasio, infrni, infsp, infbasio, infaut FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE InfraEspecieID=infspid;
	IF infbasio<>'' THEN
		SELECT infaut LIKE CONCAT('%',infbasio,'%') INTO infbasiotest;
		IF (infbasiotest=0) THEN
			SET infaut = CONCAT(infbasio,' ',infaut);
		END IF;
	END IF;	
	IF spbasio<>'' THEN
		SELECT spaut LIKE CONCAT('%',spbasio,'%') INTO spbasiotest;
		IF (spbasiotest=0) THEN
			SET spaut = CONCAT(spbasio,' ',spaut);
		END IF;
	END IF;	
	SET infaut = TRIM(infaut);
	SET nnsp = UPPER(SUBSTRING(sp,1,3));
	IF (nnsp='SP.' OR infrni='morfossp' OR infrni='') THEN
			SET nome = 'morfotipo';
	ELSE 
		SET nome = 'infraespecie';
	END IF;
	IF (nome='morfotipo' AND  morftp=0) THEN
    	SET nome = 'especie'; 
    END IF;
END IF;
IF ((nome='especie' OR nome='') AND specid>0) THEN
	SELECT TRIM(Tax_Familias.Familia),TRIM(Tax_Generos.Genero),TRIM(Tax_Especies.Especie),TRIM(Tax_Especies.EspecieAutor),TRIM(Tax_Especies.BasionymAutor) INTO fam,gen,sp,spaut,spbasio FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE EspecieID=specid;
	IF spbasio<>'' THEN
		SELECT spaut LIKE CONCAT('%',spbasio,'%') INTO spbasiotest;
		IF (spbasiotest=0) THEN
			SET spaut = CONCAT(spbasio,' ',spaut);
		END IF;
	END IF;	
	SET spaut = TRIM(spaut);
	SET nnsp = UPPER(SUBSTRING(sp,1,3));
	IF (nnsp='SP.' OR spaut='') THEN
			SET nome = 'morfotipo';
	ELSE 
		SET nome = 'especie';
	END IF;
	IF (nome='morfotipo' AND  morftp=0) THEN
    	SET nome = 'genero';
    ELSE 
    	SET nome = 'especie';   
    END IF;
END IF;	
IF ((nome='genero' OR nome='' OR specid=0) AND genid>0) THEN		
	SELECT TRIM(Tax_Familias.Familia),TRIM(Tax_Generos.Genero) INTO fam,gen FROM Tax_Generos JOIN Tax_Familias USING(FamiliaID) WHERE GeneroID=genid;
	IF (UPPER(gen)='INDET') THEN
		SET nome = 'familia';		
	ELSE 
		SET nome = 'genero';
	END IF;
END IF;
IF ((nome='familia' OR nome='' OR genid=0) AND famid>0) THEN
	SELECT Tax_Familias.Familia INTO fam FROM Tax_Familias WHERE FamiliaID=famid;
	IF (UPPER(fam)='INDET') THEN
		SET nome = 'reino';
	ELSE 
		SET nome = 'familia';	
	END IF;
END IF;
IF (nome='') THEN
		SET nome = 'reino';
END IF;
RETURN nome;
END
