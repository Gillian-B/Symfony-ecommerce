<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/api/register", name="security_registration", methods={"POST"})
     */
    public function registration(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator, UserPasswordEncoderInterface $encoder) {
        $data = $request->getContent();

        try {
            $data2 = $serializer->deserialize($data, User::class, 'json');

            $errors = $validator->validate($data2);

            if(count($errors) > 0) {
                return $this->json($errors, 400, []);
            }
            $hash = $encoder->encodePassword($data2, $data2->getPassword());
            $data2->setPassword($hash);

            $em->persist($data2);
            $em->flush();
            
            $data2->setPassword("");
            return $this->json($data2, 201, []);
        } catch(Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400, []);
        }
    }

    /**
     * @Route("/api/login", name="security_login", methods={"POST"})
     */
    public function login(Request $request) {
        $data = $request->getContent();
        try {
            $user = $this->getUser();
            if($user) {
                return $this->json(['token' => $user->getId()], 200, []); // ['token' => 'XXXXXXXXXX'] token dans bdd ? hash ? id pour test
            }    
        } catch(Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400, []);
        }
        
    }

    /**
     * @Route("/api/user", name="security_getcurrentuser", methods={"GET"})
     */
    public function getCurrentUser(Request $request, UserRepository $userRepository) {
        $headerData = $request->headers->get('authorization');
        if(!$headerData) {
            return $this->json(['error' => 'Not authorized'], 403, []);
        }
        $dbData = $userRepository->findOneBy(['id' => $headerData]);
        if (!$dbData) {
            return $this->json(['error' => 'Not authorized'], 403, []);
        }
        $dbData->setPassword("");
        return $this->json($dbData, 200, []);
    }

    /**
     * @Route("api/user", name="security_updatecurrentuser", methods={"PUT"})
     */
    public function updateCurrentUser(Request $request, UserRepository $userRepository, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator, UserPasswordEncoderInterface $encoder) {
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
            $data2 = $serializer->deserialize($data, User::class, 'json');
            
            $hash = $encoder->encodePassword($data2, $data2->getPassword());
            $data2->setPassword($hash);

            $dbData->setLogin($data2->getlogin());
            $dbData->setPassword($data2->getPassword());
            $dbData->setEmail($data2->getEmail());
            $dbData->setFirstname($data2->getFirstname());
            $dbData->setLastname($data2->getLastname());

            $errors = $validator->validate($dbData);

            if(count($errors) > 0) {
                return $this->json($errors, 400);
            }

            $em->flush();

            $dbData->setPassword("");
            return $this->json($dbData, 200, []);
        } catch(Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400, []);
        }
    }

    //gestion token -> md5(email+random_byte(10) ?
    //PUT avec json incomplet erreur a gerer
}
