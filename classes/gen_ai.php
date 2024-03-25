<?php

namespace block_design_ideas;

use block_design_ideas\prompt;

abstract class gen_ai
{
    const UNIVERSITY = 1;
    const COLLEGE = 2;
    const HIGH_SCHOOL = 3;
    const ELEMENTARY = 4;

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
        //Clean up the prompt and content
        $params['prompt'] = html_entity_decode(strip_tags($params['prompt']), ENT_QUOTES, 'UTF-8');
        $params['content'] = html_entity_decode(strip_tags($params['content']), ENT_QUOTES, 'UTF-8');

        // Merge params with query string
//        $query_string = array_merge($query_string, $params);
//        $query_string = str_replace('&amp;', '&', $query_string);
        // Build the URL
        $query_string = http_build_query($query_string);
        $query_string = str_replace('&amp;', '&', $query_string);
        $query_string = str_replace('&nbsp;', '&', $query_string);
        $url = $CFG->block_idi_cria_url . "/webservice/rest/server.php?" . $query_string . "&prompt=" . urlencode($params['prompt']) . "&content=" . urlencode($params['content']);

        // Make the call
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
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
    public static function render_results($prompt_id, $courseid)
    {
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
    private static function render_from_template($data)
    {
        global $OUTPUT;
        return $OUTPUT->render_from_template('block_design_ideas/ai_call', $data);
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
            'name' => $name
        ];
        return $OUTPUT->render_from_template('block_design_ideas/default_block_button', $data);
    }


    public static function render_buttons($courseid = 1)
    {
        global $OUTPUT;;
        $PROMPTS = new prompts();
        $prompts = $PROMPTS->get_records();
        $html = '';
        foreach ($prompts as $prompt) {
            // Check to see if the prompt has a class
            if (!empty($prompt->class)) {
                $class = '\block_design_ideas\\' . $prompt->class;
                $PROMPT = new $class($prompt->id);
                // Check to see if set_button method is available
                if (method_exists($PROMPT, 'get_button')) {
                    $html .= $PROMPT::get_button($prompt->id, $courseid, $prompt->name) . "\n";
                } else {
                    unset($PROMPT);
                    $html .= self::get_button($prompt->id, $courseid, $prompt->name) . "\n";
                }
            } else {
                $html .= self::get_button($prompt->id, $courseid, $prompt->name) . "\n";
            }
        }
        unset($PROMPTS);
        unset($PROMPT);
        return $html;
    }

    /**
     * Add a label to a course sections
     * @param $prompt_id
     * @param $courseid
     * @return void
     */
    public static function add_label_module($text, $course_id, $section_id = 0)
    {
        global $CFG, $DB;
        if ($module = $DB->get_record('modules', ['name' => 'label'])) {
            require_once($CFG->dirroot . '/course/modlib.php');
            // Get course object
            $course = $DB->get_record('course', ['id' => $course_id]);

            $context = \context_system::instance();

            $data = new \stdClass();
            $data->name = 'label';
            $data->introeditor = [
                'text' => $text,
                'format' => FORMAT_HTML,
            ];
            $data->showdescription = 1;
            $data->visible = 1;
            $data->visibleoncoursepage = 1;
            $data->cmidnumber = '';
            $data->groupmode = 0;
            $data->groupingid = 0;
            $data->availabilityconditionsjson = '';
            $data->completionunlocked = 1;
            $data->completion = 0;
            $data->completionexpected = 0;
            $data->tags = [];
            $data->course = $course_id;
            $data->coursemodule = 0;
            $data->section = $section_id;
            $data->module = $module->id;
            $data->modulename = 'label';
            $data->instance = '';
            $data->add = 'label';
            $data->update = 0;
            $data->return = 0;
            $data->sr = 0;
            $data->competencies = [];
            $data->competency_rule = 0;

            $modinfo = @add_moduleinfo($data, $course);

            return $modinfo;
        }
    }

    /**
     * Add a page to a course sections
     * @param $name
     * @param $content
     * @param $course_id
     * @param $text
     * @param $section_id
     * @return object|\stdClass|void
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function add_page_module($name, $content, $course_id, $section_id = 0, $text = '')
    {
        global $CFG, $DB;
        if ($module = $DB->get_record('modules', ['name' => 'page'])) {
            require_once($CFG->dirroot . '/course/modlib.php');
            // Get course object
            $course = $DB->get_record('course', ['id' => $course_id]);


            $context = \context_system::instance();

            $data = new \stdClass();
            $data->name = $name;
            $data->introeditor = [
                'text' => $text,
                'format' => FORMAT_HTML,
            ];
            $data->content = $content;
            $data->contentformat = 1;
            $data->displayoptions = 'a:2:{s:10:"printintro";s:1:"0";s:17:"printlastmodified";s:1:"1";}';
            $data->showdescription = 1;
            $data->visible = 1;
            $data->visibleoncoursepage = 1;
            $data->cmidnumber = '';
            $data->groupmode = 0;
            $data->groupingid = 0;
            $data->availabilityconditionsjson = '';
            $data->completionunlocked = 1;
            $data->completion = 0;
            $data->completionexpected = 0;
            $data->tags = [];
            $data->course = $course_id;
            $data->coursemodule = 0;;
            $data->section = $section_id;
            $data->module = $module->id;
            $data->modulename = 'page';
            $data->instance = '';
            $data->add = 'page';
            $data->update = 0;
            $data->return = 0;
            $data->sr = 0;
            $data->competencies = [];
            $data->competency_rule = 0;

            $modinfo = @add_moduleinfo($data, $course);

            return $modinfo;
        }
    }

    public static function add_assign_module($name, $content, $course_id, $section_id = 0, $description = '')
    {
        global $CFG, $DB;
        if ($module = $DB->get_record('modules', ['name' => 'assign'])) {
            require_once($CFG->dirroot . '/course/modlib.php');
            // Get course object
            $course = $DB->get_record('course', ['id' => $course_id]);


            $context = \context_system::instance();

            $data = new \stdClass();
            $data->name = $name;
            $data->introeditor = [
                'text' => $content,
                'format' => FORMAT_HTML,
            ];
            $data->activity = $content;
            $data->showdescription = 0;
            $data->submissiondrafts = 1;
            $data->requiresubmissionstatement = 0;
            $data->sendnotifications = 0;
            $data->sendlatenotifications = 0;
            $data->duedate = 0;
            $data->cutoffdate = 0;
            $data->allowsubmissionsfromdate = 0;
            $data->gradingduedate = 0;
            $data->grade = 100;
            $data->teamsubmission = 0;
            $data->requireallteammemberssubmit = 0;
            $data->blindmarking = 0;
            $data->markingworkflow = 0;
            $data->visible = 1;
            $data->visibleoncoursepage = 1;
            $data->cmidnumber = '';
            $data->groupmode = 0;
            $data->groupingid = 0;
            $data->availabilityconditionsjson = '';
            $data->completionunlocked = 1;
            $data->completion = 0;
            $data->completionexpected = 0;
            $data->tags = [];
            $data->course = $course_id;
            $data->coursemodule = 0;;
            $data->section = $section_id;
            $data->module = $module->id;
            $data->modulename = 'assign';
            $data->instance = '';
            $data->add = 'assign';
            $data->update = 0;
            $data->return = 0;
            $data->sr = 0;
            $data->competencies = [];
            $data->competency_rule = 0;

            $modinfo = @add_moduleinfo($data, $course);

            return $modinfo;
        }
    }

    /**
     * Add a url to a course sections
     * @param $name
     * @param $url
     * @param $course_id
     * @param $section_id
     * @param string $description
     * @return object|\stdClass|void
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function add_url_module($name, $url, $course_id, $section_id, $description = '')
    {
        global $CFG, $DB;
        if ($module = $DB->get_record('modules', ['name' => 'url'])) {
            require_once($CFG->dirroot . '/course/modlib.php');
            $course = $DB->get_record('course', ['id' => $course_id]);
            $context = \context_system::instance();

            $mod = new \stdClass();
            $mod->name = $name;
            $mod->course = $course_id;
            $mod->coursemodule = 0;
            $mod->externalurl = trim($url);
            $mod->section = $section_id;
            $mod->introeditor['format'] = 1;
            $mod->introeditor['text'] = $description;
            $mod->introeditor['itemid'] = -1;
            $mod->showdescription = false;
            $mod->popupwidth = 620;
            $mod->popupheight = 450;
            $mod->display = 6;
            $mod->displayoptions = 'a:2:{s:10:"popupwidth";i:620;s:11:"popupheight";i:450;}';
            $mod->visibleoncoursepage = 1;
            $mod->availability = null;
            $mod->visible = 1;
            $mod->module = $module->id;
            $mod->modulename = 'url';
            $mod->add = 'url';

            $modinfo = @add_moduleinfo($mod, $course);

            return $modinfo;
        }
    }

    /**
     * The institution prompt is always prepended to the prompt
     * @return void
     * @throws \dml_exception
     */
    public static function get_institution_prompt()
    {
        global $CFG;

        switch ($CFG->block_idi_institution) {
            case self::UNIVERSITY:
                return '---You are a University professor building your course.--- ';
            case self::COLLEGE:
                return '---You are a College instructor building your course.--- ';
            case self::HIGH_SCHOOL:
                return '---You are a High School teacher building your course.--- ';
            case self::ELEMENTARY:
                return '---You are an Elementary School teacher building your course.--- ';
        }
    }
}