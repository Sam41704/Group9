let user = null;

async function api(path, body){
  const r = await fetch(`/api/${path}`, {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify(body||{})
  });
  if(!r.ok) throw new Error(`HTTP ${r.status}`);
  return r.json();
}

function getUser(){
  if(user) return user;
  try{
    const raw = localStorage.getItem('cmUser');
    if(!raw) return null;
    user = JSON.parse(raw);
    return user;
  }catch{ return null; }
}

function requireUser(){
  const u = getUser();
  if(!u || !u.id){
    window.location.href = '/';
    return null;
  }
  return u;
}

function logout(){
  //clear locally stored user info
  localStorage.removeItem('cmUser');
  //redirect to index/login
  window.location.href = '/';
}

window.addEventListener('DOMContentLoaded', () => {
  const u = requireUser();
  if(!u) return;
  document.querySelector('#who').textContent = `Signed in as ${u.firstName} ${u.lastName}`;

  //logout button hookup
  const lb = document.querySelector('#logoutBtn');
  if (lb) lb.addEventListener('click', logoutBtn);

  searchContacts();
});

function resetForm(){
  document.querySelector('#contactId').value = '';
  document.querySelector('#cFirst').value = '';
  document.querySelector('#cLast').value = '';
  document.querySelector('#cPhone').value = '';
  document.querySelector('#cEmail').value = '';
  document.querySelector('#saveOut').textContent = '';
}

async function saveContact(e){
  if(e) e.preventDefault();
  const u = requireUser(); if(!u) return;

  const id = Number(document.querySelector('#contactId').value || 0);
  const firstName = document.querySelector('#cFirst').value.trim();
  const lastName  = document.querySelector('#cLast').value.trim();
  const phone     = document.querySelector('#cPhone').value.trim();
  const email     = document.querySelector('#cEmail').value.trim();

  const out = document.querySelector('#saveOut');
  if(!firstName || !lastName){
    out.textContent = 'first/last required';
    return;
  }

  try{
    let res;
    if(id > 0){
      res = await api('UpdateContact.php', { userId: u.id, contactId: id, firstName, lastName, phone, email });
    }else{
      res = await api('AddContact.php', { userId: u.id, firstName, lastName, phone, email });
    }
    if(res.error){
      out.textContent = res.error;
      return;
    }
    out.textContent = id ? 'Contact updated.' : `Added contact #${res.id}.`;
    resetForm();
    await searchContacts();
  }catch(err){
    out.textContent = 'Network error.';
  }
}

function esc(s){
  return String(s||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}

function renderResults(rows){
  const tbody = document.querySelector('#resultsBody');
  tbody.innerHTML = '';
  if(!rows || !rows.length){
    const tr = document.createElement('tr');
    const td = document.createElement('td');
    td.colSpan = 5; td.className='muted'; td.textContent='(no results)';
    tr.appendChild(td); tbody.appendChild(tr);
    return;
  }
  rows.forEach(r => {
    const tr = document.createElement('tr');
    tr.dataset.id = r.id;

    const tdFirst = document.createElement('td'); tdFirst.textContent = r.firstName || ''; tr.appendChild(tdFirst);
    const tdLast  = document.createElement('td'); tdLast.textContent  = r.lastName || '';  tr.appendChild(tdLast);
    const tdPhone = document.createElement('td'); tdPhone.textContent = r.phone || '';    tr.appendChild(tdPhone);
    const tdEmail = document.createElement('td'); tdEmail.textContent = r.email || '';    tr.appendChild(tdEmail);

    const tdAct = document.createElement('td');
    const editBtn = document.createElement('button');
    editBtn.className = 'btn'; editBtn.textContent = 'Edit';
    editBtn.addEventListener('click', () => {
      editContact(r);
    });
    const delBtn = document.createElement('button');
    delBtn.className = 'btn'; delBtn.textContent = 'Delete';
    delBtn.addEventListener('click', () => {
      deleteContact(r.id);
    });
    tdAct.appendChild(editBtn);
    tdAct.appendChild(delBtn);
    tr.appendChild(tdAct);

    tbody.appendChild(tr);
  });
}

async function searchContacts(e){
  if(e) e.preventDefault();
  const u = requireUser(); if(!u) return;

  const term = document.querySelector('#search').value.trim();
  try{
    const res = await api('SearchContacts.php', { userId: u.id, search: term });
    if(res.error){ renderResults([]); document.querySelector('#resultsBody').innerHTML = `<tr><td colspan="5" class="muted">${esc(res.error)}</td></tr>`; return; }
    renderResults(res.results);
  }catch(err){
    document.querySelector('#resultsBody').innerHTML = '<tr><td colspan="5" class="muted">Network error.</td></tr>';
  }
}

function editContact(data){
  document.querySelector('#contactId').value = data.id || '';
  document.querySelector('#cFirst').value = data.firstName || '';
  document.querySelector('#cLast').value  = data.lastName || '';
  document.querySelector('#cPhone').value = data.phone || '';
  document.querySelector('#cEmail').value = data.email || '';
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function deleteContact(id){
  const u = requireUser(); if(!u) return;
  if(!confirm('Delete this contact?')) return;
  try{
    const res = await api('DeleteContact.php', { userId: u.id, contactId: id });
    if(res.error){ alert(res.error); return; }
    await searchContacts();
  }catch(err){
    alert('Network error.');
  }
}
