<?php
require_once('../../config.php');

// CHECK And PREPARE DATA
global $CFG, $OUTPUT, $SESSION, $PAGE, $DB, $COURSE, $USER;

require_login(1, false);


$bot_id = required_param('id', PARAM_INT);
$context = context_system::instance();

ob_start();
$loader = '<div class="loader">Let me work on that! <br>Thanks for waiting!</div>';
echo $loader;
ob_flush();
flush();

// Preapre query string data
$token = '6104e46ed6c7f5257413c116eef059b1';
$ws_function = 'cria_get_gpt_response';
$query_string = [
    'wstoken' => $token,
    'wsfunction' => $ws_function,
    'moodlewsrestformat' => 'json',
    'bot_id' => $bot_id,
    'chat_id' => '0',
    'prompt' => '',
    'content' => '',
];
//$url ="https://innovation.uit.yorku.ca/cria/webservice/rest/server.php?wstoken=$token&moodlewsrestformat=json&wsfunction=$ws_function&bot_id=62&chat_id=0&prompt=&content=$content";
$url ="https://innovation.uit.yorku.ca/cria/webservice/rest/server.php?";
// Get the response

if ($bot_id != 83) {
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
    $results = str_replace('"{', '{',$results);
    $results = str_replace('}"', '}',$results);
    $results = str_replace('\"', '"',$results);
    $results = str_replace('\\n', '<br>',$results);
    $results = str_replace('\\', '',$results);

    $response = json_decode($results);
} else {
    // Prompt 1 Describe the course
    $query_string['prompt'] = 'Describe the course.';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
    curl_setopt($ch, CURLOPT_USERAGENT, '5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
    $reply_one = curl_exec($ch);
    curl_close($ch);
// Cleanup results
    $reply_one = strip_tags($reply_one);
    $reply_one = str_replace('"{', '{',$reply_one);
    $reply_one = str_replace('}"', '}',$reply_one);
    $reply_one = str_replace('\"', '"',$reply_one);
    $reply_one = str_replace('\\n', '<br>',$reply_one);
    $reply_one = str_replace('\\', '',$reply_one);

    $response_one = json_decode($reply_one);
    sleep(2);
    // Prompt 2 Describe the course
    $query_string['content'] = 'Course description: ' . $response_one->message . '<br>';
    $query_string['prompt'] = 'Based on the course description above, write four essay topics. For each topic, describe the topic in no more than a paragraph.';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
    curl_setopt($ch, CURLOPT_USERAGENT, '5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
    $reply_two = curl_exec($ch);
    curl_close($ch);
// Cleanup results
    $reply_two = strip_tags($reply_one);
    $reply_two = str_replace('"{', '{',$reply_two);
    $reply_two = str_replace('}"', '}',$reply_two);
    $reply_two = str_replace('\"', '"',$reply_two);
    $reply_two = str_replace('\\n', '<br>',$reply_two);
    $reply_two = str_replace('\\', '',$reply_two);

    $response = json_decode($reply_two);
}


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