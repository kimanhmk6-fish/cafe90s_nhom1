/* Góc Cà Phê 90s — JavaScript/AJAX dùng chung */
(function () {
  'use strict';
  var BASE = document.body.getAttribute('data-base') || '';
  var CSRF = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var money = function (n) { return Number(n).toLocaleString('vi-VN') + 'đ'; };

  function post(url, data) {
    var body = new URLSearchParams(data);
    return fetch(url, {
      method: 'POST', body: body, credentials: 'same-origin',
      headers: { 'X-CSRF-Token': CSRF, 'X-Requested-With': 'fetch', 'Accept': 'application/json' }
    }).then(function (r) { return r.json().then(function (j) { j.status = r.status; return j; }); });
  }

  var toastEl = $('#toast'), toastTimer;
  function toast(msg, isError) {
    if (!toastEl) return;
    toastEl.textContent = msg;
    toastEl.className = 'toast show' + (isError ? ' error' : '');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { toastEl.className = 'toast'; }, 2800);
  }

  function setCartCount(n) { var c = $('#cart-count'); if (c) c.textContent = n; }

  /* ---------- Menu mobile ---------- */
  var navToggle = $('.nav-toggle'), siteNav = $('#site-nav');
  if (navToggle && siteNav) {
    navToggle.addEventListener('click', function () {
      var open = siteNav.classList.toggle('is-open');
      navToggle.setAttribute('aria-expanded', open);
    });
  }
  var sbToggle = $('[data-sidebar-toggle]'), sidebar = $('#sidebar');
  if (sbToggle && sidebar) {
    sbToggle.addEventListener('click', function () {
      var open = sidebar.classList.toggle('is-open');
      sbToggle.setAttribute('aria-expanded', open);
    });
  }

  /* ---------- Popup chọn size / số lượng ---------- */
  var modal = $('#product-modal');
  if (modal) {
    var cur = null, qty = 1, sizeId = null;
    var el = {
      img: $('#pm-image'), cat: $('#pm-category'), name: $('#pm-name'), desc: $('#pm-desc'),
      sizes: $('#pm-sizes'), qty: $('#pm-qty'), add: $('#pm-add'), note: $('#pm-note')
    };
    var unitPrice = function () {
      if (!cur.sizes.length) return cur.price;
      var s = cur.sizes.filter(function (x) { return x.id === sizeId; })[0];
      return s ? s.price : cur.sizes[0].price;
    };
    var refresh = function () {
      el.qty.textContent = qty;
      el.add.textContent = 'Thêm vào giỏ · ' + money(unitPrice() * qty);
    };
    var open = function (p) {
      cur = p; qty = 1;
      el.img.src = p.image; el.img.alt = p.name;
      el.cat.textContent = p.category; el.name.textContent = p.name; el.desc.textContent = p.description || '';
      el.sizes.innerHTML = '<legend class="sr-only">Chọn size</legend>';
      if (p.sizes.length) {
        sizeId = p.sizes[0].id;
        p.sizes.forEach(function (s, i) {
          var l = document.createElement('label');
          l.innerHTML = '<input type="radio" name="pm-size" value="' + s.id + '"' + (i === 0 ? ' checked' : '') + '><span>' + s.name + ' ' + money(s.price) + '</span>';
          l.firstChild.addEventListener('change', function () { sizeId = s.id; refresh(); });
          el.sizes.appendChild(l);
        });
        el.sizes.hidden = false; el.note.textContent = '';
      } else {
        sizeId = null; el.sizes.hidden = true; el.note.textContent = 'Bánh & ăn vặt: giá cố định, không chọn size.';
      }
      refresh();
      if (typeof modal.showModal === 'function') modal.showModal(); else modal.setAttribute('open', '');
    };
    $$('[data-product]').forEach(function (node) {
      var data = JSON.parse(node.getAttribute('data-product'));
      node.addEventListener('click', function (e) {
        if (node.classList.contains('product-card') && !e.target.closest('.card-open, [data-quick-add]')) return;
        open(data);
      });
    });
    $('#pm-minus').addEventListener('click', function () { if (qty > 1) { qty--; refresh(); } });
    $('#pm-plus').addEventListener('click', function () { if (qty < 20) { qty++; refresh(); } });
    $('[data-close]', modal).addEventListener('click', function () { modal.close(); });
    modal.addEventListener('click', function (e) { if (e.target === modal) modal.close(); });

    el.add.addEventListener('click', function () {
      el.add.disabled = true;
      post(BASE + '/gio-hang/them', { product_id: cur.id, size_id: sizeId || '', quantity: qty })
        .then(function (res) {
          if (res.status === 401 && res.login) { window.location.href = res.login; return; }
          if (res.ok) { setCartCount(res.count); modal.close(); toast(res.message); }
          else { el.note.textContent = res.message || 'Có lỗi xảy ra.'; toast(res.message || 'Có lỗi xảy ra.', true); }
        })
        .catch(function () { toast('Không kết nối được máy chủ.', true); })
        .then(function () { el.add.disabled = false; });
    });
  }

  /* ---------- Trang giỏ hàng ---------- */
  var cartRoot = $('#cart-root');
  if (cartRoot) {
    var applySummary = function (res) {
      if (!res.ok) { toast(res.message || 'Có lỗi xảy ra.', true); return; }
      if (res.empty) { window.location.reload(); return; }
      $$('.cart-item', cartRoot).forEach(function (li) {
        var line = res.lines[li.getAttribute('data-item')];
        if (!line) { li.remove(); return; }
        $('output', li).textContent = line.quantity;
        $('.line-total', li).textContent = line.total;
      });
      $('#cart-lines').textContent = $$('.cart-item', cartRoot).length;
      $('#sum-subtotal').textContent = res.subtotal;
      $('#sum-shipping').textContent = res.shipping;
      $('#sum-total').textContent = res.total;
      setCartCount(res.count);
    };
    cartRoot.addEventListener('click', function (e) {
      var li = e.target.closest('.cart-item'); if (!li) return;
      var id = li.getAttribute('data-item');
      var step = e.target.closest('[data-step]');
      if (step) {
        var next = parseInt($('output', li).textContent, 10) + parseInt(step.getAttribute('data-step'), 10);
        if (next < 1) return;
        post(cartRoot.getAttribute('data-update'), { item_id: id, quantity: next }).then(applySummary);
      } else if (e.target.closest('[data-remove]')) {
        post(cartRoot.getAttribute('data-remove'), { item_id: id }).then(applySummary);
      }
    });
  }

  /* ---------- Tiện ích chung ---------- */
  $$('[data-autosubmit]').forEach(function (s) { s.addEventListener('change', function () { s.form.submit(); }); });
  $$('[data-autosubmit-file]').forEach(function (i) { i.addEventListener('change', function () { if (i.files.length) i.form.submit(); }); });
  $$('form[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function (e) { if (!window.confirm(f.getAttribute('data-confirm'))) e.preventDefault(); });
  });
  $$('[data-pw]').forEach(function (b) {
    b.addEventListener('click', function () {
      var input = b.parentNode.querySelector('input');
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      b.textContent = show ? 'Ẩn' : 'Hiện';
      b.setAttribute('aria-label', show ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
    });
  });

  /* ---------- Form sản phẩm (admin) ---------- */
  var pf = $('#product-form');
  if (pf) {
    var hasSize = $('#has-size'), table = $('#size-table'), price = $('#base-price');
    var calc = function () {
      var base = parseInt((price.value || '').replace(/\D/g, ''), 10) || 0;
      table.hidden = !hasSize.checked;
      $$('output[data-size]', table).forEach(function (o) {
        o.textContent = base ? money(base + parseInt(o.getAttribute('data-size'), 10)) : '—';
      });
    };
    hasSize.addEventListener('change', calc); price.addEventListener('input', calc); calc();
    var file = $('#img-input'), prev = $('#img-preview');
    file.addEventListener('change', function () {
      if (file.files[0]) prev.src = URL.createObjectURL(file.files[0]);
    });
  }
})();
