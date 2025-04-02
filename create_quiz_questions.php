<?php
require_once('../../config.php');

use block_design_ideas\ai_call;

// CHECK And PREPARE DATA
global $CFG, $DB, $COURSE, $USER, $OUTPUT;
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/format.php');
require_once($CFG->dirroot . '/question/format/gift/format.php');

$userid = $USER->id;


// Get the raw POST data
$data = file_get_contents("php://input");

// Decode the JSON data
$json_data = json_decode($data, true);
// Get question data
$question_data = $json_data['questions'];
$numofquestions = count($question_data);
//Convert questions into GIFT format;
$gift = '';
foreach ($question_data as $qd) {
    $gift .= $qd['question'] . ' {' . "\n";
    if ($qd['correct_answer'] == 'answer1') {
        $gift .= '=' . $qd['answer1'] . "\n";
    } else {
        $gift .= '~' . $qd['answer1'] . "\n";
    }
    if ($qd['correct_answer'] == 'answer2') {
        $gift .= '=' . $qd['answer2'] . "\n";
    } else {
        $gift .= '~' . $qd['answer2'] . "\n";
    }
    if ($qd['correct_answer'] == 'answer3') {
        $gift .= '=' . $qd['answer3'] . "\n";
    } else {
        $gift .= '~' . $qd['answer3'] . "\n";
    }
    if ($qd['correct_answer'] == 'answer4') {
        $gift .= '=' . $qd['answer4'] . "\n";
    } else {
        $gift .= '~' . $qd['answer4'] . "\n";
    }
    $gift .= "}\n\n";
}

$course_id = $json_data['course_id'];

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

$qformat = new \qformat_gift();

$coursecontext = \context_course::instance($course_id);



// Use existing questions category for quiz or create the defaults.
$contexts = new core_question\local\bank\question_edit_contexts($coursecontext);
if (!$category = $DB->get_record('question_categories', ['contextid' => $coursecontext->id, 'sortorder' => 999])) {
    $category = question_make_default_categories($contexts->all());
}

// Split questions based on blank lines.
// Then loop through each question and create it.
$questions = explode("\n\n", $gift);
unset($questions[4]);

if (count($questions) != $numofquestions) {
    return false;
}
$createdquestions = []; // Array of objects of created questions.
foreach ($questions as $question) {
    $singlequestion = explode("\n", $question);
    // Manipulating question text manually for question text field.
    $questiontext = explode('{', $singlequestion[0]);
    $questiontext = trim(str_replace('::', '', $questiontext[0]));
    $qtype = 'multichoice';
    $q = $qformat->readquestion($singlequestion);
    // Check if question is valid.
    if (!$q) {
        return false;
    }
    $q->category = $category->id;
    $q->createdby = $userid;
    $q->modifiedby = $userid;
    $q->timecreated = time();
    $q->timemodified = time();
    $q->questiontext = ['text' => "<p>" . $questiontext . "</p>"];
    $q->questiontextformat = 1;

    $created = question_bank::get_qtype($qtype)->save_question($q, $q);
    $createdquestions[] = $created;
}

$data = new stdClass();
if ($created) {
    $data->status = 'success';
} else {
    $data->status = 'error';
    $data->message = 'Error creating questions';
}

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