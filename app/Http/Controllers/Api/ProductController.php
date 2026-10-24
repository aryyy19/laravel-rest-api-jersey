<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Menampilkan semua produk.
     */
    public function index()
    {
        $products = Product::with('category')->paginate(10);

        return ProductResource::collection($products);
    }

    /**
     * Menambahkan produk baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,category_id',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'img' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Initialize image path
        $path = "";

        // Mengambil file image dari request img
        if ($request->hasFile('img')) {
            // Menyimpan gambar ke folder products di disk public
            $path = $request->file('img')->store('products', 'public');
        }

        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'stock' => $request->stock,
            'img' => $path,
            'slug' => Str::slug($request->name),
        ]);

        return new ProductResource($product);
    }

    /**
     * Menampilkan detail produk.
     */
    public function show(string $id)
    {
        $product = Product::with('category')->findOrFail($id);

        return new ProductResource($product);
    }

    /**
     * Mengubah data produk.
     */

    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,category_id',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'img' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Gunakan gambar lama jika tidak ada gambar baru
        $path = $product->img;

        // Simpan gambar baru jika diunggah
        if ($request->hasFile('img')) {
            $path = $request->file('img')->store('products', 'public');
        }

        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'stock' => $request->stock,
            'img' => $path,
            'slug' => Str::slug($request->name),
        ]);

        return new ProductResource($product);
    }

    /**
     * Menghapus produk.
     */
    public function destroy(string $id)
    {
        $product = Product::find($id);

        if ($product != null) {
            $product->delete();

            return response()->json([
                'message' => 'Product deleted successfully',
            ]);
        }

        return response()->json([
            'message' => 'Product not found',
        ], 404);
    }
}