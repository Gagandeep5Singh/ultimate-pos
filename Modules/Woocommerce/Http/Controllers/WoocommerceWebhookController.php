<?php

namespace Modules\Woocommerce\Http\Controllers;

use App\Business;
use App\Transaction;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Woocommerce\Utils\WoocommerceUtil;

class WoocommerceWebhookController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $woocommerceUtil;

    protected $moduleUtil;

    protected $transactionUtil;

    protected $productUtil;

    /**
     * Constructor
     *
     * @param  WoocommerceUtil  $woocommerceUtil
     * @return void
     */
    public function __construct(WoocommerceUtil $woocommerceUtil, ModuleUtil $moduleUtil, TransactionUtil $transactionUtil, ProductUtil $productUtil)
    {
        $this->woocommerceUtil = $woocommerceUtil;
        $this->moduleUtil = $moduleUtil;
        $this->transactionUtil = $transactionUtil;
        $this->productUtil = $productUtil;
    }

    /**
     * Function to create sale from woocommerce webhook request.
     *
     * @return Response
     */
    public function orderCreated(Request $request, $business_id)
    {
        // Order sync disabled - webhook received but no sale will be created
        \Log::info('WooCommerce order created webhook received but order sync is disabled. Order will not be created in POS.');
        return response()->json(['status' => 'ok', 'message' => 'Order sync disabled'], 200);
    }

    /**
     * Function to update sale from woocommerce webhook request.
     *
     * @return Response
     */
    public function orderUpdated(Request $request, $business_id)
    {
        // Order sync disabled - webhook received but no sale will be updated
        \Log::info('WooCommerce order updated webhook received but order sync is disabled. Order will not be updated in POS.');
        return response()->json(['status' => 'ok', 'message' => 'Order sync disabled'], 200);
    }

    /**
     * Function to delete sale from woocommerce webhook request.
     *
     * @return Response
     */
    public function orderDeleted(Request $request, $business_id)
    {
        // Order sync disabled - webhook received but no sale will be deleted
        \Log::info('WooCommerce order deleted webhook received but order sync is disabled. Order will not be deleted in POS.');
        return response()->json(['status' => 'ok', 'message' => 'Order sync disabled'], 200);
    }

    /**
     * Function to restore sale from woocommerce webhook request.
     *
     * @return Response
     */
    public function orderRestored(Request $request, $business_id)
    {
        // Order sync disabled - webhook received but no sale will be restored
        \Log::info('WooCommerce order restored webhook received but order sync is disabled. Order will not be restored in POS.');
        return response()->json(['status' => 'ok', 'message' => 'Order sync disabled'], 200);
    }

    private function isValidWebhookRequest($request, $secret)
    {
        $signature = $request->header('x-wc-webhook-signature');

        $payload = $request->getContent();
        $calculated_hmac = base64_encode(hash_hmac('sha256', $payload, $secret, true));

        if ($signature != $calculated_hmac) {
            return false;
        } else {
            return true;
        }
    }
}
