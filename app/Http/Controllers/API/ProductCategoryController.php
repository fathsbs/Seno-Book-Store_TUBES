<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;

class ProductCategoryController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit'); //pagination
        $name = $request->input('name');
        $show_product = $request->input('show_product');

        //Mengambil data berdasarkan ID
        if ($id) {
            $category = ProductCategory::with(['products'])->find($id);

            //jika data ada
            if ($category) {
                return ResponseFormatter::success(
                    $category,
                    'Data Kategori Berhasil Diambil'
                );
            }

            //jika data tidak ada
            else {
                return ResponseFormatter::error(
                    null,
                    'Data Kategori Tidak Ada',
                    404
                );
            }
        }//end if cek id

        //ambil semua data berdasarkan filter
        $category =  ProductCategory::query();
        
        //filter name
        if ($name) {
            $category->where('name','like','%'.$name.'%');
        }

        //filter show product
        if ($show_product) {
            $category->with('products');
        }

        //tampil data
        return ResponseFormatter::success(
            $category->paginate($limit),
            'Data List Kategori Berhasil Diambil'
        );

    }
}
