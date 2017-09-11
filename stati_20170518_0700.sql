BEGIN
	DECLARE done INT DEFAULT FALSE;
	DECLARE v_Id INT; 
	DECLARE v_analyze_id INT; 
	DECLARE v_Name VARCHAR(255); 
	DECLARE v_CreatedAt date; 
	DECLARE v_LineitemQuantity INT; 
	DECLARE v_LineitemName VARCHAR(255); 
	DECLARE v_LineitemSku VARCHAR(255); 
	DECLARE v_OrderSKU_Shopify VARCHAR(255); 
	DECLARE v_StatusFinale VARCHAR(255); 
	DECLARE v_TrackCode VARCHAR(255); 
	DECLARE v_SIMFDB_Richiesta INT; 
	DECLARE v_SIMFDB_PRESENTE INT; 
	DECLARE v_PRO INT; 
	DECLARE v_SPE INT; 
	DECLARE v_RIC INT; 
	DECLARE v_Soldout_Report VARCHAR(255); 
	DECLARE v_DBPO_Report VARCHAR(255); 
	DECLARE v_Canceled_Report INT; 
	DECLARE Stato2B VARCHAR(255);  
	DECLARE v_InArrivo INT; 
	
	DECLARE cursor_i CURSOR FOR SELECT Id, analyze_id, Name, CreatedAt, LineitemQuantity, LineitemName, LineitemSku, OrderSKU_Shopify, StatusFinale, TrackCode, SIMFDB_Richiesta, SIMFDB_PRESENTE, PRO, SPE, RIC, Soldout_Report, DBPO_Report, Canceled_Report 
			FROM dashboard ORDER BY CreatedAt ASC LIMIT 2000;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
	OPEN cursor_i;
	dashboard_loop: LOOP
		FETCH cursor_i INTO v_Id, v_analyze_id, v_Name, v_CreatedAt, v_LineitemQuantity, v_LineitemName, v_LineitemSku, v_OrderSKU_Shopify, v_StatusFinale, v_TrackCode, v_SIMFDB_Richiesta, v_SIMFDB_PRESENTE, v_PRO, v_SPE, v_RIC, v_Soldout_Report, v_DBPO_Report,v_Canceled_Report;
		IF done THEN
		  LEAVE dashboard_loop;
		END IF;
		-- SELECT v_analyze_id, v_Name, v_CreatedAt, v_LineitemQuantity, v_LineitemName, v_LineitemSku, v_OrderSKU_Shopify, v_StatusFinale, v_TrackCode, v_SIMFDB_Richiesta, v_SIMFDB_PRESENTE, v_PRO, v_SPE, v_RIC, v_Soldout_Report, v_DBPO_Report,v_Canceled_Report;
		
		SET v_InArrivo = -1;
		SELECT sum(InArrivo) QtaInArrivo, Stato, Sku 
		FROM dbpo 
		WHERE Sku = v_LineitemSku 
		GROUP BY Sku, Stato;
        
		SET Stato2B = 'Non In Consegna';
		
		SELECT 'In Consegna' INTO Stato2B  FROM spe 
		WHERE OrderSku = v_OrderSKU_Shopify
			AND TrackingNumero <> ''
			AND TrackingNumero <> '0'
			AND TrackingNumero IS NOT NULL;
		
		IF(Stato2B = 'Non In Consegna')
		THEN
			SET Stato2B = CONCAT(Stato2B, ' - ',v_OrderSKU_Shopify);
		-- se decommento mi esce soltanto un risultato
		-- ELSE
		-- 	SELECT 'Reso' INTO Stato2B  FROM ric 
		-- 	WHERE CONCAT(Numero, ' - ',AnaArtCodice) = v_OrderSKU_Shopify;
		END IF;
		
		-- SET Stato2B = CONCAT(Stato2B, ' - ',v_OrderSKU_Shopify);


		SELECT Stato2B;        	
        -- SET done = TRUE;
	END LOOP;
	CLOSE cursor_i;
END