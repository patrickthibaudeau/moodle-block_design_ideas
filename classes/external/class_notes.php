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
        $content = gen_ai::make_call($context, strip_tags($prompt), $course->lang, false);

        // Try to parse the response as JSON since it should be structured data
        $parsed_content = null;
        if (is_string($content)) {
            // Remove markdown code block formatting if present
            $clean_content = $content;

            // Remove ```json at the beginning and ``` at the end
            $clean_content = preg_replace('/^```(?:json)?\s*/i', '', $clean_content);
            $clean_content = preg_replace('/\s*```\s*$/', '', $clean_content);
            $clean_content = trim($clean_content);

            $parsed_content = json_decode($clean_content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // If JSON parsing fails, try to extract subjects from text format
                $parsed_content = self::parse_subjects_from_text($content);
            }
        }

        // Get the data
        $subjects = [];
        $subjects['data'] = [];
        $subjects['course_id'] = $course_id;
        $subjects['section'] = $topic->section;
        $subjects['section_name'] = $topic->name;

        // Handle the parsed content properly
        if (is_array($parsed_content)) {
            foreach ($parsed_content as $subject) {
                if (is_array($subject)) {
                    $subjects['data'][] = [
                        'name' => $subject['subject'] ?? $subject['name'] ?? $subject['title'] ?? 'Untitled Subject',
                    ];
                } elseif (is_object($subject)) {
                    $subjects['data'][] = [
                        'name' => $subject->subject ?? $subject->name ?? $subject->title ?? 'Untitled Subject',
                    ];
                }
            }
        } else {
            // Fallback: create a single subject with the entire content
            $subjects['data'][] = [
                'name' => is_string($content) ? $content : 'Unable to parse content',
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
        global $CFG, $DB, $USER;

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

        $markdown = '';
        $institution = $CFG->block_idi_institution;
        switch ($institution) {
            case gen_ai::UNIVERSITY:
                $educator = 'a university professor';
                break;
            case gen_ai::COLLEGE:
                $educator = 'a college instructor';
                break;
            case gen_ai::HIGH_SCHOOL:
                $educator = 'a high school teacher';
                break;
            case gen_ai::ELEMENTARY:
                $educator = 'an elementary school teacher';
                break;
        }
        $system_message = 'You are ' . $educator . '. You are creating class notes for students. ' .
            'The notes must be in markdown format. The notes must be clear and easy to read. ';
        $prompt = 'Provide class notes on subject "[subject]". The notes must be include the following sections:' .
            'An overview, followed by highlights in point form, a paragraph on any other additional/relavent information and finally a conclusion.'
            . 'Do not include the author of the notes.';
        $i = 0;
        foreach ($messages as $message) {
            // Make call to AI and retrieve the message
            $prompt_message = str_replace('[subject]', $message->name, $prompt);

            // Lets try to get results up to ten times
            // This is to avoid the AI returning empty results
            // If the AI returns empty results, we will try again
            for ($try = 0; $try < 10; $try++) {
                // Make a direct call to the AI. Do not use the moodle call.
                $result = gen_ai::direct_call($system_message, $prompt_message, $course->lang);
                $result = trim($result);
                if (!empty($result)) {
                    break;
                }
            }

            $markdown .= $result;
            $i++;
        }
// Get section name
        $section_record = $DB->get_record('course_sections', ['section' => $section, 'course' => $course_id], '*', MUST_EXIST);

        $name = get_string('class_notes', 'block_design_ideas') . ' - ' . $section_record->name;

        if ($message_count === $i) {
            gen_ai::add_page_module(
                $name,
                $markdown,
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

    /**
     * Parse subjects from text format when JSON parsing fails
     * @param string $content
     * @return array
     */
    private static function parse_subjects_from_text($content) {
        $subjects = [];

        // Try to find patterns like "Subject 1:", "1.", "Subject Name:" etc.
        $lines = explode("\n", $content);
        $current_subject = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Check if this line looks like a subject header
            if (preg_match('/^(\d+\.?\s*|Subject\s*\d*:?\s*|[A-Z][^:]*:)\s*(.+)$/i', $line, $matches)) {
                // Save current subject if exists
                if ($current_subject !== null) {
                    $subjects[] = [
                        'subject' => $current_subject,
                        'name' => $current_subject
                    ];
                }
                // Start new subject
                $current_subject = trim($matches[2] ?? $matches[1]);
            } else if ($current_subject === null) {
                // If no pattern found and no current subject, treat each line as a subject
                $current_subject = $line;
            }
        }

        // Save last subject
        if ($current_subject !== null) {
            $subjects[] = [
                'subject' => $current_subject,
                'name' => $current_subject
            ];
        }

        // If no subjects found, return the entire content as one subject
        if (empty($subjects)) {
            $subjects[] = [
                'subject' => $content,
                'name' => 'Generated Class Notes Subject'
            ];
        }

        return $subjects;
    }
}
