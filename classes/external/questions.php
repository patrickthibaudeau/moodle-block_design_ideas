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
use block_design_ideas\questions;

require_once($CFG->libdir . "/externallib.php");
require_once("$CFG->dirroot/config.php");


class block_design_ideas_questions extends external_api
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
        $content = $topic->name . ': ' . $topic->summary;

        // Get question types
        $question_types_array = questions::get_question_types();
        foreach ($question_types_array as $value => $name) {
            $question_types[] = array(
                'name' => $name,
                'value' => $value,
            );
        }
        // $data = [];
        $data['content'] = $content;
        $data['course_id'] = $course_id;
        $data['question_types'] = $question_types;

        return $data;



        return ;
    }

    public static function execute_returns()
    {
        return new external_single_structure(
            array(
                'course_id' => new external_value(PARAM_INT, 'Course id'),
                'content' => new external_value(PARAM_TEXT, 'Default: Section name and description'),
                'question_types' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_TEXT, 'Question type name'),
                            'value' => new external_value(PARAM_TEXT, 'Question type'),
                        )
                    )
                ),
            )
        );
    }


    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function build_parameters()
    {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'Course id', VALUE_REQUIRED),
                'questiontype' => new external_value(PARAM_TEXT, 'Question type', VALUE_REQUIRED),
                'content' => new external_value(PARAM_TEXT, 'Content to create questions from', VALUE_REQUIRED),
            )
        );
    }

    /**
     * Generate questions
     * @param int $course_id
     * @param int $prompt_id
     * @param int $section_id
     * @return array
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws restricted_context_exception
     */
    public static function build($course_id, $question_type, $content)
    {
        global $DB, $USER;

        //Parameter validation
        $params = self::validate_parameters(
            self::build_parameters(),
            array(
                'courseid' => $course_id,
                'questiontype' => $question_type,
                'content' => $content
            )
        );

        //Context validation
        $context = \context_course::instance($course_id);
        self::validate_context($context);

        // Get information from query string
        $course = $DB->get_record('course', array('id' => $course_id), '*', MUST_EXIST);

        // Get prompt
        $prompt = questions::get_prompt($question_type);
        $prompt = str_replace('[content]', $content, $prompt);

        // Generate questions
        $questions = gen_ai::make_call($context, strip_tags($prompt), $course->lang, true);

        $data = [];

        foreach ($questions as $q) {
            if ($question_type == 'gapselect') {
                // Find the text within curly brackets
                preg_match('/\{(.*?)\}/', $q->question, $matches);

                if (!empty($matches)) {
                    // Extract the answer part
                    $answer = $matches[0];

                    // Replace the text within curly brackets with underscores
                    $question = preg_replace('/\{.*?\}/', '_________', $q->question);

                }
            } else {
                // Create an array based on {
                $question_answer = explode('{', $q->question);
                $question = $question_answer[0];
                $answer = rtrim($question_answer[1], '}');
            }

            $data['questions'][] = array(
                'question_gift' => $q->question,
                'question' => $question,
                'answer' => $answer,
            );

        }
    }

    /**
     * Returns description of method result value
     * @return external_single_structure
     */
    public static function build_returns()
    {
        return new external_single_structure(
            array(
                'questions' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'question_gift' => new external_value(PARAM_TEXT, 'Question in GIFT format'),
                            'question' => new external_value(PARAM_TEXT, 'Question'),
                            'answer' => new external_value(PARAM_TEXT, 'Answer'),
                        )
                    )
                ),
            )
        );
    }














    // Create Question in question bank
    public static function create_parameters()
    {
        return new external_function_parameters(
            array(
                'question_type' => new external_value(PARAM_TEXT, 'Question type', VALUE_REQUIRED),
                'courseid' => new external_value(PARAM_INT, 'Course id', VALUE_REQUIRED),
                'questions' => new external_value(PARAM_RAW, 'JSON of questions', VALUE_REQUIRED),
            )
        );
    }

    public static function create($question_type, $course_id, $questions)
    {
        global $DB, $USER;

        //Parameter validation
        $params = self::validate_parameters(
            self::create_parameters(),
            array(
                'question_type' => $question_type,
                'courseid' => $course_id,
                'questions' => $questions
            )
        );

        //Context validation
        $context = \context_course::instance($course_id);
        self::validate_context($context);

        // Get course
        $course = $DB->get_record('course', array('id' => $course_id), '*', MUST_EXIST);

        $qformat = new \qformat_gift();

// Use existing questions category for quiz or create the defaults.
        $contexts = new core_question\local\bank\question_edit_contexts($context);
        if (!$category = $DB->get_record('question_categories', ['contextid' => $context->id, 'sortorder' => 999])) {
            $category = question_make_default_categories($contexts->all());
        }

// Split questions based on blank lines.
// Then loop through each question and create it.
        $questions = json_decode($questions, true);

        foreach ($questions as $question) {

            $qtype = $question_type;
            $q = $qformat->readquestion($question['question_gift']);
            // Check if question is valid.
            if (!$q) {
                return false;
            }
            $q->category = $category->id;
            $q->createdby = $USER->id;
            $q->modifiedby = $USER->id;
            $q->timecreated = time();
            $q->timemodified = time();
            $q->questiontext = ['text' => "<p>" . $question->question . "</p>"];
            $q->questiontextformat = 1;

            $created = question_bank::get_qtype($qtype)->save_question($q, $q);
        }


        return true;
    }

    public static function create_returns()
    {
        return new external_value(PARAM_BOOL, 'Status');
    }
}