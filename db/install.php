<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Install script for mod_bigbluebuttonbn.
 *
 * @package    mod_bigbluebuttonbn
 * @copyright  2022 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Perform the post-install procedures.
 */
function xmldb_block_design_ideas_install()
{
    global $DB, $USER;

    // Parameters for Course topics
    $params = [
        'name' => 'Course Topics',
        'description' => 'Generates course topics based on the course description (summary).',
        'prompt' => "Based on the course description, create [number_of_topics] topics, no more! "
            . "Include a description for each topic. "
            . "Always write in the same language as the course description. "
            . "Return the results in JSON format as per this example:
[
    {\"name\":\"Name of topic\",\"summary\":\"Description of topic\"},
    {\"name\":\"Name of topic\",\"summary\":\"Description of topic\"}
]",
        'class' => 'course_topics',
        'systemreserved' => 1, // 1 = true, 0 = false
        'sortorder' => 0,
        'usermodified' => $USER->id,
        'timecreated' => time(),
        'timemodified' => time()
    ];
    // Create record in block_design_ideas_prompts
    $DB->insert_record('block_design_ideas_prompts', (object)$params);


    // Parameters for Class notes
    $params = [
        'name' => 'Course Notes',
        'description' => 'Generate class notes based on the section title and description.',
        'prompt' => 'You are a professor offering a course entitled [course_title] with the following description: ' .
            '[course_description]. Create class note subjects with no description on the specific topic of ' .
            '[course_topic] and the following topic description [topic_description]. ' .
            'Return the results in JSON format as per this example: ' .
            '[ {"subject":"the subject"}, {"subject":"the second subject"} ]',
        'class' => 'class_notes',
        'systemreserved' => 1, // 1 = true, 0 = false
        'sortorder' => 1,
        'usermodified' => $USER->id,
        'timecreated' => time(),
        'timemodified' => time()
    ];
    // Create record in block_design_ideas_prompts
    $DB->insert_record('block_design_ideas_prompts', (object)$params);

    // Parameters for Readings
    $params = [
        'name' => 'Readings',
        'description' => 'Generates readings ideas based on the topic and topic description.',
        'prompt' => "Based on the content provided, please suggest relevant readings from journal articles or book chapters of at least 4000 words. ",
        'systemreserved' => 1, // 1 = true, 0 = false
        'class' => 'readings_generator',
        'sortorder' => 2,
        'usermodified' => $USER->id,
        'timecreated' => time(),
        'timemodified' => time()
    ];
    // Create record in block_design_ideas_prompts
    $DB->insert_record('block_design_ideas_prompts', (object)$params);

    // Parameters for Learning Outcomes
    $params = [
        'name' => 'Learning Outcomes',
        'description' => 'Generates learning outcomes based on the course summary and all sections',
        'prompt' => " 	Given the course summary above and the sections, write me a list of learning outcomes for the course",
        'systemreserved' => 1, // 1 = true, 0 = false
        'class' => 'learning_outcomes',
        'sortorder' => 3,
        'usermodified' => $USER->id,
        'timecreated' => time(),
        'timemodified' => time()
    ];
    // Create record in block_design_ideas_prompts
    $DB->insert_record('block_design_ideas_prompts', (object)$params);

    // Parameters Course Summary
    $params = [
        'name' => 'Final Course Summary',
        'description' => 'Generates a course summary (description) based on the topics and topic descriptions',
        'prompt' => "Based on the content provided, create a course description. Do not include a course title. Do not include the title \"Course description.\" Only return a description. ",
        'systemreserved' => 1, // 1 = true, 0 = false
        'class' => 'course_summary',
        'sortorder' => 4,
        'usermodified' => $USER->id,
        'timecreated' => time(),
        'timemodified' => time()
    ];
    // Create record in block_design_ideas_prompts
    $DB->insert_record('block_design_ideas_prompts', (object)$params);


    // Parameters for Essay topics
    $params = [
        'name' => 'Essay Topics',
        'description' => 'Generates essay topics based on the course description (summary).',
        'prompt' => "Based on the course description, write four essay topic ideas. Include a description for each topic."
            . "lways write in the same language as the course description. "
            . "Return the results in JSON format as per this example:
[
    {\"name\":\"Name of topic\",\"summary\":\"Description of topic\"},
    {\"name\":\"Name of topic\",\"summary\":\"Description of topic\"},
]",
        'systemreserved' => 1, // 1 = true, 0 = false
        'class' => 'essay_topics',
        'sortorder' => 5,
        'usermodified' => $USER->id,
        'timecreated' => time(),
        'timemodified' => time()
    ];
    // Create record in block_design_ideas_prompts
    $DB->insert_record('block_design_ideas_prompts', (object)$params);

    // Parameters for Activity Artist
    $params = [
        'name' => 'Activity Artist',
        'description' => 'Generates activity ideas based on the course description (summary).',
        'prompt' => "What experiential learning activities may you suggest based on the learning outcomes and the course content? "
            . "Please provide the answer in a list format. "
            . "Always write in the same language as the course description.",
        'systemreserved' => 1, // 1 = true, 0 = false
        'sortorder' => 6,
        'usermodified' => $USER->id,
        'timecreated' => time(),
        'timemodified' => time()
    ];
    // Create record in block_design_ideas_prompts
    $DB->insert_record('block_design_ideas_prompts', (object)$params);

    // Parameters for Assessment Assistant
    $params = [
        'name' => 'Assessment Assistant',
        'description' => 'Generates assessment ideas based on the course description (summary).',
        'prompt' => "What authentic assessment ideas can you give me based on the learning outcomes and the course content? "
            . "Please provide the answer in a list format. "
            . "Always write in the same language as the course description.",
        'systemreserved' => 1, // 1 = true, 0 = false
        'sortorder' => 7,
        'usermodified' => $USER->id,
        'timecreated' => time(),
        'timemodified' => time()
    ];
    // Create record in block_design_ideas_prompts
    $DB->insert_record('block_design_ideas_prompts', (object)$params);




}