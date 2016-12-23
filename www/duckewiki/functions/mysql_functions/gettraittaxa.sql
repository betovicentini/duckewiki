CREATE FUNCTION gettraittaxa(traid INT(10)) RETURNS CHAR(250)
BEGIN
DECLARE resultado  CHAR(250) DEFAULT '';
IF traid>0 THEN 
SELECT GROUP_CONCAT( IF(nome=Familia,nome,CONCAT(nome,' [',Familia,' ]')) ORDER BY nome SEPARATOR '<br />' )  INTO resultado FROM TraitsLinkedTaxa  as trb JOIN TaxonomySimple as tb ON tb.nomeid=trb.nomeid WHERE trb.TraitID=traid;
END IF;
RETURN resultado;
END
