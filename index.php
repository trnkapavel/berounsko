<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rezervace vycházek Berounsko</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* RESET A ZÁKLAD */
        body { margin: 0; padding: 0; font-family: 'Montserrat', sans-serif; }
        * { box-sizing: border-box; }

        :root {
            --c-blue: #30B0FF;
            --c-green: #80C024;
            --c-orange: #FF9200;
            --c-red: #FF4444;
            --c-text: #000000;
        }

        /* --- HERO SEKCE --- */
        .hero-section {
            position: relative; width: 100%; height: 100vh;
            background-image: url('https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?w=1600&q=80');
            background-size: cover; background-position: center;
            display: flex; justify-content: center; align-items: center;
        }

        .hero-overlay {
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.6); z-index: 1;
        }

        .hero-content { position: relative; z-index: 2; text-align: center; }

        .hero-btn {
            background-color: var(--c-green); color: white;
            font-size: 1.5em; font-weight: 700; padding: 20px 50px;
            border: none; border-radius: 50px; cursor: pointer;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            transition: transform 0.2s, background 0.3s;
            text-transform: uppercase; letter-spacing: 1px;
        }
        .hero-btn:hover { background-color: #6da61e; transform: scale(1.05); }

        /* --- MODAL LAYOUT --- */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.8); z-index: 1000;
            display: none; justify-content: center; align-items: center;
            padding: 20px;
        }

        .modal-window {
            background: #fff; width: 100%; max-width: 1000px;
            max-height: 90vh; /* Maximální výška okna */
            display: flex; flex-direction: row;
            border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            color: var(--c-text); position: relative;
            overflow: hidden; /* Důležité pro sticky footer uvnitř */
        }

        .modal-left {
            flex: 1; background-size: cover; background-position: center;
            min-height: 300px; position: relative;
        }
        
        .modal-right {
            flex: 1; padding: 40px; 
            display: flex; flex-direction: column; 
            position: relative;
            overflow-y: auto; /* Scrollování pouze v pravé části */
        }

        .close-modal {
            position: absolute; right: 20px; top: 15px; font-size: 28px; cursor: pointer; color: #333; z-index: 20;
            background: rgba(255,255,255,0.9); width: 40px; height: 40px; text-align: center; line-height: 40px; border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        h2 { font-weight: 600; margin-top: 0; margin-bottom: 15px; color: var(--c-blue); line-height: 1.3; font-size: 1.6em; }
        
        /* PŘEPÍNAČE */
        .walk-selector { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px; }
        .walk-btn {
            padding: 8px 12px; border: 1px solid #ddd; border-radius: 20px;
            cursor: pointer; font-size: 0.85em; transition: 0.3s; background: #fff;
            flex-grow: 1; text-align: center;
        }
        .walk-btn.active {
            border-color: var(--c-orange); background: var(--c-orange); color: #fff; font-weight: 600;
        }

        /* ROZBALOVACÍ ANOTACE (NOVÉ) */
        .annotation-wrapper { margin-bottom: 20px; }
        .annotation-toggle {
            color: var(--c-blue); font-weight: 600; cursor: pointer; font-size: 0.9em;
            display: flex; align-items: center; gap: 5px;
        }
        .annotation-toggle::after { content: '▼'; font-size: 0.7em; transition: transform 0.3s; }
        .annotation-toggle.open::after { transform: rotate(180deg); }
        
        .annotation-content {
            display: none; padding-top: 10px;
        }
        .annotation-content ul { padding-left: 20px; margin: 0; }
        .annotation-content li { margin-bottom: 5px; font-size: 0.95em; line-height: 1.5; color: #444; }

        /* INFO ROW */
        .info-row {
            background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 25px;
            border-left: 5px solid var(--c-blue);
            display: grid; grid-template-columns: 1fr 1fr; gap: 15px;
        }
        .info-item { display: flex; flex-direction: column; }
        .info-label { font-weight: 400; font-size: 0.75em; color: #777; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-value { font-weight: 700; font-size: 0.95em; color: #222; margin-top: 2px;}
        .difficulty-wrapper { display: flex; gap: 4px; align-items: center; margin-top: 5px; }
        .diff-dot { width: 12px; height: 12px; border-radius: 50%; background-color: #e0e0e0; }
        .diff-dot.active-green { background-color: var(--c-green); }
        .diff-dot.active-orange { background-color: var(--c-orange); }
        .diff-dot.active-red { background-color: var(--c-red); }

        /* FORMULÁŘ */
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9em; }
        input, select {
            width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px;
            font-family: 'Montserrat', sans-serif; font-size: 1em; background: #fff;
        }

        /* STICKY FOOTER (NOVÉ) */
        .sticky-footer {
            margin-top: auto; /* Odtlačí se dospod */
            background: #fff;
            padding-top: 20px;
            border-top: 1px solid #eee;
            /* Pro desktop normální zobrazení */
        }

        .footer-row {
            display: flex; align-items: center; justify-content: space-between; gap: 20px;
        }

        .price-display {
            font-size: 1.5em; font-weight: 700; color: var(--c-green); white-space: nowrap;
        }

        .btn-submit {
            background: var(--c-blue); color: white; border: none; padding: 16px;
            width: 100%; font-size: 1.1em; font-weight: 700; border-radius: 6px; cursor: pointer;
            transition: background 0.2s; box-shadow: 0 4px 10px rgba(48, 176, 255, 0.25);
        }
        .btn-submit:hover { background: #2090dd; }

        /* SUCCESS STAV */
        .success-message {
            display: none; flex-direction: column; align-items: center; justify-content: center;
            text-align: center; height: 100%; padding: 40px 0;
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

        /* --- MOBILNÍ OPTIMALIZACE --- */
        @media (max-width: 768px) {
            .hero-btn { font-size: 1.2em; padding: 15px 30px; width: 80%; }
            .modal-overlay { padding: 0; } /* Fullscreen na mobilu */
            
            .modal-window { 
                flex-direction: column; 
                width: 100%; height: 100%; max-height: 100%; 
                border-radius: 0;
            }
            
            .modal-left { 
                height: 120px; min-height: 120px; flex: none; /* Menší obrázek */
            }
            
            .modal-right { 
                padding: 20px 20px 100px 20px; /* Velký padding dole pro sticky tlačítko */
                display: block; /* Zruší flex pro scroll */
            }

            .close-modal { top: 10px; right: 10px; }

            /* STICKY TLAČÍTKO NA MOBILU */
            .sticky-footer {
                position: fixed; bottom: 0; left: 0; right: 0;
                background: #fff;
                padding: 15px 20px;
                border-top: 1px solid #e0e0e0;
                box-shadow: 0 -5px 15px rgba(0,0,0,0.1);
                z-index: 100;
            }

            .info-row { 
                grid-template-columns: 1fr; gap: 10px; margin-bottom: 15px;
            }
            
            .info-item {
                flex-direction: row; justify-content: space-between; align-items: center;
                border-bottom: 1px solid #eee; padding-bottom: 5px;
            }
            .info-item:last-child { border-bottom: none; }
        }
    </style>
</head>
<body>

    <div class="hero-section">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <button class="hero-btn" onclick="document.getElementById('bookingModal').style.display='flex'">
                Rezervovat vycházku
            </button>
        </div>
    </div>

    <div id="bookingModal" class="modal-overlay">
        <div class="modal-window">
            <span class="close-modal" onclick="closeAndReset()">&times;</span>
            
            <div class="modal-left" id="modalImage" style="background-image: url('img/cesky-kras.jpg');"></div>

            <div class="modal-right">
                
                <div id="formContent">
                    <h2>Komentované vycházky</h2>

                    <div class="walk-selector">
                        <div class="walk-btn active" data-walk="kras" onclick="changeWalk('kras')">Kras</div>
                        <div class="walk-btn" data-walk="svatojan" onclick="changeWalk('svatojan')">Svatojan</div>
                        <div class="walk-btn" data-walk="krivoklat" onclick="changeWalk('krivoklat')">Křivoklát</div>
                        <div class="walk-btn" data-walk="alkazar" onclick="changeWalk('alkazar')">Alkazar</div>
                    </div>

                    <div class="annotation-wrapper">
                        <div class="annotation-toggle" onclick="toggleAnnotation()">
                            <span id="toggleText">Zobrazit podrobnosti o trase</span>
                        </div>
                        <div class="annotation-box annotation-content" id="annotationContent"></div>
                    </div>

                    <div class="info-row">
                        <div class="info-item">
                            <span class="info-label">Průvodce</span>
                            <span class="info-value" id="guideName"></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Datum</span>
                            <span class="info-value" id="walkDate"></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Délka</span>
                            <span class="info-value" id="walkDist"></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Náročnost</span>
                            <div class="difficulty-wrapper" id="diffContainer"></div>
                        </div>
                    </div>

                    <form id="reservationForm" onsubmit="submitForm(event)">
                        <input type="hidden" name="walk_id" id="inputWalkId" value="kras">
                        <input type="hidden" name="walk_name" id="inputWalkName" value="">
                        
                        <div class="form-group">
                            <label>Váš e-mail</label>
                            <input type="email" name="email" required placeholder="@" value="trnkapavel@gmail.com">
                        </div>

                        <div class="form-group">
                            <label>Počet účastníků</label>
                            <select name="count" id="participantCount" onchange="calculatePrice()">
                            </select>
                        </div>

                        <div class="sticky-footer">
                            <div class="footer-row">
                                <div class="price-display" id="finalPrice">Zdarma</div>
                                <button type="submit" class="btn-submit" id="submitBtn">Zaplatit</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div id="successView" class="success-message">
                    <div class="checkmark-circle"></div>
                    <div class="success-text">
                        <h3>Odesláno!</h3>
                        <p>Rezervace byla úspěšně vytvořena.</p>
                        <p>E-mail s potvrzením je na cestě.</p>
                        <button onclick="closeAndReset()" class="btn-submit" style="margin-top: 30px; background: #555;">Zavřít</button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
    // DATA
    const walks = {
        'kras': {
            title: 'CHKO Český kras',
            img: 'https://images.unsplash.com/photo-1605199216405-0239b35d143a?w=800&q=80', 
            guide: 'RNDr. Petr Skála',
            date: '15. 4. 2024',
            distance: '8,5 km',
            difficulty: 3, 
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
            difficulty: 4, 
            pricePerPerson: 100,
            desc: [
                'Poutní místo Svatý Jan pod Skálou.',
                'Výstup na vyhlídku u kříže (náročnější).',
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
            difficulty: 2, 
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
            difficulty: 1, 
            pricePerPerson: 100,
            desc: [
                'Bývalý lom Alkazar a jeho historie.',
                'Rovina podél řeky Berounky.',
                'Horolezecká stěna a podzemní štoly.',
                'Vhodné pro rodiny s dětmi.'
            ]
        }
    };

    const select = document.getElementById('participantCount');
    for (let i = 1; i <= 20; i++) {
        let opt = document.createElement('option');
        opt.value = i; opt.innerHTML = i; select.appendChild(opt);
    }

    // NOVÉ: Funkce pro toggle anotace
    function toggleAnnotation() {
        const content = document.getElementById('annotationContent');
        const toggleBtn = document.querySelector('.annotation-toggle');
        const text = document.getElementById('toggleText');
        
        if (content.style.display === 'block') {
            content.style.display = 'none';
            toggleBtn.classList.remove('open');
            text.innerText = "Zobrazit podrobnosti o trase";
        } else {
            content.style.display = 'block';
            toggleBtn.classList.add('open');
            text.innerText = "Skrýt podrobnosti";
        }
    }

    function changeWalk(id) {
        const data = walks[id];
        
        document.querySelectorAll('.walk-btn').forEach(b => b.classList.remove('active'));
        document.querySelector(`.walk-btn[data-walk="${id}"]`).classList.add('active');
        
        document.getElementById('modalImage').style.backgroundImage = `url('${data.img}')`;
        document.getElementById('guideName').innerText = data.guide;
        document.getElementById('walkDate').innerText = data.date;
        document.getElementById('walkDist').innerText = data.distance;

        // Reset anotace při změně
        document.getElementById('annotationContent').style.display = 'none';
        document.querySelector('.annotation-toggle').classList.remove('open');
        document.getElementById('toggleText').innerText = "Zobrazit podrobnosti o trase";

        // Puntíky
        const diffContainer = document.getElementById('diffContainer');
        diffContainer.innerHTML = ''; 
        let activeClass = 'active-orange';
        if (data.difficulty <= 2) activeClass = 'active-green';
        if (data.difficulty >= 5) activeClass = 'active-red';

        for(let i = 1; i <= 5; i++) {
            let dot = document.createElement('div');
            dot.className = 'diff-dot';
            if (i <= data.difficulty) dot.classList.add(activeClass);
            diffContainer.appendChild(dot);
        }

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
            document.getElementById('submitBtn').innerText = "Rezervovat";
        } else {
            document.getElementById('finalPrice').innerText = total + " Kč";
            document.getElementById('submitBtn').innerText = "Zaplatit";
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

    changeWalk('kras');
    </script>
</body>
</html>