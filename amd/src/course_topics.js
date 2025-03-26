import ModalFactory from 'core/modal_factory';
import {get_string as getString} from 'core/str';
import ajax from 'core/ajax';
import Templates from 'core/templates';

export const init = async () => {
    // Check if the button exists before adding the event listener
    const button = document.getElementsByClassName('block-design-ideas-btn-ai-call')[0];
    document.addEventListener('click', function(event) {


        if (event.target.classList.contains('block-design-ideas-btn-ai-call')) {
            var clickedElement = event.target;
            // Get the data attributes
            var promptId = clickedElement.getAttribute('data-prompt_id');
            var numberOfTopics = clickedElement.getAttribute('data-topics');
            var courseId = clickedElement.getAttribute('data-course_id');

            ModalFactory.create({
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

                    console.log('results', results);

                    // Close the modal
                    loaderModal.hide();

                    // Show results.generatedcontent in a modal
                    ModalFactory.create({
                        title: 'Generated Topics',
                        body: Templates.render('block_design_ideas/course_topics', results),
                        large: true
                    }).then(function (modal) {
                        modal.show();
                    });


                }).fail(function () {
                    alert('An error has occurred. Cannot display data');
                });
            });
        }
    });
};