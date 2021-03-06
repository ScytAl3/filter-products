<?php

namespace App\Service\Cart;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{
    /**
     * 
     * @var SessionInterface
     */
    protected $session;

    /**
     * 
     * @var ProductRepository
     */
    protected $productRepository;

    protected $flashBag;

    /**
     * Methode d'ajout de dépendances à la méthode __construct
     * injection de dépendance
     * @param SessionInterface $session 
     * @param ProductRepository $productRepository 
     * 
     * @return void 
     */
    public function __construct(SessionInterface $session, ProductRepository $productRepository, FlashBagInterface $flashBag)
    {
        $this->session = $session;
        $this->productRepository = $productRepository;
        $this->flashBag = $flashBag;
    }

    /**
     * Ajoute un produit dans le panier ou augmente sa quantité de 1 s'il est déjà présent
     * @param int $id 
     * @param int $qty 
     * @return void 
     */
    public function add(int $id, int $qty = 1)
    {
        // Récupération du panier de la session s'il existe - la valeur par défaut est un tableau vide
        $cart = $this->session->get('cart', []);
        // Verifie si l'identifiant du produit est déjà dans le panier - si oui ajoute 1 à la quantité
        if (!empty($cart[$id])) {
            // Si la quantité en stock - (quantité déjà dans le panier +1) est supérieur à 0
            if ($this->getStockProduct($id, $qty)) {
                // Ajout 1 à la quantité à commander
                $cart[$id] += $qty;
                // Ajout d'un message de confirmation
                $this->flashBag->add(
                    'success',
                    'The quantity of the product has been increased by 1 successfully!'
                );
            } else {
                // Sinon creation d'un message pour stock insuffisant
                $this->flashBag->add(
                    'danger',
                    "We don't have enough of this product in stock!"
                );
            }
        } else {
            // Si la quantité en stock - (quantité déjà dans le panier +1) est supérieur à 0
            if ($this->getStockProduct($id, $qty)) {
                // Ajoute au panier l'identifiant du produit associé à la quantité
                $cart[$id] = $qty;
                // Ajout d'un message de confirmation
                $this->flashBag->add(
                    'success',
                    'The product was added successfully!'
                );
            } else {
                // Sinon creation d'un message pour stock insuffisant
                $this->flashBag->add(
                    'danger',
                    "We don't have enough of this product in stock!"
                );
            }
        }
        // Sauvegarde le panier en cours dans la session
        $this->session->set('cart', $cart);
    }

    /**
     * Supprime un produit du panier
     * @param int $id 
     * 
     * @return void 
     */
    public function remove(int $id)
    {
        // Récupération du panier
        $cart = $this->session->get('cart', []);
        // Si l'identifiant du produit existe dans le panier
        if (!empty($cart[$id])) {
            // Suppression de cette variable de session
            unset($cart[$id]);
            // Ajout d'un message de confirmation
            $this->flashBag->add(
                'danger',
                'The product was successfully deleted!'
            );
        }
        // Actualise le nouveau panier
        $this->session->set('cart', $cart);
    }

    /**
     * Ajoute ou enlève 1 à la quantité du produit
     * @param int $id 
     * @param string $direction 
     * 
     * @return int 
     */
    public function updateQuantity(int $id, string $direction): int
    {
        // Récupération du panier de la session s'il existe - la valeur par défaut est un tableau vide
        $cart = $this->session->get('cart', []);
        // Verifie si l'identifiant du produit est déjà dans le panier - si oui modifie la quantité
        if (!empty($cart[$id])) {
            // Suivant la direction la quantité augmente ou diminue de 1
            $qte = ($direction === "up") ? 1 : -1;
            // Si la quantité en stock - (quantité déjà dans le panier +1) est supérieur à 0
            if ($this->getStockProduct($id, $qte)) {
                // Met à jour la quantité du produit
                $cart[$id] += $qte;
            } else {
                // Return false pour afficher une alerte sur la quantité en stock
                return false;
            }         
            // Si la quantité devient égale à zéro
            if ($cart[$id] < 1) {
                // La quantité par défaut = 1
                $cart[$id] = 1;
            }
        }
        // Sauvegarde le panier en cours dans la session
        $this->session->set('cart', $cart);
        return $cart[$id];
    }

    /**
     * Vérifie la quantité du produit en stock
     * @param int $id 
     * @param int $requiredQuantity
     * @return bool 
     */
    private function getStockProduct(int $id, int $requiredQuantity): bool
    {
        // Récupération du panier de la session
        $cart = $this->session->get('cart');
        // Quantité en stock
        $stockQuantity = $this->productRepository->find($id)->getQuantity();
        // Si le produit existe déjà dans le panier on récupère sa quantité - sinon 0
        $cartQuantity = ( isset($cart[$id])) ? $cart[$id] : 0;

        if (($stockQuantity - ($cartQuantity + $requiredQuantity)) < 0) {
            return false;
        }
        return true;
    }

    /**
     * Renvoie les informations concernant les produits contenus dans le panier
     * 
     * @return array 
     */
    public function getDataCart(): array
    {
        // Récupere le panier en cours
        $cart = $this->session->get('cart', []);
        // Initialisation d'un tableau pour stocker les données d'un produit et sa quantité
        $cartProductData = [];
        // Parcours du panier pour récuperer tous les produits qu'il contient
        foreach ($cart as $id => $quantity) {
            // Ajoute dans le tableau des données le produit associé à sa quantité
            $cartProductData[] = [
                'product' => $this->productRepository->find($id),
                'quantity' => $quantity
            ];
        }
        return $cartProductData;
    }

    /**
     * Renvoie le montant total du panier
     * 
     * @return float 
     */
    public function getTotalCart(): float
    {
        // Initialisation du montant du panier
        $total = 0;
        // Boucle sur le panier
        foreach ($this->getDataCart() as $productData) {
            // Calcul du montant du panier avant de l'envoyer sur la page twig
            $total += $productData['product']->getPrice() * $productData['quantity'];
        }
        return $total;
    }

    /**
     * Renvoie la quantité de produit dans le panier
     * 
     * @return int 
     */
    public function getQuantityCart(): int
    {
        // Initialisation du montant du panier
        $count = 0;
        // Boucle sur le panier
        foreach ($this->getDataCart() as $productData) {
            // Calcul du montant du panier avant de l'envoyer sur la page twig
            $count += $productData['quantity'];
        }
        return $count;
    }

    /**
     * Renvoie le montant total d'un article
     * @param int $id
     * 
     * @return float 
     */
    public function getTotalProduct(int $id): float
    {
        // Récupere le panier en cours
        $cart = $this->session->get('cart', []);
        // Initialisation du montant de la ligne produit
        $total = 0;
        // Recupère le prix du produit
        $price = $this->productRepository->find($id)->getPrice();
        $total = $price * $cart[$id];
        return $total;
    }

    /**
     * Vide le panier - enlève la variable de session
     * @return void 
     */
    public function clearCart()
    {
        $this->session->remove('cart');
    }
}
