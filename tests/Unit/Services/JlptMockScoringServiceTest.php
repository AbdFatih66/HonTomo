<?php

namespace Tests\Unit\Services;

use App\Services\JlptMockScoringService;
use PHPUnit\Framework\TestCase;

class JlptMockScoringServiceTest extends TestCase
{
    public function test_perfect_score_passes_with_full_scale(): void
    {
        $s = (new JlptMockScoringService)->score('N5', 33, 32, 24);

        $this->assertSame(['language' => 120, 'listening' => 60, 'total' => 180, 'passed' => true], $s);
    }

    public function test_zero_fails(): void
    {
        $s = (new JlptMockScoringService)->score('N5', 0, 0, 0);

        $this->assertSame(0, $s['total']);
        $this->assertFalse($s['passed']);
    }

    public function test_total_can_be_enough_but_a_section_below_minimum_still_fails(): void
    {
        $service = new JlptMockScoringService;

        // Language 37/120 (< 38) even though the total reaches 97.
        $lowLanguage = $service->score('N5', 10, 10, 24);
        $this->assertSame(37, $lowLanguage['language']);
        $this->assertSame(97, $lowLanguage['total']);
        $this->assertFalse($lowLanguage['passed']);

        // Listening 0 (< 19) even though language is perfect (total 120 >= 80).
        $noListening = $service->score('N5', 33, 32, 0);
        $this->assertSame(120, $noListening['total']);
        $this->assertFalse($noListening['passed']);
    }

    public function test_balanced_score_passes(): void
    {
        $s = (new JlptMockScoringService)->score('N5', 20, 19, 10);

        $this->assertSame(72, $s['language']);
        $this->assertSame(25, $s['listening']);
        $this->assertTrue($s['passed']);
    }

    public function test_limits_match_the_n5_question_counts(): void
    {
        $this->assertSame(['vocab' => 33, 'grammar' => 32, 'listening' => 24], (new JlptMockScoringService)->limits('N5'));
    }
}
