<?php
require_once("../../config.php");

use block_design_ideas\base;
use block_design_ideas\prompt;
use block_design_ideas\gen_ai;
use block_design_ideas\questions;

global $CFG, $OUTPUT, $USER, $PAGE, $DB;
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/format.php');
require_once($CFG->dirroot . '/question/format/gift/format.php');


$course_id = 5;
$prompt_id = 3;
$section_id = 56;
$context = context_course::instance($course_id);
require_login($context->instanceid, false);


base::page(
    new moodle_url('/blocks/design_ideas/test.php', []),
    'Testing',
    'Testing',
    $context
);

echo $OUTPUT->header();

$question_type = 'multichoice';
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

$questions = '[{"question":"What is the primary purpose of database management?{=To organize and manage data ~To create websites ~To develop software ~To design graphics }"},{"question":"Which of the following is a key concept in database management?{=Data integrity ~Online shopping ~Social media ~Web design }"}]';
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


//print_object($questions);

// Get information from query string
//$course = $DB->get_record('course', array('id' => $course_id), '*', MUST_EXIST);
//$topic = $DB->get_record('course_sections', array('id' => $section_id), '*', MUST_EXIST);
//$topic_name = $topic->name;
//$topic_description = $topic->summary;
//
//// Get prompt
//$PROMPT = new prompt($prompt_id);
//// Get prompt
//$prompt = $PROMPT->get_prompt();
//$prompt = str_replace('[topic]', $topic_name, $prompt);
//$prompt = str_replace('[topic_description]', $topic_description, $prompt);
//$prompt = html_entity_decode($prompt);
//// Make the call
//$query = gen_ai::make_call($context, strip_tags($prompt), $course->lang);
//
//
//// Make a call to Semantic Scholar AI
//$url = $CFG->block_idi_semantic_scholar_api_url;
//$api_key = $CFG->block_idi_semantic_scholar_api_key;
//$call = '/paper/search';
//$data = [
//    'query' => $query,
//    'fields' => 'title,year,abstract,authors.name,journal,publicationTypes,isOpenAccess,openAccessPdf,url,externalIds',
//    'publicationTypes' => 'JournalArticle,""',
//    'isOpenAccess' => true,
//    'offset' => 0,
//    'limit' => 10
//];
//
//$results= base::make_api_call($url, $api_key, $data, $call, 'GET');
//
//print_object($results);

echo $OUTPUT->footer();
