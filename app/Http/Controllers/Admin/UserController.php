<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{

    public function adminUserShow()
    {
        $users = User::where('role', 'admin')->get();
        $header = 'Admin Users';
        return view('admin.users.all_users', compact('users', 'header'));
    }
    public function managerUserShow()
    {
        $users = User::where('role', 'manager')->get();
        $header = 'Admin Users';
        return view('admin.users.all_users', compact('users', 'header'));
    }
    public function kitchenUserShow()
    {
        $users = User::where('role', 'kitchen')->get();
        $header = 'Kitchen Users';
        $user = Auth::user();

        if ($user->role == "manager") {
            return view('manager.users.all_users', compact('users', 'header'));
        } else {
            return view('admin.users.all_users', compact('users', 'header'));
        }
    }
    public function waiterUserShow()
    {
        $users = User::where('role', 'waiter')->get();
        $header = 'Waiter Users';
        $user = Auth::user();

        if ($user->role == "manager") {
            return view('manager.users.all_users', compact('users', 'header'));
        } else {
            return view('admin.users.all_users', compact('users', 'header'));
        }
    }
    public function customerUserShow()
    {
        $users = User::where('role', 'customer')->get();
        $header = 'Customer Users';
        $user = Auth::user();

        if ($user->role == "manager") {
            return view('manager.users.all_users', compact('users', 'header'));
        } else {
            return view('admin.users.all_users', compact('users', 'header'));
        }
    }
    public function showMyProfile()
    {
        $user = Auth::user();

        if ($user->role == "manager") {
            return view('manager.users.user_profile', compact('user'));
        } else {
            return view('admin.users.user_profile', compact('user'));
        }
    }
    public function show(Request $request, User $user)
    {
        // Default values so the Blade never explodes if not a customer
        $orders        = collect();
        $statusCounts  = collect();
        $lifetimeSpend = 0;
        $lastOrderAt   = null;
        // dd($user);

        if ($user->role === 'customer') {
            $q = trim((string) $request->get('q', ''));

            $ordersQuery = Order::with(['items', 'table'])
                ->where('user_id', $user->id);

            if ($q !== '') {
                $like = "%{$q}%";
                $ordersQuery->where(function ($qb) use ($like) {
                    $qb->where('order_no', 'like', $like)
                        ->orWhereHas('items', fn($iq) => $iq->where('name', 'like', $like));
                });
            }

            $orders = $ordersQuery
                ->orderByDesc('created_at')
                ->paginate(10)
                ->withQueryString();

            $statusCounts = Order::select('status', DB::raw('COUNT(*) as c'))
                ->where('user_id', $user->id)
                ->groupBy('status')
                ->pluck('c', 'status');

            $lifetimeSpend = (int) Order::where('user_id', $user->id)->sum('total');
            $lastOrder   = Order::where('user_id', $user->id)->latest('created_at')->first();
            $lastOrderAt = $lastOrder?->created_at; // Carbon|null
        }

        return view('admin.users.show_user', compact(
            'user',
            'orders',
            'statusCounts',
            'lifetimeSpend',
            'lastOrderAt'
        ));
    }
    public function showManager(Request $request, User $user)
    {
        // Default values so the Blade never explodes if not a customer
        $orders        = collect();
        $statusCounts  = collect();
        $lifetimeSpend = 0;
        $lastOrderAt   = null;
        // dd($user);

        if ($user->role === 'customer') {
            $q = trim((string) $request->get('q', ''));

            $ordersQuery = Order::with(['items', 'table'])
                ->where('user_id', $user->id);

            if ($q !== '') {
                $like = "%{$q}%";
                $ordersQuery->where(function ($qb) use ($like) {
                    $qb->where('order_no', 'like', $like)
                        ->orWhereHas('items', fn($iq) => $iq->where('name', 'like', $like));
                });
            }

            $orders = $ordersQuery
                ->orderByDesc('created_at')
                ->paginate(10)
                ->withQueryString();

            $statusCounts = Order::select('status', DB::raw('COUNT(*) as c'))
                ->where('user_id', $user->id)
                ->groupBy('status')
                ->pluck('c', 'status');

            $lifetimeSpend = (int) Order::where('user_id', $user->id)->sum('total');
            $lastOrder   = Order::where('user_id', $user->id)->latest('created_at')->first();
            $lastOrderAt = $lastOrder?->created_at; // Carbon|null
        }

        return view('manager.users.show_user', compact(
            'user',
            'orders',
            'statusCounts',
            'lifetimeSpend',
            'lastOrderAt'
        ));
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
    public function updateManager(Request $request, User $user)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);
        $user->update([
            'name'     => $request->name,
            'email'    => $request->email,
            'role'     => Auth::user()->role,
        ]);
        // $user->update($request->all());
        $notification = array(
            'message' => 'User Updated Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('manager.home')->with($notification);
    }
    public function destroy(User $user)
    {
        $user->delete();
        $notification = array(
            'message' => 'User Deleted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('admin.users.admin')->with($notification);
    }
    public function createUserPage()
    {
        $roles = ['admin', 'manager', 'kitchen', 'waiter', 'customer'];
        return view('admin.users.create_user', compact('roles'));
    }
    public function store(Request $request)
    {

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'string', 'in:admin,manager,kitchen,waiter,customer'],
        ]);

        // Create the new user
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,

        ]);
        $notification = array(
            'message' => 'User Created Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('admin.users.admin')->with($notification);
    }
    // public function userPasswordUpdate(Request $request){
    //     $user = Auth::user();
    //     $request->validate([
    //         'current_password' => 'required',
    //         'password' => 'required|confirmed|min:8',
    //     ]);

    //     if (!password_verify($request->current_password, $user->password)) {
    //         return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect']);
    //     }

    //     $user->password = bcrypt($request->password);
    //     $user->save();

    //     return redirect()->back()->with('success', 'Password updated successfully');
    // }
}
