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
        'prompt' => "You are a University professor building your course. "
            . "Based on the course description, create [number_of_topics] topics, no more! "
            . "Include a description for each topic. "
            . "Always write in the same language as the course description. "
            . "Return the results in JSON format as per this example:
[
    {\"name\":\"Name of topic\",\"summary\":\"Description of topic\"},
    {\"name\":\"Name of topic\",\"summary\":\"Description of topic\"}
]",
        'class' => 'course_topics',
        'systemreserved' => 1, // 1 = true, 0 = false
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
        'prompt' => "Based on the course description, write four university-grade essay topic ideas. "
            . "Include a description for each topic. "
            . "Add a statement that these are ideas to get you started. "
            . "You can adapt as required. "
            . "Always write in the same language as the course description.",
        'systemreserved' => 1, // 1 = true, 0 = false
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
        'usermodified' => $USER->id,
        'timecreated' => time(),
        'timemodified' => time()
    ];
    // Create record in block_design_ideas_prompts
    $DB->insert_record('block_design_ideas_prompts', (object)$params);
}