<?php

/**
 * External Web Service Template
 *
 * @package    localwstemplate
 * @copyright  2011 Moodle Pty Ltd (http://moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_design_ideas\prompt;
use block_design_ideas\gen_ai;
use block_design_ideas\base;

require_once($CFG->libdir . "/externallib.php");
require_once("$CFG->dirroot/config.php");


class block_design_ideas_final_course_summary extends external_api
{
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters()
    {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'Course id', VALUE_REQUIRED),
                'promptid' => new external_value(PARAM_INT, 'Prompt id', VALUE_REQUIRED)
            )
        );
    }

    /**
     * Displays course topics
     * @param int $course_id
     * @param int $prompt_id
     * @param int $section_id
     * @return array
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws restricted_context_exception
     */
    public static function execute($course_id, $prompt_id)
    {
        global $DB, $USER;

        //Parameter validation
        $params = self::validate_parameters(
            self::execute_parameters(),
            array(
                'courseid' => $course_id,
                'promptid' => $prompt_id
            )
        );

        //Context validation
        $context = \context_course::instance($course_id);
        self::validate_context($context);

        // Get modinfo
        $modinfo = get_fast_modinfo($course_id);
        $sections = $modinfo->get_section_info_all();
        $topics = '';
        $description = '';
        // Add section name and summary to content
        foreach ($sections as $section) {
            $topics .= $section->name . "\n";
            $topics .= $section->summary . "\n\n";
        }
        // get course record
        $course = $DB->get_record('course', ['id' => $course_id]);

        $prompt = 'Content: ' . $topics;
        $PROMPT = new prompt($prompt_id);
        // Get prompt
        $prompt .= $PROMPT->get_prompt();
        // Make the call
        $content = gen_ai::make_call($context, strip_tags($prompt), $course->lang);

        $response = [];
        $response['content'] = base::convert_string_to_html_list($content);

        return $response;
    }

    public static function execute_returns()
    {
        return new external_single_structure(
            array(
                'content' => new external_value(PARAM_RAW, 'Content'),
            )
        );
    }


    // Create course topics
    public static function create_parameters()
    {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'Course id', VALUE_REQUIRED),
                'content' => new external_value(PARAM_RAW, 'JSON of subjects', VALUE_REQUIRED),
            )
        );
    }

    public static function create( $course_id, $content)
    {
        global $DB, $USER;

        //Parameter validation
        $params = self::validate_parameters(
            self::create_parameters(),
            array(
                'courseid' => $course_id,
                'content' => $content
            )
        );

        //Context validation
        $context = \context_course::instance($course_id);
        self::validate_context($context);

        // Save course summary
        $DB->set_field('course', 'summary', $content, array('id' => $course_id));

        return true;
    }

    public static function create_returns()
    {
        return new external_value(PARAM_BOOL, 'Status');
    }
}