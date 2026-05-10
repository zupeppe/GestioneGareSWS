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

    function inizializzaModalNuovoTeam() {
        const form = document.getElementById('form-modal-nuovo-team');
        const btn = document.getElementById('btn-modal-salva-team');
        if (!form || !btn) {
            return;
        }
        bloccaSubmitReload(form);
        btn.addEventListener('click', async function () {
            const fd = new FormData(form);
            try {
                const response = await fetchAjax(`${baseUrl}/teams/store`, {
                    method: 'POST',
                    body: fd
                });
                const team = response.data;
                const selectTeam = document.getElementById('team_id');
                if (selectTeam && team && team.id) {
                    const opt = document.createElement('option');
                    opt.value = String(team.id);
                    opt.textContent = team.nome_team;
                    selectTeam.appendChild(opt);
                    selectTeam.value = String(team.id);
                }
                form.reset();
                if (typeof closeModal === 'function') {
                    closeModal('modal-nuovo-team');
                }
            } catch (errore) {
                alert(errore.message);
            }
        });
    }

    function inizializzaModalNuovoPilota() {
        const form = document.getElementById('form-modal-nuovo-pilota');
        const btn = document.getElementById('btn-modal-salva-pilota');
        if (!form || !btn) {
            return;
        }
        bloccaSubmitReload(form);
        btn.addEventListener('click', async function () {
            const fd = new FormData(form);
            try {
                const response = await fetchAjax(`${baseUrl}/piloti/store`, {
                    method: 'POST',
                    body: fd
                });
                const pilota = response.data;
                const selectPilota = document.getElementById('pilota_id');
                if (selectPilota && pilota && pilota.id) {
                    const opt = document.createElement('option');
                    opt.value = String(pilota.id);
                    opt.textContent = `${pilota.cognome} ${pilota.nome}`;
                    selectPilota.appendChild(opt);
                    selectPilota.value = String(pilota.id);
                }
                form.reset();
                if (typeof closeModal === 'function') {
                    closeModal('modal-nuovo-pilota');
                }
            } catch (errore) {
                alert(errore.message);
            }
        });
    }

    async function gestisciClickRimuovi(event) {
        const target = event.target.closest('a.js-rimuovi-pilota, a.js-rimuovi-fila, a.js-rimuovi-iscrizione');
        if (!target) {
            return;
        }
        event.preventDefault();

        let messaggio = 'Confermi la rimozione?';
        if (target.classList.contains('js-rimuovi-pilota')) {
            messaggio = 'Rimuovere dal roster?';
        } else if (target.classList.contains('js-rimuovi-fila')) {
            messaggio = 'Rimuovere questa fila?';
        } else if (target.classList.contains('js-rimuovi-iscrizione')) {
            messaggio = 'Sicuro di voler rimuovere questo team dalla gara?';
        }

        if (!window.confirm(messaggio)) {
            return;
        }

        try {
            const response = await fetchAjax(target.href, { method: 'GET' });

            const row = target.closest('tr');
            if (row) {
                if (target.classList.contains('js-rimuovi-pilota')) {
                    const selectPilota = document.getElementById('pilota_id');
                    const pilotaId = row.getAttribute('data-pilota-id');
                    const nomePilota = row.getAttribute('data-nome-pilota');
                    if (selectPilota && pilotaId && nomePilota && !selectPilota.querySelector(`option[value="${pilotaId}"]`)) {
                        const option = document.createElement('option');
                        option.value = pilotaId;
                        option.textContent = nomePilota;
                        selectPilota.appendChild(option);
                    }
                } else if (target.classList.contains('js-rimuovi-iscrizione')) {
                    const selectTeam = document.getElementById('team_id');
                    const teamId = row.getAttribute('data-team-id');
                    const nomeTeam = row.getAttribute('data-nome-team');
                    if (selectTeam && teamId && nomeTeam && !selectTeam.querySelector(`option[value="${teamId}"]`)) {
                        const option = document.createElement('option');
                        option.value = teamId;
                        option.textContent = nomeTeam;
                        selectTeam.appendChild(option);
                    }
                }
                row.remove();
            } else if (response && response.data && target.classList.contains('js-rimuovi-iscrizione')) {
                const selectTeam = document.getElementById('team_id');
                if (selectTeam && response.data.team_id && response.data.nome_team && !selectTeam.querySelector(`option[value="${String(response.data.team_id)}"]`)) {
                    const option = document.createElement('option');
                    option.value = String(response.data.team_id);
                    option.textContent = String(response.data.nome_team);
                    selectTeam.appendChild(option);
                }
            }

            aggiornaVisibilitaPlaceholder('tbody-piloti-roster', 'empty-piloti-roster', 'tabella-piloti-roster');
            aggiornaVisibilitaPlaceholder('tbody-file-pit', 'empty-file-pit', 'tabella-file-pit');
            aggiornaVisibilitaPlaceholder('tbody-iscritti', 'empty-iscritti', 'tabella-iscritti');
        } catch (errore) {
            alert(errore.message);
        }
    }

    document.addEventListener('click', gestisciClickRimuovi);
    bloccaSubmitReload(formParametri);
    inizializzaAutosaveParametri();
    inizializzaAggiungiPilota();
    inizializzaAggiungiFilaPit();
    inizializzaAutosaveFila();
    inizializzaIscrizioneTeam();
    inizializzaModalNuovoTeam();
    inizializzaModalNuovoPilota();
})();
