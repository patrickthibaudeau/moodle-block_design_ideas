<?php
require_once('../../config.php');

// CHECK And PREPARE DATA
global $CFG, $OUTPUT, $SESSION, $PAGE, $DB, $COURSE, $USER;

require_login(1, false);


$bot_id = required_param('id', PARAM_INT);
$course_id = required_param('courseid', PARAM_INT);
$number_of_topics = optional_param('topics',  4,PARAM_INT);

$context = context_system::instance();


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
ob_start();
$loader = $OUTPUT->render_from_template('block_design_ideas/loader', []);
echo $loader;
ob_flush();
flush();
// Get course
$course = $DB->get_record('course', array('id' => $course_id), '*', MUST_EXIST);
// Clean course summary from any HTMl tags
$course->summary = strip_tags($course->summary);
$token = $CFG->block_idi_cria_token;
$ws_function = 'cria_get_gpt_response';
$query_string = [
    'wstoken' => $token,
    'wsfunction' => $ws_function,
    'moodlewsrestformat' => 'json',
    'bot_id' => $CFG->block_idi_cria_bot_id,
    'chat_id' => 'none',
    'prompt' => '',
    'content' => $course->summary
];

$url = $CFG->block_idi_cria_url . "/webservice/rest/server.php?";

// Get the response
if ($bot_id == 844) {
    $query_string['prompt'] = 'Based on the course description, write four university grade essay topic ideas. Include a description for each topic.' .
        ' Add a statement that these are ideas to get you started. You can adapt as required.'
        . "Always write in the same language as the course description.\n";
}

$show_create_topics_button = false;
if ($bot_id == 841) {
    $query_string['prompt'] = "---You are a University professor building your course.---\n"
        . "Based on the course description, create $number_of_topics topics, no more!\n"
        . "Include a description for each topic.\n"
        . "Always write in the same language as the course description.\n"
        . "Return the results in JSON format as per this example: \n"
        . '[
            {"name":"Name of topic","summary":"Description of topic},
            {"name":"Name of topic","summary":"Description of topic},
            ]';
    $show_create_topics_button = true;
}

if ($bot_id == 842) {
    $query_string['prompt'] = 'What experiential learning activities may you suggest based on the learning outcomes and the course content? Please provide the answer in a list format.'
        . "Always write in the same language as the course description.\n";
}

if ($bot_id == 843) {
    $query_string['prompt'] = 'What authentic assessment ideas can you give me based on the learning outcomes and the course content? Please provide the answer in a list format.'
        . "Always write in the same language as the course description.\n";
}

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
curl_setopt($ch, CURLOPT_USERAGENT, '5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
//curl_setopt($ch, CURLOPT_HEADER, 1);
$results = curl_exec($ch);

curl_close($ch);

$response = json_decode($results);
// If the bot is 841, then save the topics in the session
// Use a template to display the topics
if ($bot_id == 841) {
    $topics = $response->message;
    $topics = str_replace('&quot;', '"', $topics);
    $topics = str_replace('<br />',"\n",$topics);
    $_SESSION[$course_id . 'ai_gen_topics'] = $topics;
    $topics = json_decode($topics);
    $message = $OUTPUT->render_from_template('block_design_ideas/ai_generated_topics', ['topics' => $topics]);
} else {
    $message = $response->message;
}

//echo $response;
$data = [
    'course_id' => $course_id,
    'show_create_topics_button' => $show_create_topics_button,
    'message' => $message,
];
echo $OUTPUT->render_from_template('block_design_ideas/ai_call', $data);
ob_flush();
flush();
//**********************
//*** DISPLAY FOOTER ***
//**********************
echo $OUTPUT->footer();
ob_clean();