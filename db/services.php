<?php
$functions = array(
    'block_design_ideas_get_course_topics' => array(
        'classname' => 'block_design_ideas_course_topics',
        'methodname' => 'execute',
        'classpath' => 'blocks/design_ideas/classes/external/course_topics.php',
        'description' => 'Generate course topics based on the course summary.',
        'type' => 'write',
        'capabilities' => '',
        'ajax' => true
    ),
    'block_design_ideas_create_course_topics' => array(
        'classname' => 'block_design_ideas_course_topics',
        'methodname' => 'create',
        'classpath' => 'blocks/design_ideas/classes/external/course_topics.php',
        'description' => 'Create selected course topics',
        'type' => 'write',
        'capabilities' => '',
        'ajax' => true
    ),

);
