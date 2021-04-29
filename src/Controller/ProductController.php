<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\UserRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    /**
     * @Route("/api/products", name="product_getall", methods={"GET"})
     */
    public function getAllProducts(ProductRepository $productRepository) {
        return $this->json($productRepository->findAll(), 200, []);
    }

    /**
     * @Route("api/product/{productId}", name="product_getbyid", methods={"GET"})
     */
    public function getProductById(int $productId, ProductRepository $productRepository) {
        $data = $productRepository->findOneBy(['id' => $productId]);
        if(!$data) {
            return $this->json(['error' => 'Product not found'], 404, []);
        }

        return $this->json($data, 200, []);
    }

    /**
     * @Route("api/product", name="product_add", methods={"POST"})
     */
    public function addProduct(Request $request, UserRepository $userRepository, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator) {
        $headerData = $request->headers->get('authorization');
        if(!$headerData) {
            return $this->json(['error' => 'Not authorized'], 403, []);
        }
        $dbData = $userRepository->findOneBy(['id' => $headerData]);
        if (!$dbData) {
            return $this->json(['error' => 'Not authorized'], 403, []);
        }

        $data = $request->getContent();

        try {
            $data2 = $serializer->deserialize($data, Product::class, 'json');

            $errors = $validator->validate($data2);

            if(count($errors) > 0) {
                return $this->json($errors, 400, []);
            }

            $em->persist($data2);
            $em->flush();

            return $this->json($data2, 201, []);
        } catch(Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400, []);
        }
    }

    /**
     * @Route("api/product/{productId}", name="product_modify", methods={"PUT"})
     */
    public function modifyProduct(int $productId, Request $request, ProductRepository $productRepository, UserRepository $userRepository, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator) {
        $headerData = $request->headers->get('authorization');
        if(!$headerData) {
            return $this->json(['error' => 'Not authorized'], 403, []);
        }
        $dbData = $userRepository->findOneBy(['id' => $headerData]);
        if (!$dbData) {
            return $this->json(['error' => 'Not authorized'], 403, []);
        }

        $data = $request->getContent();
        $dbprodData = $productRepository->findOneBy(['id' => $productId]);
        if(!$dbprodData) {
            return $this->json(['error' => 'Product not found'], 404, []);
        }

        try {
            $data2 = $serializer->deserialize($data, Product::class, 'json');

            $dbprodData->setName($data2->getName());
            $dbprodData->setDescription($data2->getDescription());
            $dbprodData->setPhoto($data2->getPhoto());
            $dbprodData->setPrice($data2->getPrice());

            $errors = $validator->validate($dbprodData);

            if(count($errors) > 0) {
                return $this->json($errors, 400, []);
            }

            $em->flush();

            return $this->json($dbprodData, 200, []);
        } catch(Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400, []);
        }
    }

    /**
     * @Route("api/product/{productId}", name="product_delete", methods={"DELETE"})
     */
    public function deleteProduct(int $productId, Request $request, ProductRepository $productRepository, UserRepository $userRepository, EntityManagerInterface $em) {
        $headerData = $request->headers->get('authorization');
        if(!$headerData) {
            return $this->json(['error' => 'Not authorized'], 403, []);
        }
        $dbData = $userRepository->findOneBy(['id' => $headerData]);
        if (!$dbData) {
            return $this->json(['error' => 'Not authorized'], 403, []);
        }

        $dbprodData = $productRepository->findOneBy(['id' => $productId]);
        if(!$dbprodData) {
            return $this->json(['error' => 'Product not found'], 404, []);
        }

        $em->remove($dbprodData);
        $em->flush();

        return $this->json([], 204, []);
    }

    //PUT avec json incomplet erreur a gerer
}
