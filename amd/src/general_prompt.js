import ModalFactory from 'core/modal_factory';
import {get_string as getString} from 'core/str';
import ajax from 'core/ajax';
import Templates from 'core/templates';

export const init = async () => {
    document.addEventListener('click', function (event) {

        if (event.target.classList.contains('block-design-ideas-btn-ai-call')) {
            var clickedElement = event.target;
            // Get the data attributes
            var promptId = clickedElement.getAttribute('data-prompt_id');
            var courseId = clickedElement.getAttribute('data-course_id');
            var name = clickedElement.getAttribute('data-name');

            ModalFactory.create({
                title: '',
                body: Templates.render('block_design_ideas/loader', {}),
                large: false
            }).then(function (modal) {
                modal.show();
                var loaderModal = modal;

                var get_content = ajax.call([{
                    methodname: 'block_design_ideas_get_generic_content',
                    args: {
                        'courseid': courseId,
                        'promptid': promptId
                    }
                }]);

                get_content[0].done(function (results) {

                    // Close the modal
                    loaderModal.hide();
                    results.name = name;

                    // Show results.generatedcontent in a modal
                    ModalFactory.create({
                        title: getString('generated_content', 'block_design_ideas'),
                        body: Templates.render('block_design_ideas/ai_call', results),
                        large: true
                    }).then(function (modal) {
                        // Show Modal
                        modal.show();
                        // Set a timeout so that the element can be discovered in the DOM
                        setTimeout(function () {
                            // When buttin block-design-ideas-btn-copy-to-clipboard is clicked,
                            // copy the content from id block-design-ideas-content to the clipboard
                            var copyButton = document.querySelector('.block-design-ideas-btn-copy-to-clipboard');
                            if (copyButton) {
                                copyButton.addEventListener('click', function () {
                                    var content = document.getElementById('block-design-ideas-content');
                                    if (content) {
                                        navigator.clipboard.writeText(content.innerText).then(function () {
                                            getString('copied_to_clipboard', 'block_design_ideas').then(function (message) {
                                                alert(message);
                                            });
                                        }, function () {
                                            getString('copy_to_clipboard_failed', 'block_design_ideas').then(function (message) {
                                                alert(message);
                                            });
                                        });
                                    }
                                });
                            }

                        }, 1000);
                    });


                }).fail(function () {
                    alert('An error has occurred. Cannot display data');
                });
            });
        }
    });
};