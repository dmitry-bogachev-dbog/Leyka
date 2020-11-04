<?php if( !defined('WPINC') ) die;
/**
 * Leyka_Chronopay_Gateway class
 */

class Leyka_Chronopay_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'chronopay';
        $this->_title = __('Chronopay', 'leyka');

        $this->_description = apply_filters(
            'leyka_gateway_description',
            __('Chronopay allows a simple and safe way to pay for goods and services with bank cards through internet. You will have to fill a payment form, you will be redirected to the <a href="http://www.chronopay.com/ru/">Chronopay</a> secure payment page to enter your bank card data and to confirm your payment.', 'leyka'),
            $this->_id
        );

        $this->_docs_link = '//leyka.te-st.ru/docs/chronopay/';
        $this->_registration_link = '//chronopay.com/ru/connection/';

        $this->_min_commission = 2.7;
        $this->_receiver_types = array('legal');
        $this->_may_support_recurring = true;

    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = array(
            'chronopay_shared_sec' => array(
                'type' => 'text',
                'title' => __('Shared Sec', 'leyka'),
                'comment' => __('Please, enter your Chronopay shared_sec value here. It can be found in your contract.', 'leyka'),
                'required' => true,
                'is_password' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '4G0i8590sl5Da37I'),
            ),
            'chronopay_ip' => array(
                'type' => 'text',
                'default' => '185.30.16.166',
                'title' => __('Chronopay IP', 'leyka'),
                'comment' => __('IP address to check for requests.', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '185.30.16.166'),
            ),
            'chronopay_use_payment_uniqueness_control' => array(
                'type' => 'checkbox',
                'default' => false,
                'title' => __('Use the payments uniqueness control', 'leyka'),
                'comment' => __('Check if you use Chronopay payment uniqueness control setting.', 'leyka'),
                'short_format' => true,
            ),
        );

    }

    protected function _initialize_pm_list() {
        if(empty($this->_payment_methods['chronopay_card'])) {
            $this->_payment_methods['chronopay_card'] = Leyka_Chronopay_Card::get_instance();
        }
    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {
    }

    public function submission_redirect_url($current_url, $pm_id) {
        return 'https://payments.chronopay.com/';
    }

    public function submission_form_data($form_data, $pm_id, $donation_id) {

        if(false === strpos($pm_id, 'chronopay')) {
            return $form_data; // It's not our PM
        }

        if(is_wp_error($donation_id)) { /** @var WP_Error $donation_id */
            return array('status' => 1, 'message' => $donation_id->get_error_message());
        } else if( !$donation_id ) {
            return array('status' => 1, 'message' => __('The donation was not created due to error.', 'leyka'));
        }

        $donation = Leyka_Donations::get_instance()->get_donation($donation_id);

        if(empty($_POST['leyka_recurring'])) { // Single donation

            $donation->type = 'single';
            $chronopay_product_id = leyka_options()->opt($pm_id.'_product_id_'.$donation->currency);

        } else { // Recurring donation

            $donation->type = 'rebill';
            $chronopay_product_id = leyka_options()->opt($pm_id.'_rebill_product_id_'.$donation->currency);

        }

        $price = number_format((float)$donation->amount, 2, '.', '');

        $country = $donation->currency == 'rur' ? 'RUS' : '';

        $form_data =  array(
            'product_id' => $chronopay_product_id,
            'product_price' => $price,
            'product_price_currency' => $this->_get_currency_id($donation->currency),
            'cs1' => esc_attr($donation->title), // Purpose of the donation
            'cs2' => $donation_id,
            'cb_url' => leyka_maybe_encode_hostname_to_punycode(home_url('leyka/service/'.$this->_id.'/response/')),
            'cb_type' => 'P',
            'success_url' => leyka_maybe_encode_hostname_to_punycode(leyka_get_success_page_url()),
            'decline_url' => leyka_maybe_encode_hostname_to_punycode(leyka_get_failure_page_url()),
            'sign' => md5(
                $chronopay_product_id.'-'.$price
                .(leyka_options()->opt('chronopay_use_payment_uniqueness_control') ? '-'.$donation_id : '')
                .'-'.leyka_options()->opt('chronopay_shared_sec')
            ),
            'language' => get_locale() == 'ru_RU' ? 'ru' : 'en',
            'email' => $donation->donor_email,
        );

        if(leyka_options()->opt('chronopay_use_payment_uniqueness_control')) {
            $form_data['order_id'] = $donation_id;
        }

        if($country) {
            $form_data['country'] = $country;
        }

        return $form_data;

    }

    protected function _get_currency_id($leyka_currency_id){

        $chronopay_currencies = array('rur' => 'RUB', 'usd' => 'USD', 'eur' => 'EUR');

        return isset($chronopay_currencies[$leyka_currency_id]) ? $chronopay_currencies[$leyka_currency_id] : 'RUB';

    }

    public function _handle_service_calls($call_type = '') {

        // TODO DBG
        $tmp_log = get_transient('leyka_tmp_dbg');
        $tmp_log = $tmp_log ? $tmp_log : array();

        $tmp_log_entry = array('time' => current_time('d.m.Y H:i:s'), 'post' => $_POST);
        // TODO DBG - END

        $client_ip = leyka_get_client_ip();

        // Test for gateway's IP:
        if(
            leyka_options()->opt('chronopay_ip') &&
            !in_array($client_ip, explode(',', leyka_options()->opt('chronopay_ip')))
        ) { // Security fail

            $message = __("This message has been sent because a call to your ChronoPay function was made from an IP that did not match with the one in your Chronopay gateway setting. This could mean someone is trying to hack your payment website. The details of the call are below.", 'leyka')."\n\r\n\r";

            $message .= "POST:\n\r".print_r($_POST, true)."\n\r\n\r";
            $message .= "GET:\n\r".print_r($_GET, true)."\n\r\n\r";
            $message .= "SERVER:\n\r".print_r(apply_filters('leyka_notification_server_data', $_SERVER), true)."\n\r\n\r";
            $message .= "IP: ".print_r($client_ip, true)."\n\r\n\r";
            $message .= "Chronopay IP setting value: ".print_r(leyka_options()->opt('chronopay_ip'),true)."\n\r\n\r";

            wp_mail(get_option('admin_email'), __('Chronopay IP check failed!', 'leyka'), $message);

            status_header(200);
            die(1);

        }

        // Test for e-sign:
        $customer_id = isset($_POST['customer_id'])? trim(stripslashes($_POST['customer_id'])) : '';
        $transaction_id = isset($_POST['transaction_id']) ? trim(stripslashes($_POST['transaction_id'])): '';
        $transaction_type = isset($_POST['transaction_type']) ? trim(stripslashes($_POST['transaction_type'])) : '';
        $total = isset($_POST['total']) ? trim(stripslashes($_POST['total'])) : '';
        $sign = md5(leyka_options()->opt('chronopay_shared_sec').$customer_id.$transaction_id.$transaction_type.$total);

        if(empty($_POST['sign']) || $sign != trim(stripslashes($_POST['sign']))) { // Security fail

            $message = __("This message has been sent because a call to your ChronoPay function was made by a server that did not have the correct security key.  This could mean someone is trying to hack your payment site.  The details of the call are below.", 'leyka')."\n\r\n\r";

            $message .= "POST:\n\r".print_r($_POST, true)."\n\r\n\r";
            $message .= "GET:\n\r".print_r($_GET, true)."\n\r\n\r";
            $message .= "SERVER:\n\r".print_r(apply_filters('leyka_notification_server_data', $_SERVER), true)."\n\r\n\r";

            wp_mail(get_option('admin_email'), __('Chronopay security key check failed!', 'leyka'), $message);

            status_header(200);
            die(2);

        }

        $_POST['cs2'] = (int)$_POST['cs2'];
        $donation = Leyka_Donations::get_instance()->get_donation($_POST['cs2']);

        if( !$donation->id || !$donation->campaign_id ) {

            $message = __("This message has been sent because a call to your ChronoPay callbacks URL was made with a donation ID parameter (POST['cs2']) that Leyka is unknown of. The details of the call are below.", 'leyka')."\n\r\n\r";

            $message .= "POST:\n\r".print_r($_POST, true)."\n\r\n\r";
            $message .= "GET:\n\r".print_r($_GET, true)."\n\r\n\r";
            $message .= "SERVER:\n\r".print_r(apply_filters('leyka_notification_server_data', $_SERVER), true)."\n\r\n\r";
            $message .= "Donation ID: ".$_POST['cs2']."\n\r\n\r";

            wp_mail(get_option('admin_email'), __('Chronopay gives unknown donation ID parameter!', 'leyka'), $message);

            // TODO DBG
            $tmp_log_entry['error'] = 'Do init donation found by ID: '.$_POST['cs2'];
            $tmp_log[] = $tmp_log_entry;
            set_transient('leyka_tmp_dbg', $tmp_log);
            // TODO DBG - END

            status_header(200);
            die(3);

        }

        $_POST['currency'] = mb_strtolower($_POST['currency']);
        if($_POST['currency'] == 'rub') {
            $currency_string = 'rur';
        } else if($_POST['currency'] == 'usd') {
            $currency_string = 'usd';
        } else if($_POST['currency'] == 'eur') {
            $currency_string = 'eur';
        } else {

            $message = __("This message has been sent because a call to your ChronoPay callbacks URL was made with a currency parameter (POST['currency']) that Leyka is unknown of. The details of the call are below.", 'leyka')."\n\r\n\r";

            $message .= "POST:\n\r".print_r($_POST, true)."\n\r\n\r";
            $message .= "GET:\n\r".print_r($_GET, true)."\n\r\n\r";
            $message .= "SERVER:\n\r".print_r(apply_filters('leyka_notification_server_data', $_SERVER), true)."\n\r\n\r";

            // TODO DBG
            $tmp_log_entry['error'] = 'Unknown donation currency: '.$_POST['currency'];
            $tmp_log[] = $tmp_log_entry;
            set_transient('leyka_tmp_dbg', $tmp_log);
            // TODO DBG - END

            wp_mail(get_option('admin_email'), __('Chronopay gives unknown currency parameter!', 'leyka'), $message);
            status_header(200);
            die(4);

        }

        // Store donation data - rebill payment:
        if(apply_filters(
            'leyka_chronopay_callback_is_recurring',
            (
                leyka_options()->opt('chronopay_card_rebill_product_id_'.$currency_string) &&
                $_POST['product_id'] == leyka_options()->opt('chronopay_card_rebill_product_id_'.$currency_string)
            ),
            $_POST['product_id']
        )) {

            // TODO DBG
            $tmp_log_entry['chronopay_is_recurring'] = array();
            // TODO DBG - END

            if($transaction_type == 'Purchase') { // Initial recurring donation (subscription)

                $tmp_log_entry['chronopay_is_recurring'][] = 'Is init recurring'; // TODO DBG

                if($donation->status != 'funded') {

                    $donation->add_gateway_response($_POST);
                    $donation->status = 'funded';
                    $donation->type = 'rebill';

                    if( !$donation->donor_email && !empty($_POST['email']) ) {
                        $donation->donor_email = $_POST['email'];
                    }

                    Leyka_Donation_Management::send_all_emails($donation->id);

                    $donation->chronopay_customer_id = $customer_id;
                    $donation->chronopay_transaction_id = $transaction_id;

                    $tmp_log_entry['chronopay_is_recurring'][] = 'Init recurring added'; // TODO DBG

                }

            } else if($transaction_type == 'Rebill') { // Non-init recurring donation

                $tmp_log_entry['chronopay_is_recurring'][] = 'Is rebill recurring'; // TODO DBG

                // Callback is repeated (like when Chronopay didn't get an answer in prev. attempt):
                if($this->_donation_exists($transaction_id)) {

                    $tmp_log_entry['chronopay_is_recurring'][] = 'Rebill recurring already exists: '.$transaction_id; // TODO DBG

                    status_header(200);
                    die(0);

                }

                $init_recurring_donation = $this->get_init_recurring_donation($customer_id);

                $tmp_log_entry['chronopay_is_recurring'][] = 'Init recurring D found: '.$init_recurring_donation->id; // TODO DBG

                $new_recurring_donation = Leyka_Donations::get_instance()->add_clone(
                    $init_recurring_donation,
                    array(
                        'status' => 'funded',
                        'payment_type' => 'rebill',
                        'init_recurring_donation' => $init_recurring_donation->id,
                        'chronopay_customer_id' => $customer_id,
                        'chronopay_transaction_id' => $transaction_id,
                    ),
                    array('recalculate_total_amount' => true,)
                );

                if(is_wp_error($new_recurring_donation)) {
                    $tmp_log_entry['chronopay_is_recurring'][] = print_r('Error adding rebill recurring', $new_recurring_donation); // TODO DBG
                    return false;
                }

                $tmp_log_entry['chronopay_is_recurring'][] = 'Rebill recurring added: '.$new_recurring_donation->id; // TODO DBG

                Leyka_Donation_Management::send_all_emails($new_recurring_donation->id);

            }

        } else if( // Single payment. For now, processing is just like initial rebills
            leyka_options()->opt('chronopay_card_product_id_'.$currency_string) &&
            $_POST['product_id'] == leyka_options()->opt('chronopay_card_product_id_'.$currency_string)
        ) {

            $tmp_log_entry['chronopay_is_single'] = array('Is single'); // TODO DBG

            if($donation->status != 'funded') {

                $donation->add_gateway_response($_POST);
                $donation->status = 'funded';

                if( !$donation->donor_email && !empty($_POST['email']) ) {
                    $donation->donor_email = $_POST['email'];
                }

                Leyka_Donation_Management::send_all_emails($donation->id);

                $donation->chronopay_customer_id = $customer_id;
                $donation->chronopay_transaction_id = $transaction_id;

                $tmp_log_entry['chronopay_is_single'][] = 'Single added'; // TODO DBG

            }
        }

        // TODO DBG
        $tmp_log[] = $tmp_log_entry;
        set_transient('leyka_tmp_dbg', $tmp_log);
        // TODO DBG - END

        status_header(200);
        die(0);

    }

    public function get_init_recurring_donation($recurring) {

        if(is_a($recurring, 'Leyka_Donation_Base')) {
            $recurring = $recurring->chronopay_customer_id;
        } else if(empty($recurring)) {
            return false;
        }

        $init_recurring_donation = Leyka_Donations::get_instance()->get(array(
            'meta' => array(array('key' => '_chronopay_customer_id', 'value' => $recurring,),),
            'type' => 'rebill',
            'status' => 'funded',
            'orderby' => 'date',
            'order' => 'ASC',
            'get_single' => true,
        ));

        return $init_recurring_donation ? $init_recurring_donation : false;

    }

    /**
     * Check if there is already a donation with transaction ID given.
     *
     * @param $chronopay_transaction_id string Chronopay transaction ID value.
     * @return boolean
     */
    protected function _donation_exists($chronopay_transaction_id) {

        $chronopay_transaction_id = trim($chronopay_transaction_id);

        if(empty($chronopay_transaction_id)) {
            return false;
        }

        return Leyka_Donations::get_instance()->get_count(array(
            'meta' => array(array('key' => 'chronopay_transaction_id', 'value' => $chronopay_transaction_id,),),
            'get_single' => true,
        )) > 0;

    }

    public function cancel_recurring_subscription(Leyka_Donation_Base $donation) {

        $ch = curl_init();

        $product_id = leyka_options()->opt($donation->payment_method_id.'_product_id_'.$donation->currency);
        $hash = md5(leyka_options()->opt('chronopay_shared_sec').'-7-'.$product_id);

        curl_setopt_array($ch, array(
            CURLOPT_URL => 'https://gate.chronopay.com/',
            CURLOPT_HEADER => 0,
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_POSTFIELDS => "<request>
                <Opcode>7</Opcode>
                <hash>$hash</hash>
                <Customer>{$donation->chronopay_customer_id}</Customer>
                <Product>$product_id</Product>
            </request>",
        ));

        $result = curl_exec($ch);
        if($result === false) {

            $errno = curl_errno($ch);
            $error = curl_error($ch);
            curl_close($ch);
            die( json_encode(array('status' => 0, 'message' => $error." ($errno)")) );

        } else {

            $donation->add_gateway_response($result);

            $p = xml_parser_create();
            $response_xml = array();
            xml_parse_into_struct($p, $result, $response_xml);
            xml_parser_free($p);

            $response_ok = false;
            $response_text = '';
            $response_code = 0;
            foreach($response_xml as $index => $tag) {

                if(strtolower($tag['tag']) == 'code' && $tag['type'] == 'complete') {
                    $response_ok = $tag['value'] == '000';
                    if( !$response_ok ) {
                        $response_code = $tag['value'];
                        $response_text = $response_xml[$index+1]['value'];
                    }

                    break;
                }
            }

            curl_close($ch);
            if($response_ok) {

                // Save the fact that recurrents has been cancelled:
                $init_recurring_donation = $this->get_init_recurring_donation($donation);
                $init_recurring_donation->recurrents_cancelled = true;

                die(json_encode(array('status' => 1, 'message' => __('Recurring subscription cancelled.', 'leyka'))));

            } else {
                die(json_encode(array('status' => 0, 'message' => sprintf(__('Error on a gateway side: %s', 'leyka'), $response_text." (code $response_code)"))));
            }

        }

    }

    public function get_gateway_response_formatted(Leyka_Donation_Base $donation) {

        if( !$donation->gateway_response ) {
            return array();
        }

        $response_vars = maybe_unserialize($donation->gateway_response);
        if( !$response_vars || !is_array($response_vars) ) {
            return array();
        }

        return array(
            __('Operation status:', 'leyka') => $response_vars['transaction_type'],
            __('Transaction ID:', 'leyka') => $response_vars['transaction_id'],
            __('Full donation amount:', 'leyka') => $response_vars['total'].' '.$donation->currency_label,
            __("Gateway's donor ID:", 'leyka') => $response_vars['customer_id'],
            __('Response date:', 'leyka') => date('d.m.Y, H:i:s', strtotime($response_vars['date']))
        );

    }

    public function display_donation_specific_data_fields($donation = false) {

        if($donation) { // Edit donation page displayed ?>

            <label><?php _e('Chronopay customer ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">

                <?php if($donation->type === 'correction') {?>
                    <input type="text" id="chronopay-customer-id" name="chronopay-customer-id" placeholder="<?php _e('Enter Chronopay Customer ID', 'leyka');?>" value="<?php echo $donation->chronopay_customer_id;?>">
                <?php } else {?>
                    <span class="fake-input"><?php echo $donation->chronopay_customer_id;?></span>
                <?php }?>
            </div>

            <label><?php _e('Chronopay transaction ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">

                <?php if($donation->type === 'correction') {?>
                    <input type="text" id="chronopay-transaction-id" name="chronopay-transaction-id" placeholder="<?php _e('Enter Chronopay Transaction ID', 'leyka');?>" value="<?php echo $donation->chronopay_transaction_id;?>">
                <?php } else {?>
                    <span class="fake-input"><?php echo $donation->chronopay_transaction_id;?></span>
                <?php }?>
            </div>

        <?php } else { // New donation page displayed ?>

            <label for="chronopay-customer-id"><?php _e('Chronopay customer ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <input type="text" id="chronopay-customer-id" name="chronopay-customer-id" placeholder="<?php _e('Enter Chronopay Customer ID', 'leyka');?>" value="">
            </div>
            <?php
        }

    }

    public function get_specific_data_value($value, $field_name, Leyka_Donation_Base $donation) {
        switch($field_name) {
            case 'chronopay_customer_id': return $donation->get_meta('_chronopay_customer_id');
            case 'chronopay_transaction_id': return $donation->get_meta('_chronopay_transaction_id');
            default: return $value;
        }
    }

    public function set_specific_data_value($field_name, $value, Leyka_Donation_Base $donation) {
        switch($field_name) {
            case 'chronopay_customer_id': return $donation->set_meta('_chronopay_customer_id', $value);
            case 'chronopay_transaction_id': return $donation->set_meta('_chronopay_transaction_id', $value);
            default: return false;
        }
    }

    public function save_donation_specific_data(Leyka_Donation_Base $donation) {

        if(isset($_POST['chronopay-customer-id']) && $donation->chronopay_customer_id != $_POST['chronopay-customer-id']) {
            $donation->chronopay_customer_id = $_POST['chronopay-customer-id'];
        }

        if(isset($_POST['chronopay-transaction-id']) && $donation->chronopay_transaction_id != $_POST['chronopay-transaction-id']) {
            $donation->chronopay_transaction_id = $_POST['chronopay-transaction-id'];
        }

    }

    public function add_donation_specific_data($donation_id, array $donation_params) {

        if( !empty($donation_params['chronopay_customer_id']) ) {
            Leyka_Donations::get_instance()
                ->get_donation($donation_id)
                ->set_meta('_chronopay_customer_id', $donation_params['chronopay_customer_id']);
        }

        if( !empty($donation_params['chronopay_transaction_id']) ) {
            Leyka_Donations::get_instance()
                ->get_donation($donation_id)
                ->set_meta('_chronopay_transaction_id', $donation_params['chronopay_transaction_id']);
        }

    }

}


class Leyka_Chronopay_Card extends Leyka_Payment_Method {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'chronopay_card';
        $this->_gateway_id = 'chronopay';
        $this->_category = 'bank_cards';

        $this->_description = apply_filters(
            'leyka_pm_description',
            __('Chronopay allows a simple and safe way to pay for goods and services with bank cards through internet. You will have to fill a payment form, you will be redirected to the <a href="http://www.chronopay.com/ru/">Chronopay</a> secure payment page to enter your bank card data and to confirm your payment.', 'leyka'),
            $this->_id,
            $this->_gateway_id,
            $this->_category
        );

        $this->_label_backend = __('Bank card', 'leyka');
        $this->_label = __('Bank card', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-visa.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mastercard.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-maestro.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mir.svg',
        ));

        $this->_submit_label = __('Donate', 'leyka');
        $this->_default_currency = 'rur';

    }

    protected function _set_dynamic_attributes() {

        if(leyka_options()->opt('chronopay_card_product_id_rur')) {
            $this->_supported_currencies[] = 'rur';
        }
        if(leyka_options()->opt('chronopay_card_product_id_usd')) {
            $this->_supported_currencies[] = 'usd';
        }
        if(leyka_options()->opt('chronopay_card_product_id_eur')) {
            $this->_supported_currencies[] = 'eur';
        }

    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = array(
            'chronopay_card_product_id_rur' => array(
                'type' => 'text',
                'title' => __('Chronopay product_id for RUR', 'leyka'),
                'comment' => __('Please, enter Chronopay product_id for RUR currency.', 'leyka'),
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '012345-0001-0001'),
            ),
            'chronopay_card_product_id_usd' => array(
                'type' => 'text',
                'title' => __('Chronopay product_id for USD', 'leyka'),
                'comment' => __('Please, enter Chronopay product_id for USD currency.', 'leyka'),
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '012345-0001-0002'),
            ),
            'chronopay_card_product_id_eur' => array(
                'type' => 'text',
                'title' => __('Chronopay product_id for EUR', 'leyka'),
                'comment' => __('Please, enter Chronopay product_id for EUR currency.', 'leyka'),
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '012345-0001-0003'),
            ),
            'chronopay_card_rebill_product_id_rur' => array(
                'type' => 'text',
                'title' => __('Chronopay product_id for rebills in RUR', 'leyka'),
                'comment' => __('Please, enter Chronopay product_id for rebills in RUR currency.', 'leyka'),
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '012345-0001-0011'),
            ),
            'chronopay_card_rebill_product_id_usd' => array(
                'type' => 'text',
                'title' => __('Chronopay product_id for rebills in USD', 'leyka'),
                'comment' => __('Please, enter Chronopay product_id for rebills in USD currency.', 'leyka'),
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '012345-0001-0012'),
            ),
            'chronopay_card_rebill_product_id_eur' => array(
                'type' => 'text',
                'title' => __('Chronopay product_id for rebills in EUR', 'leyka'),
                'comment' => __('Please, enter Chronopay product_id for rebills in EUR currency.', 'leyka'),
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '012345-0001-0013'),
            ),
        );

    }

    public function has_recurring_support() { // Support recurring donations only if both single & recurring options set
        return ( !!leyka_options()->opt('chronopay_card_rebill_product_id_rur') && !!leyka_options()->opt('chronopay_card_product_id_rur') ) ||
            ( !!leyka_options()->opt('chronopay_card_rebill_product_id_usd') && !!leyka_options()->opt('chronopay_card_product_id_usd') ) ||
            ( !!leyka_options()->opt('chronopay_card_rebill_product_id_eur') && !!leyka_options()->opt('chronopay_card_product_id_eur') ) ? 'passive' : false;
    }

}

function leyka_add_gateway_chronopay() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_gateway(Leyka_Chronopay_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_chronopay');