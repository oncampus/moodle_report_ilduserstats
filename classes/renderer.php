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

/**
 * Userstats report renderer class.
 *
 * @package    report_ilduserstats
 */
class report_ilduserstats_renderer extends plugin_renderer_base {
    protected $renderable;

    /**
     * Renderer constructor.
     *
     * @param report_ilduserstats_renderable $renderable ilduserstats report renderable instance.
     */
    protected function render_report_ilduserstats(report_ilduserstats_renderable $renderable) {
        $this->renderable = $renderable;
        $this->report_selector_form();
    }

    /**
     * This function is used to generate and display period filter.
     */
    public function report_selector_form() {
        $renderable = $this->renderable;
        $selectedPeriod = $renderable->selectedPeriod;
        $selectedChartType = $renderable->selectedChartType;
        $selectedPeriodFrom = $renderable->selectedPeriodFrom;
        $selectedPeriodTo = $renderable->selectedPeriodTo;

        $periods = array(
            0 => get_string('period', 'report_ilduserstats'),
            1 => get_string('day', 'report_ilduserstats'),
            2 => get_string('kw', 'report_ilduserstats'),
            3 => get_string('month', 'report_ilduserstats'));

        $chart_types = array(
            0 => get_string('chart-type', 'report_ilduserstats'),
            1 => 'ColumnChart',
            2 => 'LineChart',
            3 => 'PieChart',
            4 => 'AreaChart',
            5 => 'ScatterChart',
            6 => 'BarChart');

        echo html_writer::start_tag('form', array('class' => 'userstats-form', 'action' => 'index.php', 'method' => 'post'));
        echo html_writer::start_div();
        echo '<h3>Aktive Teilnehmer</h3>';
        echo html_writer::empty_tag('br');
        echo html_writer::select($periods, 'period', $selectedPeriod, false);
        echo html_writer::select($chart_types, 'chart', $selectedChartType, false);
        echo html_writer::empty_tag('br');
        echo html_writer::label('Vom: ', 'from_day');
        echo html_writer::select_time('days', 'from_day', $selectedPeriodFrom);
        echo html_writer::select_time('months', 'from_month', $selectedPeriodFrom);
        echo html_writer::select_time('years', 'from_year', $selectedPeriodFrom);
        echo html_writer::empty_tag('br');
        echo html_writer::label('Bis: ', 'to_day');
        echo html_writer::select_time('days', 'to_day', $selectedPeriodTo);
        echo html_writer::select_time('months', 'to_month', $selectedPeriodTo);
        echo html_writer::select_time('years', 'to_year', $selectedPeriodTo);
        echo html_writer::empty_tag('br');
        echo html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string('show', 'report_ilduserstats')));
        echo html_writer::empty_tag('input', array('name' => 'export', 'type' => 'submit', 'value' => get_string('export', 'report_ilduserstats')));
        echo html_writer::end_div();
        echo html_writer::end_tag('form');
    }

    /**
     * Generate chart
     */
    public function report_generate_chart() {
        $renderable = $this->renderable;
        $renderable->get_gchart_data();

        echo $renderable->activeUsers;
    }

    /**
     * Generate export
     */
    public function report_generate_export() {
        $renderable = $this->renderable;
        $renderable->get_export_data();

        echo $renderable->activeUsers;
    }

    /**
     * Generate Google Maps
     */
    public function report_generate_gmap() {
        $renderable = $this->renderable;
        $renderable->get_gmap_data();

        echo $renderable->activeUsersMap;
    }
}