<?php
// public/final_output.php - Displays the summarized final output table for a selected regime

require_once 'header.php'; // Includes common header, db_connection, HTML head/nav
require_once __DIR__ . '/../includes/db_connection.php'; // Ensure db connection is available

$conn = getDbConnection();

$cuttingRegimes = [45, 50, 55, 60, 65]; // Define available cutting regimes
$selectedRegime = ''; // Variable to hold the currently selected cutting regime

// Handle display selection from the dropdown
if (isset($_POST['select_regime']) && in_array($_POST['select_regime'], $cuttingRegimes)) {
    $selectedRegime = $_POST['select_regime'];
} else if (isset($_GET['regime']) && in_array($_GET['regime'], $cuttingRegimes)) {
    // Fallback for direct links from dashboard, if any exist that still use GET
    $selectedRegime = $_GET['regime'];
}


$tableNameToDisplay = '';
$finalOutputData = [];

// Only attempt to fetch data if a regime is selected
if ($selectedRegime) {
    $tableNameToDisplay = "simulation_results_" . $selectedRegime;

    // Check if the table exists for the selected regime
    $checkTableExists = $conn->query("SHOW TABLES LIKE '$tableNameToDisplay'");

    if ($checkTableExists && $checkTableExists->num_rows > 0) {
        // --- Fetch data for Final Output Table ---
        $sqlFinalOutput = "
            SELECT
                t.species_group,
                SUM(t.volume) AS total_volume_all_trees,
                SUM(CASE WHEN t.status IN ('cut', 'victim') THEN t.prod ELSE 0 END) AS total_production_current,
                SUM(CASE WHEN t.status = 'victim' THEN t.volume ELSE 0 END) AS total_damage_volume,
                SUM(CASE WHEN t.status IN ('keep', 'stand') THEN t.volume ELSE 0 END) AS total_remainder_volume,
                SUM(t.volume30) AS total_volume30,
                SUM(t.prod30) AS total_production30
            FROM
                $tableNameToDisplay t
            GROUP BY
                t.species_group
            ORDER BY
                t.species_group ASC
        ";

        $resultFinalOutput = $conn->query($sqlFinalOutput);

        if ($resultFinalOutput) {
            while ($row = $resultFinalOutput->fetch_assoc()) {
                $finalOutputData[] = $row;
            }
            $resultFinalOutput->free();

            // Fetch species group names to map IDs to names
            $speciesGroupMapping = [];
            $sqlSpeciesGroupNames = "SELECT species_group, speciesgroup_name FROM species_group_names ORDER BY species_group ASC";
            $resultSpeciesGroupNames = $conn->query($sqlSpeciesGroupNames);
            if ($resultSpeciesGroupNames) {
                while ($row = $resultSpeciesGroupNames->fetch_assoc()) {
                    $speciesGroupMapping[$row['species_group']] = $row['speciesgroup_name'];
                }
                $resultSpeciesGroupNames->free();
            } else {
                error_log("Error fetching species group names: " . $conn->error);
                // Fallback or warning if table not found
            }

            // Replace species_group ID with name and calculate overall totals
            $overallTotals = [
                'species_group' => 'Overall Total',
                'total_volume_all_trees' => 0,
                'total_production_current' => 0,
                'total_damage_volume' => 0,
                'total_remainder_volume' => 0,
                'total_volume30' => 0,
                'total_production30' => 0
            ];

            foreach ($finalOutputData as $key => $row) {
                // Map species_group ID to name
                $finalOutputData[$key]['species_group_name'] = $speciesGroupMapping[$row['species_group']] ?? 'Unknown Group ' . $row['species_group'];

                // Sum for overall totals
                $overallTotals['total_volume_all_trees'] += (float)$row['total_volume_all_trees'];
                $overallTotals['total_production_current'] += (float)$row['total_production_current'];
                $overallTotals['total_damage_volume'] += (float)$row['total_damage_volume'];
                $overallTotals['total_remainder_volume'] += (float)$row['total_remainder_volume'];
                $overallTotals['total_volume30'] += (float)$row['total_volume30'];
                $overallTotals['total_production30'] += (float)$row['total_production30'];
            }
            if (!empty($finalOutputData)) {
                $finalOutputData[] = $overallTotals; // Add overall totals as the last row
            }

        } else {
            echo "<p class='message-error'>Error fetching final output data from $tableNameToDisplay: " . $conn->error . "</p>";
        }
    } else {
        echo "<p class='message-info'>No simulation results table found for $selectedRegime cm DBH. Please run the simulation first from the Forest Generation page.</p>";
    }
} else {
    // If no regime is selected initially or via POST/GET
    // This message will be displayed before the dropdown form
}

$conn->close();
?>

<section class="home-content">
    <h1>Final Output Table <?php echo $selectedRegime ? "for " . $selectedRegime . "cm DBH Regime" : ""; ?></h1>

    <div class="simulation-controls">
        <form method="POST" class="display-selection-form">
            <label for="regime_select">Select Simulation Regime:</label>
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

    <?php if (!$selectedRegime): ?>
        <p class='message-info'>Please select a cutting regime from the dropdown above to view the Final Output Table.</p>
    <?php elseif ($selectedRegime && !empty($finalOutputData)): ?>
        <div class="table-responsive">
            <table class="final-output-table">
                <thead>
                    <tr>
                        <th>Species Group</th>
                        <th>Total Volume (All Trees) (m³)</th>
                        <th>Production (Current) (m³)</th>
                        <th>Damage Volume (Victim) (m³)</th>
                        <th>Remainder Volume (Keep & Stand) (m³)</th>
                        <th>Volume 30 (m³)</th>
                        <th>Production 30 (m³)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($finalOutputData as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['species_group_name'] ?? $row['species_group']); ?></td>
                        <td><?php echo htmlspecialchars(number_format($row['total_volume_all_trees'], 2)); ?></td>
                        <td><?php echo htmlspecialchars(number_format($row['total_production_current'], 2)); ?></td>
                        <td><?php echo htmlspecialchars(number_format($row['total_damage_volume'], 2)); ?></td>
                        <td><?php echo htmlspecialchars(number_format($row['total_remainder_volume'], 2)); ?></td>
                        <td><?php echo htmlspecialchars(number_format($row['total_volume30'], 2)); ?></td>
                        <td><?php echo htmlspecialchars(number_format($row['total_production30'], 2)); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: // Selected regime but no data ?>
        <p class='message-info'>No final output data available for <?php echo $selectedRegime; ?>cm DBH. This could mean the simulation table is empty or the query returned no results. Please ensure the simulation has been run.</p>
    <?php endif; ?>
</section>

<?php require_once 'footer.php'; ?>