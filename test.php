<?php
require_once("../../config.php");

use block_design_ideas\base;
use block_design_ideas\prompt;
use block_design_ideas\gen_ai;

global $CFG, $OUTPUT, $USER, $PAGE, $DB;


$courseid = 5;
$prompt_id = 4;
$context = context_course::instance($courseid);
require_login($context->instanceid, false);


base::page(
    new moodle_url('/blocks/design_ideas/test.php', []),
    'Testing',
    'Testing',
    $context
);

echo $OUTPUT->header();





$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);


$PROMPT = new prompt($prompt_id);

$course_summary = strip_tags($course->summary);
$course_summary = str_replace(['<br>', '<br />'], "\n", $course_summary);
$prompt = 'Summary: ' . $course_summary . "\n\n Q: ";

// Add prompt
$prompt .= $PROMPT->get_prompt();

$content = gen_ai::make_call($context, $prompt, 'en');

echo base::convert_string_to_html_list($content);

echo $OUTPUT->footer();
