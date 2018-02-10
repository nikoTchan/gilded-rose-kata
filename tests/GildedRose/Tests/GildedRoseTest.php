<?php

namespace GildedRose\Tests;
use GildedRose\Item;
use GildedRose\Objects\Dexterity;
use GildedRose\Objects\ItemInterface;
use GildedRose\Program;
use PHPUnit\Framework\TestCase;

class GildedRoseTest extends TestCase
{
    public function testOutputIsTheExpected()
    {
        $items = [
            new Dexterity(['name' => ItemInterface::DEXTERITY, 'sellIn' => 10, 'quality' => 20]),
            new Item(['name' => ItemInterface::AGED_BRIE, 'sellIn' => 2, 'quality' => 0]),
            new Item(['name' => ItemInterface::ELIXIR, 'sellIn' => 5, 'quality' => 7]),
            new Item(['name' => ItemInterface::SULFURAS, 'sellIn' => 0, 'quality' => 80]),
            new Item(
                [
                    'name'    => ItemInterface::BACKSTAGE,
                    'sellIn'  => 15,
                    'quality' => 20
                ]
            ),
            new Item(['name' => ItemInterface::CAKE, 'sellIn' => 3, 'quality' => 6]),
        ];
        $expected = 'HELLO
                                              Name -  SellIn - Quality
                                 +5 Dexterity Vest -       9 -      19
                                         Aged Brie -       1 -       1
                            Elixir of the Mongoose -       4 -       6
                        Sulfuras, Hand of Ragnaros -       0 -      80
         Backstage passes to a TAFKAL80ETC concert -      14 -      21
                                Conjured Mana Cake -       2 -       5
';

        $app = new Program($items);
        $app->Main();

        $expected2 = 'HELLO
                                              Name -  SellIn - Quality
                                 +5 Dexterity Vest -       8 -      18
                                         Aged Brie -       0 -       2
                            Elixir of the Mongoose -       3 -       5
                        Sulfuras, Hand of Ragnaros -       0 -      80
         Backstage passes to a TAFKAL80ETC concert -      13 -      22
                                Conjured Mana Cake -       1 -       4
';
        $app->Main();
        $this->expectOutputString($expected.$expected2);
    }

    public function testNeverExceedsMaxQuality()
    {
        $items = [
            new Item(['name' => ItemInterface::AGED_BRIE, 'sellIn' => 2, 'quality' => 49]),
            new Item(['name' => ItemInterface::BACKSTAGE, 'sellIn' => 15, 'quality' => 49]),
        ];
        $expected
               = 'HELLO
                                              Name -  SellIn - Quality
                                         Aged Brie -       1 -      50
         Backstage passes to a TAFKAL80ETC concert -      14 -      50
';

        $app = new Program($items);
        $app->Main();

        $expected2
            = 'HELLO
                                              Name -  SellIn - Quality
                                         Aged Brie -       0 -      50
         Backstage passes to a TAFKAL80ETC concert -      13 -      50
';
        $app->Main();
        $this->expectOutputString($expected.$expected2);
    }

    public function testBackstageDoubleQualityWhenAreTenDaysOrLess()
    {
        $items = [
            new Item(['name' => ItemInterface::BACKSTAGE, 'sellIn' => 11, 'quality' => 40]),
        ];
        $expected
               = 'HELLO
                                              Name -  SellIn - Quality
         Backstage passes to a TAFKAL80ETC concert -      10 -      41
';

        $app = new Program($items);
        $app->Main();

        $expected2
            = 'HELLO
                                              Name -  SellIn - Quality
         Backstage passes to a TAFKAL80ETC concert -       9 -      43
';
        $app->Main();
        $this->expectOutputString($expected.$expected2);
    }

    public function testBackstageTripleQualityWhenAreFiveDaysOrLess()
    {
        $items = [
            new Item(['name' => ItemInterface::BACKSTAGE, 'sellIn' => 6, 'quality' => 40]),
        ];
        $expected
               = 'HELLO
                                              Name -  SellIn - Quality
         Backstage passes to a TAFKAL80ETC concert -       5 -      42
';

        $app = new Program($items);
        $app->Main();

        $expected2
            = 'HELLO
                                              Name -  SellIn - Quality
         Backstage passes to a TAFKAL80ETC concert -       4 -      45
';
        $app->Main();
        $this->expectOutputString($expected.$expected2);
    }

    public function testBackstageQualityDropsToZeroAfterTheConcert()
    {
        $items = [
            new Item(['name' => ItemInterface::BACKSTAGE, 'sellIn' => 1, 'quality' => 40]),
        ];
        $expected
               = 'HELLO
                                              Name -  SellIn - Quality
         Backstage passes to a TAFKAL80ETC concert -       0 -      43
';

        $app = new Program($items);
        $app->Main();

        $expected2
            = 'HELLO
                                              Name -  SellIn - Quality
         Backstage passes to a TAFKAL80ETC concert -      -1 -       0
';
        $app->Main();
        $this->expectOutputString($expected.$expected2);
    }

    public function testConjuredItemsDecreaseDoubleQualityWhenExpired()
    {
        $items = [
            new Item(['name' => ItemInterface::DEXTERITY, 'sellIn' => 1, 'quality' => 40]),
            new Item(['name' => ItemInterface::CAKE, 'sellIn' => 1, 'quality' => 20]),
        ];
        $expected
               = 'HELLO
                                              Name -  SellIn - Quality
                                 +5 Dexterity Vest -       0 -      39
                                Conjured Mana Cake -       0 -      19
';

        $app = new Program($items);
        $app->Main();

        $expected2
            = 'HELLO
                                              Name -  SellIn - Quality
                                 +5 Dexterity Vest -      -1 -      37
                                Conjured Mana Cake -      -1 -      17
';
        $app->Main();
        $this->expectOutputString($expected.$expected2);
    }

    public function testDexterity()
    {
        $dexterity = new Dexterity(['name' => ItemInterface::DEXTERITY, 'sellIn' => 10, 'quality' => 20]);
        $dexterity->updateQuality();
        $this->assertEquals(19, $dexterity->quality);
        $this->assertEquals(9, $dexterity->sellIn);
        $dexterity->updateQuality();
        $this->assertEquals(18, $dexterity->quality);
        $this->assertEquals(8, $dexterity->sellIn);
    }

    public function testDexterityDecreaseTwiceWhenHasNotSellIn()
    {
        $dexterity = new Dexterity(['name' => ItemInterface::DEXTERITY, 'sellIn' => 0, 'quality' => 20]);
        $dexterity->updateQuality();
        $this->assertEquals(18, $dexterity->quality);
        $this->assertEquals(-1, $dexterity->sellIn);
    }
}
