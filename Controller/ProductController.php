<?php
namespace BorysZielonka\ClientStoreProductBundle\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ProductController extends Controller
{

    /**
     * 
     * @return mixed
     */
    private function getApiStoreUri()
    {
        return
            $this->container->getParameter('borys_zielonka_client_store_product.api_store_uri');
    }

    /**
     * @Route("/", name="product_index")
     * @Template()
     * @param Request $request
     * @return array
     */
    public function indexAction(Request $request)
    {
        $query = http_build_query([
            'moreThanAmount' => $request->get('moreThanAmount'),
            'inStock' => $request->get('inStock')
        ]);

        $client = new Client();
        $response = $client->get($this->getApiStoreUri() . '?' . $query);

        $products = json_decode($response->getBody()->getContents());

        return ['products' => $products];
    }

    /**
     * 
     * @Route("/add", name="product_add")
     * @Template()
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return array | RedirectResponse
     */
    public function addAction(Request $request)
    {
        $form = $this->createForm('BorysZielonka\ClientStoreProductBundle\Form\ProductType');
        $form->handleRequest($request);
        $client = new Client();

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // 'form_params' bo application/x-www-form-urlencoded
                $client->post($this->getApiStoreUri(), ['form_params' => $form->getData()]);
                $this->addFlash('success', 'Product added!');
                return $this->redirectToRoute('product_index');
            } catch (RequestException $e) {
                $this->addFlash('warning', 'Product hasn\'t been added. Error Code:' . $e->getCode());
            }
        }

        return ['form' => $form->createView()];
    }

    /**
     * 
     * @Route("/edit/{id}", name="product_edit")
     * @Template()
     * @param type $id
     * @param Request $request
     * @return array | RedirectResponse
     */
    public function editAction($id, Request $request)
    {
        $client = new Client();
        try {
            $response = $client->get($this->getApiStoreUri() . $id);
            $productData = json_decode($response->getBody()->getContents());
        } catch (RequestException $e) {
            $this->addFlash('warning', 'No product. Error Code:' . $e->getCode());
            return $this->redirectToRoute('product_index');
        }

        $editForm = $this->createForm('BorysZielonka\ClientStoreProductBundle\Form\ProductType', $productData);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            try {
                // 'form_params' bo application/x-www-form-urlencoded
                $client->put($this->getApiStoreUri() . $id, ['form_params' => $editForm->getData()]);
                $this->addFlash('success', 'Product added!');
                return $this->redirectToRoute('product_index');
            } catch (RequestException $e) {
                $this->addFlash('warning', 'Product hasn\'t been added. Error Code:' . $e->getCode());
            }
        }

        return ['edit_form' => $editForm->createView()];
    }

    /**
     * 
     * @Route("/delete/{id}", name="product_delete")
     * @param type $id
     * @return RedirectResponse
     */
    public function deleteAction($id)
    {
        $client = new Client();

        try {
            $client->delete($this->getApiStoreUri() . $id);
            $this->addFlash('success', 'Product removed!');
        } catch (RequestException $e) {
            $this->addFlash('warning', 'Product hasn\'t been removed . Error Code:' . $e->getCode());
        }

        return $this->redirectToRoute('product_index');
    }
}
