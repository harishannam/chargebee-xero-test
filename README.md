# chargebee-xero-test
ChargeBee Xero Integration (Testing)

Follow the below instructions to connect your XERO account and setup your ChargeBee Webhook Settings.

* Visit http://pg.chipap.com/chargebee/xero_oauth/public.php
* Click "Connect to Xero" button and Authorize permission.
* After authorization it will redirect back and will ask for your Xero Invoicing account & Xero Revenue account (Bank). Select appropriate accounts and click submit.
* You will be displayed with a unique Webhook URL for your ChargeBee account.
* Copy the URL to ChargeBee > Settings > Webhook Settings > Add New Webhook > Webhook URL
* Save the settings.

For every new invoice that is generated in ChargeBee this application will create a new invoice in XERO account.

**Please Note** : This is a Xero Public Application and hence it will work only for 30 minutes after which it requires new authorization.
