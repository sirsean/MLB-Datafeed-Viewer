<?php

class Player {
    function __construct($name, $stats) {
        $this->name = $name;
        $this->stats = $stats;
    }

    function addStat($day, $stat) {
        $this->stats[$day] = $stat;
    }

    function getStatsFor($day) {
        if (array_key_exists($day, $this->stats)) {
            return $this->stats[$day];
        } else {
            return "null";
        }
    }
}

$ini = parse_ini_file(dirname(__FILE__) . "/config.ini");

$filedate = $_GET["file"];
$filename = dirname(__FILE__) . $ini["path"] . "moving_average_" . $filedate . ".xml";

if (!file_exists($filename)) {
    echo "Not found";
    exit;
}

$players = array();
$xml = simplexml_load_file($filename);
foreach ($xml->batter as $batter) {
    $player = new Player((string)$batter->attributes()->name, array());
    foreach ($batter->stat as $stat) {
        $player->addStat((string)$stat->attributes()->date, (string)$stat->attributes()->value);
    }
    array_push($players, $player);
}

$days = array();
$now = time();
$time = strtotime("4/1/2011");
$current = $time;
while ($current < $now) {
    array_push($days, strftime("%m/%d/%Y", $current));
    $current += 86400;
}

?>
<!--
You are free to copy and use this sample in accordance with the terms of the
Apache license (http://www.apache.org/licenses/LICENSE-2.0.html)
-->

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>
      Google Visualization API Sample
    </title>
    <script type="text/javascript" src="http://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load('visualization', '1', {packages: ['corechart']});
    </script>
    <script type="text/javascript">
      function drawVisualization() {
        // Create and populate the data table.
        var data = new google.visualization.DataTable();
        data.addColumn("string", "Date");
        <?php
        foreach ($players as $player) {
        ?>
            data.addColumn("number", "<?php echo $player->name ?>");
        <?php } ?>
        var row = null;
        <?php foreach ($days as $day) { ?>
            row = ["<?php echo $day ?>"];
            <?php foreach ($players as $player) { ?>
                row.push(<?php echo $player->getStatsFor($day) ?>);
            <?php } ?>
            data.addRow(row);
        <?php } ?>
       
        // Create and draw the visualization.
        new google.visualization.LineChart(document.getElementById('visualization')).
            draw(data, {curveType: "function",
                        width: 1024, height: 768,
                        vAxis: {maxValue: 1.0, minValue: 0.1},
                        interpolateNulls: true}
                );
      }
      

      google.setOnLoadCallback(drawVisualization);
    </script>
  </head>
  <body style="font-family: Arial;border: 0 none;">
    <h1>wOBA 10-day Moving Average</h1>
    <div id="visualization" style="width: 1024px; height: 768px;"></div>
    <div>
        <p>Created by <a href="http://twitter.com/sirsean">Sean Schulte</a>.</p>
        <ul>
            <li><a href="https://github.com/sirsean/MLB-Datafeed-Parser">MLB Datafeed Parser</a></li>
            <li><a href="https://github.com/sirsean/MLB-Datafeed-Viewer">MLB Datafeed Viewer</a></li>
        </ul>
    </div>
  </body>
</html>

