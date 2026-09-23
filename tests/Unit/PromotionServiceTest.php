<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Product;
use App\Service\PromotionService;
use PHPUnit\Framework\TestCase;

class PromotionServiceTest extends TestCase
{
    private PromotionService $service;

    protected function setUp(): void
    {
        $this->service = new PromotionService();
    }

    public function testReturnsNormalPriceWhenNoPromotion(): void
    {
        $product = $this->createProduct(50.00);

        $this->assertSame(50.00, $this->service->getCurrentPrice($product));
        $this->assertFalse($this->service->isOnPromotion($product));
    }

    public function testReturnsPromoPriceDuringPeriod(): void
    {
        $product = $this->createProduct(50.00);

        $product->setPromoPrice(40.00);
        $product->setPromoStartsAt(new \DateTimeImmutable('-1 day'));
        $product->setPromoEndsAt(new \DateTimeImmutable('+1 day'));

        $this->assertSame(40.00, $this->service->getCurrentPrice($product));
        $this->assertTrue($this->service->isOnPromotion($product));
    }

    public function testReturnsNormalPriceBeforePromotionPeriod(): void
    {
        $product = $this->createProduct(50.00);

        $product->setPromoPrice(40.00);
        $product->setPromoStartsAt(new \DateTimeImmutable('+1 day'));
        $product->setPromoEndsAt(new \DateTimeImmutable('+3 days'));

        $this->assertSame(50.00, $this->service->getCurrentPrice($product));
        $this->assertFalse($this->service->isOnPromotion($product));
    }

    public function testReturnsNormalPriceAfterPromotionPeriod(): void
    {
        $product = $this->createProduct(50.00);

        $product->setPromoPrice(40.00);
        $product->setPromoStartsAt(new \DateTimeImmutable('-3 days'));
        $product->setPromoEndsAt(new \DateTimeImmutable('-1 day'));

        $this->assertSame(50.00, $this->service->getCurrentPrice($product));
        $this->assertFalse($this->service->isOnPromotion($product));
    }

    public function testPromoPriceEqualToNormalIsNotActive(): void
    {
        $product = $this->createProduct(50.00);

        $product->setPromoPrice(50.00);
        $product->setPromoStartsAt(new \DateTimeImmutable('-1 day'));
        $product->setPromoEndsAt(new \DateTimeImmutable('+1 day'));

        $this->assertSame(50.00, $this->service->getCurrentPrice($product));
        $this->assertFalse($this->service->isOnPromotion($product));
    }

    public function testPromoPriceGreaterThanNormalIsNotActive(): void
    {
        $product = $this->createProduct(50.00);

        $product->setPromoPrice(60.00);
        $product->setPromoStartsAt(new \DateTimeImmutable('-1 day'));
        $product->setPromoEndsAt(new \DateTimeImmutable('+1 day'));

        $this->assertSame(50.00, $this->service->getCurrentPrice($product));
        $this->assertFalse($this->service->isOnPromotion($product));
    }

    public function testInvertedDatesAreNotActive(): void
    {
        $product = $this->createProduct(50.00);

        $product->setPromoPrice(40.00);
        $product->setPromoStartsAt(new \DateTimeImmutable('+7 days'));
        $product->setPromoEndsAt(new \DateTimeImmutable('+3 days'));

        $this->assertSame(50.00, $this->service->getCurrentPrice($product));
        $this->assertFalse($this->service->isOnPromotion($product));
    }

    public function testBoundaryStartIsIncluded(): void
    {
        $product = $this->createProduct(50.00);

        $product->setPromoPrice(40.00);
        $product->setPromoStartsAt(new \DateTimeImmutable());
        $product->setPromoEndsAt(new \DateTimeImmutable('+3 days'));

        $this->assertSame(40.00, $this->service->getCurrentPrice($product));
        $this->assertTrue($this->service->isOnPromotion($product));
    }

    public function testBoundaryEndIsIncluded(): void
    {
        $product = $this->createProduct(50.00);
        $now = new \DateTimeImmutable('2026-09-15 12:00:00');

        $product->setPromoPrice(40.00);
        $product->setPromoStartsAt(new \DateTimeImmutable('2026-09-12 12:00:00'));
        $product->setPromoEndsAt($now);

        $this->assertSame(40.00, $this->service->getCurrentPrice($product, $now));
        $this->assertTrue($this->service->isOnPromotion($product, $now));
    }

    private function createProduct(float $price, ?float $promoPrice = null): Product
    {
        $product = new Product();
        $product->setName('Test Product')->setPrice($price);

        if (null !== $promoPrice) {
            $product->setPromoPrice($promoPrice);
            $product->setPromoStartsAt(new \DateTimeImmutable('2026-08-01 00:00:00'));
            $product->setPromoEndsAt(new \DateTimeImmutable('2026-08-31 23:59:59'));
        }

        return $product;
    }
}
