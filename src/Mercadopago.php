<?php

namespace Bymovi\Mercadopago;

/**
 * MercadoPago Integration Library
 * Access MercadoPago for payments integration
 * 
 * @author hcasatti
 *
 */
// PSR-4 base is src/. Prefer src/cacert.pem, fallback to legacy nested path if not moved yet.
$GLOBALS["LIB_LOCATION"] = __DIR__;
if (!file_exists($GLOBALS["LIB_LOCATION"]."/cacert.pem")) {
    $legacy = __DIR__ . DIRECTORY_SEPARATOR . 'Bymovi' . DIRECTORY_SEPARATOR . 'Mercadopago';
    if (file_exists($legacy . DIRECTORY_SEPARATOR . 'cacert.pem')) {
        $GLOBALS["LIB_LOCATION"] = $legacy;
    }
}

class Mercadopago {
    const version = "0.5.3";

    private $client_id;
    private $client_secret;
    private $ll_access_token;
    private $access_data;
    private $sandbox = FALSE;

    function __construct() {
        $i = func_num_args();

        if ($i > 2 || $i < 1) {
            throw new MercadoPagoException("Invalid arguments. Use CLIENT_ID and CLIENT SECRET, or ACCESS_TOKEN");
        }

        if ($i == 1) {
            $this->ll_access_token = func_get_arg(0);
        }

        if ($i == 2) {
            $this->client_id = func_get_arg(0);
            $this->client_secret = func_get_arg(1);
        }
    }

    public function sandbox_mode($enable = NULL) {
        if (!is_null($enable)) {
            $this->sandbox = $enable === TRUE;
        }

        return $this->sandbox;
    }

    /**
     * Get Access Token for API use
     */
    public function get_access_token() {
        if (isset ($this->ll_access_token) && !is_null($this->ll_access_token)) {
            return $this->ll_access_token;
        }

        $app_client_values = array(
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'client_credentials'
        );

        $access_data = MPRestClient::post(array(
            "uri" => "/oauth/token",
            "data" => $app_client_values,
            "headers" => array(
                "content-type" => "application/x-www-form-urlencoded"
            )
        ));

        if ($access_data["status"] != 200) {
            throw new MercadoPagoException ($access_data['response']['message'], $access_data['status']);
        }

        $this->access_data = $access_data['response'];

        return $this->access_data['access_token'];
    }

    /**
     * Get information for specific payment
     * @param int $id
     * @return array(json)
     */
    public function get_payment($id) {
        $uri_prefix = $this->sandbox ? "/sandbox" : "";

        $request = array(
            "uri" => "/v1/payments/{$id}",
            "params" => array(
                "access_token" => $this->get_access_token()
            )
        );

        $payment_info = MPRestClient::get($request);
        return $payment_info;
    }
    public function get_payment_info($id) {
        return $this->get_payment($id);
    }

    /**
     * Get information for specific authorized payment
     * @param id
     * @return array(json)
     */
    public function get_authorized_payment($id) {
        $request = array(
            "uri" => "/authorized_payments/{$id}",
            "params" => array(
                "access_token" => $this->get_access_token()
            )
        );

        $result = MPRestClient::get($request);
        return $result;
    }

    /**
     * Refund accredited payment
     * @param id
     * @return array(json)
     */
    public function refund_payment($id) {
        $request = array(
            "uri" => "/v1/payments/{$id}/refunds",
            "params" => array(
                "access_token" => $this->get_access_token()
            )
        );

        $result = MPRestClient::post($request);
        return $result;
    }

    /**
     * Cancel pending payment
     * @param id
     * @return array(json)
     */
    public function cancel_payment($id) {
        $request = array(
            "uri" => "/v1/payments/{$id}",
            "params" => array(
                "access_token" => $this->get_access_token()
            ),
            "data" => array(
                "status" => "cancelled"
            )
        );

        $result = MPRestClient::put($request);
        return $result;
    }

    /**
     * Cancel preapproval payment
     * @param id
     * @return array(json)
     */
    public function cancel_preapproval_payment($id) {
        $request = array(
            "uri" => "/preapproval/{$id}",
            "params" => array(
                "access_token" => $this->get_access_token()
            ),
            "data" => array(
                "status" => "cancelled"
            )
        );

        $result = MPRestClient::put($request);
        return $result;
    }

    /**
     * Search payments according to filters, with pagination
     * @param filters (array):
     *      q: (string) text you want to search
     *      external_reference: (string) reference you use to identify the payment
     *      operation_type: (string) the operation_type of the payment could be regular_payment, money_transfer, recurring_payment
     *      payment_type: (string) the payment_type of the payment could be credit_card, ticket, bank_transfer, atm
     *      collector: (integer) collector id
     *      range: (string) date range of time: date_created, last_modified
     *      begin_date: (string) begin date to filter the search
     *      end_date: (string) end date to filter the search (begin_date <= end_date)
     * @param offset: (integer) pagination offset
     * @param limit: (integer) pagination limit
     * @return array(json)
     */
    public function search_payment($filters, $offset = 0, $limit = 0) {
        $filters['access_token'] = $this->get_access_token();

        $request = array(
            "uri" => "/v1/payments/search",
            "params" => $filters
        );

        $result = MPRestClient::get($request);
        return $result;
    }

    /**
     * Create a checkout preference
     * @param preference (array):
     * @return array(json)
     */
    public function create_preference($preference) {
        $request = array(
            "uri" => "/checkout/preferences",
            "params" => array(
                "access_token" => $this->get_access_token()
            ),
            "data" => $preference
        );

        $result = MPRestClient::post($request);
        return $result;
    }

    /**
     * Update a checkout preference
     * @param id (string):
     * @param preference (array):
     * @return array(json)
     */
    public function update_preference($id, $preference) {
        $request = array(
            "uri" => "/checkout/preferences/{$id}",
            "params" => array(
                "access_token" => $this->get_access_token()
            ),
            "data" => $preference
        );

        $result = MPRestClient::put($request);
        return $result;
    }

    /**
     * Get a checkout preference
     * @param id (string):
     * @return array(json)
     */
    public function get_preference($id) {
        $request = array(
            "uri" => "/checkout/preferences/{$id}",
            "params" => array(
                "access_token" => $this->get_access_token()
            )
        );

        $result = MPRestClient::get($request);
        return $result;
    }

    /**
     * Create a preapproval payment
     * @param preapproval_payment (array):
     * @return array(json)
     */
    public function create_preapproval_payment($preapproval_payment) {
        $request = array(
            "uri" => "/preapproval",
            "params" => array(
                "access_token" => $this->get_access_token()
            ),
            "data" => $preapproval_payment
        );

        $result = MPRestClient::post($request);
        return $result;
    }

    /**
     * Get a preapproval payment
     * @param id (string):
     * @return array(json)
     */
    public function get_preapproval_payment($id) {
        $request = array(
            "uri" => "/preapproval/{$id}",
            "params" => array(
                "access_token" => $this->get_access_token()
            )
        );

        $result = MPRestClient::get($request);
        return $result;
    }

    /**
     * Update a preapproval payment
     * @param id (string):
     * @param preapproval_payment (array):
     * @return array(json)
     */
    public function update_preapproval_payment($id, $preapproval_payment) {
        $request = array(
            "uri" => "/preapproval/{$id}",
            "params" => array(
                "access_token" => $this->get_access_token()
            ),
            "data" => $preapproval_payment
        );

        $result = MPRestClient::put($request);
        return $result;
    }

    /**
     * Generic resource get
     * @param request
     * @param params (array):
     * @param authenticate = true (boolean)
     * @return array(json)
     */
    public function get($request, $params = null, $authenticate = true) {
        if ($authenticate) {
            $params = is_array($params) ? $params : array();
            $params['access_token'] = $this->get_access_token();
        }

        $request = array(
            "uri" => $request,
            "params" => $params
        );

        $result = MPRestClient::get($request);
        return $result;
    }

    /**
     * Generic resource post
     * @param request
     * @param data (array):
     * @param params (array):
     * @return array(json)
     */
    public function post($request, $data, $params = null) {
        $params = is_array($params) ? $params : array();
        $params['access_token'] = $this->get_access_token();

        $request = array(
            "uri" => $request,
            "params" => $params,
            "data" => $data
        );

        $result = MPRestClient::post($request);
        return $result;
    }

    /**
     * Generic resource put
     * @param request
     * @param data (array):
     * @param params (array):
     * @return array(json)
     */
    public function put($request, $data, $params = null) {
        $params = is_array($params) ? $params : array();
        $params['access_token'] = $this->get_access_token();

        $request = array(
            "uri" => $request,
            "params" => $params,
            "data" => $data
        );

        $result = MPRestClient::put($request);
        return $result;
    }

    /**
     * Generic resource delete
     * @param request
     * @param params (array):
     * @return array(json)
     */
    public function delete($request, $params = null) {
        $params = is_array($params) ? $params : array();
        $params['access_token'] = $this->get_access_token();

        $request = array(
            "uri" => $request,
            "params" => $params
        );

        $result = MPRestClient::delete($request);
        return $result;
    }
}
