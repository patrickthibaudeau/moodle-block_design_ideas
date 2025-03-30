<?php

namespace block_design_ideas;

class readings extends gen_ai
{

    /**
     * Create deafult button
     * @param $promptid
     * @param $courseid
     * @return mixed
     */
    public static function get_button($promptid, $courseid, $name = 'Class notes')
    {
        global $CFG, $OUTPUT;

        if (empty($CFG->block_idi_semantic_scholar_api_key)) {
            return '';
        }

        $data = base::get_course_topics($courseid, $promptid, get_string('readings', 'block_design_ideas'));
        // Render buttons
        return $OUTPUT->render_from_template('block_design_ideas/readings_button', $data);
    }
}