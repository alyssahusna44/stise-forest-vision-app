<?php
// includes/ForestSimulator.php

require_once 'db_connection.php';
require_once 'SpeciesManager.php'; // Needed to fetch species data if not fully loaded with base_forest

class ForestSimulator
{
    private $conn;
    private $speciesManager;
    private $diameterIncrements;
    private $fellingSpeciesGroups; // Species groups considered for felling

    public function __construct(mysqli $conn, SpeciesManager $speciesManager)
    {
        $this->conn = $conn;
        $this->speciesManager = $speciesManager;
        $this->diameterIncrements = [
            '5-15' => 0.4,
            '15-30' => 0.6,
            '30-45' => 0.5,
            '45-60' => 0.5,
            '60+' => 0.7
        ];
        $this->fellingSpeciesGroups = [1, 2, 3, 5]; // Species Group IDs 1,2,3,5 are "Cut or Fell"
    }

    /**
     * Runs the full simulation for a given cutting regime.
     * @param int $cuttingRegimeCm The DBH threshold for felling (e.g., 45, 50, 55, 60, 65 cm).
     * @return bool True on success, false on failure.
     */
    public function runSimulation($cuttingRegimeCm)
    {
        $tableName = "simulation_results_" . $cuttingRegimeCm;

        // 1. Drop and create the simulation results table for a clean run
        $this->conn->query("DROP TABLE IF EXISTS " . $tableName);
        $createTableSql = "CREATE TABLE " . $tableName . " (
            tree_id VARCHAR(20) PRIMARY KEY,
            block_i INT,
            block_j INT,
            coord_x INT,
            coord_y INT,
            species_id INT,
            species_name VARCHAR(100),
            species_group INT,
            diameter DECIMAL(5,2),
            diameter_class VARCHAR(10),
            height DECIMAL(5,2),
            is_dipterocarp BOOLEAN,
            volume DECIMAL(10,4),
            status VARCHAR(10),
            prod DECIMAL(10,4),
            cut_angle DECIMAL(5,2),
            damage_stem DECIMAL(10,4),
            damage_crown DECIMAL(10,4),
            diameter30 DECIMAL(5,2),
            volume30 DECIMAL(10,4),
            prod30 DECIMAL(10,4)
        );";
        if (!$this->conn->query($createTableSql)) {
            echo "Error creating simulation table $tableName: " . $this->conn->error . "<br>";
            return false;
        }
        echo "Created simulation table: " . $tableName . "<br>";

        // 2. Fetch all trees from base_forest
        $allTrees = $this->getAllTreesFromBaseForest();
        if (empty($allTrees)) {
            echo "No trees found in base_forest. Please generate forest first.<br>";
            return false;
        }
        echo "Processing " . count($allTrees) . " trees from base_forest.<br>";

        // Group trees by block for efficient damage calculation
        $treesByBlock = [];
        foreach ($allTrees as $tree) {
            $blockKey = $tree['block_i'] . '_' . $tree['block_j'];
            $treesByBlock[$blockKey][] = $tree;
        }

        // Prepare insert statement for simulation results table
        $insertSql = "INSERT INTO " . $tableName . " (tree_id, block_i, block_j, coord_x, coord_y, species_id, species_name, species_group, diameter, diameter_class, height, is_dipterocarp, volume, status, prod, cut_angle, damage_stem, damage_crown, diameter30, volume30, prod30) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($insertSql);
        if (!$stmt) {
            die("SQL prepare failed for simulation results insert: " . $this->conn->error);
        }

        foreach ($treesByBlock as $blockKey => $blockTrees) {
            // Step A: Determine initial status (Cut/Stand) and identify potential felling trees
            $cutTreesInBlock = [];
            $processedTrees = []; // Temporarily store processed trees for this block

            foreach ($blockTrees as $tree) {
                $tree_diameter_cm = $tree['diameter'] * 100; // Convert diameter from meters to cm for felling criteria
                $tree_species_group = $tree['species_group'];

                $isFellableSpecies = in_array($tree_species_group, $this->fellingSpeciesGroups);
                $meetsDiameterCriteria = ($tree_diameter_cm > $cuttingRegimeCm);

                $tree['status'] = 'Stand'; // Default status
                $tree['prod'] = 0;
                $tree['cut_angle'] = 0; // Default
                $tree['damage_stem'] = 0;
                $tree['damage_crown'] = 0;
                $tree['diameter30'] = 0;
                $tree['volume30'] = 0;
                $tree['prod30'] = 0;

                if ($isFellableSpecies && $meetsDiameterCriteria) {
                    $tree['status'] = 'Cut';
                    $tree['prod'] = $tree['volume']; // Cut trees contribute their full volume to production
                    $cutTreesInBlock[] = $tree;
                }
                $processedTrees[$tree['tree_id']] = $tree; // Add to processed list for this block
            }

            // Step B: Simulate felling damage where EACH 'Cut' tree acts as a felling origin
            if (!empty($cutTreesInBlock)) {
                foreach ($cutTreesInBlock as $fellingOriginTree) { // Loop through ALL 'Cut' trees in this block
                    $fellingOriginTreeId = $fellingOriginTree['tree_id'];

                    $fellingX0 = $fellingOriginTree['coord_x'];
                    $fellingY0 = $fellingOriginTree['coord_y'];
                    $fellingStemHeight = $fellingOriginTree['height'];
                    $fellingCutAngle = rand(1, 360); // Each falling tree has its own random angle

                    // Update the felling origin tree itself with its chosen cut_angle
                    // Its status and prod are already 'Cut' from Step A
                    $processedTrees[$fellingOriginTreeId]['cut_angle'] = $fellingCutAngle;

                    // Iterate through ALL trees in the block to check for damage caused by THIS felling tree
                    foreach ($processedTrees as $otherTreeId => &$otherTree) { // Use & for reference to modify in place
                        // A tree cannot damage itself
                        if ($otherTreeId === $fellingOriginTreeId) {
                            continue;
                        }

                        // Only 'Stand' trees can become 'Victim' or 'Keep' due to a fall.
                        // If a tree was already made a 'Victim' or 'Keep' by a previous falling tree in the same block,
                        // its status remains as such (it's not re-evaluated or 'undamaged').
                        if ($otherTree['status'] === 'Stand') {
                            $isDamaged = $this->isTreeDamaged(
                                $otherTree['coord_x'],
                                $otherTree['coord_y'],
                                $fellingX0,
                                $fellingY0,
                                $fellingStemHeight,
                                $fellingCutAngle
                            );

                            if ($isDamaged) {
                                // Randomly decide between stem (fatal) or crown (survivable) damage
                                if (rand(0, 1) === 0) { // 0 for Victim (stem damage), 1 for Keep (crown damage)
                                    // Victim (fatal) damage
                                    $otherTree['status'] = 'Victim';
                                    $otherTree['damage_stem'] = $otherTree['volume']; // 100% volume affected
                                    $otherTree['damage_crown'] = 0; // No crown damage
                                    $otherTree['prod'] = $otherTree['volume']; // Victim trees also contribute to production (salvage)
                                } else {
                                    // Keep (survivable) damage
                                    $otherTree['status'] = 'Keep';
                                    $otherTree['damage_stem'] = 0;
                                    $otherTree['damage_crown'] = $otherTree['volume'] * 0.3; // Example: 30% crown damage
                                    $otherTree['prod'] = 0; // Keep trees don't contribute to current production
                                }
                            }
                        }
                    }
                    unset($otherTree); // Unset reference to avoid unexpected behavior
                } // End of foreach ($cutTreesInBlock as $fellingOriginTree)
            }


            // Step C: Calculate 30-year projections for 'Stand' and 'Keep' trees
            foreach ($processedTrees as &$tree) { // Use & for reference
                if ($tree['status'] === 'Stand' || $tree['status'] === 'Keep') {
                    $initialDiameterCm = $tree['diameter'] * 100; // Convert to CM for projection logic
                    $tree['diameter30'] = $this->calculateDiameter30($initialDiameterCm);
                    // Convert diameter30 back to meters for volume calculation
                    $diameter30_m = $tree['diameter30'] / 100;
                    $tree['volume30'] = $this->calculateVolume(
                        $tree['is_dipterocarp'],
                        $diameter30_m,
                        $tree['height'], // Assuming height for projection stays same or needs a separate growth model
                        $tree['diameter30'] // Pass projected diameter in CM for formula conditions
                    );
                    // Prod30 is volume30 for stand/keep trees at projection point
                    $tree['prod30'] = $tree['volume30'];
                } else {
                    // For Cut/Victim trees, projections are not applicable or 0
                    $tree['diameter30'] = 0;
                    $tree['volume30'] = 0;
                    $tree['prod30'] = 0;
                }
            }
            unset($tree); // Unset reference

            // Step D: Insert processed trees into the simulation results table
            foreach ($processedTrees as $tree) {
                // Ensure correct data types for binding parameters
                $diameter_m = (float) $tree['diameter'];
                $height_m = (float) $tree['height'];
                $volume_m3 = (float) $tree['volume'];
                $prod_m3 = (float) $tree['prod'];
                $cut_angle_deg = (float) $tree['cut_angle'];
                $damage_stem_m3 = (float) $tree['damage_stem'];
                $damage_crown_m3 = (float) $tree['damage_crown'];
                $diameter30_cm = (float) $tree['diameter30'];
                $volume30_m3 = (float) $tree['volume30'];
                $prod30_m3 = (float) $tree['prod30'];

                $stmt->bind_param(
                    "siiiiisidsdidsddddddd", // CORRECTED BIND PARAMETER STRING (21 characters)
                    $tree['tree_id'],
                    $tree['block_i'],
                    $tree['block_j'],
                    $tree['coord_x'],
                    $tree['coord_y'],
                    $tree['species_id'],
                    $tree['species_name'],
                    $tree['species_group'],
                    $diameter_m,
                    $tree['diameter_class'],
                    $height_m,
                    $tree['is_dipterocarp'],
                    $volume_m3,
                    $tree['status'],
                    $prod_m3,
                    $cut_angle_deg,
                    $damage_stem_m3,
                    $damage_crown_m3,
                    $diameter30_cm,
                    $volume30_m3,
                    $prod30_m3
                );

                if (!$stmt->execute()) {
                    echo "Error inserting simulated tree " . $tree['tree_id'] . " into " . $tableName . ": " . $stmt->error . "<br>";
                }
            }
        }
        $stmt->close();
        echo "Simulation for cutting regime $cuttingRegimeCm cm complete!<br>";
        return true;
    }

    /**
     * Fetches all tree data from the 'base_forest' table.
     * @return array An array of associative arrays, each representing a tree.
     */
    private function getAllTreesFromBaseForest()
    {
        $trees = [];
        $result = $this->conn->query("SELECT * FROM base_forest");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $trees[] = $row;
            }
            $result->free();
        } else {
            echo "Error fetching trees from base_forest: " . $this->conn->error . "<br>";
        }
        return $trees;
    }

    /**
     * Determines if a target tree is damaged by a felling origin tree.
     * Logic based on cutting angle and spatial coordinates.
     * @param int $tree_x Target tree's x-coordinate.
     * @param int $tree_y Target tree's y-coordinate.
     * @param int $felling_x0 Felling tree's x-coordinate (origin).
     * @param int $felling_y0 Felling tree's y-coordinate (origin).
     * @param float $felling_height Felling tree's height in meters.
     * @param float $felling_angle_degrees Cutting angle in degrees (0-360).
     * @return bool True if damaged, false otherwise.
     */
    private function isTreeDamaged($tree_x, $tree_y, $felling_x0, $felling_y0, $felling_height, $felling_angle_degrees)
    {
        $x = (float) $tree_x;
        $y = (float) $tree_y;
        $X0 = (float) $felling_x0;
        $Y0 = (float) $felling_y0;
        // The "+5" constant should be added to the stem height for the damage zone size.
        // Assuming stem height is in meters, and coordinates are arbitrary units (0-99).
        // Using felling_height + 5 directly as the linear dimension for the rectangular zone.
        $DamageZoneSize = $felling_height + 5;

        // Cutting Angle 0-90 (Degrees): X0 < x < (X0+Size), Y0 < y < (Y0+Size)
        if ($felling_angle_degrees >= 0 && $felling_angle_degrees < 90) {
            if (
                $x > $X0 && $x < ($X0 + $DamageZoneSize) &&
                $y > $Y0 && $y < ($Y0 + $DamageZoneSize)
            ) {
                return true;
            }
        }
        // Cutting Angle 90-180 (Degrees): X0 < x < (X0+Size), Y0 > y > (Y0−Size)
        else if ($felling_angle_degrees >= 90 && $felling_angle_degrees < 180) {
            if (
                $x > $X0 && $x < ($X0 + $DamageZoneSize) &&
                $y < $Y0 && $y > ($Y0 - $DamageZoneSize)
            ) {
                return true;
            }
        }
        // Cutting Angle 180-270 (Degrees): X0 > x > (X0−Size), Y0 < y < (Y0+Size)
        else if ($felling_angle_degrees >= 180 && $felling_angle_degrees < 270) {
            if (
                $x < $X0 && $x > ($X0 - $DamageZoneSize) &&
                $y > $Y0 && $y < ($Y0 + $DamageZoneSize)
            ) {
                return true;
            }
        }
        // Cutting Angle 270-360 (Degrees): X0 > x > (X0−Size), Y0 > y > (Y0−Size)
        else if ($felling_angle_degrees >= 270 && $felling_angle_degrees <= 360) {
            if (
                $x < $X0 && $x > ($X0 - $DamageZoneSize) &&
                $y < $Y0 && $y > ($Y0 - $DamageZoneSize)
            ) {
                return true;
            }
        }
        return false;
    }


    /**
     * Gets the diameter increment per year based on current diameter.
     * @param float $diameterCm Current diameter in centimeters.
     * @return float Increment in cm/year.
     */
    private function getDiameterIncrement($diameterCm)
    {
        if ($diameterCm >= 5 && $diameterCm < 15)
            return 0.4;
        if ($diameterCm >= 15 && $diameterCm < 30)
            return 0.6;
        if ($diameterCm >= 30 && $diameterCm < 45)
            return 0.5;
        if ($diameterCm >= 45 && $diameterCm < 60)
            return 0.5;
        if ($diameterCm >= 60)
            return 0.7;
        return 0; // Should not happen for valid diameters
    }

    /**
     * Calculates the projected diameter after 30 years using iterative increments.
     * @param float $initialDiameterCm Initial diameter in centimeters.
     * @return float Projected diameter in centimeters.
     */
    private function calculateDiameter30($initialDiameterCm)
    {
        $currentDiameterCm = $initialDiameterCm;
        for ($year = 1; $year <= 30; $year++) {
            $increment = $this->getDiameterIncrement($currentDiameterCm);
            $currentDiameterCm += $increment;
        }
        return round($currentDiameterCm, 2); // Round to 2 decimal places for consistency
    }

    /**
     * Calculates the volume of a tree based on its species type, diameter, and height.
     * Diameter (D) must be in meters for the formulas.
     * @param bool $isDipterocarp True if the tree is a Dipterocarp, false otherwise.
     * @param float $diameterM Tree diameter at breast height (DBH) in meters.
     * @param float $heightM Tree height in meters.
     * @param float $diameterCm Original diameter in centimeters (used for class checks).
     * @return float Calculated volume in cubic meters.
     */
    private function calculateVolume($isDipterocarp, $diameterM, $heightM, $diameterCm)
    {
        $volume = 0;
        $D = (float) $diameterM;
        $L = (float) $heightM;

        if ($isDipterocarp) {
            if ($diameterCm < 15) {
                $volume = 0.022 + 3.4 * ($D * $D);
            } elseif ($diameterCm >= 15 && $diameterCm < 45) {
                $volume = -0.0971 + 9.503 * ($D * $D);
            } else { // $diameterCm >= 45cm
                $volume = 0.015 + 2.137 * ($D * $D) + 0.513 * ($D * $D) * $L;
            }
        } else { // NonDipterocarp
            if ($diameterCm < 30) {
                $volume = 0.03 + 2.8 * ($D * $D);
            } elseif ($diameterCm >= 30 && $diameterCm < 45) {
                $volume = -0.331 + 6.694 * ($D * $D);
            } else { // $diameterCm >= 45cm
                $volume = -0.0023 + 2.942 * ($D * $D) + 0.262 * ($D * $D) * $L;
            }
        }
        return round($volume, 4); // Round to 4 decimal places for consistency
    }
}
?>