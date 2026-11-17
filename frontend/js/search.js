let user = null, current = null, curDate = '', picked = '';

(async () => {
  user = await initNav('find');
  $('#date').value = today(); $('#date').min = today();
  await load(true);
})();

async function load(first) {
  try {
    const d = await api('routes', { params: { from: $('#from').value, to: $('#to').value, date: $('#date').value } });
    if (first) for (const id of ['from', 'to']) $('#' + id).insertAdjacentHTML('beforeend', d.cities.map((c) => `<option>${esc(c)}</option>`).join(''));
    $('#msg').innerHTML = '';
    $('#list').innerHTML = d.routes.length ? d.routes.map((r) => `
      <div class="card trip"><div>
        <h3>${esc(r.start_city)} → ${esc(r.end_city)}</h3>
        <div class="mu">${r.departure_time.slice(0, 5)} – ${r.arrival_time.slice(0, 5)} · ${esc(r.transport_type)} · ${esc(r.distance)} km</div>
        <div class="mu">${r.taken} seat(s) already booked on ${esc($('#date').value)}</div></div>
        <div><div class="price">${gbp(r.price)}</div><button class="btn" data-id="${r.route_id}" data-name="${esc(r.start_city)} → ${esc(r.end_city)}" data-price="${r.price}">Book</button></div>
      </div>`).join('') : '<div class="msg">No routes match your search.</div>';
  } catch (e) { $('#msg').innerHTML = `<div class="msg err">${esc(e.message)}</div>`; }
}

$('#f').onsubmit = (e) => { e.preventDefault(); load(false); };

$('#list').onclick = async (e) => {
  const b = e.target.closest('button[data-id]'); if (!b) return;
  if (!user) { location.href = 'login.html?next=' + encodeURIComponent('index.html'); return; }
  current = +b.dataset.id; curDate = $('#date').value; picked = '';
  $('#dtitle').textContent = 'Book ' + b.dataset.name;
  $('#dsub').textContent = curDate + ' · ' + gbp(b.dataset.price);
  $('#derr').textContent = '';
  $('#sel').innerHTML = '<b>No seat selected</b>';
  $('#seatmap').innerHTML = '<span class="mu">Loading seats…</span>';
  $('#dlg').showModal();
  try { drawSeats(await api('seats', { params: { route_id: current, date: curDate } })); }
  catch (err) { $('#derr').textContent = err.message; }
};

function drawSeats(s) {
  const taken = new Set(s.taken), half = s.cols / 2;
  $('#seatmap').innerHTML = s.rows.map((r) => `<div class="srow"><span class="mu">${r}</span>` +
    Array.from({ length: s.cols }, (_, i) => {
      const id = r + (i + 1), t = taken.has(id);
      return `<button type="button" class="seat${t ? ' taken' : ''}${i === half ? ' aisle' : ''}" data-seat="${id}" ${t ? 'disabled' : ''}>${i + 1}</button>`;
    }).join('') + '</div>').join('');
}

$('#seatmap').onclick = (e) => {
  const b = e.target.closest('button[data-seat]'); if (!b || b.disabled) return;
  picked = b.dataset.seat;
  document.querySelectorAll('.seat.sel').forEach((x) => x.classList.remove('sel'));
  b.classList.add('sel');
  $('#sel').innerHTML = 'Seat selected: <b>' + esc(picked) + '</b>';
};

$('#dclose').onclick = () => $('#dlg').close();

$('#bf').onsubmit = async (e) => {
  e.preventDefault();
  if (!picked) { $('#derr').textContent = 'Pick a seat first.'; return; }
  try {
    await api('book', { method: 'POST', body: { route_id: current, date: curDate, seat: picked, luggage: +$('#lug').value, method: $('#pay').value } });
    location.href = 'bookings.html?ok=1';
  } catch (err) {
    $('#derr').textContent = err.message;
    if (err.status === 409) try { drawSeats(await api('seats', { params: { route_id: current, date: curDate } })); picked = ''; $('#sel').innerHTML = '<b>No seat selected</b>'; } catch {}
  }
};
