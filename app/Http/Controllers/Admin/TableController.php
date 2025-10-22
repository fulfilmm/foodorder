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

    public function allTableShow()
    {
        // $tables=Table::all();
        // $tables = Table::orderBy('created_at', 'desc')->get();
        $tables = Table::with('latestOrderToday')
            ->orderBy('created_at', 'desc')
            ->get();
        $user = Auth::user();
        if ($user->role == "manager") {
            return view('manager.table.all_table', compact('tables'));
        } else {
            return view('admin.table.all_table', compact('tables'));
        }
    }
    public function createPage()
    {
        return view('admin.table.create_table');
    }

    public function show(Table $table)
    {
        $user = Auth::user();
        $table->load('latestOrderToday');

        if ($user->role == "manager") {
            return view('manager.table.show_table', compact('table'));
        } else {
            return view('admin.table.show_table', compact('table'));
        }
    }


    public function destroy(Table $table)
    {
        // $table->delete();
        if ($table->qr_path && file_exists(public_path($table->qr_path))) {
            @unlink(public_path($table->qr_path));
        }

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
        $qrContent = url('customer/die-in/validate') . '?table=' . $request->name;
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

    public function edit(Table $table)
    {
        $user = Auth::user();
        if ($user->role === 'manager') {
            return view('manager.table.edit_table', compact('table'));
        }
        return view('admin.table.edit_table', compact('table'));
    }

    public function update(Request $request, Table $table)
    {
        $validated = $request->validate([
            'name'   => ['required', 'string', 'max:255', 'unique:tables,name,' . $table->id],
        ]);

        $regenerateQR = false;

        // If name changed, regenerate QR (since you used $request->name for qr content on create)
        if ($validated['name'] !== $table->name) {
            $regenerateQR = true;
        }

        $table->name   = $validated['name'];

        if ($regenerateQR) {
            $qrContent = $validated['name']; // same logic as store()
            $qrPngData = QrCode::format('png')->size(200)->margin(1)->generate($qrContent);

            $filename      = hexdec(uniqid()) . '.png';
            $saveDirectory = public_path('assets/vendors/tables');
            if (!is_dir($saveDirectory)) {
                mkdir($saveDirectory, 0755, true);
            }
            $finalPath = $saveDirectory . '/' . $filename;
            file_put_contents($finalPath, $qrPngData);

            \Intervention\Image\Facades\Image::configure(['driver' => 'gd']);
            $img = \Intervention\Image\Facades\Image::make($finalPath);
            $img->save($finalPath);

            // Optionally delete the old QR file to avoid orphaned files
            if ($table->qr_path && file_exists(public_path($table->qr_path))) {
                @unlink(public_path($table->qr_path));
            }

            $table->qr_path = 'assets/vendors/tables/' . $filename;
        }

        $table->save();

        $notification = [
            'message'    => 'Table updated successfully' . ($regenerateQR ? ' and QR regenerated.' : '.'),
            'alert-type' => 'success',
        ];

        // return redirect()->route('admin.tables.show', $table)->with($notification);
        $user = Auth::user();
        if ($user->role == "manager") {
            return  redirect()->route('manager.tables.all')->with($notification);
        } else {
            return  redirect()->route('admin.tables.all')->with($notification);
        }
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
