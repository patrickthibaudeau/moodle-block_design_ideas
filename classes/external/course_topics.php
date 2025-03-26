<?php

/**
 * External Web Service Template
 *
 * @package    localwstemplate
 * @copyright  2011 Moodle Pty Ltd (http://moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_design_ideas\prompt;
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

        $action = new \core_ai\aiactions\generate_text(
            contextid: $context->id,
            userid: $USER->id,
            prompttext: $prompt,
        );

// Send the action to the AI manager.
        $manager = \core\di::get(\core_ai\manager::class);
        $response = $manager->process_action($action);
        $content = json_decode($response->get_response_data()['generatedcontent']);

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
}