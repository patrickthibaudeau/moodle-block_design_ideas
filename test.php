<?php
require_once("../../config.php");

use block_design_ideas\base;
use block_design_ideas\prompt;

global $CFG, $OUTPUT, $USER, $PAGE, $DB;


//$prompt_id = required_param('promptid', PARAM_INT);


$context = context_course::instance(45);

require_login($context->instanceid, false);
//
//$PROMPT = new prompt($prompt_id);
//if (empty($PROMPT->get_class())) {
//    $class = '\block_design_ideas\gen_ai';
//} else {
//    $class = '\block_design_ideas\\' . $PROMPT->get_class();
//}
//
//$GENAI = new $class();

base::page(
    new moodle_url('/blocks/design_ideas/test.php', []),
    'Testing',
    'Testing',
    $context
);

echo $OUTPUT->header();
//echo $GENAI::render_results($prompt_id, $context->instanceid);
\block_design_ideas\ai_call::add_assign_module('Test assignment', 'Hello world', 45,1);
echo $OUTPUT->footer();
