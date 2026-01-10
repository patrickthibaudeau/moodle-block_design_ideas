<?php
/*
 * Author: Admin User
 * Create Date: 17-02-2024
 * License: LGPL 
 * 
 */

namespace block_design_ideas;

abstract class crud
{


    /**
     * /* string
     **/
    private $table;

    /**
     * /* int
     **/
    private $id;

    /**
     * Get record
     *
     * @global \moodle_database $DB
     *
     */
    public function get_record()
    {
        global $DB;
        $result = $DB->get_record($this->table, ['id' => $this->id], '*', MUST_EXIST);
        return $result;

    }

    /**
     * Delete the row
     *
     * @global \moodle_database $DB
     *
     */
    public function delete_record()
    {
        global $DB;
        $DB->delete_records($this->table, ['id' => $this->id]);
    }

    /**
     * Insert record into selected table
     * @param object $data
     * @global \stdClass $USER
     * @global \moodle_database $DB
     */
    public function insert_record($data)
    {
        global $DB, $USER;

        if (!isset($data->timecreated)) {
            $data->timecreated = time();
        }

        if (!isset($data->imemodified)) {
            $data->timemodified = time();
        }

        //Set user
        $data->usermodified = $USER->id;

        $id = $DB->insert_record($this->table, $data);

        return $id;
    }

    /**
     * Update record into selected table
     * @param object $data
     * @global \stdClass $USER
     * @global \moodle_database $DB
     */
    public function update_record($data)
    {
        global $DB, $USER;

        if (!isset($data->timemodified)) {
            $data->timemodified = time();
        }

        //Set user
        $data->usermodified = $USER->id;

        $id = $DB->update_record($this->table, $data);

        return $id;
    }

    /**
     * /* get id
     **/
    public function get_id(): int
    {
        return $this->id;
    }

    /**
     * /* get table
     **/
    public function get_table(): string
    {
        return $this->table;
    }

    public function set_id($id)
    {
        $this->id = $id;
    }

    public function set_table($table)
    {
        $this->table = $table;
    }

}