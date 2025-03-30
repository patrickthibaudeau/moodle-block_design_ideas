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
    'block_design_ideas_get_section_subjects' => array(
        'classname' => 'block_design_ideas_class_notes',
        'methodname' => 'execute',
        'classpath' => 'blocks/design_ideas/classes/external/class_notes.php',
        'description' => 'Returns a list of subjects.',
        'type' => 'write',
        'capabilities' => '',
        'ajax' => true
    ),
    'block_design_ideas_create_class_notes' => array(
        'classname' => 'block_design_ideas_class_notes',
        'methodname' => 'create',
        'classpath' => 'blocks/design_ideas/classes/external/class_notes.php',
        'description' => 'Create a page module for class notes.',
        'type' => 'write',
        'capabilities' => '',
        'ajax' => true
    ),
    'block_design_ideas_get_generic_content' => array(
        'classname' => 'block_design_ideas_general_prompt',
        'methodname' => 'execute',
        'classpath' => 'blocks/design_ideas/classes/external/general_prompt.php',
        'description' => 'Return content based on a prompt',
        'type' => 'write',
        'capabilities' => '',
        'ajax' => true
    ),
    'block_design_ideas_get_learning_outcomes' => array(
        'classname' => 'block_design_ideas_learning_outcomes',
        'methodname' => 'execute',
        'classpath' => 'blocks/design_ideas/classes/external/learning_outcomes.php',
        'description' => 'Returns learning outcomes based on course summary and all subjects',
        'type' => 'write',
        'capabilities' => '',
        'ajax' => true
    ),
    'block_design_ideas_create_learning_outcomes' => array(
        'classname' => 'block_design_ideas_learning_outcomes',
        'methodname' => 'create',
        'classpath' => 'blocks/design_ideas/classes/external/learning_outcomes.php',
        'description' => 'Create a page module for the leaning outcomes',
        'type' => 'write',
        'capabilities' => '',
        'ajax' => true
    ),
    'block_design_ideas_get_final_course_summary' => array(
        'classname' => 'block_design_ideas_final_course_summary',
        'methodname' => 'execute',
        'classpath' => 'blocks/design_ideas/classes/external/final_course_summary.php',
        'description' => 'Returns new course summary based on course title and all subjects',
        'type' => 'write',
        'capabilities' => '',
        'ajax' => true
    ),
    'block_design_ideas_save_final_course_summary' => array(
        'classname' => 'block_design_ideas_final_course_summary',
        'methodname' => 'create',
        'classpath' => 'blocks/design_ideas/classes/external/final_course_summary.php',
        'description' => 'Save new course summary to course.',
        'type' => 'write',
        'capabilities' => '',
        'ajax' => true
    ),

);
