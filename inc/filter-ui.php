<?php
// inc/filter-ui.php
// Filter UI: Bottom Sheet + Segmented Buttons + Other Options
// Included in index.php only when $isCatalog is true
?>

<!-- Filter trigger button -->
<button id="openFilter" class="filter-btn">Filter</button>

<!-- Modal overlay for bottom sheet filter -->
<div id="filterSheet" class="modal">
  <!-- Bottom sheet content panel -->
  <div class="sheet-content">
    <!-- Header with title and close icon -->
    <div class="sheet-header">
      <h2>Filter Products</h2>
      <button id="closeFilter" class="close-btn" aria-label="Close filters">&times;</button>
    </div>

    <!-- Segmented buttons for gender filter -->
    <div class="segmented-control" role="group" aria-label="Filter by gender">
      <!-- "All" option -->
      <label class="segment">
        <input type="radio" name="gender-filter" value="all" checked>
        <span class="segment-label">All</span>
      </label>
      <!-- "Women" option (female symbol) -->
      <label class="segment">
        <input type="radio" name="gender-filter" value="women">
        <span class="segment-label" aria-label="Women">&#9792;</span>
      </label>
      <!-- "Men" option (male symbol) -->
      <label class="segment">
        <input type="radio" name="gender-filter" value="men">
        <span class="segment-label" aria-label="Men">&#9794;</span>
      </label>
    </div>

    <!-- Other category filters -->
    <div class="other-filters">
      <!-- Kids category button -->
      <button id="filterKids" class="text-filter-btn">Kids</button>
      <!-- Plus-Size toggle -->
      <label class="plus-toggle">
        <input type="checkbox" id="plusSizeCheck">
        <span>Plus-Size Only</span>
      </label>
    </div>
  </div>
</div>
