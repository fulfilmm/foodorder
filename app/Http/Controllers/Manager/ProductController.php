<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;

class ProductController extends Controller
{

    public function allProductShow(){
        $products = Product::with('category')
        ->orderBy('created_at', 'desc')
        ->get();
        return view('admin.product.all_products', compact('products'));
    }
    public function createPage(){
        $categories = Category::all();
        return view('admin.product.create_product',compact('categories'));
    }

    public function show(Product $product)
    {
        return view('admin.product.show_product', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('admin.product.edit_product', compact('product','categories'));
    }
   public function update(Request $request, Product $product)
{
    $validated = $request->validate([
        'name'        => ['required','string','max:255', Rule::unique('products','name')->ignore($product->id)],
        'code'        => ['required','string','max:255', Rule::unique('products','code')->ignore($product->id)],
        'category_id' => ['required','exists:categories,id'],

        // pricing inputs
        'actual_price'   => ['required','integer','min:0'],
        'has_discount'   => ['sometimes','boolean'],
        'discount_type'  => [Rule::requiredIf($request->boolean('has_discount')), 'nullable','in:percent,fixed'],
        'discount_value' => [Rule::requiredIf($request->boolean('has_discount')), 'nullable','integer','min:0'],

        'qty'         => ['required','integer','min:0'],
        'description' => ['required','string'],
        'image'       => ['nullable','image','mimes:jpeg,png,jpg,gif','max:2048'],
    ]);

    // Prevent overselling
    if ($product->sell_qty > $validated['qty']) {
        return back()->withErrors(['qty' => 'New quantity cannot be less than quantity already sold.'])
                     ->withInput();
    }

    // Recalculate remain_qty
    $validated['remain_qty'] = $validated['qty'] - $product->sell_qty;

    // Normalize discount fields if checkbox is off
    $has = $request->boolean('has_discount');
    if (!$has) {
        $validated['discount_type'] = null;
        $validated['discount_value'] = null;
    }

    // Compute final price (server-trust)
    $actual = (int) $validated['actual_price'];
    $discountAmount = 0;
    if ($has && !empty($validated['discount_type']) && $validated['discount_value'] !== null) {
        if ($validated['discount_type'] === 'percent') {
            $p = max(0, min(100, (int) $validated['discount_value']));
            $discountAmount = (int) floor($actual * $p / 100);
        } elseif ($validated['discount_type'] === 'fixed') {
            $discountAmount = min($actual, max(0, (int) $validated['discount_value']));
        }
    }
    $validated['has_discount'] = $has;
    $validated['price'] = max(0, $actual - $discountAmount); // final selling price

    // Image replace
    if ($request->hasFile('image')) {
        if ($product->image && file_exists(public_path($product->image))) {
            @unlink(public_path($product->image));
        }
        $validated['image'] = $this->processImage($request->file('image'));
    }

    // Update
    $product->update($validated);

    return redirect()->route('admin.products.all')->with([
        'message' => 'Product Updated Successfully',
        'alert-type' => 'success',
    ]);
}


    public function destroy(Product $product)
    {
        $product->delete();
        $notification = array(
            'message' => 'Product Deleted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('admin.products.all')->with($notification);
    }

    public function store(Request $request)
    {
    // 1) Validate inputs
    $request->validate([
        'name'        => ['required', 'string', 'max:255', 'unique:products,name'],
        'code'        => ['required', 'string', 'max:255', 'unique:products,code'],
        'category_id' => ['required', 'exists:categories,id'],

        // pricing
        'actual_price'   => ['required', 'integer', 'min:0'],
        'has_discount'   => ['sometimes', 'boolean'],
        'discount_type'  => [
            Rule::requiredIf($request->boolean('has_discount')),
            'nullable', 'in:percent,fixed'
        ],
        'discount_value' => [
            Rule::requiredIf($request->boolean('has_discount')),
            'nullable', 'integer', 'min:0'
        ],

        'qty'         => ['required', 'integer', 'min:0'],
        'description' => ['required', 'string'],
        'image'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
    ]);

    // 2) Image (optional)
    $imagePath = null;
    if ($request->hasFile('image')) {
        $imagePath = $this->processImage($request->file('image')); // your existing helper
    }

    // 3) Compute discount + final price on the server
    $actual = (int) $request->actual_price;
    $has    = $request->boolean('has_discount');
    $type   = $has ? $request->input('discount_type') : null;
    $value  = $has ? (int) $request->input('discount_value', 0) : null;

    $discountAmount = 0;
    if ($has && $type && $value !== null) {
        if ($type === 'percent') {
            $p = max(0, min(100, $value));
            $discountAmount = (int) floor($actual * $p / 100);
        } elseif ($type === 'fixed') {
            $discountAmount = min($actual, max(0, $value));
        }
    }
    $finalPrice = max(0, $actual - $discountAmount); // <- no tax

    // 4) Create product
    Product::create([
        'name'           => $request->name,
        'code'           => $request->code,
        'category_id'    => $request->category_id,

        'actual_price'   => $actual,         // base price
        'has_discount'   => $has,
        'discount_type'  => $type,
        'discount_value' => $value,
        'price'          => $finalPrice,     // final selling price (stored)

        'qty'            => $request->qty,
        'remain_qty'     => $request->qty,
        'sell_qty'       => 0,
        'description'    => $request->description,
        'image'          => $imagePath,
    ]);

    return redirect()->route('admin.products.all')->with([
        'message'    => 'Product Successfully Created!',
        'alert-type' => 'success',
    ]);
}

    private function processImage($image)
    {

        $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
        $directory = public_path('vendors/images/products');
        // $path = $directory . '/' . $name_gen;

        // Ensure the directory exists
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Resize and save the image
        Image::make($image)->resize(626, 626)->save('vendors/images/products/' . $name_gen);

        // Prepare the relative path
        $relativePath = 'vendors/images/products/' . $name_gen;


        return $relativePath;
    }

}
