<?php
/**
 * Composant de recherche et filtres pour les événements
 * 
 * Variables requises :
 * - $filters : tableau avec les filtres actifs
 * - $categories : liste des catégories disponibles
 */

if (!isset($filters)) {
    $filters = [
        'search' => '',
        'category' => '',
        'time_filter' => '',
        'date_filter' => ''
    ];
}
?>

<!-- Sidebar avec filtres -->
<aside class="filters-sidebar">
    <!-- Filtre par catégorie -->
    <div class="filter-group">
        <h3><?php echo t('events.category_filter'); ?></h3>
        <div class="filter-chips">
            <a href="?<?php echo http_build_query(['search' => $filters['search'], 'time' => $filters['time_filter'], 'date' => $filters['date_filter']]); ?>" 
               class="filter-chip <?php echo ($filters['category'] === '') ? 'active' : ''; ?>">
                <?php echo t('events.all_categories'); ?>
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $cat['name'], 'time' => $filters['time_filter'], 'date' => $filters['date_filter']]); ?>" 
                   class="filter-chip <?php echo ($filters['category'] === $cat['name']) ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Filtre par moment de la journée -->
    <div class="filter-group">
        <h3><?php echo t('events.time_filter'); ?></h3>
        <div class="filter-chips">
            <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'date' => $filters['date_filter']]); ?>" 
               class="filter-chip <?php echo ($filters['time_filter'] === '') ? 'active' : ''; ?>">
                <?php echo t('events.all_times'); ?>
            </a>
            <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => 'morning', 'date' => $filters['date_filter']]); ?>" 
               class="filter-chip <?php echo ($filters['time_filter'] === 'morning') ? 'active' : ''; ?>">
                <?php echo t('events.morning'); ?>
            </a>
            <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => 'afternoon', 'date' => $filters['date_filter']]); ?>" 
               class="filter-chip <?php echo ($filters['time_filter'] === 'afternoon') ? 'active' : ''; ?>">
                <?php echo t('events.afternoon'); ?>
            </a>
            <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => 'evening', 'date' => $filters['date_filter']]); ?>" 
               class="filter-chip <?php echo ($filters['time_filter'] === 'evening') ? 'active' : ''; ?>">
                <?php echo t('events.evening'); ?>
            </a>
        </div>
    </div>

    <!-- Filtre par période -->
    <div class="filter-group">
        <h3><?php echo t('events.period_filter'); ?></h3>
        <div class="filter-chips">
            <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => $filters['time_filter']]); ?>" 
               class="filter-chip <?php echo ($filters['date_filter'] === '') ? 'active' : ''; ?>">
                <?php echo t('events.all_periods'); ?>
            </a>
            <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => $filters['time_filter'], 'date' => 'week']); ?>" 
               class="filter-chip <?php echo ($filters['date_filter'] === 'week') ? 'active' : ''; ?>">
                <?php echo t('events.this_week'); ?>
            </a>
            <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => $filters['time_filter'], 'date' => 'month']); ?>" 
               class="filter-chip <?php echo ($filters['date_filter'] === 'month') ? 'active' : ''; ?>">
                <?php echo t('events.this_month'); ?>
            </a>
            <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => $filters['time_filter'], 'date' => 'coming']); ?>" 
               class="filter-chip <?php echo ($filters['date_filter'] === 'coming') ? 'active' : ''; ?>">
                <?php echo t('events.coming_soon'); ?>
            </a>
            <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => $filters['time_filter'], 'date' => 'past']); ?>" 
               class="filter-chip <?php echo ($filters['date_filter'] === 'past') ? 'active' : ''; ?>">
                <?php echo t('events.past'); ?>
            </a>
        </div>
    </div>
</aside>

<!-- Barre de recherche -->
<div class="search-section">
    <form method="GET" class="search-form">
        <!-- Conserver les filtres existants -->
        <?php if (!empty($filters['category'])): ?>
            <input type="hidden" name="category" value="<?php echo htmlspecialchars($filters['category'], ENT_QUOTES, 'UTF-8'); ?>">
        <?php endif; ?>
        <?php if (!empty($filters['time_filter'])): ?>
            <input type="hidden" name="time" value="<?php echo htmlspecialchars($filters['time_filter'], ENT_QUOTES, 'UTF-8'); ?>">
        <?php endif; ?>
        <?php if (!empty($filters['date_filter'])): ?>
            <input type="hidden" name="date" value="<?php echo htmlspecialchars($filters['date_filter'], ENT_QUOTES, 'UTF-8'); ?>">
        <?php endif; ?>
        
        <!-- Champ de recherche -->
        <input 
            type="search" 
            name="search" 
            placeholder="<?php echo t('events.search_placeholder'); ?>" 
            value="<?php echo htmlspecialchars($filters['search'], ENT_QUOTES, 'UTF-8'); ?>"
            aria-label="Rechercher un événement"
        >
        <button type="submit" class="btn btn-primary">
            <?php echo t('events.search_button'); ?>
        </button>
    </form>
</div>
