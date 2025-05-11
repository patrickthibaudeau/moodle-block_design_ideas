<?php

namespace block_design_ideas;

use block_design_ideas\prompt;

abstract class gen_ai
{
    const UNIVERSITY = 1;
    const COLLEGE = 2;
    const HIGH_SCHOOL = 3;
    const ELEMENTARY = 4;

    /**
     * This function uses the built in Moodle AI providers and placements
     * @param $context \stdClass
     * @param $prompt string
     * @param bool $decode bool Wheter to JSON decode the response or not
     * @return mixed
     */
    public static function make_call($context, $prompt, $lang = 'en', $decode = false)
    {
        global $USER;

        // Always return he response in the language of the course
        $prompt .= "\n\nYou must return the response in the language based on this language code: $lang.\n\n";

        $action = new \core_ai\aiactions\generate_text(
            contextid: $context->id,
            userid: $USER->id,
            prompttext: $prompt,
        );

// Send the action to the AI manager.
        $manager = \core\di::get(\core_ai\manager::class);
        $response = $manager->process_action($action);

        if ($decode) {
            return json_decode($response->get_response_data()['generatedcontent']);
        } else {
            return $response->get_response_data()['generatedcontent'];
        }
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
        // convert name to lower case and add underscores for spaces
        $string_name = strtolower($name);
        $string_name = str_replace(' ', '_', $string_name);
        // Check to see if string exists in lang file
        if (get_string_manager()->string_exists($string_name, 'block_design_ideas')) {
            $name = get_string($string_name, 'block_design_ideas');
        }

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
//            print_object($data);
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
}