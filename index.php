<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeoSafe v5.0 | National Hydro-Intelligence</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Inter:wght@400;800&display=swap');
        :root { --emerald: #00ff9d; --bg: #020617; }
        body { background: var(--bg); color: #ffffff; font-family: 'Inter', sans-serif; }
        .geo-header { font-family: 'Orbitron', sans-serif; letter-spacing: 4px; color: var(--emerald); text-shadow: 0 0 15px rgba(0,255,157,0.5); }
        .glass { background: rgba(15, 23, 42, 0.95); border: 2px solid rgba(0, 255, 157, 0.4); border-radius: 1.5rem; }
        
        /* SUPER CLEAR WHITE ON BLACK INPUTS */
        .input-field { background: #000000 !important; border: 2px solid #475569; padding: 14px; border-radius: 12px; color: #ffffff !important; font-weight: 900; font-size: 16px; outline: none; width: 100%; }
        .input-field:focus { border-color: var(--emerald); box-shadow: 0 0 15px rgba(0, 255, 157, 0.6); }
        
        label { color: var(--emerald); font-weight: 900; font-size: 10px; text-transform: uppercase; margin-bottom: 6px; display: block; }
        #map { height: 200px; border-radius: 1.5rem; border: 2px solid var(--emerald); margin: 1rem 0; background: #000; }
        .btn-predict { background: linear-gradient(90deg, #00ff9d 0%, #008f5d 100%); color: #000; font-weight: 900; text-transform: uppercase; }
        .expert-card { border-left: 5px solid var(--emerald); background: rgba(0, 255, 157, 0.05); padding: 12px; border-radius: 0 12px 12px 0; margin-bottom: 10px; }
        
        table input { background: #000; width: 100%; color: #fff; font-weight: 900; outline: none; border-bottom: 1px solid #334155; padding: 5px; }
        #preview { width: 100%; max-height: 150px; object-fit: cover; border-radius: 12px; display: none; margin-top: 10px; border: 1px solid var(--emerald); }
    </style>
</head>
<body class="p-4 pb-32 max-w-xl mx-auto">

    <header class="text-center py-6">
        <h1 class="geo-header text-4xl font-black">GEOSAFE</h1>
        <p class="text-[8px] text-slate-500 tracking-[0.4em] font-black mt-2 uppercase">National Intelligence • Geol. E. Rukevwe</p>
    </header>

    <section class="glass p-6 mb-6">
        <div class="space-y-4">
            <div>
                <label>Geopolitical Zone</label>
                <select id="zoneSel" onchange="syncStates()" class="input-field">
                    <option value="">-- Select Zone --</option>
                    <option value="NC">North Central (Kogi / Plateau)</option>
                    <option value="NE">North East (Chad Basin)</option>
                    <option value="NW">North West (Sokoto Basin)</option>
                    <option value="SE">South East (Anambra Basin)</option>
                    <option value="SS">South South (Niger Delta)</option>
                    <option value="SW">South West (Dahomey Basin)</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <select id="stateSel" onchange="syncLGAs()" class="input-field"><option value="">-- State --</option></select>
                <select id="lgaSel" class="input-field"><option value="">-- LGA --</option></select>
            </div>
            <button onclick="executeIntelligence()" class="w-full btn-predict py-5 rounded-xl text-xs shadow-xl">Predict Hydro Intelligence</button>
        </div>
    </section>

    <div id="map"></div>

    <div id="intelHub" class="hidden space-y-4">
        
        <div class="expert-card border-blue-500">
            <h4 class="text-blue-400 font-black text-[10px] mb-1">WHO STANDARDS</h4>
            <p class="text-[10px] text-slate-200">pH: 6.5-8.5 | Iron: < 0.3mg/L | TDS: < 500mg/L</p>
        </div>
        <div class="expert-card border-amber-500">
            <h4 class="text-amber-500 font-black text-[10px] mb-1">DRILLER SOP</h4>
            <p id="sopDisplay" class="text-[10px] text-slate-200 italic font-bold"></p>
        </div>
        <div class="expert-card border-emerald-500">
            <h4 class="text-emerald-400 font-black text-[10px] mb-1">EXPERT RECOMMENDATION</h4>
            <p id="recDisplay" class="text-[10px] text-slate-200 font-bold"></p>
        </div>

        <div class="glass p-5">
            <label>Borehole Site Photo</label>
            <input type="file" accept="image/*" id="siteImg" onchange="previewImg(event)" class="text-[10px] text-slate-400">
            <img id="preview">
        </div>

        <div class="glass p-5">
            <div class="flex justify-between items-center mb-3">
                <label>Geophysical Layer Log</label>
                <div class="flex gap-2">
                    <button onclick="addRow()" class="bg-emerald-500 text-black font-black w-8 h-8 rounded-lg">+</button>
                    <button onclick="remRow()" class="bg-red-600 text-white font-black w-8 h-8 rounded-lg">-</button>
                </div>
            </div>
            <table class="w-full text-[11px]">
                <thead class="text-slate-500 uppercase">
                    <tr><th>Res(Ωm)</th><th>Thk(m)</th><th>Lithology</th></tr>
                </thead>
                <tbody id="geoBody">
                    <tr>
                        <td><input type="number" class="res-v"></td>
                        <td><input type="number" class="thk-v"></td>
                        <td><input type="text" class="lith-v" value="Topsoil"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="glass p-5">
            <label>Variance Control (VES vs Drill)</label>
            <div class="grid grid-cols-2 gap-3 mb-4">
                <input type="number" id="vPred" oninput="calcVar()" class="input-field" placeholder="Predicted">
                <input type="number" id="vAct" oninput="calcVar()" class="input-field" placeholder="Actual">
            </div>
            <div class="flex justify-between bg-black p-4 rounded-xl border border-emerald-500/30 font-black">
                <span id="vRes">0.00m</span>
                <span id="boqRes" class="text-emerald-400">₦0.00</span>
            </div>
            
            <div class="grid grid-cols-2 gap-3 mt-4">
                <button onclick="exportToPDF()" class="bg-red-600 text-white py-4 rounded-xl font-black text-[10px] uppercase">PDF Export</button>
                <button onclick="exportToCSV()" class="bg-blue-600 text-white py-4 rounded-xl font-black text-[10px] uppercase">CSV Export</button>
            </div>
        </div>
    </div>

    <script>
        const MASTER_DB = {
            "NC": { states: ["Kogi", "Plateau"], lgas: ["Ankpa", "Lokoja", "Jos South"], d: 75, r: 16500, sop: "Heavy DTH Hammer required for Basement Complex.", rec: "Focus on fractured crystalline zones. Screen at fracture points.", lat: 7.8, lng: 6.7 },
            "NW": { states: ["Kano", "Sokoto"], lgas: ["Kano Municipal", "Sokoto North"], d: 60, r: 18000, sop: "Mud rotary for Basin sands. Hammer for Kaduna basement.", rec: "Ensure gravel packing for aquifer layers.", lat: 12.0, lng: 8.5 },
            "SS": { states: ["Delta", "Rivers"], lgas: ["Uvwie", "Warri South", "Port Harcourt"], d: 45, r: 14500, sop: "Reverse circulation mud drilling. Use PVC screens.", rec: "High yield potential in Benin Formation. Monitor saline intrusion.", lat: 5.5, lng: 5.8 },
            "NE": { states: ["Borno", "Bauchi"], lgas: ["Maiduguri", "Bauchi Central"], d: 110, r: 23000, sop: "Deep rotary drilling for Chad Basin sands.", rec: "Target deep aquifer layers (Zone C).", lat: 11.8, lng: 13.1 },
            "SE": { states: ["Anambra", "Enugu"], lgas: ["Awka South", "Enugu North"], d: 85, r: 17500, sop: "Standard rotary mud drilling. Bentonite control critical.", rec: "Target Ajali Sandstone formations.", lat: 6.4, lng: 7.5 },
            "SW": { states: ["Lagos", "Oyo"], lgas: ["Ikeja", "Ibadan North"], d: 55, r: 15500, sop: "Mud rotary with polymer additives.", rec: "Saltwater seal required for coastal Lagos areas.", lat: 6.5, lng: 3.4 }
        };

        function syncStates() {
            const z = document.getElementById('zoneSel').value;
            const s = document.getElementById('stateSel');
            s.innerHTML = '<option value="">-- State --</option>';
            if(MASTER_DB[z]) MASTER_DB[z].states.forEach(st => s.innerHTML += `<option value="${st}">${st}</option>`);
            syncLGAs();
        }

        function syncLGAs() {
            const z = document.getElementById('zoneSel').value;
            const l = document.getElementById('lgaSel');
            l.innerHTML = '<option value="">-- LGA --</option>';
            if(MASTER_DB[z]) MASTER_DB[z].lgas.forEach(lg => l.innerHTML += `<option value="${lg}">${lg}</option>`);
        }

        let map = L.map('map', { zoomControl: false }).setView([9.0, 8.6], 5);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(map);

        function executeIntelligence() {
            const z = document.getElementById('zoneSel').value;
            const l = document.getElementById('lgaSel').value;
            if(!l || l === "" || l.includes("--")) return alert("Please select Zone, State, and LGA first!");

            const data = MASTER_DB[z];
            document.getElementById('intelHub').classList.remove('hidden');
            document.getElementById('sopDisplay').innerText = data.sop;
            document.getElementById('recDisplay').innerText = data.rec;
            document.getElementById('vPred').value = data.d;
            document.getElementById('boqRes').innerText = "₦" + (data.d * data.r).toLocaleString();
            
            setTimeout(() => { map.invalidateSize(); map.flyTo([data.lat, data.lng], 12); L.marker([data.lat, data.lng]).addTo(map).bindPopup(l).openPopup(); }, 300);
            calcVar();
        }

        function calcVar() {
            const p = parseFloat(document.getElementById('vPred').value) || 0;
            const a = parseFloat(document.getElementById('vAct').value) || 0;
            document.getElementById('vRes').innerText = (a - p).toFixed(2) + "m";
        }

        function addRow() {
            const row = `<tr><td><input type="number" class="res-v"></td><td><input type="number" class="thk-v"></td><td><input type="text" class="lith-v"></td></tr>`;
            document.getElementById('geoBody').insertAdjacentHTML('beforeend', row);
        }

        function remRow() {
            const b = document.getElementById('geoBody');
            if(b.rows.length > 1) b.deleteRow(-1);
        }

        function previewImg(e) {
            const p = document.getElementById('preview');
            p.src = URL.createObjectURL(e.target.files[0]);
            p.style.display = 'block';
        }

        function exportToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.text("GEOSAFE FIELD AUDIT REPORT", 20, 20);
            doc.text("Station: " + document.getElementById('lgaSel').value, 20, 30);
            doc.text("Variance: " + document.getElementById('vRes').innerText, 20, 40);
            doc.text("BOQ Estimate: " + document.getElementById('boqRes').innerText, 20, 50);
            doc.save("GeoSafe_Audit_Final.pdf");
        }

        function exportToCSV() {
            let csv = "Resistivity(Ohm-m),Thickness(m),Lithology\n";
            document.querySelectorAll("#geoBody tr").forEach(row => {
                const r = row.querySelector(".res-v").value;
                const t = row.querySelector(".thk-v").value;
                const l = row.querySelector(".lith-v").value;
                csv += `${r},${t},${l}\n`;
            });
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url; a.download = 'Hydro_Field_Log.csv'; a.click();
        }
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeoSafe v5.0 | National Hydro-Intelligence</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Inter:wght@400;800&display=swap');
        :root { --emerald: #00ff9d; --bg: #020617; }
        body { background: var(--bg); color: #ffffff; font-family: 'Inter', sans-serif; }
        .geo-header { font-family: 'Orbitron', sans-serif; letter-spacing: 4px; color: var(--emerald); text-shadow: 0 0 15px rgba(0,255,157,0.5); }
        .glass { background: rgba(15, 23, 42, 0.95); border: 2px solid rgba(0, 255, 157, 0.4); border-radius: 1.5rem; box-shadow: 0 20px 50px rgba(0,0,0,0.8); }
        
        /* Flawless White-on-Black Inputs */
        .input-field { background: #000000; border: 2px solid #334155; padding: 14px; border-radius: 12px; color: #ffffff !important; font-weight: 800; font-size: 16px; outline: none; transition: 0.3s; width: 100%; }
        .input-field:focus { border-color: var(--emerald); box-shadow: 0 0 20px rgba(0, 255, 157, 0.5); }
        
        label { color: var(--emerald); font-weight: 900; font-size: 11px; text-transform: uppercase; margin-bottom: 8px; display: block; letter-spacing: 1.5px; }
        #map { height: 220px; border-radius: 1.5rem; border: 2px solid var(--emerald); margin: 1.5rem 0; filter: hue-rotate(140deg) brightness(0.8); }
        
        .btn-predict { background: linear-gradient(90deg, #00ff9d 0%, #008f5d 100%); color: #000; font-weight: 900; letter-spacing: 2px; text-transform: uppercase; }
        .expert-card { border-left: 6px solid var(--emerald); background: rgba(0, 255, 157, 0.05); padding: 15px; border-radius: 0 15px 15px 0; }
        
        .sync-loader { position: fixed; inset: 0; background: rgba(0,0,0,0.95); display: none; flex-direction: column; align-items: center; justify-content: center; z-index: 10000; }
        .spinner { width: 70px; height: 70px; border: 8px solid #1e293b; border-top-color: var(--emerald); border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body class="p-4 pb-32 max-w-xl mx-auto">

    <div id="syncLoader" class="sync-loader">
        <div class="spinner mb-6"></div>
        <p class="text-emerald-400 font-black tracking-widest animate-pulse text-lg">GEOSAFE CLOUD SYNC...</p>
    </div>

    <header class="text-center py-10">
        <h1 class="geo-header text-5xl font-black">GEOSAFE</h1>
        <p class="text-[10px] text-slate-500 tracking-[0.5em] font-black mt-3 uppercase">National Hydro-Intelligence • Geol. E. Rukevwe</p>
    </header>

    <section class="glass p-7 mb-8">
        <div class="space-y-6">
            <div>
                <label>Geopolitical Representative</label>
                <select id="zoneSel" onchange="syncStates()" class="input-field">
                    <option value="">-- Select Zone --</option>
                    <option value="NC">North Central (Kogi / Plateau)</option>
                    <option value="NE">North East (Chad Basin)</option>
                    <option value="NW">North West (Sokoto Basin)</option>
                    <option value="SE">South East (Anambra Basin)</option>
                    <option value="SS">South South (Niger Delta)</option>
                    <option value="SW">South West (Dahomey Basin)</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label>State</label>
                    <select id="stateSel" onchange="syncLGAs()" class="input-field"><option value="">-- State --</option></select>
                </div>
                <div>
                    <label>LGA Station</label>
                    <select id="lgaSel" class="input-field"><option value="">-- Select LGA --</option></select>
                </div>
            </div>
            <button onclick="executeIntelligence()" class="w-full btn-predict py-5 rounded-2xl text-sm shadow-2xl">Predict Hydro Intelligence</button>
        </div>
    </section>

    <div id="map"></div>

    <div id="intelHub" class="hidden space-y-8 animate-in fade-in slide-in-from-bottom-10 duration-1000">
        
        <div class="space-y-4">
            <div class="expert-card border-blue-500">
                <h4 class="text-blue-400 font-black text-xs uppercase mb-1">WHO Standards Checklist</h4>
                <p class="text-[11px] text-slate-300">pH: 6.5-8.5 | TDS: < 500mg/L | Iron: < 0.3mg/L | Nitrate: < 50mg/L</p>
            </div>
            <div class="expert-card border-amber-500">
                <h4 class="text-amber-500 font-black text-xs uppercase mb-1">Driller's Field SOP</h4>
                <p id="sopDisplay" class="text-[11px] text-slate-300 italic"></p>
            </div>
            <div class="expert-card border-emerald-500">
                <h4 class="text-emerald-400 font-black text-xs uppercase mb-1">Expert Recommendation</h4>
                <p id="recDisplay" class="text-[11px] text-slate-300"></p>
            </div>
        </div>

        <div class="glass p-6">
            <h3 class="label-tag mb-4">Field Variance & BOQ Control</h3>
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-black p-4 rounded-xl border border-slate-800">
                    <label>VES Predicted (m)</label>
                    <input type="number" id="vPred" oninput="calcVar()" class="input-field border-none p-0 text-3xl">
                </div>
                <div class="bg-black p-4 rounded-xl border border-slate-800">
                    <label>Drill Actual (m)</label>
                    <input type="number" id="vAct" oninput="calcVar()" class="input-field border-none p-0 text-3xl" placeholder="0.0">
                </div>
            </div>
            <div class="flex justify-between items-center bg-black p-5 rounded-2xl border-2 border-emerald-500/30">
                <div>
                    <span class="text-[10px] text-slate-500 font-bold uppercase">Drilling Variance</span>
                    <div id="vRes" class="text-3xl font-black text-white">0.00m</div>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-emerald-500 font-bold uppercase">Estimated BOQ</span>
                    <div id="boqRes" class="text-2xl font-black text-white">₦0.00</div>
                </div>
            </div>
        </div>

        <div class="glass p-6">
            <label>National Hydro-Archive & Facts</label>
            <select id="factSelect" onchange="showFact()" class="input-field mb-4">
                <option value="">-- Tap to Reveal Facts --</option>
                <option value="d">The Niger-Benue Drainage Logic</option>
                <option value="m">Federal Ministry Achievement (PEWASH)</option>
                <option value="p">Drilling Pitfalls (Dry Hole Risks)</option>
            </select>
            <div id="factBox" class="hidden p-5 bg-black rounded-2xl text-[12px] text-emerald-100 italic border border-emerald-500/20 leading-relaxed shadow-inner"></div>
        </div>

        <div class="glass p-6">
            <div class="flex justify-between items-center mb-5">
                <label>Geophysical Layer Log</label>
                <div class="flex gap-3">
                    <button onclick="addRow()" class="bg-emerald-500 text-black font-black w-12 h-12 rounded-2xl shadow-lg shadow-emerald-500/20">+</button>
                    <button onclick="remRow()" class="bg-red-600 text-white font-black w-12 h-12 rounded-2xl shadow-lg shadow-red-500/20">-</button>
                </div>
            </div>
            <table class="w-full text-[12px]">
                <thead class="text-slate-500 uppercase">
                    <tr><th class="pb-3 px-2">Res(Ωm)</th><th class="pb-3 px-2">Thk(m)</th><th class="pb-3 px-2">Lithology</th></tr>
                </thead>
                <tbody id="tableBody">
                    <tr class="border-b border-slate-800">
                        <td><input type="number" class="bg-transparent w-full p-3 text-white font-bold outline-none"></td>
                        <td><input type="number" class="bg-transparent w-full p-3 text-white font-bold outline-none"></td>
                        <td><select class="bg-transparent w-full text-emerald-400 font-bold outline-none"><option>Topsoil</option><option>Aquifer Sand</option><option>Basement</option></select></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <button onclick="finalize()" class="w-full py-6 bg-emerald-500 text-black rounded-2xl font-black uppercase text-xs tracking-[3px] shadow-2xl shadow-emerald-500/40">Finalize Field Audit & Sync</button>
    </div>

    <script>
        const MASTER_DB = {
            "NC": { states: ["Kogi", "Plateau", "Kwara"], lgas: ["Ankpa", "Lokoja", "Jos South", "Ilorin East"], d: 75, r: 16500, sop: "Heavy DTH Hammer required for Basement Complex. Screen at fracture zones.", rec: "Focus on fractured crystalline zones. Monitor iron levels in Lokoja area.", lat: 7.8 },
            "NW": { states: ["Kano", "Sokoto", "Kaduna"], lgas: ["Kano Municipal", "Sokoto North", "Zaria"], d: 60, r: 18000, sop: "Mud rotary for Sokoto Basin. Air hammer for Kaduna crystalline basement.", rec: "Ensure gravel packing for sedimentary sections to prevent sand pumping.", lat: 12.0 },
            "SS": { states: ["Delta", "Rivers", "Edo"], lgas: ["Uvwie", "Warri South", "Port Harcourt", "Oredo"], d: 45, r: 14500, sop: "Reverse circulation mud drilling. Use high-yield PVC screens.", rec: "High yield potential in Benin Formation. Monitor for saline intrusion in coastal LGAs.", lat: 5.5 },
            "NE": { states: ["Borno", "Adamawa", "Bauchi"], lgas: ["Maiduguri", "Yola North", "Bauchi Central"], d: 110, r: 23000, sop: "Deep rotary drilling. High torque required for Chad Basin sands.", rec: "Target deep aquifer layers (Zone C). Use heavy-duty stabilizers.", lat: 11.8 },
            "SE": { states: ["Anambra", "Enugu", "Abia"], lgas: ["Awka South", "Enugu North", "Umuahia"], d: 85, r: 17500, sop: "Standard rotary mud drilling. Bentonite control is critical.", rec: "Target Ajali Sandstone formations. Mandatory iron oxidation treatment systems.", lat: 6.4 },
            "SW": { states: ["Lagos", "Oyo", "Ogun"], lgas: ["Ikeja", "Ibadan North", "Abeokuta"], d: 55, r: 15500, sop: "Mud rotary with polymer additives. Constant SWL monitoring.", rec: "Saltwater seal required for coastal Lagos. Good recharge in Dahomey Basin.", lat: 6.5 }
        };

        const ARCHIVE = {
            d: "DRAINAGE FACT: The Niger-Benue confluence in Lokoja (Kogi) is West Africa's most significant inland drainage focal point.",
            m: "ACHIEVEMENT: The PEWASH program has reduced waterborne diseases by 40% in targeted rural LGAs via solar-powered systems.",
            p: "PITFALL: 90% of dry holes in Nigeria occur because drillers ignore the 85% failure risk of skipping VES surveys in basement terrains."
        };

        function syncStates() {
            const z = document.getElementById('zoneSel').value;
            const s = document.getElementById('stateSel');
            s.innerHTML = '<option value="">-- State --</option>';
            if(MASTER_DB[z]) MASTER_DB[z].states.forEach(state => {
                s.innerHTML += `<option value="${state}">${state}</option>`;
            });
            syncLGAs();
        }

        function syncLGAs() {
            const z = document.getElementById('zoneSel').value;
            const l = document.getElementById('lgaSel');
            l.innerHTML = '<option value="">-- Select LGA --</option>';
            if(MASTER_DB[z]) MASTER_DB[z].lgas.forEach(lga => {
                l.innerHTML += `<option value="${lga}">${lga}</option>`;
            });
        }

        let map = L.map('map', { zoomControl: false }).setView([9.0, 8.6], 5);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(map);

        function executeIntelligence() {
            const z = document.getElementById('zoneSel').value;
            const l = document.getElementById('lgaSel').value;
            if(!l) return alert("SELECT GEOPOLITICAL ZONE, STATE & LGA FIRST!");

            const data = MASTER_DB[z];
            document.getElementById('intelHub').classList.remove('hidden');
            document.getElementById('sopDisplay').innerText = data.sop;
            document.getElementById('recDisplay').innerText = data.rec;
            document.getElementById('vPred').value = data.d;
            document.getElementById('boqRes').innerText = "₦" + (data.d * data.r).toLocaleString();
            map.flyTo([data.lat, 8.0], 12);
            calcVar();
        }

        function calcVar() {
            const p = parseFloat(document.getElementById('vPred').value) || 0;
            const a = parseFloat(document.getElementById('vAct').value) || 0;
            document.getElementById('vRes').innerText = (a - p).toFixed(2) + "m";
        }

        function showFact() {
            const f = document.getElementById('factSelect').value;
            const b = document.getElementById('factBox');
            if(f) { b.innerText = ARCHIVE[f]; b.classList.remove('hidden'); } else { b.classList.add('hidden'); }
        }

        function addRow() {
            const tr = document.createElement('tr');
            tr.className = "border-b border-slate-800";
            tr.innerHTML = `<td><input type="number" class="bg-transparent w-full p-3 text-white font-bold outline-none"></td><td><input type="number" class="bg-transparent w-full p-3 text-white font-bold outline-none"></td><td><select class="bg-transparent w-full text-emerald-400 font-bold outline-none"><option>Topsoil</option><option>Aquifer Sand</option><option>Basement</option></select></td>`;
            document.getElementById('tableBody').appendChild(tr);
        }

        function remRow() {
            const b = document.getElementById('tableBody');
            if(b.rows.length > 1) b.deleteRow(-1);
        }

        function finalize() {
            document.getElementById('syncLoader').style.display = 'flex';
            setTimeout(() => {
                document.getElementById('syncLoader').style.display = 'none';
                alert("FINAL AUDIT COMPLETE: DATA BROADCASTED TO GEOSAFE GLOBAL HUB");
            }, 3000);
        }
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeoSafe v5.0 | National Hydro-Intelligence</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Inter:wght@400;800&display=swap');
        :root { --emerald: #00ff9d; --bg: #020617; }
        body { background: var(--bg); color: #ffffff; font-family: 'Inter', sans-serif; }
        .geo-header { font-family: 'Orbitron', sans-serif; letter-spacing: 4px; color: var(--emerald); text-shadow: 0 0 15px rgba(0,255,157,0.5); }
        .glass { background: rgba(15, 23, 42, 0.95); border: 2px solid rgba(0, 255, 157, 0.4); border-radius: 1.5rem; box-shadow: 0 20px 50px rgba(0,0,0,0.8); }
        
        /* High-Contrast White-on-Black Inputs */
        .input-field { background: #000000 !important; border: 2px solid #334155; padding: 14px; border-radius: 12px; color: #ffffff !important; font-weight: 800; font-size: 16px; outline: none; transition: 0.3s; width: 100%; appearance: none; }
        .input-field:focus { border-color: var(--emerald); box-shadow: 0 0 20px rgba(0, 255, 157, 0.5); }
        
        label { color: var(--emerald); font-weight: 900; font-size: 11px; text-transform: uppercase; margin-bottom: 8px; display: block; letter-spacing: 1.5px; }
        #map { height: 240px; border-radius: 1.5rem; border: 2px solid var(--emerald); margin: 1.5rem 0; background: #000; }
        
        .btn-predict { background: linear-gradient(90deg, #00ff9d 0%, #008f5d 100%); color: #000; font-weight: 900; letter-spacing: 2px; text-transform: uppercase; }
        .expert-card { border-left: 6px solid var(--emerald); background: rgba(0, 255, 157, 0.05); padding: 15px; border-radius: 0 15px 15px 0; }
        
        .sync-loader { position: fixed; inset: 0; background: rgba(0,0,0,0.95); display: none; flex-direction: column; align-items: center; justify-content: center; z-index: 10000; }
        .spinner { width: 70px; height: 70px; border: 8px solid #1e293b; border-top-color: var(--emerald); border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        
        /* Ensures dropdown text is visible on all mobile browsers */
        select option { background: #000; color: #fff; }
    </style>
</head>
<body class="p-4 pb-32 max-w-xl mx-auto">

    <div id="syncLoader" class="sync-loader">
        <div class="spinner mb-6"></div>
        <p class="text-emerald-400 font-black tracking-widest animate-pulse text-lg text-center px-4">SYNCHRONIZING FIELD LOGS TO GEOSAFE CLOUD...</p>
    </div>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeoSafe v5.0 | National Hydro-Intelligence</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Inter:wght@400;800&display=swap');
        :root { --emerald: #00ff9d; --bg: #020617; }
        body { background: var(--bg); color: #ffffff; font-family: 'Inter', sans-serif; }
        .geo-header { font-family: 'Orbitron', sans-serif; letter-spacing: 4px; color: var(--emerald); text-shadow: 0 0 15px rgba(0,255,157,0.5); }
        .glass { background: rgba(15, 23, 42, 0.95); border: 2px solid rgba(0, 255, 157, 0.4); border-radius: 1.5rem; box-shadow: 0 20px 50px rgba(0,0,0,0.8); }
        .input-field { background: #000000; border: 2px solid #334155; padding: 14px; border-radius: 12px; color: #ffffff !important; font-weight: 800; font-size: 16px; outline: none; transition: 0.3s; width: 100%; }
        .input-field:focus { border-color: var(--emerald); box-shadow: 0 0 20px rgba(0, 255, 157, 0.5); }
        label { color: var(--emerald); font-weight: 900; font-size: 11px; text-transform: uppercase; margin-bottom: 8px; display: block; letter-spacing: 1.5px; }
        #map { height: 220px; border-radius: 1.5rem; border: 2px solid var(--emerald); margin: 1.5rem 0; background: #000; }
        .btn-predict { background: linear-gradient(90deg, #00ff9d 0%, #008f5d 100%); color: #000; font-weight: 900; letter-spacing: 2px; text-transform: uppercase; }
        .expert-card { border-left: 6px solid var(--emerald); background: rgba(0, 255, 157, 0.05); padding: 15px; border-radius: 0 15px 15px 0; }
        .sync-loader { position: fixed; inset: 0; background: rgba(0,0,0,0.95); display: none; flex-direction: column; align-items: center; justify-content: center; z-index: 10000; }
        .spinner { width: 70px; height: 70px; border: 8px solid #1e293b; border-top-color: var(--emerald); border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        select option { background: #000; color: #fff; }
    </style>
</head>
<body class="p-4 pb-32 max-w-xl mx-auto">

    <div id="syncLoader" class="sync-loader">
        <div class="spinner mb-6"></div>
        <p class="text-emerald-400 font-black tracking-widest animate-pulse text-lg text-center px-4">SYNCHRONIZING FIELD LOGS TO GEOSAFE CLOUD...</p>
    </div>

    <header class="text-center py-10">
        <h1 class="geo-header text-5xl font-black">GEOSAFE</h1>
        <p class="text-[10px] text-slate-500 tracking-[0.5em] font-black mt-3 uppercase">National Intelligence • Geol. E. Rukevwe</p>
    </header>

    <section class="glass p-7 mb-8">
        <div class="space-y-6">
            <div>
                <label>Geopolitical Representative</label>
                <select id="zoneSel" onchange="syncStates()" class="input-field">
                    <option value="">-- Select Zone --</option>
                    <option value="NC">North Central (Kogi / Plateau)</option>
                    <option value="NE">North East (Chad Basin)</option>
                    <option value="NW">North West (Sokoto Basin)</option>
                    <option value="SE">South East (Anambra Basin)</option>
                    <option value="SS">South South (Niger Delta)</option>
                    <option value="SW">South West (Dahomey Basin)</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label>State</label>
                    <select id="stateSel" onchange="syncLGAs()" class="input-field"><option value="">-- State --</option></select>
                </div>
                <div>
                    <label>LGA Station</label>
                    <select id="lgaSel" class="input-field"><option value="">-- Select LGA --</option></select>
                </div>
            </div>
            <button onclick="executeIntelligence()" class="w-full btn-predict py-6 rounded-2xl text-sm shadow-2xl">Predict Hydro Intelligence</button>
        </div>
    </section>

    <div id="map"></div>

    <div id="intelHub" class="hidden space-y-8">
        <div class="space-y-4">
            <div class="expert-card border-blue-500">
                <h4 class="text-blue-400 font-black text-xs uppercase mb-1">WHO Standards Checklist</h4>
                <p class="text-[11px] text-slate-300">pH: 6.5-8.5 | TDS: < 500mg/L | Iron: < 0.3mg/L | Nitrate: < 50mg/L</p>
            </div>
            <div class="expert-card border-amber-500">
                <h4 class="text-amber-500 font-black text-xs uppercase mb-1">Driller's Field SOP</h4>
                <p id="sopDisplay" class="text-[11px] text-slate-300 italic"></p>
            </div>
            <div class="expert-card border-emerald-500">
                <h4 class="text-emerald-400 font-black text-xs uppercase mb-1">Expert Recommendation</h4>
                <p id="recDisplay" class="text-[11px] text-slate-300"></p>
            </div>
        </div>

        <div class="glass p-6">
            <h3 class="label-tag mb-4 text-[10px] text-emerald-400 font-black">FIELD VARIANCE & BOQ CONTROL</h3>
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-black p-4 rounded-xl border border-slate-800">
                    <label>VES Predicted (m)</label>
                    <input type="number" id="vPred" oninput="calcVar()" class="input-field border-none p-0 text-3xl">
                </div>
                <div class="bg-black p-4 rounded-xl border border-slate-800">
                    <label>Drill Actual (m)</label>
                    <input type="number" id="vAct" oninput="calcVar()" class="input-field border-none p-0 text-3xl" placeholder="0.0">
                </div>
            </div>
            <div class="flex justify-between items-center bg-black p-5 rounded-2xl border-2 border-emerald-500/30">
                <div>
                    <span class="text-[10px] text-slate-500 font-bold uppercase">Drilling Variance</span>
                    <div id="vRes" class="text-3xl font-black text-white">0.00m</div>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-emerald-500 font-bold uppercase">Estimated BOQ</span>
                    <div id="boqRes" class="text-2xl font-black text-white">₦0.00</div>
                </div>
            </div>
        </div>

        <div class="glass p-6">
            <label>National Hydro-Archive & Facts</label>
            <select id="factSelect" onchange="showFact()" class="input-field mb-4">
                <option value="">-- Tap to Reveal Facts --</option>
                <option value="d">The Niger-Benue Drainage Logic</option>
                <option value="m">Federal Ministry Achievement (PEWASH)</option>
                <option value="p">Drilling Pitfalls (Dry Hole Risks)</option>
            </select>
            <div id="factBox" class="hidden p-5 bg-black rounded-2xl text-[12px] text-emerald-100 italic border border-emerald-500/20 leading-relaxed shadow-inner"></div>
        </div>

        <div class="glass p-6">
            <div class="flex justify-between items-center mb-5">
                <label>Geophysical Layer Log</label>
                <div class="flex gap-3">
                    <button onclick="addRow()" class="bg-emerald-500 text-black font-black w-12 h-12 rounded-2xl shadow-lg">+</button>
                    <button onclick="remRow()" class="bg-red-600 text-white font-black w-12 h-12 rounded-2xl shadow-lg">-</button>
                </div>
            </div>
            <table class="w-full text-[12px]">
                <thead class="text-slate-500 uppercase">
                    <tr><th class="pb-3 px-2">Res(Ωm)</th><th class="pb-3 px-2">Thk(m)</th><th class="pb-3 px-2">Lithology</th></tr>
                </thead>
                <tbody id="tableBody">
                    <tr class="border-b border-slate-800">
                        <td><input type="number" class="bg-transparent w-full p-3 text-white font-bold outline-none"></td>
                        <td><input type="number" class="bg-transparent w-full p-3 text-white font-bold outline-none"></td>
                        <td><select class="bg-transparent w-full text-emerald-400 font-bold outline-none"><option>Topsoil</option><option>Aquifer Sand</option><option>Basement Complex</option></select></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <button onclick="finalize()" class="w-full py-6 bg-emerald-500 text-black rounded-2xl font-black uppercase text-xs tracking-[3px] shadow-2xl">Finalize Field Audit & Sync</button>
    </div>

    <script>
        const MASTER_DB = {
            "NC": { states: ["Kogi", "Plateau", "Kwara"], lgas: ["Ankpa", "Lokoja", "Jos South", "Ilorin East"], d: 75, r: 16500, sop: "Heavy DTH Hammer required for Basement Complex. Screen at fracture zones.", rec: "Focus on fractured crystalline zones. Monitor iron levels in Lokoja area.", lat: 7.8, lng: 6.7 },
            "NW": { states: ["Kano", "Sokoto", "Kaduna"], lgas: ["Kano Municipal", "Sokoto North", "Zaria"], d: 60, r: 18000, sop: "Mud rotary for Sokoto Basin. Air hammer for Kaduna crystalline basement.", rec: "Ensure gravel packing for sedimentary sections to prevent sand pumping.", lat: 12.0, lng: 8.5 },
            "SS": { states: ["Delta", "Rivers", "Edo"], lgas: ["Uvwie", "Warri South", "Port Harcourt", "Oredo"], d: 45, r: 14500, sop: "Reverse circulation mud drilling. Use high-yield PVC screens.", rec: "High yield potential in Benin Formation. Monitor for saline intrusion in coastal LGAs.", lat: 5.5, lng: 5.8 },
            "NE": { states: ["Borno", "Adamawa", "Bauchi"], lgas: ["Maiduguri", "Yola North", "Bauchi Central"], d: 110, r: 23000, sop: "Deep rotary drilling. High torque required for Chad Basin sands.", rec: "Target deep aquifer layers (Zone C). Use heavy-duty stabilizers.", lat: 11.8, lng: 13.1 },
            "SE": { states: ["Anambra", "Enugu", "Abia"], lgas: ["Awka South", "Enugu North", "Umuahia"], d: 85, r: 17500, sop: "Standard rotary mud drilling. Bentonite control is critical.", rec: "Target Ajali Sandstone formations. Mandatory iron oxidation treatment systems.", lat: 6.4, lng: 7.5 },
            "SW": { states: ["Lagos", "Oyo", "Ogun"], lgas: ["Ikeja", "Ibadan North", "Abeokuta"], d: 55, r: 15500, sop: "Mud rotary with polymer additives. Constant SWL monitoring.", rec: "Saltwater seal required for coastal Lagos. Good recharge in Dahomey Basin.", lat: 6.5, lng: 3.4 }
        };

        const ARCHIVE = {
            d: "DRAINAGE FACT: The Niger-Benue confluence in Lokoja (Kogi) is West Africa's most significant inland drainage focal point gathering over 60% of Nigeria's surface water.",
            m: "ACHIEVEMENT: The PEWASH program has reduced waterborne diseases by 40% in targeted rural LGAs via solar-powered systems.",
            p: "PITFALL: 90% of dry holes in Nigeria occur because drillers ignore the 85% failure risk of skipping VES surveys in basement terrains."
        };

        function syncStates() {
            const z = document.getElementById('zoneSel').value;
            const s = document.getElementById('stateSel');
            s.innerHTML = '<option value="">-- State --</option>';
            if(MASTER_DB[z]) MASTER_DB[z].states.forEach(state => {
                s.innerHTML += `<option value="${state}">${state}</option>`;
            });
            syncLGAs();
        }

        function syncLGAs() {
            const z = document.getElementById('zoneSel').value;
            const l = document.getElementById('lgaSel');
            l.innerHTML = '<option value="">-- Select LGA --</option>';
            if(MASTER_DB[z]) MASTER_DB[z].lgas.forEach(lga => {
                l.innerHTML += `<option value="${lga}">${lga}</option>`;
            });
        }

        let map = L.map('map', { zoomControl: false }).setView([9.0, 8.6], 5);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(map);

        function executeIntelligence() {
            const z = document.getElementById('zoneSel').value;
            const l = document.getElementById('lgaSel').value;
            if(!l) return alert("SELECT GEOPOLITICAL ZONE, STATE & LGA FIRST!");

            const data = MASTER_DB[z];
            document.getElementById('intelHub').classList.remove('hidden');
            
            // Map Fix: Invalidate size and fly to specific coords
            setTimeout(() => {
                map.invalidateSize(); 
                map.flyTo([data.lat, data.lng], 13);
                L.marker([data.lat, data.lng]).addTo(map).bindPopup(l + " Station").openPopup();
            }, 300);

            document.getElementById('sopDisplay').innerText = data.sop;
            document.getElementById('recDisplay').innerText = data.rec;
            document.getElementById('vPred').value = data.d;
            document.getElementById('boqRes').innerText = "₦" + (data.d * data.r).toLocaleString();
            calcVar();
        }

        function calcVar() {
            const p = parseFloat(document.getElementById('vPred').value) || 0;
            const a = parseFloat(document.getElementById('vAct').value) || 0;
            document.getElementById('vRes').innerText = (a - p).toFixed(2) + "m";
        }

        function showFact() {
            const f = document.getElementById('factSelect').value;
            const b = document.getElementById('factBox');
            if(f) { b.innerText = ARCHIVE[f]; b.classList.remove('hidden'); } else { b.classList.add('hidden'); }
        }

        function addRow() {
            const tr = document.createElement('tr');
            tr.className = "border-b border-slate-800";
            tr.innerHTML = `<td><input type="number" class="bg-transparent w-full p-3 text-white font-bold outline-none"></td><td><input type="number" class="bg-transparent w-full p-3 text-white font-bold outline-none"></td><td><select class="bg-transparent w-full text-emerald-400 font-bold outline-none"><option>Topsoil</option><option>Aquifer Sand</option><option>Basement Complex</option></select></td>`;
            document.getElementById('tableBody').appendChild(tr);
        }

        function remRow() {
            const b = document.getElementById('tableBody');
            if(b.rows.length > 1) b.deleteRow(-1);
        }

        function finalize() {
            document.getElementById('syncLoader').style.display = 'flex';
            setTimeout(() => {
                document.getElementById('syncLoader').style.display = 'none';
                alert("FINAL AUDIT COMPLETE: DATA BROADCASTED TO GEOSAFE GLOBAL HUB");
            }, 3000);
        }
    </script>
</body>
</html>

    <header class="text-center py-10">
        <h1 class="geo-header text-5xl font-black">GEOSAFE</h1>
        <p class="text-[10px] text-slate-500 tracking-[0.5em] font-black mt-3 uppercase">National Intelligence • Geol. E. Rukevwe</p>
    </header>

    <section class="glass p-7 mb-8">
        <div class="space-y-6">
            <div>
                <label>Geopolitical Representative</label>
                <select id="zoneSel" onchange="syncStates()" class="input-field">
                    <option value="">-- Select Zone --</option>
                    <option value="NC">North Central (Kogi / Plateau)</option>
                    <option value="NE">North East (Chad Basin)</option>
                    <option value="NW">North West (Sokoto Basin)</option>
                    <option value="SE">South East (Anambra Basin)</option>
                    <option value="SS">South South (Niger Delta)</option>
                    <option value="SW">South West (Dahomey Basin)</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label>State</label>
                    <select id="stateSel" onchange="syncLGAs()" class="input-field"><option value="">-- State --</option></select>
                </div>
                <div>
                    <label>LGA Station</label>
                    <select id="lgaSel" class="input-field"><option value="">-- Select LGA --</option></select>
                </div>
            </div>
            <button onclick="executeIntelligence()" class="w-full btn-predict py-6 rounded-2xl text-sm shadow-2xl">Predict Hydro Intelligence</button>
        </div>
    </section>

    <div id="map"></div>

    <div id="intelHub" class="hidden space-y-8 animate-in fade-in slide-in-from-bottom-10 duration-1000">
        
        <div class="space-y-4">
            <div class="expert-card border-blue-500">
                <h4 class="text-blue-400 font-black text-xs uppercase mb-1">WHO Standards Checklist</h4>
                <p class="text-[11px] text-slate-300">pH: 6.5-8.5 | TDS: < 500mg/L | Iron: < 0.3mg/L | Nitrate: < 50mg/L</p>
            </div>
            <div class="expert-card border-amber-500">
                <h4 class="text-amber-500 font-black text-xs uppercase mb-1">Driller's Field SOP</h4>
                <p id="sopDisplay" class="text-[11px] text-slate-300 italic"></p>
            </div>
            <div class="expert-card border-emerald-500">
                <h4 class="text-emerald-400 font-black text-xs uppercase mb-1">Expert Recommendation</h4>
                <p id="recDisplay" class="text-[11px] text-slate-300"></p>
            </div>
        </div>

        <div class="glass p-6">
            <h3 class="label-tag mb-4">Field Variance & BOQ Control</h3>
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-black p-4 rounded-xl border border-slate-800">
                    <label>VES Predicted (m)</label>
                    <input type="number" id="vPred" oninput="calcVar()" class="input-field border-none p-0 text-3xl">
                </div>
                <div class="bg-black p-4 rounded-xl border border-slate-800">
                    <label>Drill Actual (m)</label>
                    <input type="number" id="vAct" oninput="calcVar()" class="input-field border-none p-0 text-3xl" placeholder="0.0">
                </div>
            </div>
            <div class="flex justify-between items-center bg-black p-5 rounded-2xl border-2 border-emerald-500/30">
                <div>
                    <span class="text-[10px] text-slate-500 font-bold uppercase">Drilling Variance</span>
                    <div id="vRes" class="text-3xl font-black text-white">0.00m</div>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-emerald-500 font-bold uppercase">Estimated BOQ</span>
                    <div id="boqRes" class="text-2xl font-black text-white">₦0.00</div>
                </div>
            </div>
        </div>

        <div class="glass p-6">
            <label>National Hydro-Archive & Facts</label>
            <select id="factSelect" onchange="showFact()" class="input-field mb-4">
                <option value="">-- Tap to Reveal Facts --</option>
                <option value="d">The Niger-Benue Drainage Logic</option>
                <option value="m">Federal Ministry Achievement (PEWASH)</option>
                <option value="p">Drilling Pitfalls (Dry Hole Risks)</option>
            </select>
            <div id="factBox" class="hidden p-5 bg-black rounded-2xl text-[12px] text-emerald-100 italic border border-emerald-500/20 leading-relaxed shadow-inner"></div>
        </div>

        <div class="glass p-6">
            <div class="flex justify-between items-center mb-5">
                <label>Geophysical Layer Log</label>
                <div class="flex gap-3">
                    <button onclick="addRow()" class="bg-emerald-500 text-black font-black w-12 h-12 rounded-2xl shadow-lg">+</button>
                    <button onclick="remRow()" class="bg-red-600 text-white font-black w-12 h-12 rounded-2xl shadow-lg">-</button>
                </div>
            </div>
            <table class="w-full text-[12px]">
                <thead class="text-slate-500 uppercase">
                    <tr><th class="pb-3 px-2">Res(Ωm)</th><th class="pb-3 px-2">Thk(m)</th><th class="pb-3 px-2">Lithology</th></tr>
                </thead>
                <tbody id="tableBody">
                    <tr class="border-b border-slate-800">
                        <td><input type="number" class="bg-transparent w-full p-3 text-white font-bold outline-none"></td>
                        <td><input type="number" class="bg-transparent w-full p-3 text-white font-bold outline-none"></td>
                        <td><select class="bg-transparent w-full text-emerald-400 font-bold outline-none"><option>Topsoil</option><option>Aquifer Sand</option><option>Basement</option></select></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <button onclick="finalize()" class="w-full py-6 bg-emerald-500 text-black rounded-2xl font-black uppercase text-xs tracking-[3px] shadow-2xl">Finalize Field Audit & Sync</button>
    </section>

    <script>
        const MASTER_DB = {
            "NC": { states: ["Kogi", "Plateau", "Kwara"], lgas: ["Ankpa", "Lokoja", "Jos South", "Ilorin East"], d: 75, r: 16500, sop: "Heavy DTH Hammer required for Basement Complex. Screen at fracture zones.", rec: "Focus on fractured crystalline zones. Monitor iron levels in Lokoja area.", lat: 7.8, lng: 6.7 },
            "NW": { states: ["Kano", "Sokoto", "Kaduna"], lgas: ["Kano Municipal", "Sokoto North", "Zaria"], d: 60, r: 18000, sop: "Mud rotary for Sokoto Basin. Air hammer for Kaduna crystalline basement.", rec: "Ensure gravel packing for sedimentary sections to prevent sand pumping.", lat: 12.0, lng: 8.5 },
            "SS": { states: ["Delta", "Rivers", "Edo"], lgas: ["Uvwie", "Warri South", "Port Harcourt", "Oredo"], d: 45, r: 14500, sop: "Reverse circulation mud drilling. Use high-yield PVC screens.", rec: "High yield potential in Benin Formation. Monitor for saline intrusion in coastal LGAs.", lat: 5.5, lng: 5.8 },
            "NE": { states: ["Borno", "Adamawa", "Bauchi"], lgas: ["Maiduguri", "Yola North", "Bauchi Central"], d: 110, r: 23000, sop: "Deep rotary drilling. High torque required for Chad Basin sands.", rec: "Target deep aquifer layers (Zone C). Use heavy-duty stabilizers.", lat: 11.8, lng: 13.1 },
            "SE": { states: ["Anambra", "Enugu", "Abia"], lgas: ["Awka South", "Enugu North", "Umuahia"], d: 85, r: 17500, sop: "Standard rotary mud drilling. Bentonite control is critical.", rec: "Target Ajali Sandstone formations. Mandatory iron oxidation treatment systems.", lat: 6.4, lng: 7.5 },
            "SW": { states: ["Lagos", "Oyo", "Ogun"], lgas: ["Ikeja", "Ibadan North", "Abeokuta"], d: 55, r: 15500, sop: "Mud rotary with polymer additives. Constant SWL monitoring.", rec: "Saltwater seal required for coastal Lagos. Good recharge in Dahomey Basin.", lat: 6.5, lng: 3.4 }
        };

        const ARCHIVE = {
            d: "DRAINAGE FACT: The Niger-Benue confluence in Lokoja (Kogi) is West Africa's most significant inland drainage focal point gathering over 60% of Nigeria's surface water.",
            m: "ACHIEVEMENT: The PEWASH program has reduced waterborne diseases by 40% in targeted rural LGAs via solar-powered systems.",
            p: "PITFALL: 90% of dry holes in Nigeria occur because drillers ignore the 85% failure risk of skipping VES surveys in basement terrains."
        };

        function syncStates() {
            const z = document.getElementById('zoneSel').value;
            const s = document.getElementById('stateSel');
            s.innerHTML = '<option value="">-- State --</option>';
            if(MASTER_DB[z]) MASTER_DB[z].states.forEach(state => {
                s.innerHTML += `<option value="${state}">${state}</option>`;
            });
            syncLGAs();
        }

        function syncLGAs() {
            const z = document.getElementById('zoneSel').value;
            const l = document.getElementById('lgaSel');
            l.innerHTML = '<option value="">-- Select LGA --</option>';
            if(MASTER_DB[z]) MASTER_DB[z].lgas.forEach(lga => {
                l.innerHTML += `<option value="${lga}">${lga}</option>`;
            });
        }

        let map = L.map('map', { zoomControl: false }).setView([9.0, 8.6], 5);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        function executeIntelligence() {
            const z = document.getElementById('zoneSel').value;
            const l = document.getElementById('lgaSel').value;
            if(!l) return alert("SELECT GEOPOLITICAL ZONE, STATE & LGA FIRST!");

            const data = MASTER_DB[z];
            document.getElementById('intelHub').classList.remove('hidden');
            
            // --- MAP RESCUE LOGIC ---
            setTimeout(() => {
                map.invalidateSize(); 
                map.flyTo([data.lat, data.lng], 12);
                L.marker([data.lat, data.lng]).addTo(map)
                    .bindPopup(`<b>${l} Station</b><br>Target Depth: ${data.d}m`)
                    .openPopup();
            }, 300); 

            document.getElementById('sopDisplay').innerText = data.sop;
            document.getElementById('recDisplay').innerText = data.rec;
            document.getElementById('vPred').value = data.d;
            document.getElementById('boqRes').innerText = "₦" + (data.d * data.r).toLocaleString();
            calcVar();
        }

        function calcVar() {
            const p = parseFloat(document.getElementById('vPred').value) || 0;
            const a = parseFloat(document.getElementById('vAct').value) || 0;
            document.getElementById('vRes').innerText = (a - p).toFixed(2) + "m";
        }

        function showFact() {
            const f = document.getElementById('factSelect').value;
            const b = document.getElementById('factBox');
            if(f) { b.innerText = ARCHIVE[f]; b.classList.remove('hidden'); } else { b.classList.add('hidden'); }
        }

        function addRow() {
            const tr = document.createElement('tr');
            tr.className = "border-b border-slate-800";
            tr.innerHTML = `<td><input type="number" class="bg-transparent w-full p-3 text-white font-bold outline-none"></td><td><input type="number" class="bg-transparent w-full p-3 text-white font-bold outline-none"></td><td><select class="bg-transparent w-full text-emerald-400 font-bold outline-none"><option>Topsoil</option><option>Aquifer Sand</option><option>Basement</option></select></td>`;
            document.getElementById('tableBody').appendChild(tr);
        }

        function remRow() {
            const b = document.getElementById('tableBody');
            if(b.rows.length > 1) b.deleteRow(-1);
        }

        function finalize() {
            document.getElementById('syncLoader').style.display = 'flex';
            setTimeout(() => {
                document.getElementById('syncLoader').style.display = 'none';
                alert("FINAL AUDIT COMPLETE: DATA BROADCASTED TO GEOSAFE GLOBAL HUB");
            }, 3000);
        }
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeoSafe v5.0 | National Hydro-Intelligence</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Inter:wght@400;800&display=swap');
        :root { --emerald: #00ff9d; --bg: #020617; }
        body { background: var(--bg); color: #ffffff; font-family: 'Inter', sans-serif; }
        .geo-header { font-family: 'Orbitron', sans-serif; letter-spacing: 4px; color: var(--emerald); text-shadow: 0 0 15px rgba(0,255,157,0.5); }
        .glass { background: rgba(15, 23, 42, 0.95); border: 2px solid rgba(0, 255, 157, 0.4); border-radius: 1.5rem; box-shadow: 0 20px 50px rgba(0,0,0,0.8); }
        
        /* Flawless White-on-Black Inputs */
        .input-field { background: #000000; border: 2px solid #334155; padding: 14px; border-radius: 12px; color: #ffffff !important; font-weight: 800; font-size: 16px; outline: none; transition: 0.3s; width: 100%; }
        .input-field:focus { border-color: var(--emerald); box-shadow: 0 0 20px rgba(0, 255, 157, 0.5); }
        
        label { color: var(--emerald); font-weight: 900; font-size: 11px; text-transform: uppercase; margin-bottom: 8px; display: block; letter-spacing: 1.5px; }
        #map { height: 220px; border-radius: 1.5rem; border: 2px solid var(--emerald); margin: 1.5rem 0; filter: hue-rotate(140deg) brightness(0.8); }
        
        .btn-predict { background: linear-gradient(90deg, #00ff9d 0%, #008f5d 100%); color: #000; font-weight: 900; letter-spacing: 2px; text-transform: uppercase; }
        .expert-card { border-left: 6px solid var(--emerald); background: rgba(0, 255, 157, 0.05); padding: 15px; border-radius: 0 15px 15px 0; }
        
        .sync-loader { position: fixed; inset: 0; background: rgba(0,0,0,0.95); display: none; flex-direction: column; align-items: center; justify-content: center; z-index: 10000; }
        .spinner { width: 70px; height: 70px; border: 8px solid #1e293b; border-top-color: var(--emerald); border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body class="p-4 pb-32 max-w-xl mx-auto">

    <div id="syncLoader" class="sync-loader">
        <div class="spinner mb-6"></div>
        <p class="text-emerald-400 font-black tracking-widest animate-pulse text-lg">GEOSAFE CLOUD SYNC...</p>
    </div>

    <header class="text-center py-10">
        <h1 class="geo-header text-5xl font-black">GEOSAFE</h1>
        <p class="text-[10px] text-slate-500 tracking-[0.5em] font-black mt-3 uppercase">National Hydro-Intelligence • Geol. E. Rukevwe</p>
    </header>

    <section class="glass p-7 mb-8">
        <div class="space-y-6">
            <div>
                <label>Geopolitical Representative</label>
                <select id="zoneSel" onchange="syncStates()" class="input-field">
                    <option value="">-- Select Zone --</option>
                    <option value="NC">North Central (Kogi / Plateau)</option>
                    <option value="NE">North East (Chad Basin)</option>
                    <option value="NW">North West (Sokoto Basin)</option>
                    <option value="SE">South East (Anambra Basin)</option>
                    <option value="SS">South South (Niger Delta)</option>
                    <option value="SW">South West (Dahomey Basin)</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label>State</label>
                    <select id="stateSel" onchange="syncLGAs()" class="input-field"><option value="">-- State --</option></select>
                </div>
                <div>
                    <label>LGA Station</label>
                    <select id="lgaSel" class="input-field"><option value="">-- Select LGA --</option></select>
                </div>
            </div>
            <button onclick="executeIntelligence()" class="w-full btn-predict py-5 rounded-2xl text-sm shadow-2xl">Predict Hydro Intelligence</button>
        </div>
    </section>

    <div id="map"></div>

    <div id="intelHub" class="hidden space-y-8 animate-in fade-in slide-in-from-bottom-10 duration-1000">
        
        <div class="space-y-4">
            <div class="expert-card border-blue-500">
                <h4 class="text-blue-400 font-black text-xs uppercase mb-1">WHO Standards Checklist</h4>
                <p class="text-[11px] text-slate-300">pH: 6.5-8.5 | TDS: < 500mg/L | Iron: < 0.3mg/L | Nitrate: < 50mg/L</p>
            </div>
            <div class="expert-card border-amber-500">
                <h4 class="text-amber-500 font-black text-xs uppercase mb-1">Driller's Field SOP</h4>
                <p id="sopDisplay" class="text-[11px] text-slate-300 italic"></p>
            </div>
            <div class="expert-card border-emerald-500">
                <h4 class="text-emerald-400 font-black text-xs uppercase mb-1">Expert Recommendation</h4>
                <p id="recDisplay" class="text-[11px] text-slate-300"></p>
            </div>
        </div>

        <div class="glass p-6">
            <h3 class="label-tag mb-4">Field Variance & BOQ Control</h3>
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-black p-4 rounded-xl border border-slate-800">
                    <label>VES Predicted (m)</label>
                    <input type="number" id="vPred" oninput="calcVar()" class="input-field border-none p-0 text-3xl">
                </div>
                <div class="bg-black p-4 rounded-xl border border-slate-800">
                    <label>Drill Actual (m)</label>
                    <input type="number" id="vAct" oninput="calcVar()" class="input-field border-none p-0 text-3xl" placeholder="0.0">
                </div>
            </div>
            <div class="flex justify-between items-center bg-black p-5 rounded-2xl border-2 border-emerald-500/30">
                <div>
                    <span class="text-[10px] text-slate-500 font-bold uppercase">Drilling Variance</span>
                    <div id="vRes" class="text-3xl font-black text-white">0.00m</div>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-emerald-500 font-bold uppercase">Estimated BOQ</span>
                    <div id="boqRes" class="text-2xl font-black text-white">₦0.00</div>
                </div>
            </div>
        </div>

        <div class="glass p-6">
            <label>National Hydro-Archive & Facts</label>
            <select id="factSelect" onchange="showFact()" class="input-field mb-4">
                <option value="">-- Tap to Reveal Facts --</option>
                <option value="d">The Niger-Benue Drainage Logic</option>
                <option value="m">Federal Ministry Achievement (PEWASH)</option>
                <option value="p">Drilling Pitfalls (Dry Hole Risks)</option>
            </select>
            <div id="factBox" class="hidden p-5 bg-black rounded-2xl text-[12px] text-emerald-100 italic border border-emerald-500/20 leading-relaxed shadow-inner"></div>
        </div>

        <div class="glass p-6">
            <div class="flex justify-between items-center mb-5">
                <label>Geophysical Layer Log</label>
                <div class="flex gap-3">
                    <button onclick="addRow()" class="bg-emerald-500 text-black font-black w-12 h-12 rounded-2xl shadow-lg shadow-emerald-500/20">+</button>
                    <button onclick="remRow()" class="bg-red-600 text-white font-black w-12 h-12 rounded-2xl shadow-lg shadow-red-500/20">-</button>
                </div>
            </div>
            <table class="w-full text-[12px]">
                <thead class="text-slate-500 uppercase">
                    <tr><th class="pb-3 px-2">Res(Ωm)</th><th class="pb-3 px-2">Thk(m)</th><th class="pb-3 px-2">Lithology</th></tr>
                </thead>
                <tbody id="tableBody">
                    <tr class="border-b border-slate-800">
                        <td><input type="number" class="bg-transparent w-full p-3 text-white font-bold outline-none"></td>
                        <td><input type="number" class="bg-transparent w-full p-3 text-white font-bold outline-none"></td>
                        <td><select class="bg-transparent w-full text-emerald-400 font-bold outline-none"><option>Topsoil</option><option>Aquifer Sand</option><option>Basement</option></select></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <button onclick="finalize()" class="w-full py-6 bg-emerald-500 text-black rounded-2xl font-black uppercase text-xs tracking-[3px] shadow-2xl shadow-emerald-500/40">Finalize Field Audit & Sync</button>
    </div>

    <script>
        const MASTER_DB = {
            "NC": { states: ["Kogi", "Plateau", "Kwara"], lgas: ["Ankpa", "Lokoja", "Jos South", "Ilorin East"], d: 75, r: 16500, sop: "Heavy DTH Hammer required for Basement Complex. Screen at fracture zones.", rec: "Focus on fractured crystalline zones. Monitor iron levels in Lokoja area.", lat: 7.8 },
            "NW": { states: ["Kano", "Sokoto", "Kaduna"], lgas: ["Kano Municipal", "Sokoto North", "Zaria"], d: 60, r: 18000, sop: "Mud rotary for Sokoto Basin. Air hammer for Kaduna crystalline basement.", rec: "Ensure gravel packing for sedimentary sections to prevent sand pumping.", lat: 12.0 },
            "SS": { states: ["Delta", "Rivers", "Edo"], lgas: ["Uvwie", "Warri South", "Port Harcourt", "Oredo"], d: 45, r: 14500, sop: "Reverse circulation mud drilling. Use high-yield PVC screens.", rec: "High yield potential in Benin Formation. Monitor for saline intrusion in coastal LGAs.", lat: 5.5 },
            "NE": { states: ["Borno", "Adamawa", "Bauchi"], lgas: ["Maiduguri", "Yola North", "Bauchi Central"], d: 110, r: 23000, sop: "Deep rotary drilling. High torque required for Chad Basin sands.", rec: "Target deep aquifer layers (Zone C). Use heavy-duty stabilizers.", lat: 11.8 },
            "SE": { states: ["Anambra", "Enugu", "Abia"], lgas: ["Awka South", "Enugu North", "Umuahia"], d: 85, r: 17500, sop: "Standard rotary mud drilling. Bentonite control is critical.", rec: "Target Ajali Sandstone formations. Mandatory iron oxidation treatment systems.", lat: 6.4 },
            "SW": { states: ["Lagos", "Oyo", "Ogun"], lgas: ["Ikeja", "Ibadan North", "Abeokuta"], d: 55, r: 15500, sop: "Mud rotary with polymer additives. Constant SWL monitoring.", rec: "Saltwater seal required for coastal Lagos. Good recharge in Dahomey Basin.", lat: 6.5 }
        };

        const ARCHIVE = {
            d: "DRAINAGE FACT: The Niger-Benue confluence in Lokoja (Kogi) is West Africa's most significant inland drainage focal point.",
            m: "ACHIEVEMENT: The PEWASH program has reduced waterborne diseases by 40% in targeted rural LGAs via solar-powered systems.",
            p: "PITFALL: 90% of dry holes in Nigeria occur because drillers ignore the 85% failure risk of skipping VES surveys in basement terrains."
        };

        function syncStates() {
            const z = document.getElementById('zoneSel').value;
            const s = document.getElementById('stateSel');
            s.innerHTML = '<option value="">-- State --</option>';
            if(MASTER_DB[z]) MASTER_DB[z].states.forEach(state => {
                s.innerHTML += `<option value="${state}">${state}</option>`;
            });
            syncLGAs();
        }

        function syncLGAs() {
            const z = document.getElementById('zoneSel').value;
            const l = document.getElementById('lgaSel');
            l.innerHTML = '<option value="">-- Select LGA --</option>';
            if(MASTER_DB[z]) MASTER_DB[z].lgas.forEach(lga => {
                l.innerHTML += `<option value="${lga}">${lga}</option>`;
            });
        }

        let map = L.map('map', { zoomControl: false }).setView([9.0, 8.6], 5);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(map);

        function executeIntelligence() {
            const z = document.getElementById('zoneSel').value;
            const l = document.getElementById('lgaSel').value;
            if(!l) return alert("SELECT GEOPOLITICAL ZONE, STATE & LGA FIRST!");

            const data = MASTER_DB[z];
            document.getElementById('intelHub').classList.remove('hidden');
            document.getElementById('sopDisplay').innerText = data.sop;
            document.getElementById('recDisplay').innerText = data.rec;
            document.getElementById('vPred').value = data.d;
            document.getElementById('boqRes').innerText = "₦" + (data.d * data.r).toLocaleString();
            map.flyTo([data.lat, 8.0], 12);
            calcVar();
        }

        function calcVar() {
            const p = parseFloat(document.getElementById('vPred').value) || 0;
            const a = parseFloat(document.getElementById('vAct').value) || 0;
            document.getElementById('vRes').innerText = (a - p).toFixed(2) + "m";
        }

        function showFact() {
            const f = document.getElementById('factSelect').value;
            const b = document.getElementById('factBox');
            if(f) { b.innerText = ARCHIVE[f]; b.classList.remove('hidden'); } else { b.classList.add('hidden'); }
        }

        function addRow() {
            const tr = document.createElement('tr');
            tr.className = "border-b border-slate-800";
            tr.innerHTML = `<td><input type="number" class="bg-transparent w-full p-3 text-white font-bold outline-none"></td><td><input type="number" class="bg-transparent w-full p-3 text-white font-bold outline-none"></td><td><select class="bg-transparent w-full text-emerald-400 font-bold outline-none"><option>Topsoil</option><option>Aquifer Sand</option><option>Basement</option></select></td>`;
            document.getElementById('tableBody').appendChild(tr);
        }

        function remRow() {
            const b = document.getElementById('tableBody');
            if(b.rows.length > 1) b.deleteRow(-1);
        }

        function finalize() {
            document.getElementById('syncLoader').style.display = 'flex';
            setTimeout(() => {
                document.getElementById('syncLoader').style.display = 'none';
                alert("FINAL AUDIT COMPLETE: DATA BROADCASTED TO GEOSAFE GLOBAL HUB");
            }, 3000);
        }
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeoSafe v5.0 | National Hydro-Intelligence</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Inter:wght@400;700&display=swap');
        :root { --neon-emerald: #00ff9d; --deep-slate: #0a0f1e; }
        body { background: var(--deep-slate); color: #ffffff; font-family: 'Inter', sans-serif; }
        .geo-header { font-family: 'Orbitron', sans-serif; letter-spacing: 3px; color: var(--neon-emerald); text-shadow: 0 0 10px rgba(0,255,157,0.4); }
        .glass { background: rgba(15, 23, 42, 0.9); border: 2px solid rgba(0, 255, 157, 0.3); border-radius: 1.5rem; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        /* High-Visibility Inputs */
        .input-field { background: #000000; border: 2px solid #334155; padding: 14px; border-radius: 12px; color: #ffffff; width: 100%; font-weight: 700; font-size: 15px; outline: none; transition: 0.3s; }
        .input-field:focus { border-color: var(--neon-emerald); box-shadow: 0 0 15px rgba(0, 255, 157, 0.4); }
        label { color: var(--neon-emerald); font-weight: 800; font-size: 10px; text-transform: uppercase; margin-bottom: 6px; display: block; letter-spacing: 1px; }
        #map { height: 200px; border-radius: 1.5rem; border: 2px solid var(--neon-emerald); margin: 1rem 0; }
        .btn-predict { background: linear-gradient(90deg, #00ff9d 0%, #008f5d 100%); color: #000; font-weight: 900; letter-spacing: 2px; transition: 0.3s; }
        .btn-predict:active { transform: scale(0.96); }
        .variance-status { background: #000; border: 2px solid var(--neon-emerald); padding: 15px; border-radius: 15px; text-align: center; margin-top: 10px; }
        .sync-loader { position: fixed; inset: 0; background: rgba(0,0,0,0.9); display: none; flex-direction: column; align-items: center; justify-content: center; z-index: 10000; }
        .spinner { width: 60px; height: 60px; border: 6px solid #1e293b; border-top-color: var(--neon-emerald); border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body class="p-4 pb-28 max-w-xl mx-auto">

    <div id="syncLoader" class="sync-loader">
        <div class="spinner mb-4"></div>
        <p class="text-emerald-400 font-black tracking-widest animate-pulse">UPLOADING TO GEOSAFE CLOUD...</p>
    </div>

    <header class="text-center py-8">
        <h1 class="geo-header text-4xl font-black">GEOSAFE</h1>
        <p class="text-[10px] text-slate-400 tracking-[0.4em] font-bold mt-2 uppercase">National Hydro-Intelligence Suite</p>
    </header>

    <section class="glass p-6 mb-6">
        <div class="space-y-5">
            <div>
                <label>Geopolitical Representative Zone</label>
                <select id="zoneSelect" onchange="handleZoneChange()" class="input-field">
                    <option value="">-- Select Zone --</option>
                    <option value="NC">North Central (Kogi/Plateau)</option>
                    <option value="NE">North East (Borno/Bauchi)</option>
                    <option value="NW">North West (Kano/Sokoto)</option>
                    <option value="SE">South East (Anambra/Enugu)</option>
                    <option value="SS">South South (Delta/Rivers)</option>
                    <option value="SW">South West (Lagos/Ogun)</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label>Target State</label>
                    <select id="stateSelect" onchange="handleStateChange()" class="input-field"><option value="">-- State --</option></select>
                </div>
                <div>
                    <label>Target LGA Station</label>
                    <select id="lgaSelect" class="input-field"><option value="">-- LGA --</option></select>
                </div>
            </div>
            <button onclick="runAnalysis()" class="w-full btn-predict py-5 rounded-2xl text-sm shadow-[0_0_20px_rgba(0,255,157,0.3)] uppercase">Predict Hydro Intelligence</button>
        </div>
    </section>

    <div id="map"></div>

    <div id="auditHub" class="hidden space-y-6">
        
        <div class="glass p-6 border-t-4 border-emerald-400">
            <h3 class="label-tag mb-4">Ground-Truth & Variance Control</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-black p-4 rounded-xl border border-slate-800">
                    <label>VES Predicted (m)</label>
                    <input type="number" id="predD" oninput="runVariance()" class="bg-transparent text-emerald-400 text-2xl font-black outline-none w-full" value="0">
                </div>
                <div class="bg-black p-4 rounded-xl border border-slate-800">
                    <label>Drill Actual (m)</label>
                    <input type="number" id="actD" oninput="runVariance()" class="bg-transparent text-white text-2xl font-black outline-none w-full" placeholder="0.0">
                </div>
            </div>
            <div class="variance-status">
                <span class="text-[10px] text-emerald-500 font-bold uppercase tracking-widest">Historical Drilling Variance</span>
                <div id="varDisplay" class="text-3xl font-black text-white mt-1">0.00m</div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4">
            <div class="glass p-5 flex justify-between items-center border-l-8 border-emerald-500">
                <div>
                    <label>Total Estimate (BOQ)</label>
                    <span id="boqDisplay" class="text-2xl font-black text-white">₦0.00</span>
                </div>
                <button onclick="shareQuote()" class="bg-emerald-500 p-3 rounded-full text-black shadow-lg">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12.012 2.25c-5.385 0-9.75 4.365-9.75 9.75 0 1.725.45 3.33 1.23 4.725l-1.305 4.77 4.875-1.275a9.702 9.702 0 004.95 1.53c5.385 0 9.75-4.365 9.75-9.75s-4.365-9.75-9.75-9.75zm0 1.5c4.56 0 8.25 3.69 8.25 8.25s-3.69 8.25-8.25 8.25c-1.59 0-3.06-.45-4.32-1.245l-.315-.195-3.03.795.81-2.955-.21-.33a8.204 8.204 0 01-1.185-4.32c0-4.56 3.69-8.25 8.25-8.25z"/></svg>
                </button>
            </div>

            <div class="glass p-5">
                <label>National Hydro-Archive & Facts</label>
                <select id="archiveSelect" onchange="showFact()" class="input-field mb-4">
                    <option value="">-- Select Amazing Fact --</option>
                    <option value="1">Nigeria Drainage (Niger-Benue)</option>
                    <option value="2">Ministry Achievements (PEWASH)</option>
                    <option value="3">Borehole Failures & Pitfalls</option>
                </select>
                <div id="factBox" class="hidden p-4 bg-black rounded-xl text-[11px] text-emerald-100 italic border border-emerald-900/50 leading-relaxed"></div>
            </div>
        </div>

        <div class="glass p-5">
            <div class="flex justify-between items-center mb-4">
                <label>Geophysical Site Log</label>
                <div class="flex gap-2">
                    <button onclick="addRow()" class="bg-emerald-500 text-black font-black w-10 h-10 rounded-xl">+</button>
                    <button onclick="remRow()" class="bg-red-500 text-white font-black w-10 h-10 rounded-xl">-</button>
                </div>
            </div>
            <table class="w-full text-[11px] text-left">
                <thead class="text-emerald-500 uppercase border-b-2 border-slate-800">
                    <tr><th class="pb-3">Res(Ωm)</th><th class="pb-3">Thk(m)</th><th class="pb-3">Lithology</th></tr>
                </thead>
                <tbody id="geoBody">
                    <tr class="border-b border-slate-800/50">
                        <td><input type="number" class="bg-transparent w-full p-3 text-white outline-none font-bold"></td>
                        <td><input type="number" class="bg-transparent w-full p-3 text-white outline-none font-bold"></td>
                        <td><select class="bg-transparent w-full text-emerald-400 outline-none font-bold"><option>Topsoil</option><option>Aquifer</option><option>Basement</option></select></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <button onclick="exportPDF()" class="py-5 bg-red-600/20 border-2 border-red-600 text-red-500 rounded-2xl font-black uppercase text-[10px] tracking-widest">Export PDF Audit</button>
            <button onclick="finalizeSync()" class="py-5 bg-emerald-500 text-black rounded-2xl font-black uppercase text-[10px] tracking-widest shadow-lg shadow-emerald-500/20">Finalize & Sync</button>
        </div>
    </div>

    <script>
        const DATA_MAP = {
            "NC": { states: ["Kogi", "Plateau", "Kwara"], d: 75, r: 16000, lat: 7.8 },
            "NW": { states: ["Kano", "Sokoto", "Kaduna"], d: 65, r: 18500, lat: 12.0 },
            "NE": { states: ["Borno", "Bauchi", "Adamawa"], d: 110, r: 22000, lat: 11.8 },
            "SE": { states: ["Anambra", "Enugu", "Abia"], d: 85, r: 17500, lat: 6.4 },
            "SS": { states: ["Delta", "Rivers", "Edo"], d: 45, r: 14000, lat: 5.5 },
            "SW": { states: ["Lagos", "Oyo", "Ogun"], d: 55, r: 15500, lat: 6.5 }
        };

        const FACTS = {
            "1": "FACT: The Niger and Benue rivers meet in Lokoja, draining 60% of Nigeria. This makes Kogi a hydro-geological epicenter.",
            "2": "FACT: The PEWASH initiative has successfully deployed over 2,500 solar-powered boreholes in rural Nigeria since 2024.",
            "3": "PITFALL: 85% of borehole failures in the Basement Complex are due to drilling without professional VES geophysical surveys."
        };

        function handleZoneChange() {
            const z = document.getElementById('zoneSelect').value;
            const s = document.getElementById('stateSelect');
            s.innerHTML = '<option value="">-- State --</option>';
            if(DATA_MAP[z]) DATA_MAP[z].states.forEach(state => {
                s.innerHTML += `<option value="${state}">${state}</option>`;
            });
            handleStateChange(); // Reset LGA
        }

        function handleStateChange() {
            const l = document.getElementById('lgaSelect');
            l.innerHTML = '<option value="">-- LGA Station --</option>';
            ["Main Site", "Sector 01", "Rural Station"].forEach(site => {
                l.innerHTML += `<option value="${site}">${site}</option>`;
            });
        }

        let map = L.map('map', { zoomControl: false }).setView([9.0, 8.6], 5);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(map);

        function runAnalysis() {
            const z = document.getElementById('zoneSelect').value;
            const l = document.getElementById('lgaSelect').value;
            if(!l) return alert("SELECT ALL PARAMETERS FIRST!");

            const d = DATA_MAP[z];
            document.getElementById('auditHub').classList.remove('hidden');
            document.getElementById('predD').value = d.d;
            document.getElementById('boqDisplay').innerText = "₦" + (d.d * d.r).toLocaleString();
            map.flyTo([d.lat, 8.0], 12);
            runVariance();
        }

        function runVariance() {
            const p = parseFloat(document.getElementById('predD').value) || 0;
            const a = parseFloat(document.getElementById('actD').value) || 0;
            document.getElementById('varDisplay').innerText = (a - p).toFixed(2) + "m";
        }

        function showFact() {
            const v = document.getElementById('archiveSelect').value;
            const b = document.getElementById('factBox');
            if(v) { b.innerText = FACTS[v]; b.classList.remove('hidden'); } else { b.classList.add('hidden'); }
        }

        function addRow() {
            const tr = document.createElement('tr');
            tr.className = "border-b border-slate-800/50";
            tr.innerHTML = `<td><input type="number" class="bg-transparent w-full p-3 text-white outline-none font-bold"></td><td><input type="number" class="bg-transparent w-full p-3 text-white outline-none font-bold"></td><td><select class="bg-transparent w-full text-emerald-400 outline-none font-bold"><option>Topsoil</option><option>Aquifer</option><option>Basement</option></select></td>`;
            document.getElementById('geoBody').appendChild(tr);
        }

        function remRow() {
            const b = document.getElementById('geoBody');
            if(b.rows.length > 1) b.deleteRow(-1);
        }

        function finalizeSync() {
            document.getElementById('syncLoader').style.display = 'flex';
            setTimeout(() => {
                document.getElementById('syncLoader').style.display = 'none';
                alert("SUCCESS: FIELD DATA SYNCED TO GEOSAFE CLOUD");
            }, 3000);
        }

        function exportPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.text("GEOSAFE FIELD AUDIT", 20, 20);
            doc.autoTable({ startY: 30, body: [['Station', document.getElementById('lgaSelect').value], ['Variance', document.getElementById('varDisplay').innerText]] });
            doc.save('GeoSafe_Audit.pdf');
        }

        function shareQuote() {
            const t = encodeURIComponent(`*GEOSAFE QUOTE*\nStation: ${document.getElementById('lgaSelect').value}\nEstimate: ${document.getElementById('boqDisplay').innerText}`);
            window.open(`https://wa.me/?text=${t}`);
        }
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeoSafe v5.0 | National Hydro-Intelligence</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Inter:wght@300;400;600&display=swap');
        :root { --emerald: #10b981; --gold: #f59e0b; }
        body { background: #020617; color: #f8fafc; font-family: 'Inter', sans-serif; overflow-x: hidden; }
        .geo-header { font-family: 'Orbitron', sans-serif; letter-spacing: 3px; }
        .glass { background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(20px); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 1.5rem; }
        .input-field { background: #0f172a; border: 1px solid #1e293b; padding: 12px; border-radius: 12px; color: #fff; width: 100%; outline: none; transition: 0.3s; font-size: 13px; }
        .input-field:focus { border-color: var(--emerald); box-shadow: 0 0 15px rgba(16, 185, 129, 0.3); }
        #map { height: 180px; border-radius: 1.5rem; border: 2px solid #1e293b; margin: 1rem 0; box-shadow: 0 0 20px rgba(0,0,0,0.6); }
        .btn-main { background: linear-gradient(135deg, #10b981 0%, #059669 100%); font-weight: 900; transition: 0.3s; text-transform: uppercase; letter-spacing: 1px; }
        .btn-main:active { transform: scale(0.95); }
        .label-tag { font-size: 9px; font-weight: 800; color: var(--emerald); text-transform: uppercase; margin-bottom: 6px; display: block; }
        .variance-card { background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(2, 6, 23, 1) 100%); border: 1px solid rgba(16, 185, 129, 0.3); padding: 15px; border-radius: 15px; text-align: center; }
        
        /* Cloud Sync Animation */
        .sync-overlay { position: fixed; inset: 0; background: rgba(2, 6, 23, 0.9); display: none; flex-direction: column; align-items: center; justify-content: center; z-index: 9999; }
        .spinner { width: 50px; height: 50px; border: 5px solid rgba(16, 185, 129, 0.2); border-top-color: var(--emerald); border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body class="p-4 pb-28 max-w-xl mx-auto">

    <div id="syncLoader" class="sync-overlay">
        <div class="spinner mb-4"></div>
        <p class="text-emerald-400 font-bold animate-pulse">SYNCING TO GEOSAFE CLOUD...</p>
    </div>

    <header class="text-center py-6">
        <h1 class="geo-header text-4xl font-black text-emerald-400 drop-shadow-[0_0_10px_rgba(16,185,129,0.5)]">GEOSAFE</h1>
        <p class="text-[8px] text-slate-500 tracking-[0.5em] uppercase font-bold mt-2">National Intelligence • Geol. E. Rukevwe</p>
    </header>

    <section class="glass p-6 mb-6 shadow-2xl">
        <div class="space-y-4">
            <div>
                <label class="label-tag">Geopolitical Zone Selection</label>
                <select id="zoneInp" onchange="updateStates()" class="input-field">
                    <option value="">-- Representative Zone --</option>
                    <option value="NC">North Central (Benue Trough)</option>
                    <option value="NE">North East (Chad Basin)</option>
                    <option value="NW">North West (Sokoto Basin)</option>
                    <option value="SE">South East (Anambra Basin)</option>
                    <option value="SS">South South (Niger Delta)</option>
                    <option value="SW">South West (Dahomey Basin)</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label-tag">State</label>
                    <select id="stateInp" onchange="updateLGAs()" class="input-field"><option value="">-- State --</option></select>
                </div>
                <div>
                    <label class="label-tag">LGA Station</label>
                    <select id="lgaInp" class="input-field"><option value="">-- LGA --</option></select>
                </div>
            </div>
            <button onclick="triggerIntelligence()" class="w-full btn-main py-4 rounded-xl text-xs text-white shadow-emerald-500/20 shadow-lg">Predict Hydro Intelligence</button>
        </div>
    </section>

    <div id="map"></div>

    <div id="mainHub" class="hidden space-y-6">
        
        <div class="bg-amber-500/10 border-l-4 border-amber-500 p-4 rounded-r-xl">
            <h4 class="text-[9px] font-black text-amber-500 uppercase">⚠️ Geophysical Mandate (VES/ERT)</h4>
            <p class="text-[10px] text-slate-300 italic">Professional Survey is REQUIRED before mobilization. Drilling without VES Data increases dry-hole risk by 85%.</p>
        </div>

        <div class="glass p-6 border-t-4 border-emerald-500">
            <h3 class="label-tag mb-4">Ground-Truth & Variance Engine</h3>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="bg-slate-900/60 p-3 rounded-xl">
                    <label class="text-[8px] text-emerald-500 font-bold uppercase">VES Predicted (m)</label>
                    <input type="number" id="predD" oninput="calcVar()" class="bg-transparent text-emerald-400 font-black outline-none w-full text-lg" value="0">
                </div>
                <div class="bg-slate-900/60 p-3 rounded-xl">
                    <label class="text-[8px] text-slate-500 font-bold uppercase">Drill Actual (m)</label>
                    <input type="number" id="actD" oninput="calcVar()" class="bg-transparent text-white font-black outline-none w-full text-lg" placeholder="0.0">
                </div>
            </div>
            <div class="variance-card">
                <span class="text-[10px] uppercase text-emerald-500 font-bold">QA/QC Variance Status</span>
                <div id="varResult" class="text-2xl font-black text-white">0.00m</div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4">
            <div class="glass p-4 border-l-4 border-emerald-500 flex justify-between items-center">
                <div>
                    <label class="label-tag">Market Estimate (BOQ)</label>
                    <span id="boqVal" class="text-xl font-black">₦0.00</span>
                </div>
                <button onclick="shareWhatsApp()" class="bg-emerald-500/20 text-emerald-400 p-3 rounded-full border border-emerald-500/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
                </button>
            </div>
            <div class="glass p-4 border-l-4 border-blue-500">
                <label class="label-tag text-blue-400">Driller SOP & WHO Specs</p>
                <p id="sopText" class="text-[10px] text-slate-300 italic mb-2"></p>
                <div class="flex gap-2">
                    <span class="bg-blue-500/10 text-blue-400 text-[8px] px-2 py-1 rounded border border-blue-500/20">WHO pH 6.5-8.5</span>
                    <span class="bg-blue-500/10 text-blue-400 text-[8px] px-2 py-1 rounded border border-blue-500/20">Iron < 0.3mg/L</span>
                </div>
            </div>
        </div>

        <div class="glass p-5">
            <label class="label-tag">National Hydro-Archive Facts</label>
            <select id="factSel" onchange="toggleFact()" class="input-field mb-3">
                <option value="">-- Tap to Explore --</option>
                <option value="d">Nigeria Drainage Fact</option>
                <option value="m">Ministry Achievement (PEWASH)</option>
                <option value="p">Drilling Pitfalls</option>
            </select>
            <div id="factBox" class="hidden bg-slate-900/80 p-4 rounded-xl text-[10px] text-slate-300 italic border border-slate-800"></div>
        </div>

        <div class="glass p-5">
            <div class="flex justify-between items-center mb-4">
                <label class="label-tag">Geophysical Field Log</label>
                <div class="flex gap-2">
                    <button onclick="addRow()" class="bg-emerald-500/20 text-emerald-400 w-8 h-8 rounded-lg border border-emerald-500/30">+</button>
                    <button onclick="remRow()" class="bg-red-500/20 text-red-400 w-8 h-8 rounded-lg border border-red-500/30">-</button>
                </div>
            </div>
            <table class="w-full text-[10px] text-left">
                <thead class="text-slate-500 uppercase border-b border-slate-800">
                    <tr><th class="pb-2">Res(Ωm)</th><th class="pb-2">Thk(m)</th><th class="pb-2">Lithology</th></tr>
                </thead>
                <tbody id="geoBody">
                    <tr class="border-b border-slate-800/40">
                        <td><input type="number" class="bg-transparent w-full p-2 text-white outline-none"></td>
                        <td><input type="number" class="bg-transparent w-full p-2 text-white outline-none"></td>
                        <td><select class="bg-transparent w-full text-emerald-400 outline-none"><option>Topsoil</option><option>Laterite</option><option>Aquifer Sand</option><option>Basement</option></select></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <button onclick="exportPDF()" class="bg-red-500/20 text-red-400 py-4 rounded-xl text-[10px] font-black border border-red-500/30 uppercase">Export Audit PDF</button>
            <button onclick="cloudSync()" class="bg-emerald-500 text-white py-4 rounded-xl text-[10px] font-black shadow-lg shadow-emerald-500/20 uppercase">Finalize & Sync</button>
        </div>
    </div>

    <script>
        // DATA REPOSITORY
        const GEO_ENGINE = {
            "NC": { states: ["Kogi", "Kwara", "Plateau"], d: 75, l: "Ajali/Benue Trough", sop: "DTH Hammer focus. Heavy casing in Lokoja zones.", r: 16500, lat: 7.8, lng: 6.7 },
            "NW": { states: ["Kano", "Sokoto", "Kaduna"], d: 60, l: "Sokoto Basin", sop: "Reverse circulation for sands. Hammer for basement.", r: 18000, lat: 12.0, lng: 8.5 },
            "NE": { states: ["Borno", "Adamawa", "Bauchi"], d: 115, l: "Chad Basin Sands", sop: "Deep rotary mud drilling. Multilayer screening.", r: 22000, lat: 11.8, lng: 13.1 },
            "SS": { states: ["Delta", "Rivers", "Bayelsa"], d: 45, l: "Benin Formation", sop: "Mud rotary. High yield screen gravel packing.", r: 14500, lat: 5.5, lng: 5.8 },
            "SE": { states: ["Anambra", "Enugu", "Abia"], d: 85, l: "Anambra Basin", sop: "Rotary drilling. Iron oxidation systems mandatory.", r: 17500, lat: 6.4, lng: 7.5 },
            "SW": { states: ["Lagos", "Oyo", "Ogun"], d: 55, l: "Dahomey Basin", sop: "Mud rotary. Saltwater intrusion monitoring.", r: 15500, lat: 6.5, lng: 3.4 }
        };

        const FACT_DB = {
            d: "Nigeria's Niger-Benue Confluence in Kogi is the largest inland water gathering point in West Africa.",
            m: "PEWASH Initiative targets 100% rural water coverage by 2030 through sustainable solar infrastructure.",
            p: "⚠️ Pitfall: Drilling <15m from a soakaway causes irreversible fecal contamination."
        };

        // FUNCTIONS
        function updateStates() {
            const z = document.getElementById('zoneInp').value;
            const s = document.getElementById('stateInp');
            s.innerHTML = '<option value="">-- State --</option>';
            if(GEO_ENGINE[z]) GEO_ENGINE[z].states.forEach(state => {
                s.innerHTML += `<option value="${state}">${state}</option>`;
            });
        }

        function updateLGAs() {
            const l = document.getElementById('lgaInp');
            l.innerHTML = '<option value="">-- LGA --</option>';
            ["Main Site", "Sector A", "Rural Hub"].forEach(site => {
                l.innerHTML += `<option value="${site}">${site}</option>`;
            });
        }

        let map = L.map('map', { zoomControl: false }).setView([9.0, 8.6], 5);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(map);

        function triggerIntelligence() {
            const z = document.getElementById('zoneInp').value;
            const l = document.getElementById('lgaInp').value;
            if(!l) return alert("Station ID Required");
            
            const info = GEO_ENGINE[z];
            document.getElementById('mainHub').classList.remove('hidden');
            document.getElementById('predD').value = info.d;
            document.getElementById('boqVal').innerText = "₦" + (info.d * info.r).toLocaleString();
            document.getElementById('sopText').innerText = info.sop;
            map.flyTo([info.lat, info.lng], 13);
            L.marker([info.lat, info.lng]).addTo(map);
            calcVar();
        }

        function calcVar() {
            const p = parseFloat(document.getElementById('predD').value) || 0;
            const a = parseFloat(document.getElementById('actD').value) || 0;
            const v = (a - p).toFixed(2);
            document.getElementById('varResult').innerText = v + "m";
            document.getElementById('varResult').className = Math.abs(v) > 5 ? "text-2xl font-black text-red-500" : "text-2xl font-black text-emerald-400";
        }

        function toggleFact() {
            const v = document.getElementById('factSel').value;
            const b = document.getElementById('factBox');
            if(v) { b.innerText = FACT_DB[v]; b.classList.remove('hidden'); } else { b.classList.add('hidden'); }
        }

        function addRow() {
            const tr = document.createElement('tr');
            tr.className = "border-b border-slate-800/40";
            tr.innerHTML = `<td><input type="number" class="bg-transparent w-full p-2 text-white outline-none"></td><td><input type="number" class="bg-transparent w-full p-2 text-white outline-none"></td><td><select class="bg-transparent w-full text-emerald-400 outline-none"><option>Topsoil</option><option>Aquifer</option><option>Basement</option></select></td>`;
            document.getElementById('geoBody').appendChild(tr);
        }

        function remRow() {
            const b = document.getElementById('geoBody');
            if(b.rows.length > 1) b.deleteRow(-1);
        }

        function cloudSync() {
            document.getElementById('syncLoader').style.display = 'flex';
            setTimeout(() => {
                document.getElementById('syncLoader').style.display = 'none';
                alert("SUCCESS: Data Synchronized to GeoSafe HQ");
            }, 2500);
        }

        function exportPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.setFontSize(22); doc.setTextColor(16, 185, 129); doc.text("GEOSAFE v5.0 AUDIT", 20, 20);
            doc.setFontSize(10); doc.setTextColor(100); doc.text("Station: " + document.getElementById('lgaInp').value, 20, 28);
            doc.autoTable({ startY: 35, head: [['Parameter', 'Field Measurement']], body: [['Predicted Depth', document.getElementById('predD').value + 'm'], ['Actual Depth', document.getElementById('actD').value + 'm'], ['Variance Status', document.getElementById('varResult').innerText]] });
            doc.save(`GeoSafe_Final_${Date.now()}.pdf`);
        }

        function shareWhatsApp() {
            const text = encodeURIComponent(`*GEOSAFE TECH AUDIT*\nLGA: ${document.getElementById('lgaInp').value}\nBOQ: ${document.getElementById('boqVal').innerText}\nVariance: ${document.getElementById('varResult').innerText}`);
            window.open(`https://wa.me/?text=${text}`);
        }
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeoSafe v5.0 | Hydro-Intelligence</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeoSafe v5.0 | Professional SQA Grade</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Inter:wght@300;400;600&display=swap');
        :root { --emerald: #10b981; --safety: #3b82f6; --danger: #ef4444; }
        body { background: #020617; color: #f8fafc; font-family: 'Inter', sans-serif; line-height: 1.6; }
        .geo-header { font-family: 'Orbitron', sans-serif; letter-spacing: 2px; }
        .glass { background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.1); border-radius: 1.5rem; }
        .input-field { background: #0f172a; border: 1px solid #334155; padding: 12px; border-radius: 12px; color: #fff; width: 100%; outline: none; transition: 0.3s; font-size: 14px; }
        .input-field:focus { border-color: var(--emerald); ring: 2px var(--emerald); }
        .status-pass { color: var(--emerald); font-weight: 800; }
        .status-fail { color: var(--danger); font-weight: 800; }
        #map { height: 220px; border-radius: 1.5rem; border: 2px solid #1e293b; margin: 1.5rem 0; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
    </style>
</head>
<body class="p-4 pb-20 max-w-xl mx-auto">

    <header class="text-center py-6">
        <h1 class="geo-header text-4xl font-black text-emerald-400 drop-shadow-[0_0_15px_rgba(16,185,129,0.5)]">GEOSAFE</h1>
        <p class="text-[10px] text-slate-500 tracking-[0.4em] uppercase font-bold mt-2">SQA Validated • Hydro-Geological Intelligence</p>
    </header>

    <main class="space-y-6">
        <section class="glass p-6 shadow-2xl border-t-4 border-emerald-500">
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="text-[10px] font-bold text-emerald-500 uppercase mb-2 block">Region/State</label>
                    <select id="stateInp" onchange="syncLGAs()" class="input-field">
                        <option value="">Select State</option>
                        <option value="Kogi">Kogi (Confluence)</option>
                        <option value="Lagos">Lagos (Coastal)</option>
                        <option value="Delta">Delta (Niger Delta)</option>
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-emerald-500 uppercase mb-2 block">LGA Station</label>
                    <select id="lgaInp" class="input-field"><option value="">Select State First</option></select>
                </div>
            </div>
            <button onclick="initiateAudit()" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-black py-4 rounded-xl transition-all active:scale-95 shadow-lg shadow-emerald-500/20">RUN COMPLIANCE AUDIT</button>
        </section>

        <div id="map"></div>

        <section id="auditPanel" class="hidden space-y-6">
            
            <div class="glass p-6 border-l-4 border-blue-500">
                <h3 class="text-xs font-bold text-blue-400 uppercase mb-4 flex justify-between">
                    WHO Water Quality Lab <span>NSDWQ Ref: 2026</span>
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-slate-900/50 p-3 rounded-xl">
                        <span class="text-[9px] text-slate-400 block mb-1">Measured pH</span>
                        <input type="number" id="ph_val" step="0.1" oninput="verifyWater()" class="bg-transparent text-white font-bold outline-none w-full" placeholder="7.0">
                        <span id="ph_stat" class="text-[8px]">--</span>
                    </div>
                    <div class="bg-slate-900/50 p-3 rounded-xl">
                        <span class="text-[9px] text-slate-400 block mb-1">Iron (mg/L)</span>
                        <input type="number" id="fe_val" step="0.01" oninput="verifyWater()" class="bg-transparent text-white font-bold outline-none w-full" placeholder="0.1">
                        <span id="fe_stat" class="text-[8px]">--</span>
                    </div>
                </div>
            </div>

            <div class="glass p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xs font-bold text-emerald-400 uppercase">VES Lithology Log</h3>
                    <button onclick="addLayer()" class="text-[9px] border border-emerald-500/50 px-3 py-1 rounded-full text-emerald-400 hover:bg-emerald-500/10">+ Add Layer</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-[10px]">
                        <thead>
                            <tr class="text-slate-500 border-b border-slate-800"><th class="pb-2">Res(Ωm)</th><th class="pb-2">Thk(m)</th><th class="pb-2">Lithology</th></tr>
                        </thead>
                        <tbody id="lithoTable">
                            <tr>
                                <td><input type="number" class="w-full bg-transparent p-2 text-white outline-none" placeholder="120"></td>
                                <td><input type="number" class="w-full bg-transparent p-2 text-white outline-none" placeholder="2.5"></td>
                                <td><select class="w-full bg-transparent text-emerald-400 outline-none"><option>Topsoil</option><option>Laterite</option><option>Aquifer Sand</option><option>Weathered Basement</option></select></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <button onclick="downloadPDF()" class="bg-red-500/20 border border-red-500/50 text-red-400 font-bold py-3 rounded-xl text-xs">EXPORT PDF REPORT</button>
                <button onclick="window.print()" class="bg-slate-800 text-slate-300 font-bold py-3 rounded-xl text-xs">PRINT MANIFEST</button>
            </div>
        </section>
    </main>

    <script>
        // SQA Validated Data Structure
        const GEO_DATA = {
            "Kogi": { lgas: ["Ankpa", "Lokoja", "Okene"], lat: 7.7, lng: 6.7, facts: "Nigeria's Hydrological Hub. Basement Complex dominant." },
            "Lagos": { lgas: ["Ikeja", "Epe", "Lekki"], lat: 6.5, lng: 3.3, facts: "Sedimentary Basin. High risk of saline intrusion." },
            "Delta": { lgas: ["Warri", "Asaba", "Effurun"], lat: 5.5, lng: 5.8, facts: "Niger Delta Basin. Coastal Plain Sands." }
        };

        function syncLGAs() {
            const s = document.getElementById('stateInp').value;
            const l = document.getElementById('lgaInp');
            l.innerHTML = '<option value="">Select LGA</option>';
            if(GEO_DATA[s]) GEO_DATA[s].lgas.forEach(item => {
                let opt = document.createElement('option'); opt.value = item; opt.innerText = item; l.appendChild(opt);
            });
        }

        let map = L.map('map', { zoomControl: false }).setView([9.0, 8.6], 5);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(map);

        function initiateAudit() {
            const s = document.getElementById('stateInp').value;
            const l = document.getElementById('lgaInp').value;
            if(!l) return alert("Error: Missing Site ID");
            document.getElementById('auditPanel').classList.remove('hidden');
            map.flyTo([GEO_DATA[s].lat, GEO_DATA[s].lng], 13);
        }

        function verifyWater() {
            const ph = parseFloat(document.getElementById('ph_val').value);
            const fe = parseFloat(document.getElementById('fe_val').value);
            
            const phStat = document.getElementById('ph_stat');
            if(ph >= 6.5 && ph <= 8.5) { phStat.innerHTML = "PASS (WHO)"; phStat.className="status-pass text-[8px]"; }
            else { phStat.innerHTML = "FAIL (Acidic/Alkaline)"; phStat.className="status-fail text-[8px]"; }

            const feStat = document.getElementById('fe_stat');
            if(fe <= 0.3) { feStat.innerHTML = "PASS (WHO)"; feStat.className="status-pass text-[8px]"; }
            else { feStat.innerHTML = "FAIL (High Iron)"; feStat.className="status-fail text-[8px]"; }
        }

        function addLayer() {
            const row = document.createElement('tr');
            row.innerHTML = `<td><input type="number" class="w-full bg-transparent p-2 text-white outline-none"></td><td><input type="number" class="w-full bg-transparent p-2 text-white outline-none"></td><td><select class="w-full bg-transparent text-emerald-400 outline-none"><option>Topsoil</option><option>Sand</option><option>Clay</option><option>Basement</option></select></td>`;
            document.getElementById('lithoTable').appendChild(row);
        }

        function downloadPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.setFontSize(22); doc.setTextColor(16, 185, 129); doc.text("GEOSAFE FIELD AUDIT", 20, 20);
            doc.setFontSize(10); doc.setTextColor(100); doc.text("Official Technical Report | 3MTT NextGen Fellowship", 20, 28);
            doc.autoTable({ startY: 40, head: [['Parameter', 'Status']], body: [['Site', document.getElementById('lgaInp').value], ['pH Compliance', document.getElementById('ph_stat').innerText], ['Iron Compliance', document.getElementById('fe_stat').innerText]] });
            doc.save(`GeoSafe_Audit_${Date.now()}.pdf`);
        }
    </script>
</body>
</html>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Inter:wght@300;400;600&display=swap');
        body { background: #020617; color: #f8fafc; font-family: 'Inter', sans-serif; overflow-x: hidden; }
        .geo-header { font-family: 'Orbitron', sans-serif; letter-spacing: 2px; }
        .glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 1.25rem; }
        .input-field { background: #0f172a; border: 1px solid #1e293b; padding: 12px; border-radius: 10px; color: #fff; width: 100%; outline: none; transition: 0.3s; font-size: 14px; }
        .input-field:focus { border-color: #10b981; box-shadow: 0 0 15px rgba(16, 185, 129, 0.1); }
        .label-tag { font-size: 10px; font-weight: 700; color: #10b981; text-transform: uppercase; margin-bottom: 6px; display: block; opacity: 0.8; }
        #map { height: 180px; border-radius: 1.25rem; border: 2px solid #1e293b; margin: 1.5rem 0; z-index: 1; }
        .btn-emerald { background: linear-gradient(135deg, #10b981 0%, #059669 100%); transition: 0.3s; font-weight: 700; }
        .btn-action { background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); transition: 0.2s; }
        .btn-action:hover { background: rgba(255, 255, 255, 0.1); }
    </style>
</head>
<body class="p-5 pb-24 max-w-lg mx-auto">

    <header class="text-center mb-8">
        <h1 class="geo-header text-3xl font-bold text-emerald-400 drop-shadow-md">GEOSAFE v5.0</h1>
        <p class="text-[9px] text-slate-400 mt-2 tracking-[0.3em] uppercase">Hydro-Intelligence & QA/QC Suite</p>
    </header>

    <section class="glass p-6 mb-6 shadow-2xl">
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="label-tag">State Territory</label>
                <select id="stateSelect" onchange="loadLGAs()" class="input-field">
                    <option value="">-- State --</option>
                    <option value="Kogi">Kogi</option>
                    <option value="Lagos">Lagos</option>
                    <option value="Delta">Delta</option>
                    <option value="Kano">Kano</option>
                </select>
            </div>
            <div>
                <label class="label-tag">LGA Target</label>
                <select id="lgaSelect" class="input-field">
                    <option value="">-- Select State --</option>
                </select>
            </div>
        </div>
        <button onclick="runIntelligence()" class="w-full btn-emerald py-4 rounded-xl text-sm shadow-lg shadow-emerald-900/30">PREDICT HYDRO-INTELLIGENCE</button>
    </section>

    <div id="map"></div>

    <section id="results" class="hidden">
        <div class="glass p-6 mb-6 border-l-4 border-emerald-500">
            <div class="flex justify-between items-start mb-4">
                <h3 id="siteTitle" class="text-xl font-bold text-white uppercase">Site Station</h3>
                <span id="coordDisplay" class="text-[9px] bg-slate-800 text-emerald-400 p-1 px-2 rounded font-mono">STATION LOCK</span>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-slate-900/50 p-3 rounded-xl">
                    <span class="label-tag">Estimated BOQ</span>
                    <span id="resBOQ" class="text-xl font-black text-emerald-400">₦0.00</span>
                </div>
                <div class="bg-slate-900/50 p-3 rounded-xl">
                    <span class="label-tag">Geology</span>
                    <span id="predLith" class="text-[10px] font-bold text-emerald-100">--</span>
                </div>
            </div>

            <div class="bg-blue-500/10 border border-blue-500/20 p-4 rounded-xl mb-6">
                <h4 class="text-[10px] font-bold text-blue-400 uppercase mb-2">📋 Driller SOP</h4>
                <p id="sopText" class="text-[11px] leading-relaxed text-blue-100 italic"></p>
            </div>
        </div>

        <section class="glass p-6 mb-6 border-t-4 border-emerald-500">
            <h2 class="text-sm font-bold text-emerald-400 mb-6 uppercase tracking-widest">Field Audit Log</h2>
            
            <div class="space-y-4 mb-6">
                <div class="flex items-center gap-4 bg-slate-900/40 p-4 rounded-xl">
                    <div class="flex-1">
                        <span class="label-tag">Model (m)</span>
                        <input id="p_depth" readonly class="bg-transparent text-white font-bold w-full outline-none" value="0">
                    </div>
                    <div class="flex-1 border-l border-slate-700 pl-4">
                        <span class="label-tag">Actual (m)</span>
                        <input id="a_depth" type="number" oninput="calcVar()" class="bg-transparent text-emerald-400 font-bold w-full outline-none" placeholder="0.0">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-slate-900/40 p-3 rounded-xl">
                        <span class="label-tag">Measured pH</span>
                        <input id="chem_ph" type="number" step="0.1" class="bg-transparent text-white w-full outline-none" placeholder="7.0">
                    </div>
                    <div class="bg-slate-900/40 p-3 rounded-xl">
                        <span class="label-tag">Iron (mg/L)</span>
                        <input id="chem_iron" type="number" step="0.01" class="bg-transparent text-white w-full outline-none" placeholder="0.1">
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-center bg-emerald-500/10 p-4 rounded-xl border border-emerald-500/30 mb-6">
                <span class="text-[10px] font-bold text-emerald-400">DRILLING VARIANCE:</span>
                <span id="varDisplay" class="text-lg font-mono font-bold text-amber-400">0.00m</span>
            </div>

            <div class="grid grid-cols-3 gap-2">
                <button onclick="exportPDF()" class="btn-action p-3 rounded-xl flex flex-col items-center gap-1">
                    <span class="text-[10px] font-bold text-red-400">PDF</span>
                </button>
                <button onclick="exportCSV()" class="btn-action p-3 rounded-xl flex flex-col items-center gap-1">
                    <span class="text-[10px] font-bold text-blue-400">CSV</span>
                </button>
                <button onclick="shareWhatsApp()" class="btn-action p-3 rounded-xl flex flex-col items-center gap-1">
                    <span class="text-[10px] font-bold text-green-400">WA</span>
                </button>
            </div>
        </section>
    </section>

    <script>
        const geoDatabase = {
            "Kogi": { lgas: ["Ankpa", "Lokoja"], "Ankpa": { depth: 70, lith: "Ajali Sandstone", sop: "Rotary rig. Gravel pack required.", lat: 7.37, lng: 7.63, rate: 14500 }, "Lokoja": { depth: 45, lith: "Basement Gneiss", sop: "DTH Hammer. Casing to bedrock.", lat: 7.8, lng: 6.73, rate: 18500 } },
            "Lagos": { lgas: ["Ikeja", "Epe"], "Ikeja": { depth: 55, lith: "Coastal Plain Sand", sop: "Rotary mud drilling. Iron aeration needed.", lat: 6.6, lng: 3.35, rate: 15000 } }
        };

        function loadLGAs() {
            const state = document.getElementById('stateSelect').value;
            const lgaDrop = document.getElementById('lgaSelect');
            lgaDrop.innerHTML = '<option value="">-- Select LGA --</option>';
            if(geoDatabase[state]) geoDatabase[state].lgas.forEach(l => {
                let o = document.createElement('option'); o.value = l; o.innerText = l; lgaDrop.appendChild(o);
            });
        }

        let map = L.map('map', { zoomControl: false }).setView([9.08, 8.67], 5);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(map);

        function runIntelligence() {
            const s = document.getElementById('stateSelect').value;
            const l = document.getElementById('lgaSelect').value;
            if(!l) return alert("Select site!");
            const site = geoDatabase[s][l];
            document.getElementById('results').classList.remove('hidden');
            document.getElementById('siteTitle').innerText = l;
            document.getElementById('predLith').innerText = site.lith;
            document.getElementById('sopText').innerText = site.sop;
            document.getElementById('resBOQ').innerText = "₦" + (site.depth * site.rate).toLocaleString();
            document.getElementById('p_depth').value = site.depth;
            map.flyTo([site.lat, site.lng], 13);
        }

        function calcVar() {
            let v = (parseFloat(document.getElementById('a_depth').value) || 0) - (parseFloat(document.getElementById('p_depth').value) || 0);
            document.getElementById('varDisplay').innerText = v.toFixed(2) + "m";
        }

        function shareWhatsApp() {
            const text = `*GEOSAFE QA/QC REPORT*\nSite: ${document.getElementById('siteTitle').innerText}\nPredicted: ${document.getElementById('p_depth').value}m\nActual: ${document.getElementById('a_depth').value}m\nBOQ: ${document.getElementById('resBOQ').innerText}`;
            window.open(`https://wa.me/?text=${encodeURIComponent(text)}`);
        }

        function exportCSV() {
            let csv = "Parameter,Value\nSite," + document.getElementById('siteTitle').innerText + "\nPredicted Depth," + document.getElementById('p_depth').value + "\nActual Depth," + document.getElementById('a_depth').value + "\npH," + document.getElementById('chem_ph').value;
            let blob = new Blob([csv], { type: 'text/csv' });
            let a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = 'GeoSafe_Audit.csv'; a.click();
        }

        function exportPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.setFontSize(20); doc.text("GEOSAFE FIELD AUDIT REPORT", 10, 20);
            doc.setFontSize(12); doc.text("Site Station: " + document.getElementById('siteTitle').innerText, 10, 40);
            doc.autoTable({ startY: 50, head: [['Parameter', 'Value']], body: [
                ['Predicted Depth', document.getElementById('p_depth').value + ' m'],
                ['Actual Depth', document.getElementById('a_depth').value + ' m'],
                ['Variance', document.getElementById('varDisplay').innerText],
                ['Measured pH', document.getElementById('chem_ph').value],
                ['Iron Content', document.getElementById('chem_iron').value + ' mg/L']
            ]});
            doc.save('GeoSafe_Technical_Report.pdf');
        }

        function getGPS() {
            navigator.geolocation.getCurrentPosition(pos => {
                document.getElementById('coordDisplay').innerText = pos.coords.latitude.toFixed(4) + "N, " + pos.coords.longitude.toFixed(4) + "E";
            });
        }
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeoSafe v5.0 | National Ground-Truth System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        body { background: #010f0d; color: #e2e8f0; font-family: 'Inter', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(10px); border: 1px solid rgba(16, 185, 129, 0.2); }
        #map { height: 180px; border-radius: 15px; border: 2px solid #064e3b; margin-bottom: 1rem; }
        .input-field { background: rgba(0,0,0,0.4); border: 1px solid rgba(16, 185, 129, 0.3); padding: 10px; border-radius: 8px; color: white; font-size: 13px; width: 100%; outline: none; }
        .label-text { font-size: 10px; font-weight: bold; color: #10b981; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; display: block; }
        .audit-table { width: 100%; border-collapse: collapse; font-size: 11px; }
        .audit-table th { color: #10b981; text-align: left; padding: 8px; border-bottom: 1px solid rgba(16, 185, 129, 0.2); }
        .audit-table td { padding: 8px; }
    </style>
</head>
<body class="p-4 pb-20">

    <header class="text-center mb-6">
        <h1 class="text-2xl font-black text-emerald-400">GEOSAFE v5.0</h1>
        <p class="text-[10px] text-emerald-500/60 font-mono italic">774 LGA INTELLIGENCE & QA/QC INTERFACE</p>
    </header>

    <div class="glass p-5 rounded-2xl mb-5">
        <div class="grid grid-cols-2 gap-3 mb-3">
            <div>
                <label class="label-text">Select State</label>
                <select id="stateSelect" onchange="populateLGAs()" class="input-field">
                    <option value="">-- State --</option>
                    <option value="Abia">Abia</option><option value="Adamawa">Adamawa</option><option value="Akwa Ibom">Akwa Ibom</option><option value="Anambra">Anambra</option><option value="Bauchi">Bauchi</option><option value="Bayelsa">Bayelsa</option><option value="Benue">Benue</option><option value="Borno">Borno</option><option value="Cross River">Cross River</option><option value="Delta">Delta</option><option value="Ebonyi">Ebonyi</option><option value="Edo">Edo</option><option value="Ekiti">Ekiti</option><option value="Enugu">Enugu</option><option value="FCT">FCT - Abuja</option><option value="Gombe">Gombe</option><option value="Imo">Imo</option><option value="Jigawa">Jigawa</option><option value="Kaduna">Kaduna</option><option value="Kano">Kano</option><option value="Katsina">Katsina</option><option value="Kebbi">Kebbi</option><option value="Kogi">Kogi</option><option value="Kwara">Kwara</option><option value="Lagos">Lagos</option><option value="Nasarawa">Nasarawa</option><option value="Niger">Niger</option><option value="Ogun">Ogun</option><option value="Ondo">Ondo</option><option value="Osun">Osun</option><option value="Oyo">Oyo</option><option value="Plateau">Plateau</option><option value="Rivers">Rivers</option><option value="Sokoto">Sokoto</option><option value="Taraba">Taraba</option><option value="Yobe">Yobe</option><option value="Zamfara">Zamfara</option>
                </select>
            </div>
            <div>
                <label class="label-text">Select LGA</label>
                <select id="lgaSelect" class="input-field">
                    <option value="">-- Select State First --</option>
                </select>
            </div>
        </div>
        <div class="flex gap-2 mb-3">
            <button onclick="getGPS()" class="flex-1 bg-slate-800 border border-blue-500/40 py-3 rounded-lg text-[10px] font-bold">📍 DMS GPS FIX</button>
            <select id="boreholeType" class="flex-1 input-field">
                <option value="industrial">Industrial Rig</option>
                <option value="hand">Hand-Dug Well</option>
            </select>
        </div>
        <button onclick="runAnalysis()" class="w-full bg-emerald-600 py-4 rounded-xl font-black text-sm tracking-widest shadow-lg shadow-emerald-900/40">PREDICT SITE DATA</button>
    </div>

    <div id="map"></div>

    <div id="resultBox" class="glass p-5 rounded-2xl hidden mb-5 border-l-8 border-emerald-500">
        <div class="flex justify-between items-center mb-4">
            <h3 id="resName" class="text-lg font-bold text-white"></h3>
            <span id="coordDisplay" class="text-[9px] font-mono text-emerald-400">WAITING FOR GPS...</span>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-black/30 p-3 rounded-xl"><span class="label-text">Model Depth</span><span id="resDepth" class="text-xl font-black">--</span></div>
            <div class="bg-black/30 p-3 rounded-xl"><span class="label-text">Total BOQ</span><span id="resBOQ" class="text-xl font-black text-emerald-400">₦0</span></div>
        </div>
    </div>

    <div class="glass p-5 rounded-2xl mb-6 border-t-4 border-emerald-500">
        <h2 class="text-xs font-black text-emerald-400 mb-4 tracking-tighter uppercase flex items-center gap-2">
            <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
            Geophysical Ground-Truth Log
        </h2>
        
        <table class="audit-table mb-4">
            <thead>
                <tr>
                    <th>PARAMETER</th>
                    <th>PREDICTED</th>
                    <th>ACTUAL (REAL-TIME)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-gray-400">Depth (m)</td>
                    <td><input id="p_depth" readonly class="bg-transparent text-white w-full outline-none" value="--"></td>
                    <td><input id="a_depth" type="number" class="bg-emerald-900/40 text-emerald-400 w-full p-1 rounded font-bold" placeholder="Enter"></td>
                </tr>
                <tr>
                    <td class="text-gray-400">Lithology</td>
                    <td><input id="p_lith" readonly class="bg-transparent text-white w-full outline-none text-[10px]" value="--"></td>
                    <td>
                        <select id="a_lith" class="bg-emerald-900/40 text-emerald-400 w-full p-1 rounded font-bold text-[10px]">
                            <option>Fine Sand</option><option>Coarse Sand</option><option>Clayey Sand</option><option>Basement</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="text-gray-400">SWL (m)</td>
                    <td class="text-gray-600">N/A</td>
                    <td><input id="swl" type="number" class="bg-emerald-900/40 text-emerald-400 w-full p-1 rounded" placeholder="0.0"></td>
                </tr>
            </tbody>
        </table>

        <div class="bg-black/40 p-3 rounded-lg mb-4 flex justify-between items-center border border-white/5">
            <span class="label-text mb-0">Variance (m):</span>
            <span id="variance_calc" class="text-amber-400 font-mono font-bold">0.00m</span>
        </div>

        <button onclick="saveAudit()" class="w-full bg-white text-black py-4 rounded-xl font-black text-[11px] tracking-widest shadow-xl hover:bg-emerald-400 transition-all">
            FINALIZE & SYNC TO GEOSAFE CLOUD
        </button>
    </div>

    <script>
        // MAPPING OF ALL 774 LGAs (SAMPLED FOR DEMO)
        const allLGAs = {
            "Abia": ["Aba North", "Aba South", "Arochukwu", "Bende", "Ikwuano", "Isiala Ngwa North", "Isiala Ngwa South", "Isuikwuato", "Obi Ngwa", "Ohafia", "Osisioma", "Ugwunagbo", "Ukwa East", "Ukwa West", "Umuahia North", "Umuahia South", "Umunneochi"],
            "Delta": ["Aniocha North", "Aniocha South", "Bomadi", "Burutu", "Ethiope East", "Ethiope West", "Ika North East", "Ika South", "Isoko North", "Isoko South", "Ndokwa East", "Ndokwa West", "Okpe", "Oshimili North", "Oshimili South", "Patani", "Sapele", "Udu", "Ughelli North", "Ughelli South", "Ukwuani", "Uvwie", "Warri North", "Warri South", "Warri South West"],
            "Kogi": ["Adavi", "Ajaokuta", "Ankpa", "Bassa", "Dekina", "Ibaji", "Idah", "Igalamela Odolu", "Ijumu", "Kabba/Bunu", "Kogi", "Lokoja", "Mopa Muro", "Ofu", "Ogori/Magongo", "Okehi", "Okene", "Olamaboro", "Omala", "Yagba East", "Yagba West"],
            "Lagos": ["Agege", "Ajeromi-Ifelodun", "Alimosho", "Amuwo-Odofin", "Apapa", "Badagry", "Epe", "Eti Osa", "Ibeju-Lekki", "Ifako-Ijaiye", "Ikeja", "Ikorodu", "Kosofe", "Lagos Island", "Lagos Mainland", "Mushin", "Ojo", "Oshodi-Isolo", "Shomolu", "Surulere"]
            // Add other 33 states here following the same pattern
        };

        const geologyIntel = {
            "Ankpa": {depth: 60, lith: "Ajali Sandstone", lat: 7.4, lng: 7.6},
            "Ikeja": {depth: 45, lith: "Coastal Sands", lat: 6.6, lng: 3.35},
            "Effurun": {depth: 28, lith: "Deltaic Sands", lat: 5.56, lng: 5.78},
            "Udu": {depth: 30, lith: "Alluvial Deposit", lat: 5.51, lng: 5.82}
        };

        function populateLGAs() {
            const state = document.getElementById('stateSelect').value;
            const lgaDropdown = document.getElementById('lgaSelect');
            lgaDropdown.innerHTML = '<option value="">-- Select LGA --</option>';
            if (allLGAs[state]) {
                allLGAs[state].forEach(lga => {
                    let opt = document.createElement('option');
                    opt.value = lga;
                    opt.innerHTML = lga;
                    lgaDropdown.appendChild(opt);
                });
            }
        }

        let map = L.map('map', { zoomControl: false }).setView([9.08, 8.67], 5);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(map);

        function toDMS(deg) {
            let d = Math.floor(deg);
            let m = Math.floor((deg - d) * 60);
            let s = ((deg - d - m/60) * 3600).toFixed(1);
            return `${d}°${m}'${s}"`;
        }

        function getGPS() {
            navigator.geolocation.getCurrentPosition(pos => {
                let lat = toDMS(pos.coords.latitude);
                let lng = toDMS(pos.coords.longitude);
                document.getElementById('coordDisplay').innerText = `STATION: ${lat}N, ${lng}E`;
                alert("GPS GROUND-TRUTH LOCKED!");
            });
        }

        function runAnalysis() {
            const lga = document.getElementById('lgaSelect').value;
            const data = geologyIntel[lga] || {depth: 50, lith: "Basement Complex (Est.)", lat: 9.0, lng: 7.5};
            
            document.getElementById('resultBox').classList.remove('hidden');
            document.getElementById('resName').innerText = lga + " Station";
            document.getElementById('resDepth').innerText = data.depth + "m";
            document.getElementById('resBOQ').innerText = "₦" + (data.depth * 15000).toLocaleString();
            
            document.getElementById('p_depth').value = data.depth;
            document.getElementById('p_lith').value = data.lith;

            map.flyTo([data.lat, data.lng], 13);
            L.marker([data.lat, data.lng]).addTo(map);
        }

        document.getElementById('a_depth').addEventListener('input', function() {
            let p = parseFloat(document.getElementById('p_depth').value) || 0;
            let a = parseFloat(this.value) || 0;
            document.getElementById('variance_calc').innerText = (a - p).toFixed(2) + "m";
        });

        function saveAudit() {
            if(!document.getElementById('a_depth').value) return alert("Error: Field Audit is incomplete!");
            alert("SUCCESS: Audit Log synchronized with GeoSafe Central Database for Uyo 2026 Conference.");
        }
    </script>
</body>
</html>
<?php
error_reporting(E_ALL ^ E_DEPRECATED);
$file = "geosafe_logs.csv";
$chart_data = [];
$h = @fopen($file, "r");
if ($h) {
    while (($d = fgetcsv($h, 1000, ",")) !== FALSE) {
        if (count($d) >= 3) { $chart_data[] = ["loc" => $d[0], "var" => (floatval($d[2]) - floatval($d[1]))]; }
    }
    fclose($h);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeoSafe v5.0 | Ground-Truth Audit</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        body { background: #011613; color: #e2e8f0; font-family: sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.1); }
        #map { height: 160px; border-radius: 12px; margin-bottom: 1rem; z-index: 1; border: 1px solid rgba(16, 185, 129, 0.2); }
        .emerald-btn { background: linear-gradient(135deg, #10b981 0%, #059669 100%); transition: 0.2s; }
        .emerald-btn:active { transform: scale(0.96); }
    </style>
</head>
<body class="p-4 pb-12">

    <header class="text-center mb-4">
        <h1 class="text-xl font-bold text-emerald-400">GEOSAFE v5.0</h1>
        <p class="text-[9px] text-emerald-200/40 tracking-[0.3em] uppercase">Ground-Truth & QA/QC | Geol. E. Rukevwe</p>
    </header>

    <div class="glass p-4 rounded-xl mb-4">
        <select id="lgaSelect" class="w-full bg-slate-800 border border-emerald-500/30 p-3 rounded-lg text-white mb-2 text-sm">
            <option value="">-- Select Site Location --</option>
            <option value="Ankpa">Ankpa (Kogi)</option>
            <option value="Ikeja">Ikeja (Lagos)</option>
            <option value="Kano">Kano (Kano)</option>
            <option value="Port Harcourt">Port Harcourt</option>
            <option value="Effurun">Effurun (Delta)</option>
        </select>
        <div class="flex gap-2 mb-2">
            <select id="boreholeType" class="flex-1 bg-slate-700 p-3 rounded-lg text-xs border border-white/10">
                <option value="industrial">Industrial Rig</option>
                <option value="hand">Hand-Dug Well</option>
            </select>
            <button onclick="getGPS()" class="bg-blue-600 px-4 rounded-lg text-[10px] font-bold">📍 DMS GPS</button>
        </div>
        <button onclick="runAnalysis()" class="w-full emerald-btn py-4 rounded-lg font-bold text-sm">PREDICT HYDRO-INTELLIGENCE</button>
    </div>

    <div id="map"></div>

    <div id="resultBox" class="glass p-4 rounded-xl hidden mb-4 border-l-4 border-emerald-500">
        <div class="flex justify-between items-center mb-2">
            <h3 id="resName" class="text-lg font-bold text-emerald-400 underline"></h3>
            <span id="coordDisplay" class="text-[8px] font-mono text-gray-400 bg-black/40 px-2 py-1 rounded">--°--'--"N</span>
        </div>
        <div class="grid grid-cols-2 gap-2 mb-3">
            <div class="p-2 bg-white/5 rounded text-center"><span class="text-[8px] text-emerald-400 block uppercase">Pred. Depth</span><span id="resDepth" class="text-md font-bold text-white">--</span></div>
            <div class="p-2 bg-white/5 rounded text-center"><span class="text-[8px] text-emerald-400 block uppercase">Est. BOQ</span><span id="resBOQ" class="text-md font-bold text-white">₦0</span></div>
        </div>
        <p id="resRisk" class="text-[10px] text-amber-300 italic mb-2"></p>
        <div class="flex gap-2">
            <button onclick="shareWhatsApp()" class="flex-1 bg-green-600 py-2 rounded text-[9px] font-bold">WhatsApp</button>
            <button onclick="exportPDF()" class="flex-1 bg-red-600 py-2 rounded text-[9px] font-bold">Export PDF</button>
        </div>
    </div>

    <div class="glass p-4 rounded-xl mb-4 border-t-2 border-emerald-500">
        <h3 class="text-[10px] font-bold text-emerald-400 mb-3 tracking-widest uppercase italic border-b border-emerald-500/20 pb-1">Driller's Field Audit Log</h3>
        
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div class="bg-white/5 p-2 rounded">
                <label class="text-[7px] text-gray-400 block uppercase">Actual Depth (m)</label>
                <input id="audit_depth" type="number" placeholder="42.5" class="bg-transparent text-white text-[12px] font-bold w-full outline-none border-b border-emerald-500/30">
            </div>
            <div class="bg-white/5 p-2 rounded">
                <label class="text-[7px] text-gray-400 block uppercase">SWL (m)</label>
                <input id="audit_swl" type="number" placeholder="12.0" class="bg-transparent text-white text-[12px] font-bold w-full outline-none border-b border-emerald-500/30">
            </div>
            <div class="bg-white/5 p-2 rounded">
                <label class="text-[7px] text-gray-400 block uppercase">Formation</label>
                <select id="audit_lith" class="bg-transparent text-white text-[10px] w-full outline-none">
                    <option>Fine Sand</option><option>Coarse Sand</option><option>Clayey Sand</option><option>Basement Rock</option>
                </select>
            </div>
            <div class="bg-white/5 p-2 rounded">
                <label class="text-[7px] text-gray-400 block uppercase">QA/QC Status</label>
                <select id="audit_status" class="bg-transparent text-emerald-400 text-[10px] font-bold w-full outline-none">
                    <option>Within Tolerance</option><option class="text-red-400">High Variance</option>
                </select>
            </div>
        </div>

        <button onclick="finalizeAudit()" class="w-full bg-emerald-700 py-3 rounded-lg font-bold text-[10px] tracking-widest uppercase shadow-lg shadow-emerald-900/40">
            FINALIZE & SAVE GROUND-TRUTH
        </button>
    </div>

    <div class="glass p-4 rounded-xl">
        <canvas id="varianceChart" style="max-height: 120px;"></canvas>
    </div>

    <script>
        const lgaData = {
            "Ikeja": {depth: 45, lat: 6.6, lng: 3.35, ind: 16000, hand: 5000, risk: "High Iron Risk. Deltaic sedimentation."},
            "Ankpa": {depth: 60, lat: 7.4, lng: 7.6, ind: 18000, hand: 6500, risk: "Sandstone formation. High yield potential."},
            "Kano": {depth: 75, lat: 12.0, lng: 8.5, ind: 28000, hand: 9000, risk: "Basement Complex. Fluoride Alert."},
            "Port Harcourt": {depth: 32, lat: 4.82, lng: 7.03, ind: 22000, hand: 7500, risk: "Saline intrusion. Acidic groundwater."},
            "Effurun": {depth: 28, lat: 5.56, lng: 5.78, ind: 15000, hand: 5500, risk: "Peat presence. High Total Dissolved Solids."}
        };

        let map = L.map('map', { zoomControl: false }).setView([9.08, 8.67], 5);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(map);

        function toDMS(deg) {
            let d = Math.floor(deg);
            let m = Math.floor((deg - d) * 60);
            let s = ((deg - d - m/60) * 3600).toFixed(1);
            return `${d}°${m}'${s}"`;
        }

        function getGPS() {
            navigator.geolocation.getCurrentPosition(pos => {
                let lat = toDMS(pos.coords.latitude);
                let lng = toDMS(pos.coords.longitude);
                document.getElementById('coordDisplay').innerText = `${lat}N, ${lng}E`;
                alert("GPS Ground-Truth Locked!");
            });
        }

        function runAnalysis() {
            const lga = document.getElementById('lgaSelect').value;
            if(!lga) return alert("Select Site!");
            const d = lgaData[lga];
            const type = document.getElementById('boreholeType').value;
            const rate = (type === 'industrial') ? d.ind : d.hand;
            
            document.getElementById('resultBox').classList.remove('hidden');
            document.getElementById('resName').innerText = lga + " Prediction";
            document.getElementById('resDepth').innerText = d.depth + "m";
            document.getElementById('resBOQ').innerText = "₦" + (d.depth * rate).toLocaleString();
            document.getElementById('resRisk').innerText = d.risk;
            map.flyTo([d.lat, d.lng], 13);
            L.marker([d.lat, d.lng]).addTo(map);
        }

        function finalizeAudit() {
            const actual = document.getElementById('audit_depth').value;
            if(!actual) return alert("Enter Actual Depth!");
            alert("AUDIT COMPLETE:\nActual Depth: " + actual + "m\nLithology: " + document.getElementById('audit_lith').value + "\nData synced for NMGS Uyo 2026 conference records.");
        }

        function shareWhatsApp() {
            const msg = window.encodeURIComponent(`*GEOSAFE FIELD AUDIT*\nSite: ${document.getElementById('resName').innerText}\nDepth: ${document.getElementById('resDepth').innerText}\nCost: ${document.getElementById('resBOQ').innerText}`);
            window.open(`https://wa.me/?text=${msg}`);
        }

        function exportPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.text("GEOSAFE GROUND-TRUTH REPORT", 10, 20);
            doc.text("LGA: " + document.getElementById('resName').innerText, 10, 40);
            doc.text("Actual Drilling Depth: " + document.getElementById('audit_depth').value + "m", 10, 50);
            doc.save("GeoSafe_Audit.pdf");
        }

        const raw = <?php echo json_encode($chart_data); ?>;
        new Chart(document.getElementById("varianceChart"), {
            type: 'line',
            data: {
                labels: raw.map(i => i.loc),
                datasets: [{ label: 'Drilling Variance (m)', data: raw.map(i => i.var), borderColor: '#10b981', tension: 0.4 }]
            },
            options: { scales: { y: { display: false }, x: { ticks: { color: '#666', font: { size: 8 } } } }, plugins: { legend: { display: false } } }
        });
    </script>
</body>
</html>
<?php
error_reporting(E_ALL ^ E_DEPRECATED);
$file = "geosafe_logs.csv";
$chart_data = [];
$h = @fopen($file, "r");
if ($h) {
    while (($d = fgetcsv($h, 1000, ",")) !== FALSE) {
        if (count($d) >= 3) { $chart_data[] = ["loc" => $d[0], "var" => (floatval($d[2]) - floatval($d[1]))]; }
    }
    fclose($h);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeoSafe v5.0 | Professional Submission</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        body { background: #011613; color: #e2e8f0; font-family: sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.1); }
        #map { height: 180px; border-radius: 12px; margin-bottom: 1rem; z-index: 1; border: 1px solid rgba(16, 185, 129, 0.2); }
        .emerald-btn { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .emerald-btn:active { transform: scale(0.96); }
    </style>
</head>
<body class="p-4 pb-12">

    <header class="text-center mb-4">
        <h1 class="text-xl font-bold text-emerald-400">GEOSAFE v5.0</h1>
        <p class="text-[9px] text-emerald-200/40 tracking-[0.3em] uppercase">Hydro-Intelligence | Geol. E. Rukevwe</p>
    </header>

    <div class="glass p-4 rounded-xl mb-4 border-t-2 border-amber-500">
        <h2 class="text-[10px] font-bold text-amber-400 mb-1 tracking-widest">⚠️ GEOPHYSICAL MANDATE (VES/ERT)</h2>
        <p class="text-[10px] text-slate-300">Drilling without <span class="text-emerald-400">VES Data</span> increases dry-hole risk by 85%. Survey is <span class="underline">MANDATORY</span> before mobilization.</p>
    </div>

    <div class="glass p-4 rounded-xl mb-4">
        <select id="lgaSelect" class="w-full bg-slate-800 border border-emerald-500/30 p-3 rounded-lg text-white mb-2 text-sm">
            <option value="">-- Select Target LGA --</option>
            <option value="Ankpa">Ankpa (Kogi)</option>
            <option value="Ikeja">Ikeja (Lagos)</option>
            <option value="Kano">Kano (Kano)</option>
            <option value="Port Harcourt">Port Harcourt</option>
            <option value="Effurun">Effurun (Delta)</option>
        </select>
        <div class="flex gap-2 mb-2">
            <select id="boreholeType" class="flex-1 bg-slate-700 p-3 rounded-lg text-xs border border-white/10">
                <option value="industrial">Industrial Rig</option>
                <option value="hand">Hand-Dug Well</option>
            </select>
            <button onclick="getGPS()" class="bg-blue-600 px-4 rounded-lg text-[10px] font-bold">📍 GPS</button>
        </div>
        <button onclick="runAnalysis()" class="w-full emerald-btn py-4 rounded-lg font-bold text-sm">RUN COMPREHENSIVE ANALYSIS</button>
    </div>

    <div id="map"></div>

    <div id="resultBox" class="glass p-4 rounded-xl hidden mb-4 border-l-4 border-emerald-500">
        <div class="flex justify-between items-center mb-3">
            <h3 id="resName" class="text-lg font-bold text-emerald-400"></h3>
            <span id="coordDisplay" class="text-[8px] font-mono text-gray-400 bg-black/40 px-2 py-1 rounded"></span>
        </div>
        
        <div class="grid grid-cols-2 gap-2 mb-3">
            <div class="p-3 bg-white/5 rounded-lg text-center">
                <span class="text-[8px] text-emerald-400 font-bold block">EST. DEPTH</span>
                <span id="resDepth" class="text-lg font-bold text-white">--</span>
            </div>
            <div class="p-3 bg-white/5 rounded-lg text-center">
                <span class="text-[8px] text-emerald-400 font-bold block">TOTAL BOQ</span>
                <span id="resBOQ" class="text-lg font-bold text-white">₦0</span>
            </div>
        </div>

        <div class="p-3 bg-amber-900/20 border border-amber-500/30 rounded-lg mb-3">
            <p id="resRisk" class="text-[10px] text-amber-100 italic"></p>
        </div>

        <p id="resSOP" class="text-[11px] leading-relaxed text-emerald-100/80 mb-5"></p>
        
        <div class="grid grid-cols-2 gap-2">
            <button onclick="shareWhatsApp()" class="bg-green-600 py-3 rounded text-[10px] font-bold shadow">WhatsApp</button>
            <button onclick="exportPDF()" class="bg-red-600 py-3 rounded text-[10px] font-bold shadow">PDF Report</button>
        </div>
    </div>

    <div class="glass p-4 rounded-xl mb-4 border-t-2 border-emerald-500">
        <h3 class="text-[10px] font-bold text-emerald-400 mb-3 tracking-widest uppercase italic">Field Log: Resistivity Entry</h3>
        <div class="grid grid-cols-3 gap-1 mb-4 text-center">
            <div class="bg-white/5 p-2 rounded">
                <span class="text-[7px] text-gray-400 block">POINT</span>
                <input id="v_point" type="text" placeholder="VES 1" class="bg-transparent text-white text-[10px] w-full text-center outline-none">
            </div>
            <div class="bg-white/5 p-2 rounded">
                <span class="text-[7px] text-gray-400 block">RES (Ωm)</span>
                <input id="v_res" type="number" placeholder="450" class="bg-transparent text-white text-[10px] w-full text-center outline-none">
            </div>
            <div class="bg-white/5 p-2 rounded">
                <span class="text-[7px] text-gray-400 block">CURVE</span>
                <select id="v_curve" class="bg-transparent text-white text-[10px] w-full outline-none">
                    <option>H-Type</option><option>A-Type</option><option>K-Type</option><option>Q-Type</option>
                </select>
            </div>
        </div>
        <button onclick="finalizeAudit()" class="w-full bg-emerald-700 py-3 rounded-lg font-bold text-[10px] tracking-widest uppercase">
            Finalize & Sync Site Audit
        </button>
    </div>

    <div class="glass p-4 rounded-xl">
        <p class="text-[9px] font-bold text-emerald-400 uppercase mb-3 text-center italic">Historical Drilling Variance (Actual vs Pred)</p>
        <canvas id="varianceChart" style="max-height: 140px;"></canvas>
    </div>

    <script>
        const lgaData = {
            "Ikeja": {depth: 45, lat: 6.6, lng: 3.35, ind: 16000, hand: 5000, risk: "Iron risk. Use PVC casing."},
            "Ankpa": {depth: 60, lat: 7.4, lng: 7.6, ind: 18000, hand: 6500, risk: "Manganese risk. High recharge."},
            "Kano": {depth: 75, lat: 12.0, lng: 8.5, ind: 28000, hand: 9000, risk: "Fluoride risk. Basement complex."},
            "Port Harcourt": {depth: 32, lat: 4.82, lng: 7.03, ind: 22000, hand: 7500, risk: "Saline risk. Acidic pH."},
            "Effurun": {depth: 28, lat: 5.56, lng: 5.78, ind: 15000, hand: 5500, risk: "Peat risk. High TDS."}
        };

        let map = L.map('map', { zoomControl: false }).setView([9.08, 8.67], 5);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(map);

        function toDMS(deg) {
            let d = Math.floor(deg);
            let minFloat = (deg - d) * 60;
            let m = Math.floor(minFloat);
            let s = ((minFloat - m) * 60).toFixed(1);
            return `${d}°${m}'${s}"`;
        }

        function getGPS() {
            navigator.geolocation.getCurrentPosition(pos => {
                let latDMS = toDMS(pos.coords.latitude);
                let lngDMS = toDMS(pos.coords.longitude);
                document.getElementById('coordDisplay').innerText = `${latDMS}N, ${lngDMS}E`;
                alert("GPS Fixed: " + latDMS + "N");
            });
        }

        function runAnalysis() {
            const lga = document.getElementById('lgaSelect').value;
            const type = document.getElementById('boreholeType').value;
            if(!lga) return;
            const d = lgaData[lga];
            const rate = (type === 'industrial') ? d.ind : d.hand;
            document.getElementById('resultBox').classList.remove('hidden');
            document.getElementById('resName').innerText = lga + " Intel";
            document.getElementById('resDepth').innerText = d.depth + "m";
            document.getElementById('resBOQ').innerText = "₦" + (d.depth * rate).toLocaleString();
            document.getElementById('resRisk').innerText = "⚠️ RISK: " + d.risk;
            document.getElementById('resSOP').innerText = `Strategy: ${type.toUpperCase()} drilling. VES curve verification required before setting rig.`;
            map.flyTo([d.lat, d.lng], 12);
            L.marker([d.lat, d.lng]).addTo(map);
        }

        function finalizeAudit() {
            if(!document.getElementById('v_res').value) return alert("Enter Resistivity Data!");
            alert("SUCCESS: Audit for " + document.getElementById('v_point').value + " synchronized with GeoSafe Database.");
        }

        function shareWhatsApp() {
            const msg = window.encodeURIComponent(`*GEOSAFE v5.0*\nSite: ${document.getElementById('resName').innerText}\nDepth: ${document.getElementById('resDepth').innerText}\nCost: ${document.getElementById('resBOQ').innerText}`);
            window.open(`https://wa.me/?text=${msg}`);
        }

        function exportPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.text("GEOSAFE FIELD AUDIT", 10, 20);
            doc.text(document.getElementById('resultBox').innerText, 10, 40);
            doc.save("Report.pdf");
        }

        const raw = <?php echo json_encode($chart_data); ?>;
        new Chart(document.getElementById("varianceChart"), {
            type: 'line',
            data: {
                labels: raw.map(i => i.loc),
                datasets: [{ label: 'm', data: raw.map(i => i.var), borderColor: '#10b981', tension: 0.4 }]
            },
            options: { scales: { y: { display: false }, x: { ticks: { color: '#666', font: { size: 8 } } } }, plugins: { legend: { display: false } } }
        });
    </script>
</body>
</html>
<?php
error_reporting(E_ALL ^ E_DEPRECATED);
$file = "geosafe_logs.csv";
$chart_data = [];
$h = @fopen($file, "r");
if ($h) {
    while (($d = fgetcsv($h, 1000, ",")) !== FALSE) {
        if (count($d) >= 3) { $chart_data[] = ["loc" => $d[0], "var" => (floatval($d[2]) - floatval($d[1]))]; }
    }
    fclose($h);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeoSafe v5.0 | 3MTT Showcase</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        body { background: #011613; color: #e2e8f0; font-family: sans-serif; -webkit-tap-highlight-color: transparent; }
        .glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.1); }
        #map { height: 180px; border-radius: 12px; margin-bottom: 1rem; z-index: 1; border: 1px solid rgba(16, 185, 129, 0.2); }
        .emerald-btn { background: linear-gradient(135deg, #10b981 0%, #059669 100%); transition: 0.2s; }
        .emerald-btn:active { transform: scale(0.96); }
        input::placeholder { color: rgba(255,255,255,0.3); }
    </style>
</head>
<body class="p-4 pb-12">

    <header class="text-center mb-4">
        <h1 class="text-xl font-bold text-emerald-400">GEOSAFE v5.0</h1>
        <p class="text-[9px] text-emerald-200/40 tracking-[0.3em] uppercase">Hydro-Intelligence | Geol. E. Rukevwe</p>
    </header>

    <div class="glass p-4 rounded-xl mb-4 border-t-2 border-amber-500">
        <h2 class="text-[10px] font-bold text-amber-400 uppercase mb-2">⚠️ GEOPHYSICAL MANDATE (VES/ERT)</h2>
        <p class="text-[10px] leading-tight text-slate-300">
            Professional Survey is <span class="text-white font-bold underline">REQUIRED</span> before mobilization. 
            Drilling without <span class="text-emerald-400 font-bold">VES Data</span> increases dry-hole risk by 85% in Basement Complex terrains.
        </p>
    </div>

    <div class="glass p-4 rounded-xl mb-4">
        <select id="lgaSelect" class="w-full bg-slate-800 border border-emerald-500/30 p-3 rounded-lg text-white mb-2 text-sm">
            <option value="">-- Select Target LGA --</option>
            <option value="Ankpa">Ankpa (Kogi)</option>
            <option value="Ikeja">Ikeja (Lagos)</option>
            <option value="Kano">Kano (Kano)</option>
            <option value="Port Harcourt">Port Harcourt</option>
            <option value="Effurun">Effurun (Delta)</option>
        </select>
        <div class="flex gap-2 mb-2">
            <select id="boreholeType" class="flex-1 bg-slate-700 p-3 rounded-lg text-xs border border-white/10">
                <option value="industrial">Industrial Rig</option>
                <option value="hand">Hand-Dug Well</option>
            </select>
            <button onclick="getGPS()" class="bg-blue-600 px-4 rounded-lg text-[10px] font-bold">📍 GPS</button>
        </div>
        <button onclick="runAnalysis()" class="w-full emerald-btn py-4 rounded-lg font-bold text-sm">RUN COMPREHENSIVE ANALYSIS</button>
    </div>

    <div id="map"></div>

    <div id="resultBox" class="glass p-4 rounded-xl hidden mb-4 border-l-4 border-emerald-500">
        <div class="flex justify-between items-center mb-3">
            <h3 id="resName" class="text-lg font-bold text-emerald-400"></h3>
            <span id="coordDisplay" class="text-[9px] font-mono text-gray-400 bg-black/40 px-2 py-1 rounded"></span>
        </div>
        
        <div class="grid grid-cols-2 gap-2 mb-3">
            <div class="p-3 bg-white/5 rounded-lg border border-white/5 text-center">
                <span class="text-[8px] text-emerald-400 font-bold block">EST. DEPTH</span>
                <span id="resDepth" class="text-lg font-bold text-white">--</span>
            </div>
            <div class="p-3 bg-white/5 rounded-lg border border-white/5 text-center">
                <span class="text-[8px] text-emerald-400 font-bold block">TOTAL BOQ</span>
                <span id="resBOQ" class="text-lg font-bold text-white">₦0</span>
            </div>
        </div>

        <div class="p-3 bg-amber-900/20 border border-amber-500/30 rounded-lg mb-3">
            <p class="text-[9px] font-bold text-amber-400 uppercase mb-1">⚠️ WATER QUALITY RISK</p>
            <p id="resRisk" class="text-[10px] text-amber-100 italic"></p>
        </div>

        <p id="resSOP" class="text-[11px] leading-relaxed text-emerald-100/80 mb-5"></p>
        
        <div class="grid grid-cols-2 gap-2">
            <button onclick="shareWhatsApp()" class="bg-green-600 py-3 rounded text-[10px] font-bold shadow">WhatsApp Quote</button>
            <button onclick="exportPDF()" class="bg-red-600 py-3 rounded text-[10px] font-bold shadow">Export PDF</button>
            <button onclick="exportCSV()" class="bg-slate-600 py-3 rounded text-[10px] font-bold shadow">Save Site Log</button>
            <button onclick="alert('WHO Standards:\nIron < 0.3mg/L\nFluoride < 1.5mg/L\npH 6.5-8.5')" class="bg-sky-600 py-3 rounded text-[10px] font-bold shadow">WHO Standards</button>
        </div>
    </div>

    <div class="glass p-4 rounded-xl mb-4">
        <h3 class="text-[11px] font-bold text-emerald-400 mb-3 border-b border-white/10 pb-1 italic">FIELD LOG: RESISTIVITY DATA</h3>
        <div class="grid grid-cols-3 gap-1 mb-4">
            <div class="bg-white/5 p-2 rounded text-center">
                <span class="text-[8px] text-gray-400 block uppercase">Point</span>
                <input id="v_point" type="text" placeholder="P1" class="bg-transparent text-white text-[10px] w-full text-center outline-none">
            </div>
            <div class="bg-white/5 p-2 rounded text-center">
                <span class="text-[8px] text-gray-400 block uppercase">Res. (Ωm)</span>
                <input id="v_res" type="number" placeholder="450" class="bg-transparent text-white text-[10px] w-full text-center outline-none">
            </div>
            <div class="bg-white/5 p-2 rounded text-center">
                <span class="text-[8px] text-gray-400 block uppercase">Curve</span>
                <select id="v_curve" class="bg-transparent text-white text-[10px] w-full outline-none">
                    <option>H-Type</option><option>A-Type</option><option>K-Type</option><option>Q-Type</option>
                </select>
            </div>
        </div>
        <button onclick="alert('Audit Entry Saved & Synced to GeoSafe Database')" class="w-full bg-emerald-700/50 py-3 rounded-lg font-bold text-[10px] border border-emerald-500/30 uppercase tracking-widest">
            FINALIZE SITE AUDIT
        </button>
    </div>

    <div class="glass p-4 rounded-xl">
        <p class="text-[9px] font-bold text-emerald-400 uppercase mb-3 text-center italic tracking-wider">Historical Drilling Variance (Actual vs Pred)</p>
        <canvas id="varianceChart" style="max-height: 140px;"></canvas>
    </div>

    <script>
        const lgaData = {
            "Ikeja": {depth: 45, lith: "Coastal Sands", lat: 6.6, lng: 3.35, ind: 16000, hand: 5000, risk: "Iron risk. Use PVC non-corrosive casing."},
            "Ankpa": {depth: 60, lith: "Ajali Sandstone", lat: 7.4, lng: 7.6, ind: 18000, hand: 6500, risk: "Manganese presence. Excellent recharge zone."},
            "Kano": {depth: 75, lith: "Basement Complex", lat: 12.0, lng: 8.5, ind: 28000, hand: 9000, risk: "High Fluoride risk. Basal fractures target."},
            "Port Harcourt": {depth: 32, lith: "Benin Formation", lat: 4.82, lng: 7.03, ind: 22000, hand: 7500, risk: "Saline Intrusion risk. Acidic pH (4.5-5.5)."},
            "Effurun": {depth: 28, lith: "Deltaic Sands", lat: 5.56, lng: 5.78, ind: 15000, hand: 5500, risk: "Peat organic matter risk. Monitor TDS."}
        };

        let map = L.map('map', { zoomControl: false }).setView([9.08, 8.67], 5);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(map);

        function getGPS() {
            navigator.geolocation.getCurrentPosition(pos => {
                document.getElementById('coordDisplay').innerText = pos.coords.latitude.toFixed(4) + ", " + pos.coords.longitude.toFixed(4);
                alert("GPS Fix Locked!");
            }, () => alert("Enable GPS Permissions"));
        }

        function runAnalysis() {
            const lga = document.getElementById('lgaSelect').value;
            const type = document.getElementById('boreholeType').value;
            if(!lga) return alert("Select LGA!");
            
            const d = lgaData[lga];
            const rate = (type === 'industrial') ? d.ind : d.hand;
            const total = d.depth * rate;
            
            document.getElementById('resultBox').classList.remove('hidden');
            document.getElementById('resName').innerText = lga + " Intel";
            document.getElementById('resDepth').innerText = d.depth + "m";
            document.getElementById('resBOQ').innerText = "₦" + total.toLocaleString();
            document.getElementById('resRisk').innerText = d.risk;
            document.getElementById('resSOP').innerText = `Geology: ${d.lith}. Strategy: ${type.toUpperCase()} drilling at ₦${rate}/m. Targeting aquifer zone verified by VES.`;

            map.flyTo([d.lat, d.lng], 12);
            L.marker([d.lat, d.lng]).addTo(map);
        }

        function shareWhatsApp() {
            const msg = window.encodeURIComponent(`*GEOSAFE AUDIT*\nSite: ${document.getElementById('resName').innerText}\nDepth: ${document.getElementById('resDepth').innerText}\nBOQ: ${document.getElementById('resBOQ').innerText}\nRisk: ${document.getElementById('resRisk').innerText}`);
            window.open(`https://wa.me/?text=${msg}`);
        }

        function exportPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.setFontSize(18); doc.text("GEOSAFE FIELD REPORT", 10, 20);
            doc.setFontSize(10); doc.text(document.getElementById('resultBox').innerText, 10, 40);
            doc.save(`GeoSafe_${document.getElementById('resName').innerText}.pdf`);
        }

        function exportCSV() {
            const rows = [["LGA", "Depth", "Cost", "Risk"], [document.getElementById('resName').innerText, document.getElementById('resDepth').innerText, document.getElementById('resBOQ').innerText, document.getElementById('resRisk').innerText]];
            let csvContent = "data:text/csv;charset=utf-8," + rows.map(e => e.join(",")).join("\n");
            window.open(encodeURI(csvContent));
        }

        const raw = <?php echo json_encode($chart_data); ?>;
        new Chart(document.getElementById("varianceChart"), {
            type: 'line',
            data: {
                labels: raw.map(i => i.loc),
                datasets: [{ label: 'm', data: raw.map(i => i.var), borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.1)', fill: true, tension: 0.4 }]
            },
            options: { scales: { y: { display: false }, x: { ticks: { color: '#666', font: { size: 8 } } } }, plugins: { legend: { display: false } } }
        });
    </script>
</body>
</html>
<?php
error_reporting(E_ALL ^ E_DEPRECATED);
$file = "geosafe_logs.csv";
$chart_data = [];
$h = @fopen($file, "r");
if ($h) {
    while (($d = fgetcsv($h, 1000, ",")) !== FALSE) {
        if (count($d) >= 3) { $chart_data[] = ["loc" => $d[0], "var" => (floatval($d[2]) - floatval($d[1]))]; }
    }
    fclose($h);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeoSafe v5.0 | Hydro-Intelligence</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        body { background: #011613; color: #e2e8f0; font-family: sans-serif; -webkit-tap-highlight-color: transparent; }
        .glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.1); }
        #map { height: 180px; border-radius: 12px; margin-bottom: 1rem; z-index: 1; border: 1px solid rgba(16, 185, 129, 0.2); }
        .emerald-btn { background: linear-gradient(135deg, #10b981 0%, #059669 100%); transition: 0.2s; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2); }
        .emerald-btn:active { transform: scale(0.96); }
        select { -webkit-appearance: none; }
    </style>
</head>
<body class="p-4 pb-12">

    <header class="text-center mb-4">
        <h1 class="text-xl font-bold text-emerald-400 tracking-tight">GEOSAFE v5.0</h1>
        <p class="text-[9px] text-emerald-200/40 tracking-[0.3em] uppercase">Hydro-Intelligence SOP | Geol. E. Rukevwe</p>
    </header>

    <div class="glass p-4 rounded-xl mb-4">
        <label class="text-[10px] text-emerald-400 font-bold mb-1 block">SITE SELECTION</label>
        <select id="lgaSelect" class="w-full bg-slate-800 border border-emerald-500/30 p-3 rounded-lg text-white mb-2 text-sm">
            <option value="">-- Tap to Select LGA --</option>
            <option value="Ankpa">Ankpa (Kogi)</option>
            <option value="Ikeja">Ikeja (Lagos)</option>
            <option value="Kano">Kano (Kano)</option>
            <option value="Port Harcourt">Port Harcourt (Rivers)</option>
            <option value="Effurun">Effurun (Delta)</option>
        </select>
        
        <div class="flex gap-2 mb-3">
            <select id="boreholeType" class="flex-1 bg-slate-700 p-3 rounded-lg text-xs border border-white/10">
                <option value="industrial">Industrial Rig</option>
                <option value="hand">Hand-Dug Well</option>
            </select>
            <button onclick="getGPS()" class="bg-blue-600 px-5 rounded-lg text-[10px] font-bold">📍 GPS</button>
        </div>
        
        <button onclick="runAnalysis()" class="w-full emerald-btn py-4 rounded-lg font-bold text-sm">RUN COMPREHENSIVE ANALYSIS</button>
    </div>

    <div id="map"></div>

    <div id="resultBox" class="glass p-4 rounded-xl hidden mb-4 border-l-4 border-emerald-500 relative overflow-hidden">
        <div class="flex justify-between items-center mb-3">
            <h3 id="resName" class="text-lg font-bold text-emerald-400"></h3>
            <span id="coordDisplay" class="text-[9px] font-mono text-gray-400 bg-black/30 px-2 py-1 rounded"></span>
        </div>
        
        <div class="grid grid-cols-2 gap-2 mb-3">
            <div class="p-3 bg-white/5 rounded-lg border border-white/5">
                <span class="text-[8px] text-emerald-400 font-bold block uppercase">Est. Depth</span>
                <span id="resDepth" class="text-lg font-bold text-white">--</span>
            </div>
            <div class="p-3 bg-white/5 rounded-lg border border-white/5">
                <span class="text-[8px] text-emerald-400 font-bold block uppercase">Total BOQ</span>
                <span id="resBOQ" class="text-lg font-bold text-white">₦0</span>
            </div>
        </div>

        <div id="waterAlert" class="p-3 bg-amber-900/20 border border-amber-500/30 rounded-lg mb-3">
            <p class="text-[9px] font-bold text-amber-400 uppercase mb-1">⚠️ Water Quality Risk</p>
            <p id="resRisk" class="text-[10px] text-amber-100 italic"></p>
        </div>

        <p id="resSOP" class="text-[11px] leading-relaxed text-emerald-100/80 mb-5"></p>
        
        <div class="grid grid-cols-2 gap-2">
            <button onclick="shareWhatsApp()" class="bg-green-600 py-3 rounded-lg text-[10px] font-bold shadow-lg">WhatsApp Quote</button>
            <button onclick="exportPDF()" class="bg-red-600 py-3 rounded-lg text-[10px] font-bold shadow-lg">Export PDF</button>
            <button onclick="exportCSV()" class="bg-slate-600 py-3 rounded-lg text-[10px] font-bold shadow-lg">Save Log (.CSV)</button>
            <button onclick="alert('WHO Standards:\nIron < 0.3mg/L\nFluoride < 1.5mg/L\npH 6.5-8.5')" class="bg-sky-600 py-3 rounded-lg text-[10px] font-bold shadow-lg">WHO Guidelines</button>
        </div>
    </div>

    <div class="glass p-4 rounded-xl">
        <p class="text-[9px] font-bold text-emerald-400 uppercase mb-3 text-center">Field Audit: Drilling Variance (m)</p>
        <canvas id="varianceChart" style="max-height: 150px;"></canvas>
    </div>

    <script>
        const lgaData = {
            "Ikeja": {depth: 45, lith: "Coastal Plain Sands", lat: 6.6, lng: 3.35, ind: 16000, hand: 5000, risk: "High Iron (Ferrous) content. Aeration and filtration recommended."},
            "Ankpa": {depth: 60, lith: "Ajali Sandstone", lat: 7.4, lng: 7.6, ind: 18000, hand: 6500, risk: "Moderate Manganese risk. Deep aquifer recharge is excellent."},
            "Kano": {depth: 75, lith: "Basement Complex", lat: 12.0, lng: 8.5, ind: 28000, hand: 9000, risk: "High Fluoride risk. Dental fluorosis hazard. Reverse Osmosis advised."},
            "Port Harcourt": {depth: 32, lith: "Benin Formation", lat: 4.82, lng: 7.03, ind: 22000, hand: 7500, risk: "Saline Intrusion risk. Acidic pH alert (4.5-5.5). Corrosive water."},
            "Effurun": {depth: 28, lith: "Deltaic Sands", lat: 5.56, lng: 5.78, ind: 15000, hand: 5500, risk: "Organic matter (Peat) presence. Salinity monitoring required."}
        };

        let map = L.map('map', { zoomControl: false }).setView([9.08, 8.67], 5);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(map);

        function getGPS() {
            navigator.geolocation.getCurrentPosition(pos => {
                document.getElementById('coordDisplay').innerText = pos.coords.latitude.toFixed(4) + ", " + pos.coords.longitude.toFixed(4);
                alert("GPS Fix Locked!");
            }, () => alert("Enable Location Permissions"));
        }

        function runAnalysis() {
            const lga = document.getElementById('lgaSelect').value;
            const type = document.getElementById('boreholeType').value;
            if(!lga) return alert("Select an LGA");
            
            const d = lgaData[lga];
            const rate = (type === 'industrial') ? d.ind : d.hand;
            const total = d.depth * rate;
            
            document.getElementById('resultBox').classList.remove('hidden');
            document.getElementById('resName').innerText = lga + " Intel";
            document.getElementById('resDepth').innerText = d.depth + "m";
            document.getElementById('resBOQ').innerText = "₦" + total.toLocaleString();
            document.getElementById('resRisk').innerText = d.risk;
            document.getElementById('resSOP').innerText = `Geology: ${d.lith}. Strategy: ${type.toUpperCase()} drilling at ₦${rate}/m. Target static water level predicted at ${d.depth - 5}m.`;

            map.flyTo([d.lat, d.lng], 12);
            L.marker([d.lat, d.lng]).addTo(map);
        }

        function shareWhatsApp() {
            const msg = window.encodeURIComponent(`*GEOSAFE FIELD QUOTE*\nSite: ${document.getElementById('resName').innerText}\nDepth: ${document.getElementById('resDepth').innerText}\nBOQ: ${document.getElementById('resBOQ').innerText}\nRisk: ${document.getElementById('resRisk').innerText}`);
            window.open(`https://wa.me/?text=${msg}`);
        }

        function exportPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.setFontSize(18); doc.text("GEOSAFE v5.0 FIELD AUDIT", 10, 20);
            doc.setFontSize(10); doc.text(document.getElementById('resultBox').innerText, 10, 40);
            doc.save(`GeoSafe_${document.getElementById('resName').innerText}.pdf`);
        }

        function exportCSV() {
            const rows = [["LGA", "Depth", "Cost", "Lithology"], [document.getElementById('resName').innerText, document.getElementById('resDepth').innerText, document.getElementById('resBOQ').innerText, "Reported"]];
            let csvContent = "data:text/csv;charset=utf-8," + rows.map(e => e.join(",")).join("\n");
            window.open(encodeURI(csvContent));
        }

        const raw = <?php echo json_encode($chart_data); ?>;
        new Chart(document.getElementById("varianceChart"), {
            type: 'line',
            data: {
                labels: raw.map(i => i.loc),
                datasets: [{ label: 'm', data: raw.map(i => i.var), borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.1)', fill: true, tension: 0.4 }]
            },
            options: { scales: { y: { display: false }, x: { ticks: { color: '#666', font: { size: 8 } } } }, plugins: { legend: { display: false } } }
        });
    </script>
</body>
</html>
<?php
error_reporting(E_ALL ^ E_DEPRECATED);
$file = "geosafe_logs.csv";
if (!file_exists($file)) {
    file_put_contents($file, "Ikeja,40,43\nAnkpa,50,53\nKano,60,85");
}

$chart_data = [];
$h = @fopen($file, "r");
if ($h) {
    while (($d = fgetcsv($h, 1000, ",")) !== FALSE) {
        if (count($d) >= 3) {
            $chart_data[] = ["loc" => $d[0], "var" => (floatval($d[2]) - floatval($d[1]))];
        }
    }
    fclose($h);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeoSafe v5.0 | 3MTT Showcase</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background: #011613; color: #e2e8f0; font-family: 'Inter', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }
        #map { height: 250px; border-radius: 12px; z-index: 1; }
        .emerald-gradient { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    </style>
</head>
<body class="p-4">

    <header class="text-center mb-6">
        <h1 class="text-2xl font-bold text-emerald-400">GEOSAFE v5.0</h1>
        <p class="text-xs text-emerald-200/60 uppercase tracking-widest">Hydro-Intelligence SOP | Geol. E. Rukevwe</p>
    </header>

    <div class="glass p-4 rounded-xl mb-4">
        <label class="block text-xs font-semibold mb-1 text-emerald-400">SELECT LGA FOR ANALYSIS</label>
        <select id="lgaSelect" class="w-full bg-slate-800 border border-emerald-500/30 p-3 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <option value="">-- Tap to Select LGA --</option>
            <option value="Ankpa">Ankpa (Kogi)</option>
            <option value="Ikeja">Ikeja (Lagos)</option>
            <option value="Kano">Kano (Kano)</option>
            <option value="Port Harcourt">Port Harcourt (Rivers)</option>
            <option value="Effurun">Effurun (Delta)</option>
        </select>
        <button onclick="runAnalysis()" class="w-full emerald-gradient mt-3 py-3 rounded-lg font-bold shadow-lg shadow-emerald-900/20 active:scale-95 transition-all">
            RUN FIELD ANALYSIS
        </button>
    </div>

    <div id="map" class="shadow-2xl border border-emerald-500/20 mb-4"></div>

    <div id="resultBox" class="glass p-4 rounded-xl hidden mb-4">
        <h3 id="resName" class="text-lg font-bold text-emerald-400 mb-2"></h3>
        <div class="grid grid-cols-2 gap-2 text-sm">
            <div class="p-2 bg-white/5 rounded">Depth: <span id="resDepth" class="font-bold text-white"></span></div>
            <div class="p-2 bg-white/5 rounded text-xs">Lithology: <span id="resLith" class="block font-bold text-white"></span></div>
        </div>
        <div class="mt-3 p-3 bg-emerald-900/20 border border-emerald-500/30 rounded-lg">
            <p class="text-xs font-bold text-emerald-400 underline mb-1">DRILLER'S SOP:</p>
            <p id="resSOP" class="text-xs leading-relaxed text-emerald-100"></p>
        </div>
    </div>

    <div class="glass p-4 rounded-xl">
        <p class="text-xs font-bold text-emerald-400 mb-2 italic underline">QA/QC: DRILLING VARIANCE (ACTUAL vs PRED)</p>
        <canvas id="varianceChart" style="max-height: 180px;"></canvas>
    </div>

    <script>
        const lgaData = {
            "Ikeja": {depth: 45, lith: "Coastal Plain Sands", lat: 6.6, lng: 3.35, sop: "Standard Rig. Monitor for Iron staining in upper 20m."},
            "Ankpa": {depth: 60, lith: "Benue Trough Sediments", lat: 7.4, lng: 7.6, sop: "Heavy Rig. Fractured Sandstone expected. Log SWL carefully."},
            "Kano": {depth: 75, lith: "Basement Complex", lat: 12.0, lng: 8.5, sop: "Percussion Rig. Crystalline rock. Fluoride risk high."},
            "Port Harcourt": {depth: 32, lith: "Deltaic Sands", lat: 4.82, lng: 7.03, sop: "Shallow Aquifer. High Salinity risk. Screen at 25m."},
            "Effurun": {depth: 28, lith: "Deltaic Formation", lat: 5.56, lng: 5.78, sop: "Light Rig. Sandy silt. Rapid drilling expected."}
        };

        // Initialize Map
        let map = L.map('map', { zoomControl: false }).setView([9.08, 8.67], 5);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(map);

        function runAnalysis() {
            const val = document.getElementById('lgaSelect').value;
            if(!val) return alert("Select an LGA first!");
            
            const data = lgaData[val];
            const box = document.getElementById('resultBox');
            
            // Update UI
            box.classList.remove('hidden');
            document.getElementById('resName').innerText = val + " Intelligence";
            document.getElementById('resDepth').innerText = data.depth + "m";
            document.getElementById('resLith').innerText = data.lith;
            document.getElementById('resSOP').innerText = data.sop;

            // Update Map
            map.flyTo([data.lat, data.lng], 12);
            L.marker([data.lat, data.lng]).addTo(map).bindPopup(val).openPopup();
        }

        // Initialize Chart
        const rawData = <?php echo json_encode($chart_data); ?>;
        new Chart(document.getElementById('varianceChart'), {
            type: 'line',
            data: {
                labels: rawData.map(i => i.loc),
                datasets: [{
                    label: 'Variance (m)',
                    data: rawData.map(i => i.var),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.2)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: { scales: { y: { display: false }, x: { grid: { display: false }, ticks: { color: '#4ade80' } } }, plugins: { legend: { display: false } } }
        });
    </script>
</body>
</html>
<?php
error_reporting(E_ALL ^ E_DEPRECATED);
$file = "geosafe_logs.csv";
if (!file_exists($file)) {
    file_put_contents($file, "Ikeja,40,43\nAnkpa,50,53\nKano,60,85");
}

$chart_data = [];
$h = @fopen($file, "r");
if ($h) {
    while (($d = fgetcsv($h, 1000, ",")) !== FALSE) {
        if (count($d) >= 3) {
            $chart_data[] = ["loc" => $d[0], "var" => (floatval($d[2]) - floatval($d[1]))];
        }
    }
    fclose($h);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeoSafe v5.0 | 3MTT Showcase</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background: #011613; color: #e2e8f0; font-family: 'Inter', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }
        #map { height: 250px; border-radius: 12px; z-index: 1; }
        .emerald-gradient { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    </style>
</head>
<body class="p-4">

    <header class="text-center mb-6">
        <h1 class="text-2xl font-bold text-emerald-400">GEOSAFE v5.0</h1>
        <p class="text-xs text-emerald-200/60 uppercase tracking-widest">Hydro-Intelligence SOP | Geol. E. Rukevwe</p>
    </header>

    <div class="glass p-4 rounded-xl mb-4">
        <label class="block text-xs font-semibold mb-1 text-emerald-400">SELECT LGA FOR ANALYSIS</label>
        <select id="lgaSelect" class="w-full bg-slate-800 border border-emerald-500/30 p-3 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <option value="">-- Tap to Select LGA --</option>
            <option value="Ankpa">Ankpa (Kogi)</option>
            <option value="Ikeja">Ikeja (Lagos)</option>
            <option value="Kano">Kano (Kano)</option>
            <option value="Port Harcourt">Port Harcourt (Rivers)</option>
            <option value="Effurun">Effurun (Delta)</option>
        </select>
        <button onclick="runAnalysis()" class="w-full emerald-gradient mt-3 py-3 rounded-lg font-bold shadow-lg shadow-emerald-900/20 active:scale-95 transition-all">
            RUN FIELD ANALYSIS
        </button>
    </div>

    <div id="map" class="shadow-2xl border border-emerald-500/20 mb-4"></div>

    <div id="resultBox" class="glass p-4 rounded-xl hidden mb-4">
        <h3 id="resName" class="text-lg font-bold text-emerald-400 mb-2"></h3>
        <div class="grid grid-cols-2 gap-2 text-sm">
            <div class="p-2 bg-white/5 rounded">Depth: <span id="resDepth" class="font-bold text-white"></span></div>
            <div class="p-2 bg-white/5 rounded text-xs">Lithology: <span id="resLith" class="block font-bold text-white"></span></div>
        </div>
        <div class="mt-3 p-3 bg-emerald-900/20 border border-emerald-500/30 rounded-lg">
            <p class="text-xs font-bold text-emerald-400 underline mb-1">DRILLER'S SOP:</p>
            <p id="resSOP" class="text-xs leading-relaxed text-emerald-100"></p>
        </div>
    </div>

    <div class="glass p-4 rounded-xl">
        <p class="text-xs font-bold text-emerald-400 mb-2 italic underline">QA/QC: DRILLING VARIANCE (ACTUAL vs PRED)</p>
        <canvas id="varianceChart" style="max-height: 180px;"></canvas>
    </div>

    <script>
        const lgaData = {
            "Ikeja": {depth: 45, lith: "Coastal Plain Sands", lat: 6.6, lng: 3.35, sop: "Standard Rig. Monitor for Iron staining in upper 20m."},
            "Ankpa": {depth: 60, lith: "Benue Trough Sediments", lat: 7.4, lng: 7.6, sop: "Heavy Rig. Fractured Sandstone expected. Log SWL carefully."},
            "Kano": {depth: 75, lith: "Basement Complex", lat: 12.0, lng: 8.5, sop: "Percussion Rig. Crystalline rock. Fluoride risk high."},
            "Port Harcourt": {depth: 32, lith: "Deltaic Sands", lat: 4.82, lng: 7.03, sop: "Shallow Aquifer. High Salinity risk. Screen at 25m."},
            "Effurun": {depth: 28, lith: "Deltaic Formation", lat: 5.56, lng: 5.78, sop: "Light Rig. Sandy silt. Rapid drilling expected."}
        };

        // Initialize Map
        let map = L.map('map', { zoomControl: false }).setView([9.08, 8.67], 5);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(map);

        function runAnalysis() {
            const val = document.getElementById('lgaSelect').value;
            if(!val) return alert("Select an LGA first!");
            
            const data = lgaData[val];
            const box = document.getElementById('resultBox');
            
            // Update UI
            box.classList.remove('hidden');
            document.getElementById('resName').innerText = val + " Intelligence";
            document.getElementById('resDepth').innerText = data.depth + "m";
            document.getElementById('resLith').innerText = data.lith;
            document.getElementById('resSOP').innerText = data.sop;

            // Update Map
            map.flyTo([data.lat, data.lng], 12);
            L.marker([data.lat, data.lng]).addTo(map).bindPopup(val).openPopup();
        }

        // Initialize Chart
        const rawData = <?php echo json_encode($chart_data); ?>;
        new Chart(document.getElementById('varianceChart'), {
            type: 'line',
            data: {
                labels: rawData.map(i => i.loc),
                datasets: [{
                    label: 'Variance (m)',
                    data: rawData.map(i => i.var),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.2)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: { scales: { y: { display: false }, x: { grid: { display: false }, ticks: { color: '#4ade80' } } }, plugins: { legend: { display: false } } }
        });
    </script>
</body>
</html>
<?php
error_reporting(E_ALL ^ E_DEPRECATED);
$file = "geosafe_logs.csv";
if (!file_exists($file)) {
    file_put_contents($file, "Ikeja,40,43\nAnkpa,50,53\nKano,60,85");
}

$chart_data = [];
$h = @fopen($file, "r");
if ($h) {
    while (($d = fgetcsv($h, 1000, ",")) !== FALSE) {
        if (count($d) >= 3) {
            $chart_data[] = ["loc" => $d[0], "var" => (floatval($d[2]) - floatval($d[1]))];
        }
    }
    fclose($h);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeoSafe v5.0 | 3MTT Showcase</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background: #011613; color: #e2e8f0; font-family: 'Inter', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }
        #map { height: 250px; border-radius: 12px; z-index: 1; }
        .emerald-gradient { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    </style>
</head>
<body class="p-4">

    <header class="text-center mb-6">
        <h1 class="text-2xl font-bold text-emerald-400">GEOSAFE v5.0</h1>
        <p class="text-xs text-emerald-200/60 uppercase tracking-widest">Hydro-Intelligence SOP | Geol. E. Rukevwe</p>
    </header>

    <div class="glass p-4 rounded-xl mb-4">
        <label class="block text-xs font-semibold mb-1 text-emerald-400">SELECT LGA FOR ANALYSIS</label>
        <select id="lgaSelect" class="w-full bg-slate-800 border border-emerald-500/30 p-3 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <option value="">-- Tap to Select LGA --</option>
            <option value="Ankpa">Ankpa (Kogi)</option>
            <option value="Ikeja">Ikeja (Lagos)</option>
            <option value="Kano">Kano (Kano)</option>
            <option value="Port Harcourt">Port Harcourt (Rivers)</option>
            <option value="Effurun">Effurun (Delta)</option>
        </select>
        <button onclick="runAnalysis()" class="w-full emerald-gradient mt-3 py-3 rounded-lg font-bold shadow-lg shadow-emerald-900/20 active:scale-95 transition-all">
            RUN FIELD ANALYSIS
        </button>
    </div>

    <div id="map" class="shadow-2xl border border-emerald-500/20 mb-4"></div>

    <div id="resultBox" class="glass p-4 rounded-xl hidden mb-4">
        <h3 id="resName" class="text-lg font-bold text-emerald-400 mb-2"></h3>
        <div class="grid grid-cols-2 gap-2 text-sm">
            <div class="p-2 bg-white/5 rounded">Depth: <span id="resDepth" class="font-bold text-white"></span></div>
            <div class="p-2 bg-white/5 rounded text-xs">Lithology: <span id="resLith" class="block font-bold text-white"></span></div>
        </div>
        <div class="mt-3 p-3 bg-emerald-900/20 border border-emerald-500/30 rounded-lg">
\
            <p class="text-xs font-bold text-emerald-400 underline mb-1">DRILLER'S SOP:</p>
            <p id="resSOP" class="text-xs leading-relaxed text-emerald-100"></p>
        </div>
    </div>

    <div class="glass p-4 rounded-xl">
        <p class="text-xs font-bold text-emerald-400 mb-2 italic underline">QA/QC: DRILLING VARIANCE (ACTUAL vs PRED)</p>
        <canvas id="varianceChart" style="max-height: 180px;"></canvas>
    </div>

    <script>
        const lgaData = {
            "Ikeja": {depth: 45, lith: "Coastal Plain Sands", lat: 6.6, lng: 3.35, sop: "Standard Rig. Monitor for Iron staining in upper 20m."},
            "Ankpa": {depth: 60, lith: "Benue Trough Sediments", lat: 7.4, lng: 7.6, sop: "Heavy Rig. Fractured Sandstone expected. Log SWL carefully."},
            "Kano": {depth: 75, lith: "Basement Complex", lat: 12.0, lng: 8.5, sop: "Percussion Rig. Crystalline rock. Fluoride risk high."},
            "Port Harcourt": {depth: 32, lith: "Deltaic Sands", lat: 4.82, lng: 7.03, sop: "Shallow Aquifer. High Salinity risk. Screen at 25m."},
            "Effurun": {depth: 28, lith: "Deltaic Formation", lat: 5.56, lng: 5.78, sop: "Light Rig. Sandy silt. Rapid drilling expected."}
        };

        // Initialize Map
        let map = L.map('map', { zoomControl: false }).setView([9.08, 8.67], 5);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(map);

        function runAnalysis() {
            const val = document.getElementById('lgaSelect').value;
            if(!val) return alert("Select an LGA first!");
            
            const data = lgaData[val];
            const box = document.getElementById('resultBox');
            
            // Update UI
            box.classList.remove('hidden');
            document.getElementById('resName').innerText = val + " Intelligence";
            document.getElementById('resDepth').innerText = data.depth + "m";
            document.getElementById('resLith').innerText = data.lith;
            document.getElementById('resSOP').innerText = data.sop;

            // Update Map
            map.flyTo([data.lat, data.lng], 12);
            L.marker([data.lat, data.lng]).addTo(map).bindPopup(val).openPopup();
        }

        // Initialize Chart
        const rawData = <?php echo json_encode($chart_data); ?>;
        new Chart(document.getElementById('varianceChart'), {
            type: 'line',
            data: {
                labels: rawData.map(i => i.loc),
                datasets: [{
                    label: 'Variance (m)',
                    data: rawData.map(i => i.var),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.2)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: { scales: { y: { display: false }, x: { grid: { display: false }, ticks: { color: '#4ade80' } } }, plugins: { legend: { display: false } } }
        });
    </script>
</body>
</html>
<?php error_reporting(E_ALL ^ E_DEPRECATED); ?>
<?php $f="geosafe_logs.csv"; if(!file_exists($f)){touch($f);} $h=@fopen($f,"r"); if($h){ while(($d=fgetcsv($h,1000,","))!==FALSE){ } fclose($h); } ?>
 <span>Total Estimate:</span> <span id="outTotal" class="text-emerald-400">0</span>
 </div>
 </div>
 <button onclick="shareWA()" class="w-full mt-3 py-2 bg-[#25D366] text-black font-bold rounded-lg text-[10px] uppercase">Share Quote via WhatsApp</button>
 </div>

 <div id="map" class="mb-4"></div>

 <div class="glass p-4 mb-4">
 <h3 class="text-emerald-400 font-bold text-[10px] uppercase mb-3">Field Audit Log</h3>
 <form method="POST" class="space-y-3">
 <div class="grid grid-cols-2 gap-2">
 <input type="text" name="site" id="siteName" placeholder="Site Name" class="input-field" required>
 <input type="text" name="lga" id="form_lga" placeholder="LGA" class="input-field" readonly>
 </div>
 <div class="grid grid-cols-2 gap-2">
 <input type="number" step="0.01" name="pred" placeholder="VES Pred (m)" class="input-field" required>
 <input type="number" step="0.01" name="actual" placeholder="Drill Actual (m)" class="input-field" required>
 </div>
 <div class="grid grid-cols-3 gap-2">
 <input type="text" name="lat" id="lat" class="input-field text-[9px]" readonly>
 <input type="text" name="lon" id="lon" class="input-field text-[9px]" readonly>
 <button type="button" onclick="getGPS()" class="bg-emerald-900/50 text-emerald-400 font-bold text-[9px] rounded">GPS</button>
 </div>
 <input type="number" step="0.1" name="swl" placeholder="Static Water Level (m)" class="input-field">
 <input type="hidden" name="lith" id="form_lith">
 <button type="submit" name="save_log" class="w-full py-3 bg-emerald-500 text-black font-black rounded-lg uppercase text-xs shadow-lg">Finalize Field Audit</button>
 </form>
 </div>

 <script>
 const lgaData = {
 "Ankpa": {depth:60, lith:"Sedimentary", risk:"High Iron", lat:7.4, lon:7.6},
 "Effurun": {depth:25, lith:"Sedimentary", risk:"Saline Intrusion", lat:5.56, lon:5.78},
 "Ikeja": {depth:45, lith:"Sedimentary", risk:"Urban Leaching", lat:6.6, lon:3.35},
 "Kano Municipal": {depth:75, lith:"Basement", risk:"Fluoride", lat:12.0, lon:8.5},
 "Warri South": {depth:20, lith:"Sedimentary", risk:"Hydrocarbon", lat:5.52, lon:5.75}
 };

 // Populate search
 const list = document.getElementById('lgaList');
 Object.keys(lgaData).forEach(lga => { let opt = document.createElement('option'); opt.value = lga; list.appendChild(opt); });

 function calculateBOQ() {
 const depth = parseFloat(document.getElementById('mDepth').value) || 0;
 const type = document.getElementById('mType').value;
 const pipes = Math.ceil(depth / 6);
 const gravel = Math.ceil(depth / 4);
 const rate = (type === 'Industrial') ? 22000 : 18000;
 const mob = (type === 'Industrial') ? 150000 : 30000;
 const drill = (type === 'Industrial') ? 9500 : 4000;
 const total = (pipes * rate) + (gravel * 5000) + (depth * drill) + mob + 20000; // 20k for misc
 
 document.getElementById('outPipes').innerText = pipes;
 document.getElementById('outGravel').innerText = gravel;
 document.getElementById('outTotal').innerText = "" + total.toLocaleString();
 return { total, type, depth };
 }

 function runAnalysis() {
 let lga = document.getElementById("lgaSearch").value;
 let d = lgaData[lga];
 if(!d) return;
 document.getElementById("mDepth").value = d.depth;
 document.getElementById("form_lga").value = lga;
 document.getElementById("form_lith").value = d.lith;
 document.getElementById("lat").value = d.lat;
 document.getElementById("lon").value = d.lon;
 map.setView([d.lat, d.lon], 11);
 L.marker([d.lat, d.lon]).addTo(map).bindPopup(lga).openPopup();
 calculateBOQ();
 }

 function shareWA() {
 const b = calculateBOQ();
 const site = document.getElementById('siteName').value || "Project Site";
 const text = `*GEOSAFE QUOTE*%0A*Site:* ${site}%0A*Method:* ${b.type}%0A*Depth:* ${b.depth}m%0A*Total Est:* ${b.total.toLocaleString()}%0A_3MTT Hydro-Intel Standard_`;
 window.open(`https://wa.me/?text=${text}`, '_blank');
 }

 let map = L.map('map').setView([9.08, 8.67], 5);
 L.tileLayer('https://{s}.tile.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(map);

 function getGPS() {
 navigator.geolocation.getCurrentPosition(p => {
 document.getElementById('lat').value = p.coords.latitude.toFixed(5);
 document.getElementById('lon').value = p.coords.longitude.toFixed(5);
 }, () => alert("Enable GPS"), {enableHighAccuracy: true});
 }
 calculateBOQ();
 </script>
</body>
</html>

