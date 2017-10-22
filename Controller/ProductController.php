<?php
namespace BorysZielonka\ClientStoreProductBundle\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request as Request2;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\Psr7\str;

class ProductController extends Controller
{

    const API_URI = 'http://localhost:8000/api/product/';

    /**
     * @Route("/", name="product_index")
     */
    public function indexAction(Request2 $request)
    {
        $query = http_build_query([
            'moreThanAmount' => $request->get('moreThanAmount'),
            'inStock' => $request->get('inStock')
        ]);
        
        $client = new Client();
        $response = $client->get(self::API_URI.'?'.$query);

        $products = json_decode($response->getBody()->getContents());

        return $this->render('BorysZielonkaClientStoreProductBundle:Product:index.html.twig', array(
                'products' => $products
        ));
    }

    /**
     * 
     * @Route("/add", name="product_add")
     * @Method({"GET", "POST"})
     * @param Request2 $request
     * @return type
     */
    public function addAction(Request2 $request)
    {
        $form = $this->createForm('BorysZielonka\ClientStoreProductBundle\Form\ProductType');
        $form->handleRequest($request);
        $client = new Client();

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // 'form_params' bo application/x-www-form-urlencoded
                $client->post(self::API_URI, ['form_params' => $form->getData()]);
                $this->addFlash('success', 'Product added!');
                return $this->redirectToRoute('product_index');
            } catch (RequestException $e) {
                $this->addFlash('warning', 'Product hasn\'t been added');
            }
        }

        return $this->render('BorysZielonkaClientStoreProductBundle:Product:add.html.twig', array(
                'form' => $form->createView(),
        ));
    }

    /**
     * 
     * @Route("/edit/{id}", name="product_edit")
     * @param type $id
     * @param Request2 $request
     * @return type
     */
    public function editAction($id, Request2 $request)
    {
        $client = new Client();
        try {
            $response = $client->get(self::API_URI . $id);
            $productData = json_decode($response->getBody()->getContents());
        } catch (RequestException $e) {
            $this->addFlash('warning', 'No product');
            return $this->redirectToRoute('product_index');
        }

        $editForm = $this->createForm('BorysZielonka\ClientStoreProductBundle\Form\ProductType', $productData);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            try {
                // 'form_params' bo application/x-www-form-urlencoded
                $client->put(self::API_URI . $id, ['form_params' => $editForm->getData()]);
                $this->addFlash('success', 'Product added!');
                return $this->redirectToRoute('product_index');
            } catch (RequestException $e) {
                $this->addFlash('warning', 'Product hasn\'t been added');
            }
        }

        return $this->render('BorysZielonkaClientStoreProductBundle:Product:edit.html.twig', array(
                'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * @Route("/delete/{id}", name="product_delete")
     * @param type $id
     * @return type
     */
    public function deleteAction($id)
    {
        $client = new Client();

        try {
            $client->delete(self::API_URI . $id);
            $this->addFlash('success', 'Product removed!');
        } catch (RequestException $e) {
            $this->addFlash('warning', 'Product hasn\'t been removed');
        }

        return $this->redirectToRoute('product_index');
    }
}
