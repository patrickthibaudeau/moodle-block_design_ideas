<?php

namespace block_design_ideas;

class readings_generator extends gen_ai
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
        $prompt = str_replace('&amp;', ' ', $prompt);
        // Make the call
        $params = [
            'prompt' => strip_tags($prompt),
            'content' => ''
        ];

        $results = parent::make_call($params);

        // Only display if message has been returned
        if (!empty($results->message)) {
        // Get the data
        $json = $results->message;
        $json = str_replace('&quot;', '"', $json);
        $json = str_replace('<br />', "\n", $json);
        $params = json_decode($json);


            // Prepare keywords for semantic scholar query
            $keywords = '';
            // Add keyword to string
            $i = 0;
            foreach ($params as $key => $value) {
                $keywords .= $value->keyword . ' + ';
                $i++;
                if ($i > 2) {
                    break;
                }
            }
            $keywords = rtrim($keywords, ' + ');
            // Store keywords to session
            $_SESSION['keywords_readings_' . $course_id] = $keywords;
            // Make call to semantic scholar and get the results
            $results = self::make_call_semantic_scholar($keywords);
            $data = self::render_papers($results, $course->id, $topic_id);
            return self::render_from_template($data, $course->id);
        } else {
            $data = [
                'courseid' => $course->id,
                'nothing_found' => 'No results found. Please try again.'
            ];
           return self::render_from_template($data, $course->id);
        }
    }

    /**
     * @param $data
     * @return mixed
     */
    private static function render_from_template($data, $course_id)
    {
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

    /**
     * Make call to semantic scholar
     * @param $query
     * @param int $offset (Use next value returned)
     * @param int $limit
     * @return mixed
     */
    public static function make_call_semantic_scholar($query, $offset = 0, $limit = 10)
    {
        global $CFG;
        $url = $CFG->block_idi_semantic_scholar_api_url;
        $api_key = $CFG->block_idi_semantic_scholar_api_key;
        $call = '/paper/search';
        $data = [
            'query' => $query,
            'fields' => 'title,year,abstract,authors.name,journal,publicationTypes,isOpenAccess,openAccessPdf,url,externalIds',
            'publicationTypes' => 'JournalArticle,""',
            'offset' => $offset,
            'limit' => $limit
        ];
        return base::make_api_call($url, $api_key, $data, $call, 'GET');
    }

    /**
     * Render papers
     * @param $paper_data
     * @param $course_id
     */
    public static function render_papers($paper_data, $course_id, $topic_id = 0)
    {
        global $DB;
        // Get section data
        $section = $DB->get_record('course_sections', array('id' => $topic_id), '*', MUST_EXIST);
        // Add course_id and topic_id to paper data
        $paper_data['course_id'] = $course_id;
        $paper_data['topic_id'] = $topic_id;
        $paper_data['section'] = $section->section;
        // Set if more button is available or not
        if (isset($paper_data['next'])) {
            if (($paper_data['next'] + $paper_data['offset'] >= $paper_data['total']) || $paper_data['total'] == 0) {
                $paper_data['more'] = false;
            } else {
                $paper_data['more'] = true;
            }
        } else {
            $paper_data['more'] = false;
        }

        // Get papers from paper_data
        $papers = $paper_data['data'];

        // Format the data
        foreach($papers as $paper_key => $paper) {
            // Get authors. foreach author, reverse the name so that the last name is first.
            // If there is one author, just display the name.
            // If there are two authors, display the names separated by ' & '.
            // If there are more than two authors, display the first author followed by ' et al.'
            $authors = $paper['authors'];
            foreach ($authors as $key => $author) {
                $authors[$key]['name'] = explode(' ', $author['name']);
                $authors[$key]['name'] = array_reverse($authors[$key]['name']);
                $authors[$key]['name'] = implode(' ', $authors[$key]['name']);
                $authors[$key]['name'] = preg_replace('/ /', ', ', $authors[$key]['name'], 1);
            }
            switch (count($authors)) {
                case 0:
                    $author_text = '';
                    break;
                case 1:
                    $author_text = $authors[0]['name'];
                    break;
                case 2:
                    $author_text = $authors[0]['name'] . ' & ' . $authors[1]['name'];
                    break;
                default:
                    $author_text = $authors[0]['name'] . ' et al.';
                    break;
            }
            $paper_data['data'][$paper_key]['authorText'] = $author_text;
        }

        return $paper_data;
    }
}