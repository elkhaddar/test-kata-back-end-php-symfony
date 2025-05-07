<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/api/products", name="product_")
 */
class ProductController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function getAll(): JsonResponse
    {
        $products = $this->em->getRepository(Product::class)->findAll();
        return $this->json($products);
    }

    /**
     * @Route("/create", name="create", methods={"POST"})
     */
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = json_decode($request->getContent(), true);
        $product = new Product();
        $product->setCode($data['code']);
        $product->setName($data['name']);
        $product->setDescription($data['description']);
        $product->setImage($data['image']);
        $product->setCategory($data['category']);
        $product->setPrice($data['price']);
        $product->setQuantity($data['quantity']);
        $product->setInternalReference($data['internalReference']);
        $product->setShellId($data['shellId']);
        $product->setInventoryStatus($data['inventoryStatus']); 
        $product->setRating($data['rating']);
        $product->setCreatedAt(new \DateTime());
        $product->setUpdatedAt(new \DateTime());

        $this->em->persist($product);
        $this->em->flush();

        return $this->json($product, 201);
    }

    /**
     * @Route("/{id}", name="get", methods={"GET"})
     */
    public function getProduct(int $id): JsonResponse
    {
        $product = $this->em->getRepository(Product::class)->find($id);

        if (!$product) {
            return $this->json(['message' => 'Product not found'], 404);
        }

        return $this->json($product);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     */
    public function delete(int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $product = $this->em->getRepository(Product::class)->find($id);

        if (!$product) {
            return $this->json(['message' => 'Product not found'], 404);
        }

        $this->em->remove($product);
        $this->em->flush();

        return $this->json(['message' => 'Product deleted'], 200);
    }

    /**
     * @Route("/{id}", name="update", methods={"PATCH"})
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $product = $this->em->getRepository(Product::class)->find($id);

        if (!$product) {
            return $this->json(['message' => 'Product not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        
        if (isset($data['code'])) {
            $product->setCode($data['code']);
        }
        if (isset($data['name'])) {
            $product->setName($data['name']);
        }
        if (isset($data['description'])) {
            $product->setDescription($data['description']);
        }
        if (isset($data['image'])) {
            $product->setImage($data['image']);
        }
        if (isset($data['category'])) {
            $product->setCategory($data['category']);
        }
        if (isset($data['price'])) {
            $product->setPrice($data['price']);
        }
        if (isset($data['quantity'])) {
            $product->setQuantity($data['quantity']);
        }
        if (isset($data['internalReference'])) {
            $product->setInternalReference($data['internalReference']);
        }
        if (isset($data['shellId'])) {
            $product->setShellId($data['shellId']);
        }
        if (isset($data['inventoryStatus'])) {
            $product->setInventoryStatus($data['inventoryStatus']);
        }
        if (isset($data['rating'])) {
            $product->setRating($data['rating']);
        }
        $product->setUpdatedAt(new \DateTime());

        $this->em->flush();

        return $this->json($product);
    }
}
