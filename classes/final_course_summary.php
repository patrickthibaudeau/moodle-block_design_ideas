<?php

namespace block_design_ideas;

class final_course_summary extends gen_ai
{
    /**
     * Create deafult button
     * @param $promptid
     * @param $courseid
     * @return mixed
     */
    public static function get_button($promptid, $courseid, $name = 'Final Course Summary')
    {
        global $OUTPUT;

        $data = [
            'promptid' => $promptid,
            'courseid' => $courseid,
            'name' => get_string('final_course_summary', 'block_design_ideas'),
        ];

        // Render buttons
        return $OUTPUT->render_from_template('block_design_ideas/final_course_summary_button',$data);
    }
}