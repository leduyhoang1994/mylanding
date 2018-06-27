<?php
function split_name($name) {
    $name = trim($name);
    $last_name = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
    $first_name = trim( preg_replace('#'.$last_name.'#', '', $name ) );
    return array($first_name, $last_name);
}

function addToURL( $key, $value, $url) {
    $info = parse_url( $url );
    parse_str( $info['query'], $query );
    return $info['scheme'] . '://' . $info['host'] . $info['path'] . '?' . http_build_query( $query ? array_merge( $query, array($key => $value ) ) : array( $key => $value ) );
}

if(isset($_POST['create-order'])) {
    $name = isset($_POST['name']) ? $_POST['name'] : "";
    $phone = isset($_POST['phone']) ? $_POST['phone'] : "";
    $email = isset($_POST['email']) ? $_POST['email'] : "";
    $address = isset($_POST['address']) ? $_POST['address'] : "";
    $productId = isset($_POST['product-id']) ? $_POST['product-id'] : "";
    $quant = isset($_POST['quant']) ? $_POST['quant'] : "";
    do_action('woocommerce_init');
    global $woocommerce;
    $_pf = new WC_Product_Factory();  

    $product = $_pf->get_product($productId);

    $address = array(
        'first_name' => split_name($name)[0],
        'last_name'  => split_name($name)[1],
        'company'    => 'null',
        'email'      => $email,
        'phone'      => $phone,
        'address_1'  => $address,
        'address_2'  => $address,
        'city'       => '',
        'state'      => '',
        'postcode'   => '',
        'country'    => '',
        'customer_note' => $product->id.' - '.$product->name.' : '.$quant[1]
    );
    
    $order = wc_create_order();
    $order->add_order_note( $product->id.' - '.$product->name.' : '.$quant[1] );
    $order->add_product( $product, intval($quant[1]));
    $order->set_address( $address, 'billing' );
    $order->set_address( $address, 'shipping' );
    
    $order->calculate_totals();
    $order->update_status("Completed", 'Created order programmatically', TRUE);  
    $back = addToURL( 'buy', 'success', $_SERVER['HTTP_REFERER']);
    header("Location:".$back);
    exit();
}