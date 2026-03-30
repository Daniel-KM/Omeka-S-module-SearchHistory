'use strict';

(function() {
    $(document).ready(function() {

        /**
         * Requires common-dialog.js.
         *
         * @see Comment, ContactUs, Contribute, Generate, Guest, Resa, SearchHistory, Selection, TwoFactorAuth.
         */

        const saveBtn = $('button.search-history-save');
        const deleteBtn = $('button.search-history-delete');

        $(document).on('submit', '#search-history-save', function(ev) {
            ev.preventDefault();
            ev.stopImmediatePropagation();

            const heading = saveBtn.data('heading') || Omeka.jsTranslate('Save search');

            const query = window.location.search.substring(1);
            if (!query || !query.length) {
                const msg = saveBtn.data('msg-no-query') || Omeka.jsTranslate('The current search has no query.');
                CommonDialog.dialogAlert({
                    heading: heading,
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
                    $(form).closest('dialog')[0].close();
                    let msg = saveBtn.data('msg-success');
                    msg = msg || CommonDialog.jSendMessage(data);
                    CommonDialog.dialogAlert({
                        heading: heading,
                        message: msg || Omeka.jsTranslate('Search saved in your account.'),
                        nl2br: true,
                    });
                    // Update buttons.
                    deleteBtn
                        .data('url', data.data.url_delete)
                        .removeClass('hidden').show();
                    saveBtn
                        .addClass('hidden').hide();
                })
                .fail(function (xhr) {
                    const data = xhr.responseJSON;
                    if (data && data.status === 'fail') {
                        const msg = CommonDialog.jSendMessage(data);
                        CommonDialog.dialogAlert({
                            heading: heading,
                            message: msg || Omeka.jsTranslate('Check input'),
                            nl2br: true,
                        });
                        form[0].reset();
                    } else {
                        const msg = data && data.status === 'error' && data.message && data.message.length
                            ? data.message
                            : Omeka.jsTranslate('An error occurred.');
                        CommonDialog.dialogAlert({
                            heading: heading,
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
                const heading = saveBtn.data('heading') || Omeka.jsTranslate('Save search');
                const msg = saveBtn.data('msg-missing') || Omeka.jsTranslate('Cannot save search: the dialog is missing.');
                CommonDialog.dialogAlert({
                    heading: heading,
                    message: msg,
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
            const heading = submitButton.data('heading') || Omeka.jsTranslate('Save search');
            $.ajax({
                type: 'POST',
                url: urlButton,
                beforeSend: function() { CommonDialog.spinnerEnable(submitButton[0]); },
            })
            .done(function(data) {
                let msg = submitButton.data('msg-success');
                msg = msg || CommonDialog.jSendMessage(data);
                CommonDialog.dialogAlert({
                    heading: heading,
                    message: msg || Omeka.jsTranslate('Search deleted from your account.'),
                    nl2br: true,
                });
                submitButton.addClass('hidden').hide();
                saveBtn.removeClass('hidden').show();
            })
            .fail(function(xhr) {
                const data = xhr.responseJSON;
                if (data && data.status === 'fail') {
                    const msg = CommonDialog.jSendMessage(data);
                    CommonDialog.dialogAlert({
                        heading: heading,
                        message: msg || Omeka.jsTranslate('Check input'),
                        nl2br: true,
                    });
                } else {
                    const msg = data && data.status === 'error' && data.message && data.message.length
                        ? data.message
                        : Omeka.jsTranslate('An error occurred.');
                    CommonDialog.dialogAlert({
                        heading: heading,
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
            const heading = btn.data('heading') || Omeka.jsTranslate('Delete search');
            CommonDialog.dialogConfirm({
                heading: heading,
                message: btn.data('msg-confirm') || Omeka.jsTranslate('Are you sure you want to delete this saved search?'),
                nl2br: true,
                textOk: btn.data('text-ok') || Omeka.jsTranslate('Delete'),
                textCancel: btn.data('text-cancel') || Omeka.jsTranslate('Cancel'),
            }).then(function(result) {
                if (!result) return;
                btn.prop('disabled', true);
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
                    btn.prop('disabled', false);
                    CommonDialog.spinnerDisable(btn[0]);
                    const data = xhr.responseJSON;
                    const msg = data && data.data
                        ? CommonDialog.jSendMessage(data)
                        : Omeka.jsTranslate('An error occurred.');
                    CommonDialog.dialogAlert({
                        heading: heading,
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
            const heading = clearButton.data('heading') || Omeka.jsTranslate('Clear search history');
            const periods = clearButton.data('periods') || [
                { value: '1h', label: Omeka.jsTranslate('Last hour') },
                { value: '24h', label: Omeka.jsTranslate('Last 24 hours') },
                { value: '7d', label: Omeka.jsTranslate('Last 7 days') },
                { value: '30d', label: Omeka.jsTranslate('Last 30 days') },
                { value: 'all', label: Omeka.jsTranslate('All history') },
            ];
            const escHtml = function(s) {
                const d = document.createElement('div');
                d.appendChild(document.createTextNode(s));
                return d.innerHTML;
            };
            let radioHtml = '<div class="search-history-clear-options">';
            periods.forEach(function(p, i) {
                const checked = i === 0 ? ' checked' : '';
                radioHtml += '<label class="search-history-clear-option">'
                    + '<input type="radio" name="clear_period" value="' + escHtml(p.value) + '"' + checked + ' /> '
                    + escHtml(p.label)
                    + '</label>';
            });
            radioHtml += '</div>';
            const promise = CommonDialog.dialogGeneric({
                heading: heading,
                message: clearButton.data('msg-confirm') || Omeka.jsTranslate('This action is irreversible. Select the period to clear:'),
                nl2br: true,
                body: radioHtml,
                textOk: clearButton.data('text-ok') || Omeka.jsTranslate('Confirm'),
                textCancel: clearButton.data('text-cancel') || Omeka.jsTranslate('Cancel'),
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
                        heading: heading,
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
                        heading: heading,
                        message: msg,
                        nl2br: true,
                    });
                });
            });
        });

    });
})();
