$('[name="type"]').on('change', function () {
    get_teams();
    get_ticker();
});
$(document).ready(function () {
    get_teams();
    get_ticker();
    get_news();
});

function get_news() {
    $.ajax({
        type: "POST",
        url: "ajax.php",
        data: {
            'action': 'get_news'
        },
        success: function (res) {
            $('#news').html(res);

        }
    });
}

function get_ticker() {
    $.ajax({
        type: "POST",
        url: "ajax.php",
        data: {
            'type': $('[name="type"]').val(),
            'odd_type': $('[name="odd_type"]').val(),
            'action': 'get_ticker'
        },
        success: function (res) {
            $('#ticker').html(res);

        }
    });
}

function get_teams() {
    $.ajax({
        type: "POST",
        url: "ajax.php",
        data: {
            'type': $('[name="type"]').val(),
            'action': 'get_team'
        },
        success: function (res) {
            $('#match_id').html(res);
            show_graph($('.rangeSelect:checked').val());
        }
    });
}

function show_graph(range) {
    var formdata = $('#match_form').serialize();
    formdata += '&range=' + range;
    $.ajax({
        type: "POST",
        url: "ajax.php",
        data: formdata,
        success: function (res) {
            var data = JSON.parse(res);
            var homedata = data.homedata.map(Number);
            var awaydata = data.awaydata.map(Number);
            var timestamps = data.timestamps;
            var type = data.type;
            var oddtype = data.oddtype;
            var matchbtw = data.matchbtw;
            var match_id = data.match_id;
            var hometeam = $('#' + match_id).data('homefn');
            var awayteam = $('#' + match_id).data('awayfn');
            lineChart(homedata, awaydata, timestamps, type, oddtype, hometeam, awayteam);
            $('#match_type').html(type);
            $('#odd_type').html(oddtype);
            $('#home_logo').attr('src', $('#' + match_id).data('homel'));
            $('#away_logo').attr('src', $('#' + match_id).data('awayl'));
            $('#home_name').html(hometeam);
            $('#away_name').html(awayteam);
            $('#match_time').html($('#' + match_id).data('match_time'));
        }
    });
}
function lineChart(homedata, awaydata, timestamps, type, oddtype, hometeam, awayteam) {
    const backgroundColor = 'white';
    const textcolor = 'black';
    const homecolor = 'green';
    const awaycolor = 'red';
    Highcharts.chart('chartContainer', {
        chart: {
            type: 'line',
            backgroundColor: backgroundColor,
            width: 1000
        },
        title: {
            text: 'Latest Odds',
            style: {
                color: textcolor
            }
        },
        xAxis: {
            type: 'datetime',
            categories: timestamps,
            labels: {
                style: {
                    color: textcolor
                }
            },
            title: {
                text: '',
                style: {
                    color: textcolor
                }
            }
        },
        yAxis: {
            title: {
                text: '',
                style: {
                    color: textcolor
                }
            },
            labels: {
                style: {
                    color: textcolor
                }
            }
        },
        plotOptions: {
            series: {
                dataLabels: {
                    style: {
                        color: textcolor
                    }
                }
            },
            line: {
                dataLabels: {
                    style: {
                        color: textcolor
                    },
                    enabled: true
                },
                lineWidth: 3,
                enableMouseTracking: true
            }
        },
        legend: {
            itemStyle: {
                color: textcolor
            }
        },
        series: [{
            name: hometeam,
            data: homedata,
            color: homecolor
        }, {
            name: awayteam,
            data: awaydata,
            color: awaycolor
        }]
    });
}
const Radio1 = document.querySelector('.days-radio')
const Radio2 = document.querySelector('.week-radio')
const Radio3 = document.querySelector('.months-radio')
const Radio4 = document.querySelector('.year-radio')
const btnRadios1 = document.getElementById("days-btn")
const btnRadios2 = document.getElementById("weak-bnt")
const btnRadios3 = document.getElementById("month-bnt")
const btnRadios4 = document.getElementById("days-bnt")
btnRadios1.onclick = function () {
    Radio1.click();
    btnRadios1.classList.add('font-w')
    btnRadios2.classList.remove('font-w')
    btnRadios3.classList.remove('font-w')
    btnRadios4.classList.remove('font-w')
}
btnRadios2.onclick = function () {
    Radio2.click();
    btnRadios2.classList.add('font-w')
    btnRadios1.classList.remove('font-w')
    btnRadios3.classList.remove('font-w')
    btnRadios4.classList.remove('font-w')
}
btnRadios3.onclick = function () {
    Radio3.click();
    btnRadios3.classList.add('font-w')
    btnRadios1.classList.remove('font-w')
    btnRadios2.classList.remove('font-w')
    btnRadios4.classList.remove('font-w')
}
btnRadios4.onclick = function () {
    Radio4.click();
    btnRadios4.classList.add('font-w')
    btnRadios1.classList.remove('font-w')
    btnRadios3.classList.remove('font-w')
    btnRadios2.classList.remove('font-w')
}
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
$('.itemsdiv').click(function () {
    var slider_match_id = $(this).data('id');
    $('input[type="radio"][id="' + slider_match_id + '"]').click();
});
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