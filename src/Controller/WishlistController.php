<?php

namespace App\Controller;

use App\Entity\Wishlist;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/api/wishlist", name="wishlist_")
 */
class WishlistController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/add", name="add", methods={"POST"})
     */
    public function addToWishlist(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();

        // Vérifiez si $data est valide et contient 'product_id'
        if (!isset($data['product_id'])) {
            return $this->json(['message' => 'Product ID is required'], 400);
        }

        $product = $this->em->getRepository(Product::class)->find($data['product_id']);

        if (!$product) {
            return $this->json(['message' => 'Product not found'], 404);
        }

        // Ajouter le produit à la liste d'envies
        $wishlistItem = new Wishlist();
        $wishlistItem->setUser($user);
        $wishlistItem->setProduct($product);

        $this->em->persist($wishlistItem);
        $this->em->flush();

        return $this->json(['message' => 'Product added to wishlist'], 201);
    }

    /**
     * @Route("/", name="get", methods={"GET"})
     */
    public function getWishlist(): JsonResponse
    {
        $user = $this->getUser(); // Récupère l'utilisateur actuellement authentifié
        $wishlistItems = $this->em->getRepository(Wishlist::class)->findBy(['user' => $user]);

        if (!$wishlistItems) {
            return $this->json(['message' => 'Your wishlist is empty'], 404);
        }

        return $this->json($wishlistItems);
    }

    /**
     * @Route("/remove/{id}", name="remove", methods={"DELETE"})
     */
    public function removeFromWishlist(int $id): JsonResponse
    {
        $user = $this->getUser(); // Récupère l'utilisateur actuellement authentifié
        $wishlistItem = $this->em->getRepository(Wishlist::class)->findOneBy([
            'user' => $user,
            'id' => $id
        ]);

        if (!$wishlistItem) {
            return $this->json(['message' => 'Wishlist item not found'], 404);
        }

        // Supprimer le produit de la liste d'envies
        $this->em->remove($wishlistItem);
        $this->em->flush();

        return $this->json(['message' => 'Product removed from wishlist'], 200);
    }
}
