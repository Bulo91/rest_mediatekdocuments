<?php
declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../MyAccessBDD.php';

final class TestableMyAccessBDD extends MyAccessBDD
{
    public function __construct()
    {
        // On neutralise le constructeur parent pour éviter la connexion BDD
    }

    public function parutionDansAbonnementForTest(string $dateCommande, string $dateFinAbonnement, string $dateParution): bool
    {
        return $this->ParutionDansAbonnement($dateCommande, $dateFinAbonnement, $dateParution);
    }
}

final class ParutionDansAbonnementTest extends TestCase
{
    #[DataProvider('provideCases')]
    public function testParutionDansAbonnement(string $dateCommande, string $dateFinAbonnement, string $dateParution, bool $expected): void
    {
        $sut = new TestableMyAccessBDD();
        self::assertSame($expected, $sut->parutionDansAbonnementForTest($dateCommande, $dateFinAbonnement, $dateParution));
    }

    public static function provideCases(): array
    {
        return [
            ['2025-01-01', '2025-01-31', '2025-01-15', true],
            ['2025-01-01', '2025-01-31', '2025-01-01', true],
            ['2025-01-01', '2025-01-31', '2025-01-31', true],
            ['2025-01-01', '2025-01-31', '2024-12-31', false],
            ['2025-01-01', '2025-01-31', '2025-02-01', false],
            ['2025-01-01', '2025-01-31', 'date-invalide', false],
        ];
    }
}