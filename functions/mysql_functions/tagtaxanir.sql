CREATE FUNCTION tagtaxanir(famid INT(10),genid INT(10),specid INT(10),infspid INT(10), thisfamid INT(10),thisgenid INT(10),thispecid INT(10),thisinfspid INT(10)) RETURNS INT(10)
BEGIN
DECLARE nnirspp INT DEFAULT 0;
IF (infspid>0 AND infspid=thisinfspid) THEN
	SET nnirspp=1;
	ELSE
		IF (specid>0 AND specid=thispecid) THEN
			SET nnirspp=1;
		ELSE 
			IF (genid>0 AND genid=thisgenid) THEN
				SET nnirspp=1;
			ELSE
				IF (famid>0 AND famid=thisfamid) THEN
					SET nnirspp=1;
				END IF;
			END IF;
		END IF;
	END IF;
RETURN nnirspp;
END
