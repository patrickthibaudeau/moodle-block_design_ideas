<?php
require_once("../../config.php");

use block_design_ideas\base;
use block_design_ideas\prompt;

global $CFG, $OUTPUT, $USER, $PAGE, $DB;


//$prompt_id = required_param('promptid', PARAM_INT);


$context = context_course::instance(45);

require_login($context->instanceid, false);
//
//$PROMPT = new prompt($prompt_id);
//if (empty($PROMPT->get_class())) {
//    $class = '\block_design_ideas\gen_ai';
//} else {
//    $class = '\block_design_ideas\\' . $PROMPT->get_class();
//}
//
//$GENAI = new $class();

base::page(
    new moodle_url('/blocks/design_ideas/test.php', []),
    'Testing',
    'Testing',
    $context
);

$url = 'https://api.semanticscholar.org/graph/v1';
$api_key = 'GOtfB4itdR8HRsqVk5Ivr6o9qMOMDL6P1f8cxr7K';
$call = '/paper/search';
$limit = 10;
$data = [
    'query' => 'quantum computing',
    'fields' => 'title,year,abstract,authors.name,journal,publicationTypes,isOpenAccess,openAccessPdf,url,externalIds',
    'publicationTypes' => 'JournalArticle,""',
    'offset' => 10,
    'limit' => $limit
];

echo $OUTPUT->header();
//$command = escapeshellcmd('/usr/bin/python3 /var/www/html/blocks/design_ideas/hello.py');
//$output = shell_exec($command);
//echo $output;
$papers_found = base::make_api_call($url, $api_key, $data, $call, 'GET');
//print_object($papers_found);
//die;
//total papers found
$total_papers = $papers_found['total'];
// Offset
$offset = $papers_found['offset'];
// limit
$next = $papers_found['next'];
// total pages
$total_pages = ceil($total_papers / $limit);
// Data
$papers = $papers_found['data'];
//print_object($papers_found);
//die;
foreach ($papers as $paper) {

    echo '<h4>' . $paper['title'] . '</h4>';
    foreach ($paper['authors'] as $author) {
        echo $author['name'] . ', ';
    }
    echo ' (' . $paper['year'] . '). ';
    if (!empty($paper['publicationTypes'])) {
        foreach ($paper['publicationTypes'] as $type) {
            $string = $type;
            $result = preg_replace('/(?<=\\w)(?=[A-Z])/', " $1", $string);
            echo '<i>' . $result . '</i>, ';
        }
    }
    echo '<p>' . $paper['abstract'] . '</p>';
    echo '<br>Total pages: ' . $total_pages . '<br>';
}
echo $OUTPUT->footer();
