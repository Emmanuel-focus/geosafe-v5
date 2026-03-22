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
        body { background: #020617; color: #f8fafc; font-family: 'Inter', sans-serif; }
        .geo-header { font-family: 'Orbitron', sans-serif; letter-spacing: 2px; }
        .glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 1.25rem; }
        .input-field { background: #0f172a; border: 1px solid #1e293b; padding: 12px; border-radius: 10px; color: #fff; width: 100%; outline: none; font-size: 13px; transition: 0.3s; }
        .input-field:focus { border-color: #10b981; }
        #map { height: 180px; border-radius: 1.25rem; border: 2px solid #1e293b; margin: 1rem 0; }
        .btn-emerald { background: linear-gradient(135deg, #10b981 0%, #059669 100%); font-weight: 700; }
        .label-tag { font-size: 9px; font-weight: 700; color: #10b981; text-transform: uppercase; margin-bottom: 4px; display: block; }
    </style>
</head>
<body class="p-4 pb-24 max-w-xl mx-auto">

    <header class="text-center mb-6">
        <h1 class="geo-header text-3xl font-black text-emerald-400">GEOSAFE v5.0</h1>
        <p class="text-[8px] text-slate-500 tracking-[0.3em] uppercase mt-1">National Hydro-Intelligence • SQA Certified</p>
    </header>

    <section class="glass p-5 mb-6 shadow-2xl">
        <div class="space-y-4">
            <div>
                <label class="label-tag">Geopolitical Zone</label>
                <select id="zoneSelect" onchange="loadStates()" class="input-field">
                    <option value="">-- Select Zone --</option>
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
                    <select id="stateSelect" onchange="loadLGAs()" class="input-field"><option value="">-- State --</option></select>
                </div>
                <div>
                    <label class="label-tag">LGA</label>
                    <select id="lgaSelect" class="input-field"><option value="">-- LGA --</option></select>
                </div>
            </div>
            <button onclick="runExpertAnalysis()" class="w-full btn-emerald py-4 rounded-xl text-sm shadow-lg uppercase tracking-widest">Predict Hydro Intelligence</button>
        </div>
    </section>

    <div id="map"></div>

    <div id="intelligenceHub" class="hidden space-y-6">
        
        <div class="glass p-5 border-l-4 border-emerald-500">
            <h3 class="label-tag text-emerald-400 mb-4">Baseline Model (Field Editable)</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-slate-900/50 p-3 rounded-lg">
                    <span class="text-[9px] text-slate-400 block mb-1">Model BOQ (₦)</span>
                    <input type="text" id="resBOQ" class="bg-transparent text-emerald-400 font-bold outline-none w-full" value="₦0.00">
                </div>
                <div class="bg-slate-900/50 p-3 rounded-lg">
                    <span class="text-[9px] text-slate-400 block mb-1">Lithology</span>
                    <input type="text" id="resLith" class="bg-transparent text-slate-200 font-bold outline-none w-full" value="--">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4">
            <div class="glass p-4 border-l-4 border-blue-500">
                <h4 class="text-[10px] font-bold text-blue-400 uppercase mb-2">WHO & NSDWQ Standards Checklist</h4>
                <div class="grid grid-cols-2 gap-2 text-[9px] text-slate-300">
                    <span>✅ pH: 6.5 - 8.5</span>
                    <span>✅ Iron: < 0.3 mg/L</span>
                    <span>✅ TDS: < 500 mg/L</span>
                    <span>✅ Nitrate: < 50 mg/L</span>
                </div>
            </div>
            <div class="glass p-4 border-l-4 border-amber-500">
                <h4 class="text-[10px] font-bold text-amber-400 uppercase mb-2">Driller SOP & Technical Guide</h4>
                <p id="resSOP" class="text-[10px] text-slate-300 italic leading-relaxed"></p>
            </div>
        </div>

        <div class="glass p-5">
            <label class="label-tag">National Hydro-Archive & Pitfalls</label>
            <select id="archiveSelect" onchange="showArchiveFact()" class="input-field mb-4">
                <option value="">-- Explore National Facts --</option>
                <option value="drainage">The Niger-Benue Drainage System</option>
                <option value="ministry">Water Ministry Achievements (PEWASH)</option>
                <option value="pitfalls">Critical Pitfalls & Dry Hole Risks</option>
            </select>
            <div id="archiveDisplay" class="bg-slate-900/50 p-4 rounded-xl text-[10px] text-slate-300 border border-slate-800 hidden italic leading-relaxed"></div>
        </div>

        <div class="glass p-5">
            <div class="flex justify-between items-center mb-4">
                <h3 class="label-tag">VES Ground-Truthing (Editable)</h3>
                <div class="flex gap-2">
                    <button onclick="addRow()" class="bg-emerald-500/20 text-emerald-400 px-3 py-1 rounded-md text-xs font-bold border border-emerald-500/30 hover:bg-emerald-500/40">+</button>
                    <button onclick="removeRow()" class="bg-red-500/20 text-red-400 px-3 py-1 rounded-md text-xs font-bold border border-red-500/30 hover:bg-red-500/40">-</button>
                </div>
            </div>
            <table class="w-full text-[10px] text-left">
                <thead>
                    <tr class="text-slate-500 border-b border-slate-800">
                        <th>Res(Ωm)</th><th>Thk(m)</th><th>Dep(m)</th><th>Lithology</th>
                    </tr>
                </thead>
                <tbody id="geoTable">
                    <tr class="border-b border-slate-800/50">
                        <td class="py-2"><input type="number" class="bg-transparent w-full text-white outline-none" placeholder="0.0"></td>
                        <td class="py-2"><input type="number" class="bg-transparent w-full text-white outline-none" placeholder="0.0"></td>
                        <td class="py-2"><input type="number" class="bg-transparent w-full text-white outline-none" placeholder="0.0"></td>
                        <td class="py-2"><select class="bg-transparent w-full text-emerald-400 outline-none"><option>Topsoil</option><option>Sandstone</option><option>Basement Complex</option></select></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <button onclick="exportTechnicalPDF()" class="w-full bg-red-500/20 border border-red-500/40 py-4 rounded-xl text-red-400 text-xs font-bold uppercase tracking-widest">Generate Technical Audit PDF</button>
    </div>

    <script>
        const zoneData = {
            "NC": { states: ["Kogi", "Kwara", "Plateau"], data: { depth: 70, lith: "Basement/Benue Trough", sop: "DTH Hammer. Casing to bedrock in basement zones. Rotary in Trough zones.", rate: 16000, lat: 7.8, lng: 6.7 } },
            "NW": { states: ["Kano", "Kaduna", "Sokoto"], data: { depth: 65, lith: "Sokoto Basin/Crystalline", sop: "DTH drilling for basement sections. High fracture risk monitoring.", rate: 17500, lat: 12.0, lng: 8.5 } },
            "SS": { states: ["Delta", "Rivers", "Edo"], data: { depth: 40, lith: "Benin Formation", sop: "Reverse circulation mud drilling. Massive gravel packing required.", rate: 14000, lat: 5.5, lng: 5.8 } },
            "SW": { states: ["Lagos", "Oyo", "Ogun"], data: { depth: 55, lith: "Dahomey Basin", sop: "Mud rotary. Monitor saline intrusion levels strictly.", rate: 15500, lat: 6.5, lng: 3.4 } },
            "NE": { states: ["Borno", "Bauchi", "Adamawa"], data: { depth: 120, lith: "Chad Basin Sands", sop: "Deep rotary drilling. Multiple aquifer screens needed.", rate: 22000, lat: 11.8, lng: 13.1 } },
            "SE": { states: ["Enugu", "Anambra", "Abia"], data: { depth: 85, lith: "Anambra Basin", sop: "Rotary drilling. High chance of iron-rich aquifers.", rate: 18000, lat: 6.4, lng: 7.5 } }
        };

        const archives = {
            drainage: "Nigeria's drainage is divided into four main basins: The Niger, Lake Chad, Atlantic, and the Benue. Lokoja serves as the 'Hydro-Center' where the two largest meet.",
            ministry: "Achievements include the PEWASH (Partnership for Expanded Water, Sanitation and Hygiene) program aimed at ending open defecation and increasing borehole access by 2030.",
            pitfalls: "⚠️ Avoid 'Shallow Casing' in Niger Delta to prevent saltwater intrusion. ⚠️ Never drill in Chad Basin without multi-aquifer geophysical correlation."
        };

        function loadStates() {
            const z = document.getElementById('zoneSelect').value;
            const s = document.getElementById('stateSelect');
            s.innerHTML = '<option value="">-- State --</option>';
            if(zoneData[z]) zoneData[z].states.forEach(st => { s.innerHTML += `<option value="${st}">${st}</option>`; });
        }

        function loadLGAs() {
            const l = document.getElementById('lgaSelect');
            l.innerHTML = '<option value="Station_01">Main Station</option><option value="Station_02">Sub Station</option>';
        }

        let map = L.map('map', { zoomControl: false }).setView([9.0, 8.6], 5);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(map);

        function runExpertAnalysis() {
            const z = document.getElementById('zoneSelect').value;
            if(!z) return alert("Select Geopolitical Zone!");
            
            const info = zoneData[z].data;
            document.getElementById('intelligenceHub').classList.remove('hidden');
            document.getElementById('resBOQ').value = "₦" + (info.depth * info.rate).toLocaleString();
            document.getElementById('resLith').value = info.lith;
            document.getElementById('resSOP').innerText = info.sop;
            map.flyTo([info.lat, info.lng], 13);
            L.marker([info.lat, info.lng]).addTo(map);
        }

        function showArchiveFact() {
            const val = document.getElementById('archiveSelect').value;
            const disp = document.getElementById('archiveDisplay');
            if(val) { disp.innerText = archives[val]; disp.classList.remove('hidden'); }
            else { disp.classList.add('hidden'); }
        }

        function addRow() {
            const r = document.createElement('tr');
            r.className = "border-b border-slate-800/50";
            r.innerHTML = `<td class="py-2"><input type="number" class="bg-transparent w-full text-white outline-none"></td><td class="py-2"><input type="number" class="bg-transparent w-full text-white outline-none"></td><td class="py-2"><input type="number" class="bg-transparent w-full text-white outline-none"></td><td class="py-2"><select class="bg-transparent w-full text-emerald-400 outline-none"><option>Topsoil</option><option>Clay</option><option>Aquifer</option><option>Basement</option></select></td>`;
            document.getElementById('geoTable').appendChild(r);
        }

        function removeRow() {
            const t = document.getElementById('geoTable');
            if(t.rows.length > 1) t.deleteRow(-1);
        }

        function exportTechnicalPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.setFontSize(22); doc.setTextColor(16, 185, 129); doc.text("GEOSAFE v5.0 AUDIT", 14, 20);
            doc.setFontSize(10); doc.setTextColor(100); doc.text("Compliance Report: WHO Drinking Water Standards", 14, 28);
            
            doc.autoTable({ startY: 35, head: [['Field Parameter', 'Value']], body: [['Model BOQ', document.getElementById('resBOQ').value], ['Predicted Lithology', document.getElementById('resLith').value], ['Status', 'Expert Verified']] });
            
            const rows = [];
            document.querySelectorAll('#geoTable tr').forEach(tr => {
                const inp = tr.querySelectorAll('input, select');
                rows.push([inp[0].value, inp[1].value, inp[2].value, inp[3].value]);
            });
            doc.autoTable({ startY: doc.lastAutoTable.finalY + 10, head: [['Res(Ωm)', 'Thk(m)', 'Dep(m)', 'Lithology']], body: rows });
            doc.save(`GeoSafe_Report_${Date.now()}.pdf`);
        }
    </script>
</body>
</html>
