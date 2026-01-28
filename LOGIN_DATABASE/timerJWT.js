const controllaScadenza = setInterval(() => 
{
  fetch('api_permessi.php')
  .then(res => res.json())
  .then(check => {
    if(check.status === "Errore" || check.error) 
    {
      clearInterval(controllaScadenza); //linea di codice per fermare il timer
      alert("La sessione è scaduta! Ritornerai alla pagina di login.");
      window.location.href = "index.php?errore=Sessione_scaduta_dopodieciminuti";
    }
  })
}, 30000); //ogni 30 secondi il codice si ripete, per verificare che il token sia ancora valido