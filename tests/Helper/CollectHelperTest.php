<?php
/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace App\Tests\Helper;

use App\Helper\CollectHelper;
use PHPUnit\Framework\TestCase;

class CollectHelperTest extends TestCase
{
    protected $collectHelper;

    protected function setUp()
    {
        $this->collectHelper = new CollectHelper();
    }

    /**
     * @return array|\stdClass[]
     */
    public function generateFriends(): array
    {
        $friends = [
            'Markos',
            'Celinda',
            'Walker',
            'Mallissa',
            'Celisse',
            'Jerry',
            'Milissent',
            'Gabie',
            'Kenneth',
            'Husein',
            'Lutero',
            'Bert',
            'Brnaba',
            'Trixy',
            'Alane',
            'Leonore',
            'Brandice',
            'Aubine',
            'Lindsey',
            'Dedie',
            'Patrizia',
            'Lancelot',
            'Austen',
            'Guinna',
            'Joyce',
            'Anneliese'
        ];
        $friends = array_map(
            function ($name) {
                $user = new \stdClass();
                $user->screen_name = $name;
                return $user;
            },
            $friends
        );
        return $friends;
    }

    public function testQueryGeneration()
    {
        $friends = $this->generateFriends();
        $maxLength = 100;

        $expected = [
            'from:Markos OR from:Celinda OR from:Walker OR from:Mallissa OR from:Celisse OR from:Jerry',
            'from:Milissent OR from:Gabie OR from:Kenneth OR from:Husein OR from:Lutero OR from:Bert',
            'from:Brnaba OR from:Trixy OR from:Alane OR from:Leonore OR from:Brandice OR from:Aubine',
            'from:Lindsey OR from:Dedie OR from:Patrizia OR from:Lancelot OR from:Austen OR from:Guinna',
            'from:Joyce OR from:Anneliese'
        ];

        $fromQueries = $this->collectHelper->generateFromQueries($friends, $maxLength);

        foreach ($expected as $i => $expectedQuery) {
            $this->assertArrayHasKey($i, $fromQueries);
            $this->assertSame($expectedQuery, $fromQueries[$i]);
        }
    }

    /**
     * @dataProvider provideMaxLengths
     * @param int $maxLength
     */
    public function testMaxQueryLength(int $maxLength)
    {
        $friends = $this->generateFriends();

        $fromQueries = $this->collectHelper->generateFromQueries($friends, $maxLength);

        foreach ($fromQueries as $fromQuery) {
            $length = strlen($fromQuery);
            $this->assertLessThanOrEqual($maxLength, $length);
        }
    }

    /**
     *
     * @return array|int[]
     */
    public function provideMaxLengths(): array
    {
        return [
            [28],
            [50],
            [100],
            [75]
        ];
    }
}
