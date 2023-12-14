<?php
require_once('../../config.php');

// CHECK And PREPARE DATA
global $CFG, $OUTPUT, $SESSION, $PAGE, $DB, $COURSE, $USER;

require_login(1, false);


$bot_id = required_param('id', PARAM_INT);
$course_id = required_param('courseid', PARAM_INT);

$context = context_system::instance();

ob_start();
$loader = '<div class="loader">Let me work on that! <br>Thanks for waiting!</div>';
echo $loader;
ob_flush();
flush();
// Get course
$course = $DB->get_record('course', array('id' => $course_id), '*', MUST_EXIST);

$token = $CFG->block_idi_cria_token;
$ws_function = 'cria_get_gpt_response';
$query_string = [
    'wstoken' => $token,
    'wsfunction' => $ws_function,
    'moodlewsrestformat' => 'json',
    'bot_id' => $CFG->block_idi_cria_bot_id,
    'chat_id' => '0',
    'prompt' => '',
    'content' => $course->summary
];

$url = $CFG->block_idi_cria_url . "/webservice/rest/server.php?";
// Get the response
if ($bot_id == 844) {
    $query_string['prompt'] = 'Based on the course description, write four essay topic ideas. Include a description for each topic.' .
        ' Add a statement that these are ideas to get you started. You can adapt as required.';
}

if ($bot_id == 841) {
    $query_string['prompt'] = 'Based on the course description, create course topics for 13 weeks. Include a description ' .
        'for each topic. Add a statement that these are ideas to get you started. You can adapt as required.';
}

if ($bot_id == 842) {
    $query_string['prompt'] = 'What experiential learning activities may you suggest based on the learning outcomes and the course content? Please provide the answer in a list format.';
}

if ($bot_id == 843) {
    $query_string['prompt'] = 'What authentic assessment ideas can you give me based on the learning outcomes and the course content? Please provide the answer in a list format.';
}
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
curl_setopt($ch, CURLOPT_USERAGENT, '5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
$results = curl_exec($ch);
curl_close($ch);

// Cleanup results
$results = strip_tags($results);
$results = str_replace('"{', '{', $results);
$results = str_replace('}"', '}', $results);
$results = str_replace('\"', '"', $results);
$results = str_replace('\\n', '<br>', $results);
$results = str_replace('\\', '', $results);

$response = json_decode($results);



//echo $results;

//echo $response;
$data = [
    'message' => $response->message,
];

$PAGE->set_url(new moodle_url(
        '/blocks/design_ideas/ai_call.php',
        ['id' => $bot_id]
    )
);
$PAGE->set_title(get_string('pluginname', 'local_criaai'));
$PAGE->set_heading(get_string('pluginname', 'local_criaai'));
$PAGE->set_pagelayout('embedded');
$PAGE->set_context($context);
//**************** ******
//*** DISPLAY HEADER ***
//**********************
echo $OUTPUT->header();

echo $OUTPUT->render_from_template('block_design_ideas/ai_call', $data);
ob_flush();
flush();
//**********************
//*** DISPLAY FOOTER ***
//**********************
echo $OUTPUT->footer();
ob_clean();