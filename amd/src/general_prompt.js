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

                            // For future features

                        }, 1000);
                    });


                }).fail(function () {
                    alert('An error has occurred. Cannot display data');
                });
            });
        }
    });
};