<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Contribuable;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\User;
use App\Models\Categorie;
use App\Models\Service;
use App\Models\Tva;
use App\Models\Bic;
use App\Models\Facture;
use App\Models\ElementFacture;

class ImportExportController extends Controller
{
    private function schemas(): array
    {
        return [
            'clients' => [
                'model'       => Contribuable::class,
                'label'       => 'Clients',
                'icon'        => 'users',
                'color'       => '#1d4ed8',
                'bg'          => '#eff6ff',
                'cols'        => ['assujeti', 'adresse', 'adresse_localisation', 'telephone', 'ifu', 'rccm', 'division_fiscal', 'regime_fiscal', 'adresse_cadas', 'categories_id'],
                'description' => 'Clients (contribuables) avec leurs informations fiscales',
                'unique_key'  => null,
                'import_ok'   => true,
            ],
            'fournisseurs' => [
                'model'       => Fournisseur::class,
                'label'       => 'Fournisseurs',
                'icon'        => 'truck',
                'color'       => '#059669',
                'bg'          => '#f0fdf4',
                'cols'        => ['assujeti', 'adresse', 'adresse_localisation', 'telephone', 'ifu', 'rccm', 'division_fiscal', 'regime_fiscal', 'adresse_cadas', 'categories_id'],
                'description' => 'Fournisseurs avec leurs coordonnées et informations fiscales',
                'unique_key'  => null,
                'import_ok'   => true,
            ],
            'produits' => [
                'model'       => Produit::class,
                'label'       => 'Produits',
                'icon'        => 'package',
                'color'       => '#7c3aed',
                'bg'          => '#f5f3ff',
                'cols'        => ['code', 'designation', 'prix_unitaire', 'unite', 'qte', 'description', 'famille_produit', 'tva_id', 'bic_id'],
                'description' => 'Catalogue produits/services avec prix, TVA et BIC associés',
                'unique_key'  => 'code',
                'import_ok'   => true,
            ],
            'categories' => [
                'model'       => Categorie::class,
                'label'       => 'Catégories',
                'icon'        => 'tag',
                'color'       => '#d97706',
                'bg'          => '#fffbeb',
                'cols'        => ['code', 'libelle'],
                'description' => 'Lignes métier / catégories de facturation',
                'unique_key'  => 'code',
                'import_ok'   => true,
            ],
            'services' => [
                'model'       => Service::class,
                'label'       => 'Dépôts',
                'icon'        => 'home',
                'color'       => '#0891b2',
                'bg'          => '#ecfeff',
                'cols'        => ['code', 'libelle', 'description'],
                'description' => 'Dépôts / lignes de service de l\'organisation',
                'unique_key'  => 'code',
                'import_ok'   => true,
            ],
            'tva' => [
                'model'       => Tva::class,
                'label'       => 'TVA',
                'icon'        => 'percent',
                'color'       => '#dc2626',
                'bg'          => '#fef2f2',
                'cols'        => ['code', 'intitule', 'compte_tva', 'taux'],
                'description' => 'Taux de TVA applicables aux produits',
                'unique_key'  => 'code',
                'import_ok'   => true,
            ],
            'bic' => [
                'model'       => Bic::class,
                'label'       => 'BIC',
                'icon'        => 'trending-up',
                'color'       => '#ea580c',
                'bg'          => '#fff7ed',
                'cols'        => ['code', 'intitule', 'taux'],
                'description' => 'Taux BIC applicables aux produits',
                'unique_key'  => 'code',
                'import_ok'   => true,
            ],
            'factures' => [
                'model'       => Facture::class,
                'label'       => 'Factures',
                'icon'        => 'file-text',
                'color'       => '#0d9488',
                'bg'          => '#f0fdfa',
                'cols'        => ['facture_code', 'date', 'reference', 'statut', 'remise_1', 'remise_2', 'contribuable_id', 'service_id', 'typefacture_id', 'modepaie_id', 'bic_id', 'produit_id', 'quantite', 'prix_unitaire', 'designation'],
                'description' => 'Factures avec leurs lignes (entête + articles) — export complet et reprise de données historiques',
                'unique_key'  => null,
                'import_ok'   => true,
                'purge_ok'    => false,
            ],
            'utilisateurs' => [
                'model'       => User::class,
                'label'       => 'Utilisateurs',
                'icon'        => 'shield',
                'color'       => '#475569',
                'bg'          => '#f8fafc',
                'cols'        => ['nom', 'prenom', 'login', 'email', 'role', 'activate', 'module'],
                'description' => 'Comptes utilisateurs de la plateforme (export uniquement)',
                'unique_key'  => 'email',
                'import_ok'   => false,
            ],
        ];
    }

    public function index()
    {
        $schemas = $this->schemas();
        $stats   = [];

        foreach ($schemas as $key => $schema) {
            $stats[$key] = $schema['model']::count();
        }

        return view('pages.parametres.index', compact('schemas', 'stats'));
    }

    public function export(string $type)
    {
        $schemas = $this->schemas();
        abort_if(!isset($schemas[$type]), 404);

        if ($type === 'utilisateurs' && !in_array(Auth::user()->role, ['administrateur', 'superviseur'])) {
            abort(403, 'Accès non autorisé.');
        }

        if ($type === 'factures') {
            return $this->exportFactures();
        }

        $schema   = $schemas[$type];
        $rows     = $schema['model']::all();
        $cols     = $schema['cols'];
        $filename = $type . '_export_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows, $cols) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            fputcsv($out, array_merge(['id'], $cols), ';');
            foreach ($rows as $row) {
                $line = [$row->id];
                foreach ($cols as $col) {
                    $line[] = $row->{$col} ?? '';
                }
                fputcsv($out, $line, ';');
            }
            fclose($out);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function template(string $type)
    {
        $schemas = $this->schemas();
        abort_if(!isset($schemas[$type]), 404);

        if ($type === 'factures') {
            return $this->templateFactures();
        }

        $cols     = $schemas[$type]['cols'];
        $filename = 'modele_' . $type . '.csv';

        $hints = [
            'assujeti'             => 'Nom ou Raison sociale',
            'adresse'              => 'Adresse postale',
            'adresse_localisation' => 'Adresse de localisation',
            'telephone'            => '+224 620000000',
            'ifu'                  => 'Numéro IFU',
            'rccm'                 => 'Numéro RCCM',
            'division_fiscal'      => 'DGE / DGMC / ...',
            'regime_fiscal'        => 'RNI / RSI / ...',
            'adresse_cadas'        => 'Adresse cadastrale',
            'categories_id'        => 'ID de la catégorie (ex: 1)',
            'code'                 => 'Code unique (ex: CAT001)',
            'libelle'              => 'Libellé',
            'description'          => 'Description',
            'designation'          => 'Désignation du produit',
            'prix_unitaire'        => '1500.00',
            'unite'                => 'KG / PCS / M2 / ...',
            'qte'                  => 'Quantité en stock',
            'famille_produit'      => 'LOCBIE / IMPBIE / LOCSER / IMPSER',
            'tva_id'               => 'ID TVA (ex: 1)',
            'bic_id'               => 'ID BIC (ex: 1)',
            'intitule'             => 'Intitulé',
            'compte_tva'           => 'Compte comptable TVA',
            'taux'                 => 'Taux en % (ex: 18)',
            'nom'                  => 'Nom de famille',
            'prenom'               => 'Prénom',
            'login'                => 'Login de connexion',
            'email'                => 'adresse@email.com',
            'role'                 => 'administrateur / superviseur / operateur',
            'activate'             => 'oui / non',
            'module'               => 'facturation / diligence / tous',
        ];

        return response()->streamDownload(function () use ($cols, $hints) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, $cols, ';');
            // One example row with hints
            $example = array_map(fn($col) => $hints[$col] ?? '', $cols);
            fputcsv($out, $example, ';');
            fclose($out);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function import(Request $request, string $type)
    {
        $schemas = $this->schemas();
        abort_if(!isset($schemas[$type]), 404);

        if ($type === 'factures') {
            return $this->importFactures($request);
        }

        $schema = $schemas[$type];

        if (!$schema['import_ok']) {
            return back()->with('import_error_' . $type, 'L\'import est désactivé pour ce type de données.');
        }

        $request->validate([
            'fichier_' . $type => 'required|file|max:10240',
        ], [
            'fichier_' . $type . '.required' => 'Veuillez sélectionner un fichier CSV.',
            'fichier_' . $type . '.file'     => 'Le fichier est invalide.',
            'fichier_' . $type . '.max'      => 'Le fichier ne doit pas dépasser 10 Mo.',
        ]);

        $model      = $schema['model'];
        $cols       = $schema['cols'];
        $uniqueKey  = $schema['unique_key'];
        $file       = $request->file('fichier_' . $type);

        $handle = fopen($file->getRealPath(), 'r');

        // Strip UTF-8 BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            fseek($handle, 0);
        }

        // Read header row
        $header = fgetcsv($handle, 0, ';');

        if (!$header || count($header) === 0) {
            fclose($handle);
            return back()->with('import_error_' . $type, 'Le fichier est vide ou son format est invalide. Utilisez le modèle CSV fourni.');
        }

        $header = array_map('trim', $header);

        // Drop 'id' column if exported file is re-imported
        $idIndex = array_search('id', $header);
        if ($idIndex !== false) {
            array_splice($header, $idIndex, 1);
            $hasIdCol = true;
        } else {
            $hasIdCol = false;
        }

        // Check required columns
        $missing = array_diff($cols, $header);
        if (!empty($missing)) {
            fclose($handle);
            return back()->with(
                'import_error_' . $type,
                'Colonnes manquantes dans le fichier : ' . implode(', ', $missing) . '. Téléchargez le modèle CSV pour respecter le format attendu.'
            );
        }

        $imported = 0;
        $updated  = 0;
        $skipped  = 0;
        $errors   = [];
        $rowNum   = 1;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $rowNum++;

            // Drop id column value if present
            if ($hasIdCol && $idIndex !== false) {
                array_splice($row, $idIndex, 1);
            }

            if (count($row) < count($header)) {
                $skipped++;
                $errors[] = "Ligne {$rowNum} : nombre de colonnes insuffisant (" . count($row) . ' trouvées, ' . count($header) . ' attendues).';
                continue;
            }

            // Build data array from header mapping
            $data = [];
            foreach ($header as $i => $col) {
                if (!in_array($col, $cols)) {
                    continue;
                }
                $val        = isset($row[$i]) ? trim($row[$i]) : '';
                $data[$col] = ($val === '' || $val === 'NULL') ? null : $val;
            }

            // Skip entirely empty rows
            if (empty(array_filter($data))) {
                continue;
            }

            try {
                DB::beginTransaction();

                if ($uniqueKey && isset($data[$uniqueKey]) && $data[$uniqueKey] !== null) {
                    $exists = $model::where($uniqueKey, $data[$uniqueKey])->first();
                    if ($exists) {
                        $exists->update($data);
                        $updated++;
                    } else {
                        $model::create($data);
                        $imported++;
                    }
                } else {
                    $model::create($data);
                    $imported++;
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                $skipped++;
                $errors[] = "Ligne {$rowNum} : " . $this->friendlyError($e->getMessage());
                if (count($errors) >= 10) {
                    $errors[] = '… (trop d\'erreurs, import interrompu à la ligne ' . $rowNum . ')';
                    break;
                }
            }
        }

        fclose($handle);

        return back()->with('import_result_' . $type, [
            'imported' => $imported,
            'updated'  => $updated,
            'skipped'  => $skipped,
            'errors'   => $errors,
        ]);
    }

    public function backupAll()
    {
        if (!in_array(Auth::user()->role, ['administrateur', 'superviseur'])) {
            abort(403, 'Accès non autorisé.');
        }

        $schemas = $this->schemas();
        $tmpDir  = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'sig_backup_' . uniqid();
        mkdir($tmpDir, 0755, true);

        $csvFiles = [];

        foreach ($schemas as $key => $schema) {
            $rows     = $schema['model']::all();
            $cols     = $schema['cols'];
            $csvPath  = $tmpDir . DIRECTORY_SEPARATOR . $key . '.csv';
            $out      = fopen($csvPath, 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, array_merge(['id'], $cols), ';');
            foreach ($rows as $row) {
                $line = [$row->id];
                foreach ($cols as $col) {
                    $line[] = $row->{$col} ?? '';
                }
                fputcsv($out, $line, ';');
            }
            fclose($out);
            $csvFiles[] = $csvPath;
        }

        $zipName = 'backup_SIG_' . now()->format('Ymd_His') . '.zip';
        $zipPath = $tmpDir . DIRECTORY_SEPARATOR . $zipName;

        if (class_exists(\ZipArchive::class)) {
            $zip = new \ZipArchive();
            $zip->open($zipPath, \ZipArchive::CREATE);
            foreach ($csvFiles as $csvFile) {
                $zip->addFile($csvFile, basename($csvFile));
            }
            $zip->close();

            // Delete individual CSV files now that they are in the ZIP
            foreach ($csvFiles as $csvFile) {
                @unlink($csvFile);
            }

            return response()->download($zipPath, $zipName, [
                'Content-Type' => 'application/zip',
            ])->deleteFileAfterSend(true);
        }

        // Fallback: export only clients CSV if ZipArchive not available
        return response()->streamDownload(function () use ($schemas) {
            $schema = $schemas['clients'];
            $out    = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, array_merge(['id'], $schema['cols']), ';');
            foreach ($schema['model']::all() as $row) {
                $line = [$row->id];
                foreach ($schema['cols'] as $col) {
                    $line[] = $row->{$col} ?? '';
                }
                fputcsv($out, $line, ';');
            }
            fclose($out);
        }, 'clients_backup.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function backupSql()
    {
        if (!in_array(Auth::user()->role, ['administrateur', 'superviseur'])) {
            abort(403, 'Accès non autorisé.');
        }

        $dbName   = config('database.connections.' . config('database.default') . '.database');
        $filename = 'backup_SQL_' . now()->format('Ymd_His') . '.sql';

        return response()->streamDownload(function () use ($dbName) {
            echo "-- ============================================================\n";
            echo "-- SIG-FACTURATION — Sauvegarde complète SQL\n";
            echo "-- Base de données : {$dbName}\n";
            echo "-- Générée le      : " . now()->format('d/m/Y à H:i:s') . "\n";
            echo "-- ============================================================\n\n";
            echo "SET FOREIGN_KEY_CHECKS = 0;\n";
            echo "SET NAMES utf8mb4;\n";
            echo "SET CHARACTER SET utf8mb4;\n\n";

            $tablesResult = DB::select('SHOW TABLES');
            $tableKey     = 'Tables_in_' . $dbName;

            foreach ($tablesResult as $tableRow) {
                $table = $tableRow->$tableKey;

                echo "-- -------------------------------------------------------\n";
                echo "-- Table : `{$table}`\n";
                echo "-- -------------------------------------------------------\n";
                echo "DROP TABLE IF EXISTS `{$table}`;\n";

                $createResult = DB::select("SHOW CREATE TABLE `{$table}`");
                echo $createResult[0]->{'Create Table'} . ";\n\n";

                // INSERT par lots de 500 lignes
                $offset = 0;
                $limit  = 500;

                do {
                    $rows = DB::table($table)->offset($offset)->limit($limit)->get();

                    if ($rows->isEmpty()) {
                        break;
                    }

                    $columns = array_keys((array) $rows[0]);
                    $colList = '`' . implode('`, `', $columns) . '`';

                    $values = [];
                    foreach ($rows as $row) {
                        $escaped = array_map(function ($val) {
                            if ($val === null) {
                                return 'NULL';
                            }
                            return "'" . str_replace(
                                ['\\', "'", "\n", "\r", "\x1a"],
                                ['\\\\', "\\'", '\\n', '\\r', '\\Z'],
                                $val
                            ) . "'";
                        }, (array) $row);

                        $values[] = '(' . implode(', ', $escaped) . ')';
                    }

                    echo "INSERT INTO `{$table}` ({$colList}) VALUES\n";
                    echo implode(",\n", $values) . ";\n";

                    $offset += $limit;
                } while ($rows->count() === $limit);

                echo "\n";
            }

            echo "SET FOREIGN_KEY_CHECKS = 1;\n";
        }, $filename, [
            'Content-Type'        => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function purge(Request $request, string $type)
    {
        if (!in_array(Auth::user()->role, ['administrateur'])) {
            abort(403, 'Seul un administrateur peut effectuer cette opération.');
        }

        $schemas = $this->schemas();
        abort_if(!isset($schemas[$type]), 404);
        abort_if(!($schemas[$type]['purge_ok'] ?? true), 403, 'Le vidage est désactivé pour ce type de données.');

        $request->validate([
            'confirmation' => 'required|in:CONFIRMER',
        ], [
            'confirmation.in' => 'Vous devez taper exactement CONFIRMER pour valider la suppression.',
        ]);

        $schema = $schemas[$type];
        $count  = $schema['model']::count();
        $schema['model']::truncate();

        return back()->with('purge_result_' . $type, [
            'label'   => $schema['label'],
            'deleted' => $count,
        ]);
    }

    /**
     * Export des factures : une ligne CSV par article de facture (entête répétée sur chaque ligne).
     */
    private function exportFactures()
    {
        $factures = Facture::with('ElementFactures')->orderBy('id')->get();
        $cols     = $this->schemas()['factures']['cols'];
        $filename = 'factures_export_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($factures, $cols) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, array_merge(['facture_id', 'element_id'], $cols), ';');

            foreach ($factures as $facture) {
                $header = [
                    'facture_code'    => $facture->code,
                    'date'            => optional($facture->date)->format('Y-m-d'),
                    'reference'       => $facture->reference,
                    'statut'          => $facture->statut,
                    'remise_1'        => $facture->remise_1,
                    'remise_2'        => $facture->remise_2,
                    'contribuable_id' => $facture->contribuable_id,
                    'service_id'      => $facture->service_id,
                    'typefacture_id'  => $facture->typefacture_id,
                    'modepaie_id'     => $facture->modepaie_id,
                    'bic_id'          => $facture->bic_id,
                ];

                $elements = $facture->ElementFactures;

                if ($elements->isEmpty()) {
                    $row = array_merge(['facture_id' => $facture->id, 'element_id' => ''], $header, [
                        'produit_id'    => '',
                        'quantite'      => '',
                        'prix_unitaire' => '',
                        'designation'   => '',
                    ]);
                    fputcsv($out, array_values($row), ';');
                    continue;
                }

                foreach ($elements as $el) {
                    $row = array_merge(['facture_id' => $facture->id, 'element_id' => $el->id], $header, [
                        'produit_id'    => $el->produits_id,
                        'quantite'      => $el->quantite,
                        'prix_unitaire' => $el->prix_unitaire,
                        'designation'   => $el->designation,
                    ]);
                    fputcsv($out, array_values($row), ';');
                }
            }

            fclose($out);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Modèle CSV factures : exemple d'une facture à 2 lignes (même facture_code répété).
     */
    private function templateFactures()
    {
        $cols     = $this->schemas()['factures']['cols'];
        $filename = 'modele_factures.csv';

        return response()->streamDownload(function () use ($cols) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, $cols, ';');
            fputcsv($out, ['FAC-00001/2025', '2025-01-15', 'Réf. historique', 'soldée', 0, 0, 1, 1, 1, 1, 1, 3, 2, 15000, 'Article 1'], ';');
            fputcsv($out, ['FAC-00001/2025', '2025-01-15', 'Réf. historique', 'soldée', 0, 0, 1, 1, 1, 1, 1, 5, 1, 8000, 'Article 2'], ';');
            fclose($out);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Import des factures depuis un CSV (entête + lignes).
     * Chaque ligne du fichier doit répéter le même facture_code pour appartenir à la même facture.
     * Une facture dont le code existe déjà est ignorée (aucune mise à jour, aucun doublon de lignes).
     * Le stock des produits n'est volontairement pas décrémenté (reprise de données historiques).
     */
    private function importFactures(Request $request)
    {
        $request->validate([
            'fichier_factures' => 'required|file|max:10240',
        ], [
            'fichier_factures.required' => 'Veuillez sélectionner un fichier CSV.',
            'fichier_factures.file'     => 'Le fichier est invalide.',
            'fichier_factures.max'      => 'Le fichier ne doit pas dépasser 10 Mo.',
        ]);

        $cols   = $this->schemas()['factures']['cols'];
        $file   = $request->file('fichier_factures');
        $handle = fopen($file->getRealPath(), 'r');

        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            fseek($handle, 0);
        }

        $header = fgetcsv($handle, 0, ';');
        if (!$header || count($header) === 0) {
            fclose($handle);
            return back()->with('import_error_factures', 'Le fichier est vide ou son format est invalide. Utilisez le modèle CSV fourni.');
        }
        $header = array_map('trim', $header);

        $missing = array_diff($cols, $header);
        if (!empty($missing)) {
            fclose($handle);
            return back()->with(
                'import_error_factures',
                'Colonnes manquantes dans le fichier : ' . implode(', ', $missing) . '. Téléchargez le modèle CSV pour respecter le format attendu.'
            );
        }

        $imported = 0; // factures créées
        $lines    = 0; // lignes créées
        $skipped  = 0;
        $errors   = [];
        $rowNum   = 1;

        $currentCode    = null;
        $currentFacture = null;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $rowNum++;

            $data = [];
            foreach ($header as $i => $col) {
                if (!in_array($col, $cols)) {
                    continue;
                }
                $val        = isset($row[$i]) ? trim($row[$i]) : '';
                $data[$col] = ($val === '' || $val === 'NULL') ? null : $val;
            }

            if (empty(array_filter($data))) {
                continue;
            }

            $code = $data['facture_code'] ?? null;

            try {
                if ($code === null || $code !== $currentCode) {
                    if ($code !== null && Facture::where('code', $code)->exists()) {
                        $currentFacture = null;
                        $currentCode    = $code;
                        $skipped++;
                        $errors[] = "Ligne {$rowNum} : la facture « {$code} » existe déjà, ligne ignorée.";
                        continue;
                    }

                    $finalCode = $code;
                    if ($finalCode === null) {
                        $year        = $data['date'] ? date('Y', strtotime($data['date'])) : date('Y');
                        $lastFacture = Facture::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
                        $nextNumber  = ($lastFacture ? intval(substr($lastFacture->code, 4, 5)) : 0) + 1;
                        $finalCode   = 'FAC-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT) . '/' . $year;
                    }

                    $currentFacture = Facture::create([
                        'code'            => $finalCode,
                        'date'            => $data['date'] ?? null,
                        'reference'       => $data['reference'] ?? null,
                        'statut'          => $data['statut'] ?? 'Brouillon',
                        'remise_1'        => $data['remise_1'] ?? 0,
                        'remise_2'        => $data['remise_2'] ?? 0,
                        'contribuable_id' => $data['contribuable_id'] ?? null,
                        'service_id'      => $data['service_id'] ?? null,
                        'typefacture_id'  => $data['typefacture_id'] ?? null,
                        'modepaie_id'     => $data['modepaie_id'] ?? null,
                        'bic_id'          => $data['bic_id'] ?? null,
                    ]);

                    $currentCode = $code;
                    $imported++;
                }

                if ($currentFacture && !empty($data['produit_id'])) {
                    ElementFacture::create([
                        'facture_id'    => $currentFacture->id,
                        'produits_id'   => $data['produit_id'],
                        'quantite'      => $data['quantite'] ?? 0,
                        'prix_unitaire' => $data['prix_unitaire'] ?? 0,
                        'designation'   => $data['designation'] ?? null,
                    ]);
                    $lines++;
                }
            } catch (\Throwable $e) {
                $skipped++;
                $errors[]       = "Ligne {$rowNum} : " . $this->friendlyError($e->getMessage());
                $currentFacture = null;
                $currentCode    = $code;
                if (count($errors) >= 10) {
                    $errors[] = '… (trop d\'erreurs, import interrompu à la ligne ' . $rowNum . ')';
                    break;
                }
            }
        }

        fclose($handle);

        return back()->with('import_result_factures', [
            'imported' => $imported,
            'updated'  => $lines,
            'skipped'  => $skipped,
            'errors'   => $errors,
        ]);
    }

    private function friendlyError(string $message): string
    {
        if (str_contains($message, 'Duplicate entry')) {
            preg_match("/Duplicate entry '(.+?)' for key '(.+?)'/", $message, $m);
            $val = $m[1] ?? '?';
            $key = $m[2] ?? '?';
            return "Doublon détecté : la valeur « {$val} » existe déjà (clé : {$key}).";
        }
        if (str_contains($message, 'foreign key constraint')) {
            return 'Référence étrangère invalide : vérifiez les IDs de catégorie, TVA ou BIC.';
        }
        return substr($message, 0, 120);
    }

    private function cleanupTmp(string $dir): void
    {
        foreach (glob($dir . DIRECTORY_SEPARATOR . '*') as $file) {
            @unlink($file);
        }
        @rmdir($dir);
    }
}
