<?php
// public/spatial_visualisation.php - Displays geographical distribution of trees

require_once 'header.php'; // Includes common header, db_connection, HTML head/nav
require_once __DIR__ . '/../includes/db_connection.php';

$conn = getDbConnection();

$cuttingRegimes = [45, 50, 55, 60, 65]; // Define available cutting regimes
$selectedRegime = ''; // Variable to hold the currently selected cutting regime
$selectedBlockI = ''; // Variable to hold the currently selected block_i
$selectedBlockJ = ''; // Variable to hold the currently selected block_j

// Handle display selection from the dropdowns
if (isset($_POST['select_regime']) && in_array($_POST['select_regime'], $cuttingRegimes)) {
    $selectedRegime = $_POST['select_regime'];
    // Only attempt to get block selection if a regime is chosen
    if (isset($_POST['select_block_i'])) { // Check if the block dropdown was submitted
        $selectedBlockI = $_POST['select_block_i'];
    }
    if (isset($_POST['select_block_j'])) { // Check if the block dropdown was submitted
        $selectedBlockJ = $_POST['select_block_j'];
    }
} else if (isset($_GET['regime']) && in_array($_GET['regime'], $cuttingRegimes)) {
    $selectedRegime = $_GET['regime']; // For direct links if needed
    if (isset($_GET['block_i'])) {
        $selectedBlockI = $_GET['block_i'];
    }
    if (isset($_GET['block_j'])) {
        $selectedBlockJ = $_GET['block_j'];
    }
}

$treeData = [];
$speciesGroupNames = [];

// Fetch species group names for mapping
$resultSpeciesGroupNames = $conn->query("SELECT species_group, speciesgroup_name FROM species_group_names ORDER BY species_group ASC");
if ($resultSpeciesGroupNames) {
    while ($row = $resultSpeciesGroupNames->fetch_assoc()) {
        $speciesGroupNames[$row['species_group']] = $row['speciesgroup_name'];
    }
    $resultSpeciesGroupNames->free();
} else {
    error_log("Error fetching species group names: " . $conn->error);
}

// Variables to store min/max block indices for dropdown population
$minBlockI = null;
$maxBlockI = null;
$minBlockJ = null;
$maxBlockJ = null;

// Only attempt to fetch tree data if a regime is selected
if ($selectedRegime) {
    $tableNameToDisplay = "simulation_results_" . $selectedRegime;

    // Check if the table exists for the selected regime
    $checkTableExists = $conn->query("SHOW TABLES LIKE '$tableNameToDisplay'");

    if ($checkTableExists && $checkTableExists->num_rows > 0) {
        // Fetch min/max block_i and block_j values from the selected table
        $sqlBlockRanges = "
            SELECT
                MIN(block_i) AS min_block_i, MAX(block_i) AS max_block_i,
                MIN(block_j) AS min_block_j, MAX(block_j) AS max_block_j
            FROM
                $tableNameToDisplay
        ";
        $resultBlockRanges = $conn->query($sqlBlockRanges);
        if ($resultBlockRanges && $row = $resultBlockRanges->fetch_assoc()) {
            $minBlockI = $row['min_block_i'];
            $maxBlockI = $row['max_block_i'];
            $minBlockJ = $row['min_block_j'];
            $maxBlockJ = $row['max_block_j'];
            $resultBlockRanges->free();

            // If no specific block is selected, default to the first available block
            // This is crucial now that "Whole Forest" is removed.
            if ($selectedBlockI === '' && $minBlockI !== null) {
                $selectedBlockI = $minBlockI;
            }
            if ($selectedBlockJ === '' && $minBlockJ !== null) {
                $selectedBlockJ = $minBlockJ;
            }
        }

        // Fetch detailed tree data for the spatial plot
        // Data is now *always* fetched for a specific block
        // Only fetch if a block is actually selected (which it should be by now)
        if ($selectedBlockI !== '' && $selectedBlockJ !== '') {
            $sqlTreeData = "
                SELECT
                    tree_id,
                    block_i,
                    block_j,
                    coord_x,
                    coord_y,
                    species_id,
                    species_name,
                    species_group,
                    diameter,
                    status
                FROM
                    $tableNameToDisplay
                WHERE
                    block_i = " . intval($selectedBlockI) . " AND
                    block_j = " . intval($selectedBlockJ) . "
                ORDER BY block_i, block_j, tree_id ASC
            ";
            
            $resultTreeData = $conn->query($sqlTreeData);

            if ($resultTreeData) {
                while ($row = $resultTreeData->fetch_assoc()) {
                    $treeData[] = $row;
                }
                $resultTreeData->free();
            } else {
                echo "<p class='message-error'>Error fetching tree data from $tableNameToDisplay for Block (" . htmlspecialchars($selectedBlockI) . ", " . htmlspecialchars($selectedBlockJ) . "): " . $conn->error . "</p>";
            }
        }
    } else {
        echo "<p class='message-info'>No simulation results table found for " . htmlspecialchars($selectedRegime) . " cm DBH. Please run the simulation first from the Forest Generation page.</p>";
    }
}

$conn->close();
?>

<section class="home-content">
    <h1>Forest Spatial Distribution Map
        <?php
        $chartTitleSuffix = "";
        if ($selectedRegime) {
            $chartTitleSuffix .= " for " . htmlspecialchars($selectedRegime) . "cm DBH Regime";
        }
        // Now it will always show specific block coordinates as there's no "Whole Forest" option
        if ($selectedBlockI !== '' && $selectedBlockJ !== '') {
            $chartTitleSuffix .= " - Block (" . htmlspecialchars($selectedBlockI) . ", " . htmlspecialchars($selectedBlockJ) . ")";
        }
        echo $chartTitleSuffix;
        ?>
    </h1>

    <div class="simulation-controls">
        <form method="POST" class="display-selection-form">
            <label for="regime_select">Select Simulation Regime:</label>
            <select name="select_regime" id="regime_select" onchange="this.form.submit()">
                <option value="">-- Select Regime --</option>
                <?php foreach ($cuttingRegimes as $regime): ?>
                    <option value="<?php echo htmlspecialchars($regime); ?>" <?php echo ((string)$selectedRegime === (string)$regime) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($regime); ?>cm DBH
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if ($selectedRegime && $minBlockI !== null && $maxBlockI !== null && $minBlockJ !== null && $maxBlockJ !== null): // Show block selection only if a regime is selected and block ranges are found ?>
                <label for="block_i_select">Select Block I:</label>
                <select name="select_block_i" id="block_i_select" onchange="this.form.submit()">
                    <?php for ($i = $minBlockI; $i <= $maxBlockI; $i++): ?>
                        <option value="<?php echo htmlspecialchars($i); ?>" <?php echo ((string)$selectedBlockI === (string)$i) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($i); // Display only the number ?>
                        </option>
                    <?php endfor; ?>
                </select>

                <label for="block_j_select">Select Block J:</label>
                <select name="select_block_j" id="block_j_select" onchange="this.form.submit()">
                    <?php for ($j = $minBlockJ; $j <= $maxBlockJ; $j++): ?>
                        <option value="<?php echo htmlspecialchars($j); ?>" <?php echo ((string)$selectedBlockJ === (string)$j) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($j); // Display only the number ?>
                        </option>
                    <?php endfor; ?>
                </select>
            <?php endif; ?>
        </form>
    </div>

    <?php if (!$selectedRegime): ?>
        <p class='message-info'>Please select a cutting regime from the dropdown above to view the Spatial Distribution Map.</p>
    <?php elseif ($selectedRegime && ($selectedBlockI === '' || $selectedBlockJ === '')): ?>
        <p class='message-info'>Please select both Block I and Block J from the dropdowns above to view the Spatial Distribution Map for a specific block.</p>
    <?php elseif ($selectedRegime && !empty($treeData)): ?>
        <div class="chart-container" style="position: relative; height: 70vh; width: 80vw; margin: auto;">
            <canvas id="spatialChart"></canvas>
        </div>
        <p class="chart-info">
            <strong>Interact:</strong> Use mouse wheel to zoom, Ctrl + click and drag to pan.
            <br>
            <strong>Point Status Legend:</strong>
            <span style="color: blue;">●</span> Keep,
            <span style="color: green;">■</span> Stand,
            <span style="color: red;">★</span> Cut,
            <span style="color: orange;">▲</span> Victim
            <br>
            (Point shape represents tree status; color represents species group)
        </p>

        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-decimation@latest/dist/chartjs-plugin-decimation.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@1.2.0/dist/chartjs-plugin-zoom.min.js"></script>


        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const treeData = <?php echo json_encode($treeData); ?>;
                const speciesGroupNames = <?php echo json_encode($speciesGroupNames); ?>;
                const selectedRegime = <?php echo json_encode($selectedRegime); ?>;
                const selectedBlockI = <?php echo json_encode($selectedBlockI); ?>;
                const selectedBlockJ = <?php echo json_encode($selectedBlockJ); ?>;

                // Define a color palette for species groups (adjust if you have many more groups)
                const colorPalette = [
                    'rgba(63, 191, 63, 0.7)',  // Green
                    'rgba(255, 99, 132, 0.7)', // Red
                    'rgba(54, 162, 235, 0.7)', // Blue
                    'rgba(255, 206, 86, 0.7)', // Yellow
                    'rgba(153, 102, 255, 0.7)',// Purple
                    'rgba(255, 159, 64, 0.7)', // Orange
                    'rgba(0, 204, 204, 0.7)',  // Cyan
                    'rgba(204, 0, 102, 0.7)',  // Pink
                    'rgba(102, 51, 0, 0.7)',   // Brown
                    'rgba(128, 128, 128, 0.7)' // Gray
                ];

                // Map status to Chart.js point style
                const statusPointStyles = {
                    'Cut': 'star',      // For harvested trees
                    'Victim': 'triangle', // For damaged trees
                    'Keep': 'circle',   // For trees to be kept
                    'Stand': 'rect',    // For untouched standing trees
                };

                // Define fixed radius for all points to de-emphasize diameter
                const fixedPointRadius = 4; // You can adjust this value as needed
                const fixedPointHoverRadius = 6; // Slightly larger for hover effect

                // Organize data by species group
                const datasetsMap = new Map();
                let colorIndex = 0;

                treeData.forEach(tree => {
                    if (!datasetsMap.has(tree.species_group)) {
                        const groupName = speciesGroupNames[tree.species_group] || `Group ${tree.species_group}`;
                        datasetsMap.set(tree.species_group, {
                            label: groupName,
                            data: [],
                            backgroundColor: colorPalette[colorIndex % colorPalette.length],
                            borderColor: colorPalette[colorIndex % colorPalette.length].replace('0.7', '1'), // Solid border for clarity
                            borderWidth: 1,
                            pointStyle: [], // Individual point style will be set
                            radius: [],     // Individual radius will be set
                            pointHoverRadius: [],
                        });
                        colorIndex++;
                    }

                    const dataset = datasetsMap.get(tree.species_group);
                    
                    // Add point data for Chart.js scatter plot
                    dataset.data.push({
                        x: tree.coord_x, // Coordinates remain in meters
                        y: tree.coord_y, // Coordinates remain in meters
                        tree_id: tree.tree_id,
                        block_i: tree.block_i, // Add block_i to data
                        block_j: tree.block_j, // Add block_j to data
                        diameter: tree.diameter,
                        status: tree.status,
                        species_name: tree.species_name,
                        species_group_name: speciesGroupNames[tree.species_group] || `Group ${tree.species_group}`
                    });

                    // Set individual point style and fixed radius
                    const pointStyle = statusPointStyles[tree.status] || 'circle'; // Fallback to circle if status not found
                    dataset.pointStyle.push(pointStyle);
                    dataset.radius.push(fixedPointRadius);
                    dataset.pointHoverRadius.push(fixedPointHoverRadius);
                });

                const datasets = Array.from(datasetsMap.values());

                const ctx = document.getElementById('spatialChart').getContext('2d');
                const spatialChart = new Chart(ctx, {
                    type: 'scatter',
                    data: {
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: `Tree Spatial Distribution (${selectedRegime}cm DBH Regime)` +
                                      ` - Block (${selectedBlockI}, ${selectedBlockJ})`, // Title always shows specific block
                                font: {
                                    size: 18
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const tree = context.raw;
                                        return [
                                            `ID: ${tree.tree_id}`,
                                            `Species: ${tree.species_name} (${tree.species_group_name})`,
                                            `DBH: ${tree.diameter.toFixed(2)} cm`, // Keeping DBH in tooltip for completeness
                                            `Status: ${tree.status}`,
                                            `Block: (${tree.block_i}, ${tree.block_j})`,
                                            `Coords: (${tree.x.toFixed(2)} m, ${tree.y.toFixed(2)} m)` // Display in meters
                                        ];
                                    }
                                }
                            },
                            legend: {
                                display: true,
                                position: 'right',
                                labels: {
                                    boxWidth: 20,
                                    padding: 10,
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            zoom: {
                                pan: {
                                    enabled: true,
                                    mode: 'xy',
                                    modifierKey: 'ctrl',
                                },
                                zoom: {
                                    wheel: {
                                        enabled: true,
                                    },
                                    pinch: {
                                        enabled: true
                                    },
                                    mode: 'xy',
                                },
                                limits: {
                                    // Optional: You can define min/max zoom levels or coordinate ranges here
                                }
                            },
                            decimation: {
                                enabled: true,
                                algorithm: 'lttb',
                                samples: 500
                            }
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'X-Coordinate (m)' // Axis title in meters
                                },
                                beginAtZero: true,
                                // For 100m blocks, min and max are directly calculated from block index in meters
                                min: parseInt(selectedBlockI) * 100,
                                max: (parseInt(selectedBlockI) + 1) * 100,
                                grid: {
                                    offset: false,
                                    color: 'rgba(0, 0, 0, 0.1)',
                                    lineWidth: 0.5
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Y-Coordinate (m)' // Axis title in meters
                                },
                                beginAtZero: true,
                                // For 100m blocks, min and max are directly calculated from block index in meters
                                min: parseInt(selectedBlockJ) * 100,
                                max: (parseInt(selectedBlockJ) + 1) * 100,
                                grid: {
                                    offset: false,
                                    color: 'rgba(0, 0, 0, 0.1)',
                                    lineWidth: 0.5
                                }
                            }
                        },
                        onClick: function(event, activeElements) {
                            if (activeElements.length > 0) {
                                const clickedElement = activeElements[0];
                                const datasetIndex = clickedElement.datasetIndex;
                                const index = clickedElement.index;
                                const treeDataPoint = spatialChart.data.datasets[datasetIndex].data[index];

                                // Display tree information using an alert box
                                alert(`Clicked Tree ID: ${treeDataPoint.tree_id}\nSpecies: ${treeDataPoint.species_name}\nDBH: ${treeDataPoint.diameter.toFixed(2)} cm\nStatus: ${treeDataPoint.status}\nBlock: (${treeDataPoint.block_i}, ${treeDataPoint.block_j})\nCoords: (${treeDataPoint.x.toFixed(2)} m, ${treeDataPoint.y.toFixed(2)} m)`);
                            }
                        }
                    }
                });
            });
        </script>
    <?php else: ?>
        <p class='message-info'>No spatial distribution data available for Block (<?php echo htmlspecialchars($selectedBlockI); ?>, <?php echo htmlspecialchars($selectedBlockJ); ?>) for <?php echo htmlspecialchars($selectedRegime); ?>cm DBH. Please ensure the simulation has been run and data exists for the selected block.</p>
    <?php endif; ?>
</section>

<?php require_once 'footer.php'; ?>