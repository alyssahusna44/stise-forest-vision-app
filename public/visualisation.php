<?php
// public/visualisation.php - Displays various forest structure visualizations using Chart.js

require_once 'header.php'; // Correct path as header.php is in the same 'public' directory.

// Define the available cutting regimes. These should match the simulation result tables in your DB.
$cuttingRegimes = [45, 50, 55, 60, 65]; // Ensure these match your database tables (e.g., simulation_results_45) 
$selectedRegime = ''; // Variable to hold the currently selected regime

// Determine the selected cutting regime from POST (form submission) or GET (direct link).
if (isset($_POST['select_regime']) && in_array($_POST['select_regime'], $cuttingRegimes)) {
    $selectedRegime = $_POST['select_regime'];
} else if (isset($_GET['regime']) && in_array($_GET['regime'], $cuttingRegimes)) {
    $selectedRegime = $_GET['regime'];
}
?>

<section class="home-content">
    <h1>Forest Structure Visualizations</h1>

    <div class="simulation-controls">
        <form method="POST" class="display-selection-form">
            <label for="regime_select">Select Simulation Regime:</label>
            <select name="select_regime" id="regime_select" onchange="this.form.submit()">
                <option value="">-- Select Regime --</option>
                <?php foreach ($cuttingRegimes as $regime): ?>
                    <option value="<?php echo $regime; ?>" <?php echo ($selectedRegime == $regime) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($regime); ?>cm DBH Regime
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if (!$selectedRegime): ?>
        <p class='message-info'>Please select a cutting regime from the dropdown above to view the forest structure histograms.</p>
    <?php else: ?>
        <h2>Visualizations for Regime: <?php echo htmlspecialchars($selectedRegime); ?>cm DBH</h2>

        <div class="charts-wrapper">
            <div class="chart-section">
                <div class="chart-container">
                    <canvas id="currentDistributionChart"></canvas>
                </div>
                <p class="chart-error-message" id="currentDistributionError"></p>
            </div>

            <div class="chart-section">
                <div class="chart-container">
                    <canvas id="projectionChart"></canvas>
                </div>
                <p class="chart-error-message" id="projectionError"></p>
            </div>
        </div>

    <?php endif; ?>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectedRegime = "<?php echo $selectedRegime; ?>";

    if (!selectedRegime) {
        return;
    }

    const chartsToRender = [
        { id: 'currentDistributionChart', type: 'current_distribution', errorId: 'currentDistributionError' },
        { id: 'projectionChart', type: '30yr_projection', errorId: 'projectionError' }
    ];

    chartsToRender.forEach(chartInfo => {
        const chartCanvas = document.getElementById(chartInfo.id);
        const errorDisplay = document.getElementById(chartInfo.errorId);

        // Initially hide error message
        if (errorDisplay) errorDisplay.style.display = 'none';
        // Initially hide canvas if it exists, will show on success
        if (chartCanvas) chartCanvas.style.display = 'none';

        if (!chartCanvas) {
            console.error(`Canvas element with ID ${chartInfo.id} not found.`);
            return;
        }

        fetch(`../api/get_histogram_data.php?regime=${selectedRegime}&chart_type=${chartInfo.type}`)
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.error || `HTTP error! status: ${response.status}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    console.error(`Error fetching data for ${chartInfo.type}:`, data.error);
                    if (errorDisplay) {
                        errorDisplay.textContent = `Error loading chart: ${data.error}`;
                        errorDisplay.style.display = 'block';
                    }
                    if (chartCanvas) chartCanvas.style.display = 'none';
                    return;
                }

                if (Chart.getChart(chartInfo.id)) {
                    Chart.getChart(chartInfo.id).destroy();
                }

                new Chart(chartCanvas, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: data.datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false, // Allows height to be controlled by parent container
                        plugins: {
                            title: {
                                display: true,
                                text: data.chartTitle || 'Tree Count Distribution'
                            },
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Diameter Class (cm)'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Number of Trees'
                                },
                                beginAtZero: true
                            }
                        }
                    }
                });
                if (errorDisplay) errorDisplay.style.display = 'none'; // Hide error if chart loads
                if (chartCanvas) chartCanvas.style.display = 'block'; // Show canvas
            })
            .catch(error => {
                console.error(`Fetch error for ${chartInfo.type}:`, error);
                if (errorDisplay) {
                    errorDisplay.textContent = `Failed to load chart data: ${error.message}. Please ensure the simulation has been run and the database is accessible.`;
                    errorDisplay.style.display = 'block';
                }
                if (chartCanvas) chartCanvas.style.display = 'none';
            });
    });
});
</script>

<?php require_once 'footer.php'; // Include the common footer ?>