async function api(path, body){
  const r = await fetch(`/LAMPAPI/${path}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(body || {})
  });
  if(!r.ok) throw new Error(`HTTP ${r.status}`);
  return r.json();
}
async function addColor(e){
  e.preventDefault();
  const name = document.querySelector('#colorName').value.trim();
  const out  = document.querySelector('#colorOut');
  if(!name){ out.textContent = 'Enter a color name'; return; }
  const res = await api('AddColor.php', { name });
  out.textContent = res.error || `Added color #${res.id} (${res.name})`;
  document.querySelector('#colorName').value = '';
  await searchColors(); // refresh
}

async function searchColors(e){
  if(e) e.preventDefault();
  const term = document.querySelector('#search').value.trim();
  const res  = await api('SearchColors.php', { search: term });
  const box  = document.querySelector('#results');
  if(res.error){ box.textContent = res.error; return; }
  // Render as a simple list
  if(!res.results.length){ box.textContent = '(no colors found)'; return; }
  box.textContent = res.results.map(r => `• ${r.name} (id ${r.id})`).join('\n');
}
