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

function doPlot(args) {
    args.originalData.sort(binSort);
    args.data = createBins(args.originalData);
    doHighChart(args);
}

$(document).ready(function() {
    doPlot({
        container: 'widget_singly_foursquare_chart', 
        titleText: 'Checkins by State',
        originalData: all
    });
});

var plotStack = [];
function doHighChart(args) {
    var container = args.container;
    var titleText = args.titleText;
    var data = args.data;
    var subtitle = args.subtitle;
    chart = new Highcharts.Chart({
        chart: {
            renderTo: container,
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false
        },
        title: {
            text: titleText,
            margin:8
        },
        subtitle: {
            text: subtitle,
            align: 'left',
            y:15
        },
        tooltip: {
            formatter: function() {
                return '<b>'+ this.point.name +'</b>: '+ this.y + ' checkins';
            }
        },
        plotOptions: {
            pie: {
                size:'85%',
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
                        plotStack.push(args);
                        var key = this.config[0];
                        var arr;
                        var msg = 'Checkins in ' + key;
                        var subtitle = '<a href="javascript:doPlot(plotStack.pop());">&lt;&lt;</a>';
                        if(states[key])
                            arr = states[key];
                        else if(cities[key]) {
                            arr = cities[key];
                            msg = key;
                        }
                        var newArgs = {
                            container: 'widget_singly_foursquare_chart', 
                            titleText: msg,
                            originalData: arr,
                            subtitle:subtitle
                        }
                        doPlot(newArgs);
                        // else //others
                        //     doPlot('widget_singly_foursquare_chart', 'Other', others);
                    }
                }
            }
        }],
        credits: {
            text: "powered by Singly",
            href: "http://singly.com"
        }
    });
}