<?php

namespace block_design_ideas;

use block_design_ideas\gen_ai;

class course_topics extends gen_ai
{
    /**
     * Create deafult button
     * @param $promptid
     * @param $courseid
     * @return mixed
     */
    public static function get_button($promptid, $courseid, $name = 'Generate')
    {
        global $OUTPUT;
        $data = [
            'promptid' => $promptid,
            'courseid' => $courseid,
            'name' => 'Course Tpoics'
        ];
        return $OUTPUT->render_from_template('block_design_ideas/course_topic_button', $data);
    }

}