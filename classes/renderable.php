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

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/report/ilduserstats/classes/report_ilduserstats.php');

/**
 * Userstats report renderable class.
 *
 * @package    report_ilduserstats
 */
class report_ilduserstats_renderable implements renderable {
    /**
     * @var string Stores users activity events return from google charts.
     */
    public $activeUsers;
    /**
     * @var string User map.
     */
    public $activeUsersMap;
    /**
     * @var int Stores selected period.
     */
    public $selectedPeriod;
    /**
     * @var int Stores selected chart type.
     */
    public $selectedChartType;
    /**
     * @var int Startdate.
     */
    public $selectedPeriodFrom;
    /**
     * @var int Enddate.
     */
    public $selectedPeriodTo;

    /**
     * report_ilduserstats_renderable constructor.
     * @param $period
     */
    public function __construct($period, $from, $to) {
        $this->selectedPeriod = $period;
        $this->selectedPeriodFrom = $from;
        $this->selectedPeriodTo = $to;
    }

    /**
     * Displays period related graph charts.
     */
    public function get_gchart_data() {
        $graphreport = new report_ilduserstats();
        $this->activeUsers = $graphreport->get_active_users_chart($this->selectedPeriod, $this->selectedChartType, $this->selectedPeriodFrom, $this->selectedPeriodTo);
    }

    /**
     * Displays period related csv-export.
     */
    public function get_export_data() {
        $export_data = new report_ilduserstats();
        $this->activeUsers = $export_data->get_active_users_export($this->selectedPeriod, $this->selectedPeriodFrom, $this->selectedPeriodTo);
    }

    /**
     * Setter chart type.
     *
     * @param $type
     */
    public function set_chart_type($type) {
        $this->selectedChartType = $type;
    }

    /**
     * Display google map
     */
    public function get_gmap_data() {
        $gmap = new report_ilduserstats();
        $this->activeUsersMap = $gmap->get_user_map();
    }

}