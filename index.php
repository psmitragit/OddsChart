<!DOCTYPE html>
<html>
<head>
    <title>ODD Chart</title>
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/slick.css">
    <link rel="stylesheet" href="./css/slick-theme.css">
  
</head>
<body>
    <div class="container">
        <div id='ticker' class="slickdisd">
        </div>
        <div class='team_main_container'>
            <div class="chart-main-box">
                <div id="output">
                    <div class="team-box">
                        <div class="all-team-box">
                            <img id='home_logo' width='80'>
                            <span id='home_name'></span>
                        </div>
                        <p class="vrses">VS</p>
                        <div class="all-team-box">
                            <span id='away_name'></span>
                            <img id='away_logo' width='80'>
                        </div>
                    </div>
                    <div class="time-of-mathch">
                        <p id='match_time'></p>
                    </div>
                    <div class="optn-slct">
                        <span id='match_type'></span>
                        <span id='odd_type'></span>
                    </div>
                </div>
                <figure class="highcharts-figure">
                    <div id="rangeSelector">
                        <span class="zoom-text">Zoom</span>
                        <input class="rangeSelect days-radio displaynone" type="radio" name="rangeSelect" value='t' onclick="show_graph($(this).val());">
                        <input class="rangeSelect week-radio displaynone" type="radio" name="rangeSelect" value='w' onclick="show_graph($(this).val());">
                        <input checked class="rangeSelect months-radio displaynone" type="radio" name="rangeSelect" value='m' onclick="show_graph($(this).val());">
                        <input class="rangeSelect year-radio displaynone" type="radio" name="rangeSelect" value='y' onclick="show_graph($(this).val());">
                        <button class="months" id="days-btn">1d</button>
                        <button class="months" id="weak-bnt">1w</button>
                        <button class="months font-w" id="month-bnt">1m</button>
                        <button class="months" id="days-bnt">1y</button>
                    </div>
                    <div id="chartContainer" class="chart-box"></div>
                </figure>
            </div>
            <form id='match_form' class="match-form-class">
                <input type="hidden" name="action" value='get_chart'>
                <div class="slct-box">
                    <select name="type" id="">
                        <option value="NFL">NFL</option>
                        <!-- <option value='MLS'>MLS</option> -->
                        <!-- <option value='ATP Wimbledon'>ATP Wimbledon</option> -->
                        <!-- <option value='WTA Wimbledon'>WTA Wimbledon</option> -->
                    </select>
                    <select name="odd_type" id="" onchange="show_graph($('.rangeSelect:checked').val());get_ticker();">
                        <option value="h2h">H2H</option>
                        <option value='spreads'>Spreads</option>
                    </select>
                </div>
                <div id='match_id' class="match-slct">
                </div>
            </form>
        </div>
        <div class='news-container'>
            <div id='news'></div>
        </div>
  
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <script src="./js/slick.min.js"></script>
    <script src="./js/script.js"></script>
</body>
</html>