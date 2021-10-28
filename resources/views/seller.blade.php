<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
    <main class="bg-gray-50 py-16">
        <div class="container mx-auto">
            <div class="max-w-5xl mx-auto">
                @if ($errors->any())
                    <h4 class="bg-red-50 text-red-800 py-2 px-2 rounded-md shadow-md mb-2"> {{ $errors->first() }} </h4>
                @endif
                <div class="w-full mx-auto bg-white rounded-lg overflow-hidden shadow-lg">
                    <div class="bg-cover h-40" style="background-image: url('https://images.pexels.com/photos/110854/pexels-photo-110854.jpeg')"> </div>
                    <div class="border-b px-4 pb-6">
                        <div class="text-center flex mb-4 justify-center items-center">
                            <img src="{{ $seller->avatar }}" alt="" class="h-32 w-32 md:w-48 md:h-48 rounded-full border-4 border-white -mt-16 mr-4">
                        </div>
                        <div class="text-center flex mb-4 justify-center items-center">
                            <div class="py-2"> <h3 class="font-bold text-2xl mb1"> {{ $seller->name }} </h3>

                                <br class="inline md:hidden">
                                @if (!$seller->completed_stripe_onboarding)
                                    <p class="inline-flex text-center items center px-3 py-0.5 my-2 rounded-full text-sm font-medium bg-red-200 text-red-500"> Not Connected </p>
                                    <br/>
                                    <p class="font-semibold text-yellow-500 tracking-wide leading-loose underline"> Please connect your Stripe account </p>
                                @else
                                <p class="inline-flex text-center items center px-3 py-0.5 my-2 rounded-full text-sm font-medium bg-green-200 text-green-500"> Connected </p>
                                    <h1 href="#" class="font-semibold text-xl text-indigo-500 tracking-wide leading-loose"> £{{ $balance }} </h1>

                                @endif
                            </div>
                        </div>
                        <div class="flex justify-center">
                            <a
                                type="button"
                                href="{{ route('redirect.stripe', ['id' => $seller->id]) }}"
                                class="flex font-semibold text-white bg-indigo-500 w-full items-center justify-center px-3 py-3 border border-transparent text-sm leading-loose rounded">
                                <i class="fa fa-external-link" aria-hidden="true"></i> &nbsp;
                                @if ($seller->completed_stripe_onboarding)
                                    View Stripe Account
                                @else
                                    Connect Stripe Account
                                @endif
                            </a>
                        </div>
                    </div>
                </div>
                <div class="w-full mt-4 p-2 shadow-lg">
                    <form method="POST" action="{{ route('complete.purchase', [ 'id' => $seller->id ]) }}" id="payment-form">
                        @csrf
                        <div class="form-row" mt-2>
                            <!-- a Stripe Element will be inserted here. -->
                            <div id="card-element"></div>
                            <!-- Used to display Element errors -->
                            <div id="card-errors" role="alert"></div>
                        </div>
                        <input id="stripeToken" name="stripeToken" value="" type="hidden">
                        <button class="bg-indigo-500 font-bold text-white text-xs border-grey-500 py-3 px-4 focus:outline-none w-full mt-4 rounded">Submit Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
<script>

// Create a Stripe client
var stripe = Stripe('pk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
// Create an instance of Elements
var elements = stripe.elements();

// Custom styling can be passed to options when creating an Element.
// (Note that this demo uses a wider set of styles than the guide below.)
var style = {
  base: {
    // Add your base input styles here. For example:
    fontSize: '16px',
    color: "#32325d",
  }
};

// Create an instance of the card Element
var card = elements.create('card', {style: style});

// Add an instance of the card Element into the `card-element` <div>
card.mount('#card-element');

// Handle real-time validation errors from the card Element.
card.addEventListener('change', function(event) {
  var displayError = document.getElementById('card-errors');
  if (event.error) {
    displayError.textContent = event.error.message;
  } else {
    displayError.textContent = '';
  }
});

// Handle form submission
var form = document.getElementById('payment-form');
form.addEventListener('submit', function(event) {
  event.preventDefault();

  stripe.createToken(card).then(function(result) {
    if (result.error) {
      // Inform the user if there was an error
      var errorElement = document.getElementById('card-errors');
      errorElement.textContent = result.error.message;
    } else {
      // Send the token to your server
      stripeTokenHandler(result.token);
    }
  });
});

function stripeTokenHandler(token) {
    // Insert the token ID into the form so it gets submitted to the server
    var form  = document.getElementById('payment-form');
    var hiddenInput = document.createElement('input');
    hiddenInput.setAttribute('type', 'hidden');
    hiddenInput.setAttribute('name', 'stripeToken');
    hiddenInput.setAttribute('value', token.id);
    form.appendChild(hiddenInput);

    // Submit the form
    form.submit();
}

</script>
