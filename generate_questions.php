<?php
require_once('../../config.php');

use block_design_ideas\ai_call;

// CHECK And PREPARE DATA
global $CFG, $DB, $COURSE, $USER, $OUTPUT;

// Get the raw POST data
$data = file_get_contents("php://input");

// Decode the JSON data
$json_data = json_decode($data, true);

$course_id = $json_data['course_id'];
$prompt_id = $json_data['prompt_id'];
$files = $json_data['files'];
// Get prompt
$prompt_record = $DB->get_record('block_design_ideas_prompts', ['id' => $prompt_id]);
$prompt = $prompt_record->prompt;

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

// Loop through files
$i = 0;
//foreach ($files as $file) {
    // Get the file
//    if ($i == 0) {
//        $file_content = file_get_contents('wu.json');
//    } else {
//        $file_content = file_get_contents('inshyn.json');
//    }

    // Convert file content (json) to an array
    $file_content['questions'] = json_decode(file_get_contents('questions.json'));
    // Get nodes from the file content
//    $nodes = $file_content->nodes;
    // Loop through all nodes
//    $x = 0;
//    foreach ($nodes as $node) {
//        // Get the content of the node
//        $content = $node->text;
//        $new_prompt = str_replace('[reading]', $content, $prompt);
//      echo $new_prompt . "<br><br>";
        // Make call to AI and retrieve the message
//        $result = ai_call::make_call(
//            [
//                'prompt' => '',
//                'content' => $prompt
//            ]
//        );
//        // Add the result to the node
//        print_object($result);

//        $x++;
//    }
//    $i++;

    // Make call to AI and retrieve the message
//    $result = ai_call::make_call(
//        [
//            'prompt' => $file_content,
//            'content' => ''
//        ]
//    );
//print_object($file_content);
$message = $OUTPUT->render_from_template('block_design_ideas/quiz_questions_examples', $file_content);

$data = new stdClass();
$data->status = 'success';
$data->course_id = $course_id;
$data->message = $message;
sleep(6);
echo json_encode($data);

//}


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