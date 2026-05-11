/**
 * Gestisce il setup gara in modalita asincrona (autosave + CRUD senza reload).
 */
(function () {
    const root = document.getElementById('setup-gara-root');
    if (!root) {
        return;
    }

    const baseUrl = root.dataset.baseUrl || '';
    const garaId = root.dataset.garaId || '';

    const formParametri = document.getElementById('form-parametri-gara');
    const autosaveStatus = document.getElementById('autosave-status');
    const formAggiungiPilota = document.getElementById('form-aggiungi-pilota');
    const formAggiungiFilaPit = document.getElementById('form-aggiungi-fila-pit');
    const formIscriviTeam = document.getElementById('form-iscrivi-team');

    let autosaveTimeout = null;
    let autosaveTimerLabel = null;

    function escapeHtml(testo) {
        return String(testo)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    /**
     * Evita il reload della pagina se viene inviato il form (es. tasto Invio).
     *
     * @param {HTMLFormElement|null} form
     */
    function bloccaSubmitReload(form) {
        if (!form) {
            return;
        }
        form.addEventListener('submit', function (e) {
            e.preventDefault();
        });
    }

    /**
     * Normalizza un colore hex per attributi HTML/CSS (solo #RRGGBB).
     *
     * @param {string} val
     * @returns {string}
     */
    function sanitizzaHexColore(val) {
        const s = String(val || '').trim();
        return /^#[0-9A-Fa-f]{6}$/.test(s) ? s : '#343a40';
    }

    /**
     * Costruisce le celle della tabella file pit con nome/colore modificabili.
     *
     * @param {object} fila
     * @returns {string}
     */
    function htmlCelleFilaEditable(fila) {
        const nome = escapeHtml(fila.nome_colore || '');
        const hexSafe = sanitizzaHexColore(fila.colore_hex);
        const idNum = Number(fila.id);
        const ordine = escapeHtml(String(fila.ordine ?? ''));
        return `
            <td>
                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                    <span class="js-fila-anteprima-colore" style="display:inline-block; width:15px; height:15px; background:${hexSafe}; border-radius:50%; vertical-align:middle; border:1px solid #333;"></span>
                    <input type="text" class="js-fila-nome-input" value="${nome}" maxlength="120" style="padding:6px 8px; flex:1; min-width:100px; max-width:220px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;">
                    <input type="color" class="js-fila-colore-input" value="${hexSafe}" title="Colore fila" aria-label="Colore fila" style="padding:0; height:32px; width:44px; border:1px solid #ccc; border-radius:4px; cursor:pointer;">
                </div>
            </td>
            <td>${ordine}</td>
            <td>
                <a href="${baseUrl}/gare/rimuoviFilaPit/${idNum}/${garaId}" class="btn btn-danger js-rimuovi-fila" style="text-decoration:none; padding:5px 10px; font-size:0.9em; border-radius:4px;">Rimuovi</a>
            </td>
        `;
    }

    async function salvaFilaDaRiga(tr) {
        const filaId = tr.getAttribute('data-fila-id');
        const inpNome = tr.querySelector('.js-fila-nome-input');
        const inpColore = tr.querySelector('.js-fila-colore-input');
        const preview = tr.querySelector('.js-fila-anteprima-colore');
        if (!filaId || !inpNome || !inpColore) {
            return;
        }
        const nome = inpNome.value.trim();
        if (!nome) {
            alert('Il nome della fila non può essere vuoto.');
            return;
        }
        const fd = new FormData();
        fd.append('file_pit_id', filaId);
        fd.append('nome_colore', nome);
        fd.append('colore_hex', inpColore.value || '#343a40');

        try {
            const response = await fetchAjax(`${baseUrl}/gare/apiAggiornaFila/${garaId}`, {
                method: 'POST',
                body: fd
            });
            const d = response.data;
            if (d && preview && d.colore_hex) {
                preview.style.background = d.colore_hex;
            }
            if (d && d.nome_colore !== undefined) {
                inpNome.value = d.nome_colore;
            }
            if (d && d.colore_hex !== undefined) {
                inpColore.value = d.colore_hex;
            }
        } catch (errore) {
            alert(errore.message);
        }
    }

    function inizializzaAutosaveFila() {
        const tbody = document.getElementById('tbody-file-pit');
        if (!tbody) {
            return;
        }
        tbody.addEventListener('focusout', function (ev) {
            const t = ev.target;
            if (t && t.classList && t.classList.contains('js-fila-nome-input')) {
                const tr = t.closest('tr');
                if (tr) {
                    salvaFilaDaRiga(tr);
                }
            }
        });
        tbody.addEventListener('change', function (ev) {
            const t = ev.target;
            if (t && t.classList && t.classList.contains('js-fila-colore-input')) {
                const tr = t.closest('tr');
                if (tr) {
                    salvaFilaDaRiga(tr);
                }
            }
        });
    }

    function aggiornaVisibilitaPlaceholder(tbodyId, emptyId, tableId) {
        const tbody = document.getElementById(tbodyId);
        const empty = document.getElementById(emptyId);
        if (!tbody || !empty) {
            return;
        }
        const haRighe = tbody.children.length > 0;
        empty.style.display = haRighe ? 'none' : '';
        if (tableId) {
            const table = document.getElementById(tableId);
            if (table) {
                table.style.display = haRighe ? '' : 'none';
            }
        }
    }

    function mostraStatoSalvataggio(testo, tipo) {
        if (!autosaveStatus) {
            return;
        }
        autosaveStatus.textContent = testo;
        autosaveStatus.classList.remove('success', 'error');
        if (tipo) {
            autosaveStatus.classList.add(tipo);
        }

        if (autosaveTimerLabel) {
            clearTimeout(autosaveTimerLabel);
        }
        autosaveTimerLabel = setTimeout(() => {
            autosaveStatus.textContent = 'Modifica un campo per salvare automaticamente.';
            autosaveStatus.classList.remove('success', 'error');
        }, 2000);
    }

    async function fetchAjax(url, options) {
        const risposta = await fetch(url, {
            ...options,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                ...(options && options.headers ? options.headers : {})
            }
        });

        let body = null;
        try {
            body = await risposta.json();
        } catch (e) {
            body = null;
        }

        if (!risposta.ok || !body || body.status !== 'success') {
            const messaggio = body && body.message ? body.message : 'Operazione non riuscita.';
            throw new Error(messaggio);
        }

        return body;
    }

    async function salvaParametriGara() {
        if (!formParametri) {
            return;
        }

        const formData = new FormData(formParametri);
        const payload = {
            nome_gara: formData.get('nome_gara') || '',
            data_evento: formData.get('data_evento') || '',
            durata_minuti: Number(formData.get('durata_minuti') || 0),
            min_stint: Number(formData.get('min_stint') || 0),
            tempo_minimo_pit: Number(formData.get('tempo_minimo_pit') || 0),
            durata_max_stint: Number(formData.get('durata_max_stint') || 0),
            durata_min_stint: formData.get('durata_min_stint') || '',
            tempo_max_pilota: Number(formData.get('tempo_max_pilota') || 0),
            tempo_min_pilota: Number(formData.get('tempo_min_pilota') || 0),
            mio_team_id: formData.get('mio_team_id') || ''
        };

        mostraStatoSalvataggio('Salvataggio in corso...', '');
        try {
            await fetchAjax(`${baseUrl}/gare/apiAggiornaParametri/${garaId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            mostraStatoSalvataggio('Salvato!', 'success');
        } catch (errore) {
            mostraStatoSalvataggio(`Errore: ${errore.message}`, 'error');
        }
    }

    function inizializzaAutosaveParametri() {
        if (!formParametri) {
            return;
        }

        const btnManuale = document.getElementById('btn-salva-parametri-manuale');
        if (btnManuale) {
            btnManuale.addEventListener('click', salvaParametriGara);
        }

        const campi = formParametri.querySelectorAll('input, select, textarea');
        campi.forEach((campo) => {
            if (campo.name === 'gara_id') {
                return;
            }
            const trigger = () => {
                if (autosaveTimeout) {
                    clearTimeout(autosaveTimeout);
                }
                autosaveTimeout = setTimeout(salvaParametriGara, 350);
            };
            campo.addEventListener('change', trigger);
            campo.addEventListener('blur', trigger);
        });
    }

    async function eseguiAggiungiPilotaAlRoster() {
        if (!formAggiungiPilota) {
            return;
        }
        const formData = new FormData(formAggiungiPilota);

        try {
            const response = await fetchAjax(formAggiungiPilota.action, {
                method: 'POST',
                body: formData
            });

            const pilota = response.data;
            if (!pilota || !pilota.id) {
                return;
            }

            const tbody = document.getElementById('tbody-piloti-roster');
            if (tbody) {
                const tr = document.createElement('tr');
                tr.id = `pilota-row-${pilota.id}`;
                tr.setAttribute('data-associazione-id', pilota.id);
                tr.setAttribute('data-pilota-id', pilota.pilota_id);
                tr.setAttribute('data-nome-pilota', `${pilota.cognome} ${pilota.nome}`);
                tr.innerHTML = `
                    <td>${escapeHtml(pilota.cognome)} ${escapeHtml(pilota.nome)}</td>
                    <td>
                        <a href="${baseUrl}/gare/rimuoviPilotaGara/${pilota.id}/${garaId}" class="btn btn-danger js-rimuovi-pilota" style="text-decoration:none; padding:5px 10px; font-size:0.9em; border-radius:4px;">Rimuovi</a>
                    </td>
                `;
                tbody.appendChild(tr);
            }

            const selectPilota = document.getElementById('pilota_id');
            if (selectPilota) {
                const optionToRemove = selectPilota.querySelector(`option[value="${String(pilota.pilota_id)}"]`);
                if (optionToRemove) {
                    optionToRemove.remove();
                }
                selectPilota.value = '';
            }

            aggiornaVisibilitaPlaceholder('tbody-piloti-roster', 'empty-piloti-roster', 'tabella-piloti-roster');
        } catch (errore) {
            alert(errore.message);
        }
    }

    function inizializzaAggiungiPilota() {
        if (!formAggiungiPilota) {
            return;
        }
        bloccaSubmitReload(formAggiungiPilota);
        const btn = document.getElementById('btn-aggiungi-roster-pilota');
        if (btn) {
            btn.addEventListener('click', eseguiAggiungiPilotaAlRoster);
        }
    }

    async function eseguiAggiungiFilaPit() {
        if (!formAggiungiFilaPit) {
            return;
        }
        const formData = new FormData(formAggiungiFilaPit);

        try {
            const response = await fetchAjax(formAggiungiFilaPit.action, {
                method: 'POST',
                body: formData
            });

            const fila = response.data;
            if (!fila || !fila.id) {
                return;
            }

            const tbody = document.getElementById('tbody-file-pit');
            if (tbody) {
                const tr = document.createElement('tr');
                tr.id = `fila-row-${fila.id}`;
                tr.setAttribute('data-fila-id', fila.id);
                tr.innerHTML = htmlCelleFilaEditable(fila);
                tbody.appendChild(tr);
            }

            formAggiungiFilaPit.reset();
            const colore = document.getElementById('colore_hex');
            if (colore) {
                colore.value = '#343a40';
            }

            aggiornaVisibilitaPlaceholder('tbody-file-pit', 'empty-file-pit', 'tabella-file-pit');
        } catch (errore) {
            alert(errore.message);
        }
    }

    function inizializzaAggiungiFilaPit() {
        if (!formAggiungiFilaPit) {
            return;
        }
        bloccaSubmitReload(formAggiungiFilaPit);
        const btn = document.getElementById('btn-aggiungi-fila-pit');
        if (btn) {
            btn.addEventListener('click', eseguiAggiungiFilaPit);
        }
    }

    async function eseguiIscrizioneTeam() {
        if (!formIscriviTeam) {
            return;
        }
        const formData = new FormData(formIscriviTeam);

        try {
            const response = await fetchAjax(formIscriviTeam.action, {
                method: 'POST',
                body: formData
            });

            const iscrizione = response.data;
            if (!iscrizione || !iscrizione.id) {
                return;
            }

            const tbody = document.getElementById('tbody-iscritti');
            if (tbody) {
                const tr = document.createElement('tr');
                tr.id = `iscrizione-row-${iscrizione.id}`;
                tr.setAttribute('data-iscrizione-id', iscrizione.id);
                tr.setAttribute('data-team-id', iscrizione.team_id);
                tr.setAttribute('data-nome-team', iscrizione.nome_team);
                tr.setAttribute('data-numero-gara', iscrizione.numero_gara);
                tr.innerHTML = `
                    <td>${escapeHtml(iscrizione.numero_gara)}</td>
                    <td>${escapeHtml(iscrizione.nome_team)}</td>
                    <td>
                        <a href="${baseUrl}/gare/modificaIscrizione/${iscrizione.id}" class="btn" style="background:#ffc107; color:black; text-decoration:none; padding:5px 10px; font-size:0.9em; border-radius:4px;">Modifica</a>
                        <a href="${baseUrl}/gare/rimuoviIscrizione/${iscrizione.id}/${garaId}" class="btn btn-danger js-rimuovi-iscrizione" style="text-decoration:none; padding:5px 10px; font-size:0.9em; border-radius:4px; color:white;">Rimuovi</a>
                    </td>
                `;
                tbody.appendChild(tr);
            }

            const selectTeam = document.getElementById('team_id');
            if (selectTeam) {
                const optionToRemove = selectTeam.querySelector(`option[value="${String(iscrizione.team_id)}"]`);
                if (optionToRemove) {
                    optionToRemove.remove();
                }
                selectTeam.value = '';
            }
            const numero = document.getElementById('numero_gara');
            if (numero) {
                numero.value = '';
            }

            aggiornaVisibilitaPlaceholder('tbody-iscritti', 'empty-iscritti', 'tabella-iscritti');
        } catch (errore) {
            alert(errore.message);
        }
    }

    function inizializzaIscrizioneTeam() {
        if (!formIscriviTeam) {
            return;
        }
        bloccaSubmitReload(formIscriviTeam);
        const btn = document.getElementById('btn-aggiungi-iscrizione-team');
        if (btn) {
            btn.addEventListener('click', eseguiIscrizioneTeam);
        }
    }

    function inizializzaModalNuovoPilota() {
        const btn = document.getElementById('btn-modal-salva-pilota');
        if (!btn) return;
        btn.addEventListener('click', () => {
            const form = document.getElementById('form-modal-nuovo-pilota');
            if (form) form.submit();
        });
    }

    /**
     * Inizializza le checkbox per gestire lo stato 'gestito' dei team.
     */
    function inizializzaCheckboxGestito() {
        console.log('DEBUG: inizializzaCheckboxGestito() called');
        console.log('DEBUG: baseUrl =', baseUrl);
        console.log('DEBUG: garaId =', garaId);
        
        const checkboxes = document.querySelectorAll('.checkbox-gestito');
        console.log('DEBUG: checkboxes found:', checkboxes.length);
        
        checkboxes.forEach((checkbox, index) => {
            console.log(`DEBUG: checkbox ${index}:`, checkbox.dataset.iscrittoId, checkbox.checked);
            
            checkbox.addEventListener('change', function() {
                const iscrittoId = this.dataset.iscrittoId;
                const isGestito = this.checked ? 1 : 0;
                
                console.log('DEBUG: checkbox change - iscrittoId:', iscrittoId, 'is_gestito:', isGestito);
                console.log('DEBUG: API URL:', `${baseUrl}/gare/apiAggiornaGestito/${garaId}`);
                
                // Mostra stato di caricamento
                const originalDisabled = this.disabled;
                this.disabled = true;
                
                // Chiama API per aggiornare lo stato
                fetch(`${baseUrl}/gare/apiAggiornaGestito/${garaId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        iscritto_id: parseInt(iscrittoId),
                        is_gestito: isGestito
                    })
                })
                .then(response => {
                    console.log('DEBUG: response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('DEBUG: response data:', data);
                    if (data.status === 'success') {
                        // Successo: mostra notifica positiva
                        mostraNotifica(data.message, 'success');
                    } else {
                        // Errore: ripristina stato precedente
                        this.checked = !this.checked;
                        mostraNotifica(data.message || 'Errore durante l\'aggiornamento', 'error');
                    }
                })
                .catch(error => {
                    console.error('DEBUG: AJAX error:', error);
                    // Ripristina stato precedente in caso di errore di rete
                    this.checked = !this.checked;
                    mostraNotifica('Errore di connessione: ' + error.message, 'error');
                })
                .finally(() => {
                    // Ripristina stato del checkbox
                    this.disabled = originalDisabled;
                });
            });
        });
    }

    /**
     * Mostra una notifica temporanea all'utente.
     */
    function mostraNotifica(messaggio, tipo = 'info') {
        // Rimuovi notifiche esistenti
        const notificaEsistente = document.querySelector('.notifica-flottante');
        if (notificaEsistente) {
            notificaEsistente.remove();
        }
        
        // Crea nuova notifica
        const notifica = document.createElement('div');
        notifica.className = 'notifica-flottante';
        notifica.textContent = messaggio;
        
        // Stile in base al tipo
        switch(tipo) {
            case 'success':
                notifica.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: #28a745;
                    color: white;
                    padding: 12px 20px;
                    border-radius: 6px;
                    font-weight: bold;
                    z-index: 9999;
                    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                `;
                break;
            case 'error':
                notifica.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: #dc3545;
                    color: white;
                    padding: 12px 20px;
                    border-radius: 6px;
                    font-weight: bold;
                    z-index: 9999;
                    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                `;
                break;
            default:
                notifica.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: #17a2b8;
                    color: white;
                    padding: 12px 20px;
                    border-radius: 6px;
                    font-weight: bold;
                    z-index: 9999;
                    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                `;
        }
        
        document.body.appendChild(notifica);
        
        // Rimuovi automaticamente dopo 3 secondi
        setTimeout(() => {
            if (notifica.parentNode) {
                notifica.remove();
            }
        }, 3000);
    }

    bloccaSubmitReload(formParametri);
    inizializzaAutosaveParametri();
    inizializzaAggiungiPilota();
    inizializzaAggiungiFilaPit();
    inizializzaAutosaveFila();
    inizializzaIscrizioneTeam();
    inizializzaModalNuovoPilota();
    inizializzaCheckboxGestito();
})();
