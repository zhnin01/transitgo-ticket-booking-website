(async () => {
  const user = await initNav('mine');
  if (!user) { location.href = 'login.html?next=bookings.html'; return; }
  if (new URLSearchParams(location.search).has('ok')) $('#msg').innerHTML = '<div class="msg Confirmed">Booking confirmed! 🎉</div>';
  await load();
})();

async function load() {
  try {
    const { bookings } = await api('my_bookings');
    $('#list').innerHTML = bookings.length ? bookings.map((b) => `
      <div class="card trip"><div>
        <h3>#${b.booking_id} · ${esc(b.start_city)} → ${esc(b.end_city)}</h3>
        <div class="mu">${esc(b.travel_date)} · ${b.departure_time.slice(0, 5)} · Seat ${esc(b.seat_number)} · ${b.luggage_count} bag(s) · ${esc(b.payment_method)}</div>
        <span class="tag ${esc(b.booking_status)}">${esc(b.booking_status)}</span> <span class="tag ${esc(b.payment_status)}">${esc(b.payment_status)}</span></div>
        <div><div class="price">${gbp(b.total_amount)}</div>
        ${b.booking_status !== 'Cancelled' && b.travel_date >= today() ? `<button class="btn ghost" data-id="${b.booking_id}">Cancel</button>` : ''}</div>
      </div>`).join('') : '<div class="msg">No bookings yet. <a href="index.html">Find a trip</a></div>';
  } catch (e) { $('#msg').innerHTML = `<div class="msg err">${esc(e.message)}</div>`; }
}

$('#list').onclick = async (e) => {
  const b = e.target.closest('button[data-id]');
  if (!b || !confirm('Cancel booking #' + b.dataset.id + '?')) return;
  try { await api('cancel', { method: 'POST', body: { booking_id: +b.dataset.id } }); load(); }
  catch (err) { $('#msg').innerHTML = `<div class="msg err">${esc(err.message)}</div>`; }
};
