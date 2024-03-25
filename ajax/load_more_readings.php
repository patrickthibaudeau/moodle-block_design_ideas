<?php
require_once('../../../config.php');
use block_design_ideas\base;
use block_design_ideas\readings_generator;

global $OUTPUT;

$course_id = required_param('course_id', PARAM_INT);
$topic_id = required_param('topic_id', PARAM_INT);
$offset = required_param('offset', PARAM_INT);

$context = context_course::instance($course_id);
require_login($course_id, false);

$keywords = $_SESSION['keywords_readings_' . $course_id];
$results = readings_generator::make_call_semantic_scholar($keywords,$offset);

$papers = readings_generator::render_papers($results, $course_id, $topic_id);
$message = $OUTPUT->render_from_template('block_design_ideas/ai_generated_readings', $papers);
echo json_encode([
    'html' => $message,
    'next' => $results['next'],
]);