<div align="center">  
  <a href="README.md"   >   TR <img style="padding-top: 8px" src="https://raw.githubusercontent.com/yammadev/flag-icons/master/png/TR.png" alt="TR" height="20" /></a>  
  <a href="README-EN.md"> | EN <img style="padding-top: 8px" src="https://raw.githubusercontent.com/yammadev/flag-icons/master/png/US.png" alt="EN" height="20" /></a>  
  <a href="README-DE.md"> | DE <img style="padding-top: 8px" src="https://raw.githubusercontent.com/yammadev/flag-icons/master/png/DE.png" alt="DE" height="20" /></a>  
</div>

# Lidio Payment Module for WHMCS

**To activate the module**:
Place the module files in the WHMCS folder. They should go into the `modules/gateways/lidio` directory.

**Steps to activate**:
1. Go to the **apps / integrations** section in the WHMCS admin panel.
2. Click on the **Payments** tab and find Lidio.
3. Click to activate Lidio.

Afterwards, in the **system settings** > **payment gateways** section, fill in the following fields:
- Merchant Code
- API Key (token)
- Merchant Key
- API Password

When the module is activated, it will work in 3 different modes:
1. **3D**: Within an iframe, your customers' invoice payments will appear in the WHMCS interface, showing the bank screens' transactions in merchant mode.
2. **2D**: Currently not supported.
3. **Linked**: Redirects the customer to Lidio pages for invoice payment. The system does not automatically return after the payment.
4. **Hosted**: Redirects the customer to Lidio pages for invoice payment. The system automatically returns in case of successful or unsuccessful payment results.

Lidio website: [https://www.lidio.com/](https://www.lidio.com/)
Developer website: [https://bunyam.in/](https://bunyam.in/)
Developer GitHub page: [https://github.com/bakcay](https://github.com/bakcay)
