(async () => {
  const u = await initNav($('#login') ? 'login' : 'register');
  if (u) location.href = 'index.html';
})();

function goNext() {
  const n = new URLSearchParams(location.search).get('next') || '';
  location.href = /^[a-z]+\.html(\?[\w=&]*)?$/.test(n) ? n : 'index.html'; // only allow our own pages
}

if ($('#login')) $('#login').onsubmit = async (e) => {
  e.preventDefault();
  try { await api('login', { method: 'POST', body: { email: $('#email').value, password: $('#pw').value } }); goNext(); }
  catch (err) { $('#msg').textContent = err.message; }
};

if ($('#register')) $('#register').onsubmit = async (e) => {
  e.preventDefault();
  const body = { password: $('#pw').value };
  for (const k of ['first_name', 'last_name', 'email', 'phone_no', 'street_name', 'city', 'postcode']) body[k] = $('#' + k).value;
  try { await api('register', { method: 'POST', body }); location.href = 'index.html'; }
  catch (err) { $('#msg').textContent = err.message; }
};
