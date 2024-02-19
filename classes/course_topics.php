<?php

namespace block_design_ideas;

use block_design_ideas\gen_ai;

class course_topics extends gen_ai
{
    public static function render_results($promptid, $courseid) {
        global $DB;
        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        // Get number of topics
        $number_of_topics = optional_param('topics', 13, PARAM_INT);
        $PROMPT = new prompt($promptid);
        // Get prompt
        $prompt = $PROMPT->get_prompt();
        // Replace number of topics
        $prompt = str_replace('[number_of_topics]', $number_of_topics, $prompt);
        // Make the call
        $params = [
            'prompt' => $prompt,
            'content' => $course->summary
        ];
        $results = parent::make_call($params);
        return self::render_from_template($results, $courseid);
    }

    private static function render_from_template($data, $courseid) {
        global $OUTPUT;

        $topics = $data->message;
        $topics = str_replace('&quot;', '"', $topics);
        $topics = str_replace('<br />',"\n",$topics);
        $_SESSION[$courseid . 'ai_gen_topics'] = $topics;
        $topics = json_decode($topics);
        $data->message = $OUTPUT->render_from_template('block_design_ideas/ai_generated_topics', ['topics' => $topics]);
        // Return the data
        return $OUTPUT->render_from_template('block_design_ideas/course_topics', $data);
    }

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