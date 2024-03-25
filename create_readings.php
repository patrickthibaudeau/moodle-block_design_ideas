<?php
require_once('../../config.php');

use block_design_ideas\ai_call;

// CHECK And PREPARE DATA
global $CFG, $DB, $COURSE, $USER;

$course_id = required_param('course_id', PARAM_INT);
$section = required_param('section', PARAM_INT);
$title = required_param('title', PARAM_TEXT);
$author = optional_param('author','', PARAM_TEXT);
$year = optional_param('year','', PARAM_TEXT);
$url = optional_param('url','', PARAM_TEXT);
$is_open_access = optional_param('isopenaccess','', PARAM_TEXT);
$pdf_url = optional_param('pdf','', PARAM_TEXT);


require_login($course_id, false);
echo $title;
// User must have capability to edit course
if (!has_capability('moodle/course:update', context_course::instance($course_id))) {
    $status = [
        'status' => 'error',
        'message' => 'You do not have permission to edit this course'
    ];

    echo json_encode($status);
    die();
}

// Is there a pdf_url. If not set link to the url
if(empty($pdf_url)){
    $pdf_url = $url;
}

$name = $author . ' (' . $year . '). ' . $title;

ai_call::add_url_module(
    $name,
    $pdf_url,
    $course_id,
    $section,
    ''
);


$status = [
    'status' => 'success'
];
echo json_encode($status);
