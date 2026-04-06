/* global $, Swal, site_color, learningStudentData */
(function () {
    function initLegacyCours() {
        if (!document.querySelector(".corps_cours") || document.querySelector(".learning-student-page")) {
            return;
        }

        function changeChapter(element, index) {
            element.addEventListener("click", function () {
                document.querySelectorAll(".js_video").forEach(function (element_) {
                    element_.classList.remove("active");
                });

                document.querySelectorAll(".js_tab_cours").forEach(function (element_, index_) {
                    if (index === index_) {
                        element_.classList.add("active");
                    }
                });

                document.querySelectorAll(".js_cours_content").forEach(function (element_, index_) {
                    if (index === index_) {
                        element_.classList.remove("null");
                    }
                });

                if (element.getAttribute("data-quiz") === null) {
                    document.querySelectorAll("#js_vu_quiz").forEach(function (element_, index_) {
                        if (index !== index_) {
                            element_.classList.add("null");
                        } else {
                            element_.classList.remove("null");
                        }
                    });

                    document.querySelectorAll("#container_vu_video").forEach(function (element_, index_) {
                        if (index !== index_) {
                            element_.classList.add("null");
                        } else {
                            element_.classList.remove("null");
                        }
                    });

                    document.querySelectorAll("#video").forEach(function (element_, index_) {
                        if (index !== index_) {
                            element_.pause();
                            element_.currentTime = 0;
                        }
                    });
                }

                element.classList.add("active");
            });
        }

        document.querySelectorAll(".js_video").forEach(function (element, index) {
            changeChapter(element, index);
        });

        document.querySelectorAll(".js_tab_fichier").forEach(function (element, index) {
            element.addEventListener("click", function () {
                document.querySelectorAll(".tab").forEach(function (element_) {
                    element_.classList.remove("active");
                });
                document.querySelectorAll(".tab-content").forEach(function (element_) {
                    element_.classList.add("null");
                });
                document.querySelectorAll(".js_fichier_content").forEach(function (element_, index_) {
                    if (index === index_) {
                        element_.classList.remove("null");
                    }
                });
                element.classList.add("active");
            });
        });

        document.querySelectorAll(".js_tab_cours").forEach(function (element, index) {
            element.addEventListener("click", function () {
                document.querySelectorAll(".tab").forEach(function (element_) {
                    element_.classList.remove("active");
                });
                document.querySelectorAll(".tab-content").forEach(function (element_) {
                    element_.classList.add("null");
                });
                document.querySelectorAll(".js_cours_content").forEach(function (element_, index_) {
                    if (index === index_) {
                        element_.classList.remove("null");
                    }
                });
                element.classList.add("active");
            });
        });
    }

    function initLearningCatalog() {
        if (typeof window.learningStudentData === "undefined") {
            initLegacyCours();
            return;
        }

        function getOrderedItems() {
            return $(".js_learning_item").map(function () {
                return {
                    itemId: $(this).data("item-id"),
                    itemType: $(this).data("item-type")
                };
            }).get();
        }

        function activateItem(itemId) {
            $(".js_learning_item").removeClass("active");
            $('.js_learning_item[data-item-id="' + itemId + '"]').addClass("active");
            $(".js_learning_panel").removeClass("active");
            $('.js_learning_panel[data-item-id="' + itemId + '"]').addClass("active");
        }

        function saveProgress(itemId, watchedPercent) {
            return $.post("/fonctions/course_api.php", {
                action: "complete_item",
                item_id: itemId,
                watched_percent: watchedPercent
            });
        }

        function renderQuizResult(resultBox, response) {
            const lines = [];
            lines.push("<strong>Score : " + response.score + "%</strong>");
            lines.push("<span>Seuil requis : " + response.required_score + "%</span>");

            response.details.forEach(function (detail) {
                lines.push(
                    '<div class="learning-result-line ' + (detail.is_correct ? 'good' : 'bad') + '">' +
                        '<span>' + detail.question + '</span>' +
                        '<small>' +
                            (detail.is_correct ? "Bonne réponse." : "Mauvaise réponse.") +
                            (detail.explanation ? " " + detail.explanation : "") +
                        '</small>' +
                    '</div>'
                );
            });

            resultBox.html(lines.join("")).removeClass("null");
        }

        function updateVideoBanner(itemId, completed) {
            const panel = $('.js_learning_panel[data-item-id="' + itemId + '"]');
            const banner = panel.find(".learning-info-banner");

            if (!banner.length) {
                return;
            }

            if (completed) {
                banner
                    .addClass("done")
                    .text("Vidéo déjà visualisée à au moins 90 %. Elle compte dans votre progression. Vous pouvez la revoir quand vous voulez.");
            }
        }

        function playNextVideo(currentItemId) {
            const orderedItems = getOrderedItems();
            const currentIndex = orderedItems.findIndex(function (item) {
                return parseInt(item.itemId, 10) === parseInt(currentItemId, 10);
            });

            if (currentIndex === -1) {
                return;
            }

            for (let index = currentIndex + 1; index < orderedItems.length; index += 1) {
                if (orderedItems[index].itemType === "video") {
                    const nextItemId = orderedItems[index].itemId;
                    activateItem(nextItemId);

                    const nextVideo = $('.js_learning_panel[data-item-id="' + nextItemId + '"]').find(".js_learning_video").get(0);
                    if (nextVideo) {
                        const playPromise = nextVideo.play();
                        if (playPromise && typeof playPromise.catch === "function") {
                            playPromise.catch(function () {});
                        }
                    }
                    break;
                }
            }
        }

        $(document).on("click", ".js_learning_item", function () {
            activateItem($(this).data("item-id"));
        });

        $(".js_learning_video").each(function () {
            let maxPercent = 0;
            let alreadyCompleted = false;

            $(this).on("timeupdate", function () {
                const video = this;
                if (!video.duration) {
                    return;
                }

                const percent = (video.currentTime / video.duration) * 100;
                maxPercent = Math.max(maxPercent, percent);

                if (maxPercent >= 90 && !alreadyCompleted) {
                    alreadyCompleted = true;
                    saveProgress($(video).data("item-id"), maxPercent).done(function () {
                        const itemId = $(video).data("item-id");
                        $('.js_learning_item[data-item-id="' + itemId + '"]').addClass("done");
                        updateVideoBanner(itemId, true);
                    });
                }
            });

            $(this).on("ended", function () {
                const video = this;
                const itemId = $(video).data("item-id");
                const percent = video.duration ? (video.currentTime / video.duration) * 100 : 100;

                if (percent >= 90) {
                    saveProgress(itemId, percent).always(function () {
                        playNextVideo(itemId);
                    });
                } else {
                    playNextVideo(itemId);
                }
            });
        });

        $(".js_learning_quiz_form").on("submit", function (event) {
            event.preventDefault();

            const form = $(this);
            const itemId = form.data("item-id");
            const answers = {};
            let missingAnswer = false;

            form.find(".learning-question-card").each(function () {
                const checked = $(this).find('input[type="radio"]:checked');
                const questionName = $(this).find('input[type="radio"]').first().attr("name");
                const questionId = parseInt((questionName || "").replace("question_", ""), 10);

                if (!checked.length) {
                    missingAnswer = true;
                    return false;
                }

                answers[questionId] = checked.val();
            });

            if (missingAnswer) {
                Swal.fire({
                    icon: "warning",
                    title: "Quiz incomplet",
                    text: "Réponds à toutes les questions avant de valider.",
                    confirmButtonColor: site_color
                });
                return;
            }

            $.post("/fonctions/course_api.php", {
                action: "submit_quiz",
                item_id: itemId,
                answers: JSON.stringify(answers)
            }).done(function (response) {
                const resultBox = form.siblings(".js_learning_quiz_result");
                renderQuizResult(resultBox, response);

                if (response.passed) {
                    $('.js_learning_item[data-item-id="' + itemId + '"]').addClass("done");
                }

                Swal.fire({
                    icon: response.passed ? "success" : "info",
                    title: response.passed ? "Quiz réussi" : "Quiz enregistré",
                    text: response.msg,
                    confirmButtonColor: site_color
                });
            }).fail(function (xhr) {
                const response = xhr.responseJSON || {};
                Swal.fire({
                    icon: "error",
                    title: "Impossible d'envoyer le quiz",
                    text: response.msg || "Une erreur est survenue.",
                    confirmButtonColor: site_color
                });
            });
        });
    }

    initLearningCatalog();
})();
