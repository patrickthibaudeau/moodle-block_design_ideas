<?php

namespace block_design_ideas;

use block_design_ideas\gen_ai;

class essay_topics extends gen_ai
{
    public static function render_results($promptid, $courseid)
    {
        global $DB;
        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

        $PROMPT = new prompt($promptid);
        // Get prompt
        $prompt = $PROMPT->get_prompt();
        // Make the call
        $params = [
            'prompt' => $prompt,
            'content' => $course->summary
        ];
        $results = parent::make_call($params);
        return self::render_from_template($results, $courseid);
    }

    private static function render_from_template($data, $courseid)
    {
        global $OUTPUT;

        $topics = $data->message;
        $topics = str_replace('&quot;', '"', $topics);
        $topics = str_replace('<br />', "\n", $topics);
        $_SESSION[$courseid . '_ai_gen_essay_topics'] = $topics;
        $topics = json_decode($topics);
        // Add an ID to the topics
        for ($i = 0; $i < count($topics); $i++) {
            $topics[$i]->id = $i;
            $topics[$i]->course_id = $courseid;
        }

        $data->prompt_id = optional_param('prompt_id', 0, PARAM_INT);
        $data->course_id = $courseid;
        $data->message = $OUTPUT->render_from_template('block_design_ideas/ai_generated_essay_topics', ['topics' => $topics]);
        // Add course sections to data
        $modinfo = get_fast_modinfo($courseid);
        $sections = $modinfo->get_section_info_all();
        $section_array = [];
        $i = 0;
        foreach ($sections as $section) {
            $section_array[$i]['id'] = $section->section;
            if ($section->section == 0) {
                $section_array[$i]['name'] = 'General';
            } else {
                $section_array[$i]['name'] = $section->name;
            }
            $i++;
        }
        $data->sections = $section_array;
//        print_object($data);
        // Return the data
        return $OUTPUT->render_from_template('block_design_ideas/essay_topics', $data);
    }

    /**
     * Create deafult button
     * @param $promptid
     * @param $courseid
     * @return mixed
     */
    public static function get_button($promptid, $courseid, $name = 'Essay Topics')
    {
        global $OUTPUT;

        $data = base::get_course_topics($courseid, $promptid, get_string('class_notes', 'block_design_ideas'));
        // Render buttons
        return $OUTPUT->render_from_template('block_design_ideas/essay_topics_button', $data);
    }

}