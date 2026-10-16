/**
 * Quick-create helper: AJAX form submit inside a modal, inject result into a select.
 */
(function (window, $) {
    'use strict';

    const QuickCreate = {
        /**
         * @param {Object} cfg
         * @param {string} cfg.formSelector
         * @param {string} cfg.modalSelector
         * @param {string|jQuery|Function} cfg.targetSelect - select to update, or fn returning it
         * @param {Function} [cfg.onSuccess] - (item, response) => void
         * @param {Function} [cfg.mapItem] - (response) => item {id, text, ...}
         * @param {string} [cfg.errorsSelector] - container for field errors inside modal
         */
        bind: function (cfg) {
            const $form = $(cfg.formSelector);
            if (!$form.length) return;

            $form.off('submit.quickCreate').on('submit.quickCreate', function (e) {
                e.preventDefault();

                const $modal = $(cfg.modalSelector);
                const $errors = $(cfg.errorsSelector || cfg.modalSelector + ' .quick-create-errors');
                $errors.empty().addClass('d-none');

                const $btn = $form.find('[type="submit"]');
                const original = $btn.html();
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Enregistrement...');

                const formData = new FormData($form[0]);
                const token = $('meta[name="csrf-token"]').attr('content')
                    || $form.find('input[name="_token"]').val();

                fetch($form.attr('action'), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': token || '',
                    },
                    body: formData,
                    credentials: 'same-origin',
                })
                    .then(async function (res) {
                        const data = await res.json().catch(function () { return {}; });
                        if (!res.ok) {
                            const err = new Error(data.message || 'Erreur de validation');
                            err.status = res.status;
                            err.data = data;
                            throw err;
                        }
                        return data;
                    })
                    .then(function (data) {
                        const item = cfg.mapItem
                            ? cfg.mapItem(data)
                            : (data.item || data.data || data);

                        if (!item || !item.id) {
                            throw new Error('Réponse invalide du serveur');
                        }

                        // Inject into target select(s)
                        let $targets;
                        if (typeof cfg.targetSelect === 'function') {
                            $targets = $(cfg.targetSelect(item, data));
                        } else {
                            $targets = $(cfg.targetSelect);
                        }

                        $targets.each(function () {
                            const $sel = $(this);
                            const text = item.text || item.label || item.assujeti || item.designation || ('#' + item.id);
                            if ($sel.find('option[value="' + item.id + '"]').length === 0) {
                                const opt = new Option(text, item.id, true, true);
                                $sel.append(opt);
                            } else {
                                $sel.val(String(item.id));
                            }
                            if ($sel.hasClass('select2-hidden-accessible')) {
                                $sel.trigger('change');
                            } else {
                                $sel.trigger('change');
                            }
                        });

                        if (typeof cfg.onSuccess === 'function') {
                            cfg.onSuccess(item, data);
                        }

                        $form[0].reset();
                        const modalEl = $modal[0];
                        if (modalEl && window.bootstrap) {
                            const instance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                            instance.hide();
                        }
                    })
                    .catch(function (err) {
                        const data = err.data || {};
                        let html = '<div class="alert alert-danger alert-validation alert-dismissible fade show" role="alert">';
                        html += '<strong><i class="fas fa-exclamation-circle me-1"></i> Erreur</strong>';
                        html += '<ul class="mb-0 mt-2">';

                        if (data.errors) {
                            Object.keys(data.errors).forEach(function (field) {
                                (data.errors[field] || []).forEach(function (msg) {
                                    html += '<li>' + msg + '</li>';
                                });
                            });
                        } else {
                            html += '<li>' + (err.message || 'Une erreur est survenue') + '</li>';
                        }
                        html += '</ul>';
                        html += '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                        $errors.html(html).removeClass('d-none');
                    })
                    .finally(function () {
                        $btn.prop('disabled', false).html(original);
                    });
            });
        },
    };

    window.QuickCreate = QuickCreate;
})(window, jQuery);
