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


class block_design_ideas_learning_outcomes extends external_api
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

        // Get information from query string
        $course = $DB->get_record('course', array('id' => $course_id), '*', MUST_EXIST);

        // Get all course topics.
        $modinfo = get_fast_modinfo($course->id);
        $sections = $modinfo->get_section_info_all();
        $content = $course->summary . "\nTopics:\n";
        foreach ($sections as $section) {
            // Do something with each section
            // For example, you can access the section's name and summary like this:
            $content .= $section->name . "\n";
            $content .= $section->summary . "\n";
        }
        // Get prompt
        $PROMPT = new prompt($prompt_id);

        $prompt = 'Summary: ' . $content . "\n\n Q: ";
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

        // Get course
        $course = $DB->get_record('course', array('id' => $course_id), '*', MUST_EXIST);

        $name = get_string('learning_outcomes', 'block_design_ideas');
        // Create learning outcomes page.
        gen_ai::add_page_module(
            $name,
            trim($content),
            $course_id,
            0
        );

        return true;
    }

    public static function create_returns()
    {
        return new external_value(PARAM_BOOL, 'Status');
    }
}