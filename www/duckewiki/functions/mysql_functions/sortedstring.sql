CREATE FUNCTION sortedstring(texto VARCHAR(500)) RETURNS VARCHAR(500)
BEGIN
DECLARE resultado VARCHAR(500) DEFAULT NULL;
DECLARE delim CHAR(10) DEFAULT "";
DECLARE ncat INT(10) DEFAULT 0;
SELECT GROUP_CONCAT(c order by c separator '') into resultado FROM (
SELECT texto, substr(acentostosemacentos(texto),iter.pos,1) as c FROM ( SELECT id AS pos from iterationTB) as iter WHERE iter.pos <= length(texto)) x GROUP BY texto;
set resultado = replace(resultado,'.','');
set resultado = replace(resultado,',','');
set resultado = replace(resultado,'-','');
set resultado = replace(resultado,'_','');
set resultado = replace(resultado,'/','');
set resultado = replace(resultado,'\\','');
set resultado = replace(resultado,'&','');
set resultado = replace(resultado,'%','');
set resultado = replace(resultado,';','');
set resultado = replace(resultado,'\t','');
set resultado = replace(resultado,' ','');
set resultado = replace(resultado,'0','');
set resultado = replace(resultado,'1','');
set resultado = replace(resultado,'2','');
set resultado = replace(resultado,'3','');
set resultado = replace(resultado,'4','');
set resultado = replace(resultado,'5','');
set resultado = replace(resultado,'6','');
set resultado = replace(resultado,'7','');
set resultado = replace(resultado,'8','');
set resultado = replace(resultado,'9','');
set resultado = LOWER(resultado);
RETURN resultado; 
END;
