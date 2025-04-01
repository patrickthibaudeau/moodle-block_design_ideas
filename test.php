<?php
require_once("../../config.php");

use block_design_ideas\base;
use block_design_ideas\prompt;
use block_design_ideas\gen_ai;
use block_design_ideas\questions;

global $CFG, $OUTPUT, $USER, $PAGE, $DB;


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

$course = $DB->get_record('course', array('id' => $course_id), '*', MUST_EXIST);

$content = 'Data Modelling Basics: An overview of the key concepts and importance of database management in modern applications.';

$question_type= 'gapselect';
// Get prompt
$prompt = questions::get_prompt($question_type, $content);

// Generate questions

$questions = gen_ai::make_call($context, strip_tags($prompt), $course->lang, true);

print_object($questions);
foreach ($questions as $q) {
    if ($question_type == 'gapselect') {
        // Find the text within curly brackets
        preg_match('/\{(.*?)\}/', $q->question, $matches);

        if (!empty($matches)) {
            // Extract the answer part
            $answer = $matches[0];

            // Replace the text within curly brackets with underscores
            $question = preg_replace('/\{.*?\}/', '_________', $q->question);

             echo 'Question: ' . $question . '<br>';
            echo 'Answer: ' . $answer . '<br><br>';

        }
    } else {
        // Create an array based on {
        $question_answer = explode('{', $q->question);
        echo 'Question: ' . $question_answer[0] . "<br>";
        echo 'Answer: ' . rtrim($question_answer[1], '}') . "<br><br>";
    }

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
