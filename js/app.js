async function api(path, body){
  const r = await fetch(`/LAMPAPI/${path}`, {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify(body||{})
  });
  return r.json();
}
async function doLogin(e){
  e.preventDefault();
  const login = document.querySelector('#login').value.trim();
  const password = document.querySelector('#password').value.trim();
  const res = await api('Login.php', { login, password });
  const out = document.querySelector('#out');
  if(res.error){
    out.textContent = res.error;
    return;
  }
  localStorage.setItem('cmUser', JSON.stringify({
    id: res.id,
    firstName: res.firstName,
    lastName: res.lastName
  }));
  window.location.href = '/contacts.html'; 
}

async function addContact(e){
  e.preventDefault();
  const userId = Number(document.querySelector('#userId').value);
  const firstName = document.querySelector('#first').value.trim();
  const lastName = document.querySelector('#last').value.trim();
  const phone = document.querySelector('#phone').value.trim();
  const email = document.querySelector('#email').value.trim();
  const res = await api('AddContact.php', {userId, firstName, lastName, phone, email});
  document.querySelector('#out').textContent = res.error || `Added contact #${res.id}`;
}

async function searchContacts(e){
  e.preventDefault();
  const userId = Number(document.querySelector('#userId').value);
  const search = document.querySelector('#search').value.trim();
  const res = await api('SearchContacts.php', {userId, search});
  document.querySelector('#results').textContent = JSON.stringify(res.results, null, 2);
}


async function doRegister(e){
  e.preventDefault();
  const firstName = document.querySelector('#first').value.trim();
  const lastName  = document.querySelector('#last').value.trim();
  const login     = document.querySelector('#newLogin').value.trim();
  const password  = document.querySelector('#newPassword').value.trim();
  const out = document.querySelector('#regOut');

  if(!firstName || !lastName || !login || !password){
    out.textContent = 'Please fill out all fields.';
    return;
  }

  try{
    const res = await api('Register.php', { firstName, lastName, login, password });
    if(res.error){
      out.textContent = res.error;
      return;
    }
    out.textContent = `Account created for ${firstName} ${lastName} (id=${res.id}). You can log in now.`;
    // Prefill login form for convenience
    const loginEl = document.querySelector('#login');
    const passEl  = document.querySelector('#password');
    if(loginEl) loginEl.value = login;
    if(passEl)  passEl.value  = password;
  }catch(err){
    out.textContent = 'Network error while registering.';
  }
}
