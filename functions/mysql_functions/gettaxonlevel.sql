CREATE FUNCTION gettaxonlevel(identid INT(10), morftp BOOLEAN ) RETURNS CHAR(255) CHARSET utf8
BEGIN
DECLARE famid INT(10) DEFAULT 0;
DECLARE genid INT(10) DEFAULT 0;
DECLARE specid INT(10) DEFAULT 0;
DECLARE infspid INT(10) DEFAULT 0;
DECLARE fam VARCHAR(150) DEFAULT '';
DECLARE gen VARCHAR(100) DEFAULT '';
DECLARE sp VARCHAR(150) DEFAULT '';
DECLARE infrni VARCHAR(150) DEFAULT '';
DECLARE infsp VARCHAR(150) DEFAULT '';
DECLARE nome VARCHAR(255) DEFAULT '';
DECLARE nnsp VARCHAR(100) DEFAULT '';
DECLARE subspmorfo INT(10) DEFAULT 0;
DECLARE spmorfo INT(10) DEFAULT 0;
SELECT FamiliaID,GeneroID,EspecieID,InfraEspecieID INTO famid,genid,specid,infspid FROM Identidade WHERE DetID=identid;
IF infspid>0 THEN
	SELECT TRIM(Tax_Especies.Especie),TRIM(Tax_Especies.Morfotipo),TRIM(Tax_InfraEspecies.InfraEspecieNivel),TRIM(Tax_InfraEspecies.InfraEspecie),TRIM(Tax_InfraEspecies.Morfotipo) INTO sp, spmorfo, infrni, infsp, subspmorfo FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) WHERE InfraEspecieID=infspid;
	IF (morftp=0) THEN
		IF (subspmorfo=1) THEN
			SET nome = 'Espécie';
		ELSE
			IF (spmorfo=1) THEN
			SET nome = 'Gênero';
			ELSE
			SET nome = 'Espécie';
			END IF;
		END IF;
	ELSE 
	    IF (subspmorfo=1) THEN
			SET nome = 'submorfotipo';
	    ELSE 
			SET nome = 'Subespécie';
		END IF;
	END IF;
END IF;
IF (nome='' AND specid>0) THEN
	SELECT TRIM(Tax_Especies.Especie),TRIM(Tax_Especies.Morfotipo) INTO sp,spmorfo FROM Tax_Especies WHERE EspecieID=specid;
	IF (morftp=0) THEN
			IF (spmorfo=1) THEN
				SET nome = 'Gênero';
			ELSE
				SET nome = 'Espécie';
			END IF;
	ELSE 
	    IF (spmorfo=1) THEN
			SET nome = 'morfotipo';
	    ELSE 
			SET nome = 'Espécie';
		END IF;
	END IF;
END IF;
IF (genid>0 AND nome='') THEN
	SELECT TRIM(Tax_Generos.Genero) INTO gen FROM Tax_Generos WHERE GeneroID=genid;
	IF (UPPER(gen)='INDET') THEN
		SET nome = 'Família';
	ELSE 
		SET nome = 'Gênero';
	END IF;
END IF;
IF (nome='' AND famid>0) THEN
	SELECT Tax_Familias.Familia INTO fam FROM Tax_Familias WHERE FamiliaID=famid;
	IF (UPPER(fam)='INDET') THEN
		SET nome = 'Reino';
	ELSE 
		SET nome = 'Família';
	END IF;
END IF;
IF (nome='') THEN
		SET nome = 'Reino';
END IF;
RETURN nome;
END
