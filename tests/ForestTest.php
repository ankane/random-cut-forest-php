<?php

use Tests\TestCase;

final class ForestTest extends TestCase
{
    public function testScores()
    {
        $forest = new Rcf\Forest(3);

        $scores = [];
        for ($i = 0; $i < 200; $i++) {
            $point = [];
            $point[0] = mt_rand() / mt_getrandmax();
            $point[1] = mt_rand() / mt_getrandmax();
            $point[2] = mt_rand() / mt_getrandmax();

            // make the second to last point an anomaly
            if ($i == 198) {
                $point[1] = 2;
            }

            $scores[] = $forest->score($point);
            $forest->update($point);
        }

        $this->assertEqualsWithDelta(0, $scores[0], 0.00001);
        $this->assertEqualsWithDelta(0, $scores[64], 0.00001);
        $this->assertGreaterThan(0.5, $scores[65]);
        $this->assertLessThan(1.3, $scores[197]);
        $this->assertGreaterThan(3, $scores[198]);
        $this->assertLessThan(1.2, $scores[199]);
    }

    public function testBadSize()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Bad size');

        $forest = new Rcf\Forest(3);
        $forest->score([1]);
    }
}
