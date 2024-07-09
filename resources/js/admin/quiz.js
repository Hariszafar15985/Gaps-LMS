(function($) {
    "use strict";

    // *******************
    // create
    // *****************

    $("body").on("click", "#add_multiple_question", function(e) {
        e.preventDefault();
        var multipleQuestionModal = $("#multipleQuestionModal");
        var clone = multipleQuestionModal.clone();
        var id = "correctAnswerSwitch" + randomString();
        clone.find("label.js-switch").attr("for", id);
        clone.find("input.js-switch").attr("id", id);

        const random_id = randomString();
        clone.find(".panel-file-manager").attr("data-input", random_id);
        clone.find(".lfm-input").attr("id", random_id);

        clone
            .find(".main-answer-row")
            .removeClass("main-answer-row")
            .addClass("main-answer-box");

        Swal.fire({
            html: clone.html(),
            showCancelButton: false,
            showConfirmButton: false,
            customClass: {
                content: "p-0 text-left"
            },
            width: "48rem"
        });
    });

    $("body").on("click", ".add-answer-btn", function(e) {
        e.preventDefault();
        var mainRow = $(".add-answer-container .main-answer-box");

        var copy = mainRow.clone();
        copy.removeClass("main-answer-box");
        copy.find(".answer-remove").removeClass("d-none");

        const id = "correctAnswerSwitch" + randomString();
        copy.find("label.js-switch").attr("for", id);
        copy.find("input.js-switch").attr("id", id);

        const random_id = randomString();
        copy.find(".panel-file-manager").attr("data-input", random_id);
        copy.find(".lfm-input").attr("id", random_id);

        copy.find('input[type="checkbox"]').prop("checked", false);

        var copyHtml = copy.prop("innerHTML");
        const nameId = randomString();
        copyHtml = copyHtml.replace(/\[record\]/g, "[" + nameId + "]");
        copyHtml = copyHtml.replace(/\[\d+\]/g, "[" + nameId + "]");
        copy.html(copyHtml);
        copy.find('input[type="checkbox"]').prop("checked", false);
        copy.find('input[type="text"]').val("");
        mainRow.parent().append(copy);
    });

    $("body").on("click", ".answer-remove", function(e) {
        e.preventDefault();
        $(this)
            .closest(".add-answer-card")
            .remove();
    });

    function randomString() {
        var text = "";
        var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";

        for (var i = 0; i < 5; i++)
            text += possible.charAt(
                Math.floor(Math.random() * possible.length)
            );

        return text;
    }

    $("body").on("click", "#add_descriptive_question", function(e) {
        e.preventDefault();
        var multipleQuestionModal = $("#descriptiveQuestionModal");
        var clone = multipleQuestionModal.clone();

        Swal.fire({
            html: clone.html(),
            showCancelButton: false,
            showConfirmButton: false,
            customClass: {
                content: "p-0 text-left"
            },
            width: "48rem"
        });
    });

    $("body").on("click", "#add_fillInBlank_question", function(e) {
        e.preventDefault();
        let swalIdentifier = Math.floor(new Date().getTime() / 1000);
        var a = $(this).attr("data-quiz-id"),
            n = $(".fillBlankQuestionModal" + a).clone();
        Swal.fire({
            html: n.html(),
            showCancelButton: !1,
            showConfirmButton: !1,
            customClass: {
                content: `p-0 text-left el_${swalIdentifier}`
            },
            width: "60%"
        });
        /* remove any previous note editor created by summernote */
        $(`.el_${swalIdentifier} .note-editor.note-frame.card`).remove();
        $("textarea.summernote").summernote({
            /* set editable area's height */
            height: 150,
            spellCheck: true,
            toolbar: [
                ["style", ["bold", "italic", "underline", "clear"]],
                ["color", ["color"]],
                ["para", ["ul", "ol", "paragraph"]],
                ["view", ["codeview"]],
                ["custom", ["insertBlank"]]
            ],
            buttons: {
                insertBlank: insertBlankButton
            },
            callbacks: {
                onChange: function(contents) {
                    let answersCountFITB = 0;
                    let parentContainer = $(`.el_${swalIdentifier}`);
                    let previewElem = parentContainer.find("div.preview_div");
                    while (contents.indexOf("{blank}") > -1) {
                        let blankIdentifier = {
                            name: `answer[${answersCountFITB}]`,
                            id: `answer_${answersCountFITB}`
                        };
                        /* get existing value of input if it has already been entered by user */
                        let dataAttribName = `data-answer_${answersCountFITB}`;
                        let inputVal = "";
                        if (previewElem.attr(dataAttribName)) {
                            inputVal = previewElem.attr(dataAttribName);
                        }
                        contents = contents.replace(
                            "{blank}",
                            ` <input type="text" onkeyup="updateFITBPreviewAttrib(${answersCountFITB}, ${swalIdentifier})" name="${blankIdentifier.name}" id="${blankIdentifier.id}" class="form-control blankInput" value="${inputVal}" />`
                        );
                        answersCountFITB++;
                    }
                    previewElem.html(contents);
                }
            }
        });
    });

    $("body").on("click", "#add_fileUpload_question", function(e) {
        e.preventDefault();
        // alert("clicked")
        let swalIdentifier = Math.floor(new Date().getTime() / 1000);
        var a = $(this).attr("data-quiz-id"),
            i = $(".fileUploadQuestionModal" + a).clone();
        Swal.fire({
            html: i.html(),
            showCancelButton: !1,
            showConfirmButton: !1,
            customClass: {
                content: `p-0 text-left el_${swalIdentifier}`
            },
            width: "60%"
        });
    });

    $("body").on("click", "#add_matchingListText_question", function(e) {
        e.preventDefault();
        let swalIdentifier = Math.floor(new Date().getTime() / 1000);
        var a = $(this).attr("data-quiz-id"),
            i = $(".matchingListTextQuestionModal" + a).clone();
        Swal.fire({
            html: i.html(),
            showCancelButton: !1,
            showConfirmButton: !1,
            customClass: {
                content: `p-0 text-left el_${swalIdentifier}`
            },
            width: "60%"
        });
        $("body").on("click", `.el_${swalIdentifier} #add_text_pair`, function(
            e
        ) {
            addPair(swalIdentifier, "text");
        });
    });
    $("body").on("click", "#add_matchingListImage_question", function(e) {
        e.preventDefault();
        let swalIdentifier = Math.floor(new Date().getTime() / 1000);
        var a = $(this).attr("data-quiz-id"),
            i = $(".matchingListImageQuestionModal" + a).clone();
        Swal.fire({
            html: i.html(),
            showCancelButton: !1,
            showConfirmButton: !1,
            customClass: {
                content: `p-0 text-left el_${swalIdentifier}`
            },
            width: "60%"
        });
        $("body").on("click", `.el_${swalIdentifier} #add_image_pair`, function(
            e
        ) {
            addPair(swalIdentifier, "image");
        });
    });

    $("body").on("change", ".js-switch", function() {
        const $this = $(this);
        const parent = $this.closest(".js-switch-parent");

        if (this.checked) {
            $(".js-switch").each(function() {
                const switcher = $(this);
                const switcher_parent = switcher.closest(".js-switch-parent");
                const switcher_input = switcher_parent.find(
                    'input[type="checkbox"]'
                );
                switcher_input.prop("checked", false);
            });

            $this.prop("checked", true);
        }
    });

    $("body").on("click", ".save-question", function(e) {
        e.preventDefault();
        const $this = $(this);
        let form = $this.closest("form");
        let data = form.serializeObject();
        let action = form.attr("action");

        $this.addClass("loadingbar primary").prop("disabled", true);
        form.find("input").removeClass("is-invalid");
        form.find("textarea").removeClass("is-invalid");

        $.post(action, data, function(result) {
            if (result && result.code === 200) {
                Swal.fire({
                    icon: "success",
                    html:
                        '<h3 class="font-20 text-center text-dark-blue py-25">' +
                        saveSuccessLang +
                        "</h3>",
                    showConfirmButton: false,
                    width: "25rem"
                });

                setTimeout(() => {
                    window.location.reload();
                }, 500);
            }
        }).fail(err => {
            $this.removeClass("loadingbar primary").prop("disabled", false);
            var errors = err.responseJSON;
            if (errors && errors.errors) {
                Object.keys(errors.errors).forEach(key => {
                    const error = errors.errors[key];
                    let element = form.find('[name="' + key + '"]');
                    element.addClass("is-invalid");
                    element
                        .parent()
                        .find(".invalid-feedback")
                        .text(error[0]);
                });
            }
        });
    });

    // *******************
    // edit
    // *****************

    $("body").on("click", ".edit_question", function(e) {
        e.preventDefault();
        const $this = $(this);
        const question_id = $this.attr("data-question-id");
        let swalIdentifier = Math.floor(new Date().getTime() / 1000);

        loadingSwl();

        $.get("/admin/quizzes-questions/" + question_id + "/edit", function(
            result
        ) {
            if (result && result.html) {
                let $html = '<div id="editQuestion">' + result.html + "</div>";
                Swal.fire({
                    html: $html,
                    showCancelButton: false,
                    showConfirmButton: false,

                    customClass: {
                        content: `p-0 text-left el_${swalIdentifier}`
                    },
                    width: "48rem",
                    onOpen: () => {
                        const editModal = $("#editQuestion");
                        editModal
                            .find(".main-answer-row")
                            .removeClass("main-answer-row")
                            .addClass("main-answer-box");

                        const random_id = randomString();
                        editModal
                            .find(".panel-file-manager")
                            .first()
                            .attr("data-input", random_id);
                        editModal
                            .find(".lfm-input")
                            .first()
                            .attr("id", random_id);

                        const id = "correctAnswerSwitch" + randomString();
                        editModal
                            .find("label.js-switch")
                            .first()
                            .attr("for", id);
                        editModal
                            .find("input.js-switch")
                            .first()
                            .attr("id", id);
                    }
                });
                if (
                    $html.indexOf("fillBlankQuestionModal") &&
                    $html.indexOf("summernote") > -1
                ) {
                    $("textarea.summernote").summernote({
                        /* set editable area's height */
                        height: 150,
                        spellCheck: true,
                        toolbar: [
                            ["style", ["bold", "italic", "underline", "clear"]],
                            ["color", ["color"]],
                            ["para", ["ul", "ol", "paragraph"]],
                            ["view", ["codeview"]],
                            ["custom", ["insertBlank"]]
                        ],
                        buttons: {
                            insertBlank: insertBlankButton
                        },
                        callbacks: {
                            onChange: function(contents) {
                                let answersCountFITB = 0;
                                let parentContainer = $(
                                    `.el_${swalIdentifier}`
                                );
                                let previewElem = parentContainer.find(
                                    "div.preview_div"
                                );
                                while (contents.indexOf("{blank}") > -1) {
                                    let blankIdentifier = {
                                        name: `answer[${answersCountFITB}]`,
                                        id: `answer_${answersCountFITB}`
                                    };
                                    /* get existing value of input if it has already been entered by user */
                                    let dataAttribName = `data-answer_${answersCountFITB}`;
                                    let inputVal = "";
                                    if (previewElem.attr(dataAttribName)) {
                                        inputVal = previewElem.attr(
                                            dataAttribName
                                        );
                                    }
                                    contents = contents.replace(
                                        "{blank}",
                                        ` <input type="text" onkeyup="updateFITBPreviewAttrib(${answersCountFITB}, ${swalIdentifier})" name="${blankIdentifier.name}" id="${blankIdentifier.id}" class="form-control blankInput" value="${inputVal}" />`
                                    );
                                    answersCountFITB++;
                                }

                                previewElem.html(contents);
                            },
                            onInit: function() {
                                let answersCountFITB = 0;
                                let parentContainer = $(
                                    `.el_${swalIdentifier}`
                                );
                                let contents = $(
                                    ".summernote",
                                    parentContainer
                                ).val();
                                let previewElem = parentContainer.find(
                                    "div.preview_div"
                                );
                                console.log(
                                    parentContainer,
                                    contents,
                                    previewElem
                                );
                                while (contents.indexOf("{blank}") > -1) {
                                    let blankIdentifier = {
                                        name: `answer[${answersCountFITB}]`,
                                        id: `answer_${answersCountFITB}`
                                    };
                                    /* get existing value of input if it has already been entered by user */
                                    let dataAttribName = `data-answer_${answersCountFITB}`;
                                    let inputVal = "";
                                    if (previewElem.attr(dataAttribName)) {
                                        inputVal = previewElem.attr(
                                            dataAttribName
                                        );
                                    }
                                    contents = contents.replace(
                                        "{blank}",
                                        ` <input type="text" onkeyup="updateFITBPreviewAttrib(${answersCountFITB}, ${swalIdentifier})" name="${blankIdentifier.name}" id="${blankIdentifier.id}" class="form-control blankInput" value="${inputVal}" />`
                                    );
                                    answersCountFITB++;
                                }
                                previewElem.html(contents);
                            }
                        }
                    });
                    $("textarea.summernote").trigger("summernote.change");
                }
            }
        });
    });

    $("body").on("change", ".js-quiz-question-locale", function(e) {
        e.preventDefault();

        const $this = $(this);
        const $form = $(this).closest("form");
        const locale = $this.val();
        const item_id = $this.attr("data-id");

        $this.addClass("loadingbar gray");

        const path =
            "/admin/quizzes-questions/" +
            item_id +
            "/getQuestionByLocale?locale=" +
            locale;

        $.get(path, function(result) {
            const question = result.question;

            if (question.type === "descriptive") {
                const fields = ["title", "correct"];

                Object.keys(question).forEach(function(key) {
                    const value = question[key];

                    if ($.inArray(key, fields) !== -1) {
                        let element = $form.find('[name="' + key + '"]');
                        element.val(value);
                    }
                });
            } else {
                $form.find('[name="title"]').val(question.title);

                if (
                    question.quizzes_questions_answers &&
                    question.quizzes_questions_answers.length
                ) {
                    var answers = question.quizzes_questions_answers;

                    for (let answer of answers) {
                        if (answer) {
                            $form
                                .find(".js-ajax-answer-title-" + answer.id)
                                .val(answer.title);
                        }
                    }
                }
            }

            $this.removeClass("loadingbar gray");
        }).fail(err => {
            $this.removeClass("loadingbar gray");
        });
    });
    function insertBlankButton(context) {
        var ui = $.summernote.ui;

        /* create button */
        var button = ui.button({
            contents: '<i class="fa fa-minus fa-2x"></i>&nbsp;Blank',
            tooltip: "Insert Blank",
            click: function() {
                /* invoke insertText method with 'hello' on editor module. */
                context.invoke("editor.insertText", "{blank}");
            }
        });

        return button.render(); /* return button as jquery object */
    }
})(jQuery);

function updateFITBPreviewAttrib(answerId, swalIdentifier) {
    let parentContainer = $(`.el_${swalIdentifier}`);
    let previewElem = parentContainer.find('div.preview_div');
    $answerElem = $(`input[name="answer[${answerId}]"]`, previewElem);
    let inputVal = $answerElem.val();
    let dataAttribName = `data-answer_${answerId}`;
    if (previewElem) {
        previewElem.attr(dataAttribName, inputVal);
    }
};

function addPair(swalIdentifier, pairType) {
    let parentContainer = null;
    if (parseInt(swalIdentifier) > 0) {
        parentContainer = $(`.el_${swalIdentifier}`);
    } else {
        parentContainer = $(`.${swalIdentifier}`);
    }
    pairIdentifierEl = $(`input[name='pairIdentifier']`, parentContainer);
    pairIdentifier = parseInt(pairIdentifierEl.val());
    pairIdentifier++;
    if (pairType === "text") {
        let html = `<div class="col-3 pair_container">
            <div id="pair_${pairIdentifier}" class="row matchingPair">
                <div class="col-12">
                <button type="button" class="removePair-btn btn btn-sm btn-background-transparent text-danger position-absolute" title="Remove Pair" onclick="removePair(${pairIdentifier}, ${swalIdentifier})" style="right:0; height:1em; font-size:1.5em"><span aria-hidden="true">&times;</span></button>
                    <div class="form-group">
                        <label class="input-label">${quizPairTextLabel}</label>
                        <input type="text" name="answers[${pairIdentifier}][text]" class="form-control" required value="" />
                    </div>
                    <div class="form-group">
                        <label class="input-label">'Image</label>
                        <textarea type="text" name="answers[${pairIdentifier}][description]" class="form-control" required ></textarea>
                    </div>
                </div>
            </div>
        </div>`;
        $(".matchingPairs", parentContainer).append(html);
    } else {
        let html = `<div class="col-3 pair_container">
            <div id="pair_${pairIdentifier}" class="row matchingPair">
                <div class="col-12">
                <button type="button" class="removePair-btn btn btn-sm btn-background-transparent text-danger position-absolute" title="Remove Pair" onclick="removePair(${pairIdentifier}, ${swalIdentifier})" style="right:0; height:1em; font-size:1.5em"><span aria-hidden="true">&times;</span></button>
                    <div class="form-group">
                        <label class="input-label">${quizPairImageLabel}</label>
                        <div class="dropzone mx-auto">
                            <img src="/assets/default/img/upload.svg" id="preview_${pairIdentifier}" required class="upload-icon" />
                            <input type="file" id="file_${pairIdentifier}" onchange="handleFileSelect(this, this.closest('.matchingListImageQuestionModal_modal') )" accept="image/*" name="answers[${pairIdentifier}][file]" required class="upload-input cursor-pointer" />
                            <input type="hidden" name="answers[${pairIdentifier}][image]" id="image_${pairIdentifier}" required value="" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="input-label">${quizPairTextLabel}</label>
                        <input type="text" name="answers[${pairIdentifier}][text]" class="form-control" required />
                    </div>
                </div>
            </div>
        </div>`;
        $(".matchingPairs", parentContainer).append(html);
    }

    pairIdentifierEl.val(pairIdentifier);
}

function removePair(pairId, swalIdentifier) {
    let parentContainer = null;
    if (parseInt(swalIdentifier) > 0) {
        parentContainer = $(`.el_${swalIdentifier}`);
    } else {
        parentContainer = $(`.${swalIdentifier}`);
    }
    console.log(parentContainer);
    let elem = $(`#pair_${pairId}`, parentContainer);
    console.log(elem);
    let container = elem.closest(".pair_container");
    container.remove();
}

function handleFileSelect(elem, elemModal) {
    let elemId = elem.id;
    let imgElKey = elemId.replace("file", "image");
    let previewElKey = elemId.replace("file", "preview");
    console.log(elemModal);
    let base64Elem = $(`#${imgElKey}`, elemModal);
    let previewElem = $(`#${previewElKey}`, elemModal);
    console.log(base64Elem);
    let f = elem.files[0]; /* FileList object */
    let mimeType = f.type;
    let reader = new FileReader();
    /* Closure to capture the file information. */
    reader.onload = (function(theFile) {
        return function(e) {
            let binaryData = e.target.result;
            /* Converting Binary Data to base 64 */
            let base64String = window.btoa(binaryData);
            /* showing file converted to base64 */
            let imgString = `data:${mimeType};base64,${base64String}`;
            console.log(imgString);
            base64Elem.val(`${imgString}`);
            previewElem.attr("src", `${imgString}`);
            previewElem.removeClass("upload-icon");
            previewElem.addClass("my-auto");
        };
    })(f);
    /* Read in the image file as a data URL. */
    reader.readAsBinaryString(f);
}
