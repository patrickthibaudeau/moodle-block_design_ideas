<?php

namespace block_design_ideas;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');
require_once($CFG->dirroot . '/config.php');

class prompt_form extends \moodleform
{

    protected function definition()
    {
        global $DB, $OUTPUT;

        $formdata = $this->_customdata['formdata'];
        // Create form object
        $mform = &$this->_form;

        $context = \context_system::instance();
        // Add hidden id element
        $mform->addElement(
            'hidden',
            'id'
        );
        $mform->setType(
            'id',
            PARAM_INT
        );

        // Add hidden blockid element
        $mform->addElement(
            'hidden',
            'blockid'
        );
        $mform->setType(
            'blockid',
            PARAM_INT
        );

        
        // Html element as a header for About fields
        $mform->addElement(
            'header',
            'prompt_parameters',
            get_string('prompt_parameters', 'block_design_ideas')
        );
        // Name form element
        $mform->addElement(
            'text',
            'name',
            get_string('name', 'block_design_ideas')
        );
        $mform->setType(
            'name', PARAM_TEXT
        );

        // Add rule required for name
        $mform->addRule(
            'name',
            get_string('required'),
            'required',
            null,
            'client'
        );

        // Description form element
        $mform->addElement(
            'editor',
            'description_editor',
            get_string('description', 'block_design_ideas')
        );
        $mform->setType(
            'description',
            PARAM_RAW
        );

        // Add select yes/no element fora available_child
        $mform->addElement(
            'textarea',
            'prompt',
            get_string('prompt', 'block_design_ideas')
        );
        $mform->setType(
            'prompt',
            PARAM_TEXT
        );
        // Add help button
        $mform->addHelpButton(
            'prompt',
            'prompt',
            'block_design_ideas'
        );
        // Add rule required for name
        $mform->addRule(
            'prompt',
            get_string('required'),
            'required',
            null,
            'client'
        );

        // Add element for class
        $mform->addElement(
            'text',
            'class',
            get_string('class', 'block_design_ideas')
        );
        $mform->setType(
            'class',
            PARAM_TEXT
        );

        // Add action buttons
        $this->add_action_buttons();
        $this->set_data($formdata);
    }

    // Perform some extra moodle validation
    public function validation($data, $files)
    {
        global $DB;

        $errors = parent::validation($data, $files);


        return $errors;
    }

}
