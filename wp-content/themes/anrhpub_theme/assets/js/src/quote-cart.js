export function initQuoteCart() {
    var cfg = window.anrhpubQuoteCart;
    if (!cfg) {
      return;
    }

    function loadCart() {
      try {
        var raw = localStorage.getItem(cfg.storageKey);
        if (raw) {
          var parsed = JSON.parse(raw);
          if (Array.isArray(parsed)) {
            return parsed;
          }
        }
      } catch (err) {
        /* ignore */
      }
      return [];
    }

    function lineKey(productId, colorId) {
      return String(parseInt(productId, 10)) + ':' + String(parseInt(colorId, 10) || 0);
    }

    function getMinQty(productId, colorId, el) {
      var min = 1;
      if (el) {
        var qtyInput = el.matches && el.matches('[data-quote-qty-input]')
          ? el
          : el.querySelector && el.querySelector('[data-quote-qty-input]');
        if (qtyInput && qtyInput.getAttribute('data-quote-min-qty')) {
          min = parseInt(qtyInput.getAttribute('data-quote-min-qty'), 10);
        } else if (el.getAttribute('data-quote-min-qty')) {
          min = parseInt(el.getAttribute('data-quote-min-qty'), 10);
        }
      }
      if (min === 1 && productId) {
        var selector = '[data-quote-line][data-product-id="' + productId + '"]';
        if (colorId !== undefined && colorId !== null) {
          selector += '[data-color-id="' + (parseInt(colorId, 10) || 0) + '"]';
        }
        var row = document.querySelector(selector);
        if (row) {
          min = parseInt(row.getAttribute('data-quote-min-qty') || '1', 10);
        }
      }
      if (isNaN(min) || min < 1) {
        min = 1;
      }
      return Math.min(99999, min);
    }

    function getMaxQty(productId, colorId, el) {
      var max = 99999;
      if (el && el.getAttribute('data-quote-max-qty')) {
        max = parseInt(el.getAttribute('data-quote-max-qty'), 10);
      } else if (productId) {
        var selector = '[data-quote-line][data-product-id="' + productId + '"]';
        if (colorId !== undefined && colorId !== null) {
          selector += '[data-color-id="' + (parseInt(colorId, 10) || 0) + '"]';
        }
        var row = document.querySelector(selector);
        if (row && row.getAttribute('data-quote-max-qty')) {
          max = parseInt(row.getAttribute('data-quote-max-qty'), 10);
        }
      }
      if (isNaN(max) || max < 1) {
        max = 99999;
      }
      return Math.min(99999, max);
    }

    function getProductQuoteMaxQty(wrap) {
      return 99999;
    }

    function syncQuoteQtyControl(wrap) {
      if (!wrap) {
        return;
      }
      var input = wrap.querySelector('[data-quote-qty-input]');
      var minusBtn = wrap.querySelector('[data-quote-qty-minus]');
      var plusBtn = wrap.querySelector('[data-quote-qty-plus]');
      if (!input) {
        return;
      }
      var min = getMinQty(wrap.getAttribute('data-product-id'), getSelectedColorId(wrap), wrap);
      var max = Math.max(min, getProductQuoteMaxQty(wrap));
      var current = normalizeQty(input.value, min, max);
      input.value = String(current);
      input.setAttribute('min', String(min));
      input.setAttribute('max', String(max));
      input.setAttribute('data-quote-max-qty', String(max));
      wrap.setAttribute('data-quote-max-qty', String(max));
      if (minusBtn) {
        minusBtn.disabled = current <= min;
      }
      if (plusBtn) {
        plusBtn.disabled = current >= max;
      }
    }

    function applyProductColorLimits(wrap) {
      syncColorPickerSelection(wrap);
      syncQuoteQtyControl(wrap);
    }

    function syncColorPickerSelection(wrap) {
      if (!wrap) {
        return;
      }
      wrap.querySelectorAll('.product-color-picker__option').forEach(function (opt) {
        var radio = opt.querySelector('[data-quote-color-input]');
        opt.classList.toggle('is-selected', radio && radio.checked);
      });
    }

    function getSelectedColorId(wrap) {
      if (!wrap || wrap.getAttribute('data-requires-color') !== '1') {
        return 0;
      }
      var checked = wrap.querySelector('[data-quote-color-input]:checked');
      return checked ? parseInt(checked.value, 10) || 0 : 0;
    }

    function normalizeQty(qty, minQty, maxQty) {
      var min = minQty || 1;
      var max = maxQty || 99999;
      if (max < min) {
        max = min;
      }
      var n = parseInt(qty, 10);
      if (isNaN(n)) {
        n = min;
      }
      n = Math.max(min, Math.min(max, n));
      return n;
    }

    function sanitizeItems(items) {
      var map = {};
      var mins = {};
      var maxs = {};
      (items || []).forEach(function (item) {
        if (!item || !item.product_id) {
          return;
        }
        var id = parseInt(item.product_id, 10);
        var cid = parseInt(item.color_id, 10) || 0;
        if (id > 0) {
          var key = lineKey(id, cid);
          map[key] = (map[key] || 0) + normalizeQty(item.qty, 1);
          mins[key] = getMinQty(String(id), cid);
          maxs[key] = getMaxQty(String(id), cid);
        }
      });
      return Object.keys(map).map(function (key) {
        var parts = key.split(':');
        var pid = parseInt(parts[0], 10);
        var cid = parseInt(parts[1], 10) || 0;
        return {
          product_id: pid,
          color_id: cid,
          qty: normalizeQty(map[key], mins[key] || 1, maxs[key] || 99999)
        };
      });
    }

    function emitCartUpdated(detail) {
      window.dispatchEvent(
        new CustomEvent('anrhpub:cart-updated', {
          detail: detail || {}
        })
      );
    }

    function renderEmptyCartHtml() {
      var catalogue = cfg.catalogueUrl || '/catalogue/';
      return (
        '<div class="quote-cart-empty" data-quote-cart-empty>' +
        '<p>' + (cfg.i18n.empty || 'Votre panier devis est vide.') + '</p>' +
        '<a class="btn btn--primary" href="' + catalogue + '">' +
        'Parcourir le catalogue</a></div>'
      );
    }

    function updateRecapPanels(summary, hasItems) {
      var box = document.querySelector('[data-quote-recap-box]');
      var textEl = document.querySelector('[data-quote-recap-text]');
      var contactNotice = document.querySelector('[data-quote-contact-notice]');
      var contactText = document.querySelector('[data-quote-contact-summary]');

      if (!hasItems) {
        if (box) {
          box.hidden = true;
        }
        if (contactNotice) {
          contactNotice.hidden = true;
        }
        if (textEl) {
          textEl.textContent = '';
        }
        sessionStorage.removeItem('anrhpub_quote_summary');
        return;
      }

      if (textEl && summary) {
        textEl.textContent = summary;
      }
      if (box) {
        box.hidden = false;
      }
      if (contactNotice && contactText) {
        contactNotice.hidden = false;
        if (summary) {
          contactText.textContent = summary;
        }
      }
      if (summary) {
        sessionStorage.setItem('anrhpub_quote_summary', summary);
      }
    }

    function applyServerCartData(items, data) {
      data = data || {};
      var app = document.querySelector('[data-quote-cart-app]');

      if (app) {
        if (typeof data.html === 'string') {
          app.innerHTML = data.html;
        } else if (!items.length) {
          app.innerHTML = renderEmptyCartHtml();
        }
        app.classList.remove('is-syncing');
      }

      updateRecapPanels(data.summary || '', items.length > 0);
      emitCartUpdated({
        items: items,
        summary: data.summary || '',
        devisMessage: data.devis_message || '',
        productLines: data.product_lines || [],
        count: typeof data.count === 'number' ? data.count : items.length
      });
    }

    function saveCart(items) {
      var clean = sanitizeItems(items);
      localStorage.setItem(cfg.storageKey, JSON.stringify(clean));
      updateBadges(clean);

      var app = document.querySelector('[data-quote-cart-app]');
      if (app) {
        app.classList.add('is-syncing');
        if (!clean.length) {
          app.innerHTML = renderEmptyCartHtml();
        }
      }

      if (!clean.length) {
        updateRecapPanels('', false);
      }

      return syncServer(clean).then(function (res) {
        if (res.success && res.data) {
          applyServerCartData(clean, res.data);
        } else {
          if (app) {
            app.classList.remove('is-syncing');
          }
          emitCartUpdated({ items: clean });
          if (!clean.length) {
            updateRecapPanels('', false);
          }
        }
        return clean;
      });
    }

    function updateBadges(items) {
      var count = items.length;
      document.querySelectorAll('[data-quote-cart-count]').forEach(function (el) {
        el.textContent = String(count);
        el.hidden = count <= 0;
      });
    }

    function buildSummaryText(items) {
      if (!items.length) {
        return '';
      }
      var lines = ['Demande de devis — sélection catalogue:', ''];
      items.forEach(function (item) {
        var color = item.color_id ? ' (couleur #' + item.color_id + ')' : '';
        lines.push('- Produit #' + item.product_id + color + ' — ' + item.qty + ' unité(s)');
      });
      return lines.join('\n');
    }

    function buildSummaryFromHtml() {
      var rows = document.querySelectorAll('[data-quote-line]');
      if (!rows.length) {
        return '';
      }
      var lines = ['Demande de devis — sélection catalogue:', ''];
      rows.forEach(function (row) {
        var title = row.querySelector('.quote-cart-table__product a');
        var refCell = row.cells && row.cells[1] ? row.cells[1].textContent.trim() : '';
        var colorCell = row.querySelector('.quote-cart-table__color');
        var colorName = colorCell ? colorCell.textContent.replace(/\s+/g, ' ').trim() : '';
        var qtyInput = row.querySelector('[data-quote-line-qty]');
        var name = title ? title.textContent.trim() : '';
        var ref = refCell && refCell !== '—' ? ' [' + refCell + ']' : '';
        var color = colorName && colorName !== '—' ? ' — Couleur : ' + colorName : '';
        var qty = qtyInput ? qtyInput.value : '1';
        lines.push('- ' + name + ref + color + ' — ' + qty + ' unité(s)');
      });
      return lines.join('\n');
    }

    function syncServer(items) {
      var body = new FormData();
      body.append('action', 'anrhpub_sync_quote_cart');
      body.append('nonce', cfg.nonce);
      body.append('cart', JSON.stringify(items));

      return fetch(cfg.ajaxUrl, { method: 'POST', body: body, credentials: 'same-origin' })
        .then(function (r) {
          return r.json();
        })
        .catch(function () {
          return { success: false };
        });
    }

    function addToCart(productId, qty, colorId, contextEl) {
      var items = loadCart();
      var id = parseInt(productId, 10);
      var cid = parseInt(colorId, 10) || 0;
      var min = getMinQty(String(id), cid, contextEl);
      var max = getMaxQty(String(id), cid, contextEl);
      var requested = parseInt(qty, 10);
      var addQty = normalizeQty(qty, min, max);
      var key = lineKey(id, cid);
      var found = false;
      var adjusted = !isNaN(requested) && requested < min;

      items = items.map(function (item) {
        if (lineKey(item.product_id, item.color_id) === key) {
          found = true;
          return {
            product_id: id,
            color_id: cid,
            qty: normalizeQty((parseInt(item.qty, 10) || 0) + addQty, min, max)
          };
        }
        return item;
      });

      if (!found) {
        items.push({ product_id: id, color_id: cid, qty: addQty });
      }

      return saveCart(items).then(function (result) {
        if (adjusted && window.anrhpubShowToast) {
          window.anrhpubShowToast(cfg.i18n.qtyAdjusted, 'info', 3200);
        }
        if (!isNaN(requested) && requested > max && window.anrhpubShowToast) {
          window.anrhpubShowToast(cfg.i18n.stockCapped, 'info', 3200);
        }
        return result;
      });
    }

    function setLineQty(productId, qty, colorId, contextEl) {
      var id = parseInt(productId, 10);
      var cid = parseInt(colorId, 10) || 0;
      var min = getMinQty(String(id), cid, contextEl);
      var max = getMaxQty(String(id), cid, contextEl);
      var requested = parseInt(qty, 10);
      var finalQty = normalizeQty(qty, min, max);
      var key = lineKey(id, cid);
      var items = loadCart().filter(function (item) {
        return lineKey(item.product_id, item.color_id) !== key;
      });
      if (finalQty >= min) {
        items.push({ product_id: id, color_id: cid, qty: finalQty });
      }
      return saveCart(items).then(function (result) {
        if (!isNaN(requested) && requested < min && window.anrhpubShowToast) {
          window.anrhpubShowToast(cfg.i18n.qtyAdjusted, 'info', 3200);
        }
        if (!isNaN(requested) && requested > max && window.anrhpubShowToast) {
          window.anrhpubShowToast(cfg.i18n.stockCapped, 'info', 3200);
        }
        return result;
      });
    }

    function removeLine(productId, colorId) {
      var key = lineKey(productId, colorId);
      var items = loadCart().filter(function (item) {
        return lineKey(item.product_id, item.color_id) !== key;
      });
      return saveCart(items);
    }

    window.anrhpubQuoteCartAdd = addToCart;

    if (cfg.syncOnLoad && cfg.serverCart && cfg.serverCart.length) {
      localStorage.setItem(cfg.storageKey, JSON.stringify(sanitizeItems(cfg.serverCart)));
    }

    var initial = sanitizeItems(loadCart());
    updateBadges(initial);

    if (document.querySelector('[data-quote-cart-app]') || (window.anrhpubContactDevis && window.anrhpubContactDevis.isDevis)) {
      syncServer(initial).then(function (res) {
        if (res.success && res.data) {
          applyServerCartData(initial, res.data);
        } else {
          emitCartUpdated({ items: initial });
        }
      });
    }

    document.querySelectorAll('[data-product-quote]').forEach(function (wrap) {
      var productId = wrap.getAttribute('data-product-id');
      var input = wrap.querySelector('[data-quote-qty-input]');
      var addBtn = wrap.querySelector('[data-quote-add]');

      function limits() {
        var colorId = getSelectedColorId(wrap);
        var min = getMinQty(productId, colorId, wrap);
        return {
          min: min,
          max: Math.max(min, getProductQuoteMaxQty(wrap))
        };
      }

      applyProductColorLimits(wrap);
      syncColorPickerSelection(wrap);

      wrap.querySelectorAll('[data-quote-color-input]').forEach(function (radio) {
        radio.addEventListener('change', function () {
          syncColorPickerSelection(wrap);
          applyProductColorLimits(wrap);
        });
      });

      wrap.querySelectorAll('[data-quote-qty-minus]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var lim = limits();
          input.value = String(normalizeQty(parseInt(input.value, 10) - 1, lim.min, lim.max));
          syncQuoteQtyControl(wrap);
        });
      });

      wrap.querySelectorAll('[data-quote-qty-plus]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var lim = limits();
          input.value = String(normalizeQty(parseInt(input.value, 10) + 1, lim.min, lim.max));
          syncQuoteQtyControl(wrap);
        });
      });

      if (input) {
        input.addEventListener('change', function () {
          var lim = limits();
          var before = parseInt(input.value, 10);
          input.value = String(normalizeQty(input.value, lim.min, lim.max));
          if (!isNaN(before) && before < lim.min && window.anrhpubShowToast) {
            window.anrhpubShowToast(cfg.i18n.qtyAdjusted, 'info', 3200);
          }
          syncQuoteQtyControl(wrap);
        });
        input.addEventListener('input', function () {
          syncQuoteQtyControl(wrap);
        });
      }

      if (addBtn) {
        addBtn.addEventListener('click', function () {
          var colorId = getSelectedColorId(wrap);
          if (wrap.getAttribute('data-requires-color') === '1' && !colorId) {
            if (window.anrhpubShowToast) {
              window.anrhpubShowToast(cfg.i18n.colorRequired, 'error');
            }
            return;
          }
          addToCart(productId, input.value, colorId, wrap).then(function () {
            var limAfter = limits();
            input.value = String(normalizeQty(input.value, limAfter.min, limAfter.max));
            if (window.anrhpubShowToast) {
              window.anrhpubShowToast(cfg.i18n.added, 'success', 3200);
            }
          });
        });
      }
    });

    var lineQtyDebounce = null;

    document.addEventListener('click', function (e) {
      var removeBtn = e.target.closest('[data-quote-line-remove]');
      if (removeBtn) {
        var row = removeBtn.closest('[data-quote-line]');
        if (row) {
          var productId = row.getAttribute('data-product-id');
          var colorId = row.getAttribute('data-color-id');
          var tbody = row.closest('tbody');
          row.classList.add('is-removing');
          row.remove();
          if (tbody && !tbody.querySelector('[data-quote-line]')) {
            var app = document.querySelector('[data-quote-cart-app]');
            if (app) {
              app.innerHTML = renderEmptyCartHtml();
            }
          }
          removeLine(productId, colorId).then(function () {
            if (window.anrhpubShowToast) {
              window.anrhpubShowToast(cfg.i18n.removed, 'info', 2800);
            }
          });
        }
        return;
      }

      var copyBtn = e.target.closest('[data-quote-copy-recap]');
      if (copyBtn) {
        var text = document.querySelector('[data-quote-recap-text]');
        if (text && navigator.clipboard) {
          navigator.clipboard.writeText(text.textContent || '');
          if (window.anrhpubShowToast) {
            window.anrhpubShowToast(cfg.i18n.copied, 'success', 2500);
          }
        }
      }
    });

    function commitLineQty(qtyInput, showToast) {
      var row = qtyInput.closest('[data-quote-line]');
      if (!row) {
        return;
      }
      setLineQty(row.getAttribute('data-product-id'), qtyInput.value, row.getAttribute('data-color-id'), row).then(function () {
        qtyInput.value = String(
          normalizeQty(
            qtyInput.value,
            getMinQty(row.getAttribute('data-product-id'), row.getAttribute('data-color-id'), row),
            getMaxQty(row.getAttribute('data-product-id'), row.getAttribute('data-color-id'), row)
          )
        );
        if (showToast && window.anrhpubShowToast) {
          window.anrhpubShowToast(cfg.i18n.updated, 'success', 2200);
        }
      });
    }

    document.addEventListener('change', function (e) {
      var qtyInput = e.target.closest('[data-quote-line-qty]');
      if (qtyInput) {
        commitLineQty(qtyInput, true);
      }
    });

    document.addEventListener('input', function (e) {
      var qtyInput = e.target.closest('[data-quote-line-qty]');
      if (!qtyInput) {
        return;
      }
      clearTimeout(lineQtyDebounce);
      lineQtyDebounce = setTimeout(function () {
        commitLineQty(qtyInput, false);
      }, 400);
    });

    function getDevisProductLineParts(line) {
      line = line || {};
      var title = line.title || '';
      var qty = Math.max(1, parseInt(line.qty, 10) || 1);
      var details = ['Quantité : ' + qty + (qty > 1 ? ' unités' : ' unité')];

      if (line.ref) {
        title += ' — réf. ' + line.ref;
      }
      if (line.color_name) {
        details.push('Couleur : ' + line.color_name);
      } else if (line.has_colors) {
        details.push('Couleur : à définir avec vous');
      }
      if (line.min_qty && parseInt(line.min_qty, 10) > 1) {
        details.push('Quantité minimum catalogue : ' + parseInt(line.min_qty, 10) + ' unités');
      }

      return { title: title, details: details };
    }

    function formatProductLine(line) {
      var parts = getDevisProductLineParts(line);
      return parts.title + ' · ' + parts.details.join(' · ');
    }

    function buildClientDevisMessage(productLines, devisCfg) {
      devisCfg = devisCfg || window.anrhpubContactDevis || {};
      var profile = devisCfg.clientProfile || {};
      var lines = productLines && productLines.length ? productLines : [];
      var totalQty = 0;
      var message = [];

      lines.forEach(function (line) {
        totalQty += Math.max(1, parseInt(line.qty, 10) || 1);
      });

      message.push('Bonjour,');
      message.push('');

      if (lines.length) {
        message.push('Je vous contacte pour obtenir un devis sur la sélection d’objets publicitaires ci-dessous. Voici le détail de ma demande.');
      } else {
        message.push('Je vous contacte pour obtenir un devis sur des objets publicitaires. Mon panier est encore vide sur le site : pourriez-vous m’aider à finaliser ma sélection ?');
      }

      message.push('');

      if (profile.name || profile.email || profile.company || profile.phone) {
        message.push('Mes coordonnées');
        if (profile.name) {
          message.push('Nom : ' + profile.name);
        }
        if (profile.company) {
          message.push('Société : ' + profile.company);
        }
        if (profile.email) {
          message.push('E-mail : ' + profile.email);
        }
        if (profile.phone) {
          message.push('Téléphone : ' + profile.phone);
        }
        message.push('');
      }

      message.push('Adresse de livraison souhaitée');
      if (devisCfg.deliveryAddress) {
        devisCfg.deliveryAddress.split(/\r\n|\r|\n/).forEach(function (row) {
          row = row.trim();
          if (row) {
            message.push(row);
          }
        });
      } else {
        message.push(devisCfg.noDeliveryHint || 'Je n’ai pas encore renseigné d’adresse de livraison dans mon compte. Merci de me recontacter pour la valider avant l’établissement du devis.');
      }

      message.push('');
      message.push('Marquage (logo / personnalisation)');
      if (devisCfg.brandLogoUrl) {
        message.push('Mon logo est enregistré sur mon compte client — lien pour téléchargement :');
        message.push(devisCfg.brandLogoUrl);
        message.push('Si vous avez besoin d’un fichier vectoriel (AI, EPS, PDF), je peux vous l’envoyer par e-mail.');
      } else {
        message.push('Je n’ai pas encore déposé de logo sur mon compte. Indiquez-moi comment vous souhaitez le recevoir pour chiffrer le marquage.');
      }

      message.push('');
      message.push('Détail des articles demandés');
      message.push('');

      if (lines.length) {
        lines.forEach(function (line, index) {
          var parts = getDevisProductLineParts(line);
          message.push((index + 1) + '. ' + parts.title);
          parts.details.forEach(function (detail) {
            message.push('   • ' + detail);
          });
          message.push('');
        });
        message.pop();
        message.push('');
        message.push('Total : ' + totalQty + ' unités sur l’ensemble de la commande.');
        message.push('Nombre de références : ' + lines.length + '.');
      } else {
        message.push('Aucun article dans le panier pour le moment.');
      }

      message.push('');
      message.push('Pourriez-vous me faire parvenir vos tarifs, les délais (fabrication et livraison) ainsi que les options de marquage disponibles (emplacements, couleurs, etc.) ?');
      message.push('');
      message.push('Merci d’avance pour votre retour.');
      message.push('');
      message.push('Cordialement,');
      if (profile.name) {
        message.push(profile.name);
      }
      if (profile.company) {
        message.push(profile.company);
      }

      return message.join('\n');
    }

    function buildCartLinesForDevisMessage(productLines) {
      if (productLines && productLines.length) {
        return productLines.map(function (line) {
          return {
            product_id: line.product_id || 0,
            title: line.title || '',
            ref: line.ref || '',
            color_name: line.color_name || '',
            qty: line.qty || 1,
            min_qty: line.min_qty || 1,
            has_colors: !!line.has_colors
          };
        });
      }

      var rows = document.querySelectorAll('[data-quote-line]');
      var lines = [];

      if (rows.length) {
        rows.forEach(function (row) {
          var title = row.querySelector('.quote-cart-table__product a');
          var refCell = row.cells && row.cells[1] ? row.cells[1].textContent.trim() : '';
          var colorCell = row.querySelector('.quote-cart-table__color');
          var colorName = colorCell ? colorCell.textContent.replace(/\s+/g, ' ').trim() : '';
          var qtyInput = row.querySelector('[data-quote-line-qty]');
          var name = title ? title.textContent.trim() : '';
          var ref = refCell && refCell !== '—' ? refCell : '';
          var color = colorName && colorName !== '—' ? colorName : '';
          var qty = qtyInput ? parseInt(qtyInput.value, 10) || 1 : 1;
          var minQty = parseInt(row.getAttribute('data-quote-min-qty') || '1', 10) || 1;
          lines.push({
            product_id: parseInt(row.getAttribute('data-product-id') || '0', 10) || 0,
            title: name,
            ref: ref,
            color_name: color,
            qty: qty,
            min_qty: minQty,
            has_colors: color !== '' || row.querySelector('.quote-cart-table__color-swatch') !== null
          });
        });
        return lines;
      }

      loadCart().forEach(function (item) {
        lines.push({
          product_id: item.product_id,
          title: 'Produit #' + item.product_id,
          ref: '',
          color_name: item.color_id ? 'couleur #' + item.color_id : '',
          qty: item.qty || 1,
          min_qty: 1,
          has_colors: !!item.color_id
        });
      });

      return lines;
    }

    function updateContactCartPreview(detail) {
      var preview = document.querySelector('[data-contact-cart-preview]');
      var list = document.querySelector('[data-contact-cart-list]');
      var empty = document.querySelector('[data-contact-cart-empty]');
      if (!preview || !list) {
        return;
      }

      var productLines = (detail && detail.productLines) || [];
      list.innerHTML = '';

      if (!productLines.length) {
        preview.hidden = true;
        if (empty) {
          empty.hidden = false;
        }
        return;
      }

      preview.hidden = false;
      if (empty) {
        empty.hidden = true;
      }

      productLines.forEach(function (line) {
        var li = document.createElement('li');
        li.textContent = formatProductLine(line);
        list.appendChild(li);
      });
    }

    function updateContactLogoBlock(devisCfg) {
      var box = document.querySelector('[data-contact-logo-box]');
      var img = document.querySelector('[data-contact-logo-preview]');
      var hidden = document.querySelector('[data-contact-logo-hidden]');
      var emptyHint = document.querySelector('[data-contact-logo-empty]');
      if (!box) {
        return;
      }

      var url = (devisCfg && devisCfg.brandLogoUrl) || '';
      if (url && img && hidden) {
        box.hidden = false;
        box.classList.remove('contact-devis-logo--empty');
        img.hidden = false;
        img.src = url;
        img.alt = (devisCfg && devisCfg.brandLogoAlt) || '';
        hidden.value = url;
        if (emptyHint) {
          emptyHint.hidden = true;
        }
      } else if (hidden) {
        hidden.value = '';
        if (img) {
          img.hidden = true;
          img.removeAttribute('src');
        }
        if (emptyHint) {
          emptyHint.hidden = false;
        }
        box.classList.add('contact-devis-logo--empty');
      }
    }

    function refreshContactFromCart(detail) {
      var devisCfg = window.anrhpubContactDevis;
      if (!devisCfg || !devisCfg.isDevis) {
        return;
      }

      var contactMessage = document.querySelector('[data-contact-message]');
      if (!contactMessage) {
        return;
      }

      detail = detail || {};
      var productLines = buildCartLinesForDevisMessage(detail.productLines);
      var message = detail.devisMessage || buildClientDevisMessage(productLines, devisCfg);

      contactMessage.value = message;
      sessionStorage.setItem('anrhpub_quote_summary', message);
      updateContactCartPreview(detail);
      updateContactLogoBlock(devisCfg);

      if (devisCfg.deliveryAddress) {
        var hidden = document.querySelector('[data-contact-delivery-hidden]');
        if (hidden) {
          hidden.value = devisCfg.deliveryAddress;
        }
      }
    }

    window.addEventListener('anrhpub:cart-updated', function (e) {
      refreshContactFromCart(e.detail || {});
    });

    window.addEventListener('storage', function (e) {
      if (e.key !== cfg.storageKey) {
        return;
      }
      var items = [];
      try {
        items = sanitizeItems(JSON.parse(e.newValue || '[]'));
      } catch (err) {
        items = [];
      }
      updateBadges(items);
      syncServer(items).then(function (res) {
        if (res.success && res.data) {
          applyServerCartData(items, res.data);
        }
      });
    });

    if (window.anrhpubContactDevis && window.anrhpubContactDevis.isDevis) {
      refreshContactFromCart({
        devisMessage: window.anrhpubContactDevis.serverMessage || '',
        productLines: []
      });
    }

    window.addEventListener('pageshow', function (e) {
      if (!e.persisted || !(window.anrhpubContactDevis && window.anrhpubContactDevis.isDevis)) {
        return;
      }
      var items = sanitizeItems(loadCart());
      syncServer(items).then(function (res) {
        if (res.success && res.data) {
          applyServerCartData(items, res.data);
        }
      });
    });

    document.querySelectorAll('[data-quote-contact-link]').forEach(function (link) {
      link.addEventListener('click', function () {
        var items = sanitizeItems(loadCart());
        syncServer(items).then(function (res) {
          if (res.success && res.data) {
            refreshContactFromCart({
              items: items,
              summary: res.data.summary,
              devisMessage: res.data.devis_message,
              productLines: res.data.product_lines
            });
          } else {
            refreshContactFromCart({ items: items, productLines: [] });
          }
        });
      });
    });
  }
