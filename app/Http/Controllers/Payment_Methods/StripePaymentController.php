<?php

namespace App\Http\Controllers\Payment_Methods;

use App\Models\PaymentRequest;
use App\Traits\Processor;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use App\Models\UserPayment;
use App\Models\UserSubscription;
use Carbon\Carbon;

class StripePaymentController extends Controller
{
    use Processor;

    private $config_values;
    private PaymentRequest $payment;

    public function __construct(PaymentRequest $payment)
    {
        $config = $this->payment_config('stripe', 'payment_config');
        if (!is_null($config) && $config->mode == 'live') {
            $this->config_values = json_decode($config->live_values);
        } elseif (!is_null($config) && $config->mode == 'test') {
            $this->config_values = json_decode($config->test_values);
        }
        $this->payment = $payment;
    }

    public function index(Request $request): View|Factory|JsonResponse|Application
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_400, null, $this->error_processor($validator)), 400);
        }

        $data = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
        if (!isset($data)) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }
        $config = $this->config_values;

        return view('payment.stripe', compact('data', 'config'));
    }

   public function payment_process_3d(Request $request): JsonResponse
{
    $data = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
    if (!isset($data)) {
        return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
    }
    
    $payment_amount = $data['payment_amount'];
    Stripe::setApiKey($this->config_values->api_key);
    $currency_code = $data->currency_code;

    $pricing_paragraph = "Expert Consultation Service"; // Default
    $business_name = "Expert Help"; // Default

    if ($data['additional_data'] != null) {
        $additional = json_decode($data['additional_data']);
        // Yahan se paragraph uthaya jo humne startChat mein banaya tha
        $pricing_paragraph = $additional->pricing_paragraph ?? $pricing_paragraph;
        $business_name = $additional->product_title ?? "Expert Help";
        $business_logo = $additional->category_image ?? url('/');
    }

    $checkout_session = Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => $currency_code ?? 'usd',
                'unit_amount' => round($payment_amount, 2) * 100,
                'product_data' => [
                    'name' => $business_name,
                    'description' => $pricing_paragraph, // YE WAHI PARAGRAPH HAI
                    'images' => [isset($business_logo) ? $business_logo : url('/')],
                ],
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => url('/') . '/payment/stripe/success?session_id={CHECKOUT_SESSION_ID}&payment_id=' . $data->id,
        'cancel_url' => url()->previous(),
    ]);

    return response()->json(['id' => $checkout_session->id]);
}

    public function success(Request $request)
    {
        Stripe::setApiKey($this->config_values->api_key);
        $session = Session::retrieve($request->get('session_id'));

        if ($session->payment_status == 'paid' && $session->status == 'complete') {

            $this->payment::where(['id' => $request['payment_id']])->update([
                'payment_method' => 'stripe',
                'is_paid' => 1,
                'transaction_id' => $session->payment_intent,
            ]);

            $data = $this->payment::where(['id' => $request['payment_id']])->first();

            if (isset($data) && $data->success_hook) {
                if (function_exists($data->success_hook)) {
                    call_user_func($data->success_hook, $data);
                } elseif (method_exists(\App\Http\Controllers\Web\AskExpertController::class, $data->success_hook)) {
                    \App\Http\Controllers\Web\AskExpertController::{$data->success_hook}($data);
                }
            }

            if ($data->attribute === 'expert_chat') {
                \App\Http\Controllers\Web\AskExpertController::expert_chat_payment_success($data);
                return redirect()->route('expert.payment.success');
            }


            return $this->payment_response($data, 'success');
        }
        // Failed case
        $paymentRequest = $this->payment::where(['id' => $request['payment_id']])->first();
        if ($paymentRequest && function_exists($paymentRequest->failure_hook)) {
            call_user_func($paymentRequest->failure_hook, $paymentRequest);
        }

        return $this->payment_response($paymentRequest ?? null, 'fail');
    }


    public function handle(Request $request)
    {
        $config = $this->payment_config('stripe', 'payment_config');

        if (!$config) {
            Log::error('Stripe config not found in addon_settings');
            return response('Config not found', 400);
        }

        $config_values = $config->mode === 'live'
            ? json_decode($config->live_values)
            : json_decode($config->test_values);

        $api_key = $config_values->api_key ?? null;
        $webhook_secret = $config_values->webhook_secret ?? null;

        if (!$api_key || !$webhook_secret) {
            Log::error('Stripe API key or Webhook secret missing in config');
            return response('Keys missing', 400);
        }

        Stripe::setApiKey($api_key);

        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $webhook_secret
            );
        } catch (\UnexpectedValueException $e) {
            Log::error('Invalid payload: ' . $e->getMessage());
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Invalid signature: ' . $e->getMessage());
            return response('Invalid signature', 400);
        }

        switch ($event->type) {

            case 'invoice.payment_succeeded':
                $invoice = $event->data->object;

                if (!$invoice->paid) {
                    break;
                }

                $subscriptionId = $invoice->subscription;
                $subscription = UserSubscription::where(
                    'stripe_subscription_id',
                    $subscriptionId
                )->first();

                if ($subscription) {
                    $subscription->update([
                        'current_period_start' => Carbon::createFromTimestamp($invoice->period_start),
                        'current_period_end'   => Carbon::createFromTimestamp($invoice->period_end),
                    ]);

                    $amount = $invoice->amount_paid / 100;

                    UserPayment::create([
                        'user_id' => $subscription->user_id,
                        'amount'  => $amount,
                        'type'    => 'membership_renewal',
                        'stripe_payment_intent_id' => $invoice->payment_intent,
                        'paid_at' => now(),
                    ]);

                    Log::info(
                        "Monthly renewal successful | user_id={$subscription->user_id} | amount={$amount}"
                    );
                }
                break;

            case 'customer.subscription.deleted':
            case 'customer.subscription.updated':
                $stripeSub = $event->data->object;

                if ($stripeSub->cancel_at_period_end || $stripeSub->status === 'canceled') {
                    UserSubscription::where('stripe_subscription_id', $stripeSub->id)
                        ->update([
                            'active'      => false,
                            'auto_renew'  => false,
                            'canceled_at' => now(),
                        ]);

                    Log::info("Subscription cancelled | stripe_subscription_id={$stripeSub->id}");
                }
                break;

            case 'invoice.payment_failed':
                $invoice = $event->data->object;
                $amountDue = $invoice->amount_due / 100;

                Log::warning(
                    "Payment failed | subscription={$invoice->subscription} | amount={$amountDue}"
                );
                break;

            default:
                Log::info("Unhandled webhook event: {$event->type}");
        }

        return response('Webhook handled', 200);
    }
}
