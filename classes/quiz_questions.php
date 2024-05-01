<?php

namespace block_design_ideas;

use block_design_ideas\gen_ai;

class quiz_questions extends gen_ai
{
    public static function render_results($promptid, $courseid) {
        global $DB;
        // Get resource
        $resource = $DB->get_record('modules', ['name' => 'resource']);
        // Get course Modules
        $course_modules = self::get_course_modules($courseid);
        // Get available files
        $data = new \stdClass();
        $data->files = self::get_available_files($course_modules, $courseid, $resource->id);

        return self::render_from_template($data, $courseid, $promptid);
    }

    private static function render_from_template($data, $courseid, $promptid) {
        global $OUTPUT;
        $data->course_id = $courseid;
        $data->prompt_id = $promptid;
        $data->message = $OUTPUT->render_from_template('block_design_ideas/quiz_questions_files', $data);
        // Return the data
        return $OUTPUT->render_from_template('block_design_ideas/quiz_questions', $data);
    }

    /**
     * Create deafult button
     * @param $promptid
     * @param $courseid
     * @return mixed
     */
//    public static function get_button($promptid, $courseid, $name = 'Generate')
//    {
//        global $OUTPUT, $DB;
//        // Get course Modules
//        $course_modules = self::get_course_modules($courseid);
//        $resource = $DB->get_record('modules', ['name' => 'resource']);
//        $buttons = self::get_available_files($course_modules, $courseid, $resource->id);
//        $data = [
//            'promptid' => $promptid,
//            'courseid' => $courseid,
//        ];
//        return $OUTPUT->render_from_template('block_design_ideas/quiz_questions_button', $data);
//    }

    /**
     * Get all resource and url course modules
     * @param $course_id
     * @return mixed
     */
    private static function get_course_modules($course_id) {
        global $DB;
        // Get the id for module resource
        $resource = $DB->get_record('modules', ['name' => 'resource']);
        // Get the id for module url
        $url = $DB->get_record('modules', ['name' => 'url']);
        // Get all resource and url modules for the course
        $course_module_sql = "SELECT cm.id, cm.instance, cm.module
                      FROM
                          {course_modules} cm Inner Join
                          {course_sections} cs on cm.section = cs.id
                      WHERE
                          cm.course = ? AND cs.section = ? AND module IN (?, ?)";
        $course_modules = $DB->get_records_sql($course_module_sql, [$course_id, 1, $resource->id, $url->id]);

        return $course_modules;
    }

    /**
     * Get all files from the course
     * @param array $course_modules
     * @param int $course_id
     * @param int $resource
     */
    private static function get_available_files($course_modules, $course_id, $resource_id) {
        global $CFG, $DB;
        // Create a temporary folder for the block design idea
        $path = $CFG->dataroot . '/temp/block_design_idea';
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        // now do the same for the course id
        $path = $CFG->dataroot . '/temp/block_design_idea/' . $course_id;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $available_files = [];
        $i = 0;
        // loop through the course modules. Based on the module id, get the instance id and get the url or fiel associated with it
        foreach ($course_modules as $course_module) {
            if ($course_module->module == $resource_id) {
                $resource_instance = $DB->get_record('resource', ['id' => $course_module->instance]);
                // Files associated with the resource
                $fs = get_file_storage();
                //Context for the resource
                $context = \context_module::instance($course_module->id);
                // Get the files
                $files = $fs->get_area_files($context->id, 'mod_resource', 'content', 0);
                // Loop through the files
                foreach ($files as $file) {
                    // Get the file info using finfo
                    $file_type = $file->get_mimetype();
                    $file_order = $file->get_sortorder();
                    if ($file_order == 0 && $file_type != '') {
                        // Copy the file to the path
                        $file->copy_content_to($path . '/' . $file->get_filename());
                        // add to available files array
                        $available_files[$i]['path'] = $path . '/' . $file->get_filename();
                        $available_files[$i]['file_name'] = $file->get_filename();
                        $available_files[$i]['short_file_name'] = self::shorten_text($file->get_filename());
                        $available_files[$i]['file_type'] = $file->get_mimetype();
                    } else {
                        continue;
                    }
                }
            } else {
                $url_instance = $DB->get_record('url', ['id' => $course_module->instance]);
                // Get the URL
                $url = $url_instance->externalurl;
                $file_content = @file_get_contents($url);
                // Get the file content from the URL
                if ($file_content === false) {
                    continue;
                } else {
                    // Get the file info using finfo
                    $file_info = new \finfo(FILEINFO_MIME_TYPE);
                    // Get the MIME type of the file
                    $file_type = $file_info->buffer($file_content);
                    // Get the file name
                    $file_name = $url_instance->name;
                    // Add file type to filename
                    switch ($file_type) {
                        case 'text/html':
                            $file_name .= '.html';
                            break;
                        case 'text/plain':
                            $file_name .= '.txt';
                            break;
                        case 'application/pdf':
                            $file_name .= '.pdf';
                            break;
                        case 'application/msword':
                            $file_name .= '.doc';
                            break;
                        case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                            $file_name .= '.docx';
                            break;
                    }

                    // Save the file to the path
                    file_put_contents($path . '/' . $file_name, $file_content);

                    $available_files[$i]['path'] = $path . '/' . $file_name;
                    $available_files[$i]['file_name'] = $file_name;
                    $available_files[$i]['short_file_name'] = self::shorten_text($file_name);
                    $available_files[$i]['file_type'] = $file_type;
                }
            }
            $i++;
        }
        return $available_files;
    }

 private static function shorten_text($text, $max_length = 60) {
     // Get last 4 characters from text
     $last_four = substr($text, -4);
        if (strlen($text) > $max_length) {
            $text = substr($text, 0, $max_length);
            $text = substr($text, 0, strrpos($text, ' '));
            $text .= '...';
        }
        return $text . $last_four;
    }
}