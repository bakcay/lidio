<div align="center">  
  <a href="README.md"   >   TR <img style="padding-top: 8px" src="https://raw.githubusercontent.com/yammadev/flag-icons/master/png/TR.png" alt="TR" height="20" /></a>  
  <a href="README-EN.md"> | EN <img style="padding-top: 8px" src="https://raw.githubusercontent.com/yammadev/flag-icons/master/png/US.png" alt="EN" height="20" /></a>  
  <a href="README-DE.md"> | DE <img style="padding-top: 8px" src="https://raw.githubusercontent.com/yammadev/flag-icons/master/png/DE.png" alt="DE" height="20" /></a>  
</div>

# WHMCS için Lidio Ödeme Modülü

**Aktifleştirmek için**:
Modül dosyalarını WHMCS klasörüne bırakın. Dosyalar, `modules/gateways/lidio` dizinine yerleştirilmelidir.

**Aktifleştirmek için adımlar**:
1. WHMCS yönetici panelinde, **apps / integrations** bölümüne gidin.
2. **Payments** sekmesine tıklayın ve Lidio'yu bulun.
3. Lidio'yu aktifleştirmek için tıklayın.

**Sonrasında**, **system settings** > **payment gateways** bölümünde aşağıdaki alanları doldurun:
- Merchant Code
- API Key (token)
- Merchant Key
- API Password

Modül aktifleştirildiğinde, 3 farklı modda çalışacaktır:
1. **3D**: İframe içinde, müşterilerinizin fatura ödemeleri banka ekranlarının işlemleri WHMCS arayüzünde merchant modunda iframe ile görünür.
2. **2D**: Şu anda desteklenmemektedir.
3. **Linked**: Müşteriyi fatura ödemek için Lidio sayfalarına yönlendirir. Ödeme sonucunda sistem otomatik olarak geri dönmez.
4. **Hosted**: Müşteriyi fatura ödemek için Lidio sayfalarına yönlendirir. Ödeme sonucunda başarılı veya başarısız sonuçlarında sistem otomatik olarak geri döner.

Lidio web sayfası: [https://www.lidio.com/](https://www.lidio.com/)

Geliştirici Web sayfası: [https://bunyam.in/](https://bunyam.in/)

Geliştirici Github sayfası: [https://github.com/bakcay](https://github.com/bakcay)
