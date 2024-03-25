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


// Get the text between the forst <h1> tag and store it in the $name variable
preg_match('/<h1>(.*?)<\/h1>/', $message, $matches);
$name = $matches[1];
// Now remove the <h1> tag from the message
$message = preg_replace('/<h1>.*?<\/h1>/', '', $message);
// Clean the message by removing the <title> and all css
$message = preg_replace('/<title>.*?<\/title>/', '', $message);
$message = preg_replace('/<style>.*?<\/style>/', '', $message);

if (empty($name)) {
    $name = get_string('class_notes', 'block_design_ideas');
}

ai_call::add_page_module(
    $name,
    trim($message),
    $course_id,
    $section
);

$status = [
    'status' => 'success'
];
echo json_encode($status);
