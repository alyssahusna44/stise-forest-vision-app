<?php
// analysis.php - Forest Cutting Regime Analysis page
include 'header.php';
?>

<div class="analysis-content">
    <h1>Forest Cutting Regime Analysis</h1>
    
    <div class="analysis-intro">
        <p>This comprehensive analysis examines different forest cutting regimes and their long-term sustainability implications. 
        The study compares current forest structure with 30-year projections across various diameter at breast height (DBH) cutting thresholds 
        to determine the most sustainable harvesting approach.</p>
    </div>

    <section class="forest-structure-section">
        <h2>Current Forest Structure Analysis</h2>
        
        <div class="structure-images">
            <div class="image-grid">
                <div class="image-item">
                    <img src="image/histograms/current_45cm.png" alt="Current Forest Structure - 45cm DBH Regime" class="analysis-image">
                    <p class="image-caption">45cm DBH Regime - Current Structure</p>
                </div>
                <div class="image-item">
                    <img src="image/histograms/current_50cm.png" alt="Current Forest Structure - 50cm DBH Regime" class="analysis-image">
                    <p class="image-caption">50cm DBH Regime - Current Structure</p>
                </div>
                <div class="image-item">
                    <img src="image/histograms/current_55cm.png" alt="Current Forest Structure - 55cm DBH Regime" class="analysis-image">
                    <p class="image-caption">55cm DBH Regime - Current Structure</p>
                </div>
                <div class="image-item">
                    <img src="image/histograms/current_60cm.png" alt="Current Forest Structure - 60cm DBH Regime" class="analysis-image">
                    <p class="image-caption">60cm DBH Regime - Current Structure</p>
                </div>
                <div class="image-item">
                    <img src="image/histograms/current_65cm.png" alt="Current Forest Structure - 65cm DBH Regime" class="analysis-image">
                    <p class="image-caption">65cm DBH Regime - Current Structure</p>
                </div>
            </div>
        </div>

        <div class="structure-analysis">
            <h3>Key Observations</h3>
            <p>The current forest structure across all diameter at breast height (DBH) regimes reveals a consistent pattern:</p>
            <ul>
                <li><strong>Dominant Small Diameter Classes:</strong> Heavy concentration in 5-15cm and 15-30cm diameter classes, with approximately 10,000+ trees in each category</li>
                <li><strong>Declining Larger Classes:</strong> Sharp decline in 30-45cm class (around 3,500 trees) and further reduction in 45-60cm class (around 1,200 trees)</li>
                <li><strong>Limited Mature Trees:</strong> Very few trees in the 60+ cm category (less than 800 trees), indicating a young forest structure</li>
                <li><strong>Inverse J-Shape Distribution:</strong> Classic uneven-aged forest structure with exponential decline in tree numbers as diameter increases</li>
            </ul>
        </div>
    </section>

    <section class="projections-section">
        <h2>30-Year Forest Structure Projections</h2>
        
        <div class="projection-images">
            <div class="image-grid">
                <div class="image-item">
                    <img src="image/histograms/y30_45cm.png" alt="30-Year Projection - 45cm DBH Regime" class="analysis-image">
                    <p class="image-caption">45cm DBH Regime - 30-Year Projection</p>
                </div>
                <div class="image-item">
                    <img src="image/histograms/y30_50cm.png" alt="30-Year Projection - 50cm DBH Regime" class="analysis-image">
                    <p class="image-caption">50cm DBH Regime - 30-Year Projection</p>
                </div>
                <div class="image-item">
                    <img src="image/histograms/y30_55cm.png" alt="30-Year Projection - 55cm DBH Regime" class="analysis-image">
                    <p class="image-caption">55cm DBH Regime - 30-Year Projection</p>
                </div>
                <div class="image-item">
                    <img src="image/histograms/y30_60cm.png" alt="30-Year Projection - 60cm DBH Regime" class="analysis-image">
                    <p class="image-caption">60cm DBH Regime - 30-Year Projection</p>
                </div>
                <div class="image-item">
                    <img src="image/histograms/y30_65cm.png" alt="30-Year Projection - 65cm DBH Regime" class="analysis-image">
                    <p class="image-caption">65cm DBH Regime - 30-Year Projection</p>
                </div>
            </div>
        </div>

        <div class="projection-analysis">
            <h3>Projection Analysis</h3>
            <p>The 30-year projections reveal significant differences between cutting regimes:</p>
            
            <div class="regime-comparison">
                <div class="regime-item">
                    <h4>45cm DBH Regime</h4>
                    <p>Shows dramatic structural shift with 30-45cm class becoming dominant (approximately 12,500 trees). 
                    This indicates intensive harvesting of smaller trees, potentially compromising natural regeneration.</p>
                </div>
                
                <div class="regime-item">
                    <h4>50cm DBH Regime</h4>
                    <p>Maintains more balanced distribution with 30-45cm class prominent (around 12,900 trees) but less extreme than 45cm regime. 
                    Still shows concentration in middle diameter classes.</p>
                </div>
                
                <div class="regime-item">
                    <h4>55-65cm DBH Regimes</h4>
                    <p>Demonstrate increasingly balanced distributions with better retention of larger diameter classes. 
                    These regimes maintain more natural forest structure while allowing sustainable harvesting.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="sustainability-section">
        <h2>Sustainability Analysis & Recommendations</h2>
        
        <div class="recommendation-highlight">
            <h3>🌲 Recommended Cutting Size: 55-60cm DBH</h3>
            <p class="recommendation-text">Based on comprehensive analysis, the 55-60cm diameter at breast height cutting regime 
            represents the optimal balance between economic viability and long-term forest sustainability.</p>
        </div>

        <div class="sustainability-benefits">
            <div class="benefit-category">
                <h4>🌲 Sustainability of Timber Production</h4>
                <ul>
                    <li><strong>Long-term Yield Stability:</strong> 55-60cm DBH regime maintains optimal rotation cycles ensuring continuous timber supply</li>
                    <li><strong>Premium Timber Quality:</strong> Larger diameter trees produce higher-grade lumber with superior structural properties</li>
                    <li><strong>Harvesting Efficiency:</strong> Fewer, larger trees reduce operational costs and equipment wear per cubic meter harvested</li>
                    <li><strong>Natural Regeneration Success:</strong> Mature seed trees provide reliable natural regeneration, reducing replanting costs</li>
                    <li><strong>Market Certification:</strong> Sustainable practices enable FSC/PEFC certification, accessing premium markets</li>
                </ul>
            </div>

            <div class="benefit-category">
                <h4>🦋 Biodiversity Conservation</h4>
                <ul>
                    <li><strong>Habitat Complexity:</strong> Large trees create multi-layered canopy structure supporting diverse wildlife communities</li>
                    <li><strong>Nesting and Roosting Sites:</strong> Mature trees provide cavities and platforms essential for bird and mammal reproduction</li>
                    <li><strong>Food Source Diversity:</strong> Large trees produce more seeds, fruits, and support greater insect diversity</li>
                    <li><strong>Migration Corridors:</strong> Maintaining canopy connectivity facilitates wildlife movement and genetic exchange</li>
                    <li><strong>Rare Species Protection:</strong> Old-growth dependent species require large diameter trees for survival</li>
                </ul>
            </div>

            <div class="benefit-category">
                <h4>🌍 Carbon Balance</h4>
                <ul>
                    <li><strong>Enhanced Carbon Storage:</strong> Large trees store exponentially more carbon - a 60cm tree stores 4x more than a 30cm tree</li>
                    <li><strong>Soil Carbon Sequestration:</strong> Extensive root systems and leaf litter from large trees build soil organic matter</li>
                    <li><strong>Reduced Emissions:</strong> Fewer harvesting operations and transport trips lower fossil fuel consumption</li>
                    <li><strong>Long-term Carbon Retention:</strong> High-quality timber products provide durable carbon storage in construction</li>
                    <li><strong>Climate Regulation:</strong> Large tree canopies moderate local temperature and humidity, reducing climate stress</li>
                </ul>
            </div>

            <div class="benefit-category">
                <h4>💼 Socio-economic Development</h4>
                <ul>
                    <li><strong>Rural Employment:</strong> Sustainable forestry creates stable, long-term jobs in rural communities</li>
                    <li><strong>Value-added Processing:</strong> Larger timber enables local sawmills and furniture industries to develop</li>
                    <li><strong>Tourism Revenue:</strong> Well-managed forests attract eco-tourism, providing additional income streams</li>
                    <li><strong>Traditional Practices:</strong> Supports indigenous and local communities' sustainable forest use traditions</li>
                    <li><strong>Educational Opportunities:</strong> Demonstration forests provide training grounds for sustainable forestry practices</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="risk-analysis-section">
        <h2>⚠️ Risks of Aggressive Cutting Regimes</h2>
        
        <div class="risk-categories">
            <div class="risk-item">
                <h4>45-50cm DBH Regimes - High Risk Factors</h4>
                <ul>
                    <li><strong>Premature Harvesting:</strong> Trees removed before reaching peak growth and value potential</li>
                    <li><strong>Ecosystem Disruption:</strong> Loss of critical habitat and ecosystem service providers</li>
                    <li><strong>Regeneration Failure:</strong> Insufficient mature trees for adequate seed production</li>
                    <li><strong>Simplified Structure:</strong> Risk of creating even-aged, monospecific stands</li>
                    <li><strong>Economic Losses:</strong> Lower per-tree value and reduced long-term productivity</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="implementation-section">
        <h2>🎯 Implementation Guidelines</h2>
        
        <div class="guidelines-grid">
            <div class="guideline-item">
                <h4>Monitoring Protocol</h4>
                <ul>
                    <li>Annual diameter measurements for growth tracking</li>
                    <li>Species composition monitoring</li>
                    <li>Regeneration success assessment</li>
                    <li>Wildlife habitat quality evaluation</li>
                </ul>
            </div>
            
            <div class="guideline-item">
                <h4>Adaptive Management</h4>
                <ul>
                    <li>Regular review of cutting thresholds based on growth data</li>
                    <li>Adjustment for climate change impacts</li>
                    <li>Integration of market conditions</li>
                    <li>Stakeholder feedback incorporation</li>
                </ul>
            </div>
            
            <div class="guideline-item">
                <h4>Certification Alignment</h4>
                <ul>
                    <li>FSC (Forest Stewardship Council) standards compliance</li>
                    <li>PEFC (Programme for Endorsement of Forest Certification) requirements</li>
                    <li>National sustainable forestry guidelines</li>
                    <li>International best practices adoption</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="conclusion-section">
        <h2>Conclusion</h2>
        <p>The analysis clearly demonstrates that the 55-60cm DBH cutting regime represents the optimal balance for sustainable forest management. 
        This approach maximizes both ecological integrity and economic returns while ensuring long-term forest productivity. 
        The projected forest structures show that this regime maintains the natural age class distribution essential for ecosystem resilience 
        and continuous timber supply.</p>
        
        <p>Implementing this recommendation, combined with proper monitoring and adaptive management practices, will ensure that forest 
        resources remain available for future generations while meeting current economic needs. This approach aligns with international 
        sustainability standards and represents best practices in modern forest management.</p>
    </section>
</div>

<style>
/* Analysis-specific styles */
.analysis-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    line-height: 1.6;
}

.analysis-intro {
    background-color: var(--bg-light);
    padding: 25px;
    border-radius: 10px;
    margin-bottom: 30px;
    border-left: 5px solid var(--forest-green);
}

.analysis-intro p {
    font-size: 1.1em;
    margin: 0;
    text-align: justify;
}

.forest-structure-section,
.projections-section,
.sustainability-section,
.risk-analysis-section,
.implementation-section,
.conclusion-section {
    margin-bottom: 40px;
    padding: 25px;
    background-color: var(--text-light);
    border-radius: 10px;
    box-shadow: 0 2px 8px var(--shadow-light);
}

.image-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.image-item {
    text-align: center;
}

.analysis-image {
    width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 4px 8px var(--shadow-light);
    border: 2px solid var(--border-color-light);
}

.image-caption {
    margin-top: 10px;
    font-weight: bold;
    color: var(--dark-green);
    font-size: 0.9em;
}

.structure-analysis,
.projection-analysis {
    margin-top: 25px;
}

.structure-analysis h3,
.projection-analysis h3 {
    color: var(--forest-green);
    border-bottom: 2px solid var(--leaf-green);
    padding-bottom: 5px;
}

.regime-comparison {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.regime-item {
    background-color: var(--bg-light);
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid var(--forest-green);
}

.regime-item h4 {
    color: var(--dark-green);
    margin-top: 0;
}

.recommendation-highlight {
    background: linear-gradient(135deg, var(--leaf-green), var(--light-green));
    padding: 30px;
    border-radius: 12px;
    text-align: center;
    margin-bottom: 30px;
    box-shadow: 0 4px 12px var(--shadow-medium);
}

.recommendation-highlight h3 {
    color: var(--dark-green);
    font-size: 1.5em;
    margin-bottom: 15px;
}

.recommendation-text {
    font-size: 1.1em;
    font-weight: 500;
    color: var(--text-dark);
    margin: 0;
}

.sustainability-benefits {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    margin-top: 20px;
}

.benefit-category {
    background-color: var(--bg-light);
    padding: 20px;
    border-radius: 10px;
    border-top: 4px solid var(--forest-green);
}

.benefit-category h4 {
    color: var(--dark-green);
    margin-top: 0;
    font-size: 1.2em;
}

.benefit-category ul {
    list-style: none;
    padding: 0;
}

.benefit-category li {
    margin-bottom: 10px;
    padding-left: 20px;
    position: relative;
}

.benefit-category li:before {
    content: "✓";
    position: absolute;
    left: 0;
    color: var(--forest-green);
    font-weight: bold;
}

.risk-categories {
    margin-top: 20px;
}

.risk-item {
    background-color: #fff3cd;
    border: 1px solid #ffeaa7;
    border-left: 4px solid #f39c12;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 15px;
}

.risk-item h4 {
    color: #856404;
    margin-top: 0;
}

.risk-item ul {
    list-style: none;
    padding: 0;
}

.risk-item li {
    margin-bottom: 8px;
    padding-left: 20px;
    position: relative;
}

.risk-item li:before {
    content: "⚠️";
    position: absolute;
    left: 0;
    font-size: 0.8em;
}

.guidelines-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.guideline-item {
    background-color: var(--bg-light);
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid var(--forest-green);
}

.guideline-item h4 {
    color: var(--dark-green);
    margin-top: 0;
    margin-bottom: 15px;
}

.conclusion-section {
    background: linear-gradient(135deg, #f8f9fa, var(--bg-light));
    border-left: 5px solid var(--forest-green);
}

.conclusion-section h2 {
    color: var(--forest-green);
    text-align: center;
    margin-bottom: 20px;
}

.conclusion-section p {
    font-size: 1.05em;
    text-align: justify;
    margin-bottom: 15px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .image-grid {
        grid-template-columns: 1fr;
    }
    
    .sustainability-benefits,
    .guidelines-grid {
        grid-template-columns: 1fr;
    }
    
    .recommendation-highlight {
        padding: 20px;
    }
    
    .recommendation-highlight h3 {
        font-size: 1.3em;
    }
}

@media (max-width: 480px) {
    .analysis-content {
        padding: 10px;
    }
    
    .forest-structure-section,
    .projections-section,
    .sustainability-section,
    .risk-analysis-section,
    .implementation-section,
    .conclusion-section {
        padding: 15px;
    }
    
    .recommendation-highlight {
        padding: 15px;
    }
}
</style>

<?php include 'footer.php'; ?>