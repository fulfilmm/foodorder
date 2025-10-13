<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--favicon-->
    <link rel="icon" href="{{asset('assets/images/favicon-32x32.png')}}" type="image/png" />


    <!-- Bootstrap CSS -->
    <link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/css/app.css')}}" rel="stylesheet">
    <link href="{{asset('assets/css/icons.css')}}" rel="stylesheet">
    <title>Food Order</title>
</head>

<body class="bg-forgot">
<!-- wrapper -->
<div class="wrapper">
    <div class="authentication-forgot d-flex align-items-center justify-content-center">
        <div class="card forgot-box">
            <div class="card-body">
                <div class="p-4 rounded  border">
                    <div class="text-center">
                        <img src="{{asset('assets/images/icons/forgot-2.png')}}" width="120" alt="" />
                    </div>
                    <h4 class="mt-5 font-weight-bold">Forgot Password?</h4>
                    <p class="text-muted">Enter your registered email  to reset the password</p>
                    <form action="{{ route('manager.send_otp') }}" method="POST">
                        @csrf
                        <div class="my-4">
                            <label class="form-label">Email </label>
                            <input type="email" name="email" class="form-control form-control-lg" placeholder="example@user.com" required />
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Send</button>
                            <a href="{{ route('manager.login') }}" class="btn btn-light btn-lg">
                                <i class='bx bx-arrow-back me-1'></i>Back to Login
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end wrapper -->
</body>

</html>
