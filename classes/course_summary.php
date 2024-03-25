<?php

namespace block_design_ideas;

class course_summary extends gen_ai
{
    public static function render_results($promptid, $courseid) {
        global $DB;
        // Get information from query string
        $prompt_id = optional_param('prompt_id', 0, PARAM_INT);
        $course_id = optional_param('course_id', 0, PARAM_INT);
        $modinfo = get_fast_modinfo($courseid);
        $sections = $modinfo->get_section_info_all();
        $topics = '';
        $description = '';
        // Add section name and summary to content
        foreach ($sections as $section) {
            $topics .= $section->name . "\n";
            $description = $section->summary . "\n";
        }
        // get course record
        $course = $DB->get_record('course', ['id' => $course_id]);
        $PROMPT = new prompt($prompt_id);
        // Get prompt
        $prompt = $PROMPT->get_prompt();
        // Replace the prompt with topics ancourse summary
        $prompt = str_replace('[course_topics]', $topics, $prompt);
        $prompt = str_replace('[course_description]', $description, $prompt);
        // Replace course name and course_description
        $prompt = str_replace('[course_title]', $course->fullname, $prompt);
        $prompt = str_replace('[course_description]', $course->summary, $prompt);
        // Make the call
        $params = [
            'prompt' => $prompt,
            'content' => ''
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
        $_SESSION[$course_id . '_ai_gen_course_summary'] = $data->message;
        $data->course_id = $course_id;
        // Return the data
        return $OUTPUT->render_from_template('block_design_ideas/course_summary', $data);
    }
}