<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CartServiceTest extends TestCase
{
    private CartService $service;

    protected function setUp(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $this->service = new CartService($em);
    }

    public function testEmptyCartReturnsZero(): void
    {
        $user = $this->createStub(User::class);
        $cart = new Cart($user);

        $this->assertSame(0.0, $this->service->getTotal($cart));
    }

    public function testSingleItemReturnsCorrectTotal(): void
    {
        $user = $this->createStub(User::class);
        $cart = new Cart($user);

        $product = new Product();
        $product->setName('Catan');
        $product->setPrice(25.00);

        $item = new CartItem($product);
        $item->setQuantity(1);
        $item->setUnitPrice(25.00);
        $cart->addItem($item);

        $this->assertSame(25.00, $this->service->getTotal($cart));
    }

    public function testMultipleItems(): void 
    {
        $user = $this->createStub(User::class);
        $cart = new Cart($user);

        $product = new Product();
        $product->setName('Catan');
        $product->setPrice(25.00);

        $product2 = new Product();
        $product2->setName('Catan2');
        $product2->setPrice(34.00);

        $item = new CartItem($product);
        $item->setQuantity(1);
        $item->setUnitPrice(25.00);
        $item2 = new CartItem($product2);
        $item2->setQuantity(1);
        $item2->setUnitPrice(34.00);

        $cart->addItem($item);
        $cart->addItem($item2);

        $this->assertSame(59.00, $this->service->getTotal($cart));
    }

    public function testQuantityMultiplier(): void 
    {
        $user = $this->createStub(User::class);
        $cart = new Cart($user);

        $product = new Product();
        $product->setName('Catan');
        $product->setPrice(25.00);

        $item = new CartItem($product);
        $item->setQuantity(3);
        $item->setUnitPrice(25.00);

        $cart->addItem($item);

        $this->assertSame(75.00, $this->service->getTotal($cart));
    }
}
