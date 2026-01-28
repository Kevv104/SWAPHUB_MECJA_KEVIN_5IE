function debugFunction(nomePermesso) //funzione di debug
{
  alert("Test funziona");
}

fetch("api_permessi.php")
.then(res => res.json())
.then(data => {
   document.getElementById('raw-json').textContent = JSON.stringify(data,null,4);
   

   const tbody = document.getElementById('tabella-permessi-body');
   tbody.textContent = "";


   if(data.permessi && data.permessi.length) //se esistono 
   {
    data.permessi.forEach(p => {
      const riga = document.createElement('tr');//crea la riga per ogni permesso

      const tdNome = document.createElement('td');
      const code = document.createElement('code');

      code.classList.add('text-info', 'fw-bold'); 
      code.textContent = p;

      tdNome.appendChild(code);

      const tdAzione = document.createElement('td');
      tdAzione.classList.add('text-center');


      const pulsante = document.createElement('td');
      pulsante.classList.add('pulsante','pulsante-sm','pulsante-outline-warning');

      const icon = document.createElement('i');
      icon.classList.add('bi', 'bi-lightning-charge', 'me-1');
        
      pulsante.appendChild(icon);
      pulsante.append("Fai");

      pulsante.addEventListener('click', () => debugFunction(p));

      tdAzione.appendChild(pulsante);

      riga.appendChild(tdNome);
      riga.appendChild(tdNome);

      tbody.appendChild(riga);
    });
   } else //nessun permesso
   {
     const rigaVuota = document.createElement('tr');
     const tdVuoto = document.createElement('td');

     tdVuoto.setAttribute('colspan', '2');
     tdVuoto.classList.add('text-center');
     tdVuoto.textContent = "Nessun permesso legato al utente.";
     rigaVuota.appendChild(tdVuoto);
     tbody.appendChild(rigaVuota);
   }
})
.catch(err=> console.error("Errore:", err))