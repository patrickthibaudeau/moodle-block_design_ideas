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
        $message =  json_decode($results->message);

        // Get the data
        $data = [];
        $data['message'] = $message;
        $data['course_id'] = $course_id;
        $data['section'] = $topic->section;
        $data['section_name'] = $topic->name;

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
    static function get_button($promptid, $courseid, $name = 'Class notes')
    {
        global $OUTPUT;

        $data = base::get_course_topics($courseid, $promptid, get_string('class_notes', 'block_design_ideas'));
        // Render buttons
        return $OUTPUT->render_from_template('block_design_ideas/class_notes_button', $data);
    }

    public static function clean_message($message)
    {
        // Remove <html> and <body> tags
        $message = str_replace('<html>', '', $message);
        $message = str_replace('</html>', '', $message);
        $message = str_replace('<body>', '', $message);
        $message = str_replace('</body>', '', $message);

        // Remove <p>Essay written by Professor AI Bot</p>
        $message = preg_replace('/<p>Essay written by Professor AI Bot<\/p>/', '', $message);
        $message = preg_replace('/<p>Written by Professor AI Bot<\/p>/', '', $message);
        $message = str_replace('CLASS NOTES: ', '', $message);
        $message = str_replace('Class Notes: ', '', $message);
        $message = preg_replace('/<style>.*?<\/style>/', '', $message);
        // Remove <head...></head> tags and remove all content inbetween them
        $message = preg_replace('/<head>.*?<\/head>/', '', $message);

        return $message;
    }
}