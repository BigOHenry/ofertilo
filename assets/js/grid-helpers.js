// assets/js/grid-helpers.js
window.refreshGrid = function(gridName) {
    if (window.grids && window.grids[gridName]) {
        window.grids[gridName].replaceData();
    } else {
        console.error(`Grid "${gridName}" not found`);
    }
};

window.refreshAllGrids = function() {
    if (window.grids) {
        Object.values(window.grids).forEach(grid => grid.replaceData());
    }
};

window.getGrid = function(gridName) {
    return window.grids?.[gridName];
};
