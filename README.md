AppKernel
```
new BorysZielonka\ClientStoreProductBundle\BorysZielonkaClientStoreProductBundle()
```


routing
```
    borys_zielonka_client_store_product:
    resource: "@BorysZielonkaApiStoreProductBundle/Controller/"
    type:     annotation
    prefix:   /
```

composer.json
```json
    "repositories" : [
        {
        "type" : "vcs",
        "url" : "https://gitlab.com/boryszielonka/client-store-product-bundle.git"
        }
    ],
```

```sh
composer require guzzlehttp/guzzle
composer require boryszielonka/client-store-product-bundle dev-master
```




