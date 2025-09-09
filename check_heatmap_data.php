<?php
$_GET['username'] = 'mary_lepson';

ob_start();
include 'model.php';
$output = ob_get_clean();

// Look for heatmap data in the HTML
if (preg_match('/activity_by_hour.*?\[(.*?)\]/', $output, $matches)) {
    echo "✓ Found activity_by_hour data in HTML:\n";
    echo "  Raw data: " . $matches[1] . "\n";
    
    // Parse the array data
    $hours_data = explode(',', $matches[1]);
    $active_hours = [];
    foreach ($hours_data as $i => $value) {
        if (trim($value) > 0) {
            $active_hours[] = $i;
        }
    }
    echo "  Active hours: " . implode(',', $active_hours) . "\n";
} else {
    echo "✗ No activity_by_hour data found in HTML output\n";
}

// Look for any heatmap-related JavaScript or data
if (preg_match('/heatmap.*?(\d+,.*?\d+)/', $output, $matches)) {
    echo "✓ Found heatmap data: " . $matches[1] . "\n";
} else {
    echo "✗ No heatmap data patterns found\n";
}

// Check for any data-* attributes that might contain the heatmap data
if (preg_match('/data-activity.*?"(.*?)"/', $output, $matches)) {
    echo "✓ Found data-activity attribute: " . $matches[1] . "\n";
} else {
    echo "✗ No data-activity attributes found\n";
}

// Look for the specific heatmap cells
$cell_pattern = '/heatmap-cell.*?data-.*?(\d+)/';
if (preg_match_all($cell_pattern, $output, $matches)) {
    echo "✓ Found " . count($matches[0]) . " heatmap cells\n";
} else {
    echo "✗ No heatmap cells found\n";
}

echo "\n";

// Show a larger snippet around the analytics dashboard
if (preg_match('/(.*analytics-dashboard.*)/s', $output, $matches)) {
    echo "Analytics dashboard section found:\n";
    $lines = explode("\n", $matches[1]);
    for ($i = 0; $i < min(20, count($lines)); $i++) {
        $line = trim(strip_tags($lines[$i]));
        if (!empty($line)) {
            echo "  " . substr($line, 0, 100) . "\n";
        }
    }
}