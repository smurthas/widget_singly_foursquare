$(document).ready(function() {
    states.sort(function(a, b) {
        if(a[1] > b[1])
            return -1;
        else if(a[1] < b[1])
            return 1;
        return 0;
    });
    var total = 0;
    for(var i in states)
        total += states[i][1];
    var shortArray = [];
    for(var i in states) {
        if(i < MAX_BINS) {
            shortArray[i] = states[i];
        } else {
            if(!shortArray[MAX_BINS])
                shortArray[MAX_BINS] = ['Other', 0];
            shortArray[MAX_BINS][1] += states[i][1];
        }
    }
  var statesPlot = jQuery.jqplot ('widget_singly_foursquare_states_chart', [shortArray], 
    { 
      seriesDefaults: {
        // Make this a pie chart.
        renderer: jQuery.jqplot.PieRenderer, 
        rendererOptions: {
          // Put data labels on the pie slices.
          // By default, labels show the percentage of the slice.
          showDataLabels: true
        }
      }, 
      legend: { show:true, location: 'n' }
    }
  );
});