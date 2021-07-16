=== Beyond Pay for Gravity Forms ===
Contributors: beyondpay
Tags: credit card, payment, gravity forms, payment gateway, donation
Requires at least: 4.7
Tested up to: 5.8
Stable tag: trunk
Requires PHP: 7.0
License: MIT
License URI: https://opensource.org/licenses/MIT

Securely accept credit card payments within Gravity Forms using Beyond Pay gateway and optimize your cost of acceptance on B2B/corporate cards.

== Description ==
Securely accept credit cards directly from [Gravity Forms](https://gravityforms.com) using [Beyond](https://getbeyond.com) with this WordPress plugin.

## Features
- Accept Visa, MasterCard, American Express, Discover, JCB, and Diners Club brand cards directly on your website
- No redirect to a third-party hosted payment page, reducing checkout friction and cart abandonment
- Card data is securely captured with Beyond Pay Gateway's hosted payment fields presented via inline frame (iframe) and tokenized before reaching your WordPress server
- Simplifies merchant PCI compliance obligations to the shorter [Self-Assessment Questionnaire "A" (SAQ-A)](https://www.pcisecuritystandards.org/pci_security/completing_self_assessment)
- Support either pre-authorization and later capture, or authorization and capture at once (the combined "sale" transaction type)
- Optimize B2B card acceptance costs by automatically sending additional transaction data elements (also known as ["Level II" and "Level III" information](https://www.getbeyond.com/b2b-payments/)
- Gravity Forms' "Entries" page displays transaction details and allows for capture of authorizations
- Custom CSS styling support for the hosted payment fields so that you can create your ideal Gravity Forms experience
- Test/sandbox mode for development and staging

## Configuration

1. From your WordPress **/wp-admin** page, navigate to **Gravity Forms > Settings**.
1. Select the **Beyond Pay** tab on the left navigation bar.
1. Proceed to configure payment method options available on this page (see [screenshot](#beyond-pay-for-gravity-forms-configuration-settings)):
  - **Enable Test Mode** - controls whether transactions are sent to the Test/Sandbox or the Live/Production Beyond Pay Gateway environment and which type of API keys are expected; defaults to Live    
  - **PublicKey, PrivateKey, Username, Password, MerchantCode,** and **MerchantAccountCode** - these are the credentials by which the plugin authenticates to the Beyond Pay Gateway in order to process payments; for Test Mode, you can [request Beyond Pay Gateway sandbox API keys](https://forms.office.com/Pages/ResponsePage.aspx?id=Q9V6UxGq3USJSkGsz2Jk7yRG7q939HJFkFXKp4lfZo1URUJXWFhEMDlDTUs3OVlROEMxOExJQzZGNSQlQCN0PWcu) while live credentials are provided by Beyond once the merchant processing account is approved
  - **Transaction Mode** - controls how authorizations and payment captures are managed
    - Set this to ***Authorization*** to perform only an authorization ("pre-auth") when the form is submitted, which requires the **Capture Payment** button on the Gravity Forms "Entries" page be clicked in order for the payment to be captured (usually when an order is shipped)
    - Set this to ***Sale*** to authorize and capture the payment immediately (usually used for donations, registration forms, or digital purchases)
  - **Level II/III Data** - controls which extended data elements are automatically sent with transaction requests in order to [optimize interchange rates on B2B cards](https://www.getbeyond.com/b2b-payments/); Level II includes reference number and tax amount, while Level III includes line-item details. Set to Level III to ensure you always qualify for the best rates on eligible corporate purchasing cards. (Tax-exempt transactions are not eligible for Level II interchange rates but may be eligibile for Level III.)
  - **Advanced Styling** - allows for customized styling of the Beyond Pay card collection iframe via CSS

5. Click the **Update Settings** button once you have completed configuration; the page will refresh and a message reading "Beyond Pay settings updated" will display at the top.

You are now ready to accept payments through Beyond Pay for Gravity Forms!

## Creating a Payment Form and Feed

1. From your WordPress **/wp-admin** page, navigate to **Gravity Forms > New Form** and give the new form a title.
1. Select the **Pricing Fields** field type from the floating panels on the right.
1. Click **Credit Card** to add the Beyond Pay hosted payment fields to your form (see [screenshot](#creating-a-beyond-pay-form)).
1. Click **Update** to save your changes.
1. Click the **Settings** tab on the top of the edit forms page.
1. Click the **Beyond Pay** tab on the left navigation bar of the Form Settings menu.
1. Click **Add New Feed** button.
1. Enter a name for the feed and select **Products and Services** from the **Transaction Type** drop down.
   - NOTE: You should select "Products and Services" regardless of whether you are selling physical or digital products or services, or accepting a donation.
1. Select the field that will determine the Payment Amount or select **Form Total** to use the calculated total of all pricing fields.
1. Select which other fields in your form map to the customer billing information to be sent to Beyond Pay Gateway (specifically Name, Phone, Street Address, and Zip fields) (see [screenshot](#creating-a-beyond-pay-feed)).
1. Press the **Update Settings** button and you will see "Feed updated successfully."
1. Now, add or edit a WordPress page and select **Add Form** and choose the payment form you created: it will be embedded on your page and ready to take payments!
- NOTE: If you set the **Transaction Mode** to "Authorization", you MUST perform the additional "capture" step in order to receive payment:
  - Once you receive a form submission / payment, navigate to **Gravity Forms** > **Entries** from your **/wp-admin** panel.
  - From the **Payment Details** panel at the upper-right, click the **Capture Payment** button (see [screenshot](#beyond-pay-entry-payment-details)).

== Installation ==

There are 2 primary ways to install this plugin: **Automatic** or **Manual**

### Automatic Installation
1. From your WordPress **/wp-admin** page, navigate to **Plugins > Add New**.
1. Enter "Beyond Pay" in the search form in the top right of the screen.
1. Select *Beyond Pay for Gravity Forms* from the search results.
1. Click the **Install Now** button to install the plugin.
1. Click **Activate** to activate the plugin.

### Manual Installation
1. Make sure Gravity Forms is [installed and enabled on your WordPress instance](https://docs.gravityforms.com/how-to-install-gravity-forms/).
1. Download **beyond-pay-gravity-forms.zip** from [the latest release](https://github.com/getbeyond/beyondpay_gravityforms/releases/latest).
1. From your WordPress **/wp-admin** page, navigate to **Plugins > Add New**.
1. Click the **Upload Plugin** button at the top of the screen.
1. Select the **beyond-pay-gravity-forms.zip** file from your local filesystem that was obtained earlier.
1. Click **Install Now**.
1. When the installation is complete you will see the message "Plugin installed successfully."
1. Click the **Activate Plugin** button at the bottom of the page.
    - *For more information on managing WordPress plugins, see https://wordpress.org/support/article/managing-plugins/*

== Frequently Asked Questions ==

= Is it secure and/or compliant to accept credit cards directly on my website? =

Yes! Beyond Pay Gateway secures card data by hosting the actual payment fields and presenting them in an iframe so that the fields only *appear* to be part of the WooCommerce checkout form. 

Once card data is collected, then the information is further secured by *tokenization*: a process in which the sensitive card data is exchanged for a non-sensitive representation, or "token." This ensures that cardholder data is not sent from the consumer's browser to the merchant's web server, and only the surrogate token value comes into contact with the merchant's systems.

= Do I have to have an SSL/TLS certificate? =

Yes. All submission of sensitive payment data by the Beyond Pay is made via a secure HTTPS connection from the cardholder's browser. However, to protect yourself from man-in-the-middle attacks and to prevent your users from experiencing mixed content warnings in their browser, you MUST serve the page with your payment form over HTTPS.

= Does this gateway plugin support a sandbox or test option? =

Yes. For Test Mode, you can [request Beyond Pay Gateway sandbox API keys](https://forms.office.com/Pages/ResponsePage.aspx?id=Q9V6UxGq3USJSkGsz2Jk7yRG7q939HJFkFXKp4lfZo1URUJXWFhEMDlDTUs3OVlROEMxOExJQzZGNSQlQCN0PWcu) while production (live) API keys are provided by Beyond once the merchant processing account is approved.

= How can I get further support? =

Contact [BeyondPayIntegrations@getbeyond.com](mailto:BeyondPayIntegrations@getbeyond.com), [submit a support ticket via WordPress](https://wordpress.org/support/plugin/beyond-pay-for-gravity-forms/), or [submit an issue via GitHub](https://github.com/getbeyond/beyondpay_gravityforms/issues). For basic support and troubleshooting of your credit card authorizations and sales, you may also contact our Service Center at 1-888-480-1571.

== Screenshots ==

1. Beyond Pay for Gravity Forms with default form styling
2. Beyond Pay for Gravity Forms configuration settings
3. Creating a Beyond Pay form
4. Creating a Beyond Pay feed
5. Entries screen - Payment Details

== Changelog ==

= 1.1.1 =
* Initial public release.