#!/usr/bin/env php
<?php

$migrationsPath = 'database/migrations/';

// Check for the new migrations
$migrations = [
    '2026_07_16_100000_create_modepaies_table.php',
    '2026_07_16_100001_add_modepaie_to_factures_table.php',
];

foreach ($migrations as $migration) {
    if (file_exists($migrationsPath . $migration)) {
        echo "✓ Migration found: {$migration}\n";
    } else {
        echo "✗ Migration NOT found: {$migration}\n";
    }
}

// Check for ModePaieSeeder
$seederPath = 'database/seeders/ModePaieSeeder.php';
if (file_exists($seederPath)) {
    echo "✓ Seeder found: ModePaieSeeder.php\n";
} else {
    echo "✗ Seeder NOT found: ModePaieSeeder.php\n";
}

// Check ModePaie model
$modelPath = 'app/Models/ModePaie.php';
if (file_exists($modelPath)) {
    echo "✓ Model found: ModePaie.php\n";
} else {
    echo "✗ Model NOT found: ModePaie.php\n";
}

// Check ModePaieController
$controllerPath = 'app/Http/Controllers/ModePaieController.php';
if (file_exists($controllerPath)) {
    echo "✓ Controller found: ModePaieController.php\n";
} else {
    echo "✗ Controller NOT found: ModePaieController.php\n";
}

// Check ModePaie view
$viewPath = 'resources/views/pages/modepaie/index.blade.php';
if (file_exists($viewPath)) {
    echo "✓ View found: modepaie/index.blade.php\n";
} else {
    echo "✗ View NOT found: modepaie/index.blade.php\n";
}

echo "\nAll ModePaie components are ready!\n";
