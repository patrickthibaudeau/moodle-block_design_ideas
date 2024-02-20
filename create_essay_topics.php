<?php
require_once('../../config.php');

// CHECK And PREPARE DATA
global $CFG, $DB, $COURSE, $USER;

$course_id = required_param('courseid', PARAM_INT);
$data_id = $_REQUEST['data-id'];
$data_name = $_REQUEST['data-name'];
$data_summary = $_REQUEST['data-summary'];
$section = required_param('section', PARAM_INT);

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

$topics = [];

for ($i = 0; $i < count($data_id); $i++) {
    // Add assignment module
    \block_design_ideas\ai_call::add_assign_module(
        get_string('essay', 'blocks_design_ideas') . ': ' . $data_name[$i],
        $data_summary[$i],
        $course_id,
        $section,
        );
}

$status = [
    'status' => 'success'
];
echo json_encode($status);
