import ModalFactory from 'core/modal_factory';
//import {get_string as getString} from 'core/str';
import ajax from 'core/ajax';
import Templates from 'core/templates';

export const init = async () => {
    document.addEventListener('click', function (event) {

        if (event.target.classList.contains('block-design-ideas-btn-ai-call-essay-topics')) {
            var clickedElement = event.target;
            // Get the data attributes
            var promptId = clickedElement.getAttribute('data-prompt_id');
            var sectionId = clickedElement.getAttribute('data-topic_id');
            var courseId = clickedElement.getAttribute('data-course_id');

            ModalFactory.create({
                title: '',
                body: Templates.render('block_design_ideas/loader', {}),
                large: false
            }).then(function (modal) {
                modal.show();
                var loaderModal = modal;

                var get_essay_topics = ajax.call([{
                    methodname: 'block_design_ideas_get_essay_topics',
                    args: {
                        'courseid': courseId,
                        'promptid': promptId,
                        'sectionid': sectionId
                    }
                }]);

                get_essay_topics[0].done(function (results) {

                    // Close the modal
                    loaderModal.hide();

                    // Show results.generatedcontent in a modal
                    ModalFactory.create({
                        title: results.section_name,
                        body: Templates.render('block_design_ideas/essay_topics', results),
                        large: true
                    }).then(function (modal) {
                        // Show Modal
                        modal.show();

                        // Set a timeout so that the element can be discovered in the DOM
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

                            // When button with class block-design-ideas-btn-create-course-topics is clicked, get all
                            // checkboxes with class block-design-ideas-topic-select and get their data attributes, put
                            // them in an array and call the ajax function to create topics
                            var createSubjectsButton = document.querySelector('.block-design-ideas-btn-create-essay-topics');
                            createSubjectsButton.addEventListener('click', function () {
                                // Hide content modal;
                                modal.hide();
                                // Show loader modal
                                ModalFactory.create({
                                    title: '',
                                    body: Templates.render('block_design_ideas/loader', {}),
                                    large: false
                                }).then(function (modal) {
                                    modal.show();

                                    // Get data attribute replace from button
                                    var section = createSubjectsButton.getAttribute('data-section');
                                    // Get all checkboxes with class block-design-ideas-topic-select
                                    var selectedCheckboxes = document.querySelectorAll(
                                        '.block-design-ideas-subject-select:checked');
                                    var subjects = [];
                                    selectedCheckboxes.forEach(function (checkbox) {
                                        // Set data attributes name and summary for topics array
                                        var subjectName = checkbox.getAttribute('data-name');
                                        var subjectSummary = checkbox.getAttribute('data-summary');
                                        // Add to topics array
                                        subjects.push({
                                            name: subjectName,
                                            summary: subjectSummary,
                                        });

                                    });

                                    // Convert topics array to JSON string
                                    subjects = JSON.stringify(subjects);
                                    // Call the ajax function to create topics
                                    var create_class_notes = ajax.call([{
                                        methodname: 'block_design_ideas_create_essay_topics',
                                        args: {
                                            'section': section,
                                            'courseid': courseId,
                                            'subjects': subjects
                                        }
                                    }]);

                                    create_class_notes[0].done(function () {
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