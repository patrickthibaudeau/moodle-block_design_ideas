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
use aiplacement_editor\external;

require_once($CFG->libdir . "/externallib.php");
require_once("$CFG->dirroot/config.php");


class block_design_ideas_course_topics extends external_api
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
                'promptid' => new external_value(PARAM_INT, 'Prompt id', VALUE_REQUIRED),
                'number_of_topics' => new external_value(PARAM_INT, 'Number of topics', VALUE_OPTIONAL, 13),
            )
        );
    }

    /**
     * Displays course topics
     * @param int $course_id
     * @param int $prompt_id
     * @return array
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws restricted_context_exception
     */
    public static function execute($course_id, $prompt_id, $number_of_topics = 13)
    {
        global $DB, $USER;

        //Parameter validation
        $params = self::validate_parameters(
            self::execute_parameters(),
            array(
                'courseid' => $course_id,
                'promptid' => $prompt_id,
                'number_of_topics' => $number_of_topics,
            )
        );

        //Context validation
        $context = \context_course::instance($course_id);
        self::validate_context($context);

        $course = $DB->get_record('course', array('id' => $course_id), '*', MUST_EXIST);

        $PROMPT = new prompt($prompt_id);

        $course_summary = strip_tags($course->summary);
        $course_summary = str_replace(['<br>', '<br />'], "\n", $course_summary);
        $prompt = 'Summary: ' . $course_summary . "\n\n Q: ";

        // Add prompt
        $prompt .= $PROMPT->get_prompt();
        // Replace number of topics
        $prompt = str_replace('[number_of_topics]', $number_of_topics, $prompt);

        $content = gen_ai::make_call($context, $prompt, $course->lang, true);

        $topics = [];
        $topics['data'] = [];
        foreach ($content as $topic) {
            $topics['data'][] = [
                'name' => $topic->name,
                'summary' => $topic->summary,
            ];
        }
        return $topics;
    }

    public static function execute_returns()
    {
        return new external_single_structure(
            array(
                'data' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_TEXT, 'Topic name'),
                            'summary' => new external_value(PARAM_TEXT, 'Topic summary'),
                        )
                    )
                ),
            )
        );
    }


    // Create course topics
    public static function create_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'Course id', VALUE_REQUIRED),
                'topics' => new external_value(PARAM_RAW, 'JSON of Topics', VALUE_REQUIRED),
                'replace' => new external_value(PARAM_TEXT, 'Replace topics or append', VALUE_OPTIONAL,
                    'all')
            )
        );
    }

    public static function create($course_id, $topics, $replace = 'all') {
        global $DB, $USER;

        //Parameter validation
        $params = self::validate_parameters(
            self::create_parameters(),
            array(
                'courseid' => $course_id,
                'topics' => $topics,
                'replace' => $replace
            )
        );

        //Context validation
        $context = \context_course::instance($course_id);
        self::validate_context($context);

        // Get all sections for this course
        $course_sections = $DB->get_records(
            'course_sections',
            array('course' => $course_id),
            'section');

        $course_sections = array_values($course_sections);

        $topics = json_decode($topics);
        // Convert topics to an array
        if (is_object($topics)) {
            $topics = array_values((array)$topics);
        } else {
            $topics = array_values($topics);
        }
// Unset section 0
        unset($course_sections[0]);
// Reset array values
        $course_sections = array_values($course_sections);

        if ($replace == 'all') {
            foreach ($topics as $key => $topic) {
                if (!empty($course_sections[$key])) {
                    course_update_section($course_id, $course_sections[$key], $topic);
                } else {
                    $new_section = course_create_section($course_id, $key);
                    // Update new section
                    course_update_section($course_id, $new_section, $topic);
                }
            }
        } else {
            foreach ($topics as $key => $topic) {
                $new_section = course_create_section($course_id, 0);
                // Update new section
                course_update_section($course_id, $new_section, $topic);
            }
        }

        return true;
    }

    public static function create_returns() {
        return new external_value(PARAM_BOOL, 'Status');
    }
}