/**
 * Facturation Comptoir — compact SPA POS (jQuery)
 * No full-page reloads on sell / session / quick-create paths.
 */
(function ($, window) {
  'use strict';

  var cfg = window.COMPTOIR_POS || {};
  var state = {
    session: null,
    meta: null,
    ticketId: null,
    ticketCode: null,
    lines: [],
    selectedIdx: -1,
    remise: 0,
    contribuableId: null,
    clientLibre: '',
    userId: cfg.userId || null,
    modepaies: [],
    busy: false,
    held: [],
    lastProducts: []
  };

  var modals = {
    pay: null,
    open: null,
    arret: null,
    fermeture: null
  };

  function money(n) {
    n = Number(n) || 0;
    return n.toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
  }

  function round2(n) {
    return Math.round((Number(n) + Number.EPSILON) * 100) / 100;
  }

  function computeTotals(lines, remise) {
    var ht = 0, tva = 0;
    lines.forEach(function (l) {
      var mht = round2((Number(l.quantite) || 0) * (Number(l.prix_unitaire) || 0));
      var mtva = round2(mht * (Number(l.tva_taux) || 0) / 100);
      l.montant_ht = mht;
      l.montant_tva = mtva;
      l.montant_ttc_ligne = round2(mht + mtva);
      ht += mht;
      tva += mtva;
    });
    ht = round2(ht);
    tva = round2(tva);
    var brut = round2(ht + tva);
    remise = Math.max(0, round2(remise || 0));
    return {
      montant_ht: ht,
      montant_tva: tva,
      montant_ttc_brut: brut,
      montant_ttc: round2(Math.max(0, brut - remise)),
      remise: remise
    };
  }

  function toast(msg, ok) {
    var $t = $('#posToast');
    $t.removeClass('ok err show').addClass(ok ? 'ok' : 'err').text(msg).addClass('show');
    clearTimeout(toast._tm);
    toast._tm = setTimeout(function () { $t.removeClass('show'); }, 3200);
  }

  function api(method, url, data) {
    return $.ajax({
      url: url,
      method: method,
      data: method === 'GET' ? data : JSON.stringify(data || {}),
      contentType: method === 'GET' ? undefined : 'application/json',
      dataType: 'json',
      headers: {
        'X-CSRF-TOKEN': cfg.csrf,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });
  }

  function updateUrl(id) {
    return cfg.urls.ticketsStore.replace(/\/?$/, '') + '/' + id;
  }

  function bsShow(name) {
    if (modals[name] && typeof modals[name].show === 'function') {
      modals[name].show();
    } else {
      $('#' + ({
        pay: 'posPayModal',
        open: 'posOpenModal',
        arret: 'posArretModal',
        fermeture: 'posFermetureModal'
      }[name])).modal ? $('#' + ({
        pay: 'posPayModal', open: 'posOpenModal', arret: 'posArretModal', fermeture: 'posFermetureModal'
      }[name])).modal('show') : $('#' + ({
        pay: 'posPayModal', open: 'posOpenModal', arret: 'posArretModal', fermeture: 'posFermetureModal'
      }[name])).addClass('show').css('display', 'block');
    }
  }

  function bsHide(name) {
    if (modals[name] && typeof modals[name].hide === 'function') {
      modals[name].hide();
    } else {
      var id = { pay: 'posPayModal', open: 'posOpenModal', arret: 'posArretModal', fermeture: 'posFermetureModal' }[name];
      var $el = $('#' + id);
      if ($el.modal) $el.modal('hide');
      else $el.removeClass('show').css('display', 'none');
    }
  }

  function initModals() {
    if (window.bootstrap && bootstrap.Modal) {
      modals.pay = new bootstrap.Modal(document.getElementById('posPayModal'));
      modals.open = new bootstrap.Modal(document.getElementById('posOpenModal'));
      modals.arret = new bootstrap.Modal(document.getElementById('posArretModal'));
      modals.fermeture = new bootstrap.Modal(document.getElementById('posFermetureModal'));
    }
  }

  function setBusy(b) {
    state.busy = !!b;
    if (!b) refreshActionButtons();
    else $('.pos-actions .btn').prop('disabled', true);
  }

  function refreshActionButtons() {
    var hasLines = state.lines.length > 0;
    var sessOk = state.session && state.session.statut === 'ouverte';
    $('#btnPay').prop('disabled', !hasLines || !sessOk || state.busy);
    $('#btnHold').prop('disabled', !hasLines || !sessOk || state.busy);
    $('#btnCancelTicket').prop('disabled', !state.ticketId || state.busy);
    $('#btnNewTicket').prop('disabled', !sessOk || state.busy);
  }

  function renderBanner() {
    var s = state.session;
    if (!s) {
      $('#posSessBadge').attr('class', 'pos-pill none').text('Fermée');
      $('#posCaisseLabel, #posDateMetier, #posFond, #posCaissier, #posNextNum').text('—');
      $('#inputDateDisplay').val('—');
      $('#posBlocker').addClass('show');
      $('#btnArret, #btnReprise, #btnFermeture').hide();
      $('#btnOpenSession').show();
      return;
    }
    $('#posBlocker').removeClass('show');
    var st = s.statut || 'none';
    $('#posSessBadge').attr('class', 'pos-pill ' + st).text(
      st === 'ouverte' ? 'Ouverte' : (st === 'en_arret' ? 'En arrêt' : st)
    );
    $('#posCaisseLabel').text(s.caisse ? (s.caisse.code + ' · ' + s.caisse.libelle) : '—');
    $('#posDateMetier').text(s.date_metier || '—');
    $('#inputDateDisplay').val(s.date_metier || '—');
    $('#posFond').text(money(s.fond_initial));
    var ob = s.opened_by;
    $('#posCaissier').text(ob ? ((ob.prenom || '') + ' ' + (ob.nom || '')).trim() : '—');
    $('#posNextNum').text(String((s.dernier_numero || 0) + 1).padStart(3, '0'));

    $('#btnArret').toggle(st === 'ouverte');
    $('#btnReprise').toggle(st === 'en_arret');
    $('#btnFermeture').toggle(!!cfg.canFermer && st !== 'fermee');
    $('#btnOpenSession').toggle(st === 'fermee' || !s);
  }

  function renderProducts(list) {
    state.lastProducts = list || [];
    var $tb = $('#posProductBody').empty();
    if (!list || !list.length) {
      $tb.append('<tr><td colspan="4" class="pos-empty">Aucun produit</td></tr>');
      return;
    }
    list.forEach(function (p) {
      var oos = (Number(p.stock_disponible) || 0) <= 0;
      var $tr = $('<tr class="' + (oos ? 'oos' : '') + '"/>');
      $tr.append($('<td class="p-code"/>').text(p.code || ''));
      $tr.append($('<td class="p-name"/>').attr('title', p.designation).text(p.designation || ''));
      $tr.append($('<td class="p-price"/>').text(money(p.prix_ht)));
      $tr.append($('<td class="p-stock"/>').text(money(p.stock_disponible)));
      $tr.on('click', function () { addProduct(p); });
      $tb.append($tr);
    });
  }

  function renderCart() {
    var tot = computeTotals(state.lines, state.remise);
    var $tb = $('#posCartBody').empty();
    if (!state.lines.length) {
      $tb.append('<tr><td colspan="4" class="pos-empty">Panier vide</td></tr>');
    } else {
      state.lines.forEach(function (l, idx) {
        var $tr = $('<tr class="' + (idx === state.selectedIdx ? 'selected' : '') + '"/>');
        var $name = $('<td/>');
        $name.append($('<div class="c-name"/>').text(l.designation));
        $name.append($('<div class="c-sub"/>').text(money(l.prix_unitaire) + ' HT · TVA ' + money(l.tva_taux) + '%'));
        var $qty = $('<td class="text-center"/>');
        var $ctrl = $('<div class="pos-qty"/>');
        $ctrl.append($('<button type="button">−</button>').on('click', function (e) {
          e.stopPropagation(); changeQty(idx, -1);
        }));
        $ctrl.append($('<span class="q"/>').text(l.quantite));
        $ctrl.append($('<button type="button">+</button>').on('click', function (e) {
          e.stopPropagation(); changeQty(idx, 1);
        }));
        $qty.append($ctrl);
        $tr.append($name);
        $tr.append($qty);
        $tr.append($('<td class="text-end fw-bold"/>').text(money(l.montant_ttc_ligne)));
        $tr.append($('<td class="text-center"/>').append(
          $('<button type="button" class="btn-x" title="Retirer">×</button>').on('click', function (e) {
            e.stopPropagation(); removeLine(idx);
          })
        ));
        $tr.on('click', function () { state.selectedIdx = idx; renderCart(); });
        $tb.append($tr);
      });
    }
    $('#totHt').text(money(tot.montant_ht));
    $('#totTva').text(money(tot.montant_tva));
    $('#totRemise').text(money(tot.remise));
    $('#totTtc').text(money(tot.montant_ttc));
    $('#ticketCodeLabel').text(state.ticketCode || 'Nouveau ticket');
    refreshActionButtons();
  }

  function renderHeld() {
    var $h = $('#posHeldList').empty();
    if (!state.held.length) {
      $h.append('<div class="pos-empty py-2">Aucun</div>');
      return;
    }
    state.held.forEach(function (t) {
      var $i = $('<div class="pos-held-item"/>');
      $i.append($('<span class="code"/>').text(t.code));
      $i.append($('<span/>').text(money(t.montant_ttc)));
      $i.on('click', function () { resumeTicket(t.id); });
      $h.append($i);
    });
  }

  function addProduct(p) {
    if (!state.session || state.session.statut !== 'ouverte') {
      toast('Ouvrez la caisse avant de vendre.', false);
      return;
    }
    var stock = Number(p.stock_disponible) || 0;
    var existing = state.lines.find(function (l) { return l.produits_id === p.id; });
    if (existing) {
      if (existing.quantite + 1 > stock + 0.009) {
        toast('Stock insuffisant : ' + (p.designation || ''), false);
        return;
      }
      existing.quantite = round2(existing.quantite + 1);
    } else {
      if (stock <= 0) {
        toast('Stock épuisé : ' + (p.designation || ''), false);
        return;
      }
      state.lines.push({
        produits_id: p.id,
        designation: p.designation,
        quantite: 1,
        prix_unitaire: Number(p.prix_ht) || 0,
        tva_taux: Number(p.tva_taux) || 0
      });
      state.selectedIdx = state.lines.length - 1;
    }
    renderCart();
    scheduleSync();
  }

  function changeQty(idx, delta) {
    var l = state.lines[idx];
    if (!l) return;
    var q = round2((Number(l.quantite) || 0) + delta);
    if (q <= 0) { removeLine(idx); return; }
    l.quantite = q;
    state.selectedIdx = idx;
    renderCart();
    scheduleSync();
  }

  function removeLine(idx) {
    state.lines.splice(idx, 1);
    state.selectedIdx = -1;
    renderCart();
    scheduleSync();
  }

  var syncTimer = null;
  function scheduleSync() {
    clearTimeout(syncTimer);
    syncTimer = setTimeout(syncCart, 320);
  }

  function cartPayload() {
    return {
      contribuable_id: state.contribuableId,
      client_libre: state.clientLibre || null,
      user_id: state.userId,
      remise: Number(state.remise) || 0,
      lignes: state.lines.map(function (l) {
        return {
          produits_id: l.produits_id,
          designation: l.designation,
          quantite: l.quantite,
          prix_unitaire: l.prix_unitaire,
          tva_taux: l.tva_taux
        };
      })
    };
  }

  function applyTicket(t) {
    state.ticketId = t.id;
    state.ticketCode = t.code;
    state.remise = Number(t.remise) || 0;
    state.contribuableId = t.contribuable_id;
    state.clientLibre = t.client_libre || '';
    state.userId = t.user_id || state.userId;
    state.lines = (t.lignes || []).map(function (l) {
      return {
        produits_id: l.produits_id,
        designation: l.designation,
        quantite: Number(l.quantite),
        prix_unitaire: Number(l.prix_unitaire),
        tva_taux: Number(l.tva_taux)
      };
    });
    $('#inputRemise').val(state.remise || '');
    $('#inputClientLibre').val(state.clientLibre || '');
    $('#inputClientSearch').val(t.client_label || state.clientLibre || '');
    if (state.userId) $('#selectSeller').val(String(state.userId));
    renderCart();
  }

  function syncCart() {
    if (!state.session || state.session.statut !== 'ouverte') return;
    if (!state.lines.length && !state.ticketId) return;
    var payload = cartPayload();
    setBusy(true);
    var req = !state.ticketId
      ? api('POST', cfg.urls.ticketsStore, payload)
      : api('PUT', updateUrl(state.ticketId), payload);
    req.done(function (res) {
      if (res.success && res.ticket) applyTicket(res.ticket);
    }).fail(function (xhr) {
      toast((xhr.responseJSON && xhr.responseJSON.message) || 'Sync panier échouée', false);
    }).always(function () { setBusy(false); });
  }

  function loadSession() {
    return api('GET', cfg.urls.sessionCourante).done(function (res) {
      state.session = res.session || null;
      renderBanner();
      refreshActionButtons();
    });
  }

  function loadMeta() {
    return api('GET', cfg.urls.meta).done(function (res) {
      state.meta = res;
      state.modepaies = res.modepaies || [];
      var $sel = $('#selectSeller').empty();
      (res.sellers || []).forEach(function (s) {
        $sel.append($('<option/>').val(s.id).text(s.label || ('#' + s.id)));
      });
      if (state.userId) $sel.val(String(state.userId));
    });
  }

  function searchProducts(q, opts) {
    opts = opts || {};
    var params = { q: q || '', limit: 50 };
    if (opts.exactCode && q) params.barcode = q;
    return api('GET', cfg.urls.produitsSearch, params).done(function (res) {
      var list = res.produits || [];
      if (opts.exactCode && list.length === 0 && q) {
        return api('GET', cfg.urls.produitsSearch, { q: q, limit: 50 }).done(function (res2) {
          list = res2.produits || [];
          renderProducts(list);
          maybeAutoAdd(list, opts);
        });
      }
      renderProducts(list);
      maybeAutoAdd(list, opts);
    });
  }

  function maybeAutoAdd(list, opts) {
    if (opts && opts.autoAddFirst && list.length === 1) {
      addProduct(list[0]);
      $('#posProductSearch').val('').trigger('focus');
      searchProducts('');
    }
  }

  function loadHeld() {
    return api('GET', cfg.urls.ticketsEnAttente).done(function (res) {
      state.held = res.tickets || [];
      renderHeld();
    });
  }

  function newTicket() {
    state.ticketId = null;
    state.ticketCode = null;
    state.lines = [];
    state.remise = 0;
    state.contribuableId = null;
    state.clientLibre = '';
    state.selectedIdx = -1;
    $('#inputRemise').val('');
    $('#inputClientLibre').val('');
    $('#inputClientSearch').val('');
    renderCart();
  }

  function holdTicket() {
    if (!state.lines.length) {
      toast('Panier vide', false);
      return;
    }
    setBusy(true);
    var afterSync = function () {
      if (!state.ticketId) {
        toast('Impossible de créer le ticket', false);
        setBusy(false);
        return;
      }
      api('POST', updateUrl(state.ticketId) + '/attente', cartPayload())
        .done(function () {
          toast('Ticket en attente', true);
          newTicket();
          loadHeld();
        })
        .fail(function (xhr) {
          toast((xhr.responseJSON && xhr.responseJSON.message) || 'Échec attente', false);
        })
        .always(function () { setBusy(false); });
    };
    if (!state.ticketId) {
      api('POST', cfg.urls.ticketsStore, cartPayload())
        .done(function (res) {
          if (res.ticket) applyTicket(res.ticket);
          afterSync();
        })
        .fail(function (xhr) {
          toast((xhr.responseJSON && xhr.responseJSON.message) || 'Création échouée', false);
          setBusy(false);
        });
    } else {
      afterSync();
    }
  }

  function resumeTicket(id) {
    setBusy(true);
    api('POST', updateUrl(id) + '/reprendre', {})
      .done(function (res) {
        if (res.ticket) applyTicket(res.ticket);
        toast('Ticket repris', true);
        loadHeld();
      })
      .fail(function (xhr) {
        toast((xhr.responseJSON && xhr.responseJSON.message) || 'Reprise impossible', false);
      })
      .always(function () { setBusy(false); });
  }

  function cancelTicket() {
    if (!state.ticketId) { newTicket(); return; }
    if (!window.confirm('Annuler ce ticket non payé ?')) return;
    setBusy(true);
    api('POST', updateUrl(state.ticketId) + '/annuler', { motif: 'Annulation POS' })
      .done(function () {
        toast('Ticket annulé', true);
        newTicket();
      })
      .fail(function (xhr) {
        toast((xhr.responseJSON && xhr.responseJSON.message) || 'Annulation impossible', false);
      })
      .always(function () { setBusy(false); });
  }

  /* ── Pay (espèces uniquement) ───────────────────────────── */
  function espModeId() {
    var esp = state.modepaies.find(function (m) { return String(m.code || '').toUpperCase() === 'ESP'; });
    if (esp) return esp.id;
    return state.modepaies.length ? state.modepaies[0].id : null;
  }

  function isEspId(modeId) {
    var mode = state.modepaies.find(function (m) { return String(m.id) === String(modeId); });
    return mode && String(mode.code || '').toUpperCase() === 'ESP';
  }

  function openPayModal() {
    if (!state.lines.length) return;
    var tot = computeTotals(state.lines, state.remise);
    $('#payTtcLabel').text(money(tot.montant_ttc));
    $('#payRecu').val(tot.montant_ttc);
    refreshPaySum();
    bsShow('pay');
    setTimeout(function () { $('#payRecu').trigger('focus').select(); }, 120);
  }

  function collectPayLines() {
    var tot = computeTotals(state.lines, state.remise);
    var modeId = espModeId();
    if (!modeId || !(tot.montant_ttc > 0)) return [];
    return [{ modepaie_id: modeId, montant: round2(tot.montant_ttc), reference_paiement: null }];
  }

  function refreshPaySum() {
    var tot = computeTotals(state.lines, state.remise);
    var recuVal = Number($('#payRecu').val()) || 0;
    $('#payRendu').text(money(Math.max(0, round2(recuVal - tot.montant_ttc))));
  }

  function confirmPay() {
    if (!state.ticketId) {
      setBusy(true);
      api('POST', cfg.urls.ticketsStore, cartPayload())
        .done(function (res) {
          if (res.ticket) {
            applyTicket(res.ticket);
            doPay();
          }
        })
        .fail(function (xhr) {
          toast((xhr.responseJSON && xhr.responseJSON.message) || 'Création ticket impossible', false);
          setBusy(false);
        });
      return;
    }
    doPay();
  }

  function doPay() {
    var tot = computeTotals(state.lines, state.remise);
    var paiements = collectPayLines();
    if (!paiements.length) {
      toast('Ajoutez un paiement', false);
      return;
    }
    var sum = round2(paiements.reduce(function (a, p) { return a + p.montant; }, 0));
    if (Math.abs(sum - tot.montant_ttc) > (cfg.tolerance || 0.009)) {
      toast('Paiements (' + money(sum) + ') ≠ TTC (' + money(tot.montant_ttc) + ')', false);
      return;
    }
    var recu = Number($('#payRecu').val());
    var espApplied = 0;
    paiements.forEach(function (p) {
      if (isEspId(p.modepaie_id)) espApplied += p.montant;
    });
    espApplied = round2(espApplied);
    if (espApplied > 0) {
      var firstEsp = paiements.find(function (p) { return isEspId(p.modepaie_id); });
      if (firstEsp) {
        firstEsp.montant_recu = recu > 0 ? recu : espApplied;
        firstEsp.rendu = Math.max(0, round2(firstEsp.montant_recu - espApplied));
      }
    }
    var payload = cartPayload();
    payload.expected_ttc = tot.montant_ttc;
    payload.paiements = paiements;
    setBusy(true);
    api('POST', updateUrl(state.ticketId) + '/payer', payload)
      .done(function (res) {
        toast('Payé · ' + (res.ticket && res.ticket.code), true);
        bsHide('pay');
        if (res.print_url) window.open(res.print_url, '_blank');
        newTicket();
        loadSession();
        searchProducts($('#posProductSearch').val());
        loadHeld();
      })
      .fail(function (xhr) {
        toast((xhr.responseJSON && xhr.responseJSON.message) || 'Paiement refusé', false);
      })
      .always(function () { setBusy(false); });
  }

  /* ── Session modals ──────────────────────────────────── */
  function refreshOpenFondTotal() {
    var total = 0;
    $('#openFondTable tbody tr').each(function () {
      var $tr = $(this);
      var denom = Number($tr.data('denom')) || 0;
      var qte = Number($tr.find('.open-fond-qte').val()) || 0;
      var valeur = denom * qte;
      total += valeur;
      $tr.find('.open-fond-valeur').text(money(valeur));
    });
    $('#openFondTotal').text(money(total));
    $('#openFond').val(total);
  }

  function openSessionModal() {
    $('#openFondTable .open-fond-qte').val(0);
    refreshOpenFondTotal();
    bsShow('open');
  }

  function confirmOpenSession() {
    var payload = {
      caisse_id: Number($('#openCaisseId').val()),
      fond_initial: Number($('#openFond').val()) || 0,
      date_metier: $('#openDate').val() || null,
      notes: $('#openNotes').val() || null
    };
    if (!payload.caisse_id) {
      toast('Choisissez une caisse', false);
      return;
    }
    setBusy(true);
    api('POST', cfg.urls.ouvrir, payload)
      .done(function (res) {
        toast('Caisse ouverte', true);
        bsHide('open');
        if (res.session) {
          state.session = res.session;
          renderBanner();
        } else {
          loadSession();
        }
        searchProducts('');
        loadHeld();
        refreshActionButtons();
        $('#posProductSearch').trigger('focus');
      })
      .fail(function (xhr) {
        toast((xhr.responseJSON && xhr.responseJSON.message) || 'Ouverture impossible', false);
      })
      .always(function () { setBusy(false); });
  }

  function confirmArret() {
    if (!state.session) return;
    var payload = {
      session_id: state.session.id,
      fond_declare: $('#arretFond').val() !== '' ? Number($('#arretFond').val()) : null,
      notes: $('#arretNotes').val() || null
    };
    setBusy(true);
    api('POST', cfg.urls.arret, payload)
      .done(function (res) {
        toast('Arrêt enregistré', true);
        bsHide('arret');
        if (res.print_url) window.open(res.print_url, '_blank');
        loadSession();
      })
      .fail(function (xhr) {
        toast((xhr.responseJSON && xhr.responseJSON.message) || 'Arrêt impossible', false);
      })
      .always(function () { setBusy(false); });
  }

  function confirmFermeture() {
    if (!state.session) return;
    var payload = {
      session_id: state.session.id,
      montant_declare: $('#fermMontant').val() !== '' ? Number($('#fermMontant').val()) : null,
      notes: $('#fermNotes').val() || null
    };
    setBusy(true);
    api('POST', cfg.urls.fermeture, payload)
      .done(function (res) {
        toast('Caisse fermée', true);
        bsHide('fermeture');
        if (res.print_url) window.open(res.print_url, '_blank');
        loadSession();
      })
      .fail(function (xhr) {
        toast((xhr.responseJSON && xhr.responseJSON.message) || 'Fermeture impossible', false);
      })
      .always(function () { setBusy(false); });
  }

  function wireQuickCreate() {
    if (!window.QuickCreate) return;

    // Client
    QuickCreate.bind({
      formSelector: '#quickCreateClientForm',
      modalSelector: '#quickCreateClientModal',
      targetSelect: function () { return $(); },
      mapItem: function (data) {
        return data.item || data;
      },
      onSuccess: function (item) {
        state.contribuableId = item.id;
        state.clientLibre = null;
        $('#inputClientSearch').val(item.assujeti || item.text || '');
        $('#inputClientLibre').val('');
        scheduleSync();
        toast('Client créé', true);
      }
    });

    // Product
    QuickCreate.bind({
      formSelector: '#quickCreateProductForm',
      modalSelector: '#quickCreateProductModal',
      targetSelect: function () { return $(); },
      mapItem: function (data) {
        return data.item || data;
      },
      onSuccess: function (item) {
        var p = {
          id: item.id,
          code: item.code,
          designation: item.designation,
          prix_ht: Number(item.prix_unitaire) || 0,
          tva_taux: Number(item.tva_taux) || 0,
          stock_disponible: Number(item.qte) || 0
        };
        // Refresh list then add
        searchProducts(item.code || item.designation || '').done(function () {
          var found = state.lastProducts.find(function (x) { return x.id === item.id; });
          addProduct(found || p);
        });
        toast('Produit créé', true);
      }
    });
  }

  function showBsModalById(id) {
    var el = document.getElementById(id);
    if (!el) return;
    if (window.bootstrap && bootstrap.Modal) {
      bootstrap.Modal.getOrCreateInstance(el).show();
    } else if ($(el).modal) {
      $(el).modal('show');
    }
  }

  function bindEvents() {
    var searchTimer;
    $('#posProductSearch').on('input', function () {
      var q = $(this).val();
      clearTimeout(searchTimer);
      searchTimer = setTimeout(function () { searchProducts(q); }, 260);
    }).on('keydown', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        var q = $(this).val();
        searchProducts(q, { exactCode: true, autoAddFirst: true }).done(function () {
          var n = state.lastProducts.length;
          if (n > 1) {
            addProduct(state.lastProducts[0]);
            $('#posProductSearch').val('').trigger('focus');
          } else if (n === 0 && q) {
            toast('Produit introuvable : ' + q, false);
          }
        });
      }
    });

    $('#inputRemise').on('change input', function () {
      state.remise = Number($(this).val()) || 0;
      renderCart();
      scheduleSync();
    });
    $('#inputClientLibre').on('change', function () {
      state.clientLibre = $(this).val();
      scheduleSync();
    });
    $('#selectSeller').on('change', function () {
      state.userId = Number($(this).val()) || null;
      scheduleSync();
    });

    var clientTimer;
    $('#inputClientSearch').on('input', function () {
      var q = $(this).val();
      state.contribuableId = null;
      clearTimeout(clientTimer);
      clientTimer = setTimeout(function () {
        if (!q || q.length < 2) {
          $('#clientDropdown').removeClass('open').empty();
          return;
        }
        api('GET', cfg.urls.clientsSearch, { q: q }).done(function (res) {
          var $d = $('#clientDropdown').empty().addClass('open');
          (res.contribuables || []).forEach(function (c) {
            $('<div/>').text(c.assujeti + (c.ifu ? ' · ' + c.ifu : ''))
              .on('click', function () {
                state.contribuableId = c.id;
                state.clientLibre = null;
                $('#inputClientSearch').val(c.assujeti);
                $('#inputClientLibre').val('');
                $d.removeClass('open');
                scheduleSync();
              })
              .appendTo($d);
          });
        });
      }, 280);
    });
    $(document).on('click', function (e) {
      if (!$(e.target).closest('.pos-client-wrap').length) {
        $('#clientDropdown').removeClass('open');
      }
    });

    $('#btnPay').on('click', openPayModal);
    $('#btnHold').on('click', holdTicket);
    $('#btnNewTicket').on('click', newTicket);
    $('#btnCancelTicket').on('click', cancelTicket);
    $('#btnPayConfirm').on('click', confirmPay);
    $('#payRecu').on('input', refreshPaySum);

    $('#btnOpenSession, #btnOpenSession2').on('click', openSessionModal);
    $('#btnOpenConfirm').on('click', confirmOpenSession);
    $(document).on('input', '#openFondTable .open-fond-qte', refreshOpenFondTotal);

    $('#btnArret').on('click', function () { bsShow('arret'); });
    $('#btnArretConfirm').on('click', confirmArret);

    $('#btnReprise').on('click', function () {
      if (!state.session) return;
      setBusy(true);
      api('POST', cfg.urls.reprise, { session_id: state.session.id })
        .done(function (res) {
          toast('Session reprise', true);
          if (res.session) {
            state.session = res.session;
            renderBanner();
          } else loadSession();
          refreshActionButtons();
        })
        .fail(function (xhr) {
          toast((xhr.responseJSON && xhr.responseJSON.message) || 'Reprise impossible', false);
        })
        .always(function () { setBusy(false); });
    });

    $('#btnFermeture').on('click', function () { bsShow('fermeture'); });
    $('#btnFermetureConfirm').on('click', confirmFermeture);

    $('#btnQuickClient').on('click', function () {
      showBsModalById('quickCreateClientModal');
    });
    $('#btnQuickProduct').on('click', function () {
      showBsModalById('quickCreateProductModal');
    });

    $(document).on('keydown', function (e) {
      if (e.key === 'Escape') return;
      if (e.key === 'F2') {
        e.preventDefault();
        if (!$('#btnPay').prop('disabled')) openPayModal();
      }
      if (e.key === 'F3') {
        e.preventDefault();
        if (!$('#btnHold').prop('disabled')) holdTicket();
      }
      if ((e.key === '+' || e.key === '-') && state.selectedIdx >= 0 &&
          document.activeElement.tagName !== 'INPUT' &&
          document.activeElement.tagName !== 'SELECT' &&
          document.activeElement.tagName !== 'TEXTAREA') {
        e.preventDefault();
        changeQty(state.selectedIdx, e.key === '+' ? 1 : -1);
      }
    });
  }

  function init() {
    if (!cfg.urls) {
      console.error('COMPTOIR_POS config missing');
      return;
    }
    initModals();
    bindEvents();
    wireQuickCreate();
    $.when(loadSession(), loadMeta()).always(function () {
      searchProducts('');
      loadHeld();
      renderCart();
      if (cfg.autoOpen && (!state.session || state.session.statut === 'fermee')) {
        openSessionModal();
      }
      $('#posProductSearch').trigger('focus');
    });
  }

  $(init);
})(jQuery, window);
