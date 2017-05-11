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

require('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/course/lib.php');

require_login();
admin_externalpage_setup('report_ilduserstats');

$context = context_system::instance();
require_capability('report/ilduserstats:view', $context);

$PAGE->set_context($context);
$PAGE->set_url('/report/ilduserstats/index.php');
$PAGE->set_title(get_string('pluginname', 'report_ilduserstats'));
$PAGE->set_heading(get_string('pluginname', 'report_ilduserstats'));
$PAGE->set_pagelayout('report');

$period = optional_param('period', 0, PARAM_INT);
$chart_type = optional_param('chart', 0, PARAM_INT);
$export = optional_param('export', 0, PARAM_TEXT);
$from_day = optional_param('from_day', 0, PARAM_INT);
$from_month = optional_param('from_month', 0, PARAM_INT);
$from_year = optional_param('from_year', 0, PARAM_INT);
$to_day = optional_param('to_day', 0, PARAM_INT);
$to_month = optional_param('to_month', 0, PARAM_INT);
$to_year = optional_param('to_year', 0, PARAM_INT);

/**
 * Set from to 1.January 2015 if empty.
 */
if ($from_day !== 0 && $from_month !== 0 && $from_year !== 0) {
    $from = strtotime($from_day . '.' . $from_month . '.' . $from_year);
} else {
    $from = 1420066800;
}

/*
 * Get timestamp.
 */
$to = strtotime($to_day . '.' . $to_month . '.' . $to_year);

$renderable = new report_ilduserstats_renderable($period, $from, $to);
$renderer = $PAGE->get_renderer('report_ilduserstats');

/**
 * CSV-Export. Exit if completed
 */
if (!empty($period) && !empty($export)) {
    ob_start();
    echo $renderer->render($renderable);
    ob_end_clean();
    echo $renderer->report_generate_export();
}

/**
 * Render Page.
 */
echo $OUTPUT->header();
$renderable->set_chart_type($chart_type);
echo $renderer->render($renderable);

if (!empty($period) && !empty($chart_type)) {
    echo $renderer->report_generate_chart();
}

echo $renderer->report_generate_gmap();

echo $OUTPUT->footer();