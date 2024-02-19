<?php

namespace block_design_ideas;

class readings_generator extends gen_ai
{
    public static function render_results($promptid, $courseid) {
        global $DB;
        // Get information from query string
        $prompt_id = optional_param('prompt_id', 0, PARAM_INT);
        $course_id = optional_param('course_id', 0, PARAM_INT);
        $topic_id = optional_param('topic_id', 0, PARAM_INT);
        $topic_name = optional_param('topic_name', '', PARAM_TEXT);
        $topic_description = optional_param('topicDescription', '', PARAM_TEXT);

        $content = $topic_name . ': ' . $topic_description;
        $content = str_replace(array("\n", "\r"), '', $content);
        $PROMPT = new prompt($prompt_id);
        // Get prompt
        $prompt = $PROMPT->get_prompt();
        // Make the call
        $params = [
            'prompt' => $prompt,
            'content' => $content
        ];

        $results = parent::make_call($params);

        return self::render_from_template($results, $course_id);
    }

    /**
     * @param $data
     * @return mixed
     */
    private static function render_from_template($data, $course_id) {
        global $OUTPUT;
        // Return the data
        return $OUTPUT->render_from_template('block_design_ideas/reading_generator', $data);
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
        // Get all course topics.
        $modinfo = get_fast_modinfo($courseid);
        $sections = $modinfo->get_section_info_all();
        $buttons = [];
        $i = 0;
        foreach ($sections as $section) {
            // Do something with each section
            // For example, you can access the section's name and summary like this:
            $buttons[$i]['topic_name'] = $section->name;
            $buttons[$i]['topicid'] = $section->id;
            $buttons[$i]['courseid'] = $courseid;
            $buttons[$i]['topic_description'] = $section->summary;
            $i++;
        }
        $data = [
            'promptid' => $promptid,
            'courseid' => $courseid,
            'name' => 'Readings',
            'buttons' => $buttons
        ];

        return $OUTPUT->render_from_template('block_design_ideas/reading_generator_button', $data);
    }
}