const FIELDS = ['first_name', 'last_name', 'phone_no', 'street_name', 'city', 'postcode'];
const flash = (t, ok = true) => { $('#msg').innerHTML = `<div class="msg ${ok ? 'Confirmed' : 'err'}">${esc(t)}</div>`; scrollTo(0, 0); };

(async () => {
  const user = await initNav('account');
  if (!user) { location.href = 'login.html?next=account.html'; return; }
  try {
    const { account } = await api('account');
    $('#email').value = account.email;
    for (const k of FIELDS) $('#' + k).value = account[k];
  } catch (e) { flash(e.message, false); }
})();

$('#details').onsubmit = async (e) => {
  e.preventDefault();
  const body = { action: 'update' };
  for (const k of FIELDS) body[k] = $('#' + k).value;
  try { await api('account', { method: 'POST', body }); await initNav('account'); flash('Details saved.'); }
  catch (err) { flash(err.message, false); }
};

$('#pwform').onsubmit = async (e) => {
  e.preventDefault();
  if ($('#new').value !== $('#new2').value) { flash('The new passwords don’t match.', false); return; }
  try {
    await api('account', { method: 'POST', body: { action: 'password', current_password: $('#cur').value, new_password: $('#new').value } });
    $('#pwform').reset(); flash('Password changed.');
  } catch (err) { flash(err.message, false); }
};
