<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    report_ilduserstats
 * @copyright  2017 Fachhochschule Lübeck ILD
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/report/ilduserstats/lib/gcharts.php');

class report_ilduserstats extends Gcharts {
    /**
     * Generate Google Chart
     *
     * @param $period
     * @param $chart_type
     * @return mixed Google Chart
     */
    public function get_active_users_chart($period, $chart_type, $from, $to) {
        $this->set_graphic_type($chart_type);
        $data = $this->get_active_users_data($period, $from, $to);

        return $this->generate($data);
    }

    /**
     * Create CSV-File
     *
     * @param $period
     */
    public function get_active_users_export($period, $from, $to) {
        $filename = "userstats.csv";

        header("Content-Type: application/csv");
        header("Content-Disposition: attachment; filename={$filename}");

        $data = $this->get_active_users_data($period, $from, $to);

        $fp = fopen('php://output', 'w');
        fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));

        foreach ($data as $field) {
            fputcsv($fp, $field, ';');
        }

        fclose($fp);
        exit;
    }

    /**
     * Get data from database
     *
     * @param $period
     * @return array
     */
    private function get_active_users_data($period, $from, $to) {
        global $DB;

        setlocale(LC_TIME, 'de_DE.UTF8');

        $to = strtotime('+1 day', $to);

        $records = $DB->get_records_sql('SELECT firstaccess FROM {user} WHERE deleted = 0 AND firstaccess >= ? AND firstaccess <= ? ORDER BY firstaccess ASC', array($from, $to));
        $records_data = $data = array();

        switch ($period) {
            case 1:
                $date_format = '%d.%m.%Y';
                $heading = array(get_string('day', 'report_ilduserstats'), get_string('member', 'report_ilduserstats'));
                break;
            case 2:
                $date_format = '%W';
                $heading = array(get_string('kw', 'report_ilduserstats'), get_string('member', 'report_ilduserstats'));
                break;
            case 3:
                $date_format = '%b %y';
                $heading = array(get_string('month', 'report_ilduserstats'), get_string('member', 'report_ilduserstats'));
        }

        foreach ($records as $record) {
            if ($record->firstaccess != 0) {
                $date = strftime($date_format, $record->firstaccess);

                if ($period == 2) {
                    $year = strftime('%y', $record->firstaccess);
                    $date .= '. KW ' . $year;
                }

                if (!array_key_exists($date, $records_data)) {
                    $records_data[$date] = 1;
                } else {
                    $records_data[$date] += 1;
                }
            }
        }

        array_push($data, $heading);
        $total_value = 0;
        foreach ($records_data as $key => $value) {
            $total_value += $value;
            array_push($data, array($key, $total_value));
        }

        return $data;
    }

    public function get_user_map() {
        global $DB;

        $total_users = array();

        $users = $DB->get_records('block_online_users_map');
        $system_users = $DB->get_records_sql('SELECT id FROM {user} WHERE deleted = 0');
        $active_users = $DB->get_records_sql('SELECT id FROM {user} WHERE deleted = 0 AND firstaccess NOT LIKE 0');

        foreach ($users as $user) {
            $latlong = $user->lat . '$' . $user->lng;

            if (!empty($total_users[$latlong])) {
                $count = $total_users[$latlong]['count'];

                $total_users[$latlong] = array('lat' => $user->lat, 'lng' => $user->lng, 'count' => $count + 1);
            } else {
                $total_users[$latlong] = array('lat' => $user->lat, 'lng' => $user->lng, 'count' => 1);
            }
        }

        $output = '<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3"></script>';
        $output .= '<script>
google.maps.event.addDomListener(window, "load", function () {
    initMap();
});

function initMap() {
    var myLatLng = {lat: 51.108, lng: 10.646};
    var markers = ' . json_encode($total_users) . '

    map = new google.maps.Map(document.getElementById("map"), {
        zoom: 5,
        center: myLatLng
    });
        
    for(var key in markers) {
        var m = markers[key];
        
        marker = new google.maps.Marker({
            position: {lat: parseFloat(m.lat), lng: parseFloat(m.lng)},
            map: map,
            icon: "https://mooin.oncampus.de/report/ilduserstats/icons/online.png",
            title: String(m.count)
        });
    }
};
</script>';

        $output .= '<hr><h3>Teilnehmerkarte</h3><p>TN gesamt: ' . count($system_users) . '</p><p>TN aktiv: ' . count($active_users) . '</p><p>TN mit Adresse: ' . count($users) . '</p><div id="map" style="height:800px"></div>';

        return $output;
    }
}