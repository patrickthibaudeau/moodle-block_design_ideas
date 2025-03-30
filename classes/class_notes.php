<?php

namespace block_design_ideas;

class class_notes extends gen_ai
{

    /**
     * Create deafult button
     * @param $promptid
     * @param $courseid
     * @return mixed
     */
    public static function get_button($promptid, $courseid, $name = 'Class notes')
    {
        global $OUTPUT;

        $data = base::get_course_topics($courseid, $promptid, get_string('class_notes', 'block_design_ideas'));
        // Render buttons
        return $OUTPUT->render_from_template('block_design_ideas/class_notes_button', $data);
    }
}