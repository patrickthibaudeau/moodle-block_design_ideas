<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin upgrade steps are defined here.
 *
 * @package     block_design_ideas
 * @category    upgrade
 * @copyright   2023 UIT Innovation  <thibaud@yorku.ca>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute block_design_ideas upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_block_design_ideas_upgrade($oldversion) {
    global $DB, $USER;

    $dbman = $DB->get_manager();

    if ($oldversion < 2024021700) {

        // Define table block_design_ideas_prompts to be created.
        $table = new xmldb_table('block_design_ideas_prompts');

        // Adding fields to table block_design_ideas_prompts.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('prompt', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table block_design_ideas_prompts.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('usermodified', XMLDB_KEY_FOREIGN, ['usermodified'], 'user', ['id']);

        // Conditionally launch create table for block_design_ideas_prompts.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Design_ideas savepoint reached.
        upgrade_block_savepoint(true, 2024021700, 'design_ideas');
    }

    if ($oldversion < 2024021701) {

        // Define field blockid to be added to block_design_ideas_prompts.
        $table = new xmldb_table('block_design_ideas_prompts');
        $field = new xmldb_field('blockid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'id');

        // Conditionally launch add field blockid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define index block_id_idx (not unique) to be added to block_design_ideas_prompts.
        $table = new xmldb_table('block_design_ideas_prompts');
        $index = new xmldb_index('block_id_idx', XMLDB_INDEX_NOTUNIQUE, ['blockid']);

        // Conditionally launch add index block_id_idx.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }


        // Define index name_idx (not unique) to be added to block_design_ideas_prompts.
        $table = new xmldb_table('block_design_ideas_prompts');
        $index = new xmldb_index('name_idx', XMLDB_INDEX_NOTUNIQUE, ['name']);

        // Conditionally launch add index name_idx.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Design_ideas savepoint reached.
        upgrade_block_savepoint(true, 2024021701, 'design_ideas');
    }

    if ($oldversion < 2024021800) {

        // Define field class to be added to block_design_ideas_prompts.
        $table = new xmldb_table('block_design_ideas_prompts');
        $field = new xmldb_field('class', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'prompt');

        // Conditionally launch add field class.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Design_ideas savepoint reached.
        upgrade_block_savepoint(true, 2024021800, 'design_ideas');
    }

    if ($oldversion < 2024021804) {

        // Define field callback to be dropped from block_design_ideas_prompts.
        $table = new xmldb_table('block_design_ideas_prompts');
        $field = new xmldb_field('callback');

        // Conditionally launch drop field callback.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Design_ideas savepoint reached.
        upgrade_block_savepoint(true, 2024021804, 'design_ideas');
    }

    if ($oldversion < 2024021805) {

        // Define field systemreserved to be added to block_design_ideas_prompts.
        $table = new xmldb_table('block_design_ideas_prompts');
        $field = new xmldb_field('systemreserved', XMLDB_TYPE_INTEGER, '1', null, null, null, '1', 'class');

        // Conditionally launch add field systemreserved.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Design_ideas savepoint reached.
        upgrade_block_savepoint(true, 2024021805, 'design_ideas');
    }

    if ($oldversion < 2024021900) {

        // Parameters Course Summary
        $params = [
            'name' => 'Final Course Summary',
            'description' => 'Generates a course summary (description) based on the topics and topic descriptions',
            'prompt' => "Based on the content provided, create a course description. Do not include a course title. Do not include the title \"Course description.\" Only return a description. ",
            'systemreserved' => 1, // 1 = true, 0 = false
            'class' => 'course_summary',
            'usermodified' => $USER->id,
            'timecreated' => time(),
            'timemodified' => time()
        ];
        // Create record in block_design_ideas_prompts
        $DB->insert_record('block_design_ideas_prompts', (object)$params);
        // Design_ideas savepoint reached.
        upgrade_block_savepoint(true, 2024021900, 'design_ideas');
    }

    if ($oldversion < 2024021901) {

        // Define field sortorder to be added to block_design_ideas_prompts.
        $table = new xmldb_table('block_design_ideas_prompts');
        $field = new xmldb_field('sortorder', XMLDB_TYPE_INTEGER, '4', null, null, null, '0', 'systemreserved');

        // Conditionally launch add field sortorder.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Design_ideas savepoint reached.
        upgrade_block_savepoint(true, 2024021901, 'design_ideas');
    }

    if ($oldversion < 2024022000) {
 // Update essay topics
        // Parameters Course Summary
        $params = [
            'id' => 2,
            'prompt' => "Based on the course description, write four university-grade essay topic ideas. Include a description for each topic."
                . "lways write in the same language as the course description. "
                . "Return the results in JSON format as per this example:
[
    {\"name\":\"Name of topic\",\"summary\":\"Description of topic\"},
    {\"name\":\"Name of topic\",\"summary\":\"Description of topic\"},
]",
            'systemreserved' => 1, // 1 = true, 0 = false
            'class' => 'essay_topics',
            'usermodified' => $USER->id,
            'timecreated' => time(),
            'timemodified' => time()
        ];
        // Create record in block_design_ideas_prompts
        $DB->update_record('block_design_ideas_prompts', (object)$params);
        // Design_ideas savepoint reached.
        upgrade_block_savepoint(true, 2024022000, 'design_ideas');
    }

    return true;
}
