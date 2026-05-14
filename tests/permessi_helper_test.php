<?php declare(strict_types=1);

require_once __DIR__ . '/../LOGIN_DATABASE/permessi_helper.php';

use PHPUnit\Framework\TestCase;

final class PermessiHelperTest extends TestCase
{
    public function testNormalizzaPermesso(): void
    {
        $this->assertSame('manage_products', normalizzaPermesso('upload_product'));
        $this->assertNull(normalizzaPermesso('send_message'));
        $this->assertSame('custom_permission', normalizzaPermesso('custom_permission'));
    }
}