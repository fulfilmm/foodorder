<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Table;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Intervention\Image\Facades\Image;

class CategoryController extends Controller
{

    public function allCategoryShow(){
        // $categories=Category::all();
        $categories=Category::orderBy('created_at', 'desc')->get();
        return view('admin.category.all_category', compact('categories'));
    }
    public function createPage(){
        return view('admin.category.create_category');
    }

    public function show(Table $table)
    {
        return view('admin.table.show_table', compact('table'));
    }

    public function edit(Category $category)
    {
        return view('admin.category.edit_category', compact('category'));
    }
    public function update(Request $request, Category $category)
    {

        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);

        $category->update($request->all());
        $notification = array(
            'message' => 'Category Updated Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('admin.categories.all')->with($notification);
    }
    public function destroy(Category $category)
    {
        $category->delete();
        $notification = array(
            'message' => 'Category Deleted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('admin.categories.all')->with($notification);
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
        ]);

        Category::create([
            'name'    => $request->name,
        ]);

        $notification = [
            'message' => 'Category Successfully Created!',
            'alert-type' => 'success'
        ];

        return redirect()->route('admin.categories.all')->with($notification);
    }
}
