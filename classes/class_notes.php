<?php

namespace block_design_ideas;

class class_notes extends gen_ai
{
    public static function render_results($promptid, $courseid)
    {
        global $DB;
        // Get information from query string
        $prompt_id = optional_param('prompt_id', 0, PARAM_INT);
        $course_id = optional_param('course_id', 0, PARAM_INT);
        $topic_id = optional_param('topic_id', 0, PARAM_INT);

        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $topic = $DB->get_record('course_sections', array('id' => $topic_id), '*', MUST_EXIST);
        $topic_name = $topic->name;
        $topic_description = $topic->summary;
        // Reset session information
        $_SESSION['keywords_readings_' . $course_id] = '';
        $PROMPT = new prompt($prompt_id);
        // Get prompt
        $prompt = $PROMPT->get_prompt();
        $prompt = str_replace('[course_title]', $course->fullname, $prompt);
        $prompt = str_replace('[course_description]', $course->summary, $prompt);
        $prompt = str_replace('[course_topic]', $topic_name, $prompt);
        $prompt = str_replace('[topic_description]', $topic_description, $prompt);
        $prompt = html_entity_decode($prompt);
        // Make the call
        $params = [
            'prompt' => strip_tags($prompt),
            'content' => ''
        ];
        $query_string = [
            'course_id' => $course_id,
            'section' => $topic->section
        ];

        $results = parent::make_call($params);

        // Get the data
        $data = [];
        $data['message'] = $results->message;
        $data['course_id'] = $course_id;
        $data['section'] = $topic->section;

        return self::render_from_template($data, $course->id);

    }

    /**
     * @param $data
     * @return mixed
     */
    private
    static function render_from_template($data, $course_id)
    {
        global $OUTPUT;
        // Return the data
        return $OUTPUT->render_from_template('block_design_ideas/class_notes', $data);
    }

    /**
     * Create deafult button
     * @param $promptid
     * @param $courseid
     * @return mixed
     */
    public
    static function get_button($promptid, $courseid, $name = 'Generate')
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

        return $OUTPUT->render_from_template('block_design_ideas/class_notes_button', $data);
    }
}