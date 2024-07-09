(function ($) {
    "use strict";

    if ($('#summernote').length) {
        $('#summernote').summernote({
            tabsize: 2,
            height: 400,
            placeholder: $('#summernote').attr('placeholder'),
            dialogsInBody: true,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ['para', ['paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']],
            ],
        });
    }


    $('body').on('click', '#sendForReview', function (e) {
        $(this).addClass('loadingbar primary').prop('disabled', true);
        e.preventDefault();
        $('#forDraft').val(0);
        $('#webinarForm').append(`<input type="hidden" name="action_button" value="sendForReview" />`);
        $('#webinarForm').trigger('submit');
    });

    $('body').on('click', '#saveAsDraft', function (e) {
        $(this).addClass('loadingbar primary').prop('disabled', true);
        e.preventDefault();
        $('#forDraft').val(1);
        $('#webinarForm').append(`<input type="hidden" name="action_button" value="saveAsDraft" />`);
        $('#webinarForm').trigger('submit');
    });

    $('body').on('click', '#getNextStep', function (e) {
        $(this).addClass('loadingbar primary').prop('disabled', true);
        e.preventDefault();
        $('#forDraft').val(1);
        $('#getNext').val(1);
        $('#webinarForm').trigger('submit');
    });

    $('body').on('click', '.js-get-next-step', function (e) {
        e.preventDefault();

        if (!$(this).hasClass('active')) {
            $(this).addClass('loadingbar primary').prop('disabled', true);
            const step = $(this).attr('data-step');

            $('#getStep').val(step);
            $('#forDraft').val(1);
            $('#getNext').val(1);
            $('#webinarForm').trigger('submit');
        }
    });

    $('#partnerInstructorSwitch').on('change.bootstrapSwitch', function (e) {
        let isChecked = e.target.checked;

        if (isChecked) {
            $('#partnerInstructorInput').removeClass('d-none');
            panelSearchUserSelect2();
        } else {
            $('#partnerInstructorInput').addClass('d-none');
        }
    });

    $('body').on('change', '#categories', function (e) {
        e.preventDefault();
        let category_id = this.value;
        $.get('/panel/filters/get-by-category-id/' + category_id, function (result) {

            if (result && typeof result.filters !== "undefined" && result.filters.length) {
                let html = '';
                Object.keys(result.filters).forEach(key => {
                    let filter = result.filters[key];
                    let options = [];

                    if (filter.options.length) {
                        options = filter.options;
                    }

                    html += '<div class="col-12 col-md-3">\n' +
                        '<div class="webinar-category-filters">\n' +
                        '<strong class="category-filter-title d-block">' + filter.title + '</strong>\n' +
                        '<div class="py-10"></div>\n' +
                        '\n';

                    if (options.length) {
                        Object.keys(options).forEach(index => {
                            let option = options[index];

                            html += '<div class="form-group mt-20 d-flex align-items-center justify-content-between">\n' +
                                '<label class="cursor-pointer" for="filterOption' + option.id + '">' + option.title + '</label>\n' +
                                '<div class="custom-control custom-checkbox">\n' +
                                '<input type="checkbox" name="filters[]" value="' + option.id + '" class="custom-control-input" id="filterOption' + option.id + '">\n' +
                                '<label class="custom-control-label" for="filterOption' + option.id + '"></label>\n' +
                                '</div>\n' +
                                '</div>\n';
                        })
                    }

                    html += '</div></div>';
                });

                $('#categoriesFiltersContainer').removeClass('d-none');
                $('#categoriesFiltersCard').html(html);
            } else {
                $('#categoriesFiltersContainer').addClass('d-none');
                $('#categoriesFiltersCard').html('');
            }
        })
    });

    $('body').on('click', '.cancel-accordion', function (e) {
        e.preventDefault();

        $(this).closest('.accordion-row').remove();
    });

    function randomString() {
        var text = "";
        var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";

        for (var i = 0; i < 5; i++)
            text += possible.charAt(Math.floor(Math.random() * possible.length));

        return text;
    }

    /*
    *
    * */
    function handleWebinarItemForm(form, $this) {
        let data = serializeObjectByTag(form);
        let action = form.attr('data-action');

        $this.addClass('loadingbar primary').prop('disabled', true);
        form.find('input').removeClass('is-invalid');
        form.find('textarea').removeClass('is-invalid');

        $.post(action, data, function (result) {
            if (result && result.code === 200) {
                //window.location.reload();
                Swal.fire({
                    icon: 'success',
                    html: '<h3 class="font-20 text-center text-dark-blue py-25">' + saveSuccessLang + '</h3>',
                    showConfirmButton: false,
                    width: '25rem',
                });

                setTimeout(() => {
                    window.location.reload();
                }, 500)
            }
        }).fail(err => {
            $this.removeClass('loadingbar primary').prop('disabled', false);
            var errors = err.responseJSON;

            if (errors && errors.status === 'zoom_jwt_token_invalid') {
                Swal.fire({
                    icon: 'error',
                    html: '<h3 class="font-20 text-center text-dark-blue py-25">' + zoomJwtTokenInvalid + '</h3>',
                    showConfirmButton: false,
                    width: '25rem',
                });
            }

            if (errors && errors.errors) {
                Object.keys(errors.errors).forEach((key) => {
                    const error = errors.errors[key];
                    let element = form.find('.js-ajax-' + key);

                    if (key === 'zoom-not-complete-alert') {
                        form.find('.js-zoom-not-complete-alert').removeClass('d-none');
                    } else {
                        element.addClass('is-invalid');
                        element.parent().find('.invalid-feedback').text(error[0]);
                    }
                });
            }
        })
    }

    function handleAddLessonForm(form, $this) {
        let formData = new FormData(form[0]); // create a new FormData object from the form element
        let fileInput = form.find('[name="audio-file"]')[0]; // get the file input element
        let file = fileInput.files[0]; // get the file object

        // formData.append("audio-file", file); // append the file data to the FormData object

        let action = form.attr('action');

        $this.addClass('loadingbar gray').prop('disabled', true);
        form.find('input').removeClass('is-invalid');
        form.find('textarea').removeClass('is-invalid');

        $.ajax({
            url: action,
            type: 'POST',
            data: formData, // use the FormData object as the data to be submitted
            processData: false, // prevent jQuery from processing the data
            contentType: false, // let the browser set the content type automatically
            success: function(result) {
                if (result && result.code === 200) {
                    // success handling code here
                    (Swal.fire({
                        icon: "success",
                        html: '<h3 class="font-20 text-center text-dark-blue py-25">' + saveSuccessLang + "</h3>",
                        showConfirmButton: !1,
                        width: "25rem"
                    }), setTimeout(function() {
                        window.location.reload()
                    }, 500))
                }
            },
            error: function(err) {
                // error handling code here
                $this.removeClass('loadingbar primary').prop('disabled', false);
            var errors = err.responseJSON;

            if (errors && errors.status === 'zoom_jwt_token_invalid') {
                Swal.fire({
                    icon: 'error',
                    html: '<h3 class="font-20 text-center text-dark-blue py-25">' + zoomJwtTokenInvalid + '</h3>',
                    showConfirmButton: false,
                    width: '25rem',
                });
            }

            if (errors && errors.errors) {
                Object.keys(errors.errors).forEach((key) => {
                        const error = errors.errors[key];
                        let element = form.find('.js-ajax-' + key);

                        if (key === 'zoom-not-complete-alert') {
                            form.find('.js-zoom-not-complete-alert').removeClass('d-none');
                        } else {
                            element.addClass('is-invalid');
                            element.parent().find('.invalid-feedback').text(error[0]);
                        }
                    });
                }
            }
        });
    }

    /**
     * add ticket
     * */
    $('body').on('click', '#webinarAddTicket', function (e) {
        e.preventDefault();
        const key = randomString();

        let add_ticket = $('#newTicketForm').html();
        add_ticket = add_ticket.replaceAll('record', key);

        $('#ticketsAccordion').prepend(add_ticket);

        resetDatePickers();
        feather.replace();
    });

    $('body').on('click', '.js-save-ticket', function (e) {
        e.preventDefault();
        const $this = $(this);
        let form = $this.closest('.ticket-form');

        handleWebinarItemForm(form, $this);
    });

    /*
    * add chapter
    * */

    $('body').on('click', '.save-chapter', function (e) {
        e.preventDefault();
        const $this = $(this);
        let form = $this.closest('.chapter-form');

        handleWebinarItemForm(form, $this);
    });


    $('body').on('click', '.js-add-chapter', function (e) {
        const $this = $(this);

        const webinarId = $this.attr('data-webinar-id');
        const type = $this.attr('data-type');
        const itemId = $this.attr('data-chapter');
        const locale = $this.attr('data-locale');

        const random = itemId ? itemId : randomString();

        var clone = $('#chapterModalHtml').clone();
        clone.removeClass('d-none');
        var cloneHtml = clone.prop('innerHTML');
        cloneHtml = cloneHtml.replaceAll('record', random);

        clone.html('<div id="chapterModal' + random + '">' + cloneHtml + '</div>');

        Swal.fire({
            html: clone,
            showCancelButton: false,
            showConfirmButton: false,
            customClass: {
                content: 'p-0 text-left',
            },
            width: '36rem',
            onOpen: function () {

                const modal = $('#chapterModal' + random);

                modal.find('input.js-chapter-webinar-id').val(webinarId);
                modal.find('input.js-chapter-type').val(type);

                if (itemId) {
                    modal.find('.section-title').text(editChapterLang);

                    const path = '/panel/chapters/' + itemId + '/update';
                    modal.find('.chapter-form').attr('data-action', path);

                    $.get('/panel/chapters/' + itemId + '?locale=' + locale, function (result) {
                        if (result && result.chapter) {
                            modal.find('.js-ajax-title').val(result.chapter.title);

                            const status = modal.find('.js-switch');
                            if (result.chapter.status === 'active') {
                                status.prop('checked', true);
                            } else {
                                status.prop('checked', false);
                            }

                            var localeSelect = modal.find('.js-chapter-locale');
                            localeSelect.val(locale);
                            localeSelect.addClass('js-webinar-content-locale');
                            localeSelect.attr('data-id', itemId);
                        }
                    })
                }
            }
        });
    });

    $('body').on('click', '.add-course-content-btn', function (e) {
        e.preventDefault();
        const $this = $(this);
        const type = $this.attr('data-type');
        const chapterId = $this.attr('data-chapter');

        const contentTagId = '#chapterContentAccordion' + chapterId;
        const key = randomString();
        var html = '';

        switch (type) {
            case 'file':
                const newFileForm = $('#newFileForm');
                newFileForm.find('.chapter-input').val(chapterId);
                html = newFileForm.html();

                html = html.replace(/record/g, key);

                $(contentTagId).prepend(html);

                break;
            case 'session':
                const newSessionForm = $('#newSessionForm');
                newSessionForm.find('.chapter-input').val(chapterId);
                html = newSessionForm.html();

                html = html.replace(/record/g, key);

                $(contentTagId).prepend(html);
                break;
            case 'text_lesson':
                const newTextLessonForm = $('#newTextLessonForm');
                newTextLessonForm.find('.chapter-input').val(chapterId);
                html = newTextLessonForm.html();

                html = html.replace(/record/g, key);

                html = html.replaceAll('attachments-select2', 'attachments-select2-' + key);
                html = html.replaceAll('js-content-summernote', 'js-content-summernote-' + key);
                html = html.replaceAll('js-hidden-content-summernote', 'js-hidden-content-summernote-' + key);

                $(contentTagId).prepend(html);

                $('.attachments-select2-' + key).select2({
                    multiple: true,
                    width: '100%',
                });

                $('.js-content-summernote-' + key).summernote({
                    tabsize: 2,
                    height: 400,
                    callbacks: {
                        onChange: function (contents, $editable) {
                            $('.js-hidden-content-summernote-' + key).val(contents);
                        }
                    }
                });

                break;
        }

        resetDatePickers();
        feather.replace();
    });


    /**
     * add webinar sessions
     * */

    $('body').on('click', '.js-save-session', function (e) {
        e.preventDefault();
        const $this = $(this);
        let form = $this.closest('.session-form');

        handleWebinarItemForm(form, $this);
    });

    /**
     * add webinar sessions
     * */

    $('body').on('click', '.js-save-file', function (e) {
        e.preventDefault();
        const $this = $(this);
        let form = $this.closest('.file-form');

        handleWebinarItemForm(form, $this);
    });


    $('body').on('click', '.js-save-text_lesson', function (e) {
        e.preventDefault();
        const $this = $(this);
        let form = $this.closest('.text_lesson-form');
        alert("helo")
        // handleWebinarItemForm(form, $this);
    });


    function setSelect2() {
        const selectItem = $('body #prerequisitesAccordion .prerequisites-select2');
        if (selectItem.length) {
            selectItem.select2({
                minimumInputLength: 3,
                allowClear: true,
                ajax: {
                    url: '/panel/webinars/search',
                    dataType: 'json',
                    type: "POST",
                    quietMillis: 50,
                    data: function (params) {
                        return {
                            term: params.term,
                            webinar_id: $(this).data('webinar-id')
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    text: item.title,
                                    id: item.id
                                }
                            })
                        };
                    }
                }
            })
        }

        if ($('.accordion-content-wrapper .attachments-select2').length) {
            $('.accordion-content-wrapper .attachments-select2').select2({
                multiple: true,
                width: '100%',
            });
        }
    }

    $(document).ready(function () {

        var summernoteTarget = $('.accordion-content-wrapper .js-content-summernote');
        if (summernoteTarget.length) {
            summernoteTarget.summernote({
                tabsize: 2,
                height: 400,
                callbacks: {
                    onChange: function (contents, $editable) {
                        $('.js-hidden-content-summernote').val(contents);
                    }
                }
            });
        }


        setTimeout(() => {
            setSelect2();
        }, 1000);
    });
    /**
     * add webinar prerequisites
     * */
    $('body').on('click', '#webinarAddPrerequisites', function (e) {
        e.preventDefault();
        const key = randomString();

        let add_prerequisite = $('#newPrerequisiteForm').html();
        add_prerequisite = add_prerequisite.replaceAll('record', key);
        add_prerequisite = add_prerequisite.replaceAll('prerequisites-select2', 'prerequisites-select2-' + key);

        $('#prerequisitesAccordion').prepend(add_prerequisite);

        $('.prerequisites-select2-' + key).select2({
            placeholder: $(this).data('placeholder'),
            minimumInputLength: 3,
            allowClear: true,
            ajax: {
                url: '/panel/webinars/search',
                dataType: 'json',
                type: "POST",
                quietMillis: 50,
                data: function (params) {
                    return {
                        term: params.term,
                        webinar_id: $(this).data('webinar-id')
                    };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.title,
                                id: item.id
                            }
                        })
                    };
                }
            }
        })

        feather.replace();
    });

    $('body').on('click', '.js-save-prerequisite', function (e) {
        e.preventDefault();
        const $this = $(this);
        let form = $this.closest('.prerequisite-form');
        handleWebinarItemForm(form, $this);
    });

    /**
     * add webinar FAQ
     * */
    $('body').on('click', '#webinarAddFAQ', function (e) {
        e.preventDefault();
        const key = randomString();

        let add_faq = $('#newFaqForm').html();
        add_faq = add_faq.replaceAll('record', key);

        $('#faqsAccordion').prepend(add_faq);

        feather.replace();
    });

    $('body').on('click', '.js-save-faq', function (e) {
        e.preventDefault();
        const $this = $(this);
        let form = $this.closest('.faq-form');
        handleWebinarItemForm(form, $this);
    });


    /**
     * add webinar Quiz
     * */
    $('body').on('click', '#webinarAddQuiz', function (e) {
        e.preventDefault();
        const key = randomString();

        let add_quiz = $('#newQuizForm').html();
        add_quiz = add_quiz.replaceAll('record', key);

        $('#quizzesAccordion').prepend(add_quiz);

        feather.replace();
    });

    $('body').on('click', '.js-save-quiz', function (e) {
        e.preventDefault();
        const $this = $(this);
        let form = $this.closest('.quiz-form');
        handleWebinarItemForm(form, $this);
    });


    $(document).ready(function () {

        // function updateToDatabase(table, idString) {

        //     $.post('/panel/webinars/order-items', {table: table, items: idString}, function (result) {
        //     })
        // }
        // replaced from min file
        function updateToDatabase(table, idString) {
            if (table == 'text_lessons') {
                let itemsList = $('.draggable-lists-chapter-text_lesson .ui-sortable li.accordion-row');
                idString = "";
                itemsList.each(function(index, item) {
                    if (typeof item !== 'undefined' && item.hasAttribute('data-id')) {
                        if (idString.length === 0) {
                            idString = parseInt(item.getAttribute('data-id'));
                        } else {
                            idString = idString + "," + parseInt(item.getAttribute('data-id'));
                        }
                    }
                });
            }
            $.post('/panel/webinars/order-items', {table: table, items: idString}, function (result) {
            })
        }

        function setSortable(target) {
            if (target.length) {
                target.sortable({
                    group: 'no-drop',
                    handle: '.move-icon',
                    axis: "y",
                    update: function (e, ui) {
                        var sortData = target.sortable('toArray', {attribute: 'data-id'});
                        var table = e.target.getAttribute('data-order-table');
                        updateToDatabase(table, sortData.join(','))
                    }
                });
            }
        }

        var target = $('.draggable-lists');
        var target2 = $('.draggable-lists2');
        var target3 = $('.draggable-lists3');

        const items = [];

        var draggableContentLists = $('.draggable-content-lists');
        if (draggableContentLists.length) {
            for (let item of draggableContentLists) {
                items.push($(item).attr('data-drag-class'))
            }
        }

        if (items.length) {
            for (let item of items) {
                const tag = $('.' + item);

                if (tag.length) {
                    setSortable(tag);
                }
            }
        }

        setSortable(target);

        if (target2.length) {
            setSortable(target2);
        }

        if (target3.length) {
            setSortable(target3);
        }
    })

    $('body').on('change', '.js-file-storage', function (e) {
        e.preventDefault();

        const value = $(this).attr('value');
        const formGroup = $(this).closest('.file-form');

        if (this.checked && value === 'online') {
            formGroup.find('.local-input').addClass('d-none');
            formGroup.find('.online-inputs').removeClass('d-none');
            formGroup.find('.js-downloadable-file').addClass('d-none');
            formGroup.find('.js-downloadable-file input').prop('checked', false);
        } else {
            formGroup.find('.local-input').removeClass('d-none');
            formGroup.find('.online-inputs').addClass('d-none');
            formGroup.find('.js-downloadable-file').removeClass('d-none');
            formGroup.find('.js-downloadable-file input').prop('checked', true);
        }
    });

    $('body').on('change', '.js-api-input', function (e) {
        e.preventDefault();

        const sessionForm = $(this).closest('.session-form');
        const value = this.value;

        sessionForm.find('.js-zoom-not-complete-alert').addClass('d-none');

        if (value === 'big_blue_button') {
            sessionForm.find('.js-local-link').addClass('d-none');
            sessionForm.find('.js-api-secret').removeClass('d-none');
            sessionForm.find('.js-moderator-secret').removeClass('d-none');
        } else if (value === 'zoom') {
            sessionForm.find('.js-local-link').addClass('d-none');
            sessionForm.find('.js-api-secret').addClass('d-none');
            sessionForm.find('.js-moderator-secret').addClass('d-none');

            if (hasZoomApiToken && hasZoomApiToken !== 'true') {
                sessionForm.find('.js-zoom-not-complete-alert').removeClass('d-none');
            }
        } else {
            sessionForm.find('.js-local-link').removeClass('d-none');
            sessionForm.find('.js-api-secret').removeClass('d-none');
            sessionForm.find('.js-moderator-secret').addClass('d-none');
        }
    });

})(jQuery);
