<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    public function productPush(Request $request)
    {
        try {
            $requestAll = $request->all();
            
            $tenantLast = Tenant::get()->last();
            
            
            DB::table("products")->upsert([
                "sku" => $requestAll['product']['kode'],
                "data" => json_encode($requestAll),
                "shop_product_id" => ""
            ], ["sku"], ["data"], ["shop_product_id"]);
            
            
            $product = Product::where('sku', '=', $requestAll['product']['kode'])->first();

            if ($product->shop_product_id) {
                $this->updateProductShopify($product->shop_product_id, $requestAll['product'], $tenantLast);
            } else {
                $productId = $this->getProductShopify($requestAll['product']['kode'], $tenantLast);
                if ($productId) {
                    $this->updateProductShopify($productId, $requestAll['product'], $tenantLast);
                    Product::where('sku', $requestAll['product']['kode'])
                    ->update(['shop_product_id' => $productId]);
                } else {
                    $createProductResult = $this->createProductShopify($requestAll['product'], $tenantLast);
                    Product::where('sku', $requestAll['product']['kode'])
                    ->update(['shop_product_id' => $createProductResult['product']['id']]);
                    
                }
            }
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage()];
            return response()->json($result, 400);
        }
        
        $result = ['success' => 200];

        return response()->json($result, 200);
    }

    public function updateProductShopify($productId, $data, $tenant) {
        $header = [
            'X-Shopify-Access-Token' => $tenant->token,
        ];

        $images = [];
        foreach ($data['gambar'] as $img) {
            $images[] = [
                'src' => $img['image']
            ];
        }

        $bodyRequest = [
            'product' => [
                'title' => $data['nama'],
                'body_html' => $data['deskripsi'],
                'status' => strtoupper($data['status']) == 'ENABLE'? 'active' : 'inactive',
                'images' => $images,
                'variants' => [
                    [
                        "price" => $data['harga'],
                        "sku" => $data['kode'],
                        "title" => $data['nama'],
                        "weight" => $data['berat'],
                        "weight_unit" => "kg"
                    ]
                ]
            ]
        ];
        
        $response = Http::withHeaders($header)->withOptions(["verify"=>false])->put("https://{$tenant->domain}/admin/api/2024-04/products/{$productId}.json", $bodyRequest);
        $responseJson = $response->json();

        return $responseJson;
    }

    public function createProductShopify($data, $tenant) {
        $header = [
            'X-Shopify-Access-Token' => $tenant->token,
        ];

        $images = [];
        foreach ($data['gambar'] as $img) {
            $images[] = [
                'src' => $img['image']
            ];
        }

        $body = [
            'product' => [
                'title' => $data['nama'],
                'body_html' => $data['deskripsi'],
                'status' => strtoupper($data['status']) ==  'ENABLE'? 'active': 'inactive',
                'images' => $images,
                'variants' => [
                    [
                        "price" => $data['harga'],
                        "sku" => $data['kode'],
                        "title" => $data['nama'],
                        "weight" => $data['berat'],
                        "weight_unit" => "kg"
                    ]
                ]
            ]
        ];
        
        
        $response = Http::withHeaders($header)->withOptions(["verify"=>false])->post("https://{$tenant->domain}/admin/api/2024-04/products.json", $body);
        $responseJson = $response->json();

        return $responseJson;
    }

    public function getProductShopify($sku, $tenant) {
        $header = [
            'X-Shopify-Access-Token' => $tenant->token,
        ];

        $query = <<<QUERY
            query {
                Product(first: 1, query: "sku:{$sku}") {
                    edges {
                        node {
                            legacyResourceId
                        }
                    }
                }
            }
        QUERY;

        $body = ['query' => $query];
        $response = Http::withHeaders($header)->withOptions(["verify"=>false])->post("https://{$tenant->domain}/admin/api/2024-04/graphql.json", $body);
        $responseJson = $response->json();

        return $responseJson['data']['Product']['edges'][0]['node']['legacyResourceId'] ?? "";
    }
}
