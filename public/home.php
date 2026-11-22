<?php
// home.php - Landing page for Forest Vision (Forest Simulator)
require_once 'header.php';
?>

<section class="home-content">
    <h1>Welcome to Forest Vision: Sustainable Forest Management Simulator</h1>

    <div class="home-intro">
        <p>
            <strong>Forest Vision</strong> is a web application created to support sustainable forestry practices and long-term planning, particularly for tropical forest ecosystems. It provides a powerful suite of tools to simulate forest dynamics, assess the immediate and future impacts of various harvesting operations, and visualize critical data for informed decision-making.
        </p>
    </div>

    <div class="app-explanation-section">
        <h2>🔍 How Forest Vision Works: Your Simulation Journey</h2>
        <p>
            Our simulator guides you through the entire process of forest management analysis, from initial setup to detailed insights. Each step corresponds to a specific area of the application:
        </p>

        <div class="feature-subsection">
            <h3>🌱 Step 1: Setting the Stage - Species Management & Base Forest Generation</h3>
            <p>
                The foundation of any simulation starts on the <a href="forest_generation.php"><strong>Forest Generation</strong></a> page. Here, you lay the groundwork:
                <ul>
                    <li><strong>Species Data Import:</strong> Establish the ecological backbone of your simulation by importing diverse tree species characteristics into the database.</li>
                    <li><strong>Base Forest Creation:</strong> Generate a realistic, virtual forest stand with individual trees distributed across blocks, mirroring the complexities of a natural ecosystem. This initial forest serves as your untouched baseline for all subsequent simulations.</li>
                </ul>
            </p>
        </div>

        <div class="feature-subsection">
            <h3>⚡ Step 2: Running Simulations - Defining Cutting Regimes & Impacts</h3>
            <p>
                Once your base forest is ready, the core analytical engine comes to life on the same <a href="forest_generation.php"><strong>Forest Generation</strong></a> page. This is where you define and execute your management strategies:
                <ul>
                    <li><strong>Select DBH Thresholds:</strong> Test various cutting regimes (e.g., harvesting trees only above a specific Diameter at Breast Height (DBH) like 45cm, 50cm, or 60cm).</li>
                    <li><strong>Impact Assessment:</strong> For every tree, the simulator determines its fate based on the chosen threshold. It calculates whether a tree was 'cut', became a 'victim' of collateral damage during logging, was 'kept' for future harvest, or remained 'standing' undisturbed. Production volume and damage incurred are also meticulously recorded.</li>
                </ul>
                The detailed results for each regime are stored in dedicated tables for comprehensive analysis.
            </p>
        </div>

        <div class="feature-subsection">
            <h3>📊 Step 3: Analyzing the Data - Detailed & Summarized Results</h3>
            <p>
                After simulations are complete, explore the data on different pages to gain insights:
                <ul>
                    <li><strong>Individual Tree Results:</strong> On the <a href="forest_generation.php"><strong>Forest Generation</strong></a> page, you can paginate through the detailed status and metrics for every tree within a chosen simulated forest.</li>
                    <li><strong>Aggregated Final Output:</strong> The <a href="final_output.php"><strong>Final Output Table</strong></a> provides a summarized overview of the simulation's results, grouped by species. This includes crucial totals for current production, volume of damaged trees, remaining forest volume, and vital long-term projections.</li>
                    <li><strong>Stand Tables:</strong> Group trees by species and specific metrics on the <a href="stand_tables.php"><strong>Stand Tables</strong></a> page, allowing for deeper analysis of forest structure and composition.</li>
                </ul>
            </p>
        </div>

        <div class="feature-subsection">
            <h3>📈 Step 4: Visualizing Impacts - Histograms & Future Projections</h3>
            <p>
                For a more intuitive and comparative understanding of the forest's structure and future, head to the <a href="visualisation.php"><strong>Visualisation</strong></a> page:
                <ul>
                    <li><strong>Current Diameter Distribution:</strong> View interactive histograms illustrating the diameter distribution of the forest immediately after a specific cutting operation, revealing the new forest structure.</li>
                    <li><strong>30-Year Future Projection:</strong> Crucially, visualize the predicted diameter distribution of the forest 30 years into the future. This projection accounts for the growth of remaining trees, offering a powerful glimpse into the long-term health and productivity of the stand under your chosen regime.</li>
                </ul>
                These visual aids are indispensable for understanding the trade-offs between different management choices.
            </p>
        </div>
    </div>

    <div class="sustainability-logic-section">
        <h2>🌳 The Logic Behind Sustainable Forest Management</h2>
        <p>
            At its core, Forest Vision is built upon fundamental principles of sustainable forest management. It helps users understand the delicate balance required to utilize forest resources today without compromising their availability and health for future generations.
        </p>

        <div class="logic-insight-subsection">
            <h3>🌲 Insight 1: The Power of Cutting Regimes (DBH Thresholds)</h3>
            <p>
                The Diameter at Breast Height (DBH) threshold for cutting is a pivotal decision in forest management. Our simulations demonstrate its profound impacts:
                <ul>
                    <li><strong>Lower DBH Thresholds (e.g., 45cm):</strong> Lead to harvesting more trees, resulting in higher immediate timber yields. However, this often brings greater disturbance, increased collateral damage to 'victim' trees, and can slow the forest's natural recovery, potentially reducing future yields and ecological health.</li>
                    <li><strong>Higher DBH Thresholds (e.g., 60cm):</strong> Mean fewer, typically larger trees are harvested. While immediate economic returns might be lower, this strategy generally causes less overall disturbance, retains more mature trees (which are crucial for biodiversity, seed dispersal, and ecosystem resilience), and promotes a healthier, more productive forest in the long run.</li>
                </ul>
            </p>
        </div>

        <div class="logic-insight-subsection">
            <h3>📆 Insight 2: Balancing Today's Harvest with Tomorrow's Forest (30-Year Projections)</h3>
            <p>
                True sustainability isn't just about what you harvest today; it's about what you leave for tomorrow. The **30-year projections** provided by Forest Vision are vital for this assessment. They clearly illustrate whether a chosen management strategy allows the forest to replenish its resources and maintain its ecological functions over time. A management strategy is truly sustainable if its future projections show a robust and productive forest, ensuring continuous timber supply and ecological services for generations to come.
            </p>
        </div>

        <div class="logic-insight-subsection">
            <h3>🚫 Insight 3: Minimizing Collateral Damage (Victim Trees)</h3>
            <p>
                Logging operations, especially in dense tropical forests, can inadvertently damage non-harvested trees. Our simulation quantifies these 'victim' trees and their lost volume. The ability to minimize such collateral damage is paramount for maintaining the overall health, structural integrity, and future growth potential of the forest, directly contributing to both its long-term ecological and economic sustainability.
            </p>
        </div>
    </div>

    <div class="contribution-section">
        <h2>📈 Your Contribution to Sustainable Forestry</h2>
        <p>
            By interacting with Forest Vision, you gain the power to visually explore these complex trade-offs. This data-driven understanding of how different management decisions influence the long-term health and productivity of a forest ecosystem empowers you to plan smarter, more sustainable forest operations for a resilient future.
        </p>
    </div>
</section>

<?php require_once 'footer.php'; ?>