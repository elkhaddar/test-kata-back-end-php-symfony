<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/api/cart", name="cart_")
 */
class CartController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/add", name="add", methods={"POST"})
     */
    public function addToCart(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();
        $product = $this->em->getRepository(Product::class)->find($data['product_id']);

        if (!$product) {
            return $this->json(['message' => 'Product not found'], 404);
        }

        $cartItem = new Cart();
        $cartItem->setUser($user);
        $cartItem->setProduct($product);
        $cartItem->setQuantity($data['quantity']);

        $this->em->persist($cartItem);
        $this->em->flush();

        return $this->json(['message' => 'Product added to cart'], 201);
    }

    /**
     * @Route("/", name="get", methods={"GET"})
     */
    public function getCart(): JsonResponse
    {
        $user = $this->getUser();
        $cartItems = $this->em->getRepository(Cart::class)->findBy(['user' => $user]);

        return $this->json($cartItems);
    }
}

