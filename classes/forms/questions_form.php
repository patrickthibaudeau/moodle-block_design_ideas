<?php

namespace block_design_ideas\forms;

use block_design_ideas\questions;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/config.php');
require_once($CFG->dirroot . '/lib/formslib.php');


class questions_form extends \moodleform
{

    protected function definition()
    {
        global $DB, $OUTPUT;

        $formdata = $this->_customdata['formdata'];
        // Create form object
        $mform = &$this->_form;

        $context = \context_course::instance($formdata->courseid);
        // Add hidden id element
        $mform->addElement(
            'hidden',
            'courseid'
        );
        $mform->setType(
            'courseid',
            PARAM_INT
        );

        
        // Html element as a header for About fields
        $mform->addElement(
            'html',
            '<div class="alert alert-info">' .
            get_string('question_instructions', 'block_design_ideas') .
            '</div>'
        );
        // Name form element
        $mform->addElement(
            'textarea',
            'content',
            get_string('content', 'block_design_ideas')
        );
        $mform->setType(
            'content', PARAM_TEXT
        );

        // Description form element
        $mform->addElement(
            'select',
            'question_type',
            get_string('question_type', 'block_design_ideas'),
            questions::get_question_types()

        );
        $mform->setType(
            'question_type',
            PARAM_TEXT
        );

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
