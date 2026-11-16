/* Chat box: bot hỏi đáp nhanh theo từ khóa + form nhắn cho quán (vào hộp thư admin).
   Toàn bộ đáp án FAQ nằm trong mảng QA bên dưới — sửa chữ trực tiếp tại đây. */
(function () {
  'use strict';
  var box = document.getElementById('chatbox');
  if (!box) return;

  var CSRF = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  var BASE = (document.body.getAttribute('data-base') || '').replace(/\/$/, '');

  var toggle = document.getElementById('cb-toggle');
  var panel  = document.getElementById('cb-panel');
  var closeB = document.getElementById('cb-close');
  var log    = document.getElementById('cb-log');
  var chips  = document.getElementById('cb-chips');
  var askF   = document.getElementById('cb-ask');
  var qInput = document.getElementById('cb-question');
  var form   = document.getElementById('cb-form');
  var note   = document.getElementById('cb-form-note');
  var greeted = false;

  /* ===== Ngân hàng câu hỏi — trả lời theo dữ kiện thật trên web ===== */
  var QA = [
    { keys: ['gio mo cua', 'may gio', 'mo cua', 'dong cua', 'gio ban', 'gio hoat dong'],
      a: 'Quán mở cửa Thứ 2 - Chủ nhật, 7:00 - 22:00 bạn nhé.' },
    { keys: ['voucher', 'ma giam', 'giam gia', 'khuyen mai', 'uu dai', 'coupon', 'sale'],
      a: 'Hiện quán chưa áp dụng mã voucher / giảm giá khi đặt hàng online. Ưu đãi tại quán (nếu có) sẽ được thông báo ở fanpage và ngay tại quầy. Cần hỏi kỹ hơn, bạn chuyển sang tab "Nhắn cho quán" nhé.' },
    { keys: ['ship', 'giao hang', 'phi giao', 'van chuyen', 'giao nhanh'],
      a: 'Phí giao hàng là 15.000đ cho mọi đơn đặt online bạn nhé.' },
    { keys: ['thanh toan', 'vietqr', 'chuyen khoan', 'tien mat', 'cod', 'tra tien'],
      a: 'Sau khi đặt hàng thành công, trang web sẽ hiện mã VietQR để bạn quét và thanh toán nhé.' },
    { keys: ['size', 'ly lon', 'coc lon'],
      a: 'Đồ uống có 3 size: S là giá gốc, M cộng thêm 5.000đ, L cộng thêm 10.000đ. Riêng các món bánh thì không chia size.' },
    { keys: ['menu', 'thuc don', 'mon gi', 'gia mon', 'bang gia', 'do uong', 'ca phe', 'tra', 'banh'],
      a: 'Quán có cà phê, trà và bánh. Bạn xem toàn bộ món kèm giá ở trang Thực đơn nhé.',
      link: { href: BASE + '/menu', text: 'Xem thực đơn' } },
    { keys: ['dat hang', 'cach mua', 'mua hang', 'order', 'dat mon'],
      a: 'Bạn vào Thực đơn, bấm vào món để chọn size và thêm vào giỏ, sau đó bấm Đặt hàng. Lưu ý: đặt hàng cần đăng nhập tài khoản nhé.' },
    { keys: ['lich su', 'don hang cua toi', 'theo doi don', 'trang thai don', 'don cua toi'],
      a: 'Bạn đăng nhập rồi vào mục Hồ sơ > Đơn hàng của tôi để xem lịch sử và trạng thái đơn nhé.',
      link: { href: BASE + '/ho-so/don-hang', text: 'Xem đơn của tôi' } },
    { keys: ['lien he', 'so dien thoai', 'hotline', 'dia chi', 'o dau', 'email'],
      a: 'Hotline quán: 0123 456 789, email hello@goccaphe90s.com. Hoặc bạn gửi tin trực tiếp ở tab "Nhắn cho quán", quán sẽ gọi lại.' },
    { keys: ['dang ky', 'dang nhap', 'tai khoan', 'quen mat khau', 'mat khau'],
      a: 'Bạn bấm Đăng nhập / Đăng ký ở góc trên trang web. Nếu quên mật khẩu, ở trang đăng nhập có mục "Quên mật khẩu" để đặt lại qua email.' }
  ];
  var CHIPS = ['Giờ mở cửa?', 'Có voucher không?', 'Phí giao hàng?', 'Thanh toán thế nào?', 'Xem thực đơn'];

  function norm(s) {
    return (s || '').toLowerCase().normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd').replace(/\s+/g, ' ').trim();
  }
  function answerFor(q) {
    var n = norm(q);
    for (var i = 0; i < QA.length; i++) {
      for (var k = 0; k < QA[i].keys.length; k++) {
        if (n.indexOf(QA[i].keys[k]) !== -1) return QA[i];
      }
    }
    return null;
  }

  function addMsg(text, who, link) {
    var row = document.createElement('div');
    row.className = 'cb-msg ' + who;
    var bubble = document.createElement('div');
    bubble.className = 'cb-bubble';
    bubble.textContent = text;
    if (link) {
      var a = document.createElement('a');
      a.href = link.href; a.textContent = link.text; a.className = 'cb-link';
      bubble.appendChild(document.createElement('br'));
      bubble.appendChild(a);
    }
    row.appendChild(bubble);
    log.appendChild(row);
    log.scrollTop = log.scrollHeight;
  }
  function botSay(text, link) { addMsg(text, 'bot', link); }

  function greet() {
    if (greeted) return; greeted = true;
    botSay('Chào bạn! Mình là trợ lý của Góc Cà Phê 90s. Bạn cứ hỏi về giờ mở cửa, phí ship, voucher, cách đặt hàng... Còn việc cần quán xử lý trực tiếp thì sang tab "Nhắn cho quán" nhé.');
    CHIPS.forEach(function (c) {
      var b = document.createElement('button');
      b.type = 'button'; b.className = 'cb-chip'; b.textContent = c;
      b.addEventListener('click', function () { ask(c); });
      chips.appendChild(b);
    });
  }

  function ask(q) {
    if (!q || !q.trim()) return;
    addMsg(q, 'me');
    var hit = answerFor(q);
    if (hit) {
      botSay(hit.a, hit.link);
    } else {
      botSay('Câu này mình chưa có sẵn đáp án. Bạn chuyển sang tab "Nhắn cho quán", để lại số điện thoại — quán sẽ gọi lại hỗ trợ bạn ngay.');
    }
  }

  /* ===== Mở / đóng + chuyển tab ===== */
  function switchTab(name) {
    box.querySelectorAll('.cb-tab').forEach(function (t) {
      var on = t.getAttribute('data-cb-tab') === name;
      t.classList.toggle('is-active', on);
      t.setAttribute('aria-selected', on ? 'true' : 'false');
    });
    box.querySelectorAll('.cb-page').forEach(function (p) {
      p.hidden = p.getAttribute('data-cb-page') !== name;
    });
  }
  box.querySelectorAll('.cb-tab').forEach(function (t) {
    t.addEventListener('click', function () { switchTab(t.getAttribute('data-cb-tab')); });
  });
  function setOpen(open) {
    panel.hidden = !open;
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (open) { greet(); qInput.focus(); }
  }
  toggle.addEventListener('click', function () { setOpen(panel.hidden); });
  closeB.addEventListener('click', function () { setOpen(false); });

  askF.addEventListener('submit', function (ev) {
    ev.preventDefault();
    var q = qInput.value; qInput.value = '';
    ask(q);
  });

  /* ===== Gửi tin cho quán ===== */
  form.addEventListener('submit', function (ev) {
    ev.preventDefault();
    var btn = form.querySelector('.cb-send');
    btn.disabled = true; btn.textContent = 'Đang gửi...';
    var body = new URLSearchParams(new FormData(form));
    fetch(form.getAttribute('data-url'), {
      method: 'POST', body: body, credentials: 'same-origin',
      headers: { 'X-CSRF-Token': CSRF, 'X-Requested-With': 'fetch', 'Accept': 'application/json' }
    }).then(function (r) { return r.json().then(function (j) { j.status = r.status; return j; }); })
      .then(function (j) {
        note.textContent = j.message || (j.ok ? 'Đã gửi!' : 'Gửi chưa thành công, bạn thử lại nhé.');
        note.className = 'cb-note ' + (j.ok ? 'ok' : 'err');
        if (j.ok) form.querySelector('textarea[name="content"]').value = '';
      })
      .catch(function () {
        note.textContent = 'Mất kết nối, bạn thử lại sau ít phút nhé.';
        note.className = 'cb-note err';
      })
      .finally(function () { btn.disabled = false; btn.textContent = 'Gửi tin nhắn'; });
  });
})();
