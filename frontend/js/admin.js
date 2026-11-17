let tab = 'bookings', routes = [], editing = 0;

(async () => {
  const user = await initNav('admin');
  if (!user) { location.href = 'login.html?next=admin.html'; return; }
  show();
})();

const flash = (t, ok = true) => { $('#msg').innerHTML = `<div class="msg ${ok ? 'Confirmed' : 'err'}">${esc(t)}</div>`; };
$('#tab-b').onclick = () => { tab = 'bookings'; show(); };
$('#tab-r').onclick = () => { tab = 'routes'; show(); };

async function show(keepMsg) {
  $('#tab-b').classList.toggle('on', tab === 'bookings');
  $('#tab-r').classList.toggle('on', tab === 'routes');
  if (!keepMsg) $('#msg').innerHTML = '';
  try { tab === 'bookings' ? await bookingsTab() : await routesTab(); }
  catch (e) { $('#out').innerHTML = ''; flash(e.status === 403 ? 'This page is for admins only.' : e.message, false); }
}

async function bookingsTab() {
  const d = await api('admin_stats'), s = d.stats;
  $('#out').innerHTML = `
    <div class="stats">
      <div class="card"><b>${s.active}</b>Active bookings</div>
      <div class="card"><b>${gbp(s.paid)}</b>Revenue (paid)</div>
      <div class="card"><b>${gbp(s.pending)}</b>Awaiting payment</div>
      <div class="card"><b>${s.cancelled}</b>Cancelled</div>
    </div>
    ${d.top_route ? `<div class="msg">Most popular route: <b>${esc(d.top_route.name)}</b> (${d.top_route.n} bookings)</div>` : ''}
    <div class="card wrap"><table>
      <tr><th>ID</th><th>Passenger</th><th>Route</th><th>Date</th><th>Seat</th><th>Status</th><th>Payment</th><th>Amount</th><th></th></tr>
      ${d.bookings.map((b) => `<tr><td>${b.booking_id}</td><td>${esc(b.passenger)}</td><td>${esc(b.route)}</td><td>${esc(b.travel_date)}</td>
        <td>${esc(b.seat_number)}</td><td class="${esc(b.booking_status)}">${esc(b.booking_status)}</td>
        <td class="${esc(b.payment_status)}">${esc(b.payment_status)}</td><td>${gbp(b.total_amount)}</td>
        <td class="acts">${b.booking_status === 'Cancelled' ? '' :
          (b.payment_status === 'Pending' ? `<button class="btn sm ok" data-bk="${b.booking_id}" data-act="mark_paid">Mark paid</button>` : '') +
          `<button class="btn sm ghost" data-bk="${b.booking_id}" data-act="cancel">Cancel</button>`}</td></tr>`).join('')}
    </table></div>`;
}

async function routesTab() {
  routes = (await api('admin_routes')).routes;
  $('#out').innerHTML = `<p><button class="btn" id="addr">+ Add route</button></p>
    <div class="card wrap"><table>
      <tr><th>ID</th><th>Route</th><th>Type</th><th>Distance</th><th>Times</th><th>Price</th><th>Status</th><th></th></tr>
      ${routes.map((r) => `<tr><td>${r.route_id}</td><td>${esc(r.start_city)} → ${esc(r.end_city)}</td><td>${esc(r.transport_type)}</td>
        <td>${esc(r.distance)} km</td><td>${r.departure_time.slice(0, 5)} – ${r.arrival_time.slice(0, 5)}</td><td>${gbp(r.price)}</td>
        <td class="${r.route_status === 'Active' ? 'Confirmed' : 'Cancelled'}">${esc(r.route_status)}</td>
        <td class="acts"><button class="btn sm" data-edit="${r.route_id}">Edit</button>
        <button class="btn sm ghost" data-tog="${r.route_id}">${r.route_status === 'Active' ? 'Deactivate' : 'Activate'}</button></td></tr>`).join('')}
    </table></div>`;
}

$('#out').onclick = async (e) => {
  const b = e.target.closest('button'); if (!b) return;
  try {
    if (b.dataset.act) {
      if (b.dataset.act === 'cancel' && !confirm('Cancel booking #' + b.dataset.bk + '?')) return;
      await api('admin_booking', { method: 'POST', body: { booking_id: +b.dataset.bk, action: b.dataset.act } });
      await show(); flash(b.dataset.act === 'cancel' ? 'Booking cancelled.' : 'Marked as paid.');
    } else if (b.dataset.tog) {
      await api('admin_routes', { method: 'POST', body: { action: 'toggle', route_id: +b.dataset.tog } });
      await show(); flash('Route status updated. Deactivated routes disappear from search, but existing bookings stay.');
    } else if (b.dataset.edit) openRoute(routes.find((r) => r.route_id == b.dataset.edit));
    else if (b.id === 'addr') openRoute(null);
  } catch (err) { flash(err.message, false); }
};

function openRoute(r) {
  editing = r ? +r.route_id : 0;
  $('#rtitle').textContent = r ? 'Edit route #' + r.route_id : 'Add route';
  $('#r_start').value = r ? r.start_city : ''; $('#r_end').value = r ? r.end_city : '';
  $('#r_type').value = r ? r.transport_type : 'Train';
  $('#r_dist').value = r ? r.distance : ''; $('#r_price').value = r ? r.price : '';
  $('#r_dep').value = r ? r.departure_time.slice(0, 5) : ''; $('#r_arr').value = r ? r.arrival_time.slice(0, 5) : '';
  $('#rerr').textContent = ''; $('#rd').showModal();
}
$('#rclose').onclick = () => $('#rd').close();

$('#rf').onsubmit = async (e) => {
  e.preventDefault();
  const route = { route_id: editing, start_city: $('#r_start').value, end_city: $('#r_end').value, transport_type: $('#r_type').value,
    distance: $('#r_dist').value, price: $('#r_price').value, departure_time: $('#r_dep').value, arrival_time: $('#r_arr').value };
  try {
    await api('admin_routes', { method: 'POST', body: { action: 'save', route } });
    $('#rd').close(); await show(); flash(editing ? 'Route updated.' : 'Route added.');
  } catch (err) { $('#rerr').textContent = err.message; }
};
