<?php
require_once './connection.php';
error_reporting(-1);
if ($_POST['action'] == 'get_team') {
    $type = $_POST['type'];
    $sql = 'SELECT match_data.*, teams_home.logo AS home_logo, teams_home.abbr AS home_abbr, teams_away.logo AS away_logo, teams_away.abbr AS away_abbr
    FROM match_data
    JOIN teams AS teams_home ON match_data.home_team = teams_home.team_name
    JOIN teams AS teams_away ON match_data.away_team = teams_away.team_name
    WHERE match_data.type = "' . $type . '" AND DATE(match_data.match_time) >= "' . date('Y-m-d') . '"
    GROUP BY match_data.match_id
    ORDER BY match_data.match_time ASC';
    $res_buil = mysqli_query($conn, $sql);
    $i = 0;
    while ($match = mysqli_fetch_assoc($res_buil)) {
        $logopathpath = './team_logos/' . $type . '/';
?>
        <div class='team_div'>
            <input onclick="show_graph($('.rangeSelect:checked').val());" type="radio" name="match_id" id="<?= $match['match_id'] ?>" value="<?= $match['match_id'] ?>" <?= $i == 0 ? 'checked' : '' ?> data-homel="<?= $logopathpath . $match['home_logo'] ?>" data-awayl="<?= $logopathpath . $match['away_logo'] ?>" data-homefn="<?= $match['home_team'] ?>" data-awayfn="<?= $match['away_team'] ?>" data-match_time="<?= date('d M, Y - h:i A', strtotime($match['match_time'] . ' -4 hours')) ?>">
            <label for='<?= $match['match_id'] ?>'>
                <img class='team_logo' src='<?= $logopathpath . $match['home_logo'] ?>'><?= $match['home_abbr'] ?> - <?= $match['away_abbr'] ?><img class='team_logo' src='<?= $logopathpath . $match['away_logo'] ?>'>
            </label>
        </div>
    <?php
        $i++;
    }
} elseif ($_POST['action'] == 'get_chart') {
    function chartdata($type, $match_id, $odd_type, $range)
    {
        require './connection.php';
        if ($range == 't') {
            $sql = 'SELECT * FROM `match_data` WHERE type = "' . $type . '" AND match_id = "' . $match_id . '" AND DATE(created_at) = CURDATE()  ORDER BY created_at ASC';
        } elseif ($range == 'w') {
            $end_date = date('Y-m-d');
            $start_date = date('Y-m-d', strtotime('-6 days'));
            
            $sql = 'SELECT *, DATE(created_at) AS date, ' . $odd_type . '_home_odd AS home_odd, ' . $odd_type . '_away_odd AS away_odd FROM `match_data` WHERE type = "' . $type . '" AND match_id = "' . $match_id . '" AND DATE(created_at) BETWEEN "' . $start_date . '" AND "' . $end_date . '"  ORDER BY date ASC';
        } elseif ($range == 'm') {
            $end_date = date('Y-m-d');
            $start_date = date('Y-m-d', strtotime('-29 days'));
          
            $sql = 'SELECT *, DATE(created_at) AS date, ' . $odd_type . '_home_odd AS home_odd, ' . $odd_type . '_away_odd AS away_odd FROM `match_data` WHERE type = "' . $type . '" AND match_id = "' . $match_id . '" AND DATE(created_at) BETWEEN "' . $start_date . '" AND "' . $end_date . '"  ORDER BY date ASC';
        } elseif ($range == 'y') {
            $end_date = date('Y-m-d');
            $start_date = date('Y-m-d', strtotime('-364 days'));
            $sql = 'SELECT *, DATE_FORMAT(created_at, "%Y-%m") AS month, ' . $odd_type . '_home_odd AS home_odd, ' . $odd_type . '_away_odd AS away_odd FROM `match_data` WHERE type = "' . $type . '" AND match_id = "' . $match_id . '" AND DATE(created_at) BETWEEN "' . $start_date . '" AND "' . $end_date . '" ORDER BY month ASC';
        }
        $res_gen = mysqli_query($conn, $sql);
        $home = array();
        $away = array();
        $timestamps = array();
        $extra_data = '';
        $try = array();
        while ($res = mysqli_fetch_assoc($res_gen)) {
            if ($res['home_odd'] !== null && $res['away_odd'] !== null) {
                $home_odd = round($res['home_odd'], 0);
                $away_odd = round($res['away_odd'], 0);
     
                if ($range == 't') {
                    $time = date('H:i', strtotime($res['created_at']));
                } elseif ($range == 'w') {
                    $time = date('d M', strtotime($res['created_at']));
                } elseif ($range == 'm') {
                    $time = date('d M', strtotime($res['created_at']));
                } elseif ($range == 'y') {
                    $time = date('M', strtotime($res['created_at']));
                }
                
                $extra_data = $res['home_team'] . ' VS ' . $res['away_team'];
                $try[$time] = [
                    'home' => $home_odd,
                    'away' => $away_odd,
                ];
            }
        }
        foreach ($try as $k => $v) {
            $timestamps[] = $k;
            $home[] = $v['home'];
            $away[] = $v['away'];
        }
        return compact('home', 'away', 'timestamps', 'extra_data');
    }
    $type = $_POST['type'];
    $odd_type = $_POST['odd_type'];
    $match_id = $_POST['match_id'];
    $range = $_POST['range'];
    $res = chartdata($type, $match_id, $odd_type, $range);
    $homedata = ($res['home']);
    $awaydata = ($res['away']);
    $timestamps = ($res['timestamps']);
    $extra_data = $res['extra_data'];
    $res = array('homedata' => $homedata, 'awaydata' => $awaydata, 'timestamps' => $timestamps, 'type' => $type, 'oddtype' => $odd_type, 'matchbtw' => $extra_data, 'match_id' => $match_id);
    echo json_encode($res);
} elseif ($_POST['action'] == 'get_ticker') {
    $type = $_POST['type'];
    $odd_type = $_POST['odd_type'];
    $sql = "
    SELECT subquery.*,
        teams_home.logo AS home_logo,
        teams_home.abbr AS home_abbr,
        teams_away.logo AS away_logo,
        teams_away.abbr AS away_abbr
    FROM (
        SELECT *,
            (home_odd - LAG(home_odd) OVER (PARTITION BY match_id ORDER BY id DESC)) / LAG(home_odd) OVER (PARTITION BY match_id ORDER BY id DESC) * 100 AS home_odd_change,
            (away_odd - LAG(away_odd) OVER (PARTITION BY match_id ORDER BY id DESC)) / LAG(away_odd) OVER (PARTITION BY match_id ORDER BY id DESC) * 100 AS away_odd_change,
            ROW_NUMBER() OVER (PARTITION BY match_id ORDER BY id DESC) AS rn,
            COUNT(*) OVER (PARTITION BY match_id) AS cnt
        FROM (
            SELECT *,
                " . $odd_type . "_home_odd AS home_odd,
                " . $odd_type . "_away_odd AS away_odd
            FROM match_data
            WHERE type = '$type'
        ) AS subquery
    ) AS subquery
    JOIN teams AS teams_home ON subquery.home_team = teams_home.team_name
    JOIN teams AS teams_away ON subquery.away_team = teams_away.team_name
    WHERE subquery.rn = 2 AND subquery.cnt >= 2 AND DATE(subquery.match_time) >= '" . date('Y-m-d') . "' ORDER BY subquery.match_time ASC";
    $logopathpath = './team_logos/' . $type . '/';
    $res_buil = mysqli_query($conn, $sql);
    ?>
    <div class="masc">
        <?php
        while ($tickermatch = mysqli_fetch_assoc($res_buil)) {
            $home_odd_change = $tickermatch['home_odd_change'];
            $away_odd_change = $tickermatch['away_odd_change'];
            $home_abbr = $tickermatch['home_abbr'];
            $away_abbr = $tickermatch['away_abbr'];
            $home_logo = $logopathpath . $tickermatch['home_logo'];
            $away_logo = $logopathpath . $tickermatch['away_logo'];
            if ($home_odd_change > 0) {
                $colorH = 'green';
                $arrow_iconH = '<i class="fa-solid fa-caret-up"></i>';
                $home_odd_change = '+' . number_format($home_odd_change, 2) . '%';
            } elseif ($home_odd_change < 0) {
                $colorH = 'red';
                $arrow_iconH = '<i class="fa-solid fa-caret-down"></i>';
                $home_odd_change = number_format($home_odd_change, 2) . '%';
            } else {
                $colorH = '#808080';
                $arrow_iconH = '<>';
                $home_odd_change = '';
            }
            if ($away_odd_change > 0) {
                $colorA = 'green';
                $arrow_iconA = '<i class="fa-solid fa-caret-up"></i>';
                $away_odd_change = '+' . number_format($away_odd_change, 2) . '%';
            } elseif ($away_odd_change < 0) {
                $colorA = 'red';
                $arrow_iconA = '<i class="fa-solid fa-caret-down"></i>';
                $away_odd_change = number_format($away_odd_change, 2) . '%';
            } else {
                $colorA = '#808080';
                $arrow_iconA = '<>';
                $away_odd_change = '';
            }
        ?>
            <div class="iteams itemsdiv" data-id="<?= $tickermatch['match_id'] ?>">
                <span>
                    <?= date('d M, Y - h:i A', strtotime($tickermatch['match_time'] . ' -4 hours')) ?>
                </span>
                <div class="flex-div">
                    <div class="images-home">
                        <img class="ites-img1" src="<?= $home_logo ?>" alt="">
                    </div>
                    <div class="names">
                        <?= $home_abbr ?><span class="aabs" style="color:<?= $colorH ?>"><?= $arrow_iconH ?> <?= $home_odd_change ?></span>
                    </div>
                </div>
                <div class="flex-div">
                    <div class="images-home">
                        <img class="ites-img1" src="<?= $away_logo ?>" alt="">
                    </div>
                    <div class="names">
                        <?= $away_abbr ?><span class="aabs" style="color:<?= $colorA ?>"><?= $arrow_iconA ?> <?= $away_odd_change ?></span>
                    </div>
                </div>
            </div>
        <?php
        }
        ?>
    </div>
    <script>
        $('.masc').slick({
            slidesToShow: 7,
            slidesToScroll: 1,
            autoplay: true,
            autoplaySpeed: 0,
            speed: 8000,
            pauseOnHover: false,
            cssEase: 'linear',
            pauseOnFocus: false,
            pauseOnHover: false,
            pauseOnDotsHover: false,
            draggable: false,
        });
    </script>
    <script>
        $('.itemsdiv').click(function() {
            var slider_match_id = $(this).data('id');
            $('input[type="radio"][id="' + slider_match_id + '"]').click();
        });
    </script>
<?php
} elseif ($_POST['action'] == 'get_news') {
    $currentDate = date('Y-m-d');
    $del_sql = 'DELETE FROM news WHERE news_date != "' . $currentDate . '"';
    mysqli_query($conn, $del_sql);
    $sql = 'SELECT * FROM news WHERE news_date = "' . $currentDate . '"';
    $res_buil = mysqli_query($conn, $sql);
    if ($res_buil->num_rows == 0) {
        function curl_get($url)
        {
            $curl = curl_init();
            curl_setopt_array(
                $curl,
                array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_HTTPHEADER => array(
                        "cache-control: no-cache",
                        "content-type: application/json",
                        "User-Agent: Odds Chart"
                    ),
                )
            );
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                return false;
            } else {
                $res = json_decode($response, true);
                return $res;
            }
        }
        $api_key = '6a2f51bc603a45a2b669430a6c6586dc';
        $api_url = "https://newsapi.org/v2/top-headlines?country=us&category=sports&apiKey=$api_key";
        $news_response = curl_get($api_url);
        if (!empty($news_response['articles'])) {
            foreach ($news_response['articles'] as $news) {
                $src = mysqli_real_escape_string($conn, $news['source']['name']);
                $ath = mysqli_real_escape_string($conn, $news['author']);
                $tit = mysqli_real_escape_string($conn, $news['title']);
                $des = mysqli_real_escape_string($conn, $news['description']);
                $url = mysqli_real_escape_string($conn, $news['url']);
                $urlToImage = mysqli_real_escape_string($conn, $news['urlToImage']);
                $content = mysqli_real_escape_string($conn, $news['content']);
                $publishedAt = mysqli_real_escape_string($conn, $news['publishedAt']);
                $query = "INSERT INTO news (source, author, title, description, url, urlToImage, content, publishedAt, news_date) VALUES ('$src', '$ath', '$tit', '$des', '$url', '$urlToImage', '$content', '$publishedAt', '$currentDate')";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'sssssssss', $src, $ath, $tit, $des, $url, $urlToImage, $content, $publishedAt, $currentDate);
                $result = mysqli_stmt_execute($stmt);
            }
        }
    }
?>
    <div class="news-main-text">News</div>
    <div class="downslider">
        <?php
        while ($news = mysqli_fetch_assoc($res_buil)) {
            if ($news['urlToImage'] !== null && $news['urlToImage'] !== '') {
                $img_src = $news['urlToImage'];
            } else {
                $img_src = './images/no_img.jpg';
            }
        ?>
            <a href="<?= $news['url'] ?>" target="_blank" class="slider-div-down">
                <div>
                    
                    <h3 class="tetx-cenv">
                        <?= $news['title'] ?>
                    </h3>
                </div>
            </a>
        <?php
        }
        ?>
    </div>
    <script>
        $('.downslider').slick({
            speed: 12000,
            autoplay: true,
            autoplaySpeed: 0,
            centerMode: false,
            cssEase: 'linear',
            draggable: true,
            focusOnSelect: false,
            pauseOnFocus: false,
            pauseOnHover: true,
            slidesToShow: 1,
            slidesToScroll: 1,
            infinite: true,
            initialSlide: 1,
            arrows: false,
            buttons: false,
        });
    </script>
<?php
} else {
    die('Why are you here again..?');
}
?>