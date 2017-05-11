<?php

class Gcharts {
    public $library_loaded = FALSE;
    public $create_div = TRUE;
    public $chart_div = NULL;
    public $class_chart_div = NULL;
    public $open_js_tag = TRUE;
    public $graphic_type = NULL;
    public $options = array();

    /**
     * Load Google charts library
     *
     * @return null|string
     */
    public function load_library() {
        if (!$this->library_loaded) {
            $this->library_loaded = TRUE;
            return '<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>';
        }
        return NULL;
    }

    /**
     * Set Google chart type
     *
     * @param int $type
     * @return bool
     */
    public function set_graphic_type($type = 0) {
        if ($type == 0) return false;

        $types = array(
            1 => 'ColumnChart',
            2 => 'LineChart',
            3 => 'PieChart',
            4 => 'AreaChart',
            5 => 'ScatterChart',
            6 => 'BarChart');

        $this->graphic_type = $types[$type];
        return true;
    }

    /**
     * Set Google chart options
     *
     * @param array $options
     * @return array|bool
     */
    public function set_options($options = array()) {
        if ((bool)!$options) {
            return array();
        }

        $this->options = $options;
        return true;
    }

    /**
     * Generate Google chart
     *
     * @param $data
     * @return bool|null|string
     */
    public function generate($data) {
        if ((bool)!$data) {
            return false;
        }

        if (is_null($this->chart_div)) {
            $key = $this->gerarkey(5);
            $this->chart_div = 'gchart_' . $key;
        }

        $js = NULL;

        $js .= $this->load_library() . "\n";

        if ($this->open_js_tag === TRUE) {
            $js .= '<script type="text/javascript">' . "\n";
        }

        // Load the Visualization API.
        $js .= 'google.charts.load("current", {packages:["corechart", "bar"]});' . "\n";

        // Set a callback to run when the Google Visualization API is loaded.
        $js .= 'google.charts.setOnLoadCallback(drawChart);' . "\n";

        $js .= 'function drawChart() {' . "\n";

        // Create our data table.
        $js .= 'var data = google.visualization.arrayToDataTable(' . $this->array_to_jsarray($data) . ');' . "\n";

        $js .= 'var view = new google.visualization.DataView(data);' . "\n";
        $js .= 'view.setColumns([0, 1, 
                                {   calc: "stringify",
                                    sourceColumn: 1,
                                    type: "string",
                                    role: "annotation" }]);' . "\n";

        // Generate the options.
        $js .= 'var options = ' . "\n";
        $js .= $this->array_to_jsobject($this->options);
        $js .= ';' . "\n";

        $js .= "var chart = new google.visualization." . $this->graphic_type . "(document.getElementById('" . $this->chart_div . "'));\n";

        // Generat printable version
        $js .= 'google.visualization.events.addListener(chart, "ready", function () {
                document.getElementById("gchart_' . $key . '_png").outerHTML = "<a href=" + chart.getImageURI() + " target=_blank>' . get_string('printable-version', 'report_ilduserstats') . '</a>";
                });' . "\n";

        $js .= 'chart.draw(view, options);' . "\n";
        $js .= '}' . "\n";

        if ($this->open_js_tag === TRUE) {
            $js .= '</script>' . "\n";
        }

        if ($this->create_div === TRUE) {
            $js .= '<div id="' . $this->chart_div . '" class="' . $this->class_chart_div . '" style="width: 100%; height: 500px;"></div>';
            $js .= '<div id="gchart_' . $key . '_png"></div>';
        }

        $this->clean();
        return $js;
    }

    /**
     * Convert array to google options
     *
     * @INPUT array:
     * $array = array('title' => 'My Title');
     * or
     * $array = array('title' => 'My Title','vAxis' => array('title' => 'Cups'));
     *
     * @OUTPUT string:
     * {title: 'title'}
     * or
     * {title: 'My Title',
     * vAxis: {title: 'Cups'}}
     *
     * @param array $array
     * @return string
     */
    private function array_to_jsobject($array = array()) {
        if ((bool)!$array) {
            return '{}';
        }

        $return = NULL;
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $return .= $k . ": " . $this->array_to_jsobject($v) . ",";
            } else {
                if (is_string($v)) {
                    $return .= $k . ": '" . addslashes($v) . "',";
                } else {
                    $return .= $k . ": " . $v . ",";
                }
            }
        }
        return '{' . trim($return, ',') . '}';
    }

    /**
     * Convert array to google charts data
     *
     * @INPUT array:
     * $array = array(array('Year', 'Sales', 'Expenses'),
     * array('2004',1000,400),
     * array('2005',1170,460),
     * array('2006',660,1120),
     * array('2007',1030,540));
     *
     * @OUTPUT string:
     *[['Year','Sales','Expenses'],['2004','1000','400'],['2005','1170','460'],['2006','660','1120'],['2007','1030','540']]
     *
     * @param array $array
     * @return string
     */
    private function array_to_jsarray($array = array()) {
        if ((bool)!$array) {
            return '[]';
        }

        $return = NULL;
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $return .= ',' . $this->array_to_jsarray($v);
            } else {
                if (is_string($v)) {
                    $return .= ",'" . addslashes($v) . "'";
                } else {
                    $return .= "," . $v;
                }
            }
        }

        return '[' . trim($return, ',') . ']';
    }

    public function clean() {
        $this->create_div = TRUE;
        $this->chart_div = NULL;
        $this->class_chart_div = NULL;
        $this->open_js_tag = TRUE;
        $this->graphic_type = NULL;
    }

    public function gerarkey($length = 40) {
        $key = NULL;
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRTWXYZ';
        for ($i = 0; $i < $length; ++$i) {
            $key .= $pattern{rand(0, 58)};
        }
        return $key;
    }

}