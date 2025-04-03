<?php

/**
 * External Web Service Template
 *
 * @package    localwstemplate
 * @copyright  2011 Moodle Pty Ltd (http://moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_design_ideas\gen_ai;
use block_design_ideas\questions;

require_once("$CFG->dirroot/config.php");
require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . "/blocks/design_ideas/classes/forms/questions_form.php");


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
        global $CFG, $DB, $USER;

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
        // Remove all HTML tages include images
        $content = trim(strip_tags($content));

        $formdata = new stdClass();
        $formdata->courseid = $course_id;
        $formdata->content = $content;

        $mform = new block_design_ideas\forms\questions_form(null, array('formdata' => $formdata));


        $data = [];
        $data['form'] = $mform->render();

        return $data;


        return;
    }

    public static function execute_returns()
    {
        return new external_single_structure(
            array(
                'form' => new external_value(PARAM_RAW, 'HTML form'),
            )
        );
//        return new external_single_structure(
//            array(
//                'course_id' => new external_value(PARAM_INT, 'Course id'),
//                'content' => new external_value(PARAM_TEXT, 'Default: Section name and description'),
//                'question_types' => new external_multiple_structure(
//                    new external_single_structure(
//                        array(
//                            'name' => new external_value(PARAM_TEXT, 'Question type name'),
//                            'value' => new external_value(PARAM_TEXT, 'Question type'),
//                        )
//                    )
//                ),
//            )
//        );
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
        global $DB, $OUTPUT;

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
        $prompt = questions::get_prompt($question_type, $content);

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

        $html = $OUTPUT->render_from_template('block_design_ideas/question_results', $data);

        $results = array(
            'html' => $html,
        );
        return $results;
    }

    /**
     * Returns description of method result value
     * @return external_single_structure
     */
    public static function build_returns()
    {
        return new external_single_structure(
            array(
                'html' => new external_value(PARAM_RAW, 'HTML output'),
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
        global $CFG, $DB, $USER;

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

        require_once($CFG->libdir . '/questionlib.php');
        require_once($CFG->dirroot . '/question/format.php');
        require_once($CFG->dirroot . '/question/format/gift/format.php');

        // Get course
        $course = $DB->get_record('course', array('id' => $course_id), '*', MUST_EXIST);

        $qformat = new \qformat_gift();

        // Get all question banks for the course
        $question_banks = $DB->get_records('qbank', ['course' => $course_id], 'timecreated ASC');

        foreach ($question_banks as $question_bank) {
            // Get module id
            if ($module_id = $DB->get_field('course_modules', 'id', ['instance' => $question_bank->id, 'course' => $course_id, 'module' => 16])) {
                break;
            }
        }
// Knowing the module id, we can get the module context
        $module_context = context_module::instance($module_id);

// Use existing questions category for quiz or create the defaults.
        $contexts = new \core_question\local\bank\question_edit_contexts($module_context);
        if (!$category = $DB->get_record('question_categories', ['contextid' => $module_context->id, 'sortorder' => 999])) {
            $category = question_make_default_categories($contexts->all());
        }

// Split questions based on blank lines.
// Then loop through each question and create it.
        $questions = json_decode($questions, true);

        foreach ($questions as $question) {
            // Convert question into GIFT format;
            // Split the string into parts
            $parts = preg_split('/(\{|\}|~|=)/', $question['question'], -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

// Remove leading and trailing whitespace from each part
            $parts = array_map('trim', $parts);

// Rebuild the array to match the desired format
            $result = [];
            foreach ($parts as $part) {
                if ($part !== '') {
                    $result[] = $part;
                }
            }

            foreach ($result as $key => $value) {
                if ($key == 0) {
                    $gift = $value;
                } else {
                    if ($value == '{') {
                        $gift .= $value . "\n";
                    } elseif ($value == '=') {
                        $gift .= '=' . $result[$key + 1] . "\n";
                    } elseif ($value == '~') {
                        $gift .= '~' . $result[$key + 1] . "\n";
                    } elseif ($value == 'TRUE' || $value == 'FALSE') {
                        $gift .= $value . "\n";
                    } elseif ($value == '#') {
                        $gift .= $value . $result[$key + 1] . "\n";
                    } elseif ($value == '}') {
                        $gift .= "}\n";
                    }
                }
            }

            $singlequestion = explode("\n", $gift);
            // Manipulating question text manually for question text field.
            $questiontext = explode('{', $singlequestion[0]);
            $questiontext = trim(str_replace('::', '', $questiontext[0]));

            $qtype = $question_type;
            $q = $qformat->readquestion($singlequestion);
            print_object($q);
            // Check if question is valid.
            if (!$q) {
                return false;
            }
            $q->category = $category->id;
            $q->createdby = $USER->id;
            $q->modifiedby = $USER->id;
            $q->timecreated = time();
            $q->timemodified = time();
            $q->questiontext = ['text' => "<p>" . $questiontext . "</p>"];
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