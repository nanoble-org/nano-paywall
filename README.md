# Nano Paywall - A Nano-based content paywall for wordpress

## Description

Want to get paid in Nano for your digital content? Using the BrainBlocks payment service, the Nano Paywall WordPress plugin allows you to easily wrap your content in shortcodes that require payment in Nano to unlock access to the content.  This is mainly a proof-of-concept to show the microtransaction capabilities of the Nano currency, so use accordingly (see license details below).

## Default Settings

Go to WordPress admin > Nano Paywall to edit the default values for the plugin.  These can be overwritten by parameters in the shortcode (see below).
* Destination Nano account address - the destination address for BrainBlocks to forward payments to (shortcode parameter: `address`)
* Default amount (Nano) - amount in Nano to charge to view the content (shortcode parameter: `amount`)
* Description - details about the content the user is paying to access, shown above the payment button (shortcode parameter: `description`)
* Info - details that pop up when clicking the info button, good for disclaimers

## Shortcode

* `[nano_paywall]Content here[/nano_paywall]` - shows BrainBlocks payment option in front of content, uses default settings values
* `[nano_paywall address="xrb_397p6t19ajqnm6b9psgdhnpughk4moojm5ehero8srqzxexogofxnyz9myzj"]` - overwrite the default address
* `[nano_paywall amount="0.1"]` - overwrite the default amount
* `[nano_paywall description="This is a description of the content behind the paywall"]` - overwrite the default description
* `[nano_paywall info="This disclaimer shows up when clicking on the info button"]` - overwrite the default info details

## Installation

1. Download the plugin files
1. Create a .zip file from the nano-paywall folder
1. From WordPress admin, go to Plugins > Add New > Upload Plugin button
1. Choose the .zip file
1. Activate the plugin
1. Setup default settings in Nano Paywall menu
1. Wrap your content in the shortcode details listed above

## FAQs

**After someone pays, how long do they have access to the content?**
As this is only a proof-of-concept the payment confirmation is stored in the browser session,s o the user only has access while this session is active.  If they close their browser and re-open it they will be asked to pay again.

**Can I use this on my site?**
This is available for use under the MIT License (see below), but as it is just a proof-of-concept please proceed with caution on a production site. We are not responsible for any issues or loss of funds.  Make sure to update the default Information details with any disclaimer information as well.

**How small of payments can this handle?**
Currently the amounts are set in Nano and can be properly configured to 6 points of precision behind the decimal place. So the smallest fee would be 0.000001 Nano.  With any small amounts it is recommneded you test with various wallets to ensure they all work for your use case.

**Do I have to sign up with BrainBlocks to use this?**
No, BrainBlocks does not currently require sign up to use as a Nano payment capture method. Please consult your local laws before accepting Nano.

## License & Disclaimer

All assets and code are under the [![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://github.com/nanoble-org/nano-paywall/blob/master/LICENSE)

You are fully responsible for your use of this software.  There are no guarantees or warranties.  You are responsible for any potential loss of funds.