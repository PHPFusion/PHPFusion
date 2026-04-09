**Filename:** https://stripe.com/docs/testing

**Basic Test Cards**

| NUMBER	| BRAND	| CVC	| DATE|
|---|---|---|----|
| 4242424242424242	|Visa|	Any 3 digits|	Any future date|
| 4000056655665556	|Visa (debit)|	Any 3 digits|	Any future date|
| 5555555555554444	|Mastercard|	Any 3 digits|	Any future date|
| 2223003122003222	|Mastercard (2-series)|	Any 3 digits|	Any future date|
| 5200828282828210	|Mastercard (debit)|	Any 3 digits|	Any future date|
| 378282246310005	|American Express|	Any 3 digits|	Any future date|
| 371449635398431	|American Express|	Any 4 digits|	Any future date|
| 6011111111111117	|Discover|	Any 3 digits|	Any future date|
| 6011000990139424	|Discover|	Any 3 digits|	Any future date|
| 3056930009020004	|Diners Club|	Any 3 digits|	Any future date|
| 36227206271667	|Diners Club|	Any 3 digits|	Any future date|
| 3566002020360505	|JCB|	Any 3 digits|	Any future date|
| 6200000000000005	|UnionPay|	Any 3 digits|	Any future date|


**International Test Card**

| NUMBER	| TOKEN | PAYMENT METHOD | COUNTRY | BRAND |
|---|---|---|----|---|
| 4242424242424242	|tok_us|	pm_card_us|	United States (US)|Visa|
| 4000000760000002	|tok_br|	pm_card_br|	Brazil (BR)|Visa|
| 4000001240000000	|tok_ca|	pm_card_ca|	Canada (CA)|Visa|
| 4000004840008001	|tok_mx|	pm_card_mx|	Mexico (MX)|Visa|
|4000000400000008	|tok_at|	pm_card_at|	Austria (AT)|	Visa|
|4000000560000004	|tok_be|	pm_card_be|	Belgium (BE)|	Visa|
|4000001000000000	|tok_bg|	pm_card_bg|	Bulgaria (BG)|	Visa|
|4000001960000008	|tok_cy|	pm_card_cy|	Cyprus (CY)|	Visa|
|4000002030000002	|tok_cz|	pm_card_cz|	Czech Republic (CZ)|	Visa|
|4000002080000001	|tok_dk|	pm_card_dk|	Denmark (DK)	|Visa|
|4000002330000009	|tok_ee|	pm_card_ee|	Estonia (EE)|	|Visa|
|4000002460000001	|tok_fi|	pm_card_fi|	Finland (FI)|	Visa|
|4000002500000003	|tok_fr|	pm_card_fr|	France (FR)|	Visa|
|4000002760000016	|tok_de|	pm_card_de|	Germany (DE)|	Visa|
|4000003000000030	|tok_gr|	pm_card_gr|	Greece (GR)|	Visa|
|4000003480000005	|tok_hu|	pm_card_hu|	Hungary (HU)|	Visa|
|4000003720000005	|tok_ie|	pm_card_ie|	Ireland (IE)|	Visa|
|4000003800000008	|tok_it|	pm_card_it|	Italy (IT)|	Visa|
|4000004280000005|	tok_lv|	pm_card_lv|	Latvia (LV)|	Visa|
|4000004400000000	|tok_lt|	pm_card_lt|	Lithuania (LT)|	Visa|
|4000004420000006	|tok_lu|	pm_card_lu|	Luxembourg (LU)|	Visa|
|4000004700000007	|tok_mt|	pm_card_mt|	Malta (MT)|	Visa|
|4000005280000002	|tok_nl|	pm_card_nl|	Netherlands (NL)|	Visa|
|4000005780000007	|tok_no|	pm_card_no|	Norway (NO)|	Visa|
|4000006160000005	|tok_pl|	pm_card_pl|	Poland (PL)|	Visa|
|4000006200000007	|tok_pt|	pm_card_pt|	Portugal (PT)|	Visa|
|4000006420000001	|tok_ro|	pm_card_ro|	Romania (RO)|	Visa|
|4000006430000009	|tok_ru|	pm_card_ru|	Russian Federation (RU)|	Visa|
|4000007050000006	|tok_si|	pm_card_si|	Slovenia (SI)|	Visa|
|4000007030000001	|tok_sk|	pm_card_sk|	Slovakia (SK)|	Visa|
|4000007240000007	|tok_es|	pm_card_es|	Spain (ES)|	Visa|
|4000007520000008	|tok_se|	pm_card_se|	Sweden (SE)|	Visa|
|4000007560000009	|tok_ch|	pm_card_ch|	Switzerland (CH)|	Visa|
|4000008260000000	|tok_gb|	pm_card_gb|	United Kingdom (GB)|	Visa|
|4000058260000005	|tok_gb_debit|	pm_card_gb_debit|	United Kingdom (GB)|	Visa (debit)|
|4000000360000006	|tok_au|	pm_card_au|	Australia (AU)|	Visa|
|4000001560000002	|tok_cn|	pm_card_cn|	China (CN)|	Visa|
|4000003440000004	|tok_hk|	pm_card_hk|	Hong Kong (HK)|	Visa|
|4000003560000008	|tok_in|	pm_card_in|	India (IN)|	Visa|
|4000003920000003	|tok_jp|	pm_card_jp|	Japan (JP)|	Visa|
|3530111333300000	|tok_jcb|	pm_card_jcb|	Japan (JP)|	JCB|
|4000004580000002	|tok_my|	pm_card_my|	Malaysia (MY)|	Visa|
|4000005540000008	|tok_nz|	pm_card_nz|	New Zealand (NZ)|	Visa|
|4000007020000003	|tok_sg|	pm_card_sg|	Singapore (SG)|	Visa|


Regulatory (3D Secure) test card numbers

|NUMBER	|DESCRIPTION|
|---|---|
|4000002500003155	|This card requires authentication for one-time payments. However, if you set up this card and use the saved card for subsequent off-session payments, no further authentication is needed. In live mode, Stripe dynamically determines when a particular transaction requires authentication due to regional regulations such as Strong Customer Authentication.|
|4000002760003184	|This card requires authentication on all transactions, regardless of how the card is set up.|
|4000008260003178	|This card requires authentication for one-time payments. All payments will be declined with an insufficient_funds failure code even after being successfully authenticated or previously set up.|
|4000003800000446	|This card requires authentication for one-time and other on-session payments. However, all off-session payments will succeed as if the card has been previously set up.|
|4000053560000011	|This card requires authentication on all transactions, regardless of how you set it up. You can only use this card for INR payments.|
