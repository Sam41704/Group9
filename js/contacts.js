async function api(path, body){
  const r = await fetch(`/LAMPAPI/${path}`, {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify(body||{})
  });
  if(!r.ok) throw new Error(`HTTP ${r.status}`);
  return r.json();
}

let user = null;

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

window.addEventListener('DOMContentLoaded', () => {
  const u = requireUser();
  if(!u) return;
  document.querySelector('#who').textContent = `Signed in as ${u.firstName} ${u.lastName}`;
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

async function searchContacts(e){
  if(e) e.preventDefault();
  const u = requireUser(); if(!u) return;

  const term = document.querySelector('#search').value.trim();
  try{
    const res = await api('SearchContacts.php', { userId: u.id, search: term });
    if(res.error){ document.querySelector('#results').textContent = res.error; return; }
    if(!res.results || !res.results.length){ document.querySelector('#results').textContent = '(no matches)'; return; }

    const lines = res.results.map(r => {
      return `${r.firstName} ${r.lastName} — ${r.phone || ''} ${r.email || ''} [id:${r.id}]
  [Edit] click to load form → editContact(${r.id}, ${JSON.stringify(r).replace(/"/g,'&quot;')})
  [Delete] click to remove → deleteContact(${r.id})`;
    });
    document.querySelector('#results').textContent = lines.join('\n\n');
  }catch(err){
    document.querySelector('#results').textContent = 'Network error.';
  }
}

function editContact(id, data){
  document.querySelector('#contactId').value = id;
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
