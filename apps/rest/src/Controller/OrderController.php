<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Orders;

class OrderController extends AbstractFOSRestController
{
    private $customerId = 0;
    private $shippingDate;

    public function __construct()
    {
        date_default_timezone_set('Europe/Istanbul');
        $this->shippingDate = new \DateTime('+2day');
    }

    private function tokenCheck($auth)
    {
        if ($auth != null) {
            $this->user = new UserController();
            return $this->user->validToken($auth);
        } else {
            return View::create([
                'statusCode' => 10004,
                'message' => 'UNAUTHORIZED ENTRY!',
            ], Response::HTTP_UNAUTHORIZED);
        }
    }

    private function setOrderFields(Orders $order, Request $request, $orderCode = null)
    {
        if ($orderCode != null) {
            $order->setOrderCode($orderCode);
            $order->setCreateDate(new \DateTime());
            $order->setShippingDate($this->shippingDate);
        }
        $order->setCustomerId($this->customerId);
        $order->setQuantity($request->get('quantity') != null ? $request->get('quantity') : $order->getQuantity());
        $order->setAddress($request->get('address') != null ? $request->get('address') : $order->getAddress());
        $order->setProductId($request->get('productId') != null ? $request->get('productId') : $order->getProductId());
    }

    private function getByOrderCode($orderCode, $customerId)
    {
        return $this->getDoctrine()
            ->getRepository('App\Entity\Orders')
            ->findOneBy([
                'orderCode' => $orderCode,
                'customerId' => $customerId
            ]);
    }

    /**
     * @Rest\Get("/order")
     * @return View
     */
    public function getOrders(Request $request): View
    {
        $check = $this->tokenCheck($request->headers->get('authorization'));
        if ($check->getStatusCode() != 200) return $check;
        else {
            $this->customerId = json_decode(json_encode($check->getData()), true)['customer_id'];
            $orders = $this->getDoctrine()
                ->getRepository('App\Entity\Orders')
                ->findBy(['customerId' => $this->customerId]);
            if (empty($orders))
                $orders = [
                    'statusCode' => 0,
                    'message' => "You don't have order"
                ];
            return View::create($orders, Response::HTTP_OK);
        }
    }

    /**
     * @Rest\Get("/order/{orderCode}")
     * @return View
     */
    public function getOrderByCode(Request $request): View
    {
        $check = $this->tokenCheck($request->headers->get('authorization'));
        if ($check->getStatusCode() != 200) return $check;
        else {
            $this->customerId = json_decode(json_encode($check->getData()), true)['customer_id'];
            $order = $this->getByOrderCode($request->get('orderCode'), $this->customerId);
            if ($order)
                return View::create($order, Response::HTTP_OK);
            else
                return View::create([
                    'statusCode' => 10006,
                    'orderCode' => $request->get('orderCode'),
                    'message' => 'Order Not Found'
                ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @Rest\Post("/order")
     * @param Request $request
     * @return View
     */
    public function postOrder(Request $request): View
    {
        $check = $this->tokenCheck($request->headers->get('authorization'));
        if ($check->getStatusCode() != 200) return $check;
        else {
            if (empty($request->get('productId')) || empty($request->get('quantity')) || empty($request->get('address'))) {
                return View::create([
                    'statusCode' => 10002,
                    'message' => 'Parameters are missing!',
                ], Response::HTTP_FOUND);
            }
            $this->customerId = json_decode(json_encode($check->getData()), true)['customer_id'];
            $order = new Orders();
            $orderCode = uniqid('ABC');
            $this->setOrderFields($order, $request, $orderCode);
            $em = $this->getDoctrine()->getManager();
            $em->persist($order);
            $em->flush();

            return View::create([
                'statusCode' => 0,
                'orderCode' => $orderCode,
                'message' => 'Your order has been taken'
            ], Response::HTTP_OK);
        }
    }

    /**
     * @Rest\Put("/order/{orderCode}")
     * @param Request $request
     * @return View
     */
    public function putOrder(Request $request): View
    {
        $check = $this->tokenCheck($request->headers->get('authorization'));
        if ($check->getStatusCode() != 200) return $check;
        else {
            $this->customerId = json_decode(json_encode($check->getData()), true)['customer_id'];
            $order = $this->getByOrderCode($request->get('orderCode'), $this->customerId);
            if ($order) {
                $today = new \DateTime();
                $date = $order->getShippingDate()->getTimestamp() - $today->getTimestamp();
                if ($date > 0) {
                    $this->setOrderFields($order, $request);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($order);
                    $em->flush();

                    return View::create([
                        'statusCode' => 0,
                        'orderCode' => $request->get('orderCode'),
                        'message' => 'The order has been updated'
                    ], Response::HTTP_OK);
                } else {
                    return View::create([
                        'statusCode' => 10007,
                        'orderCode' => $request->get('orderCode'),
                        'message' => 'Shipment time has passed. Update failed!'
                    ], Response::HTTP_OK);
                }
            } else {
                return View::create([
                    'statusCode' => 10006,
                    'orderCode' => $request->get('orderCode'),
                    'message' => 'Order Not Found'
                ], Response::HTTP_NOT_FOUND);
            }

        }
    }

    /**
     * @Rest\Route("/order/{all}")
     * @return View
     */
    public function any(): View
    {
        return View::create([
            'statusCode' => 10005,
            'message' => 'BAD REQUEST!'
        ], Response::HTTP_BAD_REQUEST);
    }
}
