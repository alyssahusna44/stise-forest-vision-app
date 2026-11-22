<?php
// public/forest_generation.php - Forest Generation and Management Dashboard

require_once 'header.php'; // Includes common header, db_connection, SpeciesManager
require_once __DIR__ . '/../includes/ForestGenerator.php';
require_once __DIR__ . '/../includes/ForestSimulator.php'; // NEW: Include the simulator class

$conn = getDbConnection();
$speciesManager = new SpeciesManager($conn);
$forestGenerator = new ForestGenerator($conn, $speciesManager);
$forestSimulator = new ForestSimulator($conn, $speciesManager); // NEW: Instantiate ForestSimulator

$selectedRegime = ''; // Variable to hold the currently selected cutting regime for display

// --- Handle Form Submissions ---

// Handle species import request
if (isset($_POST['import_species'])) {
    $speciesManager->importCsvToDb(__DIR__ . '/../data/species.csv');
    echo "<p class='message-success'>Species import process completed. Check your 'species' table!</p>";
}

// Handle forest generation request
if (isset($_POST['generate_forest'])) {
    if (!$speciesManager->isSpeciesTablePopulated()) {
        echo "<p class='message-error'>Error: Species data is not yet imported. Please import species first!</p>";
    } else {
        $conn->query("TRUNCATE TABLE base_forest");
        echo "<p class='message-info'>Truncated 'base_forest' table.</p>";
        $forestGenerator->generateForest();
        echo "<p class='message-success'>Forest generation initiated. Check your 'base_forest' table!</p>";
    }
}

// Handle simulation run requests
$cuttingRegimes = [45, 50, 55, 60, 65];
foreach ($cuttingRegimes as $regime) {
    if (isset($_POST['run_simulation_' . $regime])) {
        echo "<p class='message-info'>Running simulation for $regime cm DBH cutting regime...</p>";
        $forestSimulator->runSimulation($regime);
        echo "<p class='message-success'>Simulation for $regime cm DBH complete!</p>";
    }
}

// Handle display selection
// Prioritize POST for initial selection, then GET for pagination
if (isset($_POST['select_regime']) && in_array($_POST['select_regime'], $cuttingRegimes)) {
    $selectedRegime = $_POST['select_regime'];
} elseif (isset($_GET['select_regime']) && in_array($_GET['select_regime'], $cuttingRegimes)) {
    $selectedRegime = $_GET['select_regime'];
}


// --- Fetch Current Counts for Display ---
$totalTreesBaseForest = 0;
$resultBaseForest = $conn->query("SELECT COUNT(*) as total FROM base_forest");
if ($resultBaseForest) {
    $rowBaseForest = $resultBaseForest->fetch_assoc();
    $totalTreesBaseForest = $rowBaseForest['total'];
}

$totalSpeciesInDb = 0; // Fetch from header or re-fetch here if needed
$resultSpecies = $conn->query("SELECT COUNT(*) as total FROM species");
if ($resultSpecies) {
    $rowSpecies = $resultSpecies->fetch_assoc();
    $totalSpeciesInDb = $rowSpecies['total'];
}

// --- Pagination Variables ---
$rowsPerPage = 50; // Number of rows to display per page
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) {
    $currentPage = 1; // Ensure page number is at least 1
}


// --- Fetch data for the selected simulation regime if any ---
$simulationResults = [];
$simulationTableCount = 0;
$totalPages = 0; // Initialize total pages

if ($selectedRegime) {
    $tableNameToDisplay = "simulation_results_" . $selectedRegime;
    $checkTableExists = $conn->query("SHOW TABLES LIKE '$tableNameToDisplay'");
    if ($checkTableExists && $checkTableExists->num_rows > 0) {
        // Get total count for pagination
        $resultCount = $conn->query("SELECT COUNT(*) as total FROM $tableNameToDisplay");
        if($resultCount) {
            $rowCount = $resultCount->fetch_assoc();
            $simulationTableCount = $rowCount['total'];
            $totalPages = ceil($simulationTableCount / $rowsPerPage);
            if ($currentPage > $totalPages && $totalPages > 0) {
                $currentPage = $totalPages; // Adjust current page if it exceeds total pages
            } else if ($totalPages == 0) { // Handle case with no records
                $currentPage = 1;
            }
            $resultCount->free();
        }

        $offset = ($currentPage - 1) * $rowsPerPage;
        // Adjusted query for pagination
        $resultSim = $conn->query("SELECT * FROM $tableNameToDisplay LIMIT $rowsPerPage OFFSET $offset");
        if ($resultSim) {
            while ($row = $resultSim->fetch_assoc()) {
                $simulationResults[] = $row;
            }
            $resultSim->free();
        } else {
            echo "<p class='message-error'>Error fetching data from $tableNameToDisplay: " . $conn->error . "</p>";
        }
    } else {
        echo "<p class='message-info'>No simulation results found for $selectedRegime cm DBH. Please run the simulation first.</p>";
    }
}

$conn->close();
?>

    <h1>Forest Generation & Management</h1>

    <section class="home-content">
        <h2>Species Management</h2>
        <form method="POST">
            <p>Current number of species in database: <?php echo $totalSpeciesInDb; ?></p>
            <button type="submit" name="import_species">Import Species from CSV</button>
            <p class="warning-text">Warning: This will clear existing species data in the 'species' table and re-import from 'data/species.csv'.</p>
        </form>

        <h2>Create Base Forest</h2>
        <form method="POST">
            <p>Current number of trees in 'base_forest' database: <?php echo $totalTreesBaseForest; ?></p>
            <button type="submit" name="generate_forest">Generate New Base Forest (10x10 blocks)</button>
            <p class="warning-text">Warning: This will clear existing tree data in the 'base_forest' table and generate a new forest.</p>
        </form>
    </section>

    <section class="home-content">
        <h2>Damage and Projection Simulations</h2>
        <p>Run simulations for different cutting regimes (DBH thresholds) to analyze impacts and future growth.</p>
        <div class="simulation-controls">
            <div class="simulation-buttons">
                <?php foreach ($cuttingRegimes as $regime): ?>
                    <form method="POST" style="display:inline-block; margin-right: 10px;">
                        <button type="submit" name="run_simulation_<?php echo $regime; ?>">Run Simulation <?php echo $regime; ?>cm</button>
                    </form>
                <?php endforeach; ?>
            </div>
            <p class="warning-text">Note: Running a simulation will clear previous results for that specific regime table.</p>

            <form method="POST" class="display-selection-form">
                <label for="regime_select">View Simulation Results:</label>
                <select name="select_regime" id="regime_select" onchange="this.form.submit()">
                    <option value="">-- Select Regime --</option>
                    <?php foreach ($cuttingRegimes as $regime): ?>
                        <option value="<?php echo $regime; ?>" <?php echo ($selectedRegime == $regime) ? 'selected' : ''; ?>>
                            <?php echo $regime; ?>cm DBH
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </section>

    <?php if ($selectedRegime && !empty($simulationResults)): ?>
        <section class="home-content">
            <h2>Results for <?php echo $selectedRegime; ?>cm DBH Regime (Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?> - Total Trees: <?php echo $simulationTableCount; ?>)</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Tree ID</th>
                            <th>Block</th>
                            <th>Coord (X,Y)</th>
                            <th>Species</th>
                            <th>S. Group</th>
                            <th>Diam (m)</th>
                            <th>D. Class</th>
                            <th>Height (m)</th>
                            <th>Dip?</th>
                            <th>Vol (m³)</th>
                            <th>Status</th>
                            <th>Prod (m³)</th>
                            <th>Cut Angle (deg)</th>
                            <th>D. Stem (m³)</th>
                            <th>D. Crown (m³)</th>
                            <th>D30 (cm)</th>
                            <th>Vol30 (m³)</th>
                            <th>Prod30 (m³)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($simulationResults as $tree): ?>
                            <tr>
                                <td><?php echo $tree['tree_id']; ?></td>
                                <td><?php echo $tree['block_i'] . ',' . $tree['block_j']; ?></td>
                                <td><?php echo $tree['coord_x'] . ',' . $tree['coord_y']; ?></td>
                                <td><?php echo $tree['species_name']; ?></td>
                                <td><?php echo $tree['species_group']; ?></td>
                                <td><?php echo number_format($tree['diameter'], 2); ?></td>
                                <td><?php echo $tree['diameter_class']; ?></td>
                                <td><?php echo number_format($tree['height'], 2); ?></td>
                                <td><?php echo $tree['is_dipterocarp'] ? 'Yes' : 'No'; ?></td>
                                <td><?php echo number_format($tree['volume'], 4); ?></td>
                                <td><strong><?php echo $tree['status']; ?></strong></td>
                                <td><?php echo number_format($tree['prod'], 4); ?></td>
                                <td><?php echo ($tree['cut_angle'] != 0) ? number_format($tree['cut_angle'], 0) : '-'; ?></td>
                                <td><?php echo ($tree['damage_stem'] != 0) ? number_format($tree['damage_stem'], 4) : '-'; ?></td>
                                <td><?php echo ($tree['damage_crown'] != 0) ? number_format($tree['damage_crown'], 4) : '-'; ?></td>
                                <td><?php echo number_format($tree['diameter30'], 2); ?></td>
                                <td><?php echo number_format($tree['volume30'], 4); ?></td>
                                <td><?php echo number_format($tree['prod30'], 4); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($totalPages > 1): // Only show dropdown if there's more than one page ?>
                <div class="pagination-dropdown">
                    <label for="page_select">Go to Page:</label>
                    <select name="page_select" id="page_select">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo ($i == $currentPage) ? 'selected' : ''; ?>>
                                Page <?php echo $i; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
            <?php endif; ?>
        </section>
    <?php elseif ($selectedRegime): ?>
        <section class="home-content">
            <p class="message-info">No data to display for <?php echo $selectedRegime; ?>cm DBH. Please run the simulation first.</p>
        </section>
    <?php endif; ?>


<?php
require_once 'footer.php';
?>