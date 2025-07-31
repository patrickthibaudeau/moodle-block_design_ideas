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
     * This function uses Azure OpenAI instead of Moodle core AI
     * @param $context \stdClass
     * @param $prompt string
     * @param string $lang Language code
     * @param bool $decode Whether to JSON decode the response or not
     * @return mixed
     */
    public static function make_call($context, $prompt, $lang = 'en', $decode = false)
    {
        file_put_contents(
            '/var/www/moodledata/temp/azure_openai_log.txt',
            date('Y-m-d H:i:s') . " - Prompt: $prompt\n",
            FILE_APPEND
        );
        // Always return the response in the language of the course
        $prompt .= "\n\nYou must return the response in the language based on this language code: $lang.
        Never return the response in any other language.\n\n";

        $messages = array(
            array(
                'role' => 'user',
                'content' => $prompt
            )
        );

        $response = self::azure_openai_chat($messages);

        if ($decode) {
            return json_decode($response);
        } else {
            return $response;
        }
    }

    /**
     * Direct call to Azure OpenAI with system message
     * @param string $system_message
     * @param string $prompt
     * @param string $lang
     * @param bool $decode
     * @return mixed
     */
    public static function direct_call(string $system_message, string $prompt, string $lang = 'en', bool $decode = false)
    {
        // Always return the response in the language of the course
        $prompt .= "\n\nYou must return the response in the language based on this language code: $lang.\n\n";

        $messages = array(
            array(
                'role' => 'system',
                'content' => $system_message,
            ),
            array(
                'role' => 'user',
                'content' => $prompt
            )
        );

        $response = self::azure_openai_chat($messages);

        if ($decode) {
            return json_decode($response);
        } else {
            return self::markdown_to_html($response);
        }
    }

    /**
     * Convert markdown to HTML (simple implementation)
     * @param string $markdown
     * @return string
     */
    private static function markdown_to_html($markdown) {
        // Basic markdown to HTML conversion
        $html = $markdown;

        // Convert headers
        $html = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $html);
        $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $html);
        $html = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $html);

        // Convert bold and italic
        $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $html);

        // Convert line breaks to paragraphs
        $html = '<p>' . preg_replace('/\n\s*\n/', '</p><p>', $html) . '</p>';

        return $html;
    }

    /**
     * Get Azure OpenAI configuration from admin settings
     * @return \stdClass
     * @throws \Exception
     */
    private static function get_azure_config(): \stdClass
    {
        global $CFG;

        $config = new \stdClass();
        $config->endpoint = $CFG->block_idi_azure_openai_endpoint ?? '';
        $config->deployment = $CFG->block_idi_azure_openai_deployment ?? '';
        $config->apiversion = $CFG->block_idi_azure_openai_version ?? '';
        $config->apikey = $CFG->block_idi_azure_openai_api_key ?? '';

        // Additional parameters with defaults
        $config->max_tokens = (int) ($CFG->block_idi_azure_openai_max_tokens ?? 4096);
        $config->temperature = (float) ($CFG->block_idi_azure_openai_temperature ?? 0.7);
        $config->top_p = (float) ($CFG->block_idi_azure_openai_top_p ?? 0.95);
        $config->frequency_penalty = (float) ($CFG->block_idi_azure_openai_frequency_penalty ?? 0.0);
        $config->presence_penalty = (float) ($CFG->block_idi_azure_openai_presence_penalty ?? 0.0);
        $config->stop = $CFG->block_idi_azure_openai_stop ?? null;
        $config->stream = (bool) ($CFG->block_idi_azure_openai_stream ?? false);

        // Validate required settings
        if (empty($config->endpoint) || empty($config->deployment) || empty($config->apiversion) || empty($config->apikey)) {
            throw new \Exception('Azure OpenAI configuration is incomplete. Please check the plugin settings.');
        }

        return $config;
    }

    /**
     * This function creates an Azure OpenAI chat session
     * @param array $messages
     * @return string
     * @throws \Exception
     */
    public static function azure_openai_chat($messages): string
    {
        $config = self::get_azure_config();

        $url = rtrim($config->endpoint, '/') . "/openai/deployments/{$config->deployment}/chat/completions?api-version={$config->apiversion}";

        $headers = [
            "Content-Type: application/json",
            "api-key: {$config->apikey}"
        ];

        $data = [
            "messages" => $messages,
            "max_tokens" => $config->max_tokens,
            "temperature" => $config->temperature,
            "top_p" => $config->top_p,
            "frequency_penalty" => $config->frequency_penalty,
            "presence_penalty" => $config->presence_penalty
        ];

        // Add stop sequences if configured
        if (!empty($config->stop)) {
            // Split stop sequences by comma and trim whitespace
            $stop_sequences = array_map('trim', explode(',', $config->stop));
            $stop_sequences = array_filter($stop_sequences); // Remove empty values
            if (!empty($stop_sequences)) {
                $data["stop"] = array_slice($stop_sequences, 0, 4); // Azure OpenAI supports max 4 stop sequences
            }
        }

        // Note: Stream is not implemented in this version as it requires different handling
        // The setting is available but ignored for now

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($result === false) {
            throw new \Exception('cURL error: ' . $error);
        }

        if ($httpcode !== 200) {
            throw new \Exception('Azure OpenAI API error. HTTP Code: ' . $httpcode . '. Response: ' . $result);
        }

        $response = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response from Azure OpenAI API');
        }

        if (!isset($response['choices'][0]['message']['content'])) {
            throw new \Exception('Unexpected response format from Azure OpenAI API');
        }

        return $response['choices'][0]['message']['content'];
    }

    /**
     * Create default button
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
        global $OUTPUT;
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
     * @param string $text
     * @param int $course_id
     * @param int $section_id
     * @return object|null
     */
    public static function add_label_module($text, $course_id, $section_id = 0)
    {
        global $CFG, $DB;
        if ($module = $DB->get_record('modules', ['name' => 'label'])) {
            require_once($CFG->dirroot . '/course/modlib.php');
            // Get course object
            $course = $DB->get_record('course', ['id' => $course_id]);

            $context = \context_course::instance($course_id);

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

            $modinfo = add_moduleinfo($data, $course);

            return $modinfo;
        }
        return null;
    }

    /**
     * Add a page to a course sections
     * @param string $name
     * @param string $content
     * @param int $course_id
     * @param int $section_id
     * @param string $text
     * @return object|null
     */
    public static function add_page_module($name, $content, $course_id, $section_id = 0, $text = '')
    {
        global $CFG, $DB;
        if ($module = $DB->get_record('modules', ['name' => 'page'])) {
            require_once($CFG->dirroot . '/course/modlib.php');
            // Get course object
            $course = $DB->get_record('course', ['id' => $course_id]);
            $context = \context_course::instance($course_id);

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
            $data->coursemodule = 0;
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

            $modinfo = add_moduleinfo($data, $course);

            return $modinfo;
        }
        return null;
    }

    /**
     * Add an assignment to a course sections
     * @param string $name
     * @param string $content
     * @param int $course_id
     * @param int $section_id
     * @param string $description
     * @return object|null
     */
    public static function add_assign_module($name, $content, $course_id, $section_id = 0, $description = '')
    {
        global $CFG, $DB;
        if ($module = $DB->get_record('modules', ['name' => 'assign'])) {
            require_once($CFG->dirroot . '/course/modlib.php');
            // Get course object
            $course = $DB->get_record('course', ['id' => $course_id]);

            $context = \context_course::instance($course_id);

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
            $data->coursemodule = 0;
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

            $modinfo = add_moduleinfo($data, $course);

            return $modinfo;
        }
        return null;
    }

    /**
     * Add a url to a course sections
     * @param string $name
     * @param string $url
     * @param int $course_id
     * @param int $section_id
     * @param string $description
     * @return object|null
     */
    public static function add_url_module($name, $url, $course_id, $section_id, $description = '')
    {
        global $CFG, $DB;
        if ($module = $DB->get_record('modules', ['name' => 'url'])) {
            require_once($CFG->dirroot . '/course/modlib.php');
            $course = $DB->get_record('course', ['id' => $course_id]);
            $context = \context_course::instance($course_id);

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

            $modinfo = add_moduleinfo($mod, $course);

            return $modinfo;
        }
        return null;
    }
}
