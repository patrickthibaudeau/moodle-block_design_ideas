<?php
require_once("../../config.php");

use block_design_ideas\base;
use block_design_ideas\prompt;
use block_design_ideas\ai_call;

global $CFG, $OUTPUT, $USER, $PAGE, $DB;

$context = context_course::instance(47);

require_login($context->instanceid, false);


base::page(
    new moodle_url('/blocks/design_ideas/test.php', []),
    'Testing',
    'Testing',
    $context
);

echo $OUTPUT->header();

//$tables = [
//    'user',
//    'course',
//    'grade_grades',
//    'grade_items',
//    'course_modules'
//];
//
//$structure = 'You are an SQL query writer using MySQL. Having the follwoing tables: ' . "\n";
//
//foreach ($tables as $table) {
//    $columns = $DB->get_columns($table);
//    $structure .= 'Table: mdl_' . $table . ' with columns: ';
//    foreach ($columns as $column) {
//        $structure .= $column->name . ' (' . $column->type . '), ';
//    }
//    $structure .= "\n";
//}
//
//$structure .= 'column userid is always joined with mdl_user.id' . "\n";
//$structure .= 'columns course and courseid are always joined with mdl_course.id' . "\n";
//$structure .= 'With the information above, write an SQL query based on the following requirements:' . "\n";
//
//$structure .= "List all students and their grades for the course with id 45. Include the course name and the student's name.";
//$structure .= 'Include the course name, the student\'s full name, and the date they received the grade. Only return the SQL query. Never return any explanation';
//
//echo '<textarea>' . $structure . '</textarea>';
//$results = ai_call::make_call(
//    [
//        'prompt' => $structure,
//        'content' => ''
//    ]
//);
//
//print_object($results);
/**
 * Code below for getting all documents from the course and converting them to JSON
 */
$course_id = 47;
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
print_object($course_modules);
//print_object($course_modules);
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
// loop through the course modules. Based on the module id, get the instance id and get the url or fiel associated with it
foreach ($course_modules as $course_module) {
    if ($course_module->module == $resource->id) {
        $resource_instance = $DB->get_record('resource', ['id' => $course_module->instance]);
        // Files associated with the resource
        $fs = get_file_storage();
        //Context for the resource
        $context = context_module::instance($course_module->id);
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
                $available_files[] = $path . '/' . $file->get_filename();
                // Print the file path
                echo 'The file path is: ' . $path . '/' . $file->get_filename() . "<br>";
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
            $file_info = new finfo(FILEINFO_MIME_TYPE);
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
            // Print the file type
            echo 'The file type is: ' . $file_type . "<br>";
            // Print the file path
            echo 'The file path is: ' . $path . '/' . $file_name . "<br>";
        }
    }
}



echo $OUTPUT->footer();
