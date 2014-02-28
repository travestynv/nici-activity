<?php
// include phplot library
require_once 'lib/phplot-6.1.0/phplot.php';

//set output file name and path
$path = "images/temp";
$output_file = sprintf("%s/%s_%s.png", $path , $cv_course_id , $user['id']);

// build chart_array
#print_r($view_dates);
$plot_data= buildPlotArray($view_dates);
#print_r($plot_data);


// configure plot
#Start Chart
$plot = new PHPlot(800, 250);
$plot->SetImageBorderType('plain');

$plot->SetPlotType('bars');
$plot->SetDataType('text-data');
$plot->SetDataValues($plot_data);

# Main plot title:
$plot->SetTitle('14 day Activity chart');

# Make a legend for the 3 data sets plotted:
$plot->SetLegend(array('Views', 'Submissions (x5)'));

# Turn off X tick labels and ticks because they don't apply here:
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');

# output
$plot->SetIsInline(TRUE);
$plot->SetOutputFile($output_file);
$plot->DrawGraph();

printf('<img src="%s">',$output_file);
