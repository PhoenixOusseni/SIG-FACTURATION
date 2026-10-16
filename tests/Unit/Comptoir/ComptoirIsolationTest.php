<?php

namespace Tests\Unit\Comptoir;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Tests\TestCase;

/**
 * Hard invariant: comptoir domain must not import SFE/MCF signing stack.
 */
class ComptoirIsolationTest extends TestCase
{
    public function test_comptoir_php_sources_do_not_import_sfe_services(): void
    {
        $roots = [
            base_path('app/Services/Comptoir'),
            base_path('app/Http/Controllers/Comptoir'),
        ];

        $forbidden = [
            'App\\Services\\SFE',
            'InvoiceSigningService',
            'BridgeClient',
            'VirtualMcfService',
            'McfController',
        ];

        $offenders = [];

        foreach ($roots as $root) {
            if (!is_dir($root)) {
                $this->markTestSkipped('Comptoir source directory missing: ' . $root);
            }

            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
            $files = new RegexIterator($iterator, '/^.+\.php$/i', RegexIterator::GET_MATCH);

            foreach ($files as $file) {
                $path = $file[0];
                $src = file_get_contents($path);
                // Strip block comments that document the ban
                $code = preg_replace('#/\*.*?\*/#s', '', $src);
                $code = preg_replace('#//.*$#m', '', $code);

                foreach ($forbidden as $needle) {
                    if (strpos($code, $needle) !== false) {
                        $offenders[] = basename($path) . ' contains ' . $needle;
                    }
                }
            }
        }

        $this->assertSame([], $offenders, "SFE/MCF coupling found:\n" . implode("\n", $offenders));
    }

    public function test_converter_file_exists_and_documents_no_sfe(): void
    {
        $path = base_path('app/Services/Comptoir/TicketToFactureConverter.php');
        $this->assertFileExists($path);
        $src = file_get_contents($path);
        $this->assertStringContainsString('Never call InvoiceSigningService', $src);
        $this->assertStringNotContainsString('use App\\Services\\SFE', $src);
    }
}
