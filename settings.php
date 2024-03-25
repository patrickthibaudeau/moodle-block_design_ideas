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
 * Plugin administration pages are defined here.
 *
 * @package     block_design_ideas
 * @category    admin
 * @copyright   2023 UIT Innovation  <thibaud@yorku.ca>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('block_design_ideas_settings', new lang_string('pluginname', 'block_design_ideas'));

    $settings->add(new admin_setting_configtext(
        'block_idi_cria_url',
        get_string('cria_url', 'block_design_ideas'),
        get_string('cria_url_help', 'block_design_ideas'),
        '',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configpasswordunmask(
        'block_idi_cria_token',
        get_string('cria_token', 'block_design_ideas'),
        get_string('cria_token_help', 'block_design_ideas'),
        0,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'block_idi_cria_bot_id',
        get_string('cria_bot_id', 'block_design_ideas'),
        get_string('cria_bot_id_help', 'block_design_ideas'),
        0,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'block_idi_semantic_scholar_api_url',
        get_string('semantic_scholar_api_url', 'block_design_ideas'),
        get_string('semantic_scholar_api_url_help', 'block_design_ideas'),
        'https://api.semanticscholar.org/graph/v1',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configpasswordunmask(
        'block_idi_semantic_scholar_api_key',
        get_string('semantic_scholar_api_key', 'block_design_ideas'),
        get_string('semantic_scholar_api_key_help', 'block_design_ideas'),
        '',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configselect(
        'block_idi_institution',
        get_string('institution_type', 'block_design_ideas'),
        get_string('institution_type_help', 'block_design_ideas'),
        1,
        [
            '1' => get_string('university', 'block_design_ideas'),
            '2' => get_string('college', 'block_design_ideas'),
            '3' => get_string('high_school', 'block_design_ideas'),
            '4' => get_string('elementary', 'block_design_ideas')
        ],
        PARAM_INT
    ));

    $settings->add(new admin_setting_description(
        'block_idi_prompts',
        get_string('prompts', 'block_design_ideas'),
        '<a href="' . $CFG->wwwroot . '/blocks/design_ideas/prompts.php" class="btn btn-primary mb-3">'
        . get_string('edit_prompts', 'block_design_ideas') . '</a>',
        0,
        PARAM_INT
    ));

    // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
    if ($ADMIN->fulltree) {
        // TODO: Define actual plugin settings page and add it to the tree - {@link https://docs.moodle.org/dev/Admin_settings}.
    }
}
