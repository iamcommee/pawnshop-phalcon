$(function () {

    var url = window.location.pathname;
    var split = url.split("/");
    if (split[3] == '') {
        var date_time = new Date();
        var year = date_time.getFullYear();
        var start_month = date_time.getMonth() + 1;
        var end_month = date_time.getMonth() + 1;
    }

    $('form').on('click', 'button.confirm-search', function (e) {
        e.preventDefault();
        var start_month = $('.start_month').val();
        var end_month = $('.end_month').val();
        var year = $('.year').val();
        $('.chart_wrap').addClass('d-none');
        $('.spinner').removeClass('d-none');
        loadChart(year, start_month, end_month)
    });

    var options = {
        chart: {
            renderTo: 'chart',
            type: 'column',
            style: {
                fontFamily: 'Sarabun'
            }
        },
        lang: {
            drillUpText: 'กลับไปหน้าหลัก',
        },
        title: {
            text: 'สถิติประจำปี ' + (parseInt($('.year').val()) + parseInt(543))
        },
        subtitle: {
            text: 'คลิกแต่ละคอลัมน์เพื่อดูรายละเอียด'
        },
        xAxis: {
            type: 'category'
        },
        yAxis: {
            title: {
                text: 'ยอดเงิน'
            }
        },
        legend: {
            layout: 'horizontal',
            align: 'center',
            verticalAlign: 'top'
        },
        plotOptions: {
            series: {
                borderWidth: 0,
                dataLabels: {
                    enabled: true,
                    format: '<span>{point.y:,.0f} บาท</span>'
                }
            }
        },
        tooltip: {
            headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
            pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:,.0f} บาท </b><br/>'
        },
        series: {},
        drilldown: {
            series: {}
        },
    };


    function loadChart(year, start_month, end_month) {
        $.ajax({
            type: "GET",
            dataType: "json",
            url: "server_processing/" + year + "/" + start_month + "/" + end_month,
            success: function (strRetorno) {
                options.series = strRetorno.month;
                options.drilldown.series = strRetorno.date;
                var chart = new Highcharts.Chart(options);
                $('.chart_wrap').removeClass('d-none');
                $('.spinner').addClass('d-none');
                // console.log('options', options)
                // ! Fix comma in number
                Highcharts.setOptions({
                    lang: {
                        thousandsSep: ','
                    }
                });
            },
            error: function (txt) {

            }
        });


    }

    loadChart(year, start_month, end_month);

    // Set type
    $.each(['line', 'column', 'spline', 'area', 'areaspline', 'scatter', 'pie'], function (i, type) {
        $('#' + type).click(function () {
            options.chart.type = type;
            $('#chart').highcharts(options);
        });
    });

});