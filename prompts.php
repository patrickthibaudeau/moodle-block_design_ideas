<?php
require "../../config.php";

use block_design_ideas\base;
use block_design_ideas\prompts;

global $PAGE, $OUTPUT, $DB, $CFG;

$context = context_system::instance();

require_login(1, true);

if (!has_capability('block/design_ideas:edit_prompts', $context)) {
    redirect($CFG->wwwroot);
}
$PROMPTS = new prompts();

$prompts = $PROMPTS->get_records();
$prompts = array_values($prompts);
$data = [
    'prompts' => $prompts
];

base::page(
    new moodle_url('/blocks/design_ideas/prompts.php', []),
    get_string('prompts', 'block_design_ideas'),
    get_string('prompts', 'block_design_ideas'),
    $context
);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('block_design_ideas/prompts_table', $data);
echo $OUTPUT->footer();
