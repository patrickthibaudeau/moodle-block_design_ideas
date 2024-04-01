<?php
require_once('../../config.php');

use block_design_ideas\ai_call;
// CHECK And PREPARE DATA
global $CFG, $DB, $COURSE, $USER, $OUTPUT;

$course_id = required_param('course_id', PARAM_INT);
$section = required_param('section', PARAM_INT);
$message = required_param('message', PARAM_RAW);

require_login($course_id, false);

// User must have capability to edit course
if (!has_capability('moodle/course:update', context_course::instance($course_id))) {
    $status = [
        'status' => 'error',
        'message' => 'You do not have permission to edit this course'
    ];

    echo json_encode($status);
    die();
}

// Conert message to array
$messages = json_decode($message);
$html = '';
$prompt = 'You are a professor. Provide class notes on the specific subject of [subject] in point form using full sentences. '
    . 'Return the results as formatted HTML. Omit adding styles in the head tag. Do not include the author of the notes.';
for ($i = 0; $i < count($messages); $i++) {
    // Make call to AI and retrieve the message
    $prompt = str_replace('[subject]', $messages[$i], $prompt);

    $result = ai_call::make_call(
        [
            'prompt' => $prompt,
            'content' => ''
        ]
    );
    $html .= clean_message($result->message);
    $prompt = 'You are a professor. Provide class notes on the specific subject of [subject] in point form using full sentences. '
        . 'Return the results as formatted HTML. Omit adding styles in the head tag. Do not include the author of the notes.';
}
// Get section name
$section_record = $DB->get_record('course_sections', ['section' => $section, 'course' => $course_id], '*', MUST_EXIST);

$name = get_string('class_notes', 'block_design_ideas'). ' - ' . $section_record->name;

ai_call::add_page_module(
    $name,
    trim($html),
    $course_id,
    $section
);

$status = [
    'status' => 'success'
];
echo json_encode($status);

function clean_message($message)
{
    // Remove <html> and <body> tags
    $message = str_replace('<html>', '', $message);
    $message = str_replace('</html>', '', $message);
    $message = str_replace('<body>', '', $message);
    $message = str_replace('</body>', '', $message);

    // Remove <p>Essay written by Professor AI Bot</p>
    $message = preg_replace('/<p>Essay written by Professor AI Bot<\/p>/', '', $message);
    $message = preg_replace('/<p>Written by Professor AI Bot<\/p>/', '', $message);
    $message = str_replace('CLASS NOTES: ', '', $message);
    $message = str_replace('Class Notes: ', '', $message);
    $message = preg_replace('/<style>.*?<\/style>/', '', $message);
    // Remove <head...></head> tags and remove all content inbetween them
    $message = preg_replace('/<head>.*?<\/head>/', '', $message);

    return $message;
}