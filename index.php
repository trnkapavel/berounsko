<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
<style>
    /* ZÁKLADNÍ PROMĚNNÉ A STYLY */
    :root {
        --c-blue: #30B0FF;
        --c-green: #80C024;
        --c-orange: #FF9200;
        --c-red: #FF4444; /* Nová barva pro těžkou náročnost */
        --c-text: #000000;
        --font-main: 'Montserrat', sans-serif;
    }

    /* MODAL LAYOUT */
    .modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.7); z-index: 1000;
        display: none; justify-content: center; align-items: center;
    }

    .modal-window {
        background: #fff; width: 90%; max-width: 1000px;
        max-height: 90vh; overflow-y: auto;
        display: flex; flex-direction: row;
        border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        font-family: var(--font-main); color: var(--c-text);
        position: relative;
    }

    .modal-left {
        flex: 1; background-size: cover; background-position: center;
        min-height: 300px; position: relative;
    }
    
    .modal-left::after {
        content: ''; position: absolute; top:0; left:0; right:0; bottom:0;
        background: linear-gradient(to bottom, transparent 70%, rgba(0,0,0,0.6));
    }

    .modal-right {
        flex: 1; padding: 40px; display: flex; flex-direction: column; position: relative;
    }

    .close-modal {
        position: absolute; right: 20px; top: 15px; font-size: 24px; cursor: pointer; color: #333; z-index: 10;
    }

    h2 { font-weight: 600; margin-bottom: 20px; color: var(--c-blue); }
    
    /* PŘEPÍNAČE */
    .walk-selector { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; }
    .walk-btn {
        padding: 8px 15px; border: 2px solid #ddd; border-radius: 20px;
        cursor: pointer; font-size: 0.9em; transition: 0.3s; background: #fff;
    }
    .walk-btn.active {
        border-color: var(--c-orange); background: var(--c-orange); color: #fff; font-weight: 600;
    }

    /* ANOTACE */
    .annotation-box ul { padding-left: 20px; margin-bottom: 20px; }
    .annotation-box li { margin-bottom: 5px; font-size: 0.95em; line-height: 1.4; }

    /* INFO ROW (Upraveno pro indikátor) */
    .info-row {
        background: #f4f4f4; padding: 15px; border-radius: 5px; margin-bottom: 20px;
        border-left: 4px solid var(--c-blue);
        display: grid; grid-template-columns: 1fr 1fr; gap: 15px; /* Grid layout */
    }
    .info-item { display: flex; flex-direction: column; }
    .info-label { font-weight: 400; font-size: 0.85em; color: #666; margin-bottom: 3px; }
    .info-value { font-weight: 600; font-size: 1em; color: #000; }

    /* NOVÉ STYLY PRO INDIKÁTOR NÁROČNOSTI */
    .difficulty-wrapper {
        display: flex; gap: 4px; align-items: center; margin-top: 2px;
    }
    .diff-dot {
        width: 12px; height: 12px; border-radius: 50%;
        background-color: #ddd; /* Neaktivní šedá */
        transition: background-color 0.3s;
    }
    /* Barvy pro aktivní puntíky (přiřazuje JS) */
    .diff-dot.active-green { background-color: var(--c-green); }
    .diff-dot.active-orange { background-color: var(--c-orange); }
    .diff-dot.active-red { background-color: var(--c-red); }

    /* FORMULÁŘ */
    .form-group { margin-bottom: 15px; }
    label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.9em; }
    input, select {
        width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;
        font-family: var(--font-main); font-size: 1em;
    }

    .price-display {
        font-size: 1.5em; font-weight: 600; color: var(--c-green); text-align: right; margin: 20px 0;
    }

    .btn-submit {
        background: var(--c-blue); color: white; border: none; padding: 15px;
        width: 100%; font-size: 1.1em; font-weight: 600; border-radius: 4px; cursor: pointer;
        transition: background 0.2s;
    }
    .btn-submit:hover { background: #2090dd; }

    /* SUCCESS STAV */
    .success-message {
        display: none; flex-direction: column; align-items: center; justify-content: center;
        text-align: center; height: 100%; animation: fadeIn 0.5s; padding-top: 40px;
    }
    .checkmark-circle {
        width: 80px; height: 80px; position: relative; display: inline-block;
        border-radius: 50%; border: 3px solid #80C024; margin-bottom: 20px;
    }
    .checkmark-circle::after {
        content: ''; position: absolute; top: 50%; left: 50%; width: 25px; height: 13px;
        border-left: 4px solid #80C024; border-bottom: 4px solid #80C024;
        transform: translate(-50%, -65%) rotate(-45deg);
    }
    .success-text h3 { color: #80C024; font-size: 1.8em; margin: 0 0 10px 0; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

    /* RESPONZIVITA */
    @media (max-width: 768px) {
        .modal-window { flex-direction: column; width: 95%; max-height: 95vh; }
        .modal-left { height: 200px; flex: none; }
        .modal-right { padding: 20px; }
        .info-row { grid-template-columns: 1fr; gap: 10px; } /* Na mobilu pod sebou */
    }
</style>

<button onclick="document.getElementById('bookingModal').style.display='flex'">Rezervovat vycházku</button>

<div id="bookingModal" class="modal-overlay">
    <div class="modal-window">
        <span class="close-modal" onclick="closeAndReset()">&times;</span>
        
        <div class="modal-left" id="modalImage" style="background-image: url('img/cesky-kras.jpg');"></div>

        <div class="modal-right">
            
            <div id="formContent">
                <h2>Komentované vycházky</h2>

                <label>Vyberte trasu:</label>
                <div class="walk-selector">
                    <div class="walk-btn active" data-walk="kras" onclick="changeWalk('kras')">Český kras</div>
                    <div class="walk-btn" data-walk="svatojan" onclick="changeWalk('svatojan')">Svatojanský okruh</div>
                    <div class="walk-btn" data-walk="krivoklat" onclick="changeWalk('krivoklat')">Křivoklátsko</div>
                    <div class="walk-btn" data-walk="alkazar" onclick="changeWalk('alkazar')">Alkazar</div>
                </div>

                <div class="annotation-box" id="annotationContent"></div>

                <div class="info-row">
                    <div class="info-item">
                        <span class="info-label">Průvodce:</span>
                        <span class="info-value" id="guideName">Jan Novák</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Datum:</span>
                        <span class="info-value" id="walkDate">20. 5. 2024</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Délka trasy:</span>
                        <span class="info-value" id="walkDist">8 km</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Náročnost:</span>
                        <div class="difficulty-wrapper" id="diffContainer">
                            </div>
                    </div>
                </div>

                <form id="reservationForm" onsubmit="submitForm(event)">
                    <input type="hidden" name="walk_id" id="inputWalkId" value="kras">
                    <input type="hidden" name="walk_name" id="inputWalkName" value="">
                    
                    <div class="form-group">
                        <label>Váš e-mail</label>
                        <input type="email" name="email" required placeholder="jan.novak@email.cz" value="trnkapavel@gmail.com">
                    </div>

                    <div class="form-group">
                        <label>Počet účastníků</label>
                        <select name="count" id="participantCount" onchange="calculatePrice()">
                        </select>
                    </div>

                    <div class="price-display" id="finalPrice">Zdarma</div>

                    <button type="submit" class="btn-submit" id="submitBtn">Zaplatit a rezervovat místo</button>
                </form>
            </div>

            <div id="successView" class="success-message">
                <div class="checkmark-circle"></div>
                <div class="success-text">
                    <h3>Odesláno!</h3>
                    <p>Rezervace byla úspěšně vytvořena.</p>
                    <p>Potvrzení a platební údaje dorazí na Váš e-mail.</p>
                    <button onclick="closeAndReset()" class="btn-submit" style="margin-top: 30px; background: #555;">Zavřít okno</button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
// DATA PROCHÁZEK - Přidána vlastnost 'difficulty' (1-5)
const walks = {
    'kras': {
        title: 'CHKO Český kras',
        img: 'https://images.unsplash.com/photo-1605199216405-0239b35d143a?w=800&q=80', 
        guide: 'RNDr. Petr Skála',
        date: '15. 4. 2024',
        distance: '8,5 km',
        difficulty: 3, // Střední (Oranžová)
        pricePerPerson: 0,
        desc: [
            'Unikátní vápencová krajina a krasové jevy.',
            'Návštěva národní přírodní rezervace Karlštejn.',
            'Vyhlídky do hlubokých kaňonů.',
            'Historie těžby vápence v lomech Amerika.'
        ]
    },
    'svatojan': {
        title: 'Svatojanský okruh',
        img: 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=800&q=80', 
        guide: 'Mgr. Jana Veselá',
        date: '22. 4. 2024',
        distance: '6 km',
        difficulty: 5, // Těžší (Oranžová/Červená hranice)
        pricePerPerson: 100,
        desc: [
            'Poutní místo Svatý Jan pod Skálou.',
            'Výstup na vyhlídku u kříže (náročnější stoupání).',
            'Prohlídka benediktinského kláštera.',
            'Návštěva jeskyně sv. Ivana.'
        ]
    },
    'krivoklat': {
        title: 'CHKO Křivoklátsko',
        img: 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=800&q=80', 
        guide: 'Ing. Karel Les',
        date: '12. 5. 2024',
        distance: '11 km',
        difficulty: 2, // Lehká/Střední (Zelená)
        pricePerPerson: 100,
        desc: [
            'Hluboké lesy a biosférická rezervace.',
            'Vyhlídky na řeku Berounku.',
            'Pozorování lesní zvěře v oboře.',
            'Dlouhá, ale mírná trasa.'
        ]
    },
    'alkazar': {
        title: 'Alkazar',
        img: 'https://images.unsplash.com/photo-1560097020-591b96717792?w=800&q=80', 
        guide: 'Tomáš Průvodce',
        date: '19. 5. 2024',
        distance: '5,5 km',
        difficulty: 1, // Velmi lehká (Zelená)
        pricePerPerson: 100,
        desc: [
            'Bývalý lom Alkazar a jeho historie.',
            'Rovina podél řeky Berounky.',
            'Horolezecká stěna a podzemní štoly.',
            'Vhodné pro rodiny s dětmi i kočárky.'
        ]
    }
};

const select = document.getElementById('participantCount');
for (let i = 1; i <= 20; i++) {
    let opt = document.createElement('option');
    opt.value = i; opt.innerHTML = i; select.appendChild(opt);
}

function changeWalk(id) {
    const data = walks[id];
    
    document.querySelectorAll('.walk-btn').forEach(b => b.classList.remove('active'));
    document.querySelector(`.walk-btn[data-walk="${id}"]`).classList.add('active');
    
    document.getElementById('modalImage').style.backgroundImage = `url('${data.img}')`;
    document.getElementById('guideName').innerText = data.guide;
    document.getElementById('walkDate').innerText = data.date;
    document.getElementById('walkDist').innerText = data.distance;

    // --- LOGIKA PRO VYKRESLENÍ PUNTÍKŮ ---
    const diffContainer = document.getElementById('diffContainer');
    diffContainer.innerHTML = ''; // Vymazat staré
    
    // Určení barvy podle stupně
    let activeClass = 'active-orange'; // Default
    if (data.difficulty <= 2) activeClass = 'active-green';
    if (data.difficulty >= 5) activeClass = 'active-red';

    // Generování 5 puntíků
    for(let i = 1; i <= 5; i++) {
        let dot = document.createElement('div');
        dot.className = 'diff-dot';
        if (i <= data.difficulty) {
            dot.classList.add(activeClass);
        }
        diffContainer.appendChild(dot);
    }
    // -------------------------------------

    document.getElementById('inputWalkId').value = id;
    document.getElementById('inputWalkName').value = data.title;
    
    let ul = '<ul>';
    data.desc.forEach(point => { ul += `<li>${point}</li>`; });
    ul += '</ul>';
    document.getElementById('annotationContent').innerHTML = ul;

    calculatePrice();
}

function calculatePrice() {
    const id = document.getElementById('inputWalkId').value;
    const count = parseInt(document.getElementById('participantCount').value);
    const pricePerPerson = walks[id].pricePerPerson;
    let total = count * pricePerPerson;
    
    if (total === 0) {
        document.getElementById('finalPrice').innerText = "Zdarma";
        document.getElementById('submitBtn').innerText = "Rezervovat zdarma";
    } else {
        document.getElementById('finalPrice').innerText = total + " Kč";
        document.getElementById('submitBtn').innerText = "Zaplatit (" + total + " Kč)";
    }
}

function closeAndReset() {
    document.getElementById('bookingModal').style.display = 'none';
    setTimeout(() => {
        document.getElementById('formContent').style.display = 'block';
        document.getElementById('successView').style.display = 'none';
        document.getElementById('reservationForm').reset();
        calculatePrice();
    }, 300);
}

function submitForm(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    const originalText = btn.innerText;
    btn.disabled = true;
    btn.innerText = "Odesílám...";

    const formData = new FormData(document.getElementById('reservationForm'));

    fetch('rezervace.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            document.getElementById('formContent').style.display = 'none';
            document.getElementById('successView').style.display = 'flex';
        } else {
            alert("Chyba: " + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("Chyba komunikace.");
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerText = originalText;
    });
}

// Init
changeWalk('kras');
</script>