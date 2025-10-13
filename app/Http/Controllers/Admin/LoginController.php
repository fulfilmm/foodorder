<?php

namespace App\Http\Controllers\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class LoginController extends Controller
{
   // -- ------------------------   Admin Section ---------------------------------
    public function AdminloginPage(){
        return view('admin.auth.login');
    }
    public function Adminlogin(Request $request)
    {
        // Validate the request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);
        // Attempt to log the user in using the Auth facade
        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            // dd($user);
            if ($user->role === 'admin') {
                $notification = array(
                    'message' => 'Admin Login Successfully',
                    'alert-type' => 'success'
                );
                return redirect()->route('admin.home')->with($notification);
            }
             else {
                $notification = array(
                    'message' => 'Your email or password is incorrect.',
                    'alert-type' => 'error'
                );
                return redirect()->back()->with('error', 'Your email or password is incorrect.')->with($notification);
            }
        }
        $notification = array(
            'message' => 'Your email or password is incorrect.',
            'alert-type' => 'error'
        );
        return redirect()->back()->with('error', 'Your email or password is incorrect.')->with($notification);

    }
    public function Adminlogout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $notification = array(
            'message' => 'Admin Logout Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('admin.login')->with($notification);
    }
    public function adminForgotPasswordPage(){
        return view('admin.auth.forgot-password');
    }
    public function sendOtpAdmin(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::firstWhere('email', $request->email);
        if($user==null){
            $notification = array(
                'message' => 'Your email is incorrect.',
                'alert-type' => 'error'
            );
            return redirect()->back()->with('error', 'Your email  is incorrect.')->with($notification);
        }

        // Generate and store OTP
        $otp = rand(100000, 999999);

        $user->update(['otp' => $otp]);


        // Send OTP via email
        try {
            Mail::raw("Your OTP code is: $otp", function ($message) use ($request) {
                $message->to($request->email)
                    ->subject("Forgot Password");
            });
        } catch (\Exception $e) {
            dd("Mail error: " . $e->getMessage());
        }
        $notification = array(
            'message' => 'Your OTP is successfully sent.',
            'alert-type' => 'success'
        );
        session(['otp_email' => $request->email]);

        return redirect()->route('admin.verify_otp')
            ->with($notification);
    }
    public function showOtpPageAdmin()
    {
        $email = session('otp_email');

        return view('admin.auth.verify-otp', compact('email'));
    }

    public function verifyOtpOnlyAdmin(Request $request)
    {
        $email = session('otp_email'); // or authenticated user

        $request->validate([
            'otp' => 'required|numeric|digits:6',
        ]);
        if (!$email) {
            $notification = array(
                'message' => 'Session expired or invalid.',
                'alert-type' => 'error'
            );
            return redirect()->back()->with('error', 'Session expired or invalid.')->with($notification);
        }
        $user = User::firstWhere('email', $email);

        if($user==null){
            $notification = array(
                'message' => 'Your Information Is Wrong.',
                'alert-type' => 'error'
            );
            return redirect()->back()->with('error', 'Your Information Is Wrong.')->with($notification);
        }
        $otp = $request->otp;
        if($otp == $user->otp){
            $user->update(['otp' => null]);
        }
        else{
            $notification = array(
                'message' => 'Otp is wrong.',
                'alert-type' => 'error'
            );
            return redirect()->back()->with('error', 'OTP is Wrong..')->with($notification);
        }
        $notification = array(
            'message' => 'OTP verified successfully.',
            'alert-type' => 'success'
        );
        return redirect()->route('admin.reset_password')->with($notification);
    }
    public function adminResetPasswordPage()
    {
        $email = session('otp_email');

        return view('admin.auth.reset-password', compact('email'));
    }
    public function resetPasswordAdmin(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $email = session('otp_email');

        if(!$email) {
            $notification = array(
                'message' => 'Session expired or invalid.',
                'alert-type' => 'error'
            );
            return redirect()->back()->with('error', 'Session expired or invalid.')->with($notification);
        }
        $user = User::firstWhere('email', $email);

        if($user==null){
            $notification = array(
                'message' => 'Your Information Is Wrong.',
                'alert-type' => 'error'
            );
            return redirect()->back()->with('error', 'Your Information Is Wrong.')->with($notification);
        }
        $password=$request->password;
        $user->update(['password' => Hash::make($password)]);
        session()->forget('otp_email');
        $notification = array(
            'message' => 'Password Reset Successfully.',
            'alert-type' => 'success'
        );

        return redirect()->route('admin.login')->with($notification);
    }


    // ----------------------  Manager Section ---------------------------------------------
     public function ManagerLoginPage(){
        return view('manager.auth.login');
    }
    public function ManagerLogin(Request $request)
    {
        // Validate the request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);
        // Attempt to log the user in using the Auth facade
        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user->role === 'manager') {
                $notification = array(
                    'message' => 'Manager Login Successfully',
                    'alert-type' => 'success'
                );
                return redirect()->route('manager.home')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Your email or password is incorrect.',
                    'alert-type' => 'error'
                );
                return redirect()->back()->with('error', 'Your email or password is incorrect.')->with($notification);
            }
        }
        $notification = array(
            'message' => 'Your email or password is incorrect.',
            'alert-type' => 'error'
        );
        return redirect()->back()->with('error', 'Your email or password is incorrect.')->with($notification);

    }
    public function ManagerLogout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $notification = array(
            'message' => 'Manager Logout Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('manager.login')->with($notification);
    }
    public function managerForgotPasswordPage(){
        return view('manager.auth.forgot-password');
    }
    public function sendOtpManager(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::firstWhere('email', $request->email);
        if($user==null){
            $notification = array(
                'message' => 'Your email is incorrect.',
                'alert-type' => 'error'
            );
            return redirect()->back()->with('error', 'Your email  is incorrect.')->with($notification);
        }

        // Generate and store OTP
        $otp = rand(100000, 999999);

        $user->update(['otp' => $otp]);


        // Send OTP via email
        try {
            Mail::raw("Your OTP code is: $otp", function ($message) use ($request) {
                $message->to($request->email)
                    ->subject("Forgot Password");
            });
        } catch (\Exception $e) {
            dd("Mail error: " . $e->getMessage());
        }
        $notification = array(
            'message' => 'Your OTP is successfully sent.',
            'alert-type' => 'success'
        );
        session(['otp_email' => $request->email]);

        return redirect()->route('manager.verify_otp')
            ->with($notification);
    }
    public function showOtpPageManager()
    {
        $email = session('otp_email');
        return view('manager.auth.verify-otp', compact('email'));
    }
     public function verifyOtpOnlyManager(Request $request)
    {
        $email = session('otp_email'); // or authenticated user

        $request->validate([
            'otp' => 'required|numeric|digits:6',
        ]);
        if (!$email) {
            $notification = array(
                'message' => 'Session expired or invalid.',
                'alert-type' => 'error'
            );
            return redirect()->back()->with('error', 'Session expired or invalid.')->with($notification);
        }
        $user = User::firstWhere('email', $email);

        if($user==null){
            $notification = array(
                'message' => 'Your Information Is Wrong.',
                'alert-type' => 'error'
            );
            return redirect()->back()->with('error', 'Your Information Is Wrong.')->with($notification);
        }
        $otp = $request->otp;
        if($otp == $user->otp){
            $user->update(['otp' => null]);
        }
        else{
            $notification = array(
                'message' => 'Otp is wrong.',
                'alert-type' => 'error'
            );
            return redirect()->back()->with('error', 'OTP is Wrong..')->with($notification);
        }
        $notification = array(
            'message' => 'OTP verified successfully.',
            'alert-type' => 'success'
        );
        return redirect()->route('manager.reset_password')->with($notification);
    }
    public function managerResetPasswordPage()
    {
        $email = session('otp_email');
        return view('manager.auth.reset-password', compact('email'));
    }
    public function resetPasswordManager(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $email = session('otp_email');

        if(!$email) {
            $notification = array(
                'message' => 'Session expired or invalid.',
                'alert-type' => 'error'
            );
            return redirect()->back()->with('error', 'Session expired or invalid.')->with($notification);
        }
        $user = User::firstWhere('email', $email);

        if($user==null){
            $notification = array(
                'message' => 'Your Information Is Wrong.',
                'alert-type' => 'error'
            );
            return redirect()->back()->with('error', 'Your Information Is Wrong.')->with($notification);
        }
        $password=$request->password;
        $user->update(['password' => Hash::make($password)]);
        session()->forget('otp_email');
        $notification = array(
            'message' => 'Password Reset Successfully.',
            'alert-type' => 'success'
        );

        return redirect()->route('manager.login')->with($notification);
    }
    // ---------------------   Kitchen Section ----------------------------------------------
    public function KitchenLoginPage(){
        return view('kitchen.auth.login');
    }
    public function KitchenLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->role === 'kitchen') {
                return redirect()->route('kitchen.home')->with([
                    'message' => 'Kitchen Login Successfully',
                    'alert-type' => 'success'
                ]);
            }
        }

        return redirect()->back()
            ->withErrors(['email' => 'Your email and password is wrong'])
            ->withInput($request->only('email', 'remember'));
    }
    public function kitchenLogout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $notification = array(
            'message' => 'Logout Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('kitchen.login')->with($notification);
    }
    public function kitchenForgotPasswordPage(){
        return view('kitchen.auth.forgot-password');
    }
    public function sendOtpKitchen(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::firstWhere('email', $request->email);
        if($user==null){
            $notification = array(
                'message' => 'Your email is incorrect.',
                'alert-type' => 'error'
            );
            return redirect()->back()->with('error', 'Your email  is incorrect.')->with($notification);
        }

        // Generate and store OTP
        $otp = rand(100000, 999999);

        $user->update(['otp' => $otp]);


        // Send OTP via email
        try {
            Mail::raw("Your OTP code is: $otp", function ($message) use ($request) {
                $message->to($request->email)
                    ->subject("Forgot Password");
            });
        } catch (\Exception $e) {
            dd("Mail error: " . $e->getMessage());
        }
        $notification = array(
            'message' => 'Your OTP is successfully sent.',
            'alert-type' => 'success'
        );
        session(['otp_email' => $request->email]);

        return redirect()->route('kitchen.verify_otp')
            ->with($notification);
    }
    public function showOtpKitchenPage()
    {
        $email = session('otp_email');

        return view('kitchen.auth.verify-otp', compact('email'));
    }

    public function verifyKitchenOtpOnly(Request $request)
    {
        $email = session('otp_email'); // or authenticated user

        $request->validate([
            'otp' => 'required|numeric|digits:6',
        ]);
        if (!$email) {
            $notification = array(
                'message' => 'Session expired or invalid.',
                'alert-type' => 'error'
            );
            return redirect()->back()->with('error', 'Session expired or invalid.')->with($notification);
        }
        $user = User::firstWhere('email', $email);

        if($user==null){
            $notification = array(
                'message' => 'Your Information Is Wrong.',
                'alert-type' => 'error'
            );
            return redirect()->back()->with('error', 'Your Information Is Wrong.')->with($notification);
        }
        $otp = $request->otp;
        if($otp == $user->otp){
            $user->update(['otp' => null]);
        }
        else{
            $notification = array(
                'message' => 'Otp is wrong.',
                'alert-type' => 'error'
            );
            return redirect()->back()->with('error', 'OTP is Wrong..')->with($notification);
        }
        $notification = array(
            'message' => 'OTP verified successfully.',
            'alert-type' => 'success'
        );
        return redirect()->route('kitchen.reset_password')->with($notification);
    }
    public function kitchenResetPasswordPage()
    {
        $email = session('otp_email');

        return view('kitchen.auth.reset-password', compact('email'));
    }
    public function resetPasswordKitchen(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $email = session('otp_email');

        if(!$email) {
            $notification = array(
                'message' => 'Session expired or invalid.',
                'alert-type' => 'error'
            );
            return redirect()->back()->with('error', 'Session expired or invalid.')->with($notification);
        }
        $user = User::firstWhere('email', $email);

        if($user==null){
            $notification = array(
                'message' => 'Your Information Is Wrong.',
                'alert-type' => 'error'
            );
            return redirect()->back()->with('error', 'Your Information Is Wrong.')->with($notification);
        }
        $password=$request->password;
        $user->update(['password' => Hash::make($password)]);
        session()->forget('otp_email');
        $notification = array(
            'message' => 'Password Reset Successfully.',
            'alert-type' => 'success'
        );

        return redirect()->route('kitchen.login')->with($notification);
    }

    // ----------------------  Waiter Section      ------------------------------------
    public function waiterLoginPage(){
        return view('waiter.auth.login');
    }
    public function waiterLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->role === 'waiter') {
                return redirect()->route('waiter.home')->with([
                    'message' => 'Waiter Login Successfully',
                    'alert-type' => 'success'
                ]);
            }
        }

        return redirect()->back()
            ->withErrors(['email' => 'Your email and password is wrong'])
            ->withInput($request->only('email', 'remember'));
    }

    public function waiterForgotPasswordPage(){
        return view('waiter.auth.forgot-password');
    }
    public function sendOtpWaiter(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::firstWhere('email', $request->email);
        if($user==null){
            $notification = array(
                'message' => 'Your email is incorrect.',
                'alert-type' => 'error'
            );
            return redirect()->back()->with('error', 'Your email  is incorrect.')->with($notification);
        }

        // Generate and store OTP
        $otp = rand(100000, 999999);

        $user->update(['otp' => $otp]);


        // Send OTP via email
        try {
            Mail::raw("Your OTP code is: $otp", function ($message) use ($request) {
                $message->to($request->email)
                    ->subject("Forgot Password");
            });
        } catch (\Exception $e) {
            dd("Mail error: " . $e->getMessage());
        }
        $notification = array(
            'message' => 'Your OTP is successfully sent.',
            'alert-type' => 'success'
        );
        session(['otp_email' => $request->email]);

        return redirect()->route('waiter.verify_otp')
            ->with($notification);
    }
    public function showOtpPage()
    {
        $email = session('otp_email');

        return view('waiter.auth.verify-otp', compact('email'));
    }

    public function verifyOtpOnly(Request $request)
    {
        $email = session('otp_email'); // or authenticated user

        $request->validate([
            'otp' => 'required|numeric|digits:6',
        ]);
        if (!$email) {
            $notification = array(
                'message' => 'Session expired or invalid.',
                'alert-type' => 'error'
            );
            return redirect()->back()->with('error', 'Session expired or invalid.')->with($notification);
        }
        $user = User::firstWhere('email', $email);

          if($user==null){
              $notification = array(
                  'message' => 'Your Information Is Wrong.',
                  'alert-type' => 'error'
              );
              return redirect()->back()->with('error', 'Your Information Is Wrong.')->with($notification);
          }
          $otp = $request->otp;
         if($otp == $user->otp){
             $user->update(['otp' => null]);
         }
         else{
                $notification = array(
                    'message' => 'Otp is wrong.',
                    'alert-type' => 'error'
                );
                return redirect()->back()->with('error', 'OTP is Wrong..')->with($notification);
        }
        $notification = array(
            'message' => 'OTP verified successfully.',
            'alert-type' => 'success'
        );
        return redirect()->route('waiter.reset_password')->with($notification);
    }
    public function waiterResetPasswordPage()
    {
        $email = session('otp_email');

        return view('waiter.auth.reset-password', compact('email'));
    }
    public function resetPasswordWatier(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $email = session('otp_email');

        if(!$email) {
            $notification = array(
                'message' => 'Session expired or invalid.',
                'alert-type' => 'error'
            );
            return redirect()->back()->with('error', 'Session expired or invalid.')->with($notification);
        }
        $user = User::firstWhere('email', $email);

        if($user==null){
            $notification = array(
                'message' => 'Your Information Is Wrong.',
                'alert-type' => 'error'
            );
            return redirect()->back()->with('error', 'Your Information Is Wrong.')->with($notification);
        }
        $password=$request->password;
        $user->update(['password' => Hash::make($password)]);
        session()->forget('otp_email');
        $notification = array(
            'message' => 'Password Reset Successfully.',
            'alert-type' => 'success'
        );

        return redirect()->route('waiter.login')->with($notification);
    }
    public function waiterLogout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $notification = array(
            'message' => 'Logout Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('waiter.login')->with($notification);
    }

}
