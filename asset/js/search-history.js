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
                msg = msg && msg.length ? msg : Omeka.jsTranslate('The current search has no query.');
                CommonDialog.dialogAlert({
                    heading: Omeka.jsTranslate('Save search'),
                    message: msg,
                    nl2br: true,
                });
                return;
            }

            const form = $(this);
            const urlForm = form.attr('action') ? form.attr('action') : window.location.href;
            const submitButton = form.closest('dialog').find('[type=submit]');

            // Set query in hidden input, or append it to serialized data as a
            // fallback when the input is missing from the dom.
            form.find('input[name=query]').val(query);
            let data = form.serialize();
            if (data.indexOf('query=') === -1) {
                data += (data ? '&' : '') + 'query=' + encodeURIComponent(query);
            }

            // TODO Use CommonDialog.jSend.
            $
                .ajax({
                    type: 'POST',
                    url: urlForm,
                    data: data,
                    beforeSend: function() { CommonDialog.spinnerEnable(submitButton[0]); },
                })
                .done(function(data) {
                    // Success to store query.
                    // So close the form and display the message.
                    // form[0].reset();
                    $(form).closest('dialog')[0].close();
                    let msg = $('button.search-history-save').data('msg-success');
                    msg = msg && msg.length ? msg : CommonDialog.jSendMessage(data);
                    CommonDialog.dialogAlert({
                        heading: Omeka.jsTranslate('Save search'),
                        message: msg ? msg : Omeka.jsTranslate('Search saved in your account.'),
                        nl2br: true,
                    });
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
                        CommonDialog.dialogAlert({
                            heading: Omeka.jsTranslate('Save search'),
                            message: msg ? msg : Omeka.jsTranslate('Check input'),
                            nl2br: true,
                        });
                        form[0].reset();
                    } else {
                        // Error is a server error (in particular cannot send mail).
                        let msg = data && data.status === 'error' && data.message && data.message.length
                            ? data.message
                            : Omeka.jsTranslate('An error occurred.');
                        CommonDialog.dialogAlert({
                            heading: Omeka.jsTranslate('Save search'),
                            message: msg,
                            nl2br: true,
                        });
                    }
                })
                .always(function () {
                    CommonDialog.spinnerDisable(submitButton[0]);
                });
        });

        $(document).on('click', 'button.search-history-save', function() {
            const dialog = document.querySelector('dialog.dialog-search-save');
            if (dialog) {
                const input = dialog.querySelector('input[name="comment"]');
                if (input && !input.value) {
                    const params = new URLSearchParams(window.location.search);
                    const parts = [];
                    ['q', 'search', 'fulltext_search'].forEach(function(key) {
                        const val = params.get(key);
                        if (val) parts.push(val.trim());
                    });
                    const refines = params.getAll('refine[]');
                    if (refines.length) {
                        refines.forEach(function(val) {
                            if (val) parts.push(val.trim());
                        });
                    } else {
                        const refine = params.get('refine');
                        if (refine) parts.push(refine.trim());
                    }
                    if (parts.length) {
                        input.value = parts.filter(Boolean).join(' — ');
                    }
                }
                dialog.showModal();
                $(dialog).trigger('o:dialog-opened');
            } else {
                CommonDialog.dialogAlert({
                    heading: Omeka.jsTranslate('Save search'),
                    message: Omeka.jsTranslate('Cannot save search: the dialog is missing.'),
                    nl2br: true,
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
                CommonDialog.dialogAlert({
                    heading: Omeka.jsTranslate('Save search'),
                    message: msg ? msg : Omeka.jsTranslate('Search deleted from your account.'),
                    nl2br: true,
                });
                // Update buttons.
                submitButton.addClass('hidden').hide();
                $('button.search-history-save').removeClass('hidden').show();
            })
            .fail(function(xhr) {
                const data = xhr.responseJSON;
                if (data && data.status === 'fail') {
                    // The search may have been deleted in another tab.
                    let msg = CommonDialog.jSendMessage(data);
                    CommonDialog.dialogAlert({
                        heading: Omeka.jsTranslate('Save search'),
                        message: msg ? msg : Omeka.jsTranslate('Check input'),
                        nl2br: true,
                    });
                } else {
                    // Error is a server error (in particular cannot send mail).
                    let msg = data && data.status === 'error' && data.message && data.message.length
                        ? data.message
                        : Omeka.jsTranslate('An error occurred.');
                    CommonDialog.dialogAlert({
                        heading: Omeka.jsTranslate('Save search'),
                        message: msg,
                        nl2br: true,
                    });
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

        $(document).on('click', '.search-history-delete-one', function(ev) {
            ev.preventDefault();
            ev.stopImmediatePropagation();
            const btn = $(this);
            const urlDelete = btn.data('url');
            CommonDialog.dialogConfirm({
                heading: Omeka.jsTranslate('Delete search'),
                message: Omeka.jsTranslate('Are you sure you want to delete this saved search?'),
                nl2br: true,
                textOk: Omeka.jsTranslate('Delete'),
                textCancel: Omeka.jsTranslate('Cancel'),
            }).then(function(result) {
                if (!result) return;
                $.ajax({
                    type: 'POST',
                    url: urlDelete,
                    beforeSend: function() {
                        CommonDialog.spinnerEnable(btn[0]);
                    },
                })
                .done(function() {
                    btn.closest('li.search-request').fadeOut(300, function() {
                        $(this).remove();
                    });
                })
                .fail(function(xhr) {
                    CommonDialog.spinnerDisable(btn[0]);
                    const data = xhr.responseJSON;
                    const msg = data && data.data
                        ? CommonDialog.jSendMessage(data)
                        : Omeka.jsTranslate('An error occurred.');
                    CommonDialog.dialogAlert({
                        heading: Omeka.jsTranslate('Delete search'),
                        message: msg,
                        nl2br: true,
                    });
                });
            });
        });

        $(document).on('click', '.search-history-clear', function(ev) {
            ev.preventDefault();
            ev.stopImmediatePropagation();
            const clearButton = $(this);
            const urlClear = clearButton.data('url');
            const periods = [
                { value: '1h', label: Omeka.jsTranslate('Last hour') },
                { value: '24h', label: Omeka.jsTranslate('Last 24 hours') },
                { value: '7d', label: Omeka.jsTranslate('Last 7 days') },
                { value: '30d', label: Omeka.jsTranslate('Last 30 days') },
                { value: 'all', label: Omeka.jsTranslate('All history') },
            ];
            let radioHtml = '<div class="search-history-clear-options">';
            periods.forEach(function(p, i) {
                const checked = i === 0 ? ' checked' : '';
                radioHtml += '<label class="search-history-clear-option">'
                    + '<input type="radio" name="clear_period" value="' + p.value + '"' + checked + ' /> '
                    + p.label
                    + '</label>';
            });
            radioHtml += '</div>';
            const promise = CommonDialog.dialogGeneric({
                heading: Omeka.jsTranslate('Clear search history'),
                message: Omeka.jsTranslate('This action is irreversible. Select the period to clear:'),
                nl2br: true,
                body: radioHtml,
                textOk: Omeka.jsTranslate('Confirm'),
                textCancel: Omeka.jsTranslate('Cancel'),
            });
            // Capture dialog ref before it gets removed on resolve.
            const dialogEl = document.querySelector('dialog.dialog-generic');
            promise.then(function(result) {
                if (!result) return;
                const checked = dialogEl
                    ? dialogEl.querySelector('input[name="clear_period"]:checked')
                    : null;
                const period = checked ? checked.value : 'all';
                $.ajax({
                    type: 'POST',
                    url: urlClear,
                    data: { period: period },
                    beforeSend: function() {
                        CommonDialog.spinnerEnable(clearButton[0]);
                    },
                })
                .done(function() {
                    CommonDialog.spinnerDisable(clearButton[0]);
                    CommonDialog.dialogAlert({
                        heading: Omeka.jsTranslate('Clear search history'),
                        message: clearButton.data('msg-success'),
                        nl2br: true,
                    }).then(function() {
                        window.location.reload();
                    });
                })
                .fail(function(xhr) {
                    CommonDialog.spinnerDisable(clearButton[0]);
                    const data = xhr.responseJSON;
                    const msg = data && data.data
                        ? CommonDialog.jSendMessage(data)
                        : Omeka.jsTranslate('An error occurred.');
                    CommonDialog.dialogAlert({
                        heading: Omeka.jsTranslate('Clear search history'),
                        message: msg,
                        nl2br: true,
                    });
                });
            });
        });

    });
})();
