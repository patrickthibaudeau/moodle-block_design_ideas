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

$name = get_string('learning_outcomes', 'block_design_ideas');

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