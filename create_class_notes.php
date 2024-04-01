<?php
require_once('../../config.php');

use block_design_ideas\ai_call;

// CHECK And PREPARE DATA
global $CFG, $DB, $COURSE, $USER;

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
$prompt = 'You are a professor. Provide class notes on the specific subject of [subject] in point form using full sentences. Return the results as formatted HTML.';
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
    $prompt = 'You are a professor. Provide class notes on the specific subject of [subject] in point form using full sentences. Never identify who wrote the essay. Return the results as formatted HTML.';
}

$name = get_string('class_notes', 'block_design_ideas');

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
    // Remove <head...></head> tags and remove all content inbetween them
    $message = preg_replace('/<head>.*?<\/head>/', '', $message);
    // Clean the message by removing the <title> and all css
    $message = preg_replace('/<title>.*?<\/title>/', '', $message);
    $message = preg_replace('/<style>.*?<\/style>/', '', $message);
    // Remove all <script> tags and content inbetween them
    $message = preg_replace('/<script>.*?<\/script>/', '', $message);
    // Remove all <link> tags and content inbetween them
    $message = preg_replace('/<link.*?>/', '', $message);
    $message = str_replace('<html>', '', $message);
    $message = str_replace('</html>', '', $message);
    $message = str_replace('<body>', '', $message);
    $message = str_replace('</body>', '', $message);
    // Remove <p>Essay written by Professor AI Bot</p>
    $message = preg_replace('/<p>Essay written by Professor AI Bot<\/p>/', '', $message);
    $message = preg_replace('/<p>Written by Professor AI Bot<\/p>/', '', $message);
    $message = str_replace('CLASS NOTES: ', '', $message);

    return $message;
}