# API Interne ed Endpoint JSON

L'applicazione utilizza endpoint API interni per l'aggiornamento asincrono (Polling) del Muretto.

## 1. Stato Kart (Polling Muretto)
- **URL:** `/muretto/apiStatoKart/{gara_id}`
- **Metodo:** `GET`
- **Descrizione:** Restituisce lo stato attuale (Rating) del kart assegnato a ciascun team, incluso il nostro.
- **Risposta Successo (200 OK):**
  ```json
  {
    "status": "success",
    "data": {
      "nostro_kart": { "rating": "Buono", "numero": 27 },
      "avversari": [
        { "team_id": 1, "nome": "Team A", "kart_rating": "Scarso" }
      ]
    }
  }

  ## 2. Endpoint disponibili
  ### Radar Avversari (Stato Kart Live)
  - **URL:** `/muretto/apiStatoKart/{gara_id}`
  - **Metodo:** `GET`
  - **Descrizione:** Restituisce lo stato attuale (Rating) del kart assegnato a ciascun team, incluso il nostro.
  - **Risposta Successo (200 OK):**
    ```json
    {
      "status": "success",
      "data": {
        "nostro_kart": { "rating": "Buono", "numero": 27 },
        "avversari": [
          { "team_id": 1, "nome": "Team A", "kart_rating": "Scarso" }
        ]
      }
    }

    ## 3. Endpoint Modifica Kart (Solo per Admin/Muretto)
- **URL:** `/spotter/apiAssegnaKart`
- **Metodo:** `POST`
- **Descrizione:** Permette di assegnare un kart specifico a un team che sta entrando in pista.
- **Parametri Richiesti (form-data o JSON):**
  - `gara_id`: ID della gara.
  - `team_id`: ID del team da modificare.
  - `kart_id`: ID del kart da assegnare.
- **Risposta Successo (200 OK):**
  ```json
  {
    "status": "success",
    "message": "Kart assegnato con successo!"
  }

## 4. Endpoint Reset Rating (Solo per Admin)
- **URL:** `/muretto/apiResetRating/{gara_id}`
- **Metodo:** `POST` (o `GET` se si preferisce un link semplice, ma `POST` è più sicuro per azioni distruttive).
- **Descrizione:** Resetta a 0 (Ignoto) il rating di tutti i kart della gara, utile prima di un cambio gomme o per una nuova sessione.
- **Risposta Successo (200 OK):**
  ```json
  {
    "status": "success",
    "message": "Rating azzerati con successo!"
  }

## risposte di errore
-**descrizione** : quando una chiamata api fallisce, viene restituito un JSON con il campo status impostato a "error" e un campo message contenente una descrizione leggibile del problema.
**risposta di errore generica** :

```json
{"status": "error", "message": "Gara non trovata o ID non valido."}

  
  ## sicurezza e prevenzione (linee guida per sviluppi futuri)
- **descrizione** : Quando verranno aggiunti endpoint che modificano lo stato del database (es. POST /spotter/apiCambiaRating), dovranno rigorosamente rispettare queste regole aggiuntive:

1. **CSRF Token:**: Ogni payload JSON inviato tramite POST, PUT o DELETE dovrà includere un token CSRF valido, verificato dal Front Controller.
2. **Validazione Input**: I dati JSON in ingresso dovranno essere decodificati (json_decode(file_get_contents('php://input'), true)) e validati attentamente prima di essere passati ai Model per prevenire SQL Injection.
