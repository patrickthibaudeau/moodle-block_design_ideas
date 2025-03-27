<?php
require_once("../../config.php");

use block_design_ideas\base;
use block_design_ideas\prompt;
use block_design_ideas\gen_ai;

global $CFG, $OUTPUT, $USER, $PAGE, $DB;

require_login($context->instanceid, false);


base::page(
    new moodle_url('/blocks/design_ideas/test.php', []),
    'Testing',
    'Testing',
    $context
);

echo $OUTPUT->header();

$courseid = 2;
$prompt_id = 7;
$section_id = 2;

$context = context_course::instance($courseid);

// Get information from query string
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
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
$content = gen_ai::make_call($context, strip_tags($prompt));

print_object($content);

echo $OUTPUT->footer();
