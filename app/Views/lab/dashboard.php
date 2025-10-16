<!-- Lab Technician dashboard partial (inner content only) -->
<section class="panel">
    <header class="panel-header">
        <h2>Laboratory Dashboard</h2>
        <p>Welcome back, <?= session()->get('name') ?>. Here's your lab overview for today.</p>
    </header>
    <div class="stack">
        <div class="actions-grid">
            <div class="action-tile">
                <span>Pending Tests</span>
                <strong>15</strong>
            </div>
            <div class="action-tile">
                <span>Completed Today</span>
                <strong>8</strong>
            </div>
            <div class="action-tile">
                <span>Urgent Tests</span>
                <strong>3</strong>
            </div>
            <div class="action-tile">
                <span>Equipment Status</span>
                <strong>All OK</strong>
            </div>
        </div>
    </div>
</section>

<section class="panel">
    <header class="panel-header">
        <h3>Quick Actions</h3>
    </header>
    <div class="stack">
        <div class="button-group">
            <a href="/lab/new-test" class="button button-primary">New Test Request</a>
            <a href="/lab/enter-results" class="button button-secondary">Enter Results</a>
            <a href="/lab/equipment" class="button button-secondary">Equipment Check</a>
            <a href="/lab/inventory" class="button button-secondary">Inventory</a>
        </div>
    </div>
</section>

<section class="panel">
    <header class="panel-header">
        <h3>Pending Test Requests</h3>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Test ID</th>
                        <th>Patient</th>
                        <th>Test Type</th>
                        <th>Doctor</th>
                        <th>Priority</th>
                        <th>Requested</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>T001234</td>
                        <td>Juan Dela Cruz</td>
                        <td>Blood Test</td>
                        <td>Dr. Santos</td>
                        <td><span class="badge badge-warning">Normal</span></td>
                        <td>09:00 AM</td>
                        <td><a href="#" class="button button-small">Start Test</a></td>
                    </tr>
                    <tr>
                        <td>T001235</td>
                        <td>Maria Garcia</td>
                        <td>Urine Analysis</td>
                        <td>Dr. Lopez</td>
                        <td><span class="badge badge-danger">Urgent</span></td>
                        <td>09:15 AM</td>
                        <td><a href="#" class="button button-small">Start Test</a></td>
                    </tr>
                    <tr>
                        <td>T001236</td>
                        <td>Pedro Rodriguez</td>
                        <td>X-Ray</td>
                        <td>Dr. Santos</td>
                        <td><span class="badge badge-warning">Normal</span></td>
                        <td>09:30 AM</td>
                        <td><a href="#" class="button button-small">Start Test</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="panel">
    <header class="panel-header">
        <h3>Recent Test Results</h3>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Test ID</th>
                        <th>Patient</th>
                        <th>Test Type</th>
                        <th>Result</th>
                        <th>Status</th>
                        <th>Completed</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>T001230</td>
                        <td>Ana Martinez</td>
                        <td>Blood Test</td>
                        <td>Normal</td>
                        <td><span class="badge badge-success">Completed</span></td>
                        <td>08:45 AM</td>
                    </tr>
                    <tr>
                        <td>T001231</td>
                        <td>Carlos Reyes</td>
                        <td>Urine Analysis</td>
                        <td>Abnormal</td>
                        <td><span class="badge badge-success">Completed</span></td>
                        <td>08:30 AM</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>
