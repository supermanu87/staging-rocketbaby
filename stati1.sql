DELIMITER $$

DROP PROCEDURE IF EXISTS `stati` $$
CREATE DEFINER=`root`@`%` PROCEDURE `stati`(
  IN loc_username VARCHAR(255),
  IN loc_password VARCHAR(255)
)
BEGIN
DECLARE done INT DEFAULT FALSE;
DECLARE v_Canceled_Report INT;

 DECLARE cursor_i CURSOR FOR SELECT Canceled_Report FROM dashboard ORDER BY CreatedAt ASC;
 DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
 OPEN cursor_i;
 dashboard_loop: LOOP
 FETCH cursor_i INTO v_Canceled_Report;
  IF done THEN
     LEAVE dashboard_loop;
  END IF;
  SELECT v_Canceled_Report;
 END LOOP;
 CLOSE cursor_i;
END $$

DELIMITER ;
