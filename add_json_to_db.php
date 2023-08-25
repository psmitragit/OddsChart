<?php
require_once './connection.php';
function curl_get($url)
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "content-type: application/json",
        ),
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        return false;
    } else {
        $res = json_decode($response, true);
        if (isset($res['message'])) {
            return false;
        } else {
            return $res;
        }
    }
}
$sports = array(
    0 => 'americanfootball_nfl',
    // 1 => 'soccer_usa_mls',
    // 2 => 'tennis_atp_wimbledon',
    // 3 => 'tennis_wta_wimbledon'
);
$response = array();

foreach ($sports as $sport) {
    $conditionMet = 0;
    while ($conditionMet === 0) {
        $api_sql = 'SELECT * FROM `api_keys` WHERE status = 1';
        $res_gen_api = mysqli_query($conn, $api_sql);
        if ($res_gen_api->num_rows > 0) {
            $res_api = mysqli_fetch_assoc($res_gen_api);
            $apiKey = str_replace(' ', '', $res_api['api_key']);
            $baseUrl = 'https://api.the-odds-api.com/v4';
            $regions = 'us';
            $mkt = 'spreads,h2h';
            $url = "$baseUrl/sports/$sport/odds/?apiKey=$apiKey&regions=$regions&markets=$mkt&oddsFormat=american";
            $response = curl_get($url);
            if ($response !== false) {
                $conditionMet = 1;
                $newResponse = array();
                foreach ($response as $res) {
                    if ($sport == 'americanfootball_nfl') {
                        $filteredBookmakers = array_filter($res['bookmakers'], function ($bookmaker) {
                            return $bookmaker['key'] == 'draftkings';
                        });
                    } elseif ($sport == 'soccer_usa_mls') {
                        $filteredBookmakers = array_filter($res['bookmakers'], function ($bookmaker) {
                            return $bookmaker['key'] == 'mybookieag';
                        });
                    } elseif ($sport == 'tennis_atp_wimbledon' || $sport == 'tennis_wta_wimbledon') {
                        $filteredBookmakers = array_filter($res['bookmakers'], function ($bookmaker) {
                            return $bookmaker['key'] == 'bovada';
                        });
                    }
                    $newResponse[] = array(
                        'id' => $res['id'],
                        'sport_key' => $res['sport_key'],
                        'sport_title' => $res['sport_title'],
                        'commence_time' => $res['commence_time'],
                        'home_team' => $res['home_team'],
                        'away_team' => $res['away_team'],
                        'bookmakers' => array_values($filteredBookmakers)
                    );
                }
                $response = $newResponse;


                echo '<pre>';

                print_r(htmlspecialchars($url));

                echo '</pre>';
             

                $data = json_encode($response, true);
                $jsonData = $data;
                $jsonData = str_replace('u00a0', '', str_replace('@', '', $data));
                $jsonData = str_replace('u00a0', '', str_replace("'", '', $jsonData));

                if (!empty($jsonData)) {
                    $currentDate = date('Y-m-d H:i:s');

                    $callQuery = "CALL Saveteam(?)";
                    $callStmt = $conn->prepare($callQuery);

                    $callStmt->bind_param("s", $jsonData);

                    if ($callStmt->execute()) {
                        echo "Team updated successfully <br>";
                    } else {
                        echo "Error executing the stored procedure: " . $callStmt->error;
                    }
                    $callStmt->close();

                    $callQuery = "CALL  Insertmatchdata(?)";
                    $callStmt = $conn->prepare($callQuery);

                    $callStmt->bind_param("s", $jsonData);

                    if ($callStmt->execute()) {
                        echo "Match Added successfully <br>";
                    } else {
                        echo "Error executing the stored procedure: " . $callStmt->error;
                    }
                    $callStmt->close();
                } else {
                    echo "JSON data is empty";
                }
            } else {
                $update_sql = 'UPDATE api_keys SET status = 0 WHERE id = ' . $res_api['id'];
                mysqli_query($conn, $update_sql);
                if ($res_api['id'] < 74) {
                    $update_sql2 = 'UPDATE api_keys SET status = 1 WHERE id = ' . ((int)$res_api['id'] + 1);
                } else {
                    $update_sql2 = 'UPDATE api_keys SET status = 1 WHERE id = 1';
                }
                mysqli_query($conn, $update_sql2);
            }
        } else {
            echo 'No Active Key<br>';
            break;
        }
    }
}
