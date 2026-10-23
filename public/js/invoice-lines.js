/**
 * Shared invoice line-item engine.
 * - Auto-fill unit price + default qty on product select
 * - Immediate amount recalculation
 * - Merge duplicate product selections into existing line
 * - Optional stock enforcement (disable / grey out zero stock)
 */
(function (window, $) {
    'use strict';

    const InvoiceLines = {
        options: {
            container: '#elementsContainer',
            rowSelector: '.element-row',
            productSelect: '.base-taxable-select',
            qtyInput: '.qte-input',
            priceInput: '.prix-input',
            montantCell: '.montant-cell',
            grandTotal: '#grandTotal',
            addRowBtn: '#addRowBtn',
            removeRowBtn: '.removeRowBtn',
            products: {},
            enforceStock: true,
            defaultQty: 1,
            mergeDuplicates: true,
            modalParent: null,
            productName: 'produits_id[]',
            qtyName: 'quantite[]',
            priceName: 'prix_unitaire[]',
            includeElementId: false,
        },

        init: function (opts) {
            this.options = $.extend({}, this.options, opts || {});
            this._normalizeProducts();
            this._bind();
            this._initExistingSelects();
            this.recalcAll();
            this.renumberRows();
            return this;
        },

        _normalizeProducts: function () {
            const src = this.options.products || {};
            const map = {};
            Object.keys(src).forEach(function (k) {
                const p = src[k] || {};
                map[String(k)] = {
                    code: p.code || '',
                    designation: p.designation || '',
                    label_fiscal: p.label_fiscal || '',
                    prix_unitaire: parseFloat(p.prix_unitaire) || 0,
                    qte: parseFloat(p.qte) || 0,
                    is_service: !!p.is_service,
                    taux_tva: parseFloat(p.taux_tva) || 0,
                    groupe_tva: p.groupe_tva || 'A',
                    type_ts: p.type_ts || 'AUCUNE',
                    montant_ts: parseFloat(p.montant_ts) || 0,
                    pourcentage_ts: parseFloat(p.pourcentage_ts) || 0,
                    type_article: p.type_article || '',
                };
            });
            this.options.products = map;
        },

        formatMontant: function (n) {
            return new Intl.NumberFormat('fr-FR', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            }).format(Math.round(n || 0)) + ' FCFA';
        },

        productLabel: function (id, data) {
            data = data || this.options.products[String(id)] || {};
            if (data.label_fiscal) {
                return data.label_fiscal;
            }
            const code = data.code ? '[' + data.code + '] ' : '';
            const desig = data.designation || ('#' + id);
            const stock = !data.is_service ? ' (Stock: ' + (parseFloat(data.qte) || 0) + ')' : '';
            const grp = data.groupe_tva ? 'Groupe ' + data.groupe_tva + ' (' + (data.taux_tva || 0) + '%)' : 'Exonéré (0%)';
            const type = data.type_article || (data.is_service ? 'LOCSER' : 'LOCBIE');
            return code + desig + stock + ' — [' + grp + '] — [' + type + ']';
        },

        isOutOfStock: function (id) {
            if (!this.options.enforceStock) return false;
            const data = this.options.products[String(id)];
            if (!data) return false;
            if (data.is_service) return false; // Prestation de service : STRICTEMENT JAMAIS hors stock
            return (parseFloat(data.qte) || 0) <= 0;
        },

        buildOptionsHtml: function (selectedId) {
            const self = this;
            let html = '<option value="">-- Sélectionner un article ou prestation --</option>';
            Object.keys(this.options.products).forEach(function (id) {
                const data = self.options.products[id];
                const out = self.isOutOfStock(id);
                const sel = String(selectedId || '') === String(id) ? ' selected' : '';
                const dis = out && String(selectedId || '') !== String(id) ? ' disabled' : '';
                html += '<option value="' + id + '"' + sel + dis + ' data-stock="' + (data.qte || 0) + '">'
                    + self.escapeHtml(self.productLabel(id, data))
                    + '</option>';
            });
            return html;
        },

        escapeHtml: function (str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        },

        refreshFeather: function () {
            if (typeof feather !== 'undefined') feather.replace();
        },

        $rows: function () {
            return $(this.options.container).find(this.options.rowSelector);
        },

        renumberRows: function () {
            this.$rows().each(function (i) {
                $(this).find('.row-index').text(i + 1);
            });
        },

        recalcRow: function ($row) {
            const qte = parseFloat($row.find(this.options.qtyInput).val()) || 0;
            const prix = parseFloat($row.find(this.options.priceInput).val()) || 0;
            const montant = qte * prix;
            $row.find(this.options.montantCell).text(this.formatMontant(montant));
            return montant;
        },

        recalcAll: function () {
            const self = this;
            let total = 0;
            this.$rows().each(function () {
                total += self.recalcRow($(this));
            });
            $(this.options.grandTotal).text(this.formatMontant(total));
            if (typeof window.calculerSyntheseLive === 'function') {
                window.calculerSyntheseLive();
            }
            return total;
        },

        select2Options: function ($select) {
            const self = this;
            const opts = {
                placeholder: 'Sélectionner...',
                allowClear: true,
                width: '100%',
                templateResult: function (state) {
                    if (!state.id) return state.text;
                    const data = self.options.products[String(state.id)];
                    const $el = $('<span></span>').text(state.text);
                    if (data && self.isOutOfStock(state.id)) {
                        $el.addClass('option-out-of-stock text-muted');
                        $el.css({ opacity: 0.45, textDecoration: 'line-through' });
                    }
                    return $el;
                },
                templateSelection: function (state) {
                    return state.text || state.id;
                },
            };
            if (this.options.modalParent) {
                opts.dropdownParent = $(this.options.modalParent);
            }
            return opts;
        },

        initSelect2: function ($select) {
            if (!$select || !$select.length) return;
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
            $select.select2(this.select2Options($select));
        },

        _initExistingSelects: function () {
            const self = this;
            this.$rows().find(this.options.productSelect).each(function () {
                const $sel = $(this);
                // Ensure stock-aware labels / disabled flags on existing options
                const current = $sel.val();
                $sel.html(self.buildOptionsHtml(current));
                if (current) $sel.val(current);
                self.initSelect2($sel);
            });
        },

        findRowByProductId: function (productId, exceptRow) {
            const self = this;
            let found = null;
            this.$rows().each(function () {
                if (exceptRow && this === exceptRow[0]) return;
                const val = $(this).find(self.options.productSelect).val();
                if (String(val) === String(productId)) {
                    found = $(this);
                    return false;
                }
            });
            return found;
        },

        onProductChange: function (selectEl) {
            const $select = $(selectEl);
            const id = $select.val();
            const $row = $select.closest(this.options.rowSelector);

            if (!id) {
                this.recalcAll();
                return;
            }

            // Merge duplicates
            if (this.options.mergeDuplicates) {
                const $existing = this.findRowByProductId(id, $row);
                if ($existing && $existing.length) {
                    const data = this.options.products[String(id)] || {};
                    const stock = parseFloat(data.qte) || 0;
                    let qty = parseFloat($existing.find(this.options.qtyInput).val()) || 0;
                    qty += this.options.defaultQty || 1;

                    if (this.options.enforceStock && !data.is_service && stock > 0 && qty > stock) {
                        qty = stock;
                        this.showStickyAlert(
                            'Stock insuffisant pour augmenter « ' + (data.designation || id) + ' ». Disponible : ' + stock,
                            'warning'
                        );
                    }

                    $existing.find(this.options.qtyInput).val(qty);
                    if (!$existing.find(this.options.priceInput).val()) {
                        $existing.find(this.options.priceInput).val(data.prix_unitaire || 0);
                    }

                    // Reset current row selection
                    $select.val(null).trigger('change.select2');
                    $row.find(this.options.qtyInput).val('');
                    $row.find(this.options.priceInput).val('');
                    this.recalcAll();
                    $existing.find(this.options.qtyInput).focus();
                    return;
                }
            }

            const data = this.options.products[String(id)];
            if (!data) {
                this.recalcAll();
                return;
            }

            if (this.options.enforceStock && !data.is_service && (parseFloat(data.qte) || 0) <= 0) {
                this.showStickyAlert('Produit hors stock : ' + (data.designation || id), 'danger');
                $select.val(null).trigger('change.select2');
                this.recalcAll();
                return;
            }

            // Store both HT and TTC base unit prices on the row to prevent toggle drift
            const puHt = parseFloat(data.prix_unitaire) || 0;
            const tauxTva = parseFloat(data.taux_tva) || 0;
            let tsUnit = 0;
            if (data.type_ts === 'MONTANT') tsUnit = parseFloat(data.montant_ts) || 0;
            else if (data.type_ts === 'POURCENTAGE') tsUnit = puHt * ((parseFloat(data.pourcentage_ts) || 0) / 100);
            const puTtc = data.prix_unitaire_ttc !== undefined ? parseFloat(data.prix_unitaire_ttc) : Math.round(((puHt + tsUnit) * (1 + tauxTva / 100)) * 100) / 100;

            const $priceInp = $row.find(this.options.priceInput);
            $row.data('pu-ht', puHt).attr('data-pu-ht', puHt);
            $row.data('pu-ttc', puTtc).attr('data-pu-ttc', puTtc);
            $priceInp.data('pu-ht', puHt).attr('data-pu-ht', puHt);
            $priceInp.data('pu-ttc', puTtc).attr('data-pu-ttc', puTtc);

            if (window.currentModePrix === 'TTC') {
                $priceInp.val(puTtc);
            } else {
                $priceInp.val(puHt);
            }

            // Default qty if empty / zero
            const currentQty = parseFloat($row.find(this.options.qtyInput).val());
            if (!currentQty || currentQty <= 0) {
                $row.find(this.options.qtyInput).val(this.options.defaultQty || 1);
            }

            this.recalcAll();
        },

        addRow: function () {
            const self = this;
            const elementIdHidden = this.options.includeElementId
                ? '<input type="hidden" name="element_id[]" value="">'
                : '';

            const newRow = $(
                '<tr class="element-row">' +
                    '<td class="text-center"><span class="row-index">#</span>' + elementIdHidden + '</td>' +
                    '<td>' +
                        '<select class="form-select base-taxable-select select2-input" name="' + this.options.productName + '" style="width:100%">' +
                            this.buildOptionsHtml() +
                        '</select>' +
                    '</td>' +
                    '<td><input class="form-control text-end prix-input font-monospace" name="' + this.options.priceName + '" type="number" step="0.01" min="0" placeholder="0.00" /></td>' +
                    '<td><input class="form-control text-center qte-input font-monospace" name="' + this.options.qtyName + '" type="number" step="0.01" min="0.01" placeholder="1" /></td>' +
                    '<td class="text-end montant-cell">0</td>' +
                    '<td class="text-center">' +
                        '<button type="button" class="btn-remove-row removeRowBtn">' +
                            '<i data-feather="trash-2" style="width:15px;height:15px;"></i>' +
                        '</button>' +
                    '</td>' +
                '</tr>'
            );

            $(this.options.container).append(newRow);
            this.initSelect2(newRow.find(this.options.productSelect));
            this.renumberRows();
            this.refreshFeather();
            this.recalcAll();
        },

        removeRow: function ($btn) {
            if (this.$rows().length > 1) {
                $btn.closest(this.options.rowSelector).remove();
                this.renumberRows();
                this.recalcAll();
            } else {
                this.showStickyAlert('Vous devez conserver au moins un élément.', 'warning');
            }
        },

        /**
         * Register a newly created product into the live catalog + all open selects.
         */
        registerProduct: function (item) {
            if (!item || !item.id) return;
            const id = String(item.id);
            this.options.products[id] = {
                code: item.code || '',
                designation: item.designation || item.text || '',
                prix_unitaire: parseFloat(item.prix_unitaire) || 0,
                qte: parseFloat(item.qte) || 0,
            };

            const self = this;
            this.$rows().find(this.options.productSelect).each(function () {
                const $sel = $(this);
                const current = $sel.val();
                $sel.html(self.buildOptionsHtml(current));
                if (current) $sel.val(current);
                self.initSelect2($sel);
            });
        },

        replaceRows: function (lines) {
            const self = this;
            lines = Array.isArray(lines) && lines.length ? lines : [{}];

            this.$rows().slice(1).remove();
            let $first = this.$rows().first();

            lines.forEach(function (line, index) {
                if (index > 0) self.addRow();
                const $row = index === 0 ? $first : self.$rows().last();
                const productId = line.produit_id || line.produits_id || '';
                const $select = $row.find(self.options.productSelect);
                $select.val(productId ? String(productId) : null).trigger('change.select2');
                const $qty = $row.find(self.options.qtyInput).val(line.quantite || '');
                if (line.quantite) $qty.attr('max', line.quantite);
                $row.find(self.options.priceInput).val(line.prix_unitaire || '');
            });

            this.renumberRows();
            this.recalcAll();
            this.refreshFeather();
        },

        showStickyAlert: function (message, type) {
            type = type || 'danger';
            const target = this.options.alertTarget || '#invoiceFormAlerts';
            let $box = $(target);
            if (!$box.length) {
                $box = $('<div id="invoiceFormAlerts" class="mb-3"></div>');
                const $form = $(this.options.container).closest('form');
                if ($form.length) $form.prepend($box);
                else $(this.options.container).before($box);
            }
            const alertClass = type === 'warning' ? 'alert-warning' : (type === 'success' ? 'alert-success' : 'alert-danger');
            const $alert = $(
                '<div class="alert ' + alertClass + ' alert-validation alert-dismissible fade show" role="alert">' +
                    '<span></span>' +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>' +
                '</div>'
            );
            $alert.find('span').text(message);
            $box.append($alert);
        },

        _bind: function () {
            const self = this;

            $(document).off('change.invoiceLines', this.options.productSelect)
                .on('change.invoiceLines', this.options.productSelect, function () {
                    self.onProductChange(this);
                });

            $(document).off('input.invoiceLines', this.options.qtyInput + ', ' + this.options.priceInput)
                .on('input.invoiceLines', this.options.qtyInput + ', ' + this.options.priceInput, function () {
                    self.recalcAll();
                });

            $(document).off('click.invoiceLinesAdd', this.options.addRowBtn)
                .on('click.invoiceLinesAdd', this.options.addRowBtn, function (e) {
                    e.preventDefault();
                    self.addRow();
                });

            $(document).off('click.invoiceLinesRemove', this.options.removeRowBtn)
                .on('click.invoiceLinesRemove', this.options.removeRowBtn, function (e) {
                    e.preventDefault();
                    self.removeRow($(this));
                });
        },
    };

    window.InvoiceLines = InvoiceLines;
})(window, jQuery);
