<?php
require_once("../../config.php");

use block_design_ideas\base;
use block_design_ideas\prompt;
use block_design_ideas\gen_ai;

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
$prompt = "Short answer: Answers in Short Answer question-type are all prefixed by an equal sign (=), indicating that they are all correct answers. The answers must not contain a tilde.";
$prompt .= " Examples of a short answer format: \n
Who's buried in Grant's tomb?{=Grant =Ulysses S. Grant =Ulysses Grant}\n";
$prompt .= "Two plus two equals {=four =4}\n";
$prompt .= "---\n Topic: SQL Fundamentals: Description: Introduction to SQL (Structured Query Language) for defining and manipulating databases.";
$prompt .= "---\n Based on the topic and description, generate 10 short answer quiz questions in GIFT format as described above\n";
$prompt .= "Format all questions into the following JSON format:\n";
$prompt .= "[\n";
$prompt .= "    {\n";
$prompt .= "        \"question\": \"What is the purpose of data modelling?{=Organizes data systematically for easier understanding.}\",\n";
$prompt .= "     },\n";
$prompt .= "    {\n";
$prompt .= "        \"question\": \"How do you structure data modelling?{=Identify key entities and their attributes.}\",\n";
$prompt .= "     },\n";
$prompt .= "    ]";



$questions = gen_ai::make_call($context, strip_tags($prompt), $course->lang, true);

print_object($questions);
foreach ($questions as $q) {
    // Create an array based on {=
    $question_answer = explode('{=', $q->question);
    echo 'Question: ' . $question_answer[0] . "<br>";
    echo 'Answer: ' . rtrim($question_answer[1], '}') . "<br><br>";

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
