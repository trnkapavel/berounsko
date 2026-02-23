<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berounsko.net - Komentované vycházky</title>
    
    <!-- Meta tagy pro popis -->
    <meta name="description" content="Komentované vycházky po Berounsku, Českém krasu a Křivoklátsku. Objevte krásy přírody s odborným výkladem. Rezervujte si místo na procházce!">
    <meta name="keywords" content="Berounsko, vycházky, Český kras, Křivoklátsko, procházky, turistika, příroda">
    
    <!-- Open Graph tagy pro Facebook, WhatsApp, Messenger -->
    <meta property="og:title" content="Berounsko.net - Komentované vycházky">
    <meta property="og:description" content="Komentované vycházky po Berounsku, Českém krasu a Křivoklátsku s odborným výkladem. Objevte krásy přírody s profesionálními průvodci!">
    <meta property="og:image" content="https://trnka.website/berounsko/screenshot/screenshot.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:url" content="https://berounsko.net">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="cs_CZ">
    <meta property="og:site_name" content="Berounsko.net">
    
    <!-- Twitter Card tagy -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Berounsko.net - Komentované vycházky">
    <meta name="twitter:description" content="Komentované vycházky po Berounsku, Českém krasu a Křivoklátsku s odborným výkladem.">
    <meta name="twitter:image" content="https://trnka.website/berounsko/screenshot/screenshot.jpg">
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* --- 0. RESET & GLOBAL & ANIMACE --- */
        body, html { margin: 0; padding: 0; font-family: 'Montserrat', sans-serif; height: 100%; }
        * { box-sizing: border-box; outline: none; }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes popIn { 
            0% { transform: scale(0); opacity: 0; } 
            70% { transform: scale(1.2); opacity: 1; } 
            100% { transform: scale(1); opacity: 1; } 
        }
        
        /* --- 1. HERO SEKCE --- */
        .hero-section {
            background-image: url('https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?w=1600&q=80');
            background-size: cover; background-position: center;
            height: 100vh; display: flex; align-items: center; justify-content: center; position: relative;
        }
        .hero-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.4); }
        .hero-content { position: relative; z-index: 1; text-align: center; color: white; animation: slideInUp 0.8s ease-out; }
        .hero-title { font-size: 3em; font-weight: 700; margin-bottom: 30px; text-shadow: 0 2px 10px rgba(0,0,0,0.5); }

        .hero-btn {
            padding: 20px 50px; font-size: 1.3em; background-color: #30B0FF; color: white;
            border: none; border-radius: 50px; cursor: pointer; font-weight: 600;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            transition: transform 0.3s, background-color 0.2s;
            text-transform: uppercase; letter-spacing: 1px;
        }
        .hero-btn:hover { transform: scale(1.05) translateY(-3px); background-color: #2090dd; }

        /* --- 2. PROMĚNNÉ --- */
        :root {
            --c-blue: #30B0FF; --c-green: #80C024; --c-orange: #FF9200; --c-red: #FF4444; --c-text: #000000;
            --font-main: 'Montserrat', sans-serif;
        }

        /* --- 3. MODAL --- */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.8); z-index: 1000;
            display: none; justify-content: center; align-items: center;
            backdrop-filter: blur(5px);
            opacity: 0; transition: opacity 0.3s ease;
        }
        .modal-overlay.is-visible { opacity: 1; }

        .modal-window {
            background: #fff; width: 90%; max-width: 1000px; height: 90vh; /* Fixní výška pro desktop */
            display: flex; flex-direction: row; border-radius: 8px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            font-family: var(--font-main); color: var(--c-text); position: relative;
            transform: scale(0.95); opacity: 0;
            transition: transform 0.3s, opacity 0.3s;
            overflow: hidden; /* Důležité pro sticky footer */
        }
        .modal-overlay.is-visible .modal-window { transform: scale(1); opacity: 1; }

        .modal-left {
            flex: 1; background-size: cover; background-position: center; min-height: 300px; position: relative;
            transition: opacity 0.2s ease;
        }
        .modal-left::after {
            content: ''; position: absolute; top:0; left:0; right:0; bottom:0;
            background: linear-gradient(to bottom, transparent 70%, rgba(0,0,0,0.6));
        }

        /* UPRAVENÝ MODAL RIGHT PRO STICKY FOOTER */
        .modal-right {
            flex: 1; 
            display: flex; flex-direction: column; /* Pod sebe */
            position: relative;
            overflow: hidden; /* Skryje scrollbar na hlavním kontejneru */
            padding: 0; /* Padding přesunut dovnitř */
        }

        /* Scrollable content area */
        .scroll-content {
            flex: 1; /* Zabere zbytek místa */
            overflow-y: auto; /* Scrollování pouze zde */
            padding: 40px;
            padding-bottom: 20px;
        }
        
        /* Fixed footer area */
        .fixed-footer {
            flex-shrink: 0; /* Nesmrskne se */
            padding: 20px 40px;
            background: #fff;
            border-top: 1px solid #f0f0f0;
            box-shadow: 0 -5px 20px rgba(0,0,0,0.05); /* Stín nahoru */
            z-index: 20;
        }

        /* Wrapper pro přepínání view (Form vs Success) */
        .view-wrapper {
            display: flex; flex-direction: column; height: 100%;
        }

        .close-modal {
            position: absolute; right: 20px; top: 15px; font-size: 28px; cursor: pointer; color: #aaa; z-index: 50;
            background: rgba(255,255,255,0.8); border-radius: 50%; width: 40px; height: 40px; text-align: center; line-height: 40px;
        }
        .close-modal:hover { color: var(--c-red); background: #fff; }

        h2 { font-weight: 600; margin-bottom: 20px; color: var(--c-blue); margin-top: 0; }
        
        /* --- 4. PŘEPÍNAČE --- */
        .walk-selector { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; }
        .walk-btn {
            padding: 8px 15px; border: 2px solid #ddd; border-radius: 20px;
            cursor: pointer; font-size: 0.9em; background: #fff; transition: all 0.3s;
        }
        .walk-btn.active {
            border-color: var(--c-orange); background: var(--c-orange); color: #fff; font-weight: 600; transform: scale(1.05);
        }

        /* --- 5. ROZBALOVÁNÍ TEXTU --- */
        .annotation-box ul { padding-left: 20px; margin-bottom: 5px; }
        .annotation-box li { margin-bottom: 5px; font-size: 0.95em; line-height: 1.4; }
        
        .hidden-content-wrapper {
            max-height: 0; overflow: hidden; opacity: 0; transition: max-height 0.4s ease, opacity 0.4s ease;
        }
        .hidden-content-wrapper.is-open { max-height: 500px; opacity: 1; }
        
        .toggle-details-btn {
            color: var(--c-blue); font-weight: 600; font-size: 0.9em;
            cursor: pointer; display: inline-block; margin-left: 20px; margin-bottom: 20px;
            text-decoration: underline;
        }

        /* --- 6. INFO ROW --- */
        .info-row {
            background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;
            border-left: 4px solid var(--c-blue);
            display: grid; grid-template-columns: 1fr 1fr; gap: 15px;
        }
        .info-item { display: flex; flex-direction: column; }
        .info-label { font-weight: 400; font-size: 0.85em; color: #666; margin-bottom: 3px; }
        .info-value { font-weight: 600; font-size: 1em; color: #000; }

        /* --- 7. INDIKÁTOR NÁROČNOSTI --- */
        .difficulty-wrapper { display: flex; gap: 4px; align-items: center; margin-top: 2px; }
        .diff-dot {
            width: 12px; height: 12px; border-radius: 50%; background-color: #ddd; transform: scale(0);
        }
        .diff-dot.animate { animation: popIn 0.3s forwards; }
        .diff-dot.active-green { background-color: var(--c-green); }
        .diff-dot.active-orange { background-color: var(--c-orange); }
        .diff-dot.active-red { background-color: var(--c-red); }

        /* --- 8. FORMULÁŘ --- */
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.9em; }
        input, select {
            width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px;
            font-family: var(--font-main); font-size: 1em; background: #fafafa;
        }
        
        /* Cena a tlačítko jsou nyní v patičce, ale styly zůstávají */
        .price-display {
            font-size: 1.4em; font-weight: 600; color: var(--c-green); text-align: center; margin-bottom: 15px;
        }
        .btn-submit {
            background: var(--c-blue); color: white; border: none; padding: 15px;
            width: 100%; font-size: 1.1em; font-weight: 600; border-radius: 6px; cursor: pointer;
            transition: background 0.3s;
        }
        .btn-submit:hover { background: #2090dd; }
        .btn-submit:disabled { background: #ccc; cursor: not-allowed; }

        /* --- 9. SUCCESS STAV --- */
        .success-message {
            display: none;
            flex-direction: column; align-items: center; justify-content: center;
            text-align: center; height: 100%; width: 100%;
            padding: 40px;
        }
        
        .checkmark-circle {
            width: 80px; height: 80px; position: relative; display: inline-block;
            border-radius: 50%; border: 3px solid #80C024; margin-bottom: 20px;
            transform: scale(0); opacity: 0;
        }
        
        .success-message.is-active .checkmark-circle {
            animation: popIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        .checkmark-circle::after {
            content: ''; position: absolute; top: 50%; left: 50%; width: 25px; height: 13px;
            border-left: 4px solid #80C024; border-bottom: 4px solid #80C024;
            transform: translate(-50%, -65%) rotate(-45deg);
        }
        .success-text h3 { color: #80C024; font-size: 1.8em; margin: 0 0 10px 0; }

        /* --- 10. RESPONZIVITA --- */
        @media (max-width: 768px) {
            .hero-title { font-size: 2em; padding: 0 20px; }
            .modal-window { flex-direction: column; width: 95%; max-height: 95vh; }
            .modal-left { height: 150px; flex: none; }
            /* Na mobilu chceme taky scroll */
            .scroll-content { padding: 20px; }
            .fixed-footer { padding: 15px 20px; }
        }
    </style>
</head>
<body>

    <div class="hero-section">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="hero-title">Komentované vycházky přírodou</div>
            <button class="hero-btn" onclick="openModal()">Rezervovat vycházku</button>
        </div>
    </div>

    <div id="bookingModal" class="modal-overlay">
        <div class="modal-window">
            <span class="close-modal" onclick="closeAndReset()">&times;</span>
            
            <div class="modal-left" id="modalImage" style="background-image: url('img/cesky-kras.jpg');"></div>

            <div class="modal-right">
                
                <div id="mainFormView" class="view-wrapper">
                    
                    <div class="scroll-content">
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
                                <div class="difficulty-wrapper" id="diffContainer"></div>
                            </div>
                        </div>

                        <form id="reservationForm" onsubmit="submitForm(event)">
                            <input type="hidden" name="walk_id" id="inputWalkId" value="kras">
                            <input type="hidden" name="walk_name" id="inputWalkName" value="">
                            
                            <div class="form-group">
                                <label>Váš e-mail</label>
                                <input type="email" name="email" required placeholder="jan.novak@email.cz" value="jméno@domena.cz">
                            </div>

                            <div class="form-group">
                                <label>Počet účastníků</label>
                                <select name="count" id="participantCount" onchange="calculatePrice()">
                                </select>
                            </div>
                        </form>
                    </div>

                    <div class="fixed-footer">
                        <div class="price-display" id="finalPrice">Zdarma</div>
                        <button type="submit" form="reservationForm" class="btn-submit" id="submitBtn">Zaplatit a rezervovat místo</button>
                    </div>
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
    // DATA PROCHÁZEK
    const walks = {
        'kras': {
            title: 'Okruh Srbsko, Chlum',
            img: 'img/srbsko-chlum.jpg', 
            guide: 'Martin Majer (fotograf, jeskyňář, autor publikací) a Jan Holeček (hydrogeolog)',
            date: '18. 4. 2026',
            distance: '4 km',
            difficulty: 4, 
            pricePerPerson: 0,
            desc: [
                'Srbsko – Hájkova rokle – Chlum – Hostim – Srbsko, okruh méně známými oblastmi Českého krasu.', 
                'Hájkova rokle a okolní lesy a skály, pestrá krajina a výhledy.',
                'Možnost pozorovat první jarní, kvetoucí rostliny a proměny přírody.',
                'Výklad o přírodě, krajině a geologickém vývoji Českého krasu.',
                'Souvislosti mezi geologickým podložím, půdou, klimatem a vegetací.',
                'Vnímání krajiny v širších souvislostech a šetrný pohyb v chráněném území.'
            ]
        },
        'svatojan': {
            title: 'Svatojanský okruh',
            img: 'img/svatojansky-okruh.jpg', 
            guide: 'František Zima',
            date: '16. 5. 2026',
            distance: '4 km',
            difficulty: 4, 
            pricePerPerson: 100,
            desc: [
                'Český kras jako „přírodní botanická zahrada Čech" – největší vápencové území v Čechách.',
                'Vývoj krajiny Českého krasu a vztah geologického podloží, půdy, klimatu a biodiverzity.',
                'Rostlinná společenstva a konkrétní zástupci flóry, pozorování vegetace přímo v terénu.',
                'Souvislosti v přírodě: fotosyntéza, rovnováha ekosystémů, vazby mezi organismy.',
                'Krajina jako zdroj inspirace pro vědce, umělce i návštěvníky.',
                'Vztah člověka a přírody, odpovědnost za krajinu nejen z ekonomického hlediska.'
            ]
        },
        'krivoklat': {
            title: 'Brdatka',
            img: 'img/brdatka.jpg', 
            guide: 'Markéta Hrnčálová',
            date: 'datum bude upřesněno',
            distance: '9,5 km',
            difficulty: 3, 
            pricePerPerson: 100,
            desc: [
                'Ochrana přírody a krajiny v oblasti Křivoklátska, význam chráněných území a šetrného pohybu v přírodě.',
                'Ochrana kulturních památek a historické krajiny, soužití člověka a lesa v průběhu staletí.',
                'Historie regionu na příkladu hradu Křivoklát – význam loveckých hvozdů a královských lesů.',
                'Přírodní rezervace Brdatka – ukázka cenných lesních porostů a přirozených ekosystémů.',
                'Hamouzův statek – doklad tradičního venkovského hospodaření a vztahu člověka k půdě.',
                'Proměny krajiny v čase a vliv hospodaření, lesnictví a osídlení na dnešní podobu Křivoklátska.'
            ]
        },
        'alkazar': {
            title: 'Alkazar',
            img: 'img/alkazar.jpg', 
            guide: 'Martin Majer (fotograf, jeskyňář, autor publikací) a Jan Holeček (hydrogeolog)',
            date: 'datum bude upřesněno',
            distance: '4 km',
            difficulty: 1, 
            pricePerPerson: 100,
            desc: [
                'CHKO Český kras – krajina vápencových skal, lomů a krasových jevů v okolí Berounky.',
                'Z Berouna podél řeky do osady V Kozle, klidné údolí.',
                'Alkazar – bývalé vápencové lomy, stopy těžby a proměny krajiny vlivem člověka.',
                'Krasové jevy a jeskyně, geologický vývoj území a vznik vápencových skal.',
                'Vztah člověka a přírody: těžba vápence, využívání krajiny a její dnešní ochrana.',
                'Zajímavosti o místní přírodě, vegetaci a živočiších vázaných na skalní a lesní prostředí.'
            ]
        }
    };

    // Inicializace
    const select = document.getElementById('participantCount');
    for (let i = 1; i <= 20; i++) {
        let opt = document.createElement('option');
        opt.value = i; opt.innerHTML = i; select.appendChild(opt);
    }

    // Otevření
    function openModal() {
        const modal = document.getElementById('bookingModal');
        modal.style.display = 'flex';
        setTimeout(() => { modal.classList.add('is-visible'); }, 10);
    }

    // Zavření a reset
    function closeAndReset() {
        const modal = document.getElementById('bookingModal');
        modal.classList.remove('is-visible');
        setTimeout(() => {
            modal.style.display = 'none';
            
            // Resetování view
            document.getElementById('mainFormView').style.display = 'flex';
            document.getElementById('successView').style.display = 'none';
            document.getElementById('successView').classList.remove('is-active');

            document.getElementById('reservationForm').reset();
            changeWalk(document.getElementById('inputWalkId').value);
            calculatePrice();
        }, 300);
    }

    function changeWalk(id) {
        const data = walks[id];
        
        // Animace fotky
        const imgDiv = document.getElementById('modalImage');
        imgDiv.style.opacity = '0';
        setTimeout(() => {
            imgDiv.style.backgroundImage = `url('${data.img}')`;
            imgDiv.style.opacity = '1';
        }, 200);

        document.querySelectorAll('.walk-btn').forEach(b => b.classList.remove('active'));
        document.querySelector(`.walk-btn[data-walk="${id}"]`).classList.add('active');
        
        document.getElementById('guideName').innerText = data.guide;
        document.getElementById('walkDate').innerText = data.date;
        document.getElementById('walkDist').innerText = data.distance;

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
            setTimeout(() => { dot.classList.add('animate'); }, i * 50);
        }

        document.getElementById('inputWalkId').value = id;
        document.getElementById('inputWalkName').value = data.title;
        
        // Seznam
        let htmlContent = '<ul>';
        if(data.desc.length > 0) htmlContent += `<li>${data.desc[0]}</li>`;
        if(data.desc.length > 1) {
            htmlContent += '<div id="hiddenDetails" class="hidden-content-wrapper"><ul>';
            for(let i = 1; i < data.desc.length; i++) {
                htmlContent += `<li>${data.desc[i]}</li>`;
            }
            htmlContent += '</ul></div>';
        }
        htmlContent += '</ul>';
        if(data.desc.length > 1) {
            htmlContent += `<a class="toggle-details-btn" onclick="toggleDetails()" id="toggleBtn">Zobrazit podrobnosti o trase ▼</a>`;
        }
        document.getElementById('annotationContent').innerHTML = htmlContent;

        calculatePrice();
    }

    function toggleDetails() {
        const hiddenDiv = document.getElementById('hiddenDetails');
        const btn = document.getElementById('toggleBtn');
        if (hiddenDiv.classList.contains('is-open')) {
            hiddenDiv.classList.remove('is-open');
            btn.innerText = 'Zobrazit podrobnosti o trase ▼';
        } else {
            hiddenDiv.classList.add('is-open');
            btn.innerText = 'Skrýt podrobnosti ▲';
        }
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

    // ODESLÁNÍ
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
                // Přepnutí pohledu
                document.getElementById('mainFormView').style.display = 'none';
                
                const successView = document.getElementById('successView');
                successView.style.display = 'flex';
                void successView.offsetWidth; // Force Reflow
                successView.classList.add('is-active');

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
</body>
</html>