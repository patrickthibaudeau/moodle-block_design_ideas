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
use block_design_ideas\class_notes;

require_once($CFG->libdir . "/externallib.php");
require_once("$CFG->dirroot/config.php");


class block_design_ideas_class_notes extends external_api
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
                'sectionid' => new external_value(PARAM_INT, 'Topic section id', VALUE_OPTIONAL, 13),
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
    public static function execute($course_id, $prompt_id, $section_id)
    {
        global $DB, $USER;

        //Parameter validation
        $params = self::validate_parameters(
            self::execute_parameters(),
            array(
                'courseid' => $course_id,
                'promptid' => $prompt_id,
                'sectionid' => $section_id
            )
        );

        //Context validation
        $context = \context_course::instance($course_id);
        self::validate_context($context);

        // Get information from query string
        $course = $DB->get_record('course', array('id' => $course_id), '*', MUST_EXIST);
        $topic = $DB->get_record('course_sections', array('id' => $section_id), '*', MUST_EXIST);
        $topic_name = $topic->name;
        $topic_description = $topic->summary;

        // Get prompt
        $PROMPT = new prompt($prompt_id);
        // Get prompt
        $prompt = $PROMPT->get_prompt();
        $prompt = str_replace('[course_title]', $course->fullname, $prompt);
        $prompt = str_replace('[course_description]', $course->summary, $prompt);
        $prompt = str_replace('[course_topic]', $topic_name, $prompt);
        $prompt = str_replace('[topic_description]', $topic_description, $prompt);
        $prompt = html_entity_decode($prompt);
        // Make the call
        $content = gen_ai::make_call($context, strip_tags($prompt), $course->lang, true);


        // Get the data
        $subjects = [];
        $subjects['data'] = [];
        foreach ($content as $subject) {
            $subjects['course_id'] = $course_id;
            $subjects['section'] = $topic->section;
            $subjects['section_name'] = $topic->name;
            $subjects['data'][] = [
                'name' => $subject->subject,
            ];
        }

        return $subjects;
    }

    public static function execute_returns()
    {
        return new external_single_structure(
            array(
                'course_id' => new external_value(PARAM_INT, 'Course id'),
                'section' => new external_value(PARAM_INT, 'Topic section'),
                'section_name' => new external_value(PARAM_TEXT, 'Topic name'),
                'data' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_TEXT, 'Topic name'),
                        )
                    )
                )
            )
        );
    }


    // Create course topics
    public static function create_parameters()
    {
        return new external_function_parameters(
            array(
                'section' => new external_value(PARAM_INT, 'Topic section number', VALUE_REQUIRED),
                'courseid' => new external_value(PARAM_INT, 'Course id', VALUE_REQUIRED),
                'subjects' => new external_value(PARAM_RAW, 'JSON of subjects', VALUE_REQUIRED),
            )
        );
    }

    public static function create($section, $course_id, $subjects)
    {
        global $DB, $USER;

        //Parameter validation
        $params = self::validate_parameters(
            self::create_parameters(),
            array(
                'section' => $section,
                'courseid' => $course_id,
                'subjects' => $subjects
            )
        );

        //Context validation
        $context = \context_course::instance($course_id);
        self::validate_context($context);

        // Get course
        $course = $DB->get_record('course', array('id' => $course_id), '*', MUST_EXIST);

        $messages = json_decode($subjects);
        $message_count = count($messages);
        $html = '';
        $prompt = 'You are a professor. Provide class notes on subject "[subject]" in point form using full sentences. '
            . 'Return the results as formatted HTML. Do not include the author of the notes.';
        $i = 0;
        foreach ($messages as $message) {
            // Make call to AI and retrieve the message
            $prompt_message = str_replace('[subject]', $message->name, $prompt);

            $result = gen_ai::make_call($context, $prompt_message, $course->lang);

            // Moodle returns the results in plain text. Convert the plain text to an ordered list.
            // Split the text into lines
            $lines = explode("\n", $result);

// Initialize arrays for points and other text
            $points = [];
            $otherText = [];

// Process each line
            foreach ($lines as $line) {
                // Check if the line starts with a number followed by a period
                if (preg_match('/^(\d+\.)|(-)/', $line)) {
                    $points[] = $line;
                } else {
                    $otherText[] = $line;
                }
            }
            $html .= '<h3>' . $message->name . '</h3>';
            $html .= '<ol>';
            foreach ($points as $point) {
                // If the point starts with a number, example 1., Remove the number followed by the period.
                if (preg_match('/^\d+\./', $point)) {
                    $point = preg_replace('/^\d+\.\s*/', '', $point);
                }
                // Also remove any leading dashes (-)
                $point = preg_replace('/^\s*-\s*/', '', $point);
                $html .= '<li>' . $point . '</li>';
            }
            $html .= '</ol>';

            $html .= '<br><br>';
            $i++;
        }
// Get section name
        $section_record = $DB->get_record('course_sections', ['section' => $section, 'course' => $course_id], '*', MUST_EXIST);

        $name = get_string('class_notes', 'block_design_ideas') . ' - ' . $section_record->name;

        if ($message_count === $i) {
            gen_ai::add_page_module(
                $name,
                trim($html),
                $course_id,
                $section
            );
        }


        return true;
    }

    public static function create_returns()
    {
        return new external_value(PARAM_BOOL, 'Status');
    }
}