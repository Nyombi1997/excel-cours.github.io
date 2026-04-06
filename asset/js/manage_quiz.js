/* global $, Swal, site_color, learningAdminPage */
(function () {
    if (typeof window.learningAdminPage === "undefined") {
        return;
    }

    const page = window.learningAdminPage.page;

    function getAssetBase() {
        return window.learningAdminPage.assetBase || "/asset/";
    }

    function showMessage(icon, title, text) {
        return Swal.fire({
            icon: icon,
            title: title,
            text: text || "",
            confirmButtonColor: site_color
        });
    }

    function apiPost(data, isFormData) {
        return $.ajax({
            url: "/fonctions/course_api.php",
            method: "POST",
            data: data,
            processData: !isFormData ? true : false,
            contentType: !isFormData ? "application/x-www-form-urlencoded; charset=UTF-8" : false
        });
    }

    function bindDragSort(containerSelector, itemSelector, callback) {
        let dragged = null;

        $(document).on("dragstart", itemSelector, function () {
            dragged = this;
            $(this).addClass("dragging");
        });

        $(document).on("dragend", itemSelector, function () {
            $(this).removeClass("dragging");
        });

        $(document).on("dragover", containerSelector, function (event) {
            event.preventDefault();
            const container = this;
            const afterElement = getDragAfterElement(container, event.originalEvent.clientY);

            if (!dragged) {
                return;
            }

            if (afterElement == null) {
                container.appendChild(dragged);
            } else {
                container.insertBefore(dragged, afterElement);
            }
        });

        $(document).on("drop", containerSelector, function (event) {
            event.preventDefault();
            if (typeof callback === "function") {
                callback($(this));
            }
        });
    }

    function getDragAfterElement(container, y) {
        const elements = [...container.querySelectorAll(".learning-course-card:not(.dragging), .learning-section-card:not(.dragging), .learning-item-card:not(.dragging), .learning-question-builder:not(.dragging), .learning-answer-builder:not(.dragging)")];

        return elements.reduce(function (closest, child) {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;

            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            }

            return closest;
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    function serializeIds(container, dataName) {
        const payload = [];

        container.children().each(function () {
            const id = $(this).data(dataName);
            if (id) {
                payload.push(parseInt(id, 10));
            }
        });

        return payload;
    }

    function bindCourseForm() {
        $("#js_course_form").on("submit", function (event) {
            event.preventDefault();

            const formData = new FormData(this);
            const courseId = parseInt(formData.get("course_id") || "0", 10);

            if (!formData.get("title") || formData.get("title").trim() === "") {
                showMessage("error", "Titre manquant", "Le cours doit avoir un titre.");
                return;
            }

            apiPost(formData, true).done(function (response) {
                showMessage("success", "Cours enregistré", response.msg).then(function () {
                    if (response.redirect) {
                        window.location = response.redirect;
                        return;
                    }

                    if (courseId > 0 && window.learningAdminPage.courseSlug) {
                        window.location = "/edition-cours?course=" + encodeURIComponent(window.learningAdminPage.courseSlug);
                        return;
                    }

                    window.location = "/gestion-cours";
                });
            }).fail(function (xhr) {
                const response = xhr.responseJSON || {};
                showMessage("error", "Enregistrement impossible", response.msg || "Une erreur est survenue.");
            });
        });
    }

    function bindCourseDelete() {
        $(document).on("click", ".js_delete_course", function () {
            const courseId = $(this).data("course-id");

            Swal.fire({
                icon: "warning",
                title: "Supprimer ce cours ?",
                text: "Tous les chapitres, contenus et progressions liés seront supprimés.",
                showCancelButton: true,
                confirmButtonText: "Supprimer",
                cancelButtonText: "Annuler",
                confirmButtonColor: "#dc3545"
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }

                apiPost({
                    action: "delete_course",
                    course_id: courseId
                }).done(function (response) {
                    showMessage("success", "Cours supprimé", response.msg).then(function () {
                        window.location = "/gestion-cours";
                    });
                }).fail(function (xhr) {
                    const response = xhr.responseJSON || {};
                    showMessage("error", "Suppression impossible", response.msg || "Une erreur est survenue.");
                });
            });
        });
    }

    function bindSectionDelete() {
        $(document).on("click", ".js_delete_section", function () {
            const sectionId = $(this).data("section-id");

            Swal.fire({
                icon: "warning",
                title: "Supprimer ce chapitre ?",
                text: "Les contenus qu'il contient seront aussi supprimés.",
                showCancelButton: true,
                confirmButtonText: "Supprimer",
                cancelButtonText: "Annuler",
                confirmButtonColor: "#dc3545"
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }

                apiPost({
                    action: "delete_section",
                    section_id: sectionId
                }).done(function (response) {
                    showMessage("success", "Chapitre supprimé", response.msg).then(function () {
                        window.location.reload();
                    });
                }).fail(function (xhr) {
                    const response = xhr.responseJSON || {};
                    showMessage("error", "Suppression impossible", response.msg || "Une erreur est survenue.");
                });
            });
        });
    }

    function bindSectionForm() {
        $("#js_section_form").on("submit", function (event) {
            event.preventDefault();
            const serialized = $(this).serialize();
            const currentSectionId = parseInt($(this).find('input[name="section_id"]').val() || "0", 10);
            const currentCourseSlug = window.learningAdminPage.courseSlug;

            apiPost(serialized).done(function (response) {
                showMessage("success", "Chapitre enregistré", response.msg).then(function () {
                    if (currentSectionId > 0) {
                        window.location.reload();
                        return;
                    }

                    if (response.section_id) {
                        window.location = "/gestion-chapitre?course=" + encodeURIComponent(currentCourseSlug) + "&section=" + response.section_id;
                        return;
                    }

                    window.location = "/edition-cours?course=" + encodeURIComponent(currentCourseSlug);
                });
            }).fail(function (xhr) {
                const response = xhr.responseJSON || {};
                showMessage("error", "Enregistrement impossible", response.msg || "Une erreur est survenue.");
            });
        });
    }

    function openModal() {
        $("#js_item_modal_wrapper").addClass("active");
    }

    function closeModal() {
        $("#js_item_modal_wrapper").removeClass("active");
        resetItemForm();
    }

    function resetItemForm() {
        const form = $("#js_item_form");
        if (!form.length) {
            return;
        }

        form[0].reset();
        $("#js_item_id").val("0");
        $("#js_item_type").val("video");
        $("#js_item_is_final_quiz").val("0");
        $("#js_existing_video_file").val("");
        $("#js_existing_attachment_file").val("");
        $("#js_item_passing_score").val("70");
        $("#js_video_current_file").text("");
        $("#js_attachment_current_file").text("");
        $("#js_item_modal_title").text("Ajouter un contenu");
        $("#js_questions_builder").empty();
        updateVideoPreview("");
        syncItemTypeDisplay();
    }

    function syncItemTypeDisplay() {
        const isQuiz = $("#js_item_type").val() === "quiz";
        $("#js_video_fields").toggleClass("null", isQuiz);
        $("#js_quiz_fields").toggleClass("null", !isQuiz);
        $("#js_item_passing_score_wrap").toggleClass("null", !isQuiz);
        $("#js_admin_video_preview_wrap").toggleClass("null", isQuiz);
    }

    function updateVideoPreview(previewUrl) {
        const previewWrap = $("#js_admin_video_preview_wrap");
        const preview = $("#js_admin_video_preview");

        if (!previewWrap.length || !preview.length) {
            return;
        }

        if (!previewUrl) {
            preview.attr("src", "");
            previewWrap.addClass("null");
            return;
        }

        preview.attr("src", previewUrl);
        previewWrap.removeClass("null");
    }

    function refreshAnswerNames(questionBlock) {
        const groupName = "question_correct_" + questionBlock.data("questionKey");
        questionBlock.find(".js_correct_answer").attr("name", groupName);
    }

    function addAnswerRow(questionBlock, answer) {
        const answerItem = $(`
            <div class="learning-answer-builder" draggable="true">
                <label class="learning-answer-builder__radio">
                    <input type="radio" class="js_correct_answer" name="">
                    <span>Bonne réponse</span>
                </label>
                <input type="text" class="js_answer_text" placeholder="Texte de la réponse">
                <button type="button" class="learning-icon-btn js_remove_answer">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        `);

        answerItem.find(".js_answer_text").val(answer && answer.answer_text ? answer.answer_text : (answer && answer.text ? answer.text : ""));
        answerItem.find(".js_correct_answer").prop("checked", !!(answer && (parseInt(answer.is_correct, 10) === 1 || answer.is_correct === true)));

        questionBlock.find(".js_answers_list").append(answerItem);
        refreshAnswerNames(questionBlock);
    }

    function addQuestionBlock(question) {
        const questionKey = "q_" + Date.now() + "_" + Math.floor(Math.random() * 10000);
        const questionBlock = $(`
            <div class="learning-question-builder" draggable="true">
                <div class="learning-question-builder__head">
                    <span class="learning-drag-handle"><i class="fa-solid fa-grip-vertical"></i></span>
                    <strong>Question</strong>
                    <div class="learning-question-builder__actions">
                        <button type="button" class="learning-admin-btn secondary js_add_answer">Ajouter une réponse</button>
                        <button type="button" class="learning-icon-btn js_remove_question"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </div>
                <label class="learning-field">
                    <span>Intitulé de la question</span>
                    <textarea class="js_question_title" rows="2"></textarea>
                </label>
                <label class="learning-field">
                    <span>Explication facultative</span>
                    <textarea class="js_question_explanation" rows="2"></textarea>
                </label>
                <div class="js_answers_list"></div>
            </div>
        `);

        questionBlock.attr("data-question-key", questionKey);
        questionBlock.data("questionKey", questionKey);
        questionBlock.find(".js_question_title").val(question && question.question_text ? question.question_text : (question && question.title ? question.title : ""));
        questionBlock.find(".js_question_explanation").val(question && question.explanation ? question.explanation : "");

        $("#js_questions_builder").append(questionBlock);

        const answers = question && question.answers ? question.answers : [
            { text: "", is_correct: true },
            { text: "", is_correct: false }
        ];

        answers.forEach(function (answer) {
            addAnswerRow(questionBlock, answer);
        });
    }

    function buildQuizPayload() {
        const questions = [];
        let errorMessage = "";

        $("#js_questions_builder").find(".learning-question-builder").each(function () {
            const block = $(this);
            const title = block.find(".js_question_title").val().trim();
            const explanation = block.find(".js_question_explanation").val().trim();
            const answers = [];
            let correctCount = 0;

            block.find(".learning-answer-builder").each(function () {
                const answerRow = $(this);
                const text = answerRow.find(".js_answer_text").val().trim();
                const isCorrect = answerRow.find(".js_correct_answer").is(":checked");

                if (text !== "") {
                    answers.push({
                        text: text,
                        is_correct: isCorrect
                    });
                    if (isCorrect) {
                        correctCount += 1;
                    }
                }
            });

            if (title === "") {
                errorMessage = "Chaque question doit avoir un intitulé.";
                return false;
            }

            if (answers.length < 2) {
                errorMessage = "Chaque question doit avoir au moins deux réponses.";
                return false;
            }

            if (correctCount !== 1) {
                errorMessage = "Chaque question doit avoir une seule bonne réponse.";
                return false;
            }

            questions.push({
                title: title,
                explanation: explanation,
                answers: answers
            });
        });

        if (errorMessage !== "") {
            showMessage("error", "Quiz incomplet", errorMessage);
            return null;
        }

        return questions;
    }

    function fillItemForm(payload, mode, fallbackType, sectionId) {
        resetItemForm();

        const item = payload || {};
        const itemType = item.item_type || fallbackType || "video";

        $("#js_item_modal_title").text(mode === "edit" ? "Modifier le contenu" : "Ajouter un contenu");
        $("#js_item_id").val(item.id || "0");
        $("#js_item_type").val(itemType);
        $("#js_item_title").val(item.title || "");
        $("#js_item_description").val(item.description || "");
        $("#js_item_duration_label").val(item.duration_label || "");
        $("#js_item_passing_score").val(item.passing_score || "70");
        $("#js_item_section").val(item.section_id || sectionId || window.learningAdminPage.sectionId || "");
        $("#js_item_is_preview").prop("checked", parseInt(item.is_preview || 0, 10) === 1);
        $("#js_item_is_required").prop("checked", parseInt(item.is_required || 1, 10) === 1);
        $("#js_existing_video_file").val(item.video_file || "");
        $("#js_existing_attachment_file").val(item.attachment_file || "");
        $("#js_video_current_file").text(item.video_file ? "Vidéo actuelle : " + item.video_file : "");
        $("#js_attachment_current_file").text(item.attachment_file ? "Fichier actuel : " + item.attachment_file : "");
        updateVideoPreview(item.video_file ? getAssetBase() + "videos/" + item.video_file : "");

        if (itemType === "quiz") {
            const questions = item.quiz_questions || [];
            if (questions.length) {
                questions.forEach(function (question) {
                    addQuestionBlock(question);
                });
            } else {
                addQuestionBlock();
            }
        }

        syncItemTypeDisplay();
        openModal();
    }

    function bindItemModal() {
        $(document).on("click", ".js_open_item_modal", function () {
            const mode = $(this).data("mode");
            const fallbackType = $(this).data("item-type");
            const sectionId = $(this).data("section-id");
            const payloadRaw = $(this).attr("data-item-payload");
            const payload = payloadRaw ? JSON.parse(payloadRaw) : null;

            fillItemForm(payload, mode, fallbackType, sectionId);
        });

        $("#js_close_item_modal, #js_item_modal_close").on("click", function () {
            closeModal();
        });

        $("#js_add_question").on("click", function () {
            addQuestionBlock();
        });

        $(document).on("click", ".js_add_answer", function () {
            addAnswerRow($(this).closest(".learning-question-builder"));
        });

        $(document).on("click", ".js_remove_question", function () {
            $(this).closest(".learning-question-builder").remove();
        });

        $(document).on("click", ".js_remove_answer", function () {
            const questionBlock = $(this).closest(".learning-question-builder");
            $(this).closest(".learning-answer-builder").remove();
            refreshAnswerNames(questionBlock);
        });

        $("#js_item_form").on("submit", function (event) {
            event.preventDefault();

            if ($("#js_item_title").val().trim() === "") {
                showMessage("error", "Titre manquant", "Le contenu doit avoir un titre.");
                return;
            }

            if ($("#js_item_type").val() === "quiz") {
                const payload = buildQuizPayload();
                if (!payload) {
                    return;
                }
                $("#js_quiz_payload").val(JSON.stringify(payload));
            } else {
                $("#js_quiz_payload").val("[]");
            }

            const formData = new FormData(this);

            apiPost(formData, true).done(function (response) {
                closeModal();
                showMessage("success", "Contenu enregistré", response.msg).then(function () {
                    window.location.reload();
                });
            }).fail(function (xhr) {
                const response = xhr.responseJSON || {};
                showMessage("error", "Enregistrement impossible", response.msg || "Une erreur est survenue.");
            });
        });

        $("#js_item_video_file").on("change", function () {
            const input = this;

            if (!input.files || !input.files.length) {
                const existingFile = $("#js_existing_video_file").val();
                updateVideoPreview(existingFile ? getAssetBase() + "videos/" + existingFile : "");
                return;
            }

            const objectUrl = URL.createObjectURL(input.files[0]);
            updateVideoPreview(objectUrl);
        });

        $(document).on("click", ".js_delete_item", function () {
            const itemId = $(this).data("item-id");

            Swal.fire({
                icon: "warning",
                title: "Supprimer ce contenu ?",
                showCancelButton: true,
                confirmButtonText: "Supprimer",
                cancelButtonText: "Annuler",
                confirmButtonColor: "#dc3545"
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }

                apiPost({
                    action: "delete_item",
                    item_id: itemId
                }).done(function (response) {
                    showMessage("success", "Contenu supprimé", response.msg).then(function () {
                        window.location.reload();
                    });
                }).fail(function (xhr) {
                    const response = xhr.responseJSON || {};
                    showMessage("error", "Suppression impossible", response.msg || "Une erreur est survenue.");
                });
            });
        });
    }

    if (page === "courses-list") {
        bindCourseDelete();
        bindDragSort("#js_course_sortable", ".learning-course-card", function (container) {
            apiPost({
                action: "reorder_courses",
                payload: JSON.stringify(serializeIds(container, "course-id"))
            });
        });
    }

    if (page === "course-create" || page === "course-edit") {
        bindCourseForm();
        bindCourseDelete();
    }

    if (page === "course-edit") {
        bindSectionDelete();
        bindDragSort("#js_sections_sortable", ".learning-section-card", function (container) {
            apiPost({
                action: "reorder_sections",
                payload: JSON.stringify(serializeIds(container, "section-id"))
            });
        });
    }

    if (page === "section-manage") {
        bindSectionForm();
        bindItemModal();
        bindDragSort("#js_section_items_sortable", ".learning-item-card", function (container) {
            const payload = [];
            const sectionId = container.data("section-id");

            container.find(".learning-item-card").each(function (index) {
                payload.push({
                    item_id: parseInt($(this).data("item-id"), 10),
                    section_id: parseInt(sectionId, 10),
                    position: index + 1
                });
            });

            apiPost({
                action: "reorder_items",
                payload: JSON.stringify(payload)
            });
        });
        bindDragSort("#js_questions_builder", ".learning-question-builder", function () {});
        bindDragSort(".js_answers_list", ".learning-answer-builder", function () {});
    }
})();
