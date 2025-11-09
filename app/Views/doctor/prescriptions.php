<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <h2>Prescriptions</h2>
        <p>Create and manage your patient prescriptions</p>
    </header>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>New Prescription</h2>
    </header>

    <div class="stack">
        <div class="card">
            <div class="row between" style="gap: 1rem; align-items: flex-start;">
                <div style="flex: 1 1 320px;">
                    <label>Patient</label>
                    <select id="rx_patient" class="input" style="width:100%;">
                        <option value="">Select patient...</option>
                        <?php foreach (($patients ?? []) as $pt): ?>
                            <option value="<?= (int) $pt['id'] ?>"><?= esc($pt['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex: 1 1 100%;">
                    <label>Notes (optional)</label>
                    <textarea id="rx_notes" class="input" rows="2" placeholder="General instructions, advice, etc."></textarea>
                </div>
            </div>

            <div class="rx-items">
                <div class="rx-items-header row between">
                    <div class="col-name">Medication</div>
                    <div class="col-small">Dosage</div>
                    <div class="col-instr">Instructions</div>
                    <div class="col-actions">&nbsp;</div>
                </div>
                <div id="rx_items_container"></div>
                <div class="row" style="margin-top: .5rem;">
                    <button class="btn btn-secondary" onclick="addRxItem()">Add Item</button>
                    <div style="flex:1"></div>
                    <button class="btn btn-primary" onclick="savePrescription()">Save Prescription</button>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Recent Prescriptions</h2>
    </header>
    <div class="stack">
        <?php if (!empty($prescriptions)): ?>
            <?php foreach ($prescriptions as $rx): ?>
                <?php $items = json_decode($rx['items_json'] ?? '[]', true) ?: []; ?>
                <div class="card">
                    <div class="row between">
                        <div>
                            <strong>RX#<?= (int) $rx['id'] ?></strong>
                            <div class="text-muted">Created: <?= date('M j, Y g:i A', strtotime($rx['created_at'])) ?></div>
                        </div>
                        <div>
                            <span class="badge badge-green"><?= ucfirst($rx['status'] ?? 'pending') ?></span>
                        </div>
                    </div>
                    <div class="rx-summary">
                        <?php foreach ($items as $item): ?>
                            <div class="rx-line">
                                <strong><?= esc($item['name'] ?? '') ?></strong>
                                <span class="text-muted">• <?= esc($item['dosage'] ?? '') ?></span>
                                <?php if (!empty($item['instructions'])): ?>
                                    <div class="text-muted">Instructions: <?= esc($item['instructions']) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <?php if (!empty($rx['notes'])): ?>
                            <div class="text-muted" style="margin-top:.25rem;">Notes: <?= esc($rx['notes']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card">
                <p class="text-muted">No prescriptions yet.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
.input { width: 100%; padding: .5rem .75rem; border: 1px solid #cbd5e1; border-radius: .375rem; }
.rx-items { margin-top: 1rem; }
.rx-items-header { font-weight: 600; color:#475569; margin-bottom: .5rem; }
#rx_items_container .rx-row { display: grid; grid-template-columns: 1.6fr 1fr 2fr 80px; gap: .5rem; align-items: center; margin-bottom: .5rem; }
.rx-items-header .col-name { width: 100%; }
.rx-items-header .col-small { width: 120px; text-align: left; }
.rx-items-header .col-instr { flex: 1; }
.rx-items-header .col-actions { width: 80px; }
.btn { display:inline-flex; align-items:center; gap:.5rem; padding:.6rem 1rem; border:none; border-radius:.375rem; cursor:pointer; }
.btn-primary { background:#3B82F6; color:#fff; }
.btn-secondary { background:#e2e8f0; color:#334155; }
.btn-danger { background:#ef4444; color:#fff; }
.text-muted { color:#64748b; font-size:.875rem; }
.rx-summary .rx-line { margin:.25rem 0; }
</style>

<script>
function addRxItem() {
    const container = document.getElementById('rx_items_container');
    const row = document.createElement('div');
    row.className = 'rx-row';
    row.innerHTML = `
        <select class="input" data-field="med_id" onchange="onMedChange(this)">
            <option value="">Select medication...</option>
            <?php foreach (($medications ?? []) as $m): ?>
                <option value="<?= (int) $m['id'] ?>" data-name="<?= esc(($m['name'] ?? '') . (!empty($m['strength']) ? ' ' . $m['strength'] : '')) ?>" data-dosage="<?= esc($m['default_dosage'] ?? '') ?>"><?= esc($m['name'] ?? '') ?> <?= esc($m['strength'] ?? '') ?><?= !empty($m['form']) ? ' (' . esc($m['form']) . ')' : '' ?></option>
            <?php endforeach; ?>
        </select>
        <input class="input" placeholder="e.g., 1 tab" data-field="dosage">
        <input class="input" placeholder="e.g., after meals" data-field="instructions">
        <button class="btn btn-danger" onclick="this.closest('.rx-row').remove()">Remove</button>
        <input type="hidden" data-field="name">
    `;
    container.appendChild(row);
}

function collectItems() {
    const rows = document.querySelectorAll('#rx_items_container .rx-row');
    const items = [];
    rows.forEach(r => {
        const item = {};
        r.querySelectorAll('[data-field]').forEach(inp => {
            item[inp.dataset.field] = inp.value.trim();
        });
        // if name is empty but a medication is selected, derive from selected option
        if ((!item.name || item.name.length === 0) && item.med_id) {
            const sel = r.querySelector('select[data-field="med_id"]');
            if (sel && sel.selectedOptions.length) {
                item.name = sel.selectedOptions[0].dataset.name || sel.options[sel.selectedIndex].text;
            }
        }
        if (item.med_id || item.name) items.push(item);
    });
    return items;
}

function onMedChange(sel) {
    const opt = sel.selectedOptions[0];
    if (!opt) return;
    const row = sel.closest('.rx-row');
    const dosage = row.querySelector('[data-field="dosage"]');
    const nameHidden = row.querySelector('[data-field="name"]');
    if (dosage && opt.dataset.dosage) dosage.value = opt.dataset.dosage;
    if (nameHidden) nameHidden.value = opt.dataset.name || opt.textContent;
}

function savePrescription() {
    const patientId = document.getElementById('rx_patient').value;
    const notes = document.getElementById('rx_notes').value;
    const items = collectItems();

    if (!patientId) {
        alert('Please select a patient.');
        return;
    }
    if (items.length === 0) {
        alert('Please add at least one medication item.');
        return;
    }

    fetch('<?= site_url('doctor/prescriptions/create') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ patient_id: patientId, items: items, notes: notes })
    }).then(r => r.json()).then(res => {
        if (res.success) {
            alert('Prescription saved.');
            location.reload();
        } else {
            alert(res.message || 'Failed to save.');
        }
    }).catch(err => {
        console.error(err);
        alert('Network error.');
    });
}

// initialize with one row
addRxItem();
</script>

<?= $this->endSection() ?>
