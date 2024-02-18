<?php

namespace block_design_ideas;

use block_design_ideas\prompt;

abstract class gen_ai
{
    public static function make_call($params)
    {
        global $CFG;
        $token = $CFG->block_idi_cria_token;
        $ws_function = 'cria_get_gpt_response';
        $query_string = [
            'wstoken' => $token,
            'wsfunction' => $ws_function,
            'moodlewsrestformat' => 'json',
            'bot_id' => $CFG->block_idi_cria_bot_id,
            'chat_id' => 'none'
        ];
        // Merge params with query string
        $query_string = array_merge($query_string, $params);
        // Build the URL
        $url = $CFG->block_idi_cria_url . "/webservice/rest/server.php?";
        // Make the call
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
        curl_setopt($ch, CURLOPT_USERAGENT, '5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        // get the response
        $results = curl_exec($ch);
        // close the connection
        curl_close($ch);
        // decode the response and return it
        return json_decode($results);
    }

    /**
     *
     * @param $prompt_id int
     * @param $courseid int
     * @return void
     */
    public static function render_results($prompt_id, $courseid) {
        global $DB;
        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $PROMPT = new prompt($prompt_id);
        $params = [
            'prompt' => $PROMPT->get_prompt(),
            'content' => $course->summary
        ];
        $results = self::make_call($params);

        return self::render_from_template($results);
    }

    /**
     * @param $data
     * @return mixed
     */
    private static function render_from_template($data) {
        global $OUTPUT;
        return $OUTPUT->render_from_template('block_design_ideas/ai_call', $data);
    }

}