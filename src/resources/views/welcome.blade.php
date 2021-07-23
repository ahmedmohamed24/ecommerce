<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="{{ asset('style.css') }}">
</head>

<body>
    {{-- <form action="{{ route('purchase') }}" method="post" id="payment-form">
        @csrf
        <div class="form-row">
            <label for="card-element">Credit or debit card</label>
            <textarea name="description" id="" cols="30" rows="10"></textarea>
            <div id="card-element">
                <!-- a Stripe Element will be inserted here. -->
            </div>
            <!-- Used to display form errors -->
            <div id="card-errors"></div>
        </div>
        <button>Submit Payment</button>
    </form><!-- The needed JS files --> --}}
    <!-- JQUERY File -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script><!-- Stripe JS -->
    <script src="https://js.stripe.com/v3/"></script><!-- Your JS File -->
    <script src="{{ asset('scripts.js') }}"></script>
</body>

</html>
