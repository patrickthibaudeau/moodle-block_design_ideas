<?php

namespace block_design_ideas;

class questions extends gen_ai
{

    /**
     * Create deafult button
     * @param $promptid
     * @param $courseid
     * @return mixed
     */
    public static function get_button($promptid, $courseid, $name = 'Quiz')
    {
        global $OUTPUT;

        $data = base::get_course_topics($courseid, $promptid, get_string('questions', 'block_design_ideas'));
        // Render buttons
        return $OUTPUT->render_from_template('block_design_ideas/questions_button', $data);
    }

    /**
     * Get the list of question types
     * @return array
     */
    public static function get_question_types(): array
    {
        return [
            'multichoice' => get_string('pluginname', 'qtype_multichoice'),
            'truefalse' => get_string('pluginname', 'qtype_truefalse'),
            'shortanswer' => get_string('pluginname', 'qtype_shortanswer'),
            'numerical' => get_string('pluginname', 'qtype_numerical'),
            'essay' => get_string('pluginname', 'qtype_essay'),
            'match' => get_string('pluginname', 'qtype_match'),
            'gapselect' => get_string('pluginname', 'qtype_gapselect')
        ];
    }

    /**
     * Return the prompt for the question type
     * @param $question_type
     * @param $content
     * @return string|void
     */
    public static function get_prompt($question_type, $content = null): string
    {
        // Set prompt
        $prompt = '';
        if (!empty($content)) {
            $prompt = 'Content: ' . $content . "\n";
            $prompt .= '---' . "\n";
        }
        switch ($question_type) {
            case 'multichoice':
                $prompt .= "For multiple choice questions, wrong answers are prefixed with a tilde (~) " .
                    "and the correct answer is prefixed with an equal sign (=).\n" .
                    "Here is a simple acceptable GIFT multiple choice format: \n" .
                    "Who's buried in Grant's tomb?{=Grant ~no one ~Napoleon ~Churchill ~Mother Teresa }\n" .
                    "---\n" .
                    "Based on the content provided, generate 10 multiple choice quiz questions in GIFT format as described above\n" .
                    "Format all questions into the following JSON format:\n" .
                    "[\n" .
                    "    {\n" .
                    "        \"question\": \"What is the capital of France?{=Paris ~London ~Berlin}\",\n" .
                    "     },\n" .
                    "    {\n" .
                    "        \"question\": \"What is the largest planet in our solar system?{=Jupiter ~Earth ~Mars}\",\n" .
                    "     },\n" .
                    "    ]";
                return $prompt;
            case 'truefalse':
                $prompt .= "In this question-type the answer indicates whether the statement is true or false. " .
                    "The answer should be written as {TRUE} or {FALSE}, or abbreviated to {T} or {F}.\n" .
                    "Here is a simple acceptable GIFT true/false format: \n" .
                    "The sky is blue.{TRUE}\n" .
                    "The Earth is flat.{FALSE}\n" .
                    "---\n" .
                    "Based on the content provided, generate 10 true/false quiz questions in GIFT format as described above\n" .
                    "Format all questions into the following JSON format:\n" .
                    "[\n" .
                    "    {\n" .
                    "        \"question\": \"The sky is blue.{TRUE}\",\n" .
                    "     },\n" .
                    "    {\n" .
                    "        \"question\": \"The Earth is flat.{FALSE}\",\n" .
                    "     },\n" .
                    "    ]";
                return $prompt;
            case 'shortanswer':
                $prompt .= "Answers in Short Answer question-type are all prefixed by an equal sign (=), " .
                    "indicating that they are all correct answers. The answers must not contain a tilde. \n" .
                    "Here is a simple acceptable GIFT short answer format: \n" .
                    "Who's buried in Grant's tomb?{=Grant =Ulysses S. Grant =Ulysses Grant}\n" .
                    "Two plus two equals {=four =4}\n" .
                    "---\n" .
                    "Based on the content provided, generate 10 short answer quiz questions in GIFT format as described above\n" .
                    "Format all questions into the following JSON format:\n" .
                    "[\n" .
                    "    {\n" .
                    "        \"question\": \"What is the capital of France?{=Paris}\",\n" .
                    "     },\n" .
                    "    {\n" .
                    "        \"question\": \"What is the largest planet in our solar system?{=Jupiter}\",\n" .
                    "     },\n" .
                    "    ]";
                return $prompt;
            case 'numerical':
                $prompt .= "The answer section for Numerical questions must start with a number sign (#). " .
                    "Numerical answers can include an error margin, which is written following the correct answer, " .
                    "separated by a colon. So for example, if the correct answer is anything between 1.5 and 2.5, " .
                    "then it would be written as follows {#2:0.5}. This indicates that 2 with an error margin of 0.5 " .
                    "is correct (i.e., the span from 1.5 to 2.5). If no error margin is specified, " .
                    "it will be assumed to be zero. " .
                    "Here is a simple acceptable GIFT numerical format: \n" .
                    "When was Ulysses S. Grant born?{#1822:5} \n" .
                    "It is a good idea to check the margins of the range, 3.141 is not counted as correct and 3.142 " .
                    "is considered in the range.\n" .
                    "What is the value of pi (to 3 decimal places)? {#3.14159:0.0005}. \n" .
                    "Optionally, numerical answers can be written as a span in the following " .
                    "format {#MinimumValue..MaximumValue}.\n" .
                    "What is the value of pi (to 3 decimal places)? {#3.141..3.142}.\n" .
                    "---\n" .
                    "Based on the content provided, generate 10 numerical quiz questions in GIFT format as described above\n" .
                    "Format all questions into the following JSON format:\n" .
                    "[\n" .
                    "    {\n" .
                    "        \"question\": \"What is the value of pi (to 3 decimal places)?{#3.14159:0.0005}\",\n" .
                    "     },\n" .
                    "    {\n" .
                    "        \"question\": \"When was Ulysses S. Grant born?{#1822:5}\",\n" .
                    "     },\n" .
                    "    ]";
                return $prompt;
            case 'essay':
                $prompt .= "An essay question is simply a question with an empty answer field. Nothing is permitted " .
                    "between the curly braces at all. " .
                    "Here is a simple acceptable GIFT essay format: \n" .
                    "Write a short biography of Dag Hammarskjöld. {}\n" .
                    "Write a short essay about France. {}\n" .
                    "---\n" .
                    "Based on the content provided, generate 10 essay quiz questions in GIFT format as described above\n" .
                    "Format all questions into the following JSON format:\n" .
                    "[\n" .
                    "    {\n" .
                    "        \"question\": \"Write a short biography of Dag Hammarskjöld. {}\"\n" .
                    "     },\n" .
                    "    {\n" .
                    "        \"question\": \"Write a short essay about France. {}\"\n" .
                    "     },\n" .
                    "    ]";
                return $prompt;
            case 'match':
                $prompt .= "Matching pairs begin with an equal sign (=) and are separated by this symbol \"->\" " .
                    "There must be at least three matching pairs.\n" .
                    "Here is a simple acceptable GIFT match format: \n" .
                    "Match the following countries with their corresponding capitals. {\n" .
                    "    =France -> Paris\n" .
                    "    =Germany -> Berlin\n" .
                    "    =Italy -> Rome\n" .
                    "    =Canada -> Ottawa\n" .
                    "}\n" .
                    "---\n" .
                    "Based on the content provided, generate 10 match quiz questions in GIFT format as described above\n" .
                    "Format all questions into the following JSON format:\n" .
                    "[\n" .
                    "    {\n" .
                    "        \"question\": \"Match the following countries with their corresponding capitals. {" .
                    " =France -> Paris =Germany -> Berlin =Italy -> Rome =Canada -> Ottawa }\"\n" .
                    "     },\n" .
                    "    {\n" .
                    "        \"question\": \"Match the following Canadian provinces with their corresponding capitals. {" .
                    " =Alberta -> Edmonton =British Columbia -> Victoria =Ontario -> Toronto =Quebec -> Quebec City }\"\n" .
                    "     },\n" .
                    "    ]";
                return $prompt;
            case 'gapselect':
                $prompt .= "The Missing Word format automatically inserts a fill-in-the-blank line (like this _____) " .
                    "in the middle of the sentence. To use the Missing Word format, place the answers where you " .
                    "want the line to appear in the sentence. \n" .
                    "Here is a simple acceptable GIFT gapselect format: \n" .
                    "Moodle costs {~lots of money =nothing ~a small amount} to download from moodle.org.\n" .
                    "The sky is {=blue ~green ~red} during the day.\n" .
                    "---\n" .
                    "Based on the content provided, generate 10 gapselect quiz questions in GIFT format as described above\n" .
                    "Make sure the answers in curly brackets ({}) are within the text. Not at the end. Never include (=...)".
                    "Format all questions into the following JSON format:\n" .
                    "[\n" .
                    "    {\n" .
                    "        \"question\": \"The sky is {=blue ~green ~red} during the day.\",\n" .
                    "     },\n" .
                    "    {\n" .
                    "        \"question\": \"Moodle costs {~lots of money =nothing ~a small amount} to download from moodle.org.\",\n" .
                    "     },\n" .
                    "    ]";
                return $prompt;
            default:
                return '';
        }
    }
}