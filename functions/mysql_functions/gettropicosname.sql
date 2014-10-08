CREATE FUNCTION gettropicosname(identid INT(10)) RETURNS text CHARSET utf8
BEGIN
DECLARE famid INT(10) DEFAULT 0;
DECLARE genid INT(10) DEFAULT 0;
DECLARE specid INT(10) DEFAULT 0;
DECLARE infspid INT(10) DEFAULT 0;
DECLARE infbasiotest INT(10) DEFAULT 0;
DECLARE spbasiotest INT(10) DEFAULT 0;
DECLARE fam CHAR(150) DEFAULT '';
DECLARE gen CHAR(100) DEFAULT '';
DECLARE sp CHAR(150) DEFAULT '';
DECLARE spaut CHAR(150) DEFAULT '';
DECLARE spbasio CHAR(150) DEFAULT '';
DECLARE infrni CHAR(150) DEFAULT '';
DECLARE infsp CHAR(150) DEFAULT '';
DECLARE infbasio CHAR(150) DEFAULT '';
DECLARE infaut CHAR(150) DEFAULT '';
DECLARE nome CHAR(100) DEFAULT '';
DECLARE nnsp CHAR(100) DEFAULT '';
DECLARE npess INT(10) DEFAULT 0;
DECLARE morftp INT(10) DEFAULT 0;
SELECT FamiliaID,GeneroID,EspecieID,InfraEspecieID INTO famid,genid,specid,infspid FROM Identidade WHERE DetID=identid;
IF infspid>0 THEN
	SELECT TRIM(Tax_Familias.Familia),TRIM(Tax_Generos.Genero),TRIM(Tax_Especies.Especie),TRIM(Tax_Especies.EspecieAutor),TRIM(Tax_Especies.BasionymAutor),TRIM(Tax_InfraEspecies.InfraEspecieNivel),TRIM(Tax_InfraEspecies.InfraEspecie),TRIM(Tax_InfraEspecies.BasionymAutor),TRIM(Tax_InfraEspecies.InfraEspecieAutor), Tax_Especies.Morfotipo INTO fam, gen, sp, spaut, spbasio, infrni, infsp, infbasio, infaut, morftp FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE InfraEspecieID=infspid;
	SET nnsp = UPPER(SUBSTRING(sp,1,3));
	IF (nnsp='SP.' OR infrni='morfossp' OR infrni='' OR morftp>0) THEN
		SET nome = 'morfotipo';
	ELSE 
		SET nome = 'infraespecie';
	END IF;
	IF (nome<>'morfotipo' AND infsp<>'') THEN
    	SET nome = CONCAT(gen," ",sp," ",infsp);
	ELSE 
		SET nome = '';
	END IF;
END IF;
IF (infspid=0 AND specid>0) THEN
	SELECT TRIM(Tax_Familias.Familia),TRIM(Tax_Generos.Genero),TRIM(Tax_Especies.Especie),TRIM(Tax_Especies.EspecieAutor),TRIM(Tax_Especies.BasionymAutor),Tax_Especies.Morfotipo  INTO fam,gen,sp,spaut,spbasio,morftp FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE EspecieID=specid;
SET nnsp = UPPER(SUBSTRING(sp,1,3));
	IF (nnsp='SP.' OR spaut='' OR morftp>0) THEN
		SET nome = '';
	ELSE 
    	SET nome = CONCAT(gen," ",sp);    
    END IF;
END IF;
RETURN nome;
END
