<?php
require_once('../../config.php');

// CHECK And PREPARE DATA
global $CFG, $DB, $COURSE, $USER;

$course_id = required_param('courseid', PARAM_INT);
$replace = required_param('replace', PARAM_TEXT);

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

$topics = json_decode($_SESSION[$course_id . 'ai_gen_topics']);

// Get all sections for this course
$course_sections = $DB->get_records(
    'course_sections',
    array('course' => $course_id),
    'section');

$course_sections = array_values($course_sections);
// Unset section 0
unset($course_sections[0]);
// Reset array values
$course_sections = array_values($course_sections);

if ($replace == 'all') {
    foreach ($topics as $key => $topic) {
        if (!empty($course_sections[$key])) {
            course_update_section($course_id, $course_sections[$key], $topic);
        } else {
            $new_section = course_create_section($course_id, $topic);
            // Update new section
            course_update_section($course_id, $new_section, $topic);
        }
    }
} else {
    foreach ($topics as $key => $topic) {
        $new_section = course_create_section($course_id, 0);
        // Update new section
        course_update_section($course_id, $new_section, $topic);
    }
}


$status = [
    'status' => 'success'
];
echo json_encode($status);
