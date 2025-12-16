{literal}
    <script src="/design/boostra_mini_norm/js/select2/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const detailOverlay = document.getElementById('detailModal');
            const detailContent = detailOverlay.querySelector('.tickets-modal-content');
            const createOverlay = document.getElementById('createModal');
            let currentTicketId = null;

            // Валидация файлов
            function validateFile(file) {
                // Список разрешенных расширений
                const allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif', 'zip', 'rar', 'txt'];
                // Максимальный размер файла (100 МБ в байтах)
                const maxFileSize = 100 * 1024 * 1024;

                const fileName = file.name;
                const fileExtension = fileName.split('.').pop().toLowerCase();

                if (!allowedExtensions.includes(fileExtension)) {
                    return {
                        valid: false,
                        error: `Недопустимый формат файла: ${fileName}. Разрешены только: ${allowedExtensions.join(', ')}`
                    };
                }

                if (file.size > maxFileSize) {
                    return {
                        valid: false,
                        error: `Файл слишком большой (>100 МБ): ${fileName}`
                    };
                }

                return {valid: true};
            }

            function handleFileInputChange(event) {
                const input = event.target;
                const files = input.files;

                showFileError(input, null);

                if (!files || files.length === 0) return;

                for (let i = 0; i < files.length; i++) {
                    const result = validateFile(files[i]);
                    if (!result.valid) {
                        showFileError(input, result.error);
                        input.value = '';
                        break;
                    }
                }
            }

            function showFileError(input, errorMessage) {
                if (!input) return;

                const existingError = input.parentNode.querySelector('.file-error-block');
                if (existingError) {
                    existingError.remove();
                }

                if (!errorMessage) return;

                const errorBlock = document.createElement('div');
                errorBlock.className = 'file-error-block';
                errorBlock.style.color = 'red';
                errorBlock.style.marginTop = '5px';
                errorBlock.style.fontWeight = 'bold';
                errorBlock.textContent = errorMessage;

                input.parentNode.appendChild(errorBlock);
                input.value = '';
            }

            // Применяем валидацию к форме создания тикета
            const createFormAction = document.querySelector('form[action*="createTicket"]');
            if (createFormAction) {
                const fileInput = createFormAction.querySelector('input[type="file"]');

                if (fileInput) {
                    fileInput.addEventListener('change', handleFileInputChange);

                    createFormAction.addEventListener('submit', function (e) {
                        const files = fileInput.files;
                        if (files && files.length > 0) {
                            for (let i = 0; i < files.length; i++) {
                                const result = validateFile(files[i]);
                                if (!result.valid) {
                                    showFileError(fileInput, result.error);
                                    e.preventDefault();
                                    return false;
                                }
                            }
                        }
                    });
                }
            }

            // Открытие/закрытие CreateModal
            window.openCreateModal = () => createOverlay.classList.add('open');
            window.closeCreateModal = () => createOverlay.classList.remove('open');

            // Закрытие CreateModal
            createOverlay.addEventListener('click', e => {
                if (
                    e.target.closest('.ticket-create-modal-close') ||
                    e.target === createOverlay
                ) {
                    closeCreateModal();
                }
            });
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') closeCreateModal();
            });

            // Открытие DetailModal
            function ticketRowClickHandler() {
                const ticketId = this.dataset.id;
                currentTicketId = ticketId;
                fetch(window.location.pathname + '?action=detail&ticketId=' + ticketId, {
                    headers: {'X-Requested-With': 'XMLHttpRequest'}
                })
                    .then(r => r.text())
                    .then(html => {
                        if (currentTicketId !== ticketId) return;
                        detailContent.innerHTML = html;
                        detailOverlay.classList.add('open');

                        bindReplyForm();

                        const chat = detailContent.querySelector('.tickets-ticket-chat');
                        if (chat) {
                            chat.scrollTop = chat.scrollHeight;
                        }

                        updateTicketsUnreadCommentsAlert();
                        updateTicketUnreadCommentsAlert(currentTicketId);
                    })
                    .catch(console.error);
            }

            document.querySelectorAll('.tickets-table-row')
                .forEach(row => row.addEventListener('click', ticketRowClickHandler));

            // Закрытие DetailModal
            detailOverlay.addEventListener('click', e => {
                if (
                    e.target.closest('.ticket-detail-modal-close') ||
                    e.target === detailOverlay
                ) {
                    currentTicketId = null;
                    detailOverlay.classList.remove('open');
                }
            });
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') {
                    currentTicketId = null;
                    detailOverlay.classList.remove('open');
                }
            });

            function updateTicketUnreadCommentsAlert(ticketId) {
                fetch(window.location.pathname + '?action=getUnreadOperatorCommentsCount&ticketId=' + ticketId)
                    .then(response => response.json())
                    .then(data => {
                        const count = data.unread_operator_count;
                        const row = document.querySelector('.tickets-table-row[data-id="' + ticketId + '"]');
                        const alertCell = row ? row.querySelector('.tickets-table-cell-alert') : null;
                        if (alertCell) {
                            if (count > 0) {
                                if (!alertCell.querySelector('.tickets-cell-alert')) {
                                    alertCell.innerHTML = '<span class="tickets-cell-alert" title="Есть непрочитанные сообщения"></span>';
                                }
                            } else {
                                const alert = alertCell.querySelector('.tickets-cell-alert');
                                if (alert) alert.remove();
                            }
                        }
                    });
            }

            // Отправка комментария
            function bindReplyForm() {
                const form = document.getElementById('replyForm');
                if (!form) return;

                if (form.dataset.bound === 'true') return;

                form.dataset.bound = 'true';

                const fileInput = form.querySelector('input[type="file"]');
                if (fileInput) {
                    fileInput.addEventListener('change', handleFileInputChange);
                }

                form.addEventListener('submit', e => {
                    e.preventDefault();

                    // Проверяем файлы перед отправкой
                    if (fileInput && fileInput.files && fileInput.files.length > 0) {
                        for (let i = 0; i < fileInput.files.length; i++) {
                            const result = validateFile(fileInput.files[i]);
                            if (!result.valid) {
                                showFileError(fileInput, result.error);
                                return;
                            }
                        }
                    }

                    const chat = document.querySelector('.tickets-ticket-chat');
                    const submitBtn = form.querySelector('button[type="submit"]');
                    const originalHTML = submitBtn.innerHTML;

                    submitBtn.innerHTML = `<span class="tickets-spinner-creating"></span>Отправляется...`;
                    submitBtn.disabled = true;

                    const data = new FormData(form);
                    fetch(form.action, {
                        method: 'POST',
                        headers: {'X-Requested-With': 'XMLHttpRequest'},
                        body: data
                    })
                        .then(response => {
                            const responseClone = response.clone();

                            return response.json()
                                .then(data => ({json: data, isJson: true}))
                                .catch(() => {
                                    return responseClone.text()
                                        .then(text => ({text, isJson: false}));
                                });
                        })
                        .then(result => {
                            if (result.isJson && result.json.error) {
                                showFileError(fileInput, result.json.error);
                                return;
                            }

                            if (!result.isJson) {
                                chat.insertAdjacentHTML('beforeend', result.text);
                                chat.scrollTop = chat.scrollHeight;
                                form.reply_message.value = '';
                                form.querySelectorAll('input[type="file"]').forEach(input => input.value = '');
                            }
                        })
                        .catch(error => {
                            console.error(error);
                            alert('Произошла ошибка при отправке комментария');
                        })
                        .finally(() => {
                            submitBtn.innerHTML = originalHTML;
                            submitBtn.disabled = false;
                        });
                });
            }

            // Создание тикета
            const createForm = document.querySelector('#createModal form');
            const tbody = document.querySelector('#ticketsTable tbody');
            createForm.addEventListener('submit', e => {
                e.preventDefault();
                const submitBtn = createForm.querySelector('button[type="submit"]');
                const originalHTML = submitBtn.innerHTML;
                const fileInput = createForm.querySelector('input[type="file"]');

                submitBtn.innerHTML = `<span class="tickets-spinner-creating"></span>Создаётся...`;
                submitBtn.disabled = true;

                const data = new FormData(createForm);
                fetch(createForm.action, {
                    method: 'POST',
                    headers: {'X-Requested-With': 'XMLHttpRequest'},
                    body: data
                })
                    .then(response => {
                        const responseClone = response.clone();

                        return response.json()
                            .then(data => ({json: data, isJson: true}))
                            .catch(() => {
                                return responseClone.text()
                                    .then(text => ({text, isJson: false}));
                            });
                    })
                    .then(result => {
                        if (result.isJson && result.json.error) {
                            showFileError(fileInput, result.json.error);
                            return;
                        }

                        if (!result.isJson) {
                            tbody.insertAdjacentHTML('afterbegin', result.text);
                            const newRow = tbody.querySelector('tr');
                            newRow.addEventListener('click', ticketRowClickHandler);

                            createForm.reset();
                            closeCreateModal();

                            const noTicketsRow = document.querySelector('.tickets-no-tickets');
                            if (noTicketsRow) {
                                noTicketsRow.remove();
                            }
                        }
                    })
                    .catch(error => {
                        console.error(error);
                        alert('Произошла ошибка при создании тикета');
                    })
                    .finally(() => {
                        submitBtn.innerHTML = originalHTML;
                        submitBtn.disabled = false;
                    });
            });

            const contractSelect = document.getElementById('contracts');

            if (contractSelect) {
                $(contractSelect).select2({
                    width: '100%',
                    placeholder: 'Выберите договор(ы)',
                    allowClear: true,
                    closeOnSelect: false,
                    language: {
                        noResults: function () {
                            return 'Договоры не найдены';
                        }
                    }
                });
            }

            const checkFaqModal = document.getElementById('checkFaqModal');

            window.openCheckFaqModal = () => checkFaqModal.classList.add('open');
            window.closeCheckFaqModal = () => checkFaqModal.classList.remove('open');

            window.openTicketCreateModalFromCheckFaq = function () {
                closeCheckFaqModal();
                setTimeout(() => openCreateModal(), 200);
            };

            checkFaqModal.addEventListener('click', e => {
                if (
                    e.target.classList.contains('check-faq-modal') ||
                    e.target.closest('.modal-close')
                ) {
                    closeCheckFaqModal();
                }
            });

            document.addEventListener('keydown', e => {
                if (checkFaqModal.classList.contains('open') && e.key === 'Escape') closeCheckFaqModal();
            });

            const ticketPrecheckModal = document.getElementById('ticketPrecheckModal');

            function openTicketPrecheckModal() {
                ticketPrecheckModal.style.display = 'flex';
                setTimeout(() => ticketPrecheckModal.classList.add('open'), 10);
            }

            function closeTicketPrecheckModal() {
                ticketPrecheckModal.classList.remove('open');
                setTimeout(() => ticketPrecheckModal.style.display = 'none', 200);
            }

            window.openTicketPrecheckModal = openTicketPrecheckModal;
            window.closeTicketPrecheckModal = closeTicketPrecheckModal;

            window.openTicketCreateModalFromPrecheck = function () {
                closeTicketPrecheckModal();
                setTimeout(() => openCreateModal(), 200);
            };

            ticketPrecheckModal.addEventListener('click', e => {
                if (
                    e.target.classList.contains('ticket-precheck-modal') ||
                    e.target.closest('.modal-close')
                ) {
                    closeTicketPrecheckModal();
                }
            });

            document.addEventListener('keydown', e => {
                if (ticketPrecheckModal.classList.contains('open') && e.key === 'Escape') closeTicketPrecheckModal();
            });
        });
    </script>
{/literal}