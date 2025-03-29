<?php

namespace block_design_ideas;

class class_notes extends gen_ai
{

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