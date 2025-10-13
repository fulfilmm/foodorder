<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" href="{{ asset('assets/images/logo/logo.png') }}" type="image/png">
  <title>Dining Option</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet" />
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body {
      font-family: "Inter", sans-serif;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      background-color: #fff;
      color: #4b4b4b;
      padding: 20px;
    }
    .container {
      max-width: 500px;
      width: 100%;
      text-align: center;
    }
    .illustration {
      width: 100%;
      max-width: 300px;
      height: auto;
      margin-bottom: 30px;
    }
    h1 {
      color: #2e7d32;
      font-size: 2rem;
      margin-bottom: 10px;
    }
    p {
      font-size: 1rem;
      margin-bottom: 30px;
    }
    .link-button {
      display: block;
      width: 100%;
      max-width: 300px;
      padding: 15px;
      margin: 10px auto;
      border: 2px solid #2e7d32;
      border-radius: 10px;
      background-color: white;
      color: #2e7d32;
      font-size: 1rem;
      font-weight: bold;
      text-align: center;
      text-decoration: none;
      transition: background-color 0.3s;
    }
    .link-button:hover {
      background-color: #e8f5e9;
    }
    @media (max-width: 600px) {
      h1 {
        font-size: 1.5rem;
      }
      p {
        font-size: 0.9rem;
      }
      .link-button {
        font-size: 0.95rem;
        padding: 12px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Chef Cooking Illustration" class="illustration" />

    <h1>Welcome{{ Auth::check() ? ', ' . Auth::user()->name : '' }}!</h1>

    <p>Please choose your dining option: takeaway or dine-in.</p>

    {{-- <a href="{{ route('customer.die_in.home') }}" class="link-button">🍴 Dine - In</a> --}}
    {{-- <a href="{{ route('customer.die_in.scanner') }}" class="link-button">🍴 Dine - In</a> --}}
    <a href="{{ route('customer.die_in.entry') }}" class="link-button">🍴 Dine - In</a>
    <a href="{{ route('customer.take_away.home') }}" class="link-button">🛍 Takeaway</a>
  </div>
</body>
</html>
