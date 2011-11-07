function binSort(a, b) {
    if(a[1] > b[1])
        return -1;
    else if(a[1] < b[1])
        return 1;
    return 0;
}

// var others;
function createBins(arr, bins) {
    if(!bins) bins = MAX_BINS / 2;
    var total = 0;
    for(var i in arr)
        total += arr[i][1];
    var shortArray = [];
    // others = [];
    for(var i in arr) {
        total += arr[i][1];
        if(i < bins) {
            shortArray[i] = arr[i];
        } else {
            // others.push(arr[i]);
            if(!shortArray[bins])
                shortArray[bins] = ['Other', 0];
            shortArray[bins][1] += arr[i][1];
        }
    }
    if(shortArray.length === bins && shortArray[bins][1] > shortArray[bins-1][1] && bins < MAX_BINS)
        return createBins(arr, bins + 1);
    return shortArray;
}

function doPlot(contaner, title, arr) {
    arr.sort(binSort);
    doHighChart(contaner, title, createBins(arr));
}

$(document).ready(function() {
    doPlot('widget_singly_foursquare_chart', 'Checkins by State', all);
    // doPlot('widget_singly_foursquare_cities_chart', 'Cities', cities);
});


function doJQPlot(contaner, titleText, data) {
    jQuery.jqplot (contaner, [data], { 
        seriesDefaults: {
            // Make this a pie chart.
            renderer: jQuery.jqplot.PieRenderer, 
            rendererOptions: {
                // Put data labels on the pie slices.
                // By default, labels show the percentage of the slice.
                showDataLabels: true
            }
        }
        , legend: { show:true, location: 'e' }
    });
}

function doHighChart(container, titleText, data) {
    chart = new Highcharts.Chart({
        chart: {
            renderTo: container,
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false
        },
        title: {
            text: titleText
        },
        tooltip: {
            formatter: function() {
                return '<b>'+ this.point.name +'</b>: '+ this.y + ' checkins';
            }
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: false, // true,
                    color: '#000000',
                    connectorColor: '#000000',
                    formatter: function() {
                        return '<b>'+ this.point.name +'</b>: '+ this.percentage.toPrecision(2) +' %';
                    }
                }//,
                // showInLegend: true
            }
        },
        series: [{
            type: 'pie',
            name: titleText,
            data: data,
            point: {
                events: {
                    click: function() {
                        var key = this.config[0];
                        var arr;
                        var msg = 'Checkins in ' + key;
                        if(states[key])
                            arr = states[key];
                        else if(cities[key]) {
                            arr = cities[key];
                            msg = key;
                        }
                        doPlot('widget_singly_foursquare_chart', msg, arr);
                        // else //others
                        //     doPlot('widget_singly_foursquare_chart', 'Other', others);
                    }
                }
            }
        }]
    });
}