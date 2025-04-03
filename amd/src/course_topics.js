import Modal from 'core/modal';
import {get_string as getString} from 'core/str';
import ajax from 'core/ajax';
import Templates from 'core/templates';

export const init = async () => {
    document.addEventListener('click', function (event) {

        if (event.target.classList.contains('block-design-ideas-btn-ai-call-course-topics')) {
            var clickedElement = event.target;
            // Get the data attributes
            var promptId = clickedElement.getAttribute('data-prompt_id');
            var numberOfTopics = clickedElement.getAttribute('data-topics');
            var courseId = clickedElement.getAttribute('data-course_id');

            Modal.create({
                title: '',
                body: Templates.render('block_design_ideas/loader', {}),
                large: false
            }).then(function (modal) {
                modal.show();
                var loaderModal = modal;

                var get_topics = ajax.call([{
                    methodname: 'block_design_ideas_get_course_topics',
                    args: {
                        'courseid': courseId,
                        'promptid': promptId,
                        'number_of_topics': numberOfTopics
                    }
                }]);

                get_topics[0].done(function (results) {

                    // Close the modal
                    loaderModal.hide();

                    // Show results.generatedcontent in a modal
                    Modal.create({
                        title: getString('generated_topics', 'block_design_ideas'),
                        body: Templates.render('block_design_ideas/course_topics', results),
                        large: true
                    }).then(function (modal) {
                        // Show Modal
                        modal.show();
                        // Set a timeout so that the element can be discovered in the DOM
                        setTimeout(function () {
                            // Select/Deselect the checkboxes if block-design-ideas-topic-select-all is checked
                            var selectAllCheckbox = document.getElementById('block-design-ideas-topic-select-all');
                            if (selectAllCheckbox) {
                                var checkboxes = document.querySelectorAll('.block-design-ideas-topic-select');
                                selectAllCheckbox.addEventListener('change', function () {
                                    checkboxes.forEach(function (checkbox) {
                                        checkbox.checked = selectAllCheckbox.checked;
                                    });
                                });
                            }

                            // When button with class block-design-ideas-btn-create-course-topics is clicked, get all
                            // checkboxes with class block-design-ideas-topic-select and get their data attributes, put
                            // them in an array and call the ajax function to create topics
                            var createTopicsButton = document.querySelector('.block-design-ideas-btn-create-course-topics');
                            createTopicsButton.addEventListener('click', function () {
                                // Get data attribute replace from button
                                var replace = createTopicsButton.getAttribute('data-replace');
                                // Get all checkboxes with class block-design-ideas-topic-select
                                var selectedCheckboxes = document.querySelectorAll('.block-design-ideas-topic-select:checked');
                                var topics = [];
                                selectedCheckboxes.forEach(function (checkbox) {
                                    // Set data attributes name and summary for topics array
                                    var topicName = checkbox.getAttribute('data-name');
                                    var topicSummary = checkbox.getAttribute('data-summary');
                                    // Add to topics array
                                    topics.push({
                                        name: topicName,
                                        summary: topicSummary
                                    });

                                });

                                // Convert topics array to JSON string
                                topics = JSON.stringify(topics);
                                // Call the ajax function to create topics
                                var create_topics = ajax.call([{
                                    methodname: 'block_design_ideas_create_course_topics',
                                    args: {
                                        'replace': replace,
                                        'courseid': courseId,
                                        'topics': topics
                                    }
                                }]);

                                create_topics[0].done(function () {
                                    // Reload window
                                    window.location.reload();
                                }).fail(function () {
                                    alert('An error has occurred. Cannot create topics');
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