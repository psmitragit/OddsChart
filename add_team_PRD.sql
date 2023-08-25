BEGIN
    DECLARE i INT DEFAULT 0;
    DECLARE team_count INT;
    DECLARE team_data JSON;
    DECLARE home_team_name VARCHAR(255);
    DECLARE away_team_name VARCHAR(255);
    DECLARE home_team_exists INT;
    DECLARE away_team_exists INT;
    DECLARE team_type VARCHAR(255);
    DECLARE logo_value VARCHAR(255);
    DECLARE abbr_value VARCHAR(255);

    -- Get the total number of teams in the JSON array
    SET team_count = JSON_LENGTH(team_data_array);

    WHILE i < team_count DO
        -- Extract team data for each object in the JSON array
        SET team_data = JSON_EXTRACT(team_data_array, CONCAT('$[', i, ']'));

        -- Extract home and away team names from JSON
        SET home_team_name = JSON_UNQUOTE(JSON_EXTRACT(team_data, '$.home_team'));
        SET away_team_name = JSON_UNQUOTE(JSON_EXTRACT(team_data, '$.away_team'));
        SET team_type = JSON_UNQUOTE(JSON_EXTRACT(team_data, '$.sport_key'));

        -- Check if home team already exists
        SELECT COUNT(*) INTO home_team_exists FROM teams WHERE team_name = home_team_name;
        IF home_team_exists > 0 THEN
            SELECT 'Home team already exists. Skipping.' AS Message;
        ELSE
            -- Insert home team into teams table
            IF team_type IN ('tennis_atp_wimbledon', 'tennis_wta_wimbledon') THEN
                SET logo_value = 'tennis.png';
                SET abbr_value = CONCAT(SUBSTRING(home_team_name, 1, 1), SUBSTRING(SUBSTRING_INDEX(home_team_name, ' ', -1), 1, 1));
            ELSE
                SET logo_value = NULL; -- Set the appropriate default logo value
                SET abbr_value = NULL; -- Set the appropriate default abbreviation value
            END IF;

            INSERT INTO teams (team_name, type, logo, abbr) VALUES (home_team_name, team_type, logo_value, abbr_value);
            SELECT 'Home team saved successfully.' AS Message;
        END IF;

        -- Check if away team already exists
        SELECT COUNT(*) INTO away_team_exists FROM teams WHERE team_name = away_team_name;
        IF away_team_exists > 0 THEN
            SELECT 'Away team already exists. Skipping.' AS Message;
        ELSE
            -- Insert away team into teams table
            IF team_type IN ('tennis_atp_wimbledon', 'tennis_wta_wimbledon') THEN
                SET logo_value = 'tennis.png';
                SET abbr_value = CONCAT(SUBSTRING(away_team_name, 1, 1), SUBSTRING(SUBSTRING_INDEX(away_team_name, ' ', -1), 1, 1));
            ELSE
                SET logo_value = NULL; -- Set the appropriate default logo value
                SET abbr_value = NULL; -- Set the appropriate default abbreviation value
            END IF;

            INSERT INTO teams (team_name, type, logo, abbr) VALUES (away_team_name, team_type, logo_value, abbr_value);
            SELECT 'Away team saved successfully.' AS Message;
        END IF;

        SET i = i + 1;
    END WHILE;
END
