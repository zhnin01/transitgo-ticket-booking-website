// Shared helpers: talks to the PHP backend and draws the top navigation.
const API = '../backend/api/';

async function api(name, { method = 'GET', body, params } = {}) {
  let url = API + name + '.php';
  if (params) url += '?' + new URLSearchParams(params);
  const res = await fetch(url, {
    method, credentials: 'same-origin',
    headers: body ? { 'Content-Type': 'application/json' } : {},
    body: body ? JSON.stringify(body) : undefined,
  });
  let data = {};
  try { data = await res.json(); } catch { /* non-JSON response */ }
  if (!res.ok) throw Object.assign(new Error(data.error || 'Request failed'), { status: res.status });
  return data;
}
const $ = (s) => document.querySelector(s);
const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const gbp = (n) => '£' + Number(n).toFixed(2);
const today = () => new Date().toLocaleDateString('en-CA');

// Draws the nav and returns the logged-in user (or null).
async function initNav(active) {
  let user = null;
  try { user = (await api('me')).user; } catch (e) { $('main').insertAdjacentHTML('afterbegin', `<div class="msg err">${esc(e.message)}</div>`); }
  const link = (href, id, text) => `<a href="${href}" class="${active === id ? 'on' : ''}">${text}</a>`;
  let h = link('index.html', 'find', 'Find a trip');
  if (user) {
    h += link('bookings.html', 'mine', 'My bookings');
    h += link('account.html', 'account', 'Account');
    if (+user.is_admin) h += link('admin.html', 'admin', 'Admin');
    h += `<span class="sp"></span><span class="who">${esc(user.first_name)} ${esc(user.last_name)}</span><button id="logout">Log out</button>`;
  } else {
    h += `<span class="sp"></span>` + link('login.html', 'login', 'Log in') + link('register.html', 'register', 'Register');
  }
  $('#nav').innerHTML = h;
  const lo = $('#logout');
  if (lo) lo.onclick = async () => { await api('logout', { method: 'POST' }); location.href = 'index.html'; };
  return user;
}
