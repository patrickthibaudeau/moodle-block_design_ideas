import Modal from 'core/modal';
import {get_string as getString} from 'core/str';
import ajax from 'core/ajax';
import Templates from 'core/templates';

export const init = async () => {
    document.addEventListener('click', function (event) {

        if (event.target.classList.contains('block-design-ideas-btn-ai-call-learning-outcomes')) {
            var clickedElement = event.target;
            // Get the data attributes
            var promptId = clickedElement.getAttribute('data-prompt_id');
            var courseId = clickedElement.getAttribute('data-course_id');

            Modal.create({
                title: '',
                body: Templates.render('block_design_ideas/loader', {}),
                large: false
            }).then(function (modal) {
                modal.show();
                var loaderModal = modal;

                var learning_outcomes = ajax.call([{
                    methodname: 'block_design_ideas_get_learning_outcomes',
                    args: {
                        'courseid': courseId,
                        'promptid': promptId
                    }
                }]);

                learning_outcomes[0].done(function (results) {

                    // Close the modal
                    loaderModal.hide();

                    // Show results.generatedcontent in a modal
                    Modal.create({
                        title: getString('generated_learning_outcomes', 'block_design_ideas'),
                        body: Templates.render('block_design_ideas/learning_outcomes', results),
                        large: true
                    }).then(function (modal) {
                        // Show Modal
                        modal.show();

                        // Set a timeout so that the element can be discovered in the DOM
                        setTimeout(function () {


                            // When button with class block-design-ideas-btn-create-course-topics is clicked, get all
                            // checkboxes with class block-design-ideas-topic-select and get their data attributes, put
                            // them in an array and call the ajax function to create topics
                            var createLearningOutcomesButton = document.querySelector(
                                '.block-design-ideas-btn-create-learning-outcomes');
                            createLearningOutcomesButton.addEventListener('click', function () {
                                // Hide content modal;
                                modal.hide();
                                // Show loader modal
                                Modal.create({
                                    title: '',
                                    body: Templates.render('block_design_ideas/loader', {}),
                                    large: false
                                }).then(function (modal) {
                                    modal.show();

                                    // Get content from element with id block-design-ideas-content
                                    var content = document.getElementById('block-design-ideas-content');
                                    // Call the ajax function to create topics
                                    var create_learning_outcomes = ajax.call([{
                                        methodname: 'block_design_ideas_create_learning_outcomes',
                                        args: {
                                            'courseid': courseId,
                                            'content': content.innerHTML
                                        }
                                    }]);

                                    create_learning_outcomes[0].done(function () {
                                        // Reload window
                                        window.location.reload();
                                    }).fail(function () {
                                        alert('An error has occurred. Cannot create topics');
                                    });
                                });
                            });

                        }, 1000);
                    });


                }).fail(function () {
                    alert('An error has occurred. Cannot display data');
                });
            });
        }
    });
};