CREATE FUNCTION upgazpath(gazid INT (10)) RETURNS VARCHAR(500) CHARSET utf8
BEGIN
DECLARE gazrunid INT(10) DEFAULT 0;
DECLARE resultado VARCHAR(500) DEFAULT '';
DECLARE gazparid INT(10) DEFAULT 0;
DECLARE rss VARCHAR(500) DEFAULT '';
IF (gazid>0) THEN
	SELECT gs.ParentID,gs.Gazetteer  INTO gazparid,rss FROM Gazetteer as gs WHERE gs.GazetteerID=gazid;
	SET resultado = rss;
	 IF (gazparid>0) THEN
		 SELECT gs.ParentID,gs.Gazetteer  INTO gazparid,rss FROM Gazetteer as gs WHERE gs.GazetteerID=gazparid;
		 SET resultado = CONCAT(rss,' - ',resultado);
		 IF (gazparid>0) THEN
			 SELECT gs.ParentID,gs.Gazetteer  INTO gazparid,rss FROM Gazetteer as gs WHERE gs.GazetteerID=gazparid;
			 SET resultado = CONCAT(rss,' - ',resultado);
			 IF (gazparid>0) THEN
				 SELECT gs.ParentID,gs.Gazetteer  INTO gazparid,rss FROM Gazetteer as gs WHERE gs.GazetteerID=gazparid;
				 SET resultado = CONCAT(rss,' - ',resultado);
				 IF (gazparid>0) THEN
					 SELECT gs.ParentID,gs.Gazetteer  INTO gazparid,rss FROM Gazetteer as gs WHERE gs.GazetteerID=gazparid;
					 SET resultado = CONCAT(rss,' - ',resultado);
					 IF (gazparid>0) THEN
						 SELECT gs.ParentID,gs.Gazetteer  INTO gazparid,rss FROM Gazetteer as gs WHERE gs.GazetteerID=gazparid;
						 SET resultado = CONCAT(rss,' - ',resultado);
					END IF;
				END IF;
			END IF;
		END IF;
	END IF;
END IF;
	/*
	SET gazrunid = gazid;
	myloop: WHILE (gazrunid>0) 
	DO
	 SET gazparid = gazparid+0;
	 IF resultado='' THEN
		 SET resultado = rss;
	 ELSE
		 SET resultado = CONCAT(rss,' - ',resultado);
	 END IF;
	 IF (gazparid=0) THEN
		LEAVE myloop;		
	 END IF;
	 SET gazrunid=gazparid;
	END WHILE;
	*/
RETURN resultado;
END

