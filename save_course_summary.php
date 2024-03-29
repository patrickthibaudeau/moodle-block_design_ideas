<?php
require_once('../../config.php');

// CHECK And PREPARE DATA
global $CFG, $DB, $COURSE, $USER;

$course_id = required_param('courseid', PARAM_INT);

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

$summary = $_SESSION[$course_id . '_ai_gen_course_summary'];

// Now remove the <h1> tag from the message
$summary = preg_replace('/<h1>.*?<\/h1>/', '', $summary);
// Clean the message by removing the <title> and all css
$summary = preg_replace('/<title>.*?<\/title>/', '', $summary);
$summary = preg_replace('/<style>.*?<\/style>/', '', $summary);
// Update course summary
$params = [
    'id' => $course_id,
    'summary' => $summary
];

if (!empty($summary)) {
    $DB->update_record('course', (object)$params);
}

$status = [
    'status' => 'success'
];
echo json_encode($status);
