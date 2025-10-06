'use strict';

(function() {
    $(document).ready(function() {

        /**
         * Requires common-dialog.js.
         *
         * @see Comment, ContactUs, Contribute, Generate, Guest, Resa, SearchHistory, Selection, TwoFactorAuth.
         */

        $(document).on('submit', '#search-history-save', function(ev) {
            ev.preventDefault();
            ev.stopImmediatePropagation();

            const query = window.location.search.substring(1);
            if (!query || !query.length) {
                let msg = $('button.search-history-save').data('msg-no-query');
                msg = msg && msg.length ? msg : 'The current search has no query.';
                CommonDialog.dialogAlert({ message: msg, nl2br: true });
                return;
            }

            const form = $(this);
            const urlForm = form.attr('action') ? form.attr('action') : window.location.href;
            const submitButton = form.closest('dialog').find('[type=submit]');
            form.find('input[name=query]').val(query);

            // TODO Use CommonDialog.jSend.
            $
                .ajax({
                    type: 'POST',
                    url: urlForm,
                    data: form.serialize(),
                    beforeSend: function() { CommonDialog.spinnerEnable(submitButton[0]); },
                })
                .done(function(data) {
                    // Success to store query.
                    // So close the form and display the message.
                    // form[0].reset();
                    $(form).closest('dialog')[0].close();
                    let msg = $('button.search-history-save').data('msg-success');
                    msg = msg && msg.length ? msg : CommonDialog.jSendMessage(data);
                    CommonDialog.dialogAlert({ message: msg ? msg : 'Search saved in your account.', nl2br: true });
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
                        let msg = CommonDialog.jSendMessage(data);
                        CommonDialog.dialogAlert({ message: msg ? msg : 'Check input', nl2br: true });
                        form[0].reset();
                    } else {
                        // Error is a server error (in particular cannot send mail).
                        let msg = data && data.status === 'error' && data.message && data.message.length ? data.message : 'An error occurred.';
                        CommonDialog.dialogAlert({ message: msg, nl2br: true });
                    }
                })
                .always(function () {
                    CommonDialog.spinnerDisable(submitButton[0]);
                });
        });

        $(document).on('click', 'button.search-history-save', function() {
            const dialog = document.querySelector('dialog.dialog-search-save');
            if (dialog) {
                dialog.showModal();
                $(dialog).trigger('o:dialog-opened');
            } else {
                CommonDialog.dialogAlert({
                    message: 'Cannot save search: the dialog is missing.',
                    nl2br: true
                });
            }
        });
        
        $(document).on('click', '.dialog-search-save .button-cancel', function(ev) {
            ev.preventDefault();
            const dialog = $(this).closest('dialog')[0];
            if (dialog) dialog.close();
        });

        $(document).on('click', 'button.search-history-delete', function(ev) {
            ev.preventDefault();
            ev.stopImmediatePropagation();
            const submitButton = $(this);
            const urlButton = submitButton.data('url');
            $.ajax({
                url: urlButton,
                beforeSend: function() { CommonDialog.spinnerEnable(submitButton[0]); },
            })
            .done(function(data) {
                // Success to delete query.
                let msg = submitButton.data('msg-success');
                msg = msg && msg.length ? msg : CommonDialog.jSendMessage(data);
                CommonDialog.dialogAlert({ message: msg ? msg : 'Search deleted from your account.', nl2br: true });
                // Update buttons.
                submitButton.addClass('hidden').hide();
                $('button.search-history-save').removeClass('hidden').show();
            })
            .fail(function(xhr) {
                const data = xhr.responseJSON;
                if (data && data.status === 'fail') {
                    // The search may have been deleted in another tab.
                    let msg = CommonDialog.jSendMessage(data);
                    CommonDialog.dialogAlert({ message: msg ? msg : 'Check input', nl2br: true });
                } else {
                    // Error is a server error (in particular cannot send mail).
                    let msg = data && data.status === 'error' && data.message && data.message.length ? data.message : 'An error occurred.';
                    CommonDialog.dialogAlert({ message: msg, nl2br: true });
                }
            })
            .always(function(data) {
                CommonDialog.spinnerDisable(submitButton[0]);
                if (data && data.status === 'success') {
                    submitButton.addClass('hidden').hide();
                } else {
                    submitButton.removeClass('hidden').show();
                }
            });
        });
   
    });
})();
