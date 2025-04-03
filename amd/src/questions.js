import Modal from 'core/modal';
import {get_string as getString} from 'core/str';
import ajax from 'core/ajax';
import Templates from 'core/templates';

export const init = async () => {
    document.addEventListener('click', function (event) {

            if (event.target.classList.contains('block-design-ideas-btn-ai-call-questions')) {
                var clickedElement = event.target;
                // Get the data attributes
                var promptId = clickedElement.getAttribute('data-prompt_id');
                var sectionId = clickedElement.getAttribute('data-topic_id');
                var courseId = clickedElement.getAttribute('data-course_id');

                var get_subjects = ajax.call([{
                    methodname: 'block_design_ideas_get_questions',
                    args: {
                        'courseid': courseId,
                        'promptid': promptId,
                        'sectionid': sectionId
                    }
                }]);

                get_subjects[0].done(function (results) {
                    // Show results.generatedcontent in a modal
                    Modal.create({
                        title: getString('questions_generator', 'block_design_ideas'),
                        body: Templates.render('block_design_ideas/questions', results),
                        large: true
                    }).then(function (modal) {
                        // Show Modal
                        modal.show();

                        // Set a timeout so that the element can be discovered in the DOM
                        setTimeout(function () {
                            // When thebutton with id block-design-ideas-btn-generate is clicked
                            // Run an ajax call to generate the content
                            var generateButton = document.getElementById('block-design-ideas-btn-generate');
                            generateButton.addEventListener('click', function () {
                                // Get current button text
                                var buttonText = generateButton.innerHTML;
                                // Empty the generated content
                                var generatedContent = document.getElementById('block-design-ideas-generated-questions');
                                generatedContent.innerHTML = '';
                                // Get the contetn from the form
                                var content = document.getElementById('id_content').value;
                                // Get the selected question type for element with id block-design-ideas-question-type
                                var questionType = document.getElementById('id_question_type').value;

                                // Replace the text of the button with a loading spinner
                                generateButton.innerHTML = '<span class="spinner-border spinner-border-sm" ' +
                                    'role="status" aria-hidden="true"></span>';
                                // Get generated questions
                                var get_questions = ajax.call([{
                                    methodname: 'block_design_ideas_generate_questions',
                                    args: {
                                        'courseid': courseId,
                                        'questiontype': questionType,
                                        'content': content
                                    }
                                }]);

                                get_questions[0].done(function (results) {
                                    // Replace the text of the button with the original text
                                    generateButton.innerHTML = buttonText;
                                    // Insert the results into element with id block-design-ideas-generated-questions
                                    generatedContent.innerHTML = results.html;

                                    setTimeout(function () {
                                        // Select/Deselect the checkboxes if block-design-ideas-subject-select-all is checked
                                        var selectAllCheckbox = document.getElementById('block-design-ideas-subject-select-all');
                                        if (selectAllCheckbox) {
                                            var checkboxes = document.querySelectorAll('.block-design-ideas-subject-select');
                                            selectAllCheckbox.addEventListener('change', function () {
                                                checkboxes.forEach(function (checkbox) {
                                                    checkbox.checked = selectAllCheckbox.checked;
                                                });
                                            });
                                        }
                                        // When button with id block-design-ideas-btn-create-questions is clicked
                                        // Run an ajax call to create the questions
                                        var createButton = document.getElementById('block-design-ideas-btn-create-questions');
                                        createButton.addEventListener('click', function () {
                                            // Get create button text
                                            var createButtonText = createButton.innerHTML;
                                            // Get the selected checkboxes
                                            var selectedCheckboxes = document.querySelectorAll(
                                                '.block-design-ideas-subject-select:checked'
                                            );
                                            // Get the ids of the selected checkboxes
                                            var selectedIds = [];
                                            selectedCheckboxes.forEach(function (checkbox) {
                                                // Add the data-gift attribute to the array
                                                var gift = checkbox.getAttribute('data-gift');
                                                selectedIds.push({question: gift});
                                            });
                                            // Replace the text of the button with a loading spinner
                                            createButton.innerHTML = '<span class="spinner-border spinner-border-sm" ' +
                                                'role="status" aria-hidden="true"></span>';
                                            // Json stringify the selectedIds array
                                            selectedIds = JSON.stringify(selectedIds);
                                            // Create questions
                                            var create_questions = ajax.call([{
                                                methodname: 'block_design_ideas_save_questions',
                                                args: {
                                                    'question_type': questionType,
                                                    'courseid': courseId,
                                                    'questions': selectedIds
                                                }
                                            }]);

                                            create_questions[0].done(function () {
                                                createButton.innerHTML = createButtonText;
                                                // Reload page
                                                alert('Questions created successfully');
                                            });
                                        });
                                    });
                                });
                            });

                        }, 1000);
                    });


                }).fail(function () {
                    alert('An error has occurred. Cannot display data');
                });

            }
        }
    );
};