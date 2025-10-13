<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Table;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Intervention\Image\Facades\Image;

class TableController extends Controller
{

    public function allTableShow(){
        // $tables=Table::all();
        $tables = Table::orderBy('created_at', 'desc')->get();

        return view('admin.table.all_table', compact('tables'));
    }
    public function createPage(){
        return view('admin.table.create_table');
    }

    public function show(Table $table)
    {
        return view('admin.table.show_table', compact('table'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit_user', compact('user'));
    }
    public function update(Request $request, User $user)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|string|in:admin,manager,kitchen,waiter,customer', // Adjust roles as per your app
        ]);

        $user->update($request->all());
        $notification = array(
            'message' => 'User Updated Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('admin.users.admin')->with($notification);
    }
    public function destroy(Table $table)
    {
        $table->delete();
        $notification = array(
            'message' => 'Table Deleted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('admin.tables.all')->with($notification);
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:tables,name'],
        ]);

        $latestTableId = Table::latest()->first()?->id ?? 0;
        $newCode = 'TBL' . str_pad($latestTableId + 1, 3, '0', STR_PAD_LEFT);

        // $qrContent = $newCode;
        $qrContent = $request->name;
        $qrPngData = QrCode::format('png')
            ->size(200)
            ->margin(1)
            ->generate($qrContent);

        $filename = hexdec(uniqid()) . '.png';
        $saveDirectory = public_path('assets/vendors/tables');

        if (!is_dir($saveDirectory)) {
            mkdir($saveDirectory, 0755, true);
        }

        $finalPath = $saveDirectory . '/' . $filename;

        file_put_contents($finalPath, $qrPngData);

        Image::configure(['driver' => 'gd']);
        $img = Image::make($finalPath);
        $img->save($finalPath);

        $relativePathForDb = 'assets/vendors/tables/' . $filename;

        Table::create([
            'name'    => $request->name,
            'code'    => $newCode,
            'status'  => 'available',
            'qr_path' => $relativePathForDb,
        ]);

        $notification = [
            'message' => 'Table Created Successfully with PNG QR Code!',
            'alert-type' => 'success'
        ];

        return redirect()->route('admin.tables.all')->with($notification);
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
