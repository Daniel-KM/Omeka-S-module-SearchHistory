'use strict';

(function() {
    $(document).ready(function() {

            /**
             * @see ContactUs, Guest, SearchHistory, Selection, TwoFactorAuth.
             */

            const beforeSpin = function (element) {
                var span = $(element).find('span');
                if (!span.length) {
                    span = $(element).next('span.appended');
                    if (!span.length) {
                        $('<span class="appended"></span>').insertAfter($(element));
                        span = $(element).next('span');
                    }
                }
                // TODO Disable the button instead of hide and resize it to let place to spinner?
                element.hide();
                span.addClass('fas fa-sync fa-spin');
            };

            const afterSpin = function (element) {
                var span = $(element).find('span');
                if (!span.length) {
                    span = $(element).next('span.appended');
                    if (span.length) {
                        span.remove();
                    }
                } else {
                    span.removeClass('fas fa-sync fa-spin');
                }
                element.show();
            };

            /**
             * Get the main message of jSend output, in particular for status fail.
             */
            const jSendMessage = function(data) {
                if (typeof data !== 'object') {
                    return null;
                }
                if (data.message) {
                    return data.message;
                }
                if (!data.data) {
                    return null;
                }
                for (let value of Object.values(data.data)) {
                    if (typeof value === 'string' && value.length) {
                        return value;
                    }
                }
                return null;
            }

            const dialogMessage = function (message, nl2br = false) {
                // Use a dialog to display a message, that should be escaped.
                var dialog = document.querySelector('dialog.popup-message');
                if (!dialog) {
                    dialog = `
        <dialog class="popup popup-dialog dialog-message popup-message" data-is-dynamic="1">
            <div class="dialog-background">
                <div class="dialog-panel">
                    <div class="dialog-header">
                        <button type="button" class="dialog-header-close-button" title="Close" autofocus="autofocus">
                            <span class="dialog-close">🗙</span>
                        </button>
                    </div>
                    <div class="dialog-contents">
                        {{ message }}
                    </div>
                </div>
            </div>
        </dialog>`;
                    $('body').append(dialog);
                    dialog = document.querySelector('dialog.dialog-message');
                }
                if (nl2br) {
                    message = message.replace(/(?:\r\n|\r|\n)/g, '<br/>');
                }
                dialog.innerHTML = dialog.innerHTML.replace('{{ message }}', message);
                dialog.showModal();
                $(dialog).trigger('o:dialog-opened');
            };

            $(document).on('submit', '#search-history-save', function(ev) {
                ev.preventDefault();
                ev.stopImmediatePropagation();

                const query = window.location.search.substring(1);
                if (!query || !query.length) {
                    let msg = $('button.search-history-save').data('msg-no-query');
                    msg = msg && msg.length ? msg : 'The current search has no query.';
                    dialogMessage(msg, true);
                    return;
                }

                const form = $(this);
                const urlForm = form.attr('action') ? form.attr('action') : window.location.href;
                const submitButton = form.find('[type=submit]');
                form.find('input[name=query]').val(query);

                $
                    .ajax({
                        type: 'POST',
                        url: urlForm,
                        data: form.serialize(),
                        beforeSend: beforeSpin(submitButton),
                    })
                    .done(function(data) {
                        // Success to store query.
                        // So close the form and display the message.
                        // form[0].reset();
                        $(form).closest('dialog')[0].close();
                        let msg = $('button.search-history-save').data('msg-success');
                        msg = msg && msg.length ? msg : jSendMessage(data);
                        dialogMessage(msg ? msg : 'Search saved in your account.', true);
                        // Update buttons.
                        $('button.search-history-delete')
                            .data('url', data.data.url_delete)
                            .removeClass('hidden').show();
                        $('button.search-history-save')
                            .addClass('hidden').hide();
                    })
                    .fail(function (xhr, textStatus, errorThrown) {
                        const data = xhr.responseJSON;
                        if (data && data.status === 'fail') {
                            let msg = jSendMessage(data);
                            dialogMessage(msg ? msg : 'Check input', true);
                            form[0].reset();
                        } else {
                            // Error is a server error (in particular cannot send mail).
                            let msg = data && data.status === 'error' && data.message && data.message.length ? data.message : 'An error occurred.';
                            dialogMessage(msg, true);
                        }
                    })
                    .always(function () {
                        afterSpin(submitButton)
                    });
            });

            $(document).on('click', 'button.search-history-save', function() {
                const dialog = document.querySelector('dialog.dialog-search-save');
                if (dialog) {
                    dialog.showModal();
                    $(dialog).trigger('o:dialog-opened');
                } else {
                    dialogMessage('Cannot save search: the dialog is missing.', true);
                }
            });

            $(document).on('click', 'button.search-history-delete', function(ev) {
                ev.preventDefault();
                ev.stopImmediatePropagation();
                const submitButton = $(this);
                const urlButton =  $(this).data('url');
                $
                    .ajax({
                        url: urlButton,
                        beforeSend: beforeSpin(submitButton),
                    })
                    .done(function(data) {
                        // Success to delete query.
                        let msg = submitButton.data('msg-success');
                        msg = msg && msg.length ? msg : jSendMessage(data);
                        dialogMessage(msg ? msg : 'Search deleted from your account.', true);
                        // Update buttons.
                        submitButton.addClass('hidden').hide();
                        $('button.search-history-save').removeClass('hidden').show();
                    })
                    .fail(function (xhr, textStatus, errorThrown) {
                        const data = xhr.responseJSON;
                        if (data && data.status === 'fail') {
                            // The search may have been deleted in another tab.
                            let msg = jSendMessage(data);
                            dialogMessage(msg ? msg : 'Check input', true);
                        } else {
                            // Error is a server error (in particular cannot send mail).
                            let msg = data && data.status === 'error' && data.message && data.message.length ? data.message : 'An error occurred.';
                            dialogMessage(msg, true);
                        }
                    })
                    .always(function (data) {
                        afterSpin(submitButton);
                        if (data && data.status === 'success') {
                            submitButton.addClass('hidden').hide();
                        } else {
                            submitButton.removeClass('hidden').show();
                        }
                    });
            });

            $(document).on('click', '.dialog-header-close-button', function() {
                const dialog = this.closest('dialog.popup');
                if (dialog) {
                    dialog.close();
                    if (dialog.hasAttribute('data-is-dynamic') && dialog.getAttribute('data-is-dynamic')) {
                        dialog.remove();
                    }
                } else {
                    $(this).closest('.popup').addClass('hidden').hide();
                }
            });

    });
})();
