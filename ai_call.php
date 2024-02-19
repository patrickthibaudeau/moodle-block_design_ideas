<?php
require_once('../../config.php');

use block_design_ideas\base;
use block_design_ideas\prompt;

// CHECK And PREPARE DATA
global $CFG, $OUTPUT, $SESSION, $PAGE, $DB, $COURSE, $USER;

require_login(1, false);


$prompt_id = required_param('prompt_id', PARAM_INT);
$course_id = required_param('course_id', PARAM_INT);

$context = context_course::instance($course_id);

$PAGE->set_url(new moodle_url(
        '/blocks/design_ideas/ai_call.php',
        [
            'prompt_id' => $prompt_id,
            'course_id' => $course_id
        ]
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
// Get prompt object
$PROMPT = new prompt($prompt_id);
// Get the class
if (empty($PROMPT->get_class())) {
    $class = '\block_design_ideas\ai_call';
} else {
    $class = '\block_design_ideas\\' . $PROMPT->get_class();
}

$GENAI = new $class();
echo $GENAI::render_results($prompt_id, $course_id);
ob_flush();
flush();
//**********************
//*** DISPLAY FOOTER ***
//**********************
echo $OUTPUT->footer();
ob_clean();