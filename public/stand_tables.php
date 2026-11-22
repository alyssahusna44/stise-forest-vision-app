<?php
// public/stand_tables.php - Displays various stand tables for a selected regime

require_once 'header.php'; // Includes common header, db_connection, HTML head/nav
require_once __DIR__ . '/../includes/db_connection.php'; // Ensure db connection is available

$conn = getDbConnection();

$cuttingRegimes = [45, 50, 55, 60, 65]; // Define available cutting regimes
$selectedRegime = ''; // Variable to hold the currently selected cutting regime

// Handle display selection from the dropdown
if (isset($_POST['select_regime']) && in_array($_POST['select_regime'], $cuttingRegimes)) {
    $selectedRegime = $_POST['select_regime'];
} else if (isset($_GET['regime']) && in_array($_GET['regime'], $cuttingRegimes)) {
    // Fallback for direct links from dashboard, if any exist that still uses GET
    $selectedRegime = $_GET['regime'];
}


$tableNameToDisplay = '';

// Initialize data arrays for stand tables
$prodData = [];
$damageData = [];
$remainData = [];
$general30Data = [];
$prod30Data = [];

// --- Fetch Diameter Classes ---
$diameterClasses = [];
$sqlDiameterClasses = "SELECT id, class_name, min_diameter, max_diameter FROM diameter_classes ORDER BY id ASC";
$resultDiameterClasses = $conn->query($sqlDiameterClasses);

if ($resultDiameterClasses) {
    while ($row = $resultDiameterClasses->fetch_assoc()) {
        $diameterClasses[] = $row;
    }
    $resultDiameterClasses->free();
} else {
    error_log("Error fetching diameter classes: " . $conn->error . ". Please ensure 'diameter_classes' table exists and is populated.");
    $diameterClasses = []; // Ensure it's empty to prevent errors in loops
}

// --- Fetch Species Groups ---
$speciesGroupsForTable = [];
$speciesGroupMapping = [];

$sqlSpeciesGroupNames = "SELECT species_group, speciesgroup_name FROM species_group_names ORDER BY species_group ASC";
$resultSpeciesGroupNames = $conn->query($sqlSpeciesGroupNames);
if ($resultSpeciesGroupNames) {
    while ($row = $resultSpeciesGroupNames->fetch_assoc()) {
        $speciesGroupMapping[$row['species_group']] = $row['speciesgroup_name'];
    }
    $resultSpeciesGroupNames->free();
} else {
    $speciesGroupMapping = [
        1 => 'Mersawa', 2 => 'Keruing', 3 => 'Dip Commercial',
        4 => 'Dip Non Commercial', 5 => 'NonDip Commercial',
        6 => 'NonDip Non Commercial', 7 => 'Others'
    ];
    error_log("Warning: Could not fetch species group names from 'species_group_names' table. Using default names. " . $conn->error);
}


// --- Helper function to process raw data for stand tables ---
function processStandTableData($rawResult, $db_connection, $volumeColumn, $diameterColumn, $diameterClasses)
{
    $processedData = [];
    if (!$rawResult) {
        error_log("Error in processStandTableData: " . $db_connection->error);
        return $processedData;
    }

    while ($row = $rawResult->fetch_assoc()) {
        $speciesGroupId = $row['species_group'];
        // The diameter is now expected to be in centimeters from the SQL query
        $diameterCm = (float)($row[$diameterColumn] ?? 0.0);

        $diameterClassId = null;

        foreach ($diameterClasses as $dc) {
            $min = (float) $dc['min_diameter'];
            $max = ($dc['max_diameter'] !== null) ? (float) $dc['max_diameter'] : null;

            if ($max === null) {
                if ($diameterCm >= $min) {
                    $diameterClassId = $dc['id'];
                    break;
                }
            } else {
                if ($diameterCm >= $min && $diameterCm < $max) {
                    $diameterClassId = $dc['id'];
                    break;
                }
            }
        }

        if ($diameterClassId !== null) {
            if (!isset($processedData[$speciesGroupId][$diameterClassId])) {
                $processedData[$speciesGroupId][$diameterClassId] = ['no' => 0, 'vol' => 0.0];
            }
            $processedData[$speciesGroupId][$diameterClassId]['no']++;
            $vol = (float)($row[$volumeColumn] ?? 0.0);
            $processedData[$speciesGroupId][$diameterClassId]['vol'] += $vol;
        }
    }
    return $processedData;
}


// --- Function to generate a single stand table HTML ---
function generateStandTable($title, $data, $speciesGroupsForTable, $diameterClasses)
{
    echo "<h2>" . htmlspecialchars($title) . "</h2>";
    if (empty($data) || empty($speciesGroupsForTable) || empty($diameterClasses)) {
        echo "<p class='message-info'>No data or configuration available for " . htmlspecialchars($title) . ". This could mean no trees matched the criteria (e.g., status, non-NULL values) for this table, or a database setup issue, or diameters are out of defined ranges.</p>";
        return;
    }

    echo "<div class='table-responsive stand-table-container'>";
    echo "<table class='stand-table'>";
    echo "<thead><tr>";
    echo "<th>Species Group</th>";
    foreach ($diameterClasses as $dc) {
        echo "<th colspan='2'>" . htmlspecialchars($dc['class_name']) . "</th>";
    }
    echo "<th colspan='2'>Total</th>";
    echo "</tr>";
    echo "<tr><th></th>";
    foreach ($diameterClasses as $dc) {
        echo "<th>No</th><th>Vol (m³)</th>";
    }
    echo "<th>No</th><th>Vol (m³)</th>";
    echo "</tr></thead>";
    echo "<tbody>";

    $overallTotalNo = 0;
    $overallTotalVol = 0.0;
    $columnTotals = [];

    foreach ($diameterClasses as $dc) {
        $columnTotals[$dc['id']]['no'] = 0;
        $columnTotals[$dc['id']]['vol'] = 0.0;
    }

    foreach ($speciesGroupsForTable as $sg) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($sg['name']) . "</td>";
        $rowTotalNo = 0;
        $rowTotalVol = 0.0;

        foreach ($diameterClasses as $dc) {
            $count = $data[$sg['id']][$dc['id']]['no'] ?? 0;
            $volume = $data[$sg['id']][$dc['id']]['vol'] ?? 0.0;

            echo "<td>" . number_format($count) . "</td>";
            echo "<td>" . number_format($volume, 2) . "</td>";

            $rowTotalNo += $count;
            $rowTotalVol += $volume;

            $columnTotals[$dc['id']]['no'] += $count;
            $columnTotals[$dc['id']]['vol'] += $volume;
        }
        echo "<td>" . number_format($rowTotalNo) . "</td>";
        echo "<td>" . number_format($rowTotalVol, 2) . "</td>";
        echo "</tr>";

        $overallTotalNo += $rowTotalNo;
        $overallTotalVol += $rowTotalVol;
    }

    echo "<tr class='total-row'>";
    echo "<td>Total</td>";
    foreach ($diameterClasses as $dc) {
        echo "<td>" . number_format($columnTotals[$dc['id']]['no']) . "</td>";
        echo "<td>" . number_format($columnTotals[$dc['id']]['vol'], 2) . "</td>";
    }
    echo "<td>" . number_format($overallTotalNo) . "</td>";
    echo "<td>" . number_format($overallTotalVol, 2) . "</td>";
    echo "</tr>";

    echo "</tbody>";
    echo "</table>";
    echo "</div>";
}


// Only attempt to fetch data if a regime is selected
if ($selectedRegime) {
    $tableNameToDisplay = "simulation_results_" . $selectedRegime;

    // Check if the table exists for the selected regime
    $checkTableExists = $conn->query("SHOW TABLES LIKE '$tableNameToDisplay'");

    if ($checkTableExists && $checkTableExists->num_rows > 0) {
        $sqlDistinctSpeciesGroups = "SELECT DISTINCT species_group FROM " . $tableNameToDisplay . " ORDER BY species_group ASC";
        $resultDistinctSpeciesGroups = $conn->query($sqlDistinctSpeciesGroups);

        if ($resultDistinctSpeciesGroups) {
            while ($row = $resultDistinctSpeciesGroups->fetch_assoc()) {
                $group_id = $row['species_group'];
                $speciesGroupsForTable[] = [
                    'id' => $group_id,
                    'name' => $speciesGroupMapping[$group_id] ?? 'Group ' . $group_id
                ];
            }
            $resultDistinctSpeciesGroups->free();
        } else {
            echo "<p class='message-warning'>Warning: Could not fetch distinct species groups from $tableNameToDisplay. " . $conn->error . "</p>";
        }

        // --- SQL Queries for each Stand Table Criteria ---

        // Prod Stand Table (Trees with status 'cut' or 'victim')
        // Convert diameter from meters to centimeters (diameter * 100)
        $sqlProd = "
            SELECT
                species_group,
                (diameter * 100) AS diameter_cm,
                prod
            FROM
                $tableNameToDisplay
            WHERE
                status IN ('cut', 'victim')
                AND prod IS NOT NULL
                AND diameter IS NOT NULL
        ";
        $resultProd = $conn->query($sqlProd);
        $prodData = processStandTableData($resultProd, $conn, 'prod', 'diameter_cm', $diameterClasses); // Use 'diameter_cm'
        if ($resultProd) $resultProd->free();


        // Damage Stand Table (Trees with status 'victim') - Uses damage_stem
        // Convert diameter from meters to centimeters (diameter * 100)
        $sqlDamage = "
            SELECT
                species_group,
                (diameter * 100) AS diameter_cm,
                damage_stem
            FROM
                $tableNameToDisplay
            WHERE
                status = 'victim'
                AND damage_stem IS NOT NULL
                AND diameter IS NOT NULL
        ";
        $resultDamage = $conn->query($sqlDamage);
        $damageData = processStandTableData($resultDamage, $conn, 'damage_stem', 'diameter_cm', $diameterClasses); // Use 'diameter_cm'
        if ($resultDamage) $resultDamage->free();


        // Remain Stand Table (Trees with status 'keep' or 'stand')
        // Convert diameter from meters to centimeters (diameter * 100)
        $sqlRemain = "
            SELECT
                species_group,
                (diameter * 100) AS diameter_cm,
                volume
            FROM
                $tableNameToDisplay
            WHERE
                status IN ('keep', 'stand')
                AND volume IS NOT NULL
                AND diameter IS NOT NULL
        ";
        $resultRemain = $conn->query($sqlRemain);
        $remainData = processStandTableData($resultRemain, $conn, 'volume', 'diameter_cm', $diameterClasses); // Use 'diameter_cm'
        if ($resultRemain) $resultRemain->free();


        // General30 Stand Table (All trees, using volume30 and diameter30)
        // Assume diameter30 is already in CM or its values are large enough to fit existing classes
        $sqlGeneral30 = "
            SELECT
                species_group,
                diameter30,
                volume30
            FROM
                $tableNameToDisplay
            WHERE
                diameter30 IS NOT NULL
                AND volume30 IS NOT NULL
        ";
        $resultGeneral30 = $conn->query($sqlGeneral30);
        $general30Data = processStandTableData($resultGeneral30, $conn, 'volume30', 'diameter30', $diameterClasses);
        if ($resultGeneral30) $resultGeneral30->free();


        // Prod30 Stand Table (All trees, using prod30 and diameter30)
        // Assume diameter30 is already in CM or its values are large enough to fit existing classes
        $sqlProd30 = "
            SELECT
                species_group,
                diameter30,
                prod30
            FROM
                $tableNameToDisplay
            WHERE
                prod30 IS NOT NULL
                AND diameter30 IS NOT NULL
        ";
        $resultProd30 = $conn->query($sqlProd30);
        $prod30Data = processStandTableData($resultProd30, $conn, 'prod30', 'diameter30', $diameterClasses);
        if ($resultProd30) $resultProd30->free();

    } else {
        echo "<p class='message-info'>No simulation results table found for $selectedRegime cm DBH. Please run the simulation first from the Forest Generation page.</p>";
    }
} else {
    // If no regime is selected initially or via POST/GET
}

$conn->close();
?>

<section class="home-content">
    <h1>Stand Tables <?php echo $selectedRegime ? "for " . $selectedRegime . "cm DBH Regime" : ""; ?></h1>

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
        <p class='message-info'>Please select a cutting regime from the dropdown above to view the Stand Tables.</p>
    <?php elseif ($selectedRegime && !empty($diameterClasses) && !empty($speciesGroupsForTable)): ?>
        <?php
        generateStandTable("Production Stand Table", $prodData, $speciesGroupsForTable, $diameterClasses);
        generateStandTable("Damage Stand Table", $damageData, $speciesGroupsForTable, $diameterClasses);
        generateStandTable("Remainder Stand Table", $remainData, $speciesGroupsForTable, $diameterClasses);
        generateStandTable("30-Year General Projection Stand Table", $general30Data, $speciesGroupsForTable, $diameterClasses);
        generateStandTable("30-Year Production Projection Stand Table", $prod30Data, $speciesGroupsForTable, $diameterClasses);
        ?>
    <?php else: // Selected regime but no data or configuration ?>
        <p class='message-info'>Cannot generate stand tables for <?php echo $selectedRegime; ?>cm DBH. This could mean a simulation has not been run, or there is no data for the selected regime matching the table criteria (e.g., specific tree statuses or non-NULL values). Ensure diameter classes are configured, species groups are present in the simulation data, and a simulation has been run successfully for this regime.</p>
    <?php endif; ?>
</section>

<?php require_once 'footer.php'; ?>