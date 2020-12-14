<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use \Firebase\JWT\JWT;

use App\Entity\Users;

class UserController extends AbstractFOSRestController
{

    private $key = 'ABC_COMPANY!!';
    private $issuer = "http://localhost:8000";
    private $audience = "http://localhost:8000";
    private $createTokenTime = '';
    private $expirationTokenTime = '';

    public function __construct()
    {
        date_default_timezone_set('Europe/Istanbul');
        $this->createTokenTime = time();
        $this->expirationTokenTime = time() + 1800;
    }

    private function getByUserCheck($userName, $password)
    {
        $user = $this->getDoctrine()
            ->getRepository('App\Entity\Users')
            ->findOneBy([
                'nickName' => $userName,
                'password' => $password
            ]);
        if ($user) return true;
        else return false;
    }

    private function createToken($name, $pass, $id)
    {
        $token = [
            'iss' => $this->issuer,
            'aud' => $this->audience,
            'iat' => $this->createTokenTime,
            'exp' => $this->expirationTokenTime,
            'data' => [
                'id' => $id,
                'nkm' => $name,
                'pwd' => $pass
            ]
        ];

        $jwt = JWT::encode($token, $this->key);
        return $jwt;
    }

    public function validToken($token)
    {
        try {
            $token = str_replace('Bearer ', '', $token);
            $decoded = JWT::decode($token, $this->key, array('HS256'));
            if (isset($decoded->data->nkm) && isset($decoded->data->pwd)) {
                return View::create([
                    'statusCode' => 0,
                    'message' => 'Token is valid.',
                    'customer_id' => $decoded->data->id,
                    '_token' => $token
                ], Response::HTTP_OK);
            } else {
                return View::create([
                    'statusCode' => 10003,
                    'message' => 'User Not Found!',
                ], Response::HTTP_UNAUTHORIZED);
            }
        } catch (\Exception $e) {
            return View::create([
                'statusCode' => 10001,
                'message' => $e->getMessage()
            ], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * @Rest\Get("/user")
     * @return View
     */
    public function getUsers(): View
    {
        $em = $this->getDoctrine()->getManager();
        $q = $em->createQuery('SELECT u.id,u.nickName,u.firstName,u.lastName,u.status FROM  App\Entity\Users u');
        $users = $q->execute();
        return View::create($users, Response::HTTP_OK);
    }

    /**
     * @Rest\Post("/user/login")
     * @return View
     */
    public function postUserLogin(Request $request): View
    {
        if (!empty($request->get('username')) && !empty($request->get('password'))) {
            $userName = strip_tags($request->get('username'));
            $password = sha1(md5(strip_tags($request->get('password'))));
        } else {
            return View::create([
                'statusCode' => 10002,
                'message' => 'Parameters are missing!',
            ], Response::HTTP_FOUND);
        }

        $user = $this->getDoctrine()
            ->getRepository('App\Entity\Users')
            ->findOneBy([
                'nickName' => $userName,
                'password' => $password
            ]);

        if ($user) {
            $_token = $this->createToken($userName, $password, $user->getId());
            return View::create([
                'statusCode' => 0,
                'userName' => $user->getNickName(),
                'expirationTime' => '30 Minutes',
                '_token' => $_token
            ], Response::HTTP_OK);
        } else {
            return View::create([
                'statusCode' => 10000,
                'message' => 'Username or Password Failed!',
                '_token' => null
            ], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * @Rest\Route("/user/{all}")
     * @return View
     */
    public function any(): View
    {
        return View::create([
            'statusCode' => 10005,
            'message' => 'BAD REQUEST!'
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Rest\Route("/")
     * @return View
     */
    public function anyAll(): View
    {
        return View::create([
            'statusCode' => 10005,
            'message' => 'BAD REQUEST!'
        ], Response::HTTP_BAD_REQUEST);
    }
}
