‎<!DOCTYPE html>
‎<html lang="en">
‎<head>
‎    <meta charset="UTF-8">
‎    <meta name="viewport" content="width=device-width, initial-scale=1.0">
‎    <title>GeoSafe v5.0 | National Hydro-Intelligence</title>
‎    <script src="https://cdn.tailwindcss.com"></script>
‎    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
‎    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
‎    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
‎    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
‎    <style>
‎        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Inter:wght@400;800&display=swap');
‎        :root { --emerald: #00ff9d; --bg: #020617; }
‎        body { background: var(--bg); color: #ffffff; font-family: 'Inter', sans-serif; }
‎        .geo-header { font-family: 'Orbitron', sans-serif; letter-spacing: 4px; color: var(--emerald); text-shadow: 0 0 15px rgba(0,255,157,0.5); }
‎        .glass { background: rgba(15, 23, 42, 0.95); border: 2px solid rgba(0, 255, 157, 0.4); border-radius: 1.5rem; }
‎        
‎        .input-field { background: #000000 !important; border: 2px solid #475569; padding: 14px; border-radius: 12px; color: #ffffff !important; font-weight: 900; font-size: 16px; outline: none; width: 100%; }
‎        .input-field:focus { border-color: var(--emerald); box-shadow: 0 0 15px rgba(0, 255, 157, 0.6); }
‎        
‎        label { color: var(--emerald); font-weight: 900; font-size: 10px; text-transform: uppercase; margin-bottom: 6px; display: block; }
‎        #map { height: 200px; border-radius: 1.5rem; border: 2px solid var(--emerald); margin: 1rem 0; background: #000; }
‎        .btn-predict { background: linear-gradient(90deg, #00ff9d 0%, #008f5d 100%); color: #000; font-weight: 900; text-transform: uppercase; }
‎        .expert-card { border-left: 5px solid var(--emerald); background: rgba(0, 255, 157, 0.05); padding: 12px; border-radius: 0 12px 12px 0; margin-bottom: 10px; }
‎        
‎        table input { background: #000; width: 100%; color: #fff; font-weight: 900; outline: none; border-bottom: 1px solid #334155; padding: 5px; }
‎        #preview { width: 100%; max-height: 150px; object-fit: cover; border-radius: 12px; display: none; margin-top: 10px; border: 1px solid var(--emerald); }
‎    </style>
‎</head>
‎<body class="p-4 pb-32 max-w-xl mx-auto">
‎
‎    <header class="text-center py-6">
‎        <h1 class="geo-header text-4xl font-black">GEOSAFE</h1>
‎        <p class="text-[8px] text-slate-500 tracking-[0.4em] font-black mt-2 uppercase">National Intelligence • Geol. E. Rukevwe</p>
‎    </header>
‎
‎    <section class="glass p-6 mb-6">
‎        <div class="space-y-4">
‎            <div>
‎                <label>Geopolitical Zone</label>
‎                <select id="zoneSel" onchange="syncStates()" class="input-field">
‎                    <option value="">-- Select Zone --</option>
‎                    <option value="NC">North Central (Kogi / Plateau)</option>
‎                    <option value="NE">North East (Chad Basin)</option>
‎                    <option value="NW">North West (Sokoto Basin)</option>
‎                    <option value="SE">South East (Anambra Basin)</option>
‎                    <option value="SS">South South (Niger Delta)</option>
‎                    <option value="SW">South West (Dahomey Basin)</option>
‎                </select>
‎            </div>
‎            <div class="grid grid-cols-2 gap-3">
‎                <select id="stateSel" onchange="syncLGAs()" class="input-field"><option value="">-- State --</option></select>
‎                <select id="lgaSel" class="input-field"><option value="">-- LGA --</option></select>
‎            </div>
‎            <button onclick="executeIntelligence()" class="w-full btn-predict py-5 rounded-xl text-xs shadow-xl">Predict Hydro Intelligence</button>
‎        </div>
‎    </section>
‎
‎    <div id="map"></div>
‎
‎    <div id="intelHub" class="hidden space-y-4">
‎        
‎        <div class="expert-card border-blue-500">
‎            <h4 class="text-blue-400 font-black text-[10px] mb-1">WHO STANDARDS</h4>
‎            <p class="text-[10px] text-slate-200">pH: 6.5-8.5 | Iron: < 0.3mg/L | TDS: < 500mg/L</p>
‎        </div>
‎        <div class="expert-card border-amber-500">
‎            <h4 class="text-amber-500 font-black text-[10px] mb-1">DRILLER SOP</h4>
‎            <p id="sopDisplay" class="text-[10px] text-slate-200 italic font-bold"></p>
‎        </div>
‎        <div class="expert-card border-emerald-500">
‎            <h4 class="text-emerald-400 font-black text-[10px] mb-1">EXPERT RECOMMENDATION</h4>
‎            <p id="recDisplay" class="text-[10px] text-slate-200 font-bold"></p>
‎        </div>
‎
‎        <div class="glass p-5">
‎            <label>Borehole Site Photo</label>
‎            <input type="file" accept="image/*" id="siteImg" onchange="previewImg(event)" class="text-[10px] text-slate-400">
‎            <img id="preview">
‎        </div>
‎
‎        <div class="glass p-5">
‎            <div class="flex justify-between items-center mb-3">
‎                <label>Geophysical Layer Log</label>
‎                <div class="flex gap-2">
‎                    <button onclick="addRow()" class="bg-emerald-500 text-black font-black w-8 h-8 rounded-lg">+</button>
‎                    <button onclick="remRow()" class="bg-red-600 text-white font-black w-8 h-8 rounded-lg">-</button>
‎                </div>
‎            </div>
‎            <table class="w-full text-[11px]">
‎                <thead class="text-slate-500 uppercase">
‎                    <tr><th>Res(Ωm)</th><th>Thk(m)</th><th>Lithology</th></tr>
‎                </thead>
‎                <tbody id="geoBody">
‎                    <tr>
‎                        <td><input type="number" class="res-v"></td>
‎                        <td><input type="number" class="thk-v"></td>
‎                        <td><input type="text" class="lith-v" value="Topsoil"></td>
‎                    </tr>
‎                </tbody>
‎            </table>
‎        </div>
‎
‎        <div class="glass p-5">
‎            <label>Variance Control (VES vs Drill)</label>
‎            <div class="grid grid-cols-2 gap-3 mb-4">
‎                <input type="number" id="vPred" oninput="calcVar()" class="input-field" placeholder="Predicted">
‎                <input type="number" id="vAct" oninput="calcVar()" class="input-field" placeholder="Actual">
‎            </div>
‎            <div class="flex justify-between items-center bg-black p-4 rounded-xl border border-emerald-500/30 font-black">
‎                <span id="vRes" class="text-white">0.00m</span>
‎                <div id="boqRes" class="text-right">
‎                    </div>
‎            </div>
‎            
‎            <div class="grid grid-cols-2 gap-3 mt-4">
‎                <button onclick="exportToPDF()" class="bg-red-600 text-white py-4 rounded-xl font-black text-[10px] uppercase">PDF Export</button>
‎                <button onclick="exportToCSV()" class="bg-blue-600 text-white py-4 rounded-xl font-black text-[10px] uppercase">CSV Export</button>
‎            </div>
‎        </div>
‎    </div>
‎
‎    <script>
‎        const MASTER_DB = {
‎            "NC": { 
‎                states: ["Kogi", "Plateau"], 
‎                lgas: ["Ankpa", "Idah", "Lokoja"], 
‎                d_hand: 35, r_hand: 6500, 
‎                d_ind: 95, r_ind: 19500,
‎                sop: "Ankpa/Idah: Percussion required. Lokoja: Air Hammer (DTH) for Basement.", 
‎                rec: "Hand-drill for shallow sands (<35m). Industrial Rig required for Ajali Sandstone (95m+).", 
‎                lat: 7.37, lng: 7.63 
‎            },
‎            "NW": { states: ["Kano", "Sokoto"], lgas: ["Kano Municipal", "Sokoto North"], d_hand: 25, r_hand: 5500, d_ind: 60, r_ind: 18000, sop: "Mud rotary for Basin sands. Hammer for Kaduna basement.", rec: "Ensure gravel packing for aquifer layers.", lat: 12.0, lng: 8.5 },
‎            "SS": { states: ["Delta", "Rivers"], lgas: ["Uvwie", "Warri South", "Port Harcourt"], d_hand: 20, r_hand: 6000, d_ind: 45, r_ind: 14500, sop: "Reverse circulation mud drilling. Use PVC screens.", rec: "High yield potential in Benin Formation. Monitor saline intrusion.", lat: 5.5, lng: 5.8 },
‎            "NE": { states: ["Borno", "Bauchi"], lgas: ["Maiduguri", "Bauchi Central"], d_hand: 40, r_hand: 7000, d_ind: 110, r_ind: 23000, sop: "Deep rotary drilling for Chad Basin sands.", rec: "Target deep aquifer layers (Zone C).", lat: 11.8, lng: 13.1 },
‎            "SE": { states: ["Anambra", "Enugu"], lgas: ["Awka South", "Enugu North"], d_hand: 30, r_hand: 6500, d_ind: 85, r_ind: 17500, sop: "Standard rotary mud drilling. Bentonite control critical.", rec: "Target Ajali Sandstone formations.", lat: 6.4, lng: 7.5 },
‎            "SW": { states: ["Lagos", "Oyo"], lgas: ["Ikeja", "Ibadan North"], d_hand: 25, r_hand: 6000, d_ind: 55, r_ind: 15500, sop: "Mud rotary with polymer additives.", rec: "Saltwater seal required for coastal Lagos areas.", lat: 6.5, lng: 3.4 }
‎        };
‎
‎        function syncStates() {
‎            const z = document.getElementById('zoneSel').value;
‎            const s = document.getElementById('stateSel');
‎            s.innerHTML = '<option value="">-- State --</option>';
‎            if(MASTER_DB[z]) MASTER_DB[z].states.forEach(st => s.innerHTML += `<option value="${st}">${st}</option>`);
‎            syncLGAs();
‎        }
‎
‎        function syncLGAs() {
‎            const z = document.getElementById('zoneSel').value;
‎            const l = document.getElementById('lgaSel');
‎            l.innerHTML = '<option value="">-- LGA --</option>';
‎            if(MASTER_DB[z]) MASTER_DB[z].lgas.forEach(lg => l.innerHTML += `<option value="${lg}">${lg}</option>`);
‎        }
‎
‎        let map = L.map('map', { zoomControl: false }).setView([9.0, 8.6], 5);
‎        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(map);
‎
‎        function executeIntelligence() {
‎            const z = document.getElementById('zoneSel').value;
‎            const l = document.getElementById('lgaSel').value;
‎            if(!l || l === "" || l.includes("--")) return alert("Please select Zone, State, and LGA first!");
‎
‎            const data = MASTER_DB[z];
‎            document.getElementById('intelHub').classList.remove('hidden');
‎            document.getElementById('sopDisplay').innerText = data.sop;
‎            document.getElementById('recDisplay').innerText = data.rec;
‎            
‎            // Dual BOQ Logic
‎            const hCost = (data.d_hand * data.r_hand).toLocaleString();
‎            const iCost = (data.d_ind * data.r_ind).toLocaleString();
‎            
‎            document.getElementById('vPred').value = data.d_ind; 
‎            document.getElementById('boqRes').innerHTML = `
‎                <div class="leading-tight">
‎                    <p class="text-[9px] text-slate-400 font-black">HAND: ₦${hCost}</p>
‎                    <p class="text-[12px] text-emerald-400 font-black">INDUS: ₦${iCost}</p>
‎                </div>
‎            `;
‎            
‎            setTimeout(() => { map.invalidateSize(); map.flyTo([data.lat, data.lng], 12); L.marker([data.lat, data.lng]).addTo(map).bindPopup(l).openPopup(); }, 300);
‎            calcVar();
‎        }
‎
‎        function calcVar() {
‎            const p = parseFloat(document.getElementById('vPred').value) || 0;
‎            const a = parseFloat(document.getElementById('vAct').value) || 0;
‎            document.getElementById('vRes').innerText = (a - p).toFixed(2) + "m";
‎        }
‎
‎        function addRow() {
‎            const row = `<tr><td><input type="number" class="res-v"></td><td><input type="number" class="thk-v"></td><td><input type="text" class="lith-v"></td></tr>`;
‎            document.getElementById('geoBody').insertAdjacentHTML('beforeend', row);
‎        }
‎
‎        function remRow() {
‎            const b = document.getElementById('geoBody');
‎            if(b.rows.length > 1) b.deleteRow(-1);
‎        }
‎
‎        function previewImg(e) {
‎            const p = document.getElementById('preview');
‎            p.src = URL.createObjectURL(e.target.files[0]);
‎            p.style.display = 'block';
‎        }
‎
‎        function exportToPDF() {
‎            const { jsPDF } = window.jspdf;
‎            const doc = new jsPDF();
‎            doc.setFontSize(18);
‎            doc.text("GEOSAFE FIELD AUDIT REPORT", 20, 20);
‎            doc.setFontSize(12);
‎            doc.text("Geol. E. Rukevwe - National Hydro Intelligence", 20, 30);
‎            doc.text("--------------------------------------------------", 20, 35);
‎            doc.text("LGA Station: " + document.getElementById('lgaSel').value, 20, 45);
‎            doc.text("Depth Variance: " + document.getElementById('vRes').innerText, 20, 55);
‎            doc.text("BOQ Summary: " + document.getElementById('boqRes').innerText.replace(/\n/g, ' | '), 20, 65);
‎            doc.save("GeoSafe_Audit_Final.pdf");
‎        }
‎
‎        function exportToCSV() {
‎            let csv = "Resistivity(Ohm-m),Thickness(m),Lithology\n";
‎            document.querySelectorAll("#geoBody tr").forEach(row => {
‎                const r = row.querySelector(".res-v").value || 0;
‎                const t = row.querySelector(".thk-v").value || 0;
‎                const l = row.querySelector(".lith-v").value || "N/A";
‎                csv += `${r},${t},${l}\n`;
‎            });
‎            const blob = new Blob([csv], { type: 'text/csv' });
‎            const url = URL.createObjectURL(blob);
‎            const a = document.createElement('a');
‎            a.href = url; a.download = 'GeoSafe_Field_Log.csv'; a.click();
‎        }
‎    </script>
‎</body>
‎</html>
‎
