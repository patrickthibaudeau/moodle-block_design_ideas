<?php

require_once("../../config.php");

require_once($CFG->dirroot . "/blocks/design_ideas/classes/forms/prompt.php");


use block_design_ideas\prompt;
use block_design_ideas\base;

global $CFG, $OUTPUT, $USER, $PAGE, $DB, $SITE;

$id = optional_param('id', 0, PARAM_INT);

$context = context_system::instance();

require_login(1, false);

if ($id) {
    $PROMPT = new prompt($id);
    $formdata = $PROMPT->get_record();
    // Get editor draft area
    $draftid = file_get_submitted_draft_itemid('description_editor');
    $currentText = file_prepare_draft_area(
        $draftid, $context->id,
        'block_design_ideas',
        'prompt_description',
        $formdata->id,
        base::get_editor_options($context),
        $formdata->description
    );
    $formdata->description_editor = array('text' => $currentText, 'format' => FORMAT_HTML, 'itemid' => $draftid);

} else {
    $formdata = new stdClass();
    $formdata->id = 0;
    $formdata->blockid = $blockid;
}

$mform = new block_design_ideas\prompt_form(null, array('formdata' => $formdata));
if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
    redirect($CFG->wwwroot . '/blocks/design_ideas/prompts.php');
} else if ($data = $mform->get_data()) {
    if ($data->id) {
        $PROMPT = new prompt($data->id);
        $PROMPT->update_record($data);
    } else {
        $PROMPT = new prompt();
        $data->id = $PROMPT->insert_record($data);
    }
//save editor text
    $draftid = file_get_submitted_draft_itemid('description_editor');
    $description_text = file_save_draft_area_files(
        $draftid,
        $context->id,
        'block_design_ideas',
        'prompt_description',
        $data->id,
        base::get_editor_options($context),
        $data->description_editor['text']
    );
    $data->description = $description_text;

    $DB->set_field('block_design_ideas_prompts', 'description', $data->description, array('id' => $data->id));

    redirect($CFG->wwwroot . '/blocks/design_ideas/prompts.php');
} else {
    $mform->set_data($mform);
}

base::page(
    new moodle_url('/blocks/desing_ideas/prompt.php', ['id' => $id]),
    get_string('prompt', 'block_design_ideas'),
    '',
    $context
);

echo $OUTPUT->header();
//**********************
//*** DISPLAY HEADER ***
//

$mform->display();


//**********************
//*** DISPLAY FOOTER ***
//**********************
echo $OUTPUT->footer();
?>