BEGIN
    -- Declare variables
    DECLARE jsonData JSON;
    DECLARE indexValue INT;
    DECLARE totalCount INT;

    -- Initialize variables
    SET indexValue = 1;
    SET totalCount = JSON_LENGTH(jsonParam);

    -- Loop through each JSON object
    WHILE indexValue <= totalCount DO
        -- Extract JSON object
        SET jsonData = JSON_EXTRACT(jsonParam, CONCAT('$[', indexValue - 1, ']'));

        -- Get values for comparison
        SET @match_id = JSON_UNQUOTE(JSON_EXTRACT(jsonData, '$.id'));
        SET @h2h_home_odd = JSON_UNQUOTE(JSON_EXTRACT(jsonData, '$.bookmakers[0].markets[0].outcomes[0].price'));
        SET @h2h_away_odd = JSON_UNQUOTE(JSON_EXTRACT(jsonData, '$.bookmakers[0].markets[0].outcomes[1].price'));
        SET @spreads_home_odd = JSON_UNQUOTE(JSON_EXTRACT(jsonData, '$.bookmakers[0].markets[1].outcomes[0].price'));
        SET @spreads_away_odd = JSON_UNQUOTE(JSON_EXTRACT(jsonData, '$.bookmakers[0].markets[1].outcomes[1].price'));

        -- Check if match_id exists with same column values
        IF NOT EXISTS (
            SELECT 1
            FROM match_data
            WHERE match_id = @match_id
            AND (
                h2h_home_odd = @h2h_home_odd
                AND h2h_away_odd = @h2h_away_odd
                AND spreads_home_odd = @spreads_home_odd
                AND spreads_away_odd = @spreads_away_odd
            )
        ) THEN
            -- Insert values into the match_data table with the current date and time
            INSERT INTO match_data (match_id, home_team, away_team, match_time, type, h2h_lastupdate, h2h_home_odd, h2h_away_odd, spreads_lastupdate, spreads_home_odd, spreads_away_odd, created_at)
            VALUES (
                @match_id,
                JSON_UNQUOTE(JSON_EXTRACT(jsonData, '$.home_team')),
                JSON_UNQUOTE(JSON_EXTRACT(jsonData, '$.away_team')),
                JSON_UNQUOTE(JSON_EXTRACT(jsonData, '$.commence_time')),
                JSON_UNQUOTE(JSON_EXTRACT(jsonData, '$.sport_title')),
                JSON_UNQUOTE(JSON_EXTRACT(jsonData, '$.bookmakers[0].markets[0].last_update')),
                @h2h_home_odd,
                @h2h_away_odd,
                JSON_UNQUOTE(JSON_EXTRACT(jsonData, '$.bookmakers[0].markets[1].last_update')),
                @spreads_home_odd,
                @spreads_away_odd,
                NOW()
            );
        END IF;

        -- Increment index
        SET indexValue = indexValue + 1;
    END WHILE;

    -- Print success message
    SELECT 'Data inserted successfully.' AS message;
END