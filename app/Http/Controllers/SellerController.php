<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Request;
use Stripe\StripeClient;
use Illuminate\Support\Str;
use Stripe\Exception\ApiErrorException;

class SellerController extends Controller
{
    protected StripeClient $stripeClient;
    protected DatabaseManager $databaseManager;

    public function __construct(StripeClient $stripeClient, DatabaseManager $databaseManager)
    {
        $this->stripeClient = $stripeClient;
        $this->databaseManager = $databaseManager;
    }

    public function showProfile($id)
    {
        $seller = User::find($id);

        if (is_null($seller)) {
            abort(404);
        }

        $balance =  $seller->completed_stripe_onboarding ?  $this->stripeClient
        ->balance->retrieve(null, ['stripe_account' => $seller->stripe_connect_id])
        ->available[0]
        ->amount : 0;


        return view('seller', [
            'seller'  => $seller,
            'balance' => $balance
        ]);
    }


    public function redirectToStripe($id)
    {
        $seller = User::find($id);

        if (is_null($seller)) {
            abort(404);
        }

        // Complete the onboarding process
        if (!$seller->completed_stripe_onboarding) {

            $token = Str::random();

            $this->databaseManager->table('stripe_state_tokens')->insert([
                'created_at' => now(),
                'updated_at' => now(),
                'seller_id'  => $seller->id,
                'token'      => $token
            ]);

            try {

                // Let's check if they have a stripe connect id
                if (is_null($seller->stripe_connect_id)) {

                    // Create account
                    $account = $this->stripeClient->accounts->create([
                        'country' => 'GB',
                        'type'    => 'express',
                        'email'   => $seller->email,
                    ]);

                    $seller->update(['stripe_connect_id' => $account->id]);
                    $seller->fresh();
                }

                $onboardLink = $this->stripeClient->accountLinks->create([
                    'account'     => $seller->stripe_connect_id,
                    'refresh_url' => route('redirect.stripe', ['id' => $seller->id]),
                    'return_url'  => route('save.stripe', ['token' => $token]),
                    'type'        => 'account_onboarding'
                ]);

                return redirect($onboardLink->url);

            } catch (\Exception $exception){
                return back()->withErrors(['message' => $exception->getMessage()]) ;
            }
        }

        try {

            $loginLink = $this->stripeClient->accounts->createLoginLink($seller->stripe_connect_id);
            return redirect($loginLink->url);

        } catch (\Exception $exception){
            return back()->withErrors(['message' => $exception->getMessage()]) ;
        }
    }


    public function saveStripeAccount($token)
    {
        $stripeToken = $this->databaseManager->table('stripe_state_tokens')
                        ->where('token', '=', $token)
                        ->first();

        if (is_null($stripeToken)) {
            abort(404);
        }

        $seller = User::find($stripeToken->seller_id);

        $seller->update([
            'completed_stripe_onboarding' => true
        ]);

        return redirect()->route('seller.profile', ['id' => $seller->id]);
    }


    public function purchase($id, Request $request)
    {
        $this->validate($request, [
            'stripeToken' => ['required', 'string']
        ]);

        $seller = User::find($id);

        if (is_null($seller)) {
            abort(404);
        }

        if (!$seller->completed_stripe_onboarding) {
            return back()->withErrors(['message' => 'Please finish onboarding process.']);
        }

        try {

            // Purchase a product
            $charge = $this->stripeClient->charges->create([
                'amount'      => 2000,   // £20.00
                'currency'    => 'gbp',
                'source'      => $request->stripeToken,
                'description' => 'This is an example charge.'
            ]);

            // Transfer funds to seller
            $this->stripeClient->transfers->create([
                'amount'             => 1600,   // £16.00
                'currency'           => 'gbp',
                'source_transaction' => $charge->id,
                'destination'        => $seller->stripe_connect_id
            ]);

        } catch (ApiErrorException $exception) {
            return back()->withErrors(['message' => $exception->getMessage()]) ;
        }

        return back();
    }
}
