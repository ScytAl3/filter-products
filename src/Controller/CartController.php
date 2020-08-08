<?php

namespace App\Controller;

use App\Service\Cart\CartService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CartController extends AbstractController
{
    /**
     * @Route("/cart", name="app_cart_index", methods={"GET"})
     */
    public function index(CartService $cartService)
    {
        // Appelle de la méthode qui retourne les informations associées au produit du panier
        $cartProductData = $cartService->getDataCart();
        // Appelle de la méthode qui calcul le nombre total de produit dans le panier
        $count = $cartService->getQuantityCart();
        // Appelle de la méthode qui calcul le montant total du panier
        $total = $cartService->getTotalCart();
        // dd($cartProductData);
        return $this->render('cart/index.html.twig', [
            'items' => $cartProductData,
            'count' => $count,
            'total' => $total,
        ]);
    }

    /**
     * @Route("/cart/add/{id}", name="app_cart_add", methods={"GET"})
     * @var int $id
     */
    public function add(int $id, CartService $cartService)
    {
        // Appelle de la methode add() associé au cartService et récupération du message
        $message = $cartService->add($id);
        // Création du message flash
        $this->addFlash(
            $message['type'],
            $message['text']
        );

        return $this->redirectToRoute('app_cart_index');
    }

    /**
     * @Route("/cart/remove/{id}", name="app_cart_remove")
     * @param int $id
     * 
     * @return void 
     */
    public function remove(int $id, CartService $cartService)
    {
        // Appelle de la methode add() associé au cartService et récupération du message
        $message = $cartService->remove($id);
        // Création du message flash
        $this->addFlash(
            $message['type'],
            $message['text']
        );

        return $this->redirectToRoute('app_cart_index');
    }

    /**
     * @Route("/cart/{id<\d+>}/quantity/{direction<up|down>}", methods="POST")
     * @param int $id 
     * @param string $direction
     * 
     * @return void 
     */
    public function updateQuantity(int $id, string $direction, CartService $cartService): JsonResponse
    {
        return $this->json([
            'newQuantity' => $cartService->updateQuantity($id, $direction),
            'newTotal' => $cartService->getTotalProduct($id),
            'panierNewTotal' => $cartService->getTotalCart(),
            'newProductCount' => $this->renderView('cart/_quantity.html.twig', [
                'count' => $cartService->getQuantityCart()
            ]),
            'productCoundHeader' => $cartService->getQuantityCart(),
        ]);
    }
}
