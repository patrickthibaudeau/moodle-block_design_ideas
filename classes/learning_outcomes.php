<?php

namespace block_design_ideas;

class learning_outcomes extends gen_ai
{
    /**
     * Create deafult button
     * @param $promptid
     * @param $courseid
     * @return mixed
     */
    public static function get_button($promptid, $courseid, $name = 'Learning Outcomes')
    {
        global $OUTPUT;

        $data = [
            'promptid' => $promptid,
            'courseid' => $courseid,
            'name' => get_string('learning_outcomes', 'block_design_ideas'),
        ];

        // Render buttons
        return $OUTPUT->render_from_template('block_design_ideas/learning_outcomes_button',$data);
    }
}