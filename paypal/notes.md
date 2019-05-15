# PayPal Integration
Basic Tutorial: https://developer.paypal.com/docs/checkout/integrate/

- Sandbox: https://api.sandbox.paypal.com
- Live: https://api.paypal.com

## Get Access Token
### IMPORTANT: Access tokens expire! Retrieve new ones as necessary.
```
curl -v https://api.sandbox.paypal.com/v1/oauth2/token \
   -H "Accept: application/json" \
   -H "Accept-Language: en_US" \
   -u "<client_id>:<secret>" \
   -d "grant_type=client_credentials"
```
- It will return:
```
{
  "scope": "<scope>",
  "access_token": "<Access-Token>",
  "token_type": "Bearer",
  "app_id": "<app_id>",
  "expires_in": <num_seconds>,
  "nonce": "<nonce>"
}
```
- Use the access token in a header, like so:
```
curl -v -X GET https://api.sandbox.paypal.com/v1/invoicing/invoices?page=3&page_size=4&total_count_required=true \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <Access-Token>"
```

## PayPal REST API
- PayPal API Overview: https://developer.paypal.com/docs/api/overview/
- PayPal API: https://developer.paypal.com/docs/api/reference/api-requests/#
- PayPal Orders API: https://developer.paypal.com/docs/api/orders/v2/

### Template for `curl` Command:
```
curl -v -X GET https://api.sandbox.paypal.com/<path>?<query> \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <Access-Token>"
```

### Useful Paths
- Create order: `/v2/checkout/orders`
- Update order: `/v2/checkout/orders/{order_id}`
- Show order details: `/v2/checkout/orders/{order_id}`
- Authorize payment for order: `/v2/checkout/orders/{order_id}/authorize`
- Capture payment for order: `/v2/checkout/orders/{order_id}/capture`

### Useful References
- https://github.com/jcleblanc/paypal-examples/blob/master/rest/php/api-wrapper/requests.php