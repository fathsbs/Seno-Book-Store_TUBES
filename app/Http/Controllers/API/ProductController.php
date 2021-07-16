<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function all(Request $request){
        $id = $request->input('id');
        $limit = $request->input('limit'); //pagination
        $name = $request->input('name');
        $description = $request->input('description');
        $tags = $request->input('tags');
        $categories = $request->input('categories');
        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');

        //Mengambil data berdasarkan ID
        if ($id) {
            $product = Product::with(['category','galleries'])->find($id);

            //jika data ada
            if ($product) {
                return ResponseFormatter::success(
                    $product,
                    'Data Produk Berhasil Diambil'
                );
            }

            //jika data tidak ada
            else {
                return ResponseFormatter::error(
                    null,
                    'Data Produk Tidak Ada',
                    404
                );
            }
        }
        //ambil semua data berdasarkan filter
        $product =  Product::with(['category','galleries']);

        //filter name
        if ($name) {
            $product->where('name','like','%'.$name.'%');
        }

        //filter description
        if ($description) {
            $product->where('description','like','%'.$description.'%');
        }
        
        //filter tags
        if ($tags) {
            $product->where('tags','like','%'.$tags.'%');
        }

        //filter price from
        if ($price_from) {
            $product->where('price','>=',$price_from);
        }

        //filter price to
        if ($price_to) {
            $product->where('price','<=',$price_to);
        }

        //filter categories
        if ($categories) {
            $product->where('categories','=',$categories);
        }
        
        return ResponseFormatter::success(
            $product->paginate($limit),
            'Data List Produk Berhasil Diambil'
        );
    }
}