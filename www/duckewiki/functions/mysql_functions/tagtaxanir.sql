CREATE FUNCTION tagtaxanir(famid INT(10),genid INT(10),specid INT(10),infspid INT(10), thisfamid INT(10),thisgenid INT(10),thispecid INT(10),thisinfspid INT(10)) RETURNS INT(10)
BEGIN
DECLARE nnirspp INT DEFAULT 0;
IF (infspid>0 ) THEN
	IF (infspid=thisinfspid) THEN
		SET nnirspp=1;
	END IF;
	ELSE
		IF (specid>0) THEN
			IF (specid=thispecid) THEN
				SET nnirspp=1;
			END IF;
		ELSE 
			IF (genid>0) THEN
				IF (genid=thisgenid) THEN
				SET nnirspp=1;
				END IF;
			ELSE
				IF (famid>0) THEN
					IF (famid=thisfamid) THEN
					SET nnirspp=1;
					END IF;
				END IF;
			END IF;
		END IF;
	END IF;
RETURN nnirspp;
END
