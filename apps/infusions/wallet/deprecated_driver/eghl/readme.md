Below is your Development account login : 
-----------------------------------
Production/Live account - https://securepay.e-ghl.com/IPG/Payment.aspx

SIT (SYSTEM INTEGRATION TESTING)

Test URL: https://test2pay.ghl.com/IPGSG/Payment.aspx // Not for click
Credentials
-------------
**Merchant ID:**            sit

**Merchant Password:**      sit12345

Test Cards
--------------
**VISA:** 4444333322221111

**MasterCard:** 5555444433332222

**Expiry Date:** any value
**CVV2:** any value

Txn Amount
0.14 – simulate failed

0.53 – simulate no response from gateway

Other amount – approved


----------------------------

Payment API for Customer to perform online payment (Request/Response) - customer side
Query Request to Query payment status (Request/Response) - admin side for payment status --> Reverse request for reversing payment (before Settlement)
Capture Request to Callback Data  
Refund Request - refunding to customer
Settlement Request

In index we send to checkout.php .. from checkout.php send to another url.

So in roadmap we do a default return. you can return to hosting finalize.

Here, we do this.
index.php then to ?checkout=yes
then to ?return=callback