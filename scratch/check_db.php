<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Factures columns:\n" . implode(', ', \Illuminate\Support\Facades\Schema::getColumnListing('factures')) . "\n\n";
echo "FactureAvoirs columns:\n" . implode(', ', \Illuminate\Support\Facades\Schema::getColumnListing('facture_avoirs')) . "\n\n";
echo "FactureProformas columns:\n" . implode(', ', \Illuminate\Support\Facades\Schema::getColumnListing('facture_proformas')) . "\n";
