<?php
require_once("../../config.php");

use block_design_ideas\base;
use block_design_ideas\prompt;
use block_design_ideas\gen_ai;

global $CFG, $OUTPUT, $USER, $PAGE, $DB;


$courseid = 5;
$context = context_course::instance($courseid);
require_login($context->instanceid, false);


base::page(
    new moodle_url('/blocks/design_ideas/test.php', []),
    'Testing',
    'Testing',
    $context
);

echo $OUTPUT->header();







// Get prompt
$prompt = 'You are a professor. Provide class notes on the specific subject of Introduction to Database Management in point form using full sentences. Return the results as formatted HTML. Omit adding styles in the head tag. Do not include the author of the notes.';
// Make the call
$content = gen_ai::make_call($context, $prompt);

print_object($content);

// Split the text into lines
$lines = explode("\n", $content);

// Initialize arrays for points and other text
$points = [];
$otherText = [];

// Process each line
foreach ($lines as $line) {
    // Check if the line starts with a number followed by a period
    if (preg_match('/^(\d+\.)|(-)/', $line)) {
        $points[] = $line;
    } else {
        $otherText[] = $line;
    }
}

$html = '<ol>';
foreach ($points as $point) {
    $html .= '<li>' . $point . '</li>';
}
$html .= '</ol>';

// Combine other text into a single string
$otherTextString = implode("\n", $otherText);

echo $html;

echo $OUTPUT->footer();
