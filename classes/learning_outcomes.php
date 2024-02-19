<?php

namespace block_design_ideas;

class learning_outcomes extends gen_ai
{
    public static function render_results($promptid, $courseid) {
        global $DB;
        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        // Get all course topics.
        $modinfo = get_fast_modinfo($course->id);
        $sections = $modinfo->get_section_info_all();
        $content = $course->summary . "\nTopics:\n";
        foreach ($sections as $section) {
            // Do something with each section
            // For example, you can access the section's name and summary like this:
            $content .= $section->name . "\n";
            $content .= $section->summary . "\n";
        }
        $PROMPT = new prompt($promptid);
        // Get prompt
        $prompt = $PROMPT->get_prompt();
        // Make the call
        $params = [
            'prompt' => $prompt,
            'content' => $content
        ];
        $results = parent::make_call($params);
        return self::render_from_template($results);
    }

    /**
     * @param $data
     * @return mixed
     */
    private static function render_from_template($data)
    {
        global $OUTPUT;
        return $OUTPUT->render_from_template('block_design_ideas/ai_call', $data);
    }
}