<?php

namespace Tests\Unit\SFE;

use App\Services\SFE\SfeInvoiceType;
use PHPUnit\Framework\TestCase;

class SfeInvoiceTypeTest extends TestCase
{
    public function test_bridge_mapping_for_sales_and_credits()
    {
        $this->assertSame('FV', SfeInvoiceType::toBridgeType('FV'));
        $this->assertSame('FV', SfeInvoiceType::toBridgeType('FT'));
        $this->assertSame('EV', SfeInvoiceType::toBridgeType('EV'));
        $this->assertSame('EV', SfeInvoiceType::toBridgeType('ET'));
        $this->assertSame('FA', SfeInvoiceType::toBridgeType('FA'));
        $this->assertSame('EA', SfeInvoiceType::toBridgeType('EA'));
    }

    public function test_export_and_acompte_flags()
    {
        $this->assertTrue(SfeInvoiceType::isExport('EV'));
        $this->assertTrue(SfeInvoiceType::isExport('ET'));
        $this->assertTrue(SfeInvoiceType::isExport('EA'));
        $this->assertFalse(SfeInvoiceType::isExport('FV'));
        $this->assertTrue(SfeInvoiceType::isAcompte('FT'));
        $this->assertTrue(SfeInvoiceType::isAcompte('ET'));
        $this->assertFalse(SfeInvoiceType::isAcompte('FV'));
    }

    public function test_credit_type_from_original()
    {
        $this->assertSame('FA', SfeInvoiceType::creditTypeFromOriginal('FV'));
        $this->assertSame('FA', SfeInvoiceType::creditTypeFromOriginal('FT'));
        $this->assertSame('EA', SfeInvoiceType::creditTypeFromOriginal('EV'));
        $this->assertSame('EA', SfeInvoiceType::creditTypeFromOriginal('ET'));
    }

    public function test_motif_to_nature_mapping()
    {
        $this->assertSame('COR', SfeInvoiceType::natureFromMotif('erreur'));
        $this->assertSame('RAN', SfeInvoiceType::natureFromMotif('annulation'));
        $this->assertSame('RAM', SfeInvoiceType::natureFromMotif('retour'));
        $this->assertSame('RAM', SfeInvoiceType::natureFromMotif('produit_defectueux'));
        $this->assertSame('RRR', SfeInvoiceType::natureFromMotif('remise'));
    }
}
