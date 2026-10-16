(() => {
  'use strict';

  /* =========================================================
     CONFIG
     Set USE_SAMPLE_DATA to false to see the empty states
     (like the admin page). Later, replace the arrays in `state`
     with data fetched from your PHP/MySQL endpoints.
     ========================================================= */
  const USE_SAMPLE_DATA = true;

  const VIEWS = {
    home:      ['Home', 'See what other farmers are harvesting and selling'],
    listings:  ['Produce Listings', 'Post crops with photos, track stock and manage what you sell'],
    requests:  ['Buyer Requests', 'Pre-order offers from buyers waiting for your reply'],
    orders:    ['Orders & Contracts', 'Agreed prices, delivery, payments and receipts'],
    messages:  ['Messages', 'Talk with buyers about your crops'],
    reports:   ['Reports', 'Summary of your stock, volume and sales'],
    profile:   ['My Profile', 'Your farmer details shown to buyers'],
    settings:  ['Settings', 'Choose which alerts you see']
  };

  // Shown when a listing has no photo
  const PLACEHOLDER = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11 20A7 7 0 0 1 4 13c0-5 4-9 16-9 0 12-4 16-9 16z"/><path d="M4 21c2-5 5-8 10-10"/></svg>';


  /* ---------- Sample feed posts ---------- */
  function minutesAgo(n) { return new Date(Date.now() - n * 60000).toISOString(); }
  function samplePosts() {
    const cm = (id, author, text, mins) => ({ id, author, text, createdAt: minutesAgo(mins), mine: false });
    return [
      { id: 1, author: 'Maria Santos', place: 'Silang, Cavite', createdAt: minutesAgo(35), kind: 'harvest', art: 'tomato', photo: '', sale: null, mine: false, likes: 24, liked: false,
        text: "First picking of the season! The tomatoes from our upper field came out firm and deep red after last week's dry spell. Harvesting continues all week.",
        comments: [ cm(1, 'Lorna Mendoza', 'Beautiful color! Are these already pre-sold?', 22), cm(2, 'Maria Santos', 'About 120 kg still open for next week, Lorna.', 14), cm(3, 'Jun Reyes', 'Congrats Ate Maria, great yield!', 6) ] },
      { id: 2, author: 'Roberto Villanueva', place: 'Tagaytay City, Cavite', createdAt: minutesAgo(130), kind: 'sale', art: 'lettuce', photo: '',
        sale: { crop: 'Lettuce', price: 120, qty: 80, harvest: daysFromNow(3) }, mine: false, likes: 18, liked: false,
        text: "Pre-selling lettuce for next week's harvest. Soil grown, no chemical sprays. Pre-order now and I will lock in this price until delivery.",
        comments: [ cm(1, 'Elmer Capistrano', 'Do you deliver as far as Alfonso?', 80), cm(2, 'Roberto Villanueva', 'Yes, for orders of 20 kg and up.', 55) ] },
      { id: 3, author: 'Lorna Mendoza', place: 'Indang, Cavite', createdAt: minutesAgo(310), kind: 'harvest', art: 'ampalaya', photo: '', sale: null, mine: false, likes: 31, liked: false,
        text: "The rain pushed our ampalaya harvest back by two days, but the vines are heavier than last year. Is anyone else seeing the same in their fields?",
        comments: [ cm(1, 'Nenita Alcantara', 'Same here with the eggplant. Two days behind.', 240), cm(2, 'Maria Santos', 'Ours held up, but the lower rows are wet.', 200), cm(3, 'Jun Reyes', 'Staking the vines higher helped us last season.', 150) ] },
      { id: 4, author: 'Silang Vegetable Growers Coop', place: 'Silang, Cavite', createdAt: minutesAgo(1560), kind: 'sale', art: 'eggplant', photo: '',
        sale: { crop: 'Eggplant (long purple)', price: 42, qty: 600, harvest: daysFromNow(6) }, mine: false, likes: 40, liked: false,
        text: "Bulk pre-orders are open for restaurants and stores. We deliver within Cavite on harvest day.",
        comments: [ cm(1, 'Silang Vegetable Growers Coop', 'Message us for the delivery schedule.', 1400), cm(2, 'Lorna Mendoza', 'Good price. Shared with my buyers.', 1300) ] },
      { id: 5, author: 'Elmer Capistrano', place: 'Alfonso, Cavite', createdAt: minutesAgo(1900), kind: 'harvest', art: 'corn', photo: '', sale: null, mine: false, likes: 12, liked: false,
        text: "Sweet corn day. Picked at sunrise so it goes out the same morning.",
        comments: [ cm(1, 'Maria Santos', 'Save me a few ears!', 1700) ] },
      { id: 6, author: 'Nenita Alcantara', place: 'Amadeo, Cavite', createdAt: minutesAgo(2900), kind: 'sale', art: 'banana', photo: '',
        sale: { crop: 'Lakatan banana', price: 55, qty: 300, harvest: daysFromNow(9) }, mine: false, likes: 9, liked: false,
        text: "Lakatan bananas are ripening nicely. Better price for orders of 100 kg and up.",
        comments: [] },
      { id: 7, author: 'Jun Reyes', place: 'Mendez, Cavite', createdAt: minutesAgo(4400), kind: 'harvest', art: '', photo: '', sale: null, mine: false, likes: 15, liked: false,
        text: "Spent the weekend repairing our drip lines before the dry season. A small job, but it saves a lot of water.",
        comments: [ cm(1, 'Roberto Villanueva', 'Smart. Which pipe size did you use?', 4000) ] }
    ];
  }

  /* ---------- Data ---------- */
  const state = {
    listings: [],   // { id, crop, qty, price, harvest, location, status, photo }
    requests: [],
    // { id, date, buyer, crop, qty, price, total, listingId, delivery, deliveryDate, delivered, deliveredDate,
    //   method, paid, paidDate, reference, receiptNo, status }
    orders: [],
    threads: [],
    posts: [],      // feed posts (see samplePosts)
    submissions: [], // sale requests waiting for approval: { id, crop, qty, price, harvest, location, description, photo, status, submittedAt, reason }
    profile: { name: 'Farmer', photo: '', phone: '', address: '', crops: '', farmSize: '', years: '', about: '' }
  };

  if (USE_SAMPLE_DATA) {
    state.listings = [
      { id: 1, crop: 'Tomato',   qty: 400, price: 38,  harvest: daysFromNow(9),  location: 'Dasmariñas',  status: 'Open',     photo: '' },
      { id: 2, crop: 'Eggplant', qty: 250, price: 45,  harvest: daysFromNow(16), location: 'Silang',      status: 'Open',     photo: '' },
      { id: 3, crop: 'Ampalaya', qty: 120, price: 60,  harvest: daysFromNow(24), location: 'Indang',      status: 'Open',     photo: '' },
      { id: 4, crop: 'Okra',     qty: 180, price: 52,  harvest: daysFromNow(4),  location: 'Dasmariñas',  status: 'Pre-sold', photo: '' }
    ];
    state.requests = [
      { id: 1, buyer: 'Mercado Fresh Store', crop: 'Tomato',   qty: 150, offer: 40, needed: daysFromNow(12), status: 'Pending' },
      { id: 2, buyer: 'Aling Rosa Eatery',   crop: 'Eggplant', qty: 60,  offer: 44, needed: daysFromNow(18), status: 'Pending' },
      { id: 3, buyer: 'Kainan sa Bayan',     crop: 'Okra',     qty: 100, offer: 50, needed: daysFromNow(6),  status: 'Accepted' }
    ];
    state.orders = [
      { id: 101, date: daysFromNow(-3),  buyer: 'Kainan sa Bayan',     crop: 'Okra',   qty: 100, price: 50, total: 5000, listingId: 4,
        delivery: 'Farmer delivers', deliveryDate: daysFromNow(6), delivered: false, deliveredDate: '',
        method: 'Cash on delivery', paid: false, paidDate: '', reference: '', receiptNo: '' },
      { id: 100, date: daysFromNow(-20), buyer: 'Mercado Fresh Store', crop: 'Tomato', qty: 200, price: 38, total: 7600, listingId: 1,
        delivery: 'Buyer picks up', deliveryDate: daysFromNow(-14), delivered: true, deliveredDate: daysFromNow(-14),
        method: 'GCash', paid: true, paidDate: daysFromNow(-14), reference: 'GC-884213', receiptNo: '' }
    ];
    state.orders.forEach(o => { if (o.paid) o.receiptNo = receiptNoFor(o); syncOrderStatus(o); });
    state.posts = samplePosts();
    state.submissions = [
      { id: 1, crop: 'Pechay', qty: 90, price: 35, harvest: daysFromNow(7), location: 'Dasmariñas', description: 'Soil grown, no chemical sprays.', photo: '', status: 'Pending', submittedAt: daysFromNow(0), reason: '' }
    ];
    state.threads = [
      { id: 1, buyer: 'Mercado Fresh Store', unread: true, messages: [
        { from: 'them', text: 'Hi! Can you deliver 150 kg of tomatoes on the 12th?', time: '09:12' }
      ]},
      { id: 2, buyer: 'Aling Rosa Eatery', unread: false, messages: [
        { from: 'them', text: 'Is the eggplant organic?', time: 'Yesterday' },
        { from: 'me',   text: 'Yes, no chemical sprays used.', time: 'Yesterday' }
      ]}
    ];
    state.profile = {
      name: 'Juan Dela Cruz',
      photo: '',
      phone: '0917 123 4567',
      address: 'Brgy. Salawag, Dasmariñas, Cavite',
      crops: 'Tomato, Eggplant, Okra, Ampalaya',
      farmSize: '2.5',
      years: '12',
      about: 'I grow vegetables on my family farm and sell directly to local stores and eateries. I only plant what buyers have pre-ordered, so everything you receive is freshly harvested and delivered on the agreed date.'
    };
  }

  /* ---------- Helpers ---------- */
  const $  = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const peso = n => '₱' + Number(n).toLocaleString('en-PH', { maximumFractionDigits: 2 });
  const kg   = n => Number(n).toLocaleString('en-PH') + ' kg';

  function daysFromNow(d) {
    const dt = new Date();
    dt.setDate(dt.getDate() + d);
    return localISO(dt);
  }
  function localISO(dt) {
    const m = String(dt.getMonth() + 1).padStart(2, '0');
    const d = String(dt.getDate()).padStart(2, '0');
    return `${dt.getFullYear()}-${m}-${d}`;
  }
  function daysUntil(iso) {
    const today = new Date(); today.setHours(0, 0, 0, 0);
    return Math.round((new Date(iso + 'T00:00:00') - today) / 86400000);
  }
  function fmtDate(iso) {
    if (!iso) return '';
    const d = new Date(iso + 'T00:00:00');
    return d.toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
  }
  function esc(s) {
    return String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  }
  function emptyRow(cols, text) {
    return `<tr><td class="empty" colspan="${cols}">${esc(text)}</td></tr>`;
  }
  function statusPill(s) {
    const cls = s === 'Completed' ? 'done'
      : (s === 'Declined' || s === 'Rejected' || s === 'Sold out') ? 'no'
      : (s === 'Pending' || s === 'Pre-sold' || s === 'Low stock' || s === 'Delivered' || s === 'Unpaid') ? 'warn' : '';
    return `<span class="pill ${cls}">${esc(s)}</span>`;
  }
  let toastTimer;
  function toast(msg) {
    const t = $('#toast');
    t.textContent = msg;
    t.hidden = false;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => (t.hidden = true), 2600);
  }

  /* ---------- Inventory helpers ---------- */
  // Reserved = kg under contract but not yet delivered.
  // Sold = kg already delivered to the buyer. Available = what is left to offer.
  function listingStats(l) {
    const mine = state.orders.filter(o => o.listingId === l.id);
    const reserved = mine.filter(o => !o.delivered).reduce((s, o) => s + o.qty, 0);
    const sold = mine.filter(o => o.delivered).reduce((s, o) => s + o.qty, 0);
    return { reserved, sold, available: Math.max(0, l.qty - reserved - sold) };
  }
  function stockStatus(l) {
    const { available } = listingStats(l);
    if (available <= 0) return 'Sold out';
    if (available <= l.qty * 0.2) return 'Low stock';
    return l.status;
  }

  /* ---------- Photo helpers ---------- */
  // Resizes the picture in the browser so it stays light (max 900px, JPEG).
  function readImage(file, max = 900) {
    return new Promise((resolve, reject) => {
      if (!/^image\/(png|jpeg|webp)$/.test(file.type)) return reject(new Error('Please choose a JPG, PNG or WebP image.'));
      if (file.size > 8 * 1024 * 1024) return reject(new Error('That photo is larger than 8 MB.'));
      const reader = new FileReader();
      reader.onerror = () => reject(new Error('Could not read that photo.'));
      reader.onload = () => {
        const img = new Image();
        img.onerror = () => reject(new Error('Could not read that photo.'));
        img.onload = () => {
          const scale = Math.min(1, max / Math.max(img.width, img.height));
          const c = document.createElement('canvas');
          c.width = Math.round(img.width * scale);
          c.height = Math.round(img.height * scale);
          c.getContext('2d').drawImage(img, 0, 0, c.width, c.height);
          resolve(c.toDataURL('image/jpeg', 0.82));
        };
        img.src = reader.result;
      };
      reader.readAsDataURL(file);
    });
  }

  /* ---------- Navigation ---------- */
  function showView(name) {
    if (!VIEWS[name]) name = 'home';
    $$('.view').forEach(v => v.classList.toggle('active', v.id === 'view-' + name));
    $$('.nav-link').forEach(a => a.classList.toggle('active', a.dataset.view === name));
    $('#pageTitle').textContent = VIEWS[name][0];
    $('#pageSubtitle').textContent = VIEWS[name][1];
    document.title = 'FarmLinked Farmer | ' + VIEWS[name][0];
    closeProfileMenu();
    closeSidebar();
    window.scrollTo({ top: 0 });
  }

  document.addEventListener('click', e => {
    const link = e.target.closest('[data-view]');
    if (!link) return;
    e.preventDefault();
    const name = link.dataset.view;
    history.replaceState(null, '', '#' + name);
    showView(name);
  });
  window.addEventListener('hashchange', () => showView(location.hash.slice(1)));

  /* ---------- Profile menu ---------- */
  const profileBtn  = $('#profileBtn');
  const profileMenu = $('#profileMenu');

  function openProfileMenu() {
    profileMenu.hidden = false;
    profileBtn.setAttribute('aria-expanded', 'true');
  }
  function closeProfileMenu() {
    profileMenu.hidden = true;
    profileBtn.setAttribute('aria-expanded', 'false');
  }
  profileBtn.addEventListener('click', e => {
    e.stopPropagation();
    profileMenu.hidden ? openProfileMenu() : closeProfileMenu();
  });
  document.addEventListener('click', e => {
    if (!profileMenu.hidden && !e.target.closest('.sidebar-foot')) closeProfileMenu();
  });
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeProfileMenu(); closeSidebar(); }
  });
  $('#logoutLink').addEventListener('click', e => {
    if (!confirm('Log out of FarmLinked?')) e.preventDefault();
  });

  /* ---------- Mobile sidebar ---------- */
  const sidebar = $('#sidebar');
  const scrim = $('#scrim');
  function openSidebar()  { sidebar.classList.add('open'); scrim.hidden = false; }
  function closeSidebar() { sidebar.classList.remove('open'); scrim.hidden = true; }
  $('#menuToggle').addEventListener('click', openSidebar);
  scrim.addEventListener('click', closeSidebar);

  /* ---------- Renderers ---------- */
  function renderBadges() {
    const pending = state.requests.filter(r => r.status === 'Pending').length;
    const unread  = state.threads.filter(t => t.unread).length;
    const rb = $('#requestsBadge'), mb = $('#messagesBadge');
    rb.textContent = pending; rb.hidden = !pending || !$('#setAlerts').checked;
    mb.textContent = unread;  mb.hidden = !unread  || !$('#setMessages').checked;
  }

  function monthSales() {
    const prefix = daysFromNow(0).slice(0, 7);
    const rows = state.orders.filter(o => o.date.startsWith(prefix));
    return { total: rows.reduce((s, o) => s + o.total, 0), count: rows.length };
  }

  function renderSubmissions() {
    const el = $('#submissionList');
    const rows = [...state.submissions].sort((x, y) => y.id - x.id).slice(0, 5);
    el.classList.toggle('empty-note', !rows.length);
    el.innerHTML = rows.length
      ? rows.map(s => {
          const cls = s.status === 'Approved' ? 'approved' : s.status === 'Rejected' ? 'no' : 'warn';
          return `<li><span>${esc(s.crop)}<small>${kg(s.qty)} at ${peso(s.price)}/kg${s.status === 'Rejected' && s.reason ? ' · ' + esc(s.reason) : ''}</small></span><span class="pill ${cls}">${esc(s.status === 'Pending' ? 'Pending review' : s.status)}</span></li>`;
        }).join('')
      : '<li>Nothing submitted yet.</li>';
  }

  function thumb(l) {
    return l.photo
      ? `<img class="thumb" src="${esc(l.photo)}" alt="${esc(l.crop)}">`
      : `<span class="thumb" aria-hidden="true">${PLACEHOLDER}</span>`;
  }

  function listingCard(l) {
    const s = listingStats(l);
    const status = stockStatus(l);
    const d = daysUntil(l.harvest);
    const badge = d > 0
      ? `<span class="days" title="Expected harvest ${esc(fmtDate(l.harvest))}"><b>${d}</b>${d === 1 ? 'day' : 'days'}</span>`
      : d === 0
        ? `<span class="days" title="Expected harvest ${esc(fmtDate(l.harvest))}"><b>Today</b>harvest</span>`
        : `<span class="days" title="Expected harvest ${esc(fmtDate(l.harvest))}"><b>Ready</b>harvested</span>`;
    const pct = l.qty ? Math.round((s.available / l.qty) * 100) : 0;
    const fillCls = s.available <= 0 ? 'out' : s.available <= l.qty * 0.2 ? 'low' : '';
    return `
      <article class="l-card">
        <div class="l-photo">
          ${l.photo ? `<img src="${esc(l.photo)}" alt="${esc(l.crop)}">` : PLACEHOLDER}
          ${badge}
        </div>
        <div class="l-body">
          <div class="l-top">
            <div class="l-name">${esc(l.crop)}</div>
            ${statusPill(status)}
          </div>
          <div class="l-price">${peso(l.price)} <small>/ kg</small></div>
          <div class="l-meta">${esc(l.location)} · Harvest ${fmtDate(l.harvest)}</div>
          <div class="l-stock">
            <div class="l-stock-text"><span>${kg(s.available)} left</span><span>of ${kg(l.qty)}</span></div>
            <div class="stock-bar" role="img" aria-label="${pct}% of stock available"><div class="stock-fill ${fillCls}" style="width:${pct}%"></div></div>
          </div>
          <div class="l-actions">
            <button class="btn small ghost" data-change-photo="${l.id}">${l.photo ? 'Change photo' : 'Add photo'}</button>
            <button class="btn small danger" data-delete-listing="${l.id}">Remove</button>
          </div>
        </div>
      </article>`;
  }

  function renderListings() {
    const none = !state.listings.length;

    $('#listingCards').innerHTML = none
      ? '<p class="listing-empty">No produce listed yet. Select Add listing to post your first crop.</p>'
      : state.listings.map(listingCard).join('');

    $('#listingsBody').innerHTML = none
      ? emptyRow(9, 'No produce listed yet. Select Add listing to post your first crop.')
      : state.listings.map(l => {
          const s = listingStats(l);
          return `
          <tr>
            <td><div class="crop-cell">${thumb(l)}<strong>${esc(l.crop)}</strong></div></td>
            <td>${kg(l.qty)}</td>
            <td>${kg(s.available)}</td>
            <td>${kg(s.reserved)}</td>
            <td>${peso(l.price)}</td>
            <td>${fmtDate(l.harvest)}</td>
            <td>${esc(l.location)}</td>
            <td>${statusPill(stockStatus(l))}</td>
            <td class="actions">
              <button class="btn small ghost" data-change-photo="${l.id}">${l.photo ? 'Change photo' : 'Add photo'}</button>
              <button class="btn small danger" data-delete-listing="${l.id}">Remove</button>
            </td>
          </tr>`;
        }).join('');
  }

  let requestFilter = 'Pending';
  function renderRequests() {
    const rows = state.requests.filter(r => r.status === requestFilter);
    $('#requestsBody').innerHTML = rows.length
      ? rows.map(r => `
        <tr>
          <td><strong>${esc(r.buyer)}</strong></td>
          <td>${esc(r.crop)}</td>
          <td>${kg(r.qty)}</td>
          <td>${peso(r.offer)}</td>
          <td>${fmtDate(r.needed)}</td>
          <td class="actions">${r.status === 'Pending'
            ? `<button class="btn small" data-accept="${r.id}">Accept</button> <button class="btn small danger" data-decline="${r.id}">Decline</button>`
            : statusPill(r.status)}</td>
        </tr>`).join('')
      : emptyRow(6, `No ${requestFilter.toLowerCase()} requests.`);
  }

  function orderActions(o) {
    const b = [];
    if (!o.delivered) b.push(`<button class="btn small ghost" data-deliver="${o.id}">Mark delivered</button>`);
    if (!o.paid) b.push(`<button class="btn small" data-pay="${o.id}">Record payment</button>`);
    else b.push(`<button class="btn small ghost" data-receipt="${o.id}">Receipt</button>`);
    return b.join('');
  }

  function renderOrders() {
    const total = state.orders.reduce((s, o) => s + o.total, 0);
    const paidOrders = state.orders.filter(o => o.paid);
    const received = paidOrders.reduce((s, o) => s + o.total, 0);
    const unpaidCount = state.orders.length - paidOrders.length;
    const plural = (n, w) => n + ' ' + w + (n === 1 ? '' : 's');
    $('#ordTotal').textContent = peso(total);
    $('#ordTotalNote').textContent = plural(state.orders.length, 'order');
    $('#ordReceived').textContent = peso(received);
    $('#ordReceivedNote').textContent = plural(paidOrders.length, 'paid order');
    $('#ordDue').textContent = peso(total - received);
    $('#ordDueNote').textContent = plural(unpaidCount, 'unpaid order');

    $('#ordersBody').innerHTML = state.orders.length
      ? state.orders.map(o => `
        <tr>
          <td><strong>#${o.id}</strong><small class="sub">${fmtDate(o.date)}</small></td>
          <td>${esc(o.buyer)}</td>
          <td><strong>${esc(o.crop)}</strong><small class="sub">${kg(o.qty)} × ${peso(o.price)}</small></td>
          <td><strong>${peso(o.total)}</strong></td>
          <td>${o.delivered ? 'Delivered ' + fmtDate(o.deliveredDate) : 'Due ' + fmtDate(o.deliveryDate)}<small class="sub">${esc(o.delivery)}</small></td>
          <td>${statusPill(o.paid ? 'Paid' : 'Unpaid')}<small class="sub">${esc(o.method)}${o.paid ? ' · ' + fmtDate(o.paidDate) : ''}</small></td>
          <td>${statusPill(o.status)}</td>
          <td class="actions"><div class="stack">${orderActions(o)}</div></td>
        </tr>`).join('')
      : emptyRow(8, 'No orders or contracts yet. Accepted buyer requests appear here.');
  }

  let activeThread = null;
  function renderThreads() {
    const list = $('#chatList');
    if (!state.threads.length) {
      list.innerHTML = '<p class="chat-empty" style="padding:20px">No conversations yet.</p>';
      $('#chatBody').innerHTML = '<p class="chat-empty">Messages from buyers will show up here.</p>';
      return;
    }
    list.innerHTML = state.threads.map(t => {
      const last = t.messages[t.messages.length - 1];
      return `<button class="chat-item ${t.id === activeThread ? 'active' : ''}" data-thread="${t.id}">
        <strong>${esc(t.buyer)} ${t.unread ? '<i class="dot" aria-label="Unread"></i>' : ''}</strong>
        <span>${last ? esc(last.text) : ''}</span>
      </button>`;
    }).join('');
    renderChat();
  }

  function renderChat() {
    const t = state.threads.find(x => x.id === activeThread);
    const input = $('#chatInput'), send = $('#chatForm button');
    input.disabled = send.disabled = !t;
    if (!t) {
      $('#chatHead').textContent = 'Select a conversation';
      $('#chatBody').innerHTML = '<p class="chat-empty">Choose a buyer on the left to read the conversation.</p>';
      return;
    }
    $('#chatHead').textContent = t.buyer;
    const body = $('#chatBody');
    body.innerHTML = t.messages.map(m => `<div class="msg ${m.from === 'me' ? 'me' : ''}">${esc(m.text)}<time>${esc(m.time)}</time></div>`).join('');
    body.scrollTop = body.scrollHeight;
  }

  function barRows(entries, fmt) {
    const max = entries.length ? entries[0][1] : 1;
    return entries.map(([label, v]) => `
      <div class="bar-row">
        <span>${esc(label)}</span>
        <div class="bar-track"><div class="bar-fill" style="width:${max ? (v / max) * 100 : 0}%"></div></div>
        <span class="bar-val">${fmt(v)}</span>
      </div>`).join('');
  }

  function renderReports() {
    const volume = state.listings.reduce((s, l) => s + l.qty, 0);
    const available = state.listings.reduce((s, l) => s + listingStats(l).available, 0);
    const presold = state.orders.filter(o => !o.delivered).reduce((s, o) => s + o.qty, 0);
    const revenue = state.orders.reduce((s, o) => s + o.total, 0);
    $('#repVolume').textContent = volume.toLocaleString('en-PH');
    $('#repAvailable').textContent = available.toLocaleString('en-PH');
    $('#repPresold').textContent = presold.toLocaleString('en-PH');
    $('#repMonth').textContent = peso(monthSales().total);
    $('#repRevenue').textContent = peso(revenue);
    $('#repCompleted').textContent = state.orders.filter(o => o.status === 'Completed').length;

    const byCrop = {};
    state.listings.forEach(l => (byCrop[l.crop] = (byCrop[l.crop] || 0) + l.qty));
    const volEntries = Object.entries(byCrop).sort((a, b) => b[1] - a[1]);
    $('#repBars').innerHTML = volEntries.length
      ? barRows(volEntries, kg)
      : '<p style="color:var(--muted);margin:0">No data yet. Add produce listings to see your volume by crop.</p>';

    const salesByCrop = {};
    state.orders.forEach(o => (salesByCrop[o.crop] = (salesByCrop[o.crop] || 0) + o.total));
    const saleEntries = Object.entries(salesByCrop).sort((a, b) => b[1] - a[1]);
    $('#repSalesBars').innerHTML = saleEntries.length
      ? barRows(saleEntries, peso)
      : '<p style="color:var(--muted);margin:0">No sales yet. Accepted buyer requests will show here.</p>';
  }

  function renderAll() {
    renderBadges();
    renderSubmissions();
    renderComposer();
    renderFeed();
    renderListings();
    renderRequests();
    renderOrders();
    renderThreads();
    renderReports();
    renderProfile();
  }

  /* ---------- Actions ---------- */
  // Produce listings: every new product goes through the Sell form and needs approval
  $('#toggleListingForm').addEventListener('click', () => openSellModal());

  // Card / table layout switch
  function setLayout(layout) {
    $('#listingCards').hidden = layout !== 'cards';
    $('#listingTable').hidden = layout !== 'table';
    $$('#layoutTabs .tab').forEach(t => t.classList.toggle('active', t.dataset.layout === layout));
  }
  $('#layoutTabs').addEventListener('click', e => {
    const tab = e.target.closest('.tab');
    if (tab) setLayout(tab.dataset.layout);
  });

  // Add / change the photo of an existing listing
  let photoTargetId = null;
  const photoReplace = $('#photoReplace');
  photoReplace.addEventListener('change', async () => {
    const file = photoReplace.files[0];
    const item = state.listings.find(l => l.id === photoTargetId);
    photoReplace.value = '';
    if (!file || !item) return;
    try {
      item.photo = await readImage(file);
      renderAll();
      toast('Photo updated');
      // TODO: upload the new photo to your PHP endpoint
    } catch (err) {
      toast(err.message);
    }
  });

  document.addEventListener('click', e => {
    const chg = e.target.closest('[data-change-photo]');
    if (chg) {
      photoTargetId = Number(chg.dataset.changePhoto);
      photoReplace.click();
      return;
    }

    const del = e.target.closest('[data-delete-listing]');
    if (del) {
      const id = Number(del.dataset.deleteListing);
      const item = state.listings.find(l => l.id === id);
      if (item && confirm(`Remove your ${item.crop} listing?`)) {
        state.listings = state.listings.filter(l => l.id !== id);
        renderAll();
        toast('Listing removed');
      }
      return;
    }

    const acc = e.target.closest('[data-accept]');
    if (acc) {
      openAcceptModal(Number(acc.dataset.accept));
      return;
    }

    const dlv = e.target.closest('[data-deliver]');
    if (dlv) { markDelivered(Number(dlv.dataset.deliver)); return; }

    const pay = e.target.closest('[data-pay]');
    if (pay) { openPayModal(Number(pay.dataset.pay)); return; }

    const rcpt = e.target.closest('[data-receipt]');
    if (rcpt) { openReceipt(Number(rcpt.dataset.receipt)); return; }

    const dec = e.target.closest('[data-decline]');
    if (dec) {
      const r = state.requests.find(x => x.id === Number(dec.dataset.decline));
      if (r) { r.status = 'Declined'; renderAll(); toast('Request declined'); }
      return;
    }

    const thread = e.target.closest('[data-thread]');
    if (thread) {
      activeThread = Number(thread.dataset.thread);
      const t = state.threads.find(x => x.id === activeThread);
      if (t) t.unread = false;
      renderAll();
      $('#chatInput').focus();
    }
  });

  // Request filter tabs
  $('#requestTabs').addEventListener('click', e => {
    const tab = e.target.closest('.tab');
    if (!tab) return;
    requestFilter = tab.dataset.filter;
    $$('#requestTabs .tab').forEach(t => t.classList.toggle('active', t === tab));
    renderRequests();
  });

  // Chat send
  $('#chatForm').addEventListener('submit', e => {
    e.preventDefault();
    const input = $('#chatInput');
    const text = input.value.trim();
    const t = state.threads.find(x => x.id === activeThread);
    if (!text || !t) return;
    t.messages.push({ from: 'me', text, time: new Date().toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' }) });
    input.value = '';
    renderThreads();
    // TODO: POST to your messages endpoint
  });

  /* ---------- Pop-up (modal) ---------- */
  const modal = $('#modal');
  const modalBody = $('#modalBody');
  let modalReturnFocus = null;

  function openModal(html, extraClass = '') {
    modalReturnFocus = document.activeElement;
    $('.modal', modal).className = 'modal ' + extraClass;
    modalBody.innerHTML = html;
    modal.hidden = false;
    document.body.classList.add('modal-open');
    const first = modalBody.querySelector('select, input:not([type="hidden"]), textarea') || modalBody.querySelector('button');
    if (first) first.focus();
  }
  function closeModal() {
    modal.hidden = true;
    modalBody.innerHTML = '';
    document.body.classList.remove('modal-open');
    if (modalReturnFocus && document.contains(modalReturnFocus)) modalReturnFocus.focus();
    modalReturnFocus = null;
  }
  modal.addEventListener('click', e => {
    if (e.target === modal || e.target.closest('[data-close]')) closeModal();
    else if (e.target.closest('[data-print]')) window.print();
  });
  document.addEventListener('keydown', e => {
    if (modal.hidden) return;
    if (e.key === 'Escape') { closeModal(); return; }
    if (e.key === 'Tab') {   // keep keyboard focus inside the pop-up
      const items = $$('button, select, input, textarea', $('.modal', modal)).filter(el => !el.disabled);
      if (!items.length) return;
      const first = items[0], last = items[items.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    }
  });
  modal.addEventListener('submit', e => {
    e.preventDefault();
    const form = e.target;
    const f = new FormData(form);
    if (form.id === 'acceptForm') createOrder(Number(form.dataset.request), String(f.get('method')), String(f.get('delivery')));
    else if (form.id === 'payForm') recordPayment(Number(form.dataset.order), f);
    else if (form.id === 'sellForm') submitSale(f);
  });

  /* ---------- Sell: request approval before a product goes on sale ---------- */
  const MAX_PENDING = 3;
  let sellPhoto = '';

  function openSellModal() {
    const pending = state.submissions.filter(s => s.status === 'Pending').length;
    if (pending >= MAX_PENDING) {
      toast(`You already have ${MAX_PENDING} requests waiting for review. Please wait for approval first.`);
      return;
    }
    sellPhoto = '';
    openModal(`
      <h2 id="modalTitle">Sell a product</h2>
      <p class="modal-sub sell-intro">Tell us about your produce. Our team will review it first. Once approved, it appears in your Produce Listings and buyers can see it.</p>
      <form id="sellForm">
        <div class="modal-fields">
          <label>Crop
            <input type="text" name="crop" required maxlength="60" placeholder="e.g. Tomato">
          </label>
          <label>Quantity (kg)
            <input type="number" name="qty" min="1" max="100000" step="1" required>
          </label>
          <label>Price per kg (₱)
            <input type="number" name="price" min="1" max="10000" step="0.01" required>
          </label>
          <label>Expected harvest
            <input type="date" name="harvest" min="${daysFromNow(0)}" required>
          </label>
          <label class="wide">Location
            <input type="text" name="location" required maxlength="100" placeholder="Barangay / town">
          </label>
          <label class="wide">Describe your produce
            <textarea name="description" rows="3" required minlength="15" maxlength="300" placeholder="How it was grown, its quality and how you will deliver it."></textarea>
          </label>
          <div class="photo-field wide">
            <span class="field-label">Photo of the product <small>(required)</small></span>
            <div class="photo-row">
              <div class="photo-preview" id="sellPhotoPreview" aria-live="polite">${PLACEHOLDER}</div>
              <div class="photo-controls">
                <label class="btn ghost small file-btn">Choose photo
                  <input type="file" id="sellPhotoInput" accept="image/png,image/jpeg,image/webp" class="sr-input">
                </label>
                <p class="hint" id="sellPhotoNote">Use a real photo of your own produce. JPG, PNG or WebP.</p>
              </div>
            </div>
          </div>
        </div>
        <label class="check"><input type="checkbox" name="confirm" required> I confirm this product is real, I own it, and the details above are true.</label>
        <div class="modal-actions">
          <button type="button" class="btn ghost" data-close>Cancel</button>
          <button type="submit" class="btn">Submit for approval</button>
        </div>
      </form>`);
  }

  modal.addEventListener('change', async e => {
    if (e.target.id !== 'sellPhotoInput') return;
    const file = e.target.files[0];
    const note = $('#sellPhotoNote', modal), prev = $('#sellPhotoPreview', modal);
    if (!file) return;
    try {
      sellPhoto = await readImage(file);
      prev.innerHTML = `<img src="${sellPhoto}" alt="Preview of your product photo">`;
      prev.classList.add('has-photo');
      note.textContent = file.name;
      note.classList.remove('error');
    } catch (err) {
      sellPhoto = '';
      prev.innerHTML = PLACEHOLDER;
      prev.classList.remove('has-photo');
      note.textContent = err.message;
      note.classList.add('error');
    }
  });

  function submitSale(f) {
    if (!sellPhoto) {
      const note = $('#sellPhotoNote', modal);
      note.textContent = 'Please add a photo of your product before submitting.';
      note.classList.add('error');
      return;
    }
    const sub = {
      id: Date.now(),
      crop: String(f.get('crop')).trim(),
      qty: Number(f.get('qty')),
      price: Number(f.get('price')),
      harvest: String(f.get('harvest')),
      location: String(f.get('location')).trim(),
      description: String(f.get('description')).trim(),
      photo: sellPhoto,
      status: 'Pending',
      submittedAt: daysFromNow(0),
      reason: ''
    };
    state.submissions.unshift(sub);
    sellPhoto = '';
    closeModal();
    renderAll();
    toast('Submitted. We will review your product before it goes on sale.');
    // TODO: POST to your PHP endpoint, e.g. fetch('submit_sale.php', { method: 'POST', body: formData })
    // Save it with status = 'Pending' so an admin can approve or reject it.
  }

  // Called when the admin decides. Replace with data loaded from your server.
  function reviewSubmission(id, approve, reason) {
    const s = state.submissions.find(x => x.id === id);
    if (!s || s.status !== 'Pending') return;
    if (approve) {
      s.status = 'Approved';
      state.listings.unshift({ id: s.id, crop: s.crop, qty: s.qty, price: s.price, harvest: s.harvest, location: s.location, status: 'Open', photo: s.photo });
    } else {
      s.status = 'Rejected';
      s.reason = reason || '';
    }
    renderAll();
  }
  // Demo helper for testing in the browser console: FarmLinkedDemo.approve(id) / .reject(id, 'reason')
  window.FarmLinkedDemo = { approve: id => reviewSubmission(id, true), reject: (id, why) => reviewSubmission(id, false, why) };

  $('#sellBtn').addEventListener('click', openSellModal);

  /* ---------- Orders: accept, deliver, pay, receipt ---------- */
  const PAY_METHODS = ['Cash on delivery', 'GCash', 'Bank transfer'];
  const DELIVERY_TYPES = ['Farmer delivers', 'Buyer picks up'];
  const optionList = (list, selected) => list.map(v => `<option${v === selected ? ' selected' : ''}>${esc(v)}</option>`).join('');

  function syncOrderStatus(o) {
    o.status = o.delivered && o.paid ? 'Completed' : o.delivered ? 'Delivered' : 'Contract signed';
  }
  function receiptNoFor(o) {
    return 'R-' + (o.paidDate || daysFromNow(0)).slice(0, 4) + '-' + String(o.id).padStart(4, '0');
  }
  function bestListing(crop) {
    return state.listings
      .filter(l => l.crop.toLowerCase() === crop.toLowerCase())
      .sort((a, b) => listingStats(b).available - listingStats(a).available)[0] || null;
  }

  // 1) Accept a buyer request: review the deal first, then create the order
  function openAcceptModal(requestId) {
    const r = state.requests.find(x => x.id === requestId);
    if (!r || r.status !== 'Pending') return;
    const listing = bestListing(r.crop);
    const left = listing ? listingStats(listing).available : 0;
    const short = !!listing && r.qty > left;
    const stock = !listing
      ? `<p class="note warn">You have no ${esc(r.crop)} listing, so this order will not be linked to any stock.</p>`
      : short
        ? `<p class="note bad">Only ${kg(left)} is available in your ${esc(listing.crop)} listing, but the buyer wants ${kg(r.qty)}.</p>`
        : `<p class="note ok">${kg(left)} available in your ${esc(listing.crop)} listing. ${kg(left - r.qty)} will remain after this order.</p>`;

    openModal(`
      <h2 id="modalTitle">Accept this request?</h2>
      <p class="modal-sub">Review the deal. Once accepted, the price and quantity are locked in an order.</p>
      <dl class="summary">
        <div><dt>Buyer</dt><dd>${esc(r.buyer)}</dd></div>
        <div><dt>Crop</dt><dd>${esc(r.crop)}</dd></div>
        <div><dt>Quantity</dt><dd>${kg(r.qty)}</dd></div>
        <div><dt>Price per kg</dt><dd>${peso(r.offer)}</dd></div>
        <div><dt>Needed by</dt><dd>${fmtDate(r.needed)}</dd></div>
        <div class="total"><dt>Total amount</dt><dd>${peso(r.qty * r.offer)}</dd></div>
      </dl>
      ${stock}
      <form id="acceptForm" data-request="${r.id}">
        <div class="modal-fields">
          <label>Payment method
            <select name="method">${optionList(PAY_METHODS)}</select>
          </label>
          <label>Delivery
            <select name="delivery">${optionList(DELIVERY_TYPES)}</select>
          </label>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn ghost" data-close>Cancel</button>
          <button type="submit" class="btn">${short ? 'Accept anyway' : 'Accept and create order'}</button>
        </div>
      </form>`);
  }

  function createOrder(requestId, method, delivery) {
    const r = state.requests.find(x => x.id === requestId);
    if (!r || r.status !== 'Pending') { closeModal(); return; }
    const listing = bestListing(r.crop);   // links the order to the listing with the most stock left
    r.status = 'Accepted';
    const o = {
      id: Math.max(100, ...state.orders.map(x => x.id)) + 1,
      date: daysFromNow(0),
      buyer: r.buyer, crop: r.crop, qty: r.qty, price: r.offer, total: r.qty * r.offer,
      listingId: listing ? listing.id : null,
      delivery, deliveryDate: r.needed, delivered: false, deliveredDate: '',
      method, paid: false, paidDate: '', reference: '', receiptNo: ''
    };
    syncOrderStatus(o);
    state.orders.unshift(o);
    closeModal();
    renderAll();
    toast(`Order #${o.id} created. See Orders & Contracts.`);
    // TODO: POST the new order to your PHP endpoint
  }

  // 2) Delivery
  function markDelivered(orderId) {
    const o = state.orders.find(x => x.id === orderId);
    if (!o || o.delivered) return;
    if (!confirm(`Mark order #${o.id} (${kg(o.qty)} of ${o.crop}) as delivered to ${o.buyer}?`)) return;
    o.delivered = true;
    o.deliveredDate = daysFromNow(0);
    syncOrderStatus(o);
    renderAll();
    toast(o.paid ? `Order #${o.id} completed` : `Order #${o.id} delivered. Waiting for payment.`);
    // TODO: POST the delivery update to your PHP endpoint
  }

  // 3) Payment received by the farmer
  function openPayModal(orderId) {
    const o = state.orders.find(x => x.id === orderId);
    if (!o || o.paid) return;
    openModal(`
      <h2 id="modalTitle">Record payment</h2>
      <p class="modal-sub">Enter the payment you received. A receipt is created right after.</p>
      <dl class="summary">
        <div><dt>Order</dt><dd>#${o.id} · ${esc(o.crop)}, ${kg(o.qty)}</dd></div>
        <div><dt>Buyer</dt><dd>${esc(o.buyer)}</dd></div>
        <div class="total"><dt>Amount due</dt><dd>${peso(o.total)}</dd></div>
      </dl>
      <form id="payForm" data-order="${o.id}">
        <div class="modal-fields">
          <label>Payment method
            <select name="method">${optionList(PAY_METHODS, o.method)}</select>
          </label>
          <label>Date received
            <input type="date" name="date" value="${daysFromNow(0)}" max="${daysFromNow(0)}" required>
          </label>
          <label class="wide">Reference number <small class="label-hint">Required for GCash and bank transfer</small>
            <input type="text" name="reference" maxlength="40" placeholder="e.g. GCash ref. no.">
          </label>
        </div>
        <label class="check"><input type="checkbox" name="confirm" required> I received ${peso(o.total)} from ${esc(o.buyer)}.</label>
        <div class="modal-actions">
          <button type="button" class="btn ghost" data-close>Cancel</button>
          <button type="submit" class="btn">Record payment</button>
        </div>
      </form>`);
  }

  function recordPayment(orderId, f) {
    const o = state.orders.find(x => x.id === orderId);
    if (!o || o.paid) { closeModal(); return; }
    const method = String(f.get('method'));
    const reference = String(f.get('reference') || '').trim();
    const date = String(f.get('date') || '');
    if (!date) { toast('Please choose the date you received the payment'); return; }
    if (method !== 'Cash on delivery' && !reference) {
      toast('Please enter the payment reference number');
      $('input[name="reference"]', modal).focus();
      return;
    }
    o.paid = true;
    o.method = method;
    o.reference = reference;
    o.paidDate = date;
    o.receiptNo = receiptNoFor(o);
    syncOrderStatus(o);
    closeModal();
    renderAll();
    toast('Payment recorded');
    openReceipt(o.id);
    // TODO: POST the payment to your PHP endpoint
  }

  // 4) Receipt (print it or save it as a PDF from the print window)
  function openReceipt(orderId) {
    const o = state.orders.find(x => x.id === orderId);
    if (!o || !o.paid) return;
    const p = state.profile;
    const farmer = p.name.trim() || 'Farmer';
    openModal(`
      <div class="receipt">
        <div class="receipt-head">
          <div class="receipt-brand"><span aria-hidden="true">🌱</span><div>FarmLinked<small>Payment receipt</small></div></div>
          <div class="receipt-no"><b id="modalTitle">${esc(o.receiptNo)}</b>Date paid: ${fmtDate(o.paidDate)}</div>
        </div>

        <div class="receipt-parties">
          <div>
            <h4>Received by (farmer)</h4>
            <p><strong>${esc(farmer)}</strong><br>${esc(p.address || 'Address not set')}${p.phone ? '<br>' + esc(p.phone) : ''}</p>
          </div>
          <div>
            <h4>Paid by (buyer)</h4>
            <p><strong>${esc(o.buyer)}</strong></p>
          </div>
        </div>

        <table>
          <thead><tr><th>Item</th><th class="num">Quantity</th><th class="num">Price / kg</th><th class="num">Amount</th></tr></thead>
          <tbody><tr>
            <td>${esc(o.crop)}<small class="sub">Order #${o.id}</small></td>
            <td class="num">${kg(o.qty)}</td>
            <td class="num">${peso(o.price)}</td>
            <td class="num">${peso(o.total)}</td>
          </tr></tbody>
        </table>
        <div class="receipt-total"><span>Total paid</span><span>${peso(o.total)}</span></div>

        <div class="receipt-meta">
          <div><h4>Payment method</h4><p>${esc(o.method)}</p></div>
          <div><h4>Reference number</h4><p>${o.reference ? esc(o.reference) : 'None (cash)'}</p></div>
          <div><h4>Delivery</h4><p>${o.delivered ? 'Delivered ' + fmtDate(o.deliveredDate) : 'Pending (' + fmtDate(o.deliveryDate) + ')'} · ${esc(o.delivery)}</p></div>
          <div><h4>Status</h4><p><span class="paid-stamp">PAID</span></p></div>
        </div>
        <p class="receipt-foot">Payment record generated by FarmLinked. This is not a BIR-registered official receipt.</p>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn ghost" data-close>Close</button>
        <button type="button" class="btn" data-print>Print / Save as PDF</button>
      </div>`, 'receipt-modal');
  }

  /* ---------- Home feed ---------- */
  const CROP_ART = {
    tomato:   ['🍅', '#fde2dc', '#f6b9ab'], eggplant: ['🍆', '#ece3f7', '#c9b2e6'], ampalaya: ['🥒', '#e3f1d5', '#b6da8f'],
    cucumber: ['🥒', '#e3f1d5', '#b6da8f'], okra:     ['🌿', '#e8f1d3', '#c5dd96'], corn:     ['🌽', '#fdf1c8', '#f4d77a'],
    mango:    ['🥭', '#fdebc4', '#f6c46b'], banana:   ['🍌', '#fdf3c4', '#f2dc72'], lettuce:  ['🥬', '#e2f3d6', '#b4dd9c'],
    cabbage:  ['🥬', '#e2f3d6', '#b4dd9c'], pechay:   ['🥬', '#e2f3d6', '#b4dd9c'], kangkong: ['🥬', '#e2f3d6', '#b4dd9c'],
    carrot:   ['🥕', '#fde5cf', '#f6bf8d'], pepper:   ['🌶️', '#fddcd6', '#f3a597'], chili:    ['🌶️', '#fddcd6', '#f3a597'],
    potato:   ['🥔', '#f3e6d4', '#d9b98a'], onion:    ['🧅', '#f5e6dc', '#e0b9a0'], garlic:   ['🧄', '#f4efe6', '#d9cdb6'],
    pineapple:['🍍', '#fdf0c0', '#f1d36a'], watermelon:['🍉', '#fde0dc', '#f2a69d']
  };
  const CROP_ART_DEFAULT = ['🌱', '#eaf1e0', '#cfe1b6'];
  const AVATAR_TONES = [['#dcebc8', '#2a4d08'], ['#f6e3b4', '#6b4a05'], ['#d4e8e4', '#14514a'], ['#f3d9cf', '#7a3320'], ['#e3dcf0', '#43306b']];
  const POST_MAX = 500;

  const composer = { type: 'harvest', photo: '' };
  let feedFilter = 'all';
  let feedQuery = '';
  const openComments = new Set();

  function cropArt(crop) {
    const k = String(crop || '').toLowerCase();
    const hit = Object.keys(CROP_ART).find(n => k.includes(n));
    return hit ? CROP_ART[hit] : CROP_ART_DEFAULT;
  }
  function toneFor(name) {
    let h = 0;
    for (const ch of String(name)) h = (h * 31 + ch.charCodeAt(0)) >>> 0;
    return AVATAR_TONES[h % AVATAR_TONES.length];
  }
  function timeAgo(iso) {
    const mins = Math.max(0, Math.round((Date.now() - new Date(iso).getTime()) / 60000));
    if (mins < 1) return 'Just now';
    if (mins < 60) return mins + ' min ago';
    const hrs = Math.floor(mins / 60);
    if (hrs < 24) return hrs + (hrs === 1 ? ' hr ago' : ' hrs ago');
    const days = Math.floor(hrs / 24);
    if (days === 1) return 'Yesterday';
    if (days < 7) return days + ' days ago';
    return fmtDate(localISO(new Date(iso)));
  }
  const myName = () => state.profile.name.trim() || 'Farmer';
  const myPlace = () => state.profile.address.split(',').map(x => x.trim()).filter(Boolean).slice(-2).join(', ') || 'Your farm';
  const postAuthor = p => (p.mine ? myName() : p.author);
  const postPlace = p => (p.mine ? myPlace() : p.place);
  const findPost = id => state.posts.find(x => String(x.id) === String(id));

  function avatarHtml(mine, name, size) {
    if (mine) return `<span class="avatar ${size}">${photoOrInitial(state.profile, '')}</span>`;
    const [bg, fg] = toneFor(name);
    return `<span class="avatar ${size}" style="background:${bg};color:${fg}">${esc(initialOf(name))}</span>`;
  }

  const ICON_LIKE = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 11v9H4v-9z"/><path d="M7 11l4-7c1.5 0 2.5 1 2.5 2.5V10h5a2 2 0 0 1 2 2.3l-1.2 6A2 2 0 0 1 17.3 20H7"/></svg>';
  const ICON_COMMENT = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>';

  function mediaHtml(p) {
    const name = postAuthor(p);
    if (p.photo) return `<div class="post-media"><img src="${esc(p.photo)}" alt="Photo posted by ${esc(name)}"></div>`;
    if (p.art) {
      const [emoji, from, to] = cropArt(p.art);
      return `<div class="post-media art" style="background:linear-gradient(135deg,${from},${to})" role="img" aria-label="${esc(p.art)} illustration"><span aria-hidden="true">${emoji}</span></div>`;
    }
    return '';
  }
  function saleHtml(p) {
    if (!p.sale) return '';
    return `
      <div class="sale-card">
        <div><span class="sale-crop">${esc(p.sale.crop)}</span><span class="sale-meta">${kg(p.sale.qty)} available · Harvest ${fmtDate(p.sale.harvest)}</span></div>
        <span class="sale-price">${peso(p.sale.price)} <small>/ kg</small></span>
      </div>`;
  }
  function commentHtml(c) {
    const name = c.mine ? myName() : c.author;
    return `
      <div class="comment">
        ${avatarHtml(c.mine, name, 'sm')}
        <div><div class="bubble"><b>${esc(name)}</b><p>${esc(c.text)}</p></div><small>${timeAgo(c.createdAt)}</small></div>
      </div>`;
  }
  function postHtml(p) {
    const name = postAuthor(p);
    const likes = p.likes ? `${p.likes} ${p.likes === 1 ? 'like' : 'likes'}` : '';
    const nComments = p.comments.length ? `${p.comments.length} ${p.comments.length === 1 ? 'comment' : 'comments'}` : '';
    const open = openComments.has(p.id);
    const comments = open ? `
      <div class="comments">
        ${p.comments.map(commentHtml).join('')}
        <form class="comment-form" data-post="${p.id}">
          ${avatarHtml(true, myName(), 'sm')}
          <input type="text" name="comment" maxlength="300" placeholder="Write a comment" aria-label="Write a comment" autocomplete="off" required>
          <button class="btn small" type="submit">Send</button>
        </form>
      </div>` : '';
    return `
      <article class="fb-card post">
        <header class="post-head">
          ${avatarHtml(p.mine, name, 'md')}
          <div class="post-who">
            <strong>${esc(name)}</strong>
            <div class="post-meta"><span>${esc(postPlace(p))}</span><span aria-hidden="true">·</span><span>${timeAgo(p.createdAt)}</span><span class="pill ${p.kind === 'sale' ? 'sale' : ''}">${p.kind === 'sale' ? 'For sale' : 'Harvest update'}</span></div>
          </div>
          ${p.mine ? `<button class="post-del" type="button" data-delete-post="${p.id}">Delete</button>` : ''}
        </header>
        ${p.text ? `<p class="post-text">${esc(p.text)}</p>` : ''}
        ${mediaHtml(p)}
        ${saleHtml(p)}
        ${likes || nComments ? `<div class="post-counts"><span>${likes}</span><span>${nComments}</span></div>` : '<div class="post-gap"></div>'}
        <div class="post-actions">
          <button class="act" type="button" data-like="${p.id}" aria-pressed="${p.liked}">${ICON_LIKE}${p.liked ? 'Liked' : 'Like'}</button>
          <button class="act" type="button" data-comments="${p.id}" aria-expanded="${open}">${ICON_COMMENT}Comment</button>
        </div>
        ${comments}
      </article>`;
  }

  function postMatches(p) {
    if (feedFilter !== 'all' && p.kind !== feedFilter) return false;
    if (!feedQuery) return true;
    return [postAuthor(p), postPlace(p), p.text, p.sale ? p.sale.crop : ''].join(' ').toLowerCase().includes(feedQuery);
  }
  function renderFeed() {
    const all = [...state.posts].sort((a, b) => b.createdAt.localeCompare(a.createdAt));
    const list = all.filter(postMatches);
    const filtered = feedFilter !== 'all' || feedQuery;
    $('#feedNote').textContent = filtered && all.length ? `Showing ${list.length} of ${all.length} posts` : '';
    $('#feed').innerHTML = list.length
      ? list.map(postHtml).join('')
      : `<div class="fb-card feed-empty">${all.length ? 'No posts match your search or filter.' : 'No posts yet. Share a harvest update to start the conversation.'}</div>`;
  }
  function refocus(selector) {
    const el = $(selector);
    if (el) el.focus();
  }
  function syncFeedTabs() {
    $$('#feedTabs .tab').forEach(t => t.classList.toggle('active', t.dataset.feed === feedFilter));
  }

  // Composer
  function updateComposerState() {
    const ta = $('#postText');
    const hasContent = ta.value.trim().length > 0 || !!composer.photo;
    const needsListing = composer.type === 'sale';
    $('#postBtn').disabled = !hasContent || (needsListing && !state.listings.length);
    $('#postCount').textContent = ta.value.length + ' / ' + POST_MAX;
  }
  function renderComposer() {
    const sel = $('#postListing');
    const prev = sel.value;
    sel.innerHTML = state.listings
      .map(l => `<option value="${l.id}">${esc(l.crop)} · ${peso(l.price)}/kg · ${kg(listingStats(l).available)} left</option>`)
      .join('');
    if (prev && state.listings.some(l => String(l.id) === prev)) sel.value = prev;
    sel.parentElement.hidden = !state.listings.length;
    $('#composerListing').hidden = composer.type !== 'sale';
    $('#postListingHint').textContent = state.listings.length
      ? 'Buyers will see the price, quantity and harvest date from this listing.'
      : 'You have no listings yet. Add one in Produce Listings first, then share it here.';
    $$('[data-post-type]').forEach(b => {
      const on = b.dataset.postType === composer.type;
      b.classList.toggle('active', on);
      b.setAttribute('aria-pressed', String(on));
    });
    updateComposerState();
  }
  function showPostError(msg) {
    const el = $('#postError');
    el.textContent = msg;
    el.hidden = !msg;
  }
  function setPostPhoto(src) {
    composer.photo = src;
    $('#postPhotoWrap').hidden = !src;
    const img = $('#postPhotoImg');
    if (src) img.src = src; else img.removeAttribute('src');
    updateComposerState();
  }

  $('#postText').addEventListener('input', updateComposerState);
  document.querySelector('.composer-type').addEventListener('click', e => {
    const b = e.target.closest('[data-post-type]');
    if (!b) return;
    composer.type = b.dataset.postType;
    showPostError('');
    renderComposer();
  });
  $('#postPhotoInput').addEventListener('change', async e => {
    const file = e.target.files[0];
    e.target.value = '';
    if (!file) return;
    try { setPostPhoto(await readImage(file)); showPostError(''); }
    catch (err) { showPostError(err.message); }
  });
  $('#postPhotoRemove').addEventListener('click', () => setPostPhoto(''));

  $('#postBtn').addEventListener('click', () => {
    const post = {
      id: Date.now(), mine: true, author: '', place: '', createdAt: new Date().toISOString(),
      kind: composer.type, text: $('#postText').value.trim(), photo: composer.photo, art: '', sale: null,
      likes: 0, liked: false, comments: []
    };
    if (composer.type === 'sale') {
      const l = state.listings.find(x => String(x.id) === $('#postListing').value);
      if (!l) { showPostError('Choose a listing to sell.'); return; }
      post.sale = { crop: l.crop, price: l.price, qty: listingStats(l).available, harvest: l.harvest };
      post.art = l.crop;
      if (!post.photo && l.photo) post.photo = l.photo;
    }
    state.posts.unshift(post);

    $('#postText').value = '';
    setPostPhoto('');
    showPostError('');
    composer.type = 'harvest';
    feedFilter = 'all';
    feedQuery = '';
    $('#feedSearch').value = '';
    syncFeedTabs();
    renderAll();
    toast('Posted to your feed');
    // TODO: POST the new post to your PHP endpoint (send the original photo file too)
  });

  // Filter tabs and search
  $('#feedTabs').addEventListener('click', e => {
    const tab = e.target.closest('.tab');
    if (!tab) return;
    feedFilter = tab.dataset.feed;
    syncFeedTabs();
    renderFeed();
  });
  $('#feedSearch').addEventListener('input', e => {
    feedQuery = e.target.value.trim().toLowerCase();
    if (!$('#view-home').classList.contains('active')) {
      history.replaceState(null, '', '#home');
      showView('home');
    }
    renderFeed();
  });

  // Like, comment, delete
  $('#feed').addEventListener('click', e => {
    const like = e.target.closest('[data-like]');
    if (like) {
      const p = findPost(like.dataset.like);
      if (!p) return;
      p.liked = !p.liked;
      p.likes += p.liked ? 1 : -1;
      renderFeed();
      refocus(`[data-like="${p.id}"]`);
      return;
    }
    const cm = e.target.closest('[data-comments]');
    if (cm) {
      const p = findPost(cm.dataset.comments);
      if (!p) return;
      const opening = !openComments.has(p.id);
      if (opening) openComments.add(p.id); else openComments.delete(p.id);
      renderFeed();
      refocus(opening ? `.comment-form[data-post="${p.id}"] input` : `[data-comments="${p.id}"]`);
      return;
    }
    const del = e.target.closest('[data-delete-post]');
    if (del && confirm('Delete this post?')) {
      state.posts = state.posts.filter(x => String(x.id) !== del.dataset.deletePost);
      renderFeed();
      toast('Post deleted');
      // TODO: DELETE the post on your PHP endpoint
    }
  });
  $('#feed').addEventListener('submit', e => {
    const form = e.target.closest('.comment-form');
    if (!form) return;
    e.preventDefault();
    const p = findPost(form.dataset.post);
    const text = String(new FormData(form).get('comment') || '').trim();
    if (!p || !text) return;
    p.comments.push({ id: Date.now(), author: '', mine: true, text, createdAt: new Date().toISOString() });
    renderFeed();
    refocus(`.comment-form[data-post="${p.id}"] input`);
    // TODO: POST the comment to your PHP endpoint
  });

  /* ---------- Profile ---------- */
  const profileForm = $('#profileForm');
  const profileEditPanel = $('#profileEditPanel');
  const editProfileBtn = $('#editProfileBtn');
  const pfPhotoInput = $('#pfPhotoInput');
  const pfPhotoPreview = $('#pfPhotoPreview');
  const pfPhotoNote = $('#pfPhotoNote');
  const pfRemovePhoto = $('#pfRemovePhoto');
  const PF_NOTE_DEFAULT = 'A clear photo helps buyers trust you. JPG, PNG or WebP.';
  const PROFILE_FIELDS = ['name', 'phone', 'address', 'crops', 'farmSize', 'years', 'about'];
  let profilePhotoDraft = '';

  const initialOf = name => (String(name).trim().charAt(0) || 'F').toUpperCase();
  const photoOrInitial = (p, alt) => p.photo
    ? `<img src="${esc(p.photo)}" alt="${esc(alt)}">`
    : esc(initialOf(p.name || 'Farmer'));
  const valueOrNotSet = (v, fmt) => v ? esc(fmt ? fmt(v) : v) : '<span class="not-set">Not set</span>';

  function renderProfile() {
    const p = state.profile;
    const name = p.name.trim() || 'Farmer';

    // Top bar chip
    $('#userName').textContent = name;
    $('#userInitial').innerHTML = photoOrInitial(p, '');
    $('#composerAvatar').innerHTML = photoOrInitial(p, '');
    $('#postText').placeholder = `What's happening on your farm, ${name.split(' ')[0]}?`;

    // Profile card
    $('#pfPhoto').innerHTML = photoOrInitial(p, name + ' profile photo');
    $('#pfName').textContent = name;
    $('#pfAddressLine').textContent = p.address || 'Address not set';
    $('#pfAbout').innerHTML = p.about
      ? esc(p.about)
      : '<span class="not-set">No description yet. Select Edit profile to tell buyers about your farm.</span>';

    const crops = p.crops.split(',').map(c => c.trim()).filter(Boolean);
    $('#pfCrops').innerHTML = crops.length
      ? crops.map(c => `<span class="pill">${esc(c)}</span>`).join('')
      : '<span class="not-set">No crops added yet.</span>';

    $('#pfAddress').innerHTML  = valueOrNotSet(p.address);
    $('#pfPhone').innerHTML    = valueOrNotSet(p.phone);
    $('#pfFarmSize').innerHTML = valueOrNotSet(p.farmSize, v => v + (Number(v) === 1 ? ' hectare' : ' hectares'));
    $('#pfYears').innerHTML    = valueOrNotSet(p.years, v => v + (Number(v) === 1 ? ' year' : ' years'));

    // Summary numbers come from the farmer's own data
    const done = state.orders.filter(o => o.status === 'Completed');
    $('#pfListings').textContent  = state.listings.length;
    $('#pfCompleted').textContent = done.length;
    $('#pfSold').textContent      = done.reduce((s, o) => s + o.qty, 0).toLocaleString('en-PH');
  }

  // Photo inside the edit form
  function setProfilePhotoDraft(src, note) {
    profilePhotoDraft = src;
    pfPhotoPreview.classList.toggle('has-photo', !!src);
    pfPhotoPreview.innerHTML = src
      ? `<img src="${esc(src)}" alt="Preview of your profile photo">`
      : PLACEHOLDER;
    pfRemovePhoto.hidden = !src;
    pfPhotoNote.textContent = note || PF_NOTE_DEFAULT;
    pfPhotoNote.classList.remove('error');
  }
  pfPhotoInput.addEventListener('change', async () => {
    const file = pfPhotoInput.files[0];
    pfPhotoInput.value = '';
    if (!file) return;
    try {
      setProfilePhotoDraft(await readImage(file, 480), file.name);
    } catch (err) {
      pfPhotoNote.textContent = err.message;
      pfPhotoNote.classList.add('error');
    }
  });
  pfRemovePhoto.addEventListener('click', () => setProfilePhotoDraft(''));

  // Open / close the edit form (it sits below the profile card)
  function openProfileEdit() {
    const p = state.profile;
    PROFILE_FIELDS.forEach(k => { profileForm.elements.namedItem(k).value = p[k] || ''; });
    setProfilePhotoDraft(p.photo);
    profileEditPanel.hidden = false;
    editProfileBtn.setAttribute('aria-expanded', 'true');
    editProfileBtn.parentElement.hidden = true;
    profileEditPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    profileForm.elements.namedItem('name').focus({ preventScroll: true });
  }
  function closeProfileEdit() {
    profileEditPanel.hidden = true;
    editProfileBtn.setAttribute('aria-expanded', 'false');
    editProfileBtn.parentElement.hidden = false;
  }
  editProfileBtn.addEventListener('click', openProfileEdit);
  $('#cancelProfile').addEventListener('click', () => {
    closeProfileEdit();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  profileForm.addEventListener('submit', e => {
    e.preventDefault();
    const f = new FormData(profileForm);
    const text = k => String(f.get(k) || '').trim();
    const name = text('name');
    if (!name) { toast('Please enter your name'); return; }
    state.profile = {
      name,
      photo: profilePhotoDraft,
      phone: text('phone'),
      address: text('address'),
      crops: text('crops'),
      farmSize: text('farmSize'),
      years: text('years'),
      about: text('about')
    };
    renderAll();
    closeProfileEdit();
    window.scrollTo({ top: 0, behavior: 'smooth' });
    toast('Profile saved');
    // TODO: POST to your profile endpoint, e.g.
    // const body = new FormData(profileForm);   // then add the photo file if it changed
    // fetch('save_profile.php', { method: 'POST', body });
  });

  // Settings
  ['setAlerts', 'setMessages'].forEach(id => $('#' + id).addEventListener('change', renderBadges));

  /* ---------- Init ---------- */
  renderAll();
  showView(location.hash.slice(1) || 'home');
})();